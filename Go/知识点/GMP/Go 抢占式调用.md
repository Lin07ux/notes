### 1. 问题

单核 CPU 中，运行两个 goroutine，其中一个死循环，会怎么样？

代码示例如下：

```go
// Main Goroutine
func main() {
  // 模拟单核 CPU
  runtime.GOMAXPROCS(1)
  
  // 模拟 goroutine 死循环
  go func() {
    for {
    }
  }()
  
  time.Sleep(time.Millisecond)
  fmt.Println("main end")
}
```

### 2. 答案

对于上面的程序：

* 在 Go 1.14 之前不会输出任何结果；
* 在 Go 1.14 之后能够正常输出。

这是由于 **Go 1.14 中实现了基于信号的抢占式调度**，解决了一些无法被抢占解决的场景问题。

#### 2.1 Go 1.14 之前

在 Go 1.14 之前，因为这段代码中开启了一个执行了死循环的 goroutine，所以这个 goroutine 一旦执行起来，就无法被别的 goroutine 给抢占了。

这个有死循环的 goroutine 中没有涉及到主动放弃执行权的调用（`runtime.Gosched`），也没有其他可能会导致执行权转移的调用行为，所以这个 goroutine 是没有机会暂停的，只能一直运行下去。

主 goroutine 之所以无法运行完成，是因为它在调用了休眠方法（`time.Sleep()`）之后，主动让出了执行权。而由于这是单核 CPU，只有唯一的 P，所以这个唯一的 P 上就开始运行那个死循环的 goroutine 了。最后导致主 goroutine 无法再被调用。

#### 2.2 Go 1.14 及之后

在 Go 1.14 之后，Go 程序会在启动时，通过`runtime.sighandler()`方法注册并绑定`SIGURG`信号：

```go
func mstartm0() {
  // ...
  initsig(false)
}

func initsig(preinit bool) {
  for i := uint32(0); i < _NSIG; i++ {
    // ...
    setsig(i, funcPC(sighandler))
  }
}

func sighandler(sig uint32, info *siginfo, ctxt unsafe.Pointer, gp *g) {
  // ...
  if sig == sigPreempt && debug,asyncpreemtoff == 0 {
    // 执行抢占
    doSigPreempt(gp, c)
  }
}
```

注册的信号处理器中，会在适当的条件执行`runtime.doSigPreempt()`方法进行抢占，实现 goroutine 的调度切换。

同时，在调度`runtime.sysmon()`方法时，会调用`retake()`方法，该方法会检测相关的 P，当满足下面的任意一个场景时，就会发送信号给 M：

* 抢占阻塞在系统调用上的 P；
* 抢占运行时间过长的 G。

M 收到信号后会休眠正在阻塞的 G 或运行时间过长的 G，调用绑定的信号方法，重新进行调度，从而避免死循环阻塞无法抢占的问题。

> 注：Go 语言中，`sysmon`会用于检测抢占，runtime 的系统检测器，可进行`forcegc`、`netpoll`、`retake`等一系列相关操作。


