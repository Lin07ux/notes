> 转摘：
> 
> 1. [Golang 是否有必要内存对齐？](https://ms2008.github.io/2019/08/01/golang-memory-alignment/)
> 2. [Memory Layouts](https://go101.org/article/memory-layout.html)
> 3. [图解Go之内存对齐](http://blog.newbmiao.com/slides/%E5%9B%BE%E8%A7%A3Go%E4%B9%8B%E5%86%85%E5%AD%98%E5%AF%B9%E9%BD%90.pdf)

## 一、内存对齐

内存对齐是硬件层面的一种效率性考量的结果，内存对齐后的数据能够使得程序增加一些收益，避免一些问题。

Go 语言中的 struct 结构体的字段，在进行编译的时候，会由编译器进行内存填充以实现内存对齐。这也会导致同一个 struct 定义，在不同的字段顺序情况下，占用的内存空间大小是不同的。

> CPU 不会单个字节的去读取和写入内存，而是以块为单位的访问。块的大小可以为 2/4/6/8/16 字节等大小。
> 块大小称为内存访问粒度。假设访问粒度是 4，那么 CPU 就会以每 4 个字节大小的访问粒度去读取和写入内存。

### 1.1 内存对齐收益

* 提高代码的平台兼容性

    不是所有的硬件平台都能够访问任意地址上的任意数据。例如：特定的硬件平台只允许在特定地址获取特定类型的数据，否则会导致异常情况。

* 优化数据对内存的使用，提升性能

    若访问未对齐的内存，将会导致 CPU 进行两次内存访问，并且要花费额外的时钟周期来处理对齐运算。而已经内存对齐了的数据，仅需要一次访问就可以完成读取动作。这显然高效很多，是标准的空间换时间做法。

* 避免一些内存不对齐带来的坑

    在做一些操作时，会因为内存对齐造成异常和错误。比如，在 x86 32 位平台上，原子操作 64 bit 指针就需要强制 8 字节对齐，否则程序就会 panic。

* 有助于一些源码的阅读

### 1.2 内存对齐系数

在不同平台上的编译器都有自己默认的“对齐系数”，可以通过预编译命令`#pragram pack(n)`进行变更，`n`是代指“对齐系数”。

一般来讲，常用平台的对齐系数为：32 位中为 4，64 位中为 8。不同硬件平台占用的大小和对齐值都可能是不一样的，使用的时候需要按本机的实际情况考虑。

在 Go 语言中，进行内存对齐是基于如下的两组数据进行的：

**数据类型大小保证(size guarantee)**

| type                              | size in bytes             |
| --------------------------------- | ------------------------- |
| byte, uint8, int8                 | 1                         |
| uint16, int16                     | 2                         |
| uint32, int32, float32            | 4                         |
| uint64, int64, float64, complex64 | 8                         |
| complex128                        | 16                        |
| uint, int                         | 32-bit 为 4，64-bit 为 8   |
| uintptr                           | 尽可能大，以保证能够存储指针值 |
| struct{}, [0]T{}                  | 0                         |

**数据结构对齐保证(align guarantee)**

| type                     | alignment guarantee       |
| ------------------------ | ------------------------- |
| bool, byte, uint8, int8  | 1                         |
| uint16, int16            | 2                         |
| uint32, int32            | 4                         |
| float32, complex64       | 4                         |
| arrays                   | 由其元素(element)类型决定    |
| struct                   | 由其字段(field)类型决定      |
| other types              | 一个机器字(machine word)大小 |

### 1.3 对齐规则

在 Go 中可以调用`unsafe.Sizeof()`函数来返回相应类型的占用空间，使用`unsafe.Alignof`函数来查看相应类型的对齐系数。比如：

```go
package main

import (
  "fmt"
  "unsafe"
)

type User struct {
	Name string
	Age uint32
	Gender bool
}

func main() {
  var m map[string]string
  var p *int32
  var u User
  
  fmt.Println(unsafe.Sizeof(bool(true)), unsafe.Alignof(bool(true))) // 1 1
  fmt.Println(unsafe.Sizeof(int8(0)), unsafe.Alignof(int8(0)))       // 1 1
  fmt.Println(unsafe.Sizeof(int16(0)), unsafe.Alignof(int16(0)))     // 2 2
  fmt.Println(unsafe.Sizeof(int32(0)), unsafe.Alignof(int32(0)))     // 4 4
  fmt.Println(unsafe.Sizeof(int64(0)), unsafe.Alignof(int64(0)))     // 8 8
  fmt.Println(unsafe.Sizeof(float32(0)), unsafe.Alignof(float32(0))) // 4 4
  fmt.Println(unsafe.Sizeof(byte(0)), unsafe.Alignof(byte(0)))       // 1 1
  fmt.Println(unsafe.Sizeof("string"), unsafe.Alignof("string"))     // 16 8
  fmt.Println(unsafe.Sizeof(u), unsafe.Alignof(u))                   // 24 8
  fmt.Println(unsafe.Alignof(m))          // 8
  fmt.Println(unsafe.Alignof(p))          // 8
}
```

> string 类型占用空间大小是 16 字节，是因为其底层实现中是`reflect.StringHeader`结构体，包含一个数据指针和一个 int64 类型的数据长度，所以总共大小就是 16 字节。

通过观察可知，类型的对齐系数基本都是`2^n`，最小为 1，最大不会超过 8。

在 Go 中，对结构体的对齐，有如下规则：

* 结构体的第一个成员变量的偏移位置为 0，往后的每个成员变量的对齐值为**编译器默认对齐长度**（`#pragram pack(n)`）和**当前成员变量类型的长度**（`unsafe.Sizeof()`）中最小值，其偏移量必须为对齐值的整数倍。

* 结构体本身的对齐值为**编译器默认对齐长度**（`#pragram pack(n)`）和结构体的所有成员变量类型中的最大对齐值中的最大值。

结合这个规则可知，超过结构体内成员变量的类型最大长度时，默认对齐长度是没有任何意义的。

## 二、Go struct 内存对齐

Go struct 中的字段顺序不同时，内存占用也可能会相差较大，**合理的字段顺序可以减少内存的开销。**

### 2.1 调整顺序减少内存空间

比如，对于下面的两个结构体：

```go
type T1 struct {
  a int8
  b int64
  c int16
}

type T2 struct {
  a int8
  b int16
  b int64
}
```

在 64 位平台上，T1 占用 24 bytes，T2 占用 16 bytes；而在 32 位平台上，T1 占用 16 bytes，T2 占用 12 bytes 大小。

T1 在 64 位平台上的内存布局如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652780714062-f67172560d22.jpg)

T2 在 64 位平台上的内存布局如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652780739679-c3000b7883e4.jpg)

可以看到，因为 int16 的对齐系数为 2，所以当它前后的字段的大小不同时，内存填充的大小也是不同的。这就是 T1 和 T2 的大小不同的原因。

可以看出，通过适当的调整结构体内字段的顺序就能够达到缩小结构体占用大小效果，因为能够巧妙的减少填充(padding)的存在，使得各个字段之间更加“紧凑”了。

### 2.2 空结构体字段对齐

在 Go 中，如果结构或数字类型不包含大于 0 的字段（或元素），则其大小为 0。而两个不同的 0 大小的变量在内存中可能有相同的地址。

由于结构体`struct{}`的大小为 0，所以当一个结构体中包含空结构体类型的字段时，通常不需要进行内存对齐。

例如：

```go
type Demo1 struct {
  m struct{} // 0
  n int8     // 1
}

var d1 Demo1
fmt.Println(unsafe.Sizeof(d1)) // 1
```

可以看到，Demo1 类型的变量`d1`的内存大小为 1 字节，也就是只计算了其`n int8`字段的小，而`m struct{}`字段则不占空间。最终，Demo1 类型也就没有内存对齐要求。

但是当空结构体类型作为结构体的最后一个字段时，如果有指向该字段的指针，那么就会返回该结构体之外的地址。为了避免内存泄露，会额外进行一次内存对齐。

例如：

```go
type Demo2 struct {
  n int8     // 1
  m struct{} // 0
}

var d2 Demo2
fmt.Println(unsafe.Sizeof(d2)) // 2
```

Demo2 和 Demo1 结构体的字段和字段类型完全相同，但是 Demo2 的`m struct{}`字段在其末尾。如果不在`d2`的末尾添加一些空间，就可能会导致通过`d2.m`能够访问到不属于`d2`结构体的内存数据。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652877388320-24940e460352.jpg)

此时为了避免出现内存数据泄露，就需要为`d2`进行一次内存对齐。经过内存对齐后，`m struct{}`后面也会被添加 1 字节的对齐填充，所以`d2`的内存大小就变成 2 字节了。

## 三、内存不对齐的问题

在一些特殊情况，如果不考虑内存对齐，会引发错误或者造成性能下降。

### 3.1 原子操作的内存对齐要求

在 x86 平台上，进行 64 位的原子操作需要强制 8 字节内存对齐，否则会出现错误。

在 Go 的 `sync.atomic` 包中就有该问题。该 bug 可以参考 [atomic](https://godoc.org/sync/atomic#pkg-note-bug) 官方文档：

> BUG(rsc): On 386, the 64-bit functions use instructions unavailable before the Pentium MMX.
> 
> On non-Linux ARM, the 64-bit functions use instructions unavailable before the ARMv6k core. 
> 
> On ARM, x86-32, and 32-bit MIPS, it is the caller's responsibility to arrange for 64-bit alignment of 64-bit words accessed atomically. The first word in a variable or in an allocated struct, array, or slice can be relied upon to be 64-bit aligned.

下面这段代码演示了 Go 的原子操作因内存不对齐引发的 panic：

```go
package main

import "sync/atomic"

type T struct {
  b int64
  c int32
  d int64
}

func main() {
  a := T{}
  atomic.AddInt64(&a.d, 1)
}
```

编译为 64 bit 可执行文件时，运行正常；但是当编译为 32 bit 可执行文件，运行时就会出现 panic：

```shell
$ GOARCH=386 go build aligned.go
$
$ ./aligned
panic: runtime error: invalid memory address or nil pointer dereference
[signal SIGSEGV: segmentation violation code=0x1 addr=0x0 pc=0x8049f2c]

goroutine 1 [running]:
runtime/internal/atomic.Xadd64(0x941218c, 0x1, 0x0, 0x809a4c0, 0x944e070)
	/usr/local/go/src/runtime/internal/atomic/asm_386.s:105 +0xc
main.main()
	/root/gofourge/src/lab/archive/aligned.go:18 +0x42
```

这是因为，T 类型在 64 bit 平台上是 8 字节对齐，在编译的时候会自动进行字段对齐填充，内存布局如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652779183330-ecae27d7c5b1.jpg)

而 T 类型在 32 bit 平台上是 4 字节对齐，在编译的时候不需要进行填充来对齐字段，内存布局如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652779224860-57d4b3758925.jpg)

此时因为`T.d`字段未能实现 8 字节内存对齐，就导致出现了 32 bit 平台上原子操作 64 bit 指针出现 panic。

为了解决这个问题，就需要手动进行填充对齐（也可以调整字段顺序），让其“看起来”像是 8 字节对齐的：

```go
type T struct {
  b int64
  c int32
  _ int32
  d int64
}
```

这时，在 32 bit 平台上，T3 的内存布局就变成了如下样式：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652779395231-c14c52ea8730.jpg)

一些知名的项目也有这种处理，比如 [groupcache](https://github.com/golang/groupcache/blob/869f871628b6baa9cfbc11732cdf6546b17c1298/groupcache.go#L169-L172)：

```go
type Group struct {
  _ int32 // force Stats to be 8-byte aligned on 32-bit platforms
  
  // Stats are statistics on the group.
  Stats Stats
}
```

### 3.2 伪共享 False Sharing

在一些需要防止 Cache Line 伪共享的时候，也需要进行特殊的字段对齐。

> 具体可以参考 [伪共享(False Sharing)](http://ifeve.com/falsesharing/)。

缓存系统中是以 CPU 缓存行为单位存储的。缓存行是 2 的整数幂个连续字节，一般为 32~256 个字节，常见为 64 个字节。如果多个变量共享同一个缓存行，就会无意中影响彼此的性能，这就是**False Sharing（伪共享）**。

缓存行上的写竞争是运行在 SMP 系统中并行线程实现可伸缩性最重要的限制因素。为了让可伸缩性与线程数呈线性关系，就必须确保不会有两个线程往同一个变量或缓存行中写入。下图说明了伪共享的问题：

![](http://cnd.qiniu.lin07ux.cn/markdown/1652943690626-5fb5587ee502.jpg)

在核心 1 上运行的线程想要更新变量 X，同时核心 2 上的线程想要更新变量 Y。不幸的是，这两个变量在同一个缓存行中。每个线程都要去竞争缓存行的所有权来更新变量。如果核心 1 获得了所有权并执行更新操作，缓存子系统会使核心 2 中对应的缓存行失效；如果核心 2 获得了所有权并执行更新操作，就会使核心 1 中对应的缓存行失效。这会来来回回的经过 L3 缓存，大大影响了性能。如果互相竞争的核心位于不同的插槽，就要额外横跨插槽连接，问题可能更加严重。

例如，在`sync.Pool`中就有这种设计：

```go
type poolLocal struct {
  poolLocalInternal
  
  // Prevents false sharing on widespread platforms with
  // 128 mod (cache line size) = 0
  pad [128 - unsafe.Sizeof(poolLocalInternal{})%128]byte
}
```

poolLocal 结构体中的 pad 字段就是为了防止出现伪共享而设计的。如注释中所说：这里之所以使用 128 字节进行内存对齐，是为了兼容更多的平台。

### 3.3 频繁执行指令 Hot Path

Hot Path 是指执行非常频繁的指令序列。

在访问结构体的第一个字段的时候，可以直接使用结构体的指针来访问（因为结构体变量的内存地址就是其第一个字段的内存地址）。而访问结构体的其他字段时，则需要在结构体指针的基础上进行一次偏移计算。在机器码中，偏移量是随指令传递的附加值。

因此，相对其他字段来说，访问结构体的第一个字段的机器码更紧凑，速度更快。所以，**通常将常用字段放在结构体的第一个位置上，以减少 CPU 要执行的指令数量，从而达到更快的访问效果**。

下面是`sync.Once`中的使用示例：

```go
// src/sync/once.go

// Once is an object that will perform exactly one action.
//
// A Once must not be copied after first use.
type Once struct {
  // done indicates whether the action has been performed.
  // It is first in the struct because it is used in the hot path.
  // The hot path is inlined at every call site.
  // Placing done first allows more compact instructions on some architectures(amd64/386),
  // and fewer instructions (to calculate offset) on other architectures.
  done int32
  m    Mutex
}
```

## 四、内存对齐工具

在实际编码的时候，多数情况下都不会考虑到最优的内存对齐。但是可以通过一些工具来检测当前的内存布局是否是最优的。

### 4.1 golang-sizeof.tips 网站

[golang-sizeof.tips](http://ww1.golang-sizeof.tips/) 这个网站可以可视化 struct 的内存布局，但确定是只支持 8 字节对齐。

