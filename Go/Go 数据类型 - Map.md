> 转摘：
> 
> 1. [一文啃透 Go map：初始化和访问](https://mp.weixin.qq.com/s/iL9dgMW47q0ySTYkvfl6fg)
> 2. [golang中map底层B值的计算逻辑](https://zhuanlan.zhihu.com/p/366472077)

## 一、Map 数据结构

Go Map 数据类型的底层整体结构如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1632304176236-dceb4729d396.jpg)

可以看到，Map 数据类型是一个 hmap 结构体，下面包含多个属性、一个 mapextra 结构、多个 bmap 结构组成的链表。

### 1.1 hmap

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

### 1.2 mapextra

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

### 1.3 bmap

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

## 二、初始化

### 2.1 用法

声明 map 的方式有点类似于数组，不同之处是：

* 以`map`关键字开头；
* 需要指定键的类型（在`[]`中）；
* 指定值的类型（在`[]`后）。

也可以使用`make()`来声明。

例如：

```go
m := make(map[int32]int64)
dictionary := map[string]string{"test": "this is just a test"}
```

需要注意的是：**键的类型**很特别，**只能是一个可比较的类型**。因为如果不能判断两个键是否相等，就无法确保得到的是正确的值。

另一方面，值的类型可以是任意类型，甚至可以是另一个 map。

### 2.2 函数原型及源码

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

### 2.3 B 的计算

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

### 2.4 图示

![](http://cnd.qiniu.lin07ux.cn/markdown/1632322475640-5699b72ab447.jpg)

## 三、访问

### 3.1 用法

```go
v := m[i]
v, ok := m[i]
```

### 3.2 函数原型

在实现 map 元素访问上，有很几种方法，主要是包含针对 32/64位、string 类型的特殊处理。总的函数原型如下：

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

### 3.3 源码

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

### 3.4 图示

![](http://cnd.qiniu.lin07ux.cn/markdown/1632323881598-1dea11aa37f0.jpg)



