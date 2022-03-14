> 转摘：
> 
> 1. [再见 Go 面试官：单核 CPU，开两个 Goroutine，其中一个死循环，会怎么样？](https://mp.weixin.qq.com/s/h27GXmfGYVLHRG3Mu_8axw)
> 2. [嗯，你觉得 Go 在什么时候会抢占 P？](https://mp.weixin.qq.com/s/WAPogwLJ2BZvrquoKTQXzg?forceh5=1)

## 一、调度器发展史

在 Go 语言中，早起的调度器是没有设置为抢占式的，只有在 goroutine 进行读写、主动让出、锁等相关操作时才会触发调度切换。

这样做就有一个严重的问题，就是垃圾回收器进行 STW 时，如果有一个 goroutine 一直都在阻塞调用，那么垃圾回收器就要一直等待它。

这种情况下就需要抢占式调度来解决：如果一个 goroutine 运行时间过久，就需要进行抢占来解决其他 goroutine 不能执行的问题。

Go 语言的调度器发展历程如下：

* Go 0.x 基于单线程的调度器
* Go 1.0 基于多线程的调度器
* Go 1.1 基于任务窃取的调度器
* Go 1.2 - Go 1.13 基于协作的抢占式调度器
* Go 1.14 基于信号的抢占式调度器

> 目前有一个调度器的新提案：非均匀存储器访问调度（Non-uniform memory access，NUMA），但由于实现过于复杂，优先级也不够高，因此迟迟未提上日程。

## 二、为什么要抢占

GO GMP 模型中的 P 是用来承载 G 的，一个 M 如果没有绑定 P，那么其就不能进行执行；但是 P 的数量是一定的，而 M 的数量一般都会大于 P，这就造成有些 M 是没有持有 P 的，那么这些 M 上的 G 就不能被执行了。

为了保证每个 G 都有执行的机会，就需要 Go 调度器来在不同的 M 之间对 P 进行调度。而 Go 就在不断的更新中引入了抢占式调度。

为什么要抢占 P 呢？就是因为不主动去抢占的话，就没有机会运行，会 hang 死，或者资源分配不均匀。这在调度器设计中显然是不合理的。

### 2.1 示例

比如，单核 CPU 中，运行两个 goroutine，其中一个死循环，会怎么样？

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

这段代码中开启了一个执行了死循环的 goroutine，当主 goroutine 在调用了休眠方法（`time.Sleep()`）之后，主动让出了执行权，就会由死循环的 goroutine 进行运行了。而这个死循环的 goroutine 中没有涉及到主动放弃执行权的调用（`runtime.Gosched`），所用就会一直运行下去。

所以，在 Go 1.14 之前不会输出任何结果，在 Go 1.14 之后就能够正常输出。因为 **Go 1.14 中实现了基于信号的抢占式调度**，解决了一些无法被抢占解决的场景问题。

### 2.2 抢占后的处理

在抢占后，原本正在使用 P 的 M 因为与 P 解绑了，就不能继续执行了。这是没问题的，因为该 M 上的 G 已经阻塞住了，暂时是不会有后续的执行诉求的。

如果一个 G 从阻塞状态中恢复了，期望继续运行，怎么办？这时候会先检查自己所在的 M 是否仍然绑定着 P：

* 如果绑定着 P，就调整 G 的状态，继续运行；
* 如果没有 P，就可以重新从别的 M 上抢占并绑定 P，为自己所用。

也就是抢占 P 是一个双向行为，可以被别的 M 抢占，也可以去抢占别的 M 的 P。

## 三、如何抢占

### 3.1 注册信号处理器

在 Go 1.14 之后，Go 程序会在启动时，通过`runtime.sighandler()`方法注册并绑定`SIGURG`信号处理器：

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

### 3.2 sysmon 线程

在 Go 中，sysmon 线程会用于检测抢占，具有如下几个特点：

1. sysmon 是一个独立线程，会无限循环执行；
2. sysmon 线程在每次循环中，会进行`netpool`(获取 fd 事件)、`retake`(抢占)、`force gc`(按时间强制执行 gc)，`scavenge heap`(释放自由列表中多余的项减少内存占用)等处理。
3. sysmon 线程一开始每次循环后，休眠 20us，50 次之后（即 1ms 后）每次休眠时间倍增，最终每一轮都会休眠 10ms。

`runtime.sysmon()`方法中会调用`runtime.retake()`方法，该方法会检测相关的 P，当满足下面的任意一个场景时，就会发送信号给 M：

* 抢占阻塞在系统调用上的 P；
* 抢占运行时间过长的 G。

M 收到信号后会休眠正在阻塞的 G 或运行时间过长的 G，调用绑定的信号方法，重新进行调度，从而避免死循环阻塞无法抢占的问题。

### 3.3 retake 抢占

`runtime.retake`会进行具体的抢占分析，当有符合前面的条件发生时，就会触发抢占信号。

#### 3.3.1 主体逻辑

下面是`runtime.retake()`方法中，针对抢占 P 的场景的相关代码分析：

```go
func retake(now int64) uint32 {
  n := 0
  // 防止发生改变，对所有 P 加锁
  lock(&allpLock)
  // 走入主逻辑，多所有 P 开始循环处理
  for i := 0; i < len(allp); i++ {
    _p_ := allp[i]
    pd := &_p_.sysmontick
    s := _p_.status
    sysretake := false
    
    // ...
    
    if s == _Psyscall {
      // ...
    }
  }
  unlock(&allpLock)
  return uint32(n)
}
```

该方法会先对表示全部 P 数组的`allpLock`上锁，防止该数组发生变化，会保护`allp`、`idlepMask`、`timerpMask`属性的无 P 读取和大小变化，以及对`allp`的所有写入操作，可以避免影响后续的操作。

在前置处理完毕后，就会进入主逻辑，也就是使用`for`循环对所有的 P 进行相关处理。

#### 3.3.2 sysmon 周期检测

对 P 的第一个处理，就是对其`syscalltick`进行判断：

```go
// 判断是否超过 1 个 sysmon tick 周期
t := int64(_p_.syscalltick)
if !sysretake && int64(pd.syscalltick) != t {
  pd.syscalltick = uint32(t)
  pd.syscallwhen = now
  continue
}
```

如果 P 在系统调用(syscall)中不存在超过 1 个 sysmon tick 周期（至少 20us）的任务，则会直接跳过，否则后续就会从系统调度中抢占 P。

#### 3.3.3 抢占检测

当 P 的 sysmon 周期满足条件时，会继续进行更多检测：

```go
if runqempty(_p_) &&
   atomic.Load(&sched.nmspinning)+atomic.Load(&sched.npidle) > 0 &&
   pd.syscallwhen+10*1000*1000 > now {
   continue  
}
```

这里做了三个判断：

1. `runqempty(_p_)` 判断 P 的任务队列是否为空，以此来检测有没有其他任务需要执行；
2. `atomic.Load(&sched.nmspinning)+atomic.Load(&sched.npidle) > 0` 判断是否存在空闲 P 和正在进行调度窃取 G 的 P；
3. `pd.syscallwhen+10*1000*1000 > now` 判断系统调用时间是否超过了 10ms。

如果这些条件同时满足，也是不需要进行抢占处理的。

这里奇怪的是`runqempty(_p_)`明明已经判断了是没有其他任务需要执行的，按理是不需要抢夺 P 的。但实际情况是，由于可能会阻止 sysmon 线程的深度睡眠，最终还是希望继续占有 P。

#### 3.3.4 抢占 P

在完成上述判断后，就会进入到抢占 P 的阶段了：

```go
unlock(&allpLock)
incidlelocked(-1)
if atomic.Cas(&_p_.status, s, _Pidle) {
  if trace.enabled {
    traceGoSysBlock(_p_)
    traceProcStop(_p_)
  }
  n++
  _p_.syscalltick++
  handoff(_p_)
}
incidlelocked(1)
lock(&allpLock)
```

这里首先需要将`allpLock`解锁，从而实现获取`sched.lock`，以便继续下一步操作。

然后减少闲置的 M：需要在原子操作 CAS 之前减少闲置 M 的数量（假设有一个正在运行），否则在发生抢夺 P 时可能会退出系统调用，递增 nmidle 并报告死锁事件。

接着就会使用 CAS 修改 P 的状态为`idle`，以便交给其他 M 使用。

最后进行 P 的抢占和 M 的调度：调用`handoffp()`方法从系统调用或锁定的 M 中抢夺 P，让一个新的 M 来接管这个 P。

#### 3.3.5 总结

至此，完成了抢占 P 的基本流程，可以得出抢占会发生在满足以下条件时：

1. 存在系统调用超时：存在超过 1 个 sysmon tick 周期（至少 20us）的任务；
2. 没有空闲的 P：所有的 P 都已经与 M 绑定，需要抢占当前正处于系统调用而实际上系统调用并不需要这个 P 的情况，会将其分配给其他 M 去调度其他的 G；
3. 如果 P 的运行队列里面有等待运行的 G，为了保证 P 的本地队列中的 G 得到及时调度，而 P 本身又忙于系统调度，无暇管理。此时会寻找另外一个 M 来结果 P，从而实现继续调度 G 的目的。


