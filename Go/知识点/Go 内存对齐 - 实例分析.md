> 转摘：[再谈Golang内存对齐](https://mp.weixin.qq.com/s/VllZHAcZh2zTjHyjd-ixVA)

### 1. 实例

为了规避在 32 位环境下 atomic 操作 64 位数的 BUG，在`groupcache`和`sync.WaitGroup`中有不同的内存对齐处理方式。

在`groupcache`中，是使用明确的空占位符来进行 8 位对齐的：

```go
// groupcache
type Group struct {
  name string
  getter Getter
  peersOnce sync.Once
  peers PeerPicker
  cacheBytes int64
  mainCache cache
  hotCache cache
  loadGroup flightGroup
  _ int32 // force Stats to be 8-byte aligned on 32-bit platforms
  Stats Stats
}
```

在`sync.WaitGroup`中则是使用长度为 3 的 uint32 数组来进行运行时判断对齐的：

```go
// sync.WaitGroup
type WaitGroup struct {
  noCopy noCopy
  
  // 64-bit value: hight 32 bits are counter, low 32 bits are waiter count.
  // 64-bit atomic operations require 64-bit alignment, but 32-bit
  // compilers do not ensure it. So we allocate 12 bytes and then use
  // the aligned 8 bytes in them as state, and the other 4 as storage
  // for the sema.
  state1 [3]uint32
}

func (wg *WaitGroup) state() (statep *uint64, semap *uint32) {
  if uintptr(unsafe.Pointer(&wg.state1))%8 == 0 {
    return (*uint64)(unsafe.Pointer(&wg.state1)), &wg.state1[2]
  } else {
    return (*uint64)(unsafe.Pointer(&wg.state1[1])), &wg.state1[0]
  }
}
```

### 2. 为什么 groupcache 内部对齐即可实现 64 位对齐？

在 [atomic](https://pkg.go.dev/sync/atomic) 文档的最后，关于 64 位对齐有如下相关描述：

> On ARM, 386, and 32-bit MIPS, it is the caller's responsibility to arrange for 64-bit alignment of 64-bit words accessed atomically. The fist word in a variable or in an allocated struct, array, or slice can be relied upon to be 64-bit aligned.

最后一句话：**变量、结构体、数组、切片的第一个字是 64 位对齐的。**也就是说，这几种情况是不需要考虑 64 位对齐的。

可以通过下面的代码来验证：

```go
package main

import (
  "fmt"
  "time"
  "unsafe"
)

type foo struct {
  bar int64
}

func main() {
  for range time.Tick(time.Second) {
    f := &foo{}
    p := uintptr(unsafe.Pointer(f))
    fmt.Printf("%p: %d, %d\n", f, p, p%8)
  }
}
```

指定编译为 386 平台（32 位环境）上运行：

```shell
GOARCH=386 go run main.go
```

> MacOS 上在 Go 1.15 之后就不支持编译 32 位环境程序了：
> 
> As announced in the Go 1.14 release notes, Go 1.15 drops support for 32-bit binaries on macOS, iOS, iPadOS, watchOS, and tvOS (the darwin/386 and darwin/arm ports). Go continues to support the 64-bit darwin/amd64 and darwin/arm64 ports.

在 32 位环境中，struct 的地址必然会能满足 32 位对齐，也就是 4 的倍数。而通过上述代码的执行结果可以发现，当 struct 里含有 int64 类型的数据时，struct 的地址也会自动的满足 64 位对齐了，也就是 8 的倍数。

因为 struct 的外部已经自动做了 64 位对齐了，所以只要内部进行对齐就可以确保字段是 64 位对齐的。

### 3. 为什么 sync.WaitGroup 不像 groupcache 那样进行内部对齐？

`sync.WaitGroup`和`groupcache`之所以采用不同的 64 位对齐实现方式，是因为两者的使用场景不同：

* 在实际使用的时候，`sync.WaitGroup`可能会被嵌入到别的 struct 中。由于无法知道嵌入的具体位置，那么嵌入的地方可能是 32 位对齐的，也可能是 64 位对齐的，所以不可能通过预先加入 padding 的方式来实现 64 位对齐，只能在运行时动态计算。

* 而`groupcache`则一般不会被嵌入到别的 struct 中。当然，如果非要嵌入，那么就有可能会出现问题。

比如，下面的代码中，将 groupcache 嵌入到一个 struct 中，并放在 int32 类型的字段后面：

```go
package main

import "github.com/golang/groupcache"

type foo struct {
  bar int32
  g   groupcache.Group
}

func main() {
  f := foo{}
  f.g.Stats.Gets.Add(1)
}
```

在 64 位环境中，这段代码运行正常，但是当编译为 32 位环境的程序时，就会得到如下的 panic 信息：

```
panic: unaligned 64-bit atomic operation
```

这是由于，在 32 环境中，字段`bar`是 4 位的，这就导致`g`字段的偏移量为 4 而不是 8。如此一来，虽然 groupcache 内部通过`_ int32`实现了相对的 64 位对齐，但是因为外部没有实现 64 位对齐，所以在执行 atomic 操作的时候，还是会出现 panic。

### 4. 为什么 sync.WaitGroup 中的 state1 不换成一个 int64 和一个 int32 的字段？

`sync.WaitGroup`中的`state1`字段是一个长度为 3 的 uint32 数组，它会保存两个数据：`statep`和`semap`。这两个数据一个是 int64 类型的，另一个是 int32 类型的。

为什么不把`state1`字段用两个独立的字段来代替呢？

虽然是可以换成一个 int64 和一个 int32 的字段，但是因为`sync.WaitGroup`可能会被嵌入到别的 struct 中，而且无法确定嵌入的位置，就会造成替换之后，int64 类型的字段可能没有 64 位对齐，从而会影响 atomic 操作。

所以，使用一个长度为 3 的 uint32 的数组来合并两个字段，并在运行时动态计算这两个字段的顺序，以保证 int64 的 statep 是 64 位对齐的。

### 5. 为什么 sync.WaitGroup 中的 state1 不换成 [12]byte 类型？

`sync.WaitGroup`中的`state1`字段的 3 个 uint32 元素中，根据运行时的环境，取前两个或者后两个元素作为 int64 类型的 statep 数据，剩下的一个元素作为 int32 的 semap 数据。

虽然`[12]byte`和`[3]uint32`占用的总空间是一样的，而且也能转换成 int64 和 int32 类型的数据，但是这会保证其内存对齐至少 4 字节。

Go 中关于内存对齐的保证有如下的描述：

* For a variable x of any type: unsafe.Alignof(x) is at least 1.
* For a variable x of struct type: unsafe.Alignof(x) is the largest of all the values unsafe.Alignof(x.f) for each field f of x, but at least 1.
* For a variable x of array type: unsafe.Alignof(x) is the same as the alignment of a variable of the array's element type.

其中的重点是：**对 struct 而言，它的对齐取决于其中所有字段对齐的最大值；对于 array 而言，它的对齐等于元素类型本身的对齐**。

因为`noCopy`的大小是 0，所以`sync.WaitGroup`的对齐值实际上就取决于`state1`字段的对齐：

* 当`state1`的类型是`[3]uint32`的时候，`sync.WaitGroup`的对齐就是 4；
* 当`state1`的类型是`[12]byte`的时候，`sync.WaitGroup`的对齐就是 1。

所以，当将`state1`字段的类型换成`[12]byte`的时候，`sync.WaitGroup`的地址将不再是 4 的倍数了，此时使用`uintptr(unsafe.Pointer(&wg.state1))%8 == 0`来判断是否是 64 位对齐也就没有意义了。


