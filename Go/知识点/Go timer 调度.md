> [Go timer 是如何被调度的？](https://mp.weixin.qq.com/s/OSE7A5GNNl8bkBezCyE80w)

### 一、基础

在 Go 中，不论是使用`time.NewTimer`、`time.After`还是`time.AfterFunc`来初始化一个 timer，这个 timer 最终都会加入到一个全局的 timer 堆中，由 Go runtime 统一管理。

全局的 timer 堆也经历过三个版本的重要升级：

1. Go 1.9 版本之前：所有的计时器由全局唯一的四叉堆维护，协程间竞争激烈；
2. Go 1.9 - Go 1.13：全局使用 64 个四叉堆维护全部的计时器，这也没有本质的解决 1.9 版本之前的问题；
3. Go 1.14 版本之后：每个 P 单独维护一个四叉堆。

在 Go 1.14 以后，timer 性能得到了质的飞升，不过伴随而来的是 timer 成了 Go 里面最复杂、最难以梳理的数据结构。

下面不会详细分析每一个细节，而是从大体上来了解 Go timer 的工作原理。

## 二、源码图解

### 2.1 四叉堆原理

timer 的全局堆是一个四叉堆，特别是 Go 1.14 之后每个 P 都会维护着一个四叉堆，减少了 Goroutine 之间的并发问题，提升了 timer 的性能。

四叉堆其实就是四叉树，Go timer 按照如下原则维护四叉堆：

* Go runtime 调度 timer 时，触发时间更早的 timer 要减少其查询次数，尽快被触发。所以四叉树的父节点的触发时间一定是小于子节点的。也就是，是一个小顶堆。
* 四叉树最多有四个子节点，为了兼顾四叉树插入、删除、重排速度，所以四个兄弟节点间并不要求其按触发早晚排序。

这里用两张图简单演示下 timer 的插入和删除：

![把 timer 插入堆](https://cnd.qiniu.lin07ux.cn/markdown/16428417642394.gif)

![把 timer 从堆中删除](https://cnd.qiniu.lin07ux.cn/markdown/16428418034169.gif)

### 2.2 timer 调度过程

1. 调用`time.NewTimer`、`time.After`、`time.AfterFunc`生成 timer，并将其加入到对应的 P 的堆上；
2. 调用`timer.Stop`、`timer.Reset`改变对应的 timer 的状态；
3. GMP 在调度周期内会调用`runtime.checkTimers`函数，遍历该 P 的 timer 堆上的元素，根据对应 timer 的状态执行对应的操作。

过程图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1642842261865-875a6517ebbc.jpg)

### 2.3 加入到 timer 堆上的时机

把 timer 加入 timer 四叉堆上总共有如下几种方式：

1. 通过`time.NewTimer`、`time.After`、`time.AfterFunc`初始 timer 后就会被立即放入到对应的 P 的 timer 堆上；
2. timer 已经被标记为`timerRemoved`时，调用了`timer.Reset()`，这个 timer 也会被重新加入到 P 的 timer 堆上；
3. timer 还没到需要被执行的时间，调用了`timer.Reset()`，这个 timer 会被 GMP 调度探测到，先将该 timer 从 timer 堆上删除，然后重新加入到 timer 堆上；
4. STW 时，runtime 会释放不再使用的 P 的资源，将其 timer 堆中有效的 timer（状态为`timerWaiting`、`timerModifiedEarlier`、`timerModifiedLater`）都重新加入到一个新的 P 的 timer 堆上。

### 2.4 Reset 时 timer 的处理

`timer.Reset`的目的是把 timer 重新加入到 timer 堆中，重新等待被触发。不过这种处理也分两种情况：

1. 被标记为`timerRemoved`的 timer，因为已经从 timer 堆上删除了，但会重新设置被触发时间，再被加入到 timer 堆中；
2. 等待被触发的 timer，在`timer.Reset`函数中只会修改其触发时间和状态（`timerModifiedEarlier`或`timerModifiedLater`），这个被修改状态的 timer 也同样会被重新加入到 timer 堆上，不过是由 GMP 触发的，由`runtime.checkTimers`调用`adjusttimers`或者`runtimer`来执行的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642845257442-4cf1707fec07.jpg)

### 2.5 Stop 时 timer 的处理

`time.rStop`为了让 timer 停止，不再被触发，也就是从 timer 堆上删除。不过`timer.Stop`并不会真正的从 P 的 timer 堆上删除 timer，只会将 timer 的状态修改为`timerDeleted`，然后等待 GMP 触发的`adjusttimers`或者`runtimer`来执行。

真正删除 timer 的函数有两个：`dodeltimer`、`dodeltimer0`。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642845389902-abe18b66084b.jpg)

### 2.6 timer 的执行

timer 的真正执行者是 GMP。GMP 会在每个调度周期内，通过`runtime.checkTimers`调用`time.runtimer`。

在`time.runtimer`函数内会检查该 P 的 timer 堆上的所有 timer，判断这些 timer 是否能被触发。如果该 timer 能够被触发，会通过回调函数`sendTime`给 timer 的 channel 类型的字段`C`发一个当前的时间，表示这个 timer 已经被触发了。

如果是 ticker 的话，触发之后，会计算下一次要触发的时间，然后重新将该 timer 加入到 timer 堆中。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642845614441-bc8f820b44ab.jpg)

## 三、timer 中的坑

timer 使用的很频繁，但是也是最容易导致内存泄露、CPU 狂飙的杀手之一。但是最容易造成问题的其实就在两个方面：

1. 错误的创建很多的 timer，导致资源浪费；
2. 由于`timer.Stop`不会主动关闭 timer.C ，这会导致程序阻塞。

### 3.1 错误创建很多 timer 导致内存暴涨

比如，下面这段代码是造成 timer 异常的最常见的写法，也是最容易忽略的写法：

```go
func main() {
  for {
    timeout := time.After(30 * time.Second)
    select{
    case <-someDone:
      // do something
    case <-timeout:
      return
    }
  }
}
```

这段代码造成为题的原因其实也很简单：因为`time.After`底层是调用`time.NewTimer`，也会生成一个新的 timer，并将其放入到全局的 timer 堆中。在 for 循环中会创建出数以万计的 timer，从而导致内存暴涨。同时，不管是 GMP 周期性的`checkTimers`执行，还是插入新的 timer 时，都会疯狂的遍历 timer 堆，从而也会导致 CPU 飙升。

要注意的是，不只是`time.After`，只要是能创建新的 timer 的方法，都要防止在循环中调用。

对上面的示例，解决办法就是：使用`timer.Reset`重置 timer，以重复利用 timer。

```go
func main() {
  timer := time.NewTimer(time.Second * 5)
  for {
    timer.Reset(time.Second * 5)
    select {
    case <-someDone:
      // do something
    case <-timer.C:
      return
    }
  }
}
```

### 3.2 未正常关闭 timer.C 导致 goroutine 阻塞

下面这段代码，只有等待 timer 超时时才会继续执行：

```go
func main() {
  timer1 := time.NewTimer(2 * time.Second)
  <-timer1.C
  println("done")
}
```

原理很简单：程序阻塞在`<-timer1.C`上，一直等待 timer 被触发，回调函数`time.sendTime`CIA会发送一个当前时间到`timer1.C`上，程序才能继续执行下去。

不过使用`timer.Stop`的时候就要特别注意了，因为它不会关闭`timer.C`通道。比如：

```go
func main() {
  timer1 := time.NewTimer(2 * time.Second)
  go func() {
    timer1.Stop()
  }()
  <-timer1.C
  println("done")
}
```

这时程序就会死锁了，因为`timer.Stop`使 timer 停止了，但是`timer.C`通道没有被关闭，这就使得程序一直阻塞在`timer1.C`上。

如果阻塞是子协程，那么就会造成 goroutine 泄露、内存泄露了。`timer.Stop`的正确使用方式如下：

```go
func main() {
  timer1 := time.NewTimer(2 * time.Second)
  go func() {
    if !timer1.Stop() {
      <-timer1.C
    }
  }
  
  select {
  case <-timer1.C:
    fmt.Println('expired')
  default:
  }
  println("done")
}
```

