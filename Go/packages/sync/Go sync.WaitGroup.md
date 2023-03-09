**Go 标准库提供的同步原语中，锁和原子操作注重控制 goroutine 之间的数据安全，WaitGroup、Channel 和 Context 控制的是它们的并发行为。**

### 1. 基本使用

WaitGroup 是 sync 包下的内容，用于控制协程间的同步。WaitGroup 使用场景同其名字的含义一样，当需要等待一组协程都执行完成以后才能做后续的处理时，就可以考虑使用它。

`sync.WaitGroup`的使用方式很简单，它提供了三个方法：

* `func (wg *WaitGroup) Add(delta int)` 增加 WaitGroup 中的子任务的计数；
* `func (wg *WaitGroup) Done()` 当子任务完成时，将 WaitGroup 的计数值减 1；
* `func (wg *WaitGroup) Wait()` 阻塞调用此方法的 goroutine，直到 WaitGroup 的计数值变为 0。

下面是一个基础的使用示例：

```go
package main

import (
  "fmt"
  "sync"
)

func main() {
  var wg sync.WaitGroup
  
  wa.Add(2) // worker number is 2
  
  go func() {
    // worker 1 do something
    fmt.Println("groutine 1 done!")
    wg.Done()
  }()
  
  go func() {
    // worker 2 do something
    fmt.Println("goroutine 2 done!")
    wg.Done()
  }()
  
  wg.Wait() // wait all worker done
  fmt.Println("all worker done!")
}
```

运行时，worker 1 和 2 的输出不一定，但是它们的输出都会在 main 的输出之前：

```text
groutine 1 done!
goroutine 2 done!
all worker done!
```

### 2. 前置知识

`sync.WaitGroup`的核心源码不到 100 行，十分精炼，但是包含了很多知识。在真正理解 WaitGroup 的实现之前需要了解如下的知识：

**信号量**

信号量是一种保护共享资源的机制，用于解决多线程同步问题。信号量`s`是具有非负整数值的全局变量，只能由两种特殊的操作来处理，这两种操作被称为`P`和`V`：

* `P(s)` 如果`s`是非零的，那么`P`将`s`减 1 后立即返回。如果`s`为零，那么就挂起这个线程，直到`s`变为非零时被唤醒，然后再执行将`s`减 1 的操作后返回；
* `V(s)` 将`s`加 1。如果有任何线程阻塞在`P`操作中等待`s`变为非 0，那么`V`操作会唤醒这些线程中的一个，让其继续执行`P`操作。

在 Go 的底层信号量函数中：

* `runtime_Semacquire(s *uint32)` 会阻塞 goroutine 直到信号量`s`的值大于 0，然后原子性的将信号量减 1，即`P`操作；
* `runtime_Semrelease(s *uint32, lifo bool, skipframes int)` 原子性的增加信号量的值，然后通知被`runtime_Semacquire()`函数阻塞的 goroutine，即`V`操作。

> 这两个信号量函数不止在 WaitGroup 中会用到，Go 在设计互斥锁的时候也少不了信号量的参与。

**内存对齐**

CPU 对内存的读写并不是一个字节一个字节的进行，而是一块一块的。因此，在类型的值在内存中对齐的情况下，计算机的加载或者写入会很高效。

在聚合类型（结构体或数组）的内存所占长度或许比它元素所占内存之和更大。这是因为编译器会添加未使用的内存地址，以确保连续的成员或元素相对于结构体或数组的起始地址是对其的。

**原子操作 CAS**

CAS 是原子操作的一种，可用于在多线程编程中实现不被打断的数据交换操作，从而**避免多线程同时修改某一数据时由于执行顺序的不确定性以及中断的不可预知性产生的数据不一致的问题**。该操作通过将内存中的值与指定数据进行比较，当数值一样时将内存中的数据替换为新的值。

**移位运算 >> 和 <<**

灵活的位运算能够让一个普通的数字变化出丰富的含义。如下是两种简单的移位运算：

* `<<` 左移位运算，按二进制形式将所有的位向左移动对应的位数，高位舍去，低位补零。在数字没有溢出的前提下，左移 n 位相当于乘以 2 的 n 次幂。
* `>>` 右移位运算，按二进制形式将所有的位向右移动对应的位数，低位移除，高位补符号位。右移相当于是对 2 取商，舍去余数。右移 n 位相当于除以 2 的 n 次幂。

**指针、unsafe.Pointer 和 uintptr**

Go 中的指针可以分为三类：

* `*T` 普通的指针类型，用于传递对象地址，不能进行指针计算；
* `unsafe.Pointer` 通用型指针，任何一个普通类型的指针`*T`都可以转换为`unsafe.Pointer`指针，而且`unsafe.Pointeter`指针还可以转换回普通指针，并且可以不用和原来的指针类型`*T`相同。`unsafe.Pointer`不能进行指针计算，不能读取内存中的值（必须转换为具体累心的普通指针才行）；
* `uintptr` 准确来讲，`uintptr`并不是指针，而是一个大小并不明确的无符号整型。`unsafe.Pointer`类型可以与`uintptr`相互转换。由于`uintptr`类型保存了指针所指向的地址数值，因此可以通过该数值进行指针运算。GC 时，不会将`uintptr`当做指针，其目标会被回收。

`unsafe.Pointer`是一个桥梁，可以让任意类型的普通指针实现相互转换，也可以将任意类型的指针转换为`uintptr`进行指针运算，如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/65859cd4dbc129edba9d263102fff472.jpg)

### 3. 源码实现

`sync.WaitGroup`的源码位置为`$GO_ROOT/src/sync/waitgroup.go`，本文基于 Go 1.15.7 版本源码：

> Go 后续版本中源码已经发生了变化，最重要的`sync.WaitGroup`结构的字段已经完成了拆分。

**sync.WaitGroup 结构体**

`sync.WaitGroup`的结构体中包含一个`noCopy`的辅助字段和一个具有复合意义的`state1`字段，定义如下：

```go
type WaitGroup struct {
  noCopy noCopy
  // 64-bit value: high 32 bits are counter, low 32 bits are waiter count.
  // 64-bit atomic operations require 64-bit alignment, but 32-bit
  // compilers do not ensure it. So we allocate 12 bytes and then use
  // the aligned 8 bytes in them as state, and the other 4 as storage
  // for the sema.
  state1 [3]uint32
}

// state returns pointers to the state and sema fields stored within wg.state1.
func (wg *WaitGroup) state() (statep *uint64, semap *uint32) {
  // 64 位编译器地址必然能被 8 整除，由此可以通过这个来判断是否为 64 位对齐
  if uintptr(unsafe.Pointer(&wg.state1))%8 == 0 {
    return (*uint64)(unsafe.Pointer(&wg.state1)), &wg.state1[2]
  } else {
    return (*uint64)(unsafe.Pointer(&wg.state1[1]), &wg.state1[0]
  }
}
```

其中，`noCopy`字段是一个空结构体，不会占用内存，编译器也不会对其进行字节填充。主要是为了通过 go vet 工具来做静态检查，防止开发者在使用`sync.WaitGroup`过程中对其进行了复制，从而导致的安全隐患。

`state1`字段是一个长度为 3 的`uint32`数组，总长度为 12 字节，用于表示三部分内容：通过`Add()`设置的子 goroutine 的计数值 counter；通过`Wait()`陷入阻塞的 waiter 数；信号量 semap。

由于需要对 counter 和 waiter 两者进行同时操作，需要保证原子性。而这两者都是 uint32 类型的数据，所以就是需要对一个 uint64 类型的数据进行原子操作。这需要数据是 64 位对齐的，但是 32 位的编译器并不能保证这一点。因此，在 64 位和 32 位的环境下，`state1`字段的组成含义是不相同的：

![](https://cnd.qiniu.lin07ux.cn/markdown/a7f69bce588460af416c522776f2b982.jpg)

需要注意的是：不论是在 32 位还是 64 位环境中，`state1`字 counter 值都是在 waiter 值前面的。

需要注意的是，当初始化一个 WaitGroup 对象时，其 counter、waiter、semap 的值均为 0。

**Add()**

`Add()`函数的入参是一个整型数，可正可负，用来实现对 counter 数值的修改。如果 counter 数值变为 0，那么所有阻塞在`Wait()`函数的 waiter 将会被唤醒；如果 counter 数值为负，将引起 panic。

下面是去掉静态检测相关代码后的`Add()`源码：

```go
func (wg *WaitGroup) Add(delta int) {
  // 获取包含 counter 和 waiter 的复合状态 statep，以及信号量值 semap
  statep, semap := wg.state()
  state := atomic.AddUint64(statep, uint64(delta)<<32) // statep 高 32 位表示 counter
  v := int32(state >> 32) // counter
  w := uint32(state) // waiter
  
  // counter 的值不能为负
  if v < 0 {
    panic("sync: negative WaitGroup counter")
  }
  
  // 有等待者说明之前 counter 必然不为 0，但是 counter 增加后的值和 delta 值相等，说明操作时 counter 值为 0
  // 这意味着发生了 Add 和 Wait 并发使用的情况
  if w != 0 && delta > 0 && v == int32(delta) {
    panic("sync: WaitGroup misuse: Add called concurrently with Wait")
  }
  
  // counter 值大于 0 说明还不能释放信号量，没有等待者说明无需释放信号量，所以此时不需要做其他处理
  if v > 0 || w == 0 {
    return
  }
  
  // 当前的 statep 的值和 statep 自增之后的值不相等，则说明发生了并发调用情况
  if *statep != state {
    panic("sync: WaitGroup misuse: Add called concurrently wit Wait")
  }
  
  // 执行到这里说明此时：counter = 0, waiter > 0, delta < 0
  // 这表示所有的子 goroutine 都已经完成了任务，因此需要将其复合状态 statep 归 0
  // 并释放掉 waiter 数量的信号量，以唤醒在等待该信号量的 goroutine
  *statep = 0
  for ; w != 0; w-- {
    // 释放信号量，执行一次就唤醒一个阻塞的 waiter
    runtime_Semarelease(semap, false, 0)
  }
}
```

其代码很精简，主要逻辑就是得到复合状态 statep 和信号量 semap 之后，完成对 counter 的变更。如果 counter 归 0 了，则要释放信号量以唤醒其他 waiter。

处理的核心是保证 counter 和 waiter 数值一起进行操作。假设当前 counter = 2，waiter = 1，调用`Add(1)`时，counter 和 waiter 的变化如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/5053e2156ef79b2d28227786fa4958b5.jpg)

**Done()**

`Done()`方法很简单，就是调用`Add(-1)`，完成 counter 数量的减 1。一般在子 goroutine 任务完成之后就应该调用`Done()`方法。

```go
func (wg *WaitGroup) Done() {
  wg.Add(-1)
}
```

**Wait()**

如果 WaitGroup 中的 counter 的值大于 0，那么执行`Wait()`方法的 goroutine 就会被阻塞住，直到 counter 值变为 0。同时`Wait()`方法也会将 waiter 数量增加 1。

去掉静态检测相关代码后，`Wait()`方法的源码如下：

```go
func (wg *WaitGroup) Wait() {
  statep, semap := wg.state()
  for {
    state := atomic.LoadUint64(statep) // 原子读取复合状态 statep
    v := int32(state >> 32)            // 获取 counter 值
    w := uint32(state)                 // 获取 waiter 值
    
    // 如果 v == 0 则说明没有待执行任务的子 goroutine 了，无需等待，直接返回即可
    if v == 0 {
      return
    }
    
    // 执行 CAS 原子操作将 waiter 的值加 1，操作成功则进入阻塞等待，否则进行下一轮循环
    if atomic.CompareAndSwapUint64(statep, state, state+1) {
      // 等待信号量被释放，也就是等待其他 goroutine 调用`Done/Add`方法后将 counter 的值变为 0 后唤醒自己
      runtime_Semacquire(semap)
      
      // 在唤醒之后，要确保复合状态的值为 0，也就是 counter 和 waiter 都为 0，否则表示错误的复用
      // 因为在 Add 方法中，在释放信号量之前，已经通过 *state = 0 对复合状态做了重置了
      if *statep != 0 {
        panic("sync: WaitGroup is reused before previous Wait has returned")
      }
      return
    }
  }
}
```

### 4. 总结

从上面可以看到，`sync.WaitGroup`的实现思路是比较简单的，通过结构体字段维护了两个计数器和一个信号量：

* `Add()/Done()` 对 counter 计数器进行增减，表明新增 n 个 goroutine 执行任务，或者 n 个 goroutine 完成了任务；
* `Wait()` 用来增加 waiter 计数器，如果 counter 不为 0，则会时调用方陷入等待状态；
* 信号量则用于阻塞和唤醒阻塞的 goroutine。

在使用`sync.WaitGroup`的时候，需要注意如下几点：

* 通过`Add()`函数添加的 counter 数量一定要与后续通过`Done()`或直接调用`Add()`方法减去的数值一致。如果前者大，那么阻塞在`Wait()`的 goroutine 将永远得不到唤醒；如果后者大，将会引发 panic；
* `Add()`的增量变化应该最先执行；
* 不要对 WaitGroup 对象进行复制使用；
* 如果要复用 WaitGroup 则必须在所有先前的`Wait()`调用返回之后再进行新的`Add()`调用。

### 5. 更新

`sync.WaitGroup`的结构在后续发生了几次更新，将其中的`state1 [3]uint32`字段进行了拆分，并逐步替换为一个 uint32 和一个`atomic.Uint64`类型的字段，其性能上略有提升。

**拆分 state1 字段**

首先是对 state1 字段的拆分，其原理和原先的`state1 [3]uint32`类似，也是通过运行时判断是否 64 位对齐来区分复合状态和信号量。

对应的提交历史：[sync: avoid a dynamic check in WaitGroup on 64-bit architectures](https://github.com/golang/go/commit/ad7db1f90fb66f00f5b020360aabd9f27d1c764f)

相关代码：

```go
type WaitGroup struct {
  noCopy noCopy
  
  // 64-bit value: high 32 bits are counter, low 32 bits are waiter count.
  // 64-bit atomic operations require 64-bit alignment, but 32-bit
  // compilers only guarantee that 64-bit fields are 32-bit aligned.
  // For this reason on 32 bit architectures we need to check in state()
  // if state1 is aligned or not, add dynamically "swap" the field order if
  // needed.
  state1 uint64
  state2 uint32
}

func (wg *WaitGroup) state() (statep *uint64, semap *uint32) {
  if unsafe.Alignof(wg.state1) == 8 || uintptr(unsafe.Pointer(&wg.state1))%8 == 0 {
    // state1 is 64-bit aligned: nothing to do.
    return &wg.state1, &wg.state2
  } else {
    // state1 is 32-bit aligned but not 64-bit aligned: this means that
    // (&sstate1)+4 is 64-bit aligned.
    state := (*[3]uint32)(unsafe.Pointer(&wg.state1))
    return (*uint64)(unsafe.Pointer(&state[1])), &state[0]
  }
}
```

由于 Go 结构体的字段会按照顺序分布在内存中，而且在不需要填充的情况下（已对齐），各个字段之间是紧挨着的。所以这次修改将 state1 字段拆分为两个字段，依旧占用连续的 12 个字节，所以在 32 位的情况下，依旧和之前的拆分一样。但是在 64 位的情况下，就可以直接使用，而不需要进行转换了。其性能略微提升也是来源于此。

**改用 atomic.Uint64 类型**

在拆分字段之后，又出现一个提交，使用`atomic.Uint64`类型替代了`uint64`类型，这次修改借助`aotmic.Uint64`类型的 64 位对齐保证，可以避免使用`state()`方法进行状态拆分重组的操作，性能上和前者没有变化。

对应的提交历史：[sync: use atomic.Uint64 for WaitGroup state](https://github.com/golang/go/commit/ee833ed72e8ccfdd2193b0e6c0223ee8eb99b380)

相关代码：

```go
type WaitGroup struct {
  noCopy noCopy
  
  state atomic.Uint64 // high 32 bits are counter, low 32 bits are waiter count.
  sema  uint32
}
```

通过使用`atomic.Uint64`替换`uint64`类型，就可以不用再实现`state`方法了，在`Add/Done/Wait`方法中可以直接使用`state`和`sema`字段了。

而`atomic.Uint64`类型是一个独特的 struct，其中有一个`atomic.align64`类型的字段，借助该字段实现了 64 位对齐保证：

```go
// An Uint64 is an atomic uint64. The zero value is zero.
type Uint64 struct {
	_ noCopy
	_ align64
	v uint64
}
```

而`atomic.align64`类型的 64 位对齐保证是通过编译器自动填充实现的。在进行编译的时候，编译器会自动对该类型所在的位置进行自动处理，使其保证无论在什么环境下都是 64 位对齐的：

```go
// align64 may be added to structs that must be 64-bit aligned.
// This struct is recognized by a special case in the compiler
// and will not work if copied to any other package.
type align64 struct{}
```