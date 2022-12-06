> 转摘：
> 
> 1. [Go: 通过例子学习 Map 的设计 — Part Ihttps://mp.weixin.qq.com/s/YwsemMCuPmeqeWBTIe3PWQ]()
> 2. [一文啃透 Go map：初始化和访问](https://mp.weixin.qq.com/s/iL9dgMW47q0ySTYkvfl6fg)
> 3. [golang中map底层B值的计算逻辑](https://zhuanlan.zhihu.com/p/366472077)
> 4. [Go map 如何缩容？](https://mp.weixin.qq.com/s/Slvgl3KZax2jsy2xGDdFKw)

## 一、顶层设计

Go 内置类型 map 是使用哈希表实现的，主要包含三个具体实现的部分：桶（存储键值对的数据结构）、哈希（键值对的索引）、负载因子（判断 map 是否需要扩容的指标）。

### 1.1 桶

Go 将键值对存储在桶中，每个桶容纳 8 个键值对。map 在扩容的时候，哈系统的数量会直接翻倍。

下面是持有 4 个桶的 map 的粗略示意图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635482406472-da1c6f414fa4.jpg)

### 1.2 哈希

当一个键值对存入到 map 中时，Go 会根据它的键生成哈希值，然后根据哈希值决定将其分配到哪一个桶里。

比如，对于键值对`"foo" = 1`的插入作为例子，生成的哈希值假设是`15491954468309821754`。将该值用于位操作，掩码对应于桶的数量减 1。对于有 4 个桶的 map 来说，掩码就是 3。位操作如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635482612109-6061462222ef.jpg)

位运算得到的结果是 2，也就是表示这个键值对要放在编号为 2 的桶里。

哈希值不仅用于决定值应该在哪个桶里，还会参与其他的操作。每个桶都将哈希值的首字节存储在一个内部数组中，这使得 Go 可以对键进行索引，并跟踪桶中的空槽。

下面是一个在二进制表示下的哈希的例子：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635482769355-158bc04587b3.jpg)

通过这个被称为 top hash 的表，Go 可以在数据访问期间使用它们与请求键的哈希值进行比较。

### 1.3 负载因子

根据程序中对 map 的使用，Go 需要对 map 进行扩容，以便管理较多的键值对。

> Go map 中的缩容相当于无操作。

在存储键值对时，桶会将它存储在 8 个可用的插槽中。如果这些插槽全部不可用，Go 会创建一个溢出桶，并与当前桶相链接。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635482994408-ef6727076b90.jpg)

这个`overflow`属性表明了桶的内部结构。然而，增加溢出桶会降低 map 的性能。作为弥补，Go 将会分配新的桶（当前桶的数量的两倍），保存一个旧桶和新桶之间的连接，逐步将旧桶迁移到新桶中。

实际上，在新桶分配之后，每个参与过写操作的桶，如果操作还未完成，都将被迁移。被迁移的桶中的所有键值对都将会重新分配到新桶中。这意味着，先前在同一个桶中存储在一起键值对，现在可能被分配到不同的桶中。

Go 使用负载因子来判断何时开始分配新桶并迁移旧桶。Go 在 map 中使用 6.5 作为负载因子。

可以在代码中看到与负载因子相关的研究：

```go
// Picking loadFactor: too large and we have lots of overflow
// buckets, too small and we waste a lot of space. I wrote
// a simple program to check some stats for different loads:
// (64-bit, 8 byte keys and values)
//  loadFactor    %overflow  bytes/entry     hitprobe    missprobe
//        4.00         2.13        20.77         3.00         4.00
//        4.50         4.05        17.30         3.25         4.50
//        5.00         6.85        14.77         3.50         5.00
//        5.50        10.55        12.94         3.75         5.50
//        6.00        15.27        11.67         4.00         6.00
//        6.50        20.90        10.79         4.25         6.50
//        7.00        27.14        10.15         4.50         7.00
//        7.50        34.03         9.73         4.75         7.50
//        8.00        41.10         9.40         5.00         8.00
//
// %overflow   = percentage of buckets which have an overflow bucket
// bytes/entry = overhead bytes used per key/value pair
// hitprobe    = # of entries to check when looking up a present key
// missprobe   = # of entries to check when looking up an absent key
```

如果桶中键值对的平均容量超过 6.5，map 将会扩容。考虑到基于键的哈希值的分配并不均匀，正如在上面列出的，使用 8 作为负载因子会导致大量的溢出桶。

## 二、Map 数据结构

Go Map 数据类型的底层整体结构如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1632304176236-dceb4729d396.jpg)

可以看到，Map 数据类型是一个 hmap 结构体，下面包含多个属性、一个 mapextra 结构、多个 bmap 结构组成的链表。

### 2.1 hmap

hmap 结构的定义如下所示：

```go
type hamp struct {
  count      int
  flags      uint8
  B          uint8
  noverflow  uint16
  hash0      uint32
  buckets    unsafe.Pointer
  oldbuckets unsafe.Pointer
  nevacuate  uintptr
  extra      *mapextra
}
```

* `count` map 的大小，也就是`len()`返回的值，代指 map 中的键值对个数；
* `flags` 状态标识，主要是 goroutine 写入和扩容机制相关状态控制，并发读写的判断条件之一就是该值。
* `B` 用于限定桶最大可容纳的元素数量，计算方式为：`log2(初始数量 / 负载因子)`，也就是其值要满足：`初始数量 <= 2^B * 负载因子`。其中负载因子默认为 6.5。
* `noverflow` 溢出桶的数量。
* `hash0` 哈希因子。
* `buckets` 保存当前桶数据的指针地址（指向一段连续的内存地址，主要存储键值对数据）.
* `oldbuckets` 保存旧桶的指针地址。
* `nevacuate` 迁移进度。
* `extra` 原有 buckets 满载之后，会发生扩容动作，在该数据中保存了扩容的相关信息。

这里需要注意：

1. 如果 keys 和 values 都不包含指针，并且允许内联的情况下，会将 bucket 标识为不包含指针。使用 extra 存储溢出桶就可以避免 GC 扫描整个 map，节省不必要的开销。
2. Go 用来增量扩容，而 buckets 和 oldbuckets 也是与扩容相关的载体，一边情况下只使用 buckets，而 oldbuckets 是空的。如果正在扩容的话，oldbuckets 就不为空了，同事 buckets 的大小也会改变。
3. 当 hint 大于 8 时，就会使用`*mapextra`做溢出桶；若小于 8 则存储在 buckets 桶中。

> `B`的计算参考：[golang中map底层B值的计算逻辑](https://zhuanlan.zhihu.com/p/366472077)

### 2.2 mapextra

mapextra 用于存储 map 扩容的进度信息。结构定义如下：

```go
type mapextra struct {
  overflow     *[]*bmap
  oldoverflow  *[]*bmap
  nextOverflow *bmap
}
```

* `overflow` 为`hmap.buckets`（当前）溢出桶的指针地址；
* `oldoverflow` 为`hamp.oldbuckets`（旧）溢出桶的指针地址；
* `nextOverflow` 为空闲溢出桶的指针地址。

### 2.3 bmap

bmap 用于存储键值对，并附带一些其他数据，定义如下：

```go
bucketCntBits = 3
bucketCnt     = 1 << bucketCntBits

type bmap struct {
  tophash  [bucketCnt]uint8
  keys     [bucketCnt]keyType
  values   [bucketCnt]valueType
  overflow *bmap
}
```

* `tophash` 容量为 8 的数组，用于存储 8 个 key 的 hash 值的高位。
* `keys` 容量为 8 的键类型数组，用于存储 8 个键。
* `values` 容量为 8 的值类型数组，用于存储 8 个值。
* `overflow` 溢出桶，当某个位置的 bmap 存储满了，就会在其后使用链表的方式在 overflow 上附加一个 bmap，继续进行存储。也即是：Go 采用数组 + 链地址的方式来解决哈希冲突。

![](http://cnd.qiniu.lin07ux.cn/markdown/1632314463580-28fca1220231.jpg)

需要注意的是：

1. tophash 中的每一个元素都是对应的 key 的 hahs 的高位。如果`tophash[0] < minTopHash`，则`tophash[0]`表示为迁移进度。
2. 键和值的数组类型`keyType`、`valueType`是在运行时阶段自动加入的，在源码中并没有。
3. map 的 key-value 存储并非`k/v/k/v/k/v/k/v`的模式，而是`k/k/k/k/v/v/v/v`形式。既：先存 8 个 key，再存 8 个 value。这主要是考虑在内存对齐，可以避免浪费内存。

## 三、初始化

### 3.1 函数原型及源码

map 的初始化方式有多种，对应的函数原型如下所示：

```go
func makemap_small() *hmap
func makemap64(t *maptype, hint int64, h *hmap) * hmap
func makemap(t *maptype, hint int, h *hmap) *hmap
```

* `makemap_small` 当`hint < 8`时会使用该方式来初始化 hmap，此时并不会立即初始化 hash table。
* `makemap64` 当`hint`类型为 int64 时的特殊转换及校验处理，后续实质调用的还是`makemap`。
* `makemap` 实现了标准的 map 初始化动作。

源码如下：

```go
func makemap(t *maptype, hint int64, h *hmap) *hmap {
  if hint < 0 || hint > int(maxSliceCap(t.bucket.size)) {
    hint = 0
  }
  
  if h == nil {
    h = new(hmap)
  }
  
  h.hash0 = fastrand()
  
  B := uint(8)
  for overLoadFactor(hint, B) {
    B++
  }
  h.B = B
  
  if h.B != 0 {
    var nextOverflow *bmap
    
    h.buckets, nextOverflow = makeBucketArray(t, h.B, nil)
    
    if nextOverflow != nil {
      h.extra = new(mapextra)
      h.extra.nextOverflow = netOverflow
    }
  }
  
  return h
}
```

流程如下：

1. 根据传入的`bucket`类型，获取其类型能够申请的最大容量大小，并对其长度`make(map[k]v, hint)`进行边界值检验；
2. 初始化 hamp；
3. 初始化哈希因子；
4. 根据传入的 hint 计算一个可以放下 hint 个元素的桶 B 的最小值；
5. 分配并初始化 hash table，如果 B 为 0 将在后续懒惰分配桶，大于 0 则会马上进行分配；
6. 返回初始化完毕的 hmap。

这里可以注意到，当`hint >= 8`(`h.B != 0`)时，第一次初始化 map 时，就会通过调用`makeBucketArray`对 buckets 进行分配。因此常会说，在初始化时指定一个适当大小的容量，这能够提升性能。

如果容量过少，而新增的键值对又很多时，就会导致频繁的分配 buckets，进行扩容迁移等 rehash 动作，最终的结果就是性能直接的下降。

不过，当`hint < 8`时，这种问题相对就不会凸显的太过明显，源码如下：

```go
func makemap_small() * hmap {
  h := new(hmap)
  h.hash0 = fastrand()
  return h
}
```

### 3.2 B 的计算

在前面的源码中，`h.B`的计算是通过如下代码循环得到结果的：

```go
B := uint8(0)
for overLoadFactor(hint, B) {
  B++
}
h.B = B
```

这里`overLoadFactor`函数的实现也很简单：

```go
func overLoadFactor(count int, B uint8) bool {
  return count > bucketCnt && uintptr(count) > loadFactorNum * (bucketShift(B) / loadFactorDen)
}

func bucketShift(b uint8) uintptr {
  return uintptr(1) << (b && (sy 1.PtrSize * 8 - 1))
}
```

### 3.3 图示

![](http://cnd.qiniu.lin07ux.cn/markdown/1632322475640-5699b72ab447.jpg)

## 四、访问

### 4.1 函数原型

map 主要有两种访问方式：`v := m[i]`和`v, ok := m[i]`。但这其实是由编译器自动处理的，这两种方式在汇编中会对应着不同的方法调用。比如，对于 key 为 string 类型的 map，这两种方式其实会被处理成`runtime.mapaccess1_faststr(SB)`和`runtime.mapaccess2_faststr(SB)`。

而且，在实现 map 元素访问上，有很几种方法，主要是包含针对 32/64位、string 类型的特殊处理。总的函数原型如下：

```go
mapaccess1(t *maptype, h *hmap, key unsafe.Pointer) unsafe.Pointer
mapaccess2(t *maptype, h *hmap, key unsafe.Pointer) (unsafe.Pointer, bool)

mapaccessK(t *maptype, h *hmap, key unsafe.Pointer) (unsafe.Pointer, unsafe.Pointer)

mapaccess1_fat(t *maptype, h *hmap, key, zero unsafe.Pointer) unsafe.Pointer
mapaccess2_fat(t *maptype, h *hmap, key, zero unsafe.Pointer) (unsafe.Pointer, bool)

mapaccess1_fast32(t *maptype, h *hmap, key uint32) unsafe.Pointer
mapaccess2_fast32(t *maptype, h *hmap, key uint32) (unsafe.Pointer, bool)

mapassign_fast32(t *maptype, h *hmap, key uint32) unsafe.Pointer
mapassign_fast32ptr(t *maptype, h *hmap, key unsafe.Pointer) unsafe.Pointer

mapaccess1_fast64(t *maptype, h *hmap, key uint64) unsafe.Pointer
...

mapaccess1_faststr(t *maptype, h *hmap, ky string) unsafe.Pointer
...
```

* `mapaccess1` 返回`m[key]`的指针地址，如果键不在 map 中，则返回对应类型的零值；
* `mapaccess2` 返回`m[key]`的指针地址，如果键不在 map 中，则返回零值和布尔值用于判断。

### 4.2 源码

```go
func mapaccess1(t *maptype, h *hmap, key unsafe.Pointer) unsafe.Pointer {
  ...
  if h = nil || h.count == 0 {
    return unsafe.Pointer(&zeroVal[0])
  }
  
  if h.flags & hashWriting != 0 {
    throw ("concurrent map read and map write")
  }
  
  alg := t.key.alg
  hash := alg.hash(key, uintptr(h.hash0))
  m := bucketMask(h.B)
  b := (*bmap)(add(h.buckets, (hash & m)*uintptr(t.bucketsize)))
  if c := h.oldbuckets; c != nil {
    if !h.sameSizeGrow() {
      // There used to be half as many buckets; mask down one more power of two.
      m >>= 1
    }
    
    oldb := (*bmap)(add(c, (hash & m)*uintptr(t.bucketsize)))
    if !evacuated(oldb) {
      b = oldb
    }
  }
  
  top := tophash(hash)
  for ; b != nil; b = b.overflow(t) {
    for i := uintptr(0); i < bucketCnt; i++ {
      if b.tophahs[i] != top {
        continue
      }
      
      k := add(unsafe.Pointer(b), dataOffset + i * uintptr(t.keysize))
      if t.indirectkey {
        k = *((*unsafe.Pointer)(k))
      }
      
      if alg.equal(key, k) {
        v := add(unsafe.Pointer(b), dataOffset + bucketCnt * uintptr(t.keysize) + i * uintptr(t.valuesize))
        if t.indirectvalue {
          v = *((*unsafe.Pointer)(v))
        }
        
        return v
      }
    }
  }
  
  return unsafe.Pointer(&zeroVal[0])
}
```

步骤如下：

1. 判断 map 是否为 nil、或长度是否为 0，如果是的话，则返回零值；
2. 判断当前是否并发读写 map，若是则抛出异常；
3. 根据 key 的不同类型调用不同的 hash 方法计算得出 hash 值；
4. 确定 key 在哪一个 bucket 中，并得到其位置；
5. 判断是否正在发生扩容（`h.oldbuckets`是否为 nil），若正在扩容，则到老的 buckets 中查找（因为 buckets 中可能还没有值，搬迁未完成）；若该 bucket 已经搬迁完毕，则到 buckets 中继续查找；
6. 计算 hash 的 tophash 值（高八位）；
7. 根据计算出来的 tophash，依次循环对比 buckets 的 tophash 值（快速试错）；
8. 如果 tophash 匹配成功，则计算 key 的所在位置，正式完整的对比两个 key 是否一致；
9. 若查找成功则返回；
10. 否则返回零值。

在上述步骤三中，踢到了根据不同类型计算出 hash 值，另外会计算出 hash 值的高八位和低八位。低八位会作为 bucket index，作用是用于找到 key 所在的 bucket；而高八位会存储在 bmap 的 tophash 中。

tophash 的作用是：在步骤七中进行迭代快速定位，这样可以提升性能，而不是一开始就直接用 key 进行一致性对比。

### 4.3 图示

![](http://cnd.qiniu.lin07ux.cn/markdown/1632323881598-1dea11aa37f0.jpg)

## 五、扩缩容

### 5.1 前导

在 Go 底层源码`src/runtime/map.go`中，扩缩容的处理方法是 grow 为前缀的方法来处理的。其中，扩缩容涉及到的是插入元素的操作，对应`mapassign`方法：

```go
func mapassign(t *maptype, h *hmap, key unsafe.Poiter) unsafe.Pointer {
  ...
  if !h.growing() && (overLoadFactor(h.count+1, h.B) || tooManyOverflowBuckets(h.overflow, h.B)) {
    hashGrow(t, h)
    goto again
  }
  ...
}

func (h *hmap) growing() bool {
  return h.oldBuckets != nil
}

func overLoadFactor(count int, B uint8) bool {
  return count > bucketCnt && uintptr(count) > loadFactorNum*(bucketShift(B)/loadFactorDen)
}

func tooManyOverflowBuckets(noverflow uint16, B uint8) bool {
  if B > 15 {
   B = 15
  }
  
  return noverflow >= uint16(1)<<(B&15)
}
```

针对扩缩容判断的核心逻辑如下：

1. 当前没有正在扩容，条件为：`h.oldbuckets`不为 nil；
2. 可以进行扩容，条件为：`h.count > (2^h.B)*6.5`；
3. 可以进行缩容，条件为：`h.noverflow >= 2^(h.B&15)`

### 5.2 缩容

无论是扩容还是缩容，都是由`hashGrow()`方法进行处理的：

```go
func hashGrow(t *maptype, h *hmap) {
  bigger := uint8(1)
  if !overLoadFactor(h.count+1, h.B) {
    bigger = 0
    h.flags != sameSizeGrow
  }
}
```

可以看到，根据当前 hash 表的容量是否过载来判断是扩容还是缩容：

* 如果是扩容，则 bigger 为 1，也就是会使得`h.B + 1`，表示 hash 表容量扩大一倍；
* 如果是缩容，则 bigger 为 0，也就是是的`h.B`保持不变，则 hash 表容量也不变。

可以得出结论：**map 的扩缩容主要区别在于`hmap.B`的容量大小改变**。

由于缩容的时候，`hmap.B`未发生变化，所以实际上其占用的内存空间也是不会减少的。这种方式其实是存在隐患的，也就是导致：**删除元素时，并不会释放内存，使得分配的总内存不断增加**。这在使用 map 来做大 key/value 存储时，如果不注意管理，很容易造成内存占用过多的情况。

要实现*真缩容*，Go Contributor @josharian 表示目前唯一**可用的解决方案是：创建一个新的 map，并从旧的 map 中复制元素，然后删除旧的 map 元素**。

示例如下：

```go
old := make(map[int]int, 9999999)
new := make(map[int]int, len(old))
for k, v := range old {
  new[k] = v
}
old = new
```

之所以不支持，简单来讲就是没有找到一个很好的方法实现，存在明确的实现成本问题，没法很方便的告诉 Go 运行时，是要保留存储空间以便后续的重用，还是赶紧释放存储空间，因为 map 会开始变得小很多。

抽象来看，症结就是：**需要保证增长结果在下一个开始之前完成**。此处的增长指的是：从小到大、从一个大小到相同的大小、从大到小等多重 case。

