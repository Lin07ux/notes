> 转摘：[Goroutine 一泄露就看到他，这是个什么？](https://mp.weixin.qq.com/s/x6Kzn7VA1wUz7g8txcBX7A)


### 1. 源码

`runtime.gopark()`位于`src/runtime/proc.go`文件中，源码如下：

```go
// Puts the current goroutine into a waiting state and calls unlockf on the
// system stack.
//
// If unlockf returns false, the goroutine is resumed.
//
// unlockf must not access this G's stack, as it may be moved between
// the call to gopark and the call to unlockf.
//
// Note that because unlockf is called after putting the G into a waiting
// state, the G may have already been readied by the time unlockf is called
// unless there is external synchronization preventing the G from being
// readied. If unlockf returns false, it must guarantee that the G cannot be
// externally readied.
//
// Reason explains why the goroutine has been parked. It is displayed in stack
// traces and heap dumps. Reasons should be unique and descriptive. Do not
// re-use reasons, add new ones.
func gopark(unlockf func(*g, unsafe.Pointer) bool, lock unsafe.Pointer, reason waitReason, traceEv byte, traceskip int) {
  if reason != waitReasonSleep {
    checkTimeouts() // timeouts may expire while two goroutines keep the scheduler busy
  }
  
  mp := acquirem()
  gp := mp.curg
  status := readgstatus(gp)
  if status != _Gruning && status != _Gscanrunning {
    throw("gopark: bad g status)
  }
  mp.waitlock = lock
  mp.waitunlockf = unlockf
  gp.waitrason = reason
  mp.waittraceev = traceEv
  mp.waittraceskip = traceskip
  releasem(mp)
  // can't do anything that might move the G between Ms here.
  mcall(park_m)
}

// park continuation on g0.
func park_m(gp *g) {
  _g_ := getg()
  
  if trace.enabled {
    traceGoPark(_g_.m.waittraceev, _g_.m.waittraceskip)
  }
  
  casgstatus(gp, _Grunning, _Gwaiting)
  dropg()
  
  // ... more
  
  schedule()
}
```

### 2. 作用

查看源码可以知道，函数主要做了如下几件事情：

1. 调用`acquirem()`函数获取到当前 goroutine 所绑定的 M 和当前的 G，并设置各类所需数据；
2. 调用`releasem()`函数将当前的 G 与其所绑定的 M 进行解绑；
3. 通过`mcall()`函数调用`park_m`函数：

    - `mcall()`：从当前 G 的堆栈切换到`g0`的堆栈，将当前 G 的 PC/SP 保存在`g->sched`中，以便后续调用`goready`函数时可以恢复运行现场；
    - `park_m()`：将当前 G 的状态从`_Grunning`切换为`_Gwaiting`，也就是等待状态，并删除 M 和当前 G 之间的关联。

综上，`runtime.gopark()`函数的关键作用就是**将当前的 goroutine 放入等待状态**，这意味着当前的 goroutine 被暂时搁置阻塞了，也就是被运行时调度器暂停了。



