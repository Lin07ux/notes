> 转摘：[Go 编程模式 -- Pipeline](https://mp.weixin.qq.com/s/kQLAnh-frOALCDNU924zxQ)

### 1. 解决的问题

Pipeline 流水线工作模型是将工作流程分为多个环节，每个环节根据工作强度安排合适的人员数量。良好的流水线设计尽量让各环节的流通率平衡，最大化提高产能效率。

Pipeline 由多个环节组成，且核心是数据。在 Go 中每个环节之间是通过 Channel 来保证数据流动，每个环节的数据处理由 goroutine 完成，且同一个环节任务可以由多个 goroutine 来同时处理。

除了开始环节和结束环节，每个环节都可以有任意数量的输入 Channel 和输出 Channel。开始环节被称为发送者或生产者，结束环节被称为接收者或消费者。

### 2. 实现方式

#### 2.1 基本示例

下面是一个分为三个环境的 Pipeline 示例。

第一个环节，`generate`函数：充当生产者角色，将数据写入 Channel，并把该 Channel 返回。当所有数据都写入完毕，关闭 Channel。

```go
func generate(nums ...int) <-chan int {
  out := make(chan int)
  go func() {
    for _, n := range nums {
      out <- n
    }
    close(out)
  }()
  return out
}
```

第二个环节，`square`函数：是数据处理角色，从开始环节中的 Channel 中取出数据，计算平方结果，并写入到新的 Channel 中，然后把该新 Channel 返回。当所有数据计算完毕，关闭新的 Channel。

```go
func square(in <-chan int) <-chan int {
  out := make(chan int)
  go func() {
    for n := range in {
      out <- n * n
    }
    close(out)
  }()
  return out
}
```

最后一个环节，`main`函数：负责编排整个 Pipeline，并充当消费者角色，从第二个环节的 Channel 中读取数据，打印出来。

```go
func main() {
  // Set up
  c := generate(2, 3)
  
  // Process
  out := square(c)
  
  // Consume
  for n := range out {
    fmt.Println(n)
  }
}
```

#### 2.2 Fan-in-out

在上面的例子中，环节之间通过非缓冲的 Channel 传递数据，节点中的数据都是单个 goroutine 处理与消费。

这种工作模式并不高效，会让整个流水线的效率取决于最慢的环节。因为每个环境中的任务量是不同的，这意味着其需要的资源是存在差异的：任务量小的环节尽量占有少量的机器资源，任务量重的环节，需要更多的线程并行处理。

多个 goroutine 可以从同一个 Channel 读取数据，直到通道关闭，这称为 Fan-out（扇出）。扇出是一种分发任务的模式，将数据进行分散。

![Fan-out](http://cnd.qiniu.lin07ux.cn/markdown/1639711716327-e1e4a3bcfc2e.jpg)

而单个 goroutine 可以从多个输入 Channel 中读取数据，直到所有输入都关闭。具体做法是将输入 Channel 多路复用到同一个 Channel 上，当所有输入 Channel 都关闭时，该 Channel 也关闭。这称为 Fan-in（扇入）。扇入是一种整合任务结果的模式，会将数据进行聚合。

![](http://cnd.qiniu.lin07ux.cn/markdown/1639712542571-016606f93972.jpg)

实现如下：

```go
package main

import (
  "fmt"
  "os"
  "os/signal"
  "sync"
  "syscall"
  "time"
)

func generate(message string, interval time.Duration) (<-chan string, chan<- struct{}) {
  mc := make(chan string)
  sc := make(chan struct{})

  go func() {
    defer func() {
      close(sc)
      close(mc)
    }()

    for {
      select {
      case <-sc:
        return
      default:
        time.Sleep(interval)
        mc <- message
      }
    }
  }()

  return mc, sc
}

func multiplex(mcs ...<-chan string) (chan string, *sync.WaitGroup) {
  mmc := make(chan string)
  wg := &sync.WaitGroup{}

  for _, mc := range mcs {
    wg.Add(1)

    go func(mc <-chan string, wg *sync.WaitGroup) {
      defer wg.Done()

      for m := range mc {
        mmc <- m
      }
    }(mc, wg)
  }

  return mmc, wg
}

func main() {
  // create errs channel for graceful shutdown
  errs := make(chan error)

  // wait for interrupt or terminate signal
  go func() {
    sc := make(chan os.Signal, 1)
    signal.Notify(sc, syscall.SIGINT, syscall.SIGTERM)
    errs <- fmt.Errorf("%s signal received", <-sc)
  }()

  // Create two sample message and stop channels
  mc1, sc1 := generate("message from generate 1", 200 * time.Millisecond)
  mc2, sc2 := generate("message from generate 2", 300 * time.Millisecond)

  // multiplex message channels
  mmc, wg1 := multiplex(mc1, mc2)

  // wait for multiplexed messages
  wg2 := &sync.WaitGroup{}
  wg2.Add(1)
  go func() {
    defer wg2.Done()

    for m := range mmc {
      fmt.Println(m)
    }
  }()

  // wait for errors
  if err := <-errs; err != nil {
    fmt.Println(err.Error())
  }

  // stop generators
  sc1 <- struct{}{}
  sc2 <- struct{}{}
  wg1.Wait()

  // close multiplexed messages channel
  close(mmc)
  wg2.Wait()
}
```

这个例子中：

* `generate`是一个生成器函数，通过`interval`参数控制消息生成频率，定时向 Channel 中写入消息；
* `multiplex`则是一个 Fan-in 扇入函数，可以将多个 Channel 中输出的数据汇总到一个 Channel 中，且提供了一个 WaitGroup 用来判断是否已经完成全部数据的汇总处理；
* `main`函数用来编排 Pipeline，完成整个 Pipeline 流程。并且由于整个 Pipeline 默认情况下是一直运行的，所以增加了可以中断的处理以便能优雅的结束流程（在终端上使用`CTRL+C`按键发送中断信号）.


