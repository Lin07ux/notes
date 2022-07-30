> 转摘：[Go 每日一库之 roaring](https://mp.weixin.qq.com/s/zu0HyJybjwb19nNMDeFcEw)

### 1. 简介

集合是软件中的基本抽象，实现集合的方法有很多，例如 hash set、tree 等。要实现一个整数集合，位图（bitmap，也称为 bitset 位集合，bitvector 位向量）是个不错的方法。

使用 n 个位（bit）可以表示整数范围`[0, n)`，如果整数 i 在集合中，则第 i 位设置为 1。这样集合的交集（intersection）、并集（unions）和差集（difference）可以利用整数的按位与、按位或和按位与非来实现。而计算机执行位运算是非常迅速的。

[bitset](https://github.com/bits-and-blooms/bitset) 在某些场景中会消耗大量的内存。例如，设置第 1,000,000 位，需要占用超过 100kb 的内存。为此，bitset 库的作者又开发了压缩位图库 [roaring](https://github.com/RoaringBitmap/roaring)。

默认情况下，roaring 位图只能用来存储 32 位整数，所以 roaring 位图最多能包含 4294967296（2^32）个整数。

roaring 也提供了存户 64 位整数的扩展，即 [roaring/roaring64](https://github.com/RoaringBitmap/roaring/roaring64)，其提供的接口与 32 位的基本相同，但是 64 位的位图不保证与 Java/C++ 等格式兼容。

### 2. 使用

使用 Go Modules 安装 roaring：

```shell
go get -u github.com/RoaringBitmap/roaring
```

roaring 提供的基本操作与 bitset 大体相同，只是命名完全不一样。其基本方法有：

* `roaring.New()` 创建一个空位图
* `roaring.BitmapOf()` 创建集合元素，创建位图并添加这些元素
* `r.String()` 返回 bitmap 的字符串表示
* `r.Add()` 添加元素 n
* `r.GetCardinality()` 返回集合的基数（Cardinality），即元素个数
* `r.Contains()` 判断集合是否含有指定位置元素
* `r.And(r2)` 执行集合交集，会修改 r
* `r.Or(r2)` 执行集合并集，会修改 r

基本使用示例如下：

```go
func main() {
  r1 := roaring.BitmapOf(1, 2, 3, 4, 5, 100, 1000)
  fmt.Println(r1.String())         // {1,2,3,4,5,100,1000}
  fmt.Println(r1.GetCardinality()) // 7
  fmt.Println(r1.Contains(3))      // true
  
  r2 := roaring.BitmapOf(1, 100, 500)
  fmt.Println(r2.String())         // {1,100,500}
  fmt.Println(r2.GetCardinality()) // 3
  fmt.Println(r2.Contains(3))      // false
  
  r3 := roaring.New()
  r3.Add(1)
  r3.Add(11)
  r3.Add(111)
  fmt.Println(r3.String())         // {1,11,111}
  fmt.Println(r3.GetCardinality()) // 3
  fmt.Println(r3.Contains(11))     // true
  
  r1.Or(r2)                        // 执行并集
  fmt.Println(r1.String())         // {1,2,3,4,5,100,500,1000}
  fmt.Println(r1.GetCardinality()) // 8
  fmt.Println(r1.Contains(500))    // true
  
  r2.And(r3)
  fmt.Println(r2.String())         // {1}
  fmt.Println(r2.GetCardinality()) // 1
  fmt.Println(r2.Contains(1))      // true
}
```

### 3. 操作

#### 3.1 迭代

roaring 位图支持迭代：

```go
func main() {
  r := roaring.BitmapOf(1, 2, 3, 4, 5, 100, 1000)
  i := b.Iterator()
  for i.HasNext() {
    fmt.Println(i.Next())
  }
}
```

roaring 迭代的时候，需要先调用对象的`Iterator()`元素获得迭代器，然后循环调用迭代器的`HasNext()`方法检查是否有下一个元素，有的话就可以通过迭代器的`Next()`方法获取下一个元素。

上面的代码会依次输出 1/2/3/4/5/100/1000。

#### 3.2 并行操作

roaring 支持位图集合运算的并行执行。可以指定使用多少个 goroutine 对集合执行交集、并集等，以加速操作速度。同时可以传入可变数量的位图集合。

```go
func main() {
  r1 := roaring.BitmapOf(1, 2, 3, 4, 5, 100, 1000)
  r2 := roaring.BitmapOf(1, 100, 500)
  r3 := roaring.BitmapOf(1, 00, 1000)
  
  rAnd := roaring.ParAnd(4, r1, r2, r3)
  fmt.Println(rAnd.String())         // {1}
  fmt.Println(rAnd.GetCardinality()) // 1
  fmt.Println(rAnd.Contains(1))      // true
  fmt.Println(rAnd.Contains(100))    // false
  
  rOr := roaring.ParOr(4, r1, r2, r3)
  fmt.Println(rOr.String())         // {1,2,3,4,5,10,100,500,1000}
  fmt.Println(rOr.GetCardinality()) // 9
  fmt.Println(rOr.Contains(10))     // true
}
```

#### 3.3 写入与读取

roaring 可以将压缩的位图写入到文件中，并且格式与其他语言的实现保持兼容。也就是说，可以用 Go 将 roaring 位图写入文件，然后通过网络发送给另外一台机器，在这台机器上使用 C++或 Java 的实现读取这个文件。

```go
func main() {
  r := roaring.BitmapOf(1, 3, 5, 7, 100, 300, 500, 700)
  buf := &butes.Buffer{}
  _, _ := r.WirteTo(buf)
  
  newR := roaring.New()
  _, _ := newR.ReadFrom(&buf)
  if r.Equals(newR) {
    fmt.Println("write and read back ok.")
  }
}
```

这里使用到了两个新的方法：

* `r.WriteTo(w. io.Writer)` 将 bitmap 的数据写入到一个`io.Writer`中，可以是内存（`byte.Buffer`），也可以是文件（`os.File`），甚至可以是网络（`net.Conn`）；
* `r.ReadFrom(r io.Reader)` 从一个`io.Reader`中读取数据来初始化 bitmap 的值，来源同样可以是内存、文件或网络等。

这两个方法的返回值都是 size 和 err，在使用的时候需要注意处理错误情况。

### 4. 存储格式

roaring 可以写入文件中，也可以从文件中读取，并且提供多种语言兼容的格式，这与其底层的存储格式相关。

roaring 位图默认只存储 32 位的证书，在序列话的时候，会将这些证书分容器（container）存储。每个容器有一个 16 位的基数（Cardinality，即元素个数，范围为`[1, 65536]`）和一个键（key）。键取元素的最高有效 16 位（most significant），所以键的范围为`[0, 65536)`。

这样，如果两个整数的最高 16 位有效位相同，那么它们将被保存在同一个容器中。这样做还有一个好处：减少占用的空间。

另外，**所有整数都采用小端存储**。

#### 4.1 概览

roaring 采用的存储格式布局如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1659110317-60bb52f4e615.jpg)

* Cookie Header 用来识别一个二进制流是不是一个 roaring 位图，并且存储一些少量信息；
* Descriptive Header 用来描述容器的信息；
* Offset Header 是一个可选项，记录了每个容器相对于首位的偏移，使得能够随机访问任意容器；
* Container 是存储实际数据的容器。

roaring 中一共有 3 中类型的容器：

* array 数组型容器，为 16bit 的整数数组；
* bitset 位集型容器，使用上一篇文章介绍的 bitset 存储数据；
* run 游程型容器，使用长度+数据来编码数据。

#### 4.2 Cookie Header

Cookie Header 有两种类型，分别占用 32bit 和 64bit。

第一种类型，前 32bit 的值为 12346，此时紧接着的 32bit 表示容器数量（记为 n）。同时，这意味着，后面没有 run 类型的容器。12346 这个魔术数字被定义为常量`SERIAL_COOKIE_NO_RUNCONTAINER`。

![](http://cnd.qiniu.lin07ux.cn/markdown/1659111759-cabd08117d2c.jpg)

第二种类型，前 32bit 的最低有效 16 位的值为 12347，由于最高有效位 16 位存储的值等于容器数量 -1。将 cookie 右移 16 位再加 1 即可得到容器数量。由于这种类型的容器数量不会为 0，采用这种编码能记录的容器数量会多 1 个。这种方法在很多地方都有应用，例如 Redis 中。后面紧接着会使用`(n+7)/8`字节（作为一个 bitset）表示后面的容器是否为 run 容器。每位对应一个容器，1 表示对应的容器是 run 容器，0 表示不是 run 容器。

![](http://cnd.qiniu.lin07ux.cn/markdown/1659151449-0b20d470c0dc.jpg)

由于是小端存储，所以流的前 16bit 一定是 12346 或 12347。如果读取到了其他的值，说明文件损坏，直接退出程序即可。

#### 4.3 Descriptive Header

Cookie Header 之后就是 Descriptive Header，它使用一对 16bit 的数据描述每个容器：一个 16bit 存储键（即整数的最高有效 16bit），另一个 16bit 存储对应容器的基数（Cardinality）减 1（又见到了），即容器存储的整数数量。

如果有 n 个容器，则 Descriptive Header 需要 32n 位或 4n 字节。

![](http://cnd.qiniu.lin07ux.cn/markdown/1659160048-386cd82dc5a1.jpg)

结合 Cookie Header 和 Descriptive Header 就能知道每个容器的类型：

* 如果 Cookie 的值为 12347，那么就根据 Cookie 后的一个 bitset 判断该容器是否是 run 类型；
* 对于非 run 类型的容器，如果其基数（Cardinality）小于等于 4096，那么它就是一个 array 容器，否则就是一个 bitset 容器。

#### 4.4 Offset Header

满足以下任一条件，Offset Header 就会存在：

* Cookie 的值为 SERIAL_COOKIE_NO_RUNCOTARINER(12346)；
* Cookie 的值为 SERIAL_COOKIE(12347)，并且至少有 NO_OFFSET_THRESHOLD = 4 个容器。

Offset Header 为每个容器使用 32bit 值存储对应容器距离流开始处的偏移，单位为字节：

![](http://cnd.qiniu.lin07ux.cn/markdown/1659160358-a5df35a649db.jpg)

#### 4.5 Container

接下来就是实际存储数据的容器了，分为三种类型。

**1. array**

存储**有序的 16bit 无符号整数值**。有序便于使用二分查找提高效率。

16bit 值只是数据的最低有效 16bit。在 Descriptive Header 中，每个容器都有一个 16bit 的 key，将两者拼接起来才是实际的数据。

如果容器有 x 个值，占用空间 2x 字节。

![](http://cnd.qiniu.lin07ux.cn/markdown/1659160527-bd2e1463c758.jpg)


**2. bitmap/bitset**

**bitset 容器固定使用 8KB 的空间**。以 64bit 为单位（称为字，word）进行序列化。

因此，如果值 j 存在，则第 j/64 个字（从 0 开始）的 j%64 位会被设置为 1（从 0 开始）。

![](http://cnd.qiniu.lin07ux.cn/markdown/1659160626-694fb7fe11bf.jpg)

**3. run**

以一个表示 run 数量的 16bit 整数开始，后续每个 run 用一对 16bit 整数表示：前一个 16bit 表示开始的值，后一个 16bit 表示长度-1（再次见到）。

例如：11,4 这个整数对表示数据 11,12,13,14,15。

![](http://cnd.qiniu.lin07ux.cn/markdown/1659160720-46ebc1db3e55.jpg)

### 5. 编写解析代码

验证是否真的理解了 roaring 布局最有效的方法就是手写一个解析器。开发时，使用标准库`encoding/binary`可以很容易的处理大小端问题。

#### 5.1 定义常量

```go
const (
  SERIAL_COOKIE_NO_RUNCONTAINER = 12346
  SERIAL_COOKIE                 = 12347
  NO_OFFSET_THRESHOLD           = 4
)
```

#### 5.2 读取 Cookie Header

```go
func readCookieHeader(r io.Reader) (cookie uint16, containerNum uint32, runFlagBitset []byte) {
  binary.Read(r, binary.LittleEndian, &cookie)
  switch cookie {
  case SERIAL_COOKIE_NO_RUNCONTAINER:
    var dummy uint16
    binary.Read(r, binary.LittleEndian, &dummy)
    binary.Read(r, binary.LittleEndian, &containerNum)
    
  case SERIAL_COOKIE:
    var u16 uint16
    binary.Read(r, binary.LittleEndian, &u16)
    
    containerNum = uint32(u16)
    buf := make([]uint8, (containerNum+7)/8)
    r.Read(buf)
    runFlagBitset = buf[:]
    
  default:
    log.Fatal("unknown cookie")
  }
  
  fmt.Println(cookie, containerNum, runFlagBitset)
  return
}
```

#### 5.3 读取 Descriptive Header

```go
type keycard struct {
  key  uint16
  card uint16
}

func ReadDescriptiveHeader(r io.Reader, containerNum uint32) []keycard {
  var keycards []keycard
  var key uint16
  var card uint16
  
  for i := 0; i < int(containerNum); i++ {
    binary.Read(r, binary.LittleEndian, &key)
    binary.Read(r, binary.LittleEndian, &card)
    card += 1
    
    fmt.Println("container", i, "key", key, "card", card)
    keycards = append(keycards, keycard{key, card})
  }
  
  return keycards
}
```

#### 5.4 读取 Offset Header

```go
func readOffsetHeader(r io.Reader, cookie uint16, containerNum uint32) {
  if cookie == SERIAL_COOKIE_NO_RUNCONTAINER ||
    (cookie == SERIAL_COOKIE && containerNum >= NO_OFFSET_THERSHOLD) {
    var offset uint32
    for i := 0; i < int(containerNum); i++ {
      binary.Read(r, binary.LittleEndian, &offset)
      fmt.Println("offset", i, offset)
    }
  }
}
```

#### 5.5 读取容器

容器有三种类型，就要根据不同的类型调用不同的读取方法：

```go
// array
func readArrayContainer(r io.Reader, key, card uint16, bm *roaring.Bitmap) {
  var value uint16
  for i := 0; i < int(card); i++ {
    binary.Read(r, binary.LittleEndian, &value)
    bm.Add(uint32(key)<<16 | uint32(value))
  }
}

// bitmap
func readBitmapContainer(r io.Reader, key, card uint16, bm *roaring.Bitmap) {
  var u64s [1024]uint64
  for i := 0; i < 1024; i++ {
    binary.Read(r, binary.LittleEndian, &u64[i])
  }
  
  bs := bitset.From(u64s[:])
  for i := uint32(0); i < 9182; i++ {
    if bs.Test(uint(i)) {
      bm.Add(uint32(key)<<16 | i)
    }
  }
}

// run
func readRunContainer(r io.Reder, key uint16, bm *roaring.Bitmap) {
  var runNum uint16
  binary.Read(r, binary.LittleEndian, &runNum)
  
  var startNum uint16
  var length uint16
  for i := 0; i < int(runNum); i++ {
    binary.Read(r, binary.LittleEndian, &startNum)
    binary.Read(r, binary.LittleEndian, &length)
    length += 1
    for j := uint16(0); j < length; j++ {
      bm.Add(uint32(key)<<16 | uint32(startNum+j))
    }
  }
}
```

#### 5.6 主函数

```go
func main() {
  data, err := ioutil.ReadFile("../roaring.bin")
  if err != nil {
    log.Fatal(err)
  }
  
  r := bytes.NewReader(data)
  cookie, containerNum, runFlagBitset := readCookieHeader(r)
  keycards := readerDescriptiveHeader(r)

  readOffsetHeader(r, cookie, containerNum)
  
  bm := roaring.New()
  for i := uint32(0); i < uint32(containerNum); i++ {
    if runFlagBitset != nil && runFlagBitset[i/8]&(1<<(i%8)) != 0 {
      // run container
      readRunContainer(r, keycards[i].key, bm)
    } else if keycards[i].card <= 4096 {
      // array container
      readArrayContainer(r, keycards[i].key, keycards[i].card, bm)
    } else {
      // bitmap container
      readBitmapContainer(r, keycard[i].key, keycards[i].card, bm)
    }
  }
  
  fmt.Println(bm.String())
}
```

#### 5.7 运行

将前面的写入读取实例中的`byte.Buffer`的数据保存到文件`roaring.bin`中，然后执行上面的程序就可以解析这个文件了：

```
12346 1 []
container 0 key 0 card 8
offset 0 16
{1,3,5,7,100,300,500,700}
```

可见，能够成功的还原这个位图。

### 6. 总结

roaring 是压缩位图，如果不考虑内部实现，压缩位图和普通的位图在使用上并没有多少区别。

