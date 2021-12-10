> 转摘：[Go 大杀器之跟踪剖析 trace](https://mp.weixin.qq.com/s/OY-w05uJIgjov9qGmJ-Wwg)

Go 工具中的 trace 命令可以提供一份关于 goroutine 很多隐藏行为的报告。例如：

* Goroutine 在执行时会做哪些操作？
* Goroutine 执行/阻塞了多长时间？
* Syscall 在什么时候被组织？在哪里被阻止？
* 谁锁住/解锁了 Goroutine？
* GC 是怎么影响到 Goroutine 的执行的？


## 一、基础

要使用 trace 工具导出相关的跟踪报告，需要在代码中调用 trace 接口。如下：

```go
import (
  "os"
  "runtime/trace"
)

func main() {
  trace.Start(os.Stderr)
  defer trace.Stop()
  
  ch := make(chan string)
  go func() {
    ch <- "Go 语言编程之旅"
  }()
  
  <-ch
}
```

然后执行程序，生成跟踪文件：

```shell
go run main.go 2> trace.out
```

之后即可启动可视化界面，查看跟踪报告：

```shell
go tool trace trace.out
```

此时就可以在浏览器中访问报告内容了：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639051030478-b5c26d99301a.jpg)

其中：

* View trace：查看跟踪信息
* Goroutine analysis：Goroutine 分析
* Network blocking profile：网络阻塞信息
* Synchronization blocking profile：同步阻塞信息
* Syscall blocking profile：系统调用阻塞信息
* Scheduler latency profile：调度延迟概况
* User defined tasks：用户自定义任务
* User defined regions：用户自定义区域
* Minimum mutator utilization：最低 Mutator 利用率

## 二、报告使用

### 2.1 调度延迟概况

在刚开始查看问题时，除非是很明显的现象，否则不应该一开始就陷入细节。

因此一般先查看 Scheduler latency profile，可以通过图标的方式看到整体的调用开销情况。如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639051582391-b1a968c90810.jpg)

演示程序比较简单，因此这里就两块，一个是 trace 本身，另外一个是 channel 的收发。

### 2.2 Goroutine 分析

然后就可以查看 Goroutine analysis，能通过这个功能看到整个运行过程中，每个函数块有多少个 Goroutine 在跑：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639051706207-d709d7b7d9b0.jpg)

通过上图可以看到共有 3 个 goroutine，分别是：

* `runtime.main`
* `runtime/trace.Star.func1`
* `main.main.func1`

可以通过点击具体细项查看每个 goroutine 都做了些什么事情，如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639052042031-7f3c7b536454.jpg)

同时，也可以看到当前 Goroutine 在整个调用耗时中的占比，以及 GC 清扫和 GC 暂停等待的一些开销。还可以把图表下载下来分析。

这些信息能很好的帮助对 goroutine 运行阶段做一个剖析，可以得知到底哪里慢，然后再决定下一步的排查方向。

图标中的各个时间字段含义如下：

* Execution Time：执行时间
* Network Wait Time：网络等待时间
* Sync Block Time：同步阻塞时间
* Blocking Syscall Time：调用阻塞时间
* Scheduler Wait Time：调度等待时间
* GC Sweeping：GC 清扫
* GC Pause：GC 暂停

### 2.3 查看跟踪

在对当前程序的 Goroutine 运行分布有了初步了解后，可以通过查看跟踪看看 Goroutine 之间的关联性，如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639053695800-3e795bd68c2e.jpg)

1. 时间线：显示执行的时间单元，根据时间维度的不同可以调整区间，具体可通过`Shift + ?`查看帮助手册。
2. 堆：显示执行期间的内存分配和释放情况。
3. 协程：显示在执行期间的每个 Goroutine 运行阶段有多少个协程在运行，其包含 GC 等待（GCWaiting）、可运行（Runnable）、运行中（Running）这三种状态。
4. OS 线程：显示在执行期间有多少个线程在运行，其包含正在调用 Syscall（InSyscall）、运行中（Running）这两种状态。
5. 虚拟处理器：每次虚拟处理器显示一行，虚拟处理器的数量一般默认为系统内核数。
6. 协程和事件：显示在每个虚拟处理器上有什么 Goroutine 正在运行，而连线行为代表事件关联。

点击具体的 Goroutine 行为后可以看到其相关联的详细信息：

* Start：开始时间
* Wall Duration：持续时间
* Self Time：执行时间
* Start Stack Trace：开始时的堆栈信息
* End Stack Trace：结束时的堆栈信息
* Incoming Flow：输入流
* Outgoing Flow：输出流
* Preceding Events：之前的事件
* Following Events：之后的事件
* All Connected：所有链接的事件

### 2.4 查看事件

可以通过点击 View Options-Flow events、Following events 等方式，查看应用运行中的事件流情况，如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639054762325-aab123a753ea.jpg)

通过分析图上的事件流，可以得知：

* 这程序从 G1 `runtime.main`开始运行
* 在运行时创建了两个 Goroutine：

    - 先是创建 G18 `runtime/trace.Start.func1`
    - 再是创建 G19 `main.main.func1`

同时，还可以通过 Goroutine 的名称去了解它的调用类型，如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639054883712-dcd96de57cab.jpg)

可以看到，`runtime/trace.Start.func1`就是程序中在`main.main`调用了`runtime/trace.Start`方法，紧接着该方法又利用协程创建了一个闭包`func1`去进行调用。结合开头的代码来看，很明显就是`ch`的输入输出的过程了。



