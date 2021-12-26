> 转摘：[Go：Recover 那些事](https://mp.weixin.qq.com/s/y6bLqjevvqlP3AEjTaztYw)

当程序无法适当处理错误时，比如无效的内存访问，Go 中的 panic 就会被触发。如果错误是意料之外、且没有其他方式处理该错误时，同样可以由开发者触发 panic。

Go 中的 panic 可以由 recover 来恢复，了解 recover 或者终止的过程，可以更好的理解一个会发生 painic 的程序的后果。

### 1. 多帧示例

关于 panic 以及它 recover 函数的经典例子（Go blog [Defer, Panic, and Recover](https://blog.golang.org/defer-panic-and-recover)）已经有着充分的说明。下面使用一个 panic 涉及多个 defer 函数帧的例子进行说明：

```go
func main() {
  defer println("defer 1")
  
  level1()
}

func level1() {
  defer println("defer func 3")
  defer func() {
    if err := recover(); err != nil {
      println("recovering in progress...")
    }
  }()
  defer println("defer 2")
  
  level2()
}

func level2() {
  defer println("defer func 4")
  panic("foo")
}
```

该程序由三个链式调用的函数组成，一旦这段代码到了最后层级产生 panic 的地方，Go 会构建 defer 函数的第一个帧（也就是`level2`方法中的 defer），并运行它。

由于`level2`方法中的 defer 没有 recover 这个 panic，所以在其执行完成之后，Go 会继续构建父帧（也就是`level1`方法的 defer 帧），并在该帧中调用其中的每个延迟函数。

> defer 函数是按照 LIFO（后进先出）的顺序执行的。

由于`level1`方法中有一个 defer 的函数 recover 了 panic，Go 需要一种跟踪，并恢复这个程序的方法。

### 2. panic 的恢复实现

为了达到恢复的目的，每一个 Goroutine 嵌入了一个特殊的属性，指向一个代表该 panic 的对象：

![](http://cnd.qiniu.lin07ux.cn/markdown/1638784580594-9d6a894b1f21.jpg)

当 panic 发生时，该对象会在运行 defer 函数前被创建，然后 recover 这个 panic 的函数仅仅返回这个对象的信息，同时将这个 panic 标记为已恢复(recovered)：

![](http://cnd.qiniu.lin07ux.cn/markdown/1638784737405-e07df161d73a.jpg)

一旦 panic 被认为已经恢复，Go 需要恢复当前的工作。但是，由于运行时处于 defer 函数的帧中，它不知道恢复到哪里。出于这个原因，当 panic 标记已恢复的时候，Go 保存当前的程序计数器和当前帧的堆栈指针，以便 panic 发生后恢复该函数：

![](http://cnd.qiniu.lin07ux.cn/markdown/1638784814506-1b799e883b6a.jpg)

也可以使用 objdump 查看程序计数器的指向（`objdump -D my-binary | grep 105acef`）：

![](http://cnd.qiniu.lin07ux.cn/markdown/1638847297336-0d6ca8a40eac.jpg)

该指令指向函数调用`runtime.deferreturn`，这个指令被编译器插入到每个函数的末尾，而它运行 defer 函数。

在前面的例子中，这些 defer 函数中的大多数已经运行了——直到恢复。因此，只有剩下的那些会在调用者返回前运行。

有趣的是，函数`runtime.Goexit`使用完全相同的工作流程：`runtime.Goexit`实际上创建了一个 panic 对象，且有着一个特殊标记来让它与真正的 panic 区别开来。这个标记让运行时可以跳过恢复以及适当的退出，而不是直接停止程序的运行。

### 3. recover 源码

`recover()`函数对应了`runtime.panic.go`中的`gorecover()`函数，源码如下：

```go
func gorecover(argp uintptr) interface{} {
  // 只处理 gp._panic 链表中最新的这个 _panic
  gp := getg()
  p := gp._panic
  if p != nil ** !p.recovered && argp == uintptr(p.argp) {
    p.recovered = true
    return p.arg
  }
  return nil
}
```

这个函数逻辑和功能都很简单：

1. 取出当前 goroutine 结构体；
2. 去除当前 goroutine 的`_panic`链表中最新的一个`_panic`实例；
3. 将最新的`_panic`的`recovered`字段赋值为 true，并返回`_panic`参数(`arg`字段)。

所以`recover()`方法就只会修改 panic 的状态，并取出 panic 的参数数据，不涉及代码的神奇跳转。而`_panic.recovered`字段值的改变会在`panic()`函数中起作用。

### 4. 实际使用

理解这个工作流程会让我们了解 defer 函数的重要性以及它如何起作用。比如，在处理若干个 Gouroutine 的时候，在一个 defer 函数中延迟使调用`WaigGroup`可以避免死锁。

示例如下：

```go
func main() {
  var wg sync.WaitGroup
  wg.Add(1)
  
  go func() {
    defer func() {
      if err := recover(); err != nil {
        println(err.(string))
      }
    }()
    p()
    wg.Done()
  }()
  wg.Wait()
}

func p() {
 panic("foo")
}
```

这个程序由于`wg.Done()`无法被调用而导致死锁。但是如果将其移动到一个 defer 函数中就能确保其被执行，从而让这个程序继续运行。

