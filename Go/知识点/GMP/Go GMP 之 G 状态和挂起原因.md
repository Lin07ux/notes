Goroutine 状态和挂起的原因均定义在`src/runtime/runtime2.go`文件中。

### 1. 状态

|  状态               | 值  | 含义
|:------------------- |:--:|:---------------------------
| `_Gidle`            | 0  | 刚刚被分配，还没有进行初始化
| `_Grunnable`        | 1  | 已经在运行队列中，还没有执行用户代码
| `_Grunning`         | 2  | 不在运行队里中，已经可以执行用户代码，此时已经分配了 M 和 P
| `_Gsyscall`         | 3  | 正在执行系统调用，此时分配了 M
| `_Gwaiting`         | 4  | 在运行时被阻止，没有执行用户代码，也不在运行队列中，正在某处阻塞等待中
| `_Gmoribund_unused` | 5  | *尚未使用*，但是在 gdb 中进行了硬编码
| `_Gdead`            | 6  | *尚未使用*，这个状态可能是刚退出或是刚被初始化，此时它并没有执行用户代码，有可能也有可能没有分配堆栈
| `_Genqueue_unused`  | 7  | *尚未使用*
| `_Gcopystack`       | 8  | 正在复制堆栈，并没有执行用户代码，也不在运行队列中
| `_Gpreempted`       | 9  | 由于抢占而被阻塞，没有执行用户代码，并且不在运行队列中，类似于`_Gwaiting`状态，但是需要先转换为`_Gwaiting`才能被再次运行

除此之外，还有一些可以和这些状态并行存在的状态：`_Gscan = 0x1000`、`_Gscanrunnable = 0x1001`、`_Gscanrunning = 0x1002`、`_Gscansyscall = 0x1003`、`_Gscanwaiting = 0x1004`、`_Gscanpreemted = 0x1009`。

状态的流转流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637048220124-3cf66e2f68a6.jpg)


### 2. 诱发挂起的原因

> 转摘：[会诱发 Goroutine 挂起的 27 个原因](https://mp.weixin.qq.com/s/_VJxcYz_KF1JKRUDZk9jNg)

诱发 goroutine 挂起的原因有多种情况，每种情况又可以分为更多的子类型。

### 2.1 Channel 通道

* `waitReasonChanReceiveNilChan` 对未初始化的 channel 进行读操作时阻塞。
* `waitReasonChanSendNilChan` 对未初始化的 channel 进行写操作时阻塞。
* `waitReasonChanReceive` 在 channel 进行读操作，会触发阻塞。
* `waitReasonChanSend` 在 channel 进行写操作，会触发阻塞。

### 2.2 GC 垃圾回收

* `waitReasonGCAssistWait` GC 辅助标记阶段中的结束行为触发阻塞。
* `waitReasonGCAssistMarking` GC 辅助标记阶段会触发阻塞。
* `waitReasonGCSweepWait` GC 清扫阶段中的结束行为触发阻塞。
* `waitReasonGCScavengeWait` GC scavenge 阶段的结束行为触发阻塞。这主要是对新空间的垃圾回收，是一种经常运行、快速的 GC，负责从新空间中清除较小的对象。
* `waitReasonGarbageCollection` 在垃圾回收时阻塞，主要场景是 GC 标记终止（GC Mark Termination）阶段时触发。
* `waitReasonGarbageCollectionScan` 在垃圾回收扫描时阻塞，主要场景是 GC 标记（GC Mark）扫描 Root 阶段时触发。
* `waitReasonForceGCIdle` 强制 GC（空闲时间）结束时，触发阻塞。
* `waitReasonWaitForGCCycle` 等待 GC 周期，会休眠造成阻塞。
* `waitReasonGCWorkerIdle` GC Worker 空闲时间，会休眠造成阻塞。

### 2.3 Sleep 休眠

* `waitReasonSleep` 经典的 sleep 行为触发阻塞。

### 2.4 Lock 锁

* `waitReasonSemacquire` 信号量处理结束时触发阻塞。
* `waitReasonSyncCondWait` 在调用`sync.Wait`方法时触发阻塞。

### 2.5 Preempted 抢占

* `waitReasonPreempted` 发生循环调用抢占时，会休眠等待调度。

### 2.6 IO Wait IO 阻塞

* `waitReasonIOWait` IO 时阻塞等待，如网络请求等。

### 2.7 Select 选择语法

* `waitReasonSelect` 在调用关键字 select 时触发阻塞。
* `waitReasonSelectNoCases` 在调用关键字 select 时，如果一个 case 都没有，会直接触发阻塞。

### 2.8 debug 调试

* `waitReasonTraceReaderBlocked` 与 Trace 相关，ReadTrace 会返回二进制跟踪数据，将会阻塞直到数据可用。
* `waitReasonDumpingHeap` 对 Go Heap 堆进行 dump 时阻塞。其使用场景仅为`runtime.debug`时，也就是常见的 pprof 这一类采集时阻塞。
* `waitReasonDebugCall` 调用 GODEBUG 时触发阻塞。

### 2.9 其他

* `waitReasonZero` 无正式解释，从使用情况来看，主要在 sleep 和 lock 的 2 个场景中使用。
* `waitReasonPanicWait` 在 main goroutine 发生 panic 时触发阻塞。
* `waitReasonFinalizerWait` 在 finalizer 结束阶段触发阻塞。在 Go 程序中，可以通过调用`runtime.SetFinalizer()`函数来为一个对象设置一个终结者函数。这个行为对应着结束阶段造成的回收。
* `waitReasonTimerGoroutineIdle` 与 Timer 相关，在没有定时器需要执行任务时触发。


