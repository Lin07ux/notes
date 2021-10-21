> 转摘：[Go 五种原子性操作的用法详解](https://mp.weixin.qq.com/s/gLmOz7F_2t5IYtdus5xBRQ)

原子性：

> 一个或多个操作在 CPU 执行的过程中不被中断的特性，称为**原子性(Atomicity)**。这些操作对外表现成一个不可分割的整体，他们要么都被执行，要么都不执行，外界不会看到他们只执行到一半的状态。

CPU 执行一系列的操作时不可能不发生中断，但是如果在执行多个操作时，能让他们的中间状态对外界不可见，那就可以宣称他们拥有了“不可分割”的原子性。类似的解释在数据库事务的 ACID 概念里也有。

### 1. Go 中的原子操作

Go 标准包`sync/atomic`提供了对原子操作的支持，其提供的原子操作有如下几大类：

* **递增**：保证对操作数进行原子的增加，操作方法名格式为`AddXXXType`。

* **载入**：保证读取到操作数前没有其他任务对它进行变更，操作方法名格式为`LoadXXXType`。

* **存储**：保证其他操作不会读取到修改变量过程中的脏数据，操作方法名格式为`StoreXXXType`。

* **比较并交换**：也就是`CAS`(Compare And Swap)，保证更新值前被操作数的值未发生变更，操作方法名格式为`CompareAndSwapXXXType`。Go 的很多并发原语实现就是依赖的 CAS 操作，同样是支持上面列的那些类型。

* **交换**：不比价直接交换，较少使用。

这些操作中的`XXXType`表示其支持的类型。每种操作都支持`int32`、`int64`、`uint32`、`uint64`、`uintptr`的类型，而除了*递增*操作，另外四种操作都还支持`pointer`类型（也就是支持载入任何类型的指针）。

### 2. 互斥锁 与 原子操作 的区别

Go 标准包`sync`中的 Mutex 是经常用来表征并发安全的，其与原子操作在使用目的和底层实现上都不一样：

* 使用目的：互斥锁是用来保护一段逻辑，原子操作用于对一个变量的更新保护。
* 底层实现：Mutex 由操作系统的调度器实现，而 atomic 包中的原子操作则由底层硬件指令提供支持，这些指令在执行的过程中是不允许终端的，因此原子操作可以在 lock-free 的情况下保证并发安全，并且它的性能也能做到随 CPU 个数的增多而线性扩展。

**对于一个变量更新的保护，原子操作通常会更有效率，并且更能利用计算机多核的优势**。

比如下面的代码，使用互斥锁的并发计数器程序：

```go
func mutexAdd() {
  var a int32 = 0
  var wg sync.WaitGroup
  var mu sync.Mutex
  
  start := time.Now()
  for i := 0; i < 10000000; i++ {
    wg.Add(1)
    go func() {
      defer wg.Done()
      mu.Lock()
      a += 1
      mu.Unlock()
    }()
  }
  
  wg.Wait()
  timeSpends := time.Now().Sub(start).Nanoseconds()
  fmt.Printf("use mutex a is %d, spend time: %v\n", a, timeSpends)
}
```

将这里使用 Mutex 加锁后增加变量值的代码，改成使用 atomic 的增加方法，在不加锁的情况仍然能够确保对变量递增的并发安全：

```go
func atomicAdd() {
  var a int32 = 0
  var wg sync.WaitGroup
  
  start := time.Now()
  for i := 0; i < 10000000; i++ {
    wg.Add(1)
    go func() {
      defer wg.Done()
      atomic.AddInt32(&a, 1)
    }()
  }
  
  wg.Wait()
  timeSpends := time.Now().Sub(start).Nanoseconds()
  fmt.Printf("use atomic a is %d, spend time: %v\n", atomic.LoadInt32(&a), timeSpends)
}
```

这两个函数执行的结果都是 10000000，都是线程安全的。

需要注意的是：**所有原子操作方法的备操作数形参都必须是指针类型。**通过指针变量可以获取被操作数在内存中的地址，从而施加特殊的 CPU 指令，确保同一时间只有一个 goroutine 能够进行操作。

### 3. CAS

Go `sync/atomic`包中的 CAS 操作会在更新被操作数之前，先检查被操作数的是否未发生变更，未变更时才会进行更新。返回结果表示是否完成了变更交换：

```go
func CampareAndSwapInt32(addr *int32, old, new int32) (swapped bool)

func CompareAndSwapPointer(addr *unsafe.Pointer, old, new unsafe.Pointer) (swapped bool)
```

当有大量的 goroutine 对变量进行读写操作时，可能会导致 CAS 操作无法成功，这时可以利用`for`循环进行多次尝试。

其实 Mutex 的底层实现也是依赖原子操作中的 CAS 实现的。一下是`sync.Mutex`中`Lock`方法相关的部分实现代码：

```go
type Mutex struct {
  state int32
  sema  uint32
}

func (m *Mutex) Lock() {
  // Fast path: grab unlocked mutex.
  if atomic.CompareAndSwapInt32(&m.state, 0, mutexLocked) {
    if race.Enabled {
      race.Acquire(unsafe.Pointer(m))
    }
    return
  }
  // Slow path: outlined so that the fast path can be inlined
  m.lockSlow()
}
```

为了方便理解，可以将`Mutex.state`作为状态，值为 0 或 1：0 表示锁目前空闲，1 表示已被加锁。在`Lock`方法的实现中，使用了`atomic.CompareAndSwapInt32(&m.state, 0, mutexLocked)`来进行 CAS 操作，操作成功则表示加锁成功，否则表示加锁失败。

### 4. atomic.Value

如果想要并发安全的设置一个结构体的多个字段，除了把结构体转换为指针，通过`StorePointer`一类的函数摄之外，还可以将数据转成`atomic.Value`结构体，它在底层完成了从具体指针类型到`unsafe.Pointer`之间的转换，并实现对任意类型数据的原子性读写操作。

> `unsafe.Pointer`提供了绕过 Go 语言指针类型限制的方法，`unsafe`并不是指不安全，而是说官方不保证向后兼容。

`atomic.Value`类型对外暴露了两个方法：

* `v.Store(c)` 写操作，将原始的变量`c`并发安全的存放到一个`atomic.Value`类型的`v`变量中。
* `c := v.Load()` 读操作，从`atomic.Value`类型的`v`变量中并发安全的读取数据赋值给变量`c`。

> 1.17 版本还增加了`Swap`和`CompareAndSwap`方法。
> 
> 由于`Load()`方法返回的是一个`interface{}`类型，所以在使用前记得要先转换成具体类型的值再使用。

示例如下：

```go
type Rectangle struct {
  length int
  width  int
}

var rect atomic.Value

func update(width, length int) {
  rectLocal := Rectangle{
    length: length,
    width:  width,
  }
  rect.Store(rectLocal)
}

func main() {
  wg := sync.WaitGroup{}
  wg.Add(10)
  
  // 10 个协程并发更新
  for i := 0; i < 10; i++ {
   go func() {
     defer wg.Donw()
     update(i, i + 5)
   }()
  }
  
  wg.Wait()
  _r := rect.Load().(*Rectangle)
  fmt.Printf("rect.width=%d\nrect.length=%d\n", _r.width, _r.length)
}
```

### 5. 总结

原子操作是由**底层硬件**支持，而锁是由**操作系统的调度器**实现。

锁应当用来保护一段逻辑，而对于一个变量更新的保护，原子操作通常会更有效率，并且功能能利用计算机多核的优势。

如果要更新的是一个复合对象，则应当使用`atomic.Value`类型封装好的实现。


