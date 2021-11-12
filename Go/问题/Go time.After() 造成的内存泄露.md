> 转摘：[Golang <-time.After() 在计时器过期前不会被垃圾回收](https://mp.weixin.qq.com/s/HtvJMGWmGnToplaz54pUaw)

### 1. 问题

一个使用定时器来实现超时自动关闭的场景：

```go
func ProcessChannelMessages(ctx context.Context in <-chan string, idleCounter prometheus.Counter) {
  for {
    start := time.Now()
    select {
    case s, ok := <-in:
      if !ok {
        return
      }
      // processing
    case <-time.After(5 * time.Minute):
      idleCounter.Incr()
    case <-ctx.Done():
      return
    }
  }
}
```

在运行一段时间之后，内存会出现暴涨且难以消退：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636555302730-d1c2463350d5.jpg)

左侧是未修复之前的内存消耗，右侧则是修复之后的表现。

### 2. 原因

分析器显示，`<-time.After()`是内存泄露的原因。这是因为：

**在计时器触发之前，GC 不会回收计时器资源。**

在 channel 中每秒大约有 60k 个消息，所以在一定时间内，会存在大约 1800 万个计时器以及一些不确定数量的计时器，等待垃圾收集器的回收处理。这也是导致内存出现大量泄露的问题。

`time.After()`的源码中对此有相关的注释：

```go
// time/sleep.go

// After waits for the duration to elapse and then sends the current time
// on the returned channel.
// It is equivalent to NewTimer(d).C.
// The underlying Timer is not recovered by the garbage collector
// until the timer fires. If efficiency is a concern, use NewTimer
// instead and call Timer.Stop if the timer is no longer needed.
func After(d Duration) <-chan Time {
	return NewTimer(d).C
}
```

### 3. 解决

解决方案在`time.After()`的注释中也有提到：直接使用计时器的实例，并在不需要计时器的时候调用`Timer.Stop()`方法来停止该计时器，以便 GC 能够回收掉它。

对于上面的实例，也可以循环使用同一个计时器，并在必要的时候重置该计时器，达到相同的目的：

```go
func ProcessChannelMessages(ctx context.Context, in <-chan string, idleCounter prometheus.Counter) {
  idleDuration := 5 * time.Minute
  idleDelay := time.NewTimer(idleDuration)
  defer idleDelay.Stop()
  for {
    idleDelay.Reset(idleDuration)
    switch {
    case s, ok := <-in:
      if !ok {
        return
      }
      // processing
    case <-deleDelay.C:
      idleCounter.Incr()
    case <-ctx.Done():
      return
    }
  }
}
```

