> 转摘：[回答我，停止 Goroutine 有几种方法？](https://mp.weixin.qq.com/s/zmrUe9IeX9x77Jspnv5Edg)

停止分为自身主动停止和被动停止，但是 Goroutine 只能由其自身主动停止。在 Go 语言中，**每一个 goroutine 都需要自己承担自己的任何责任**，这是基本原则。

## 一、主动停止

Goroutine 在其内部代码执行完毕后会自动被调度器停止（回收）。如果要让 Goroutine 主动停止运行，就是要让 Goroutine 根据一定的条件判断，主动进行 return 操作。

在外部来控制 Goroutine 内部的条件判断，最合适的方法就是 Channel。通过对 Channel 的不同使用方式，可以方便的实现让 Goroutine 主动退出的效果。

### 1.1 关闭 Channel

第一种方法就是借助 Channel 的 close 机制来完成对 goroutine 的精确控制：

```go
func main() {
  ch := make(chan string, 6)
  go func() {
    v, ok := <-ch
    if !ok {
      fmt.Println("结束了")
      return
    }
    fmt.Println(v)
  }()
  
  ch <- "煎鱼还没进入锅里..."
  ch <- "煎鱼进脑子里了！"
  close(ch)
  time.Sleep(time.Second)
}
```

这里使用了 Go Channel 去除两个值的方式，也可以使用`for...range`方式一直遍历 Channel，直到其关闭：

```go
go func() {
  for v := range ch {
    fmt.Println(v)
  }
}()
```

### 1.2 定期轮询 Channel

除了直接遍历 Channel，还可以更精确的来接收停止通知，其结合了第一种方法和类似信号量的处理方式，使用 select 语法来实现：

```go
func main() {
  ch := make(chan string, 6)
  done := make(chan struct{})
  go func() {
    for {
      select {
      case ch <- "脑子进煎鱼了":
      case <-done:
        close(ch)
        return
      }
      time.Sleep(100 * time.Millisecond)
    }
  }()
  
  go func() {
    time.Sleep(3 * time.Second)
    done <- struct{}{}
  }()
  
  for i := range ch {
    fmt.Println("接收到的值：", i)
  }
  
  fmt.Println("结束")
}
```

在这部分代码中，声明了 Channel 变量`done`，用作为信号量处理 goroutine 的关闭。

在 goroutine 中，使用`for-loop`结合`select`关键字进行监听，如果没有结束就执行业务代码，如果需要结束，就关闭通道，并返回。

> 如果程序逻辑比较简单、结构化，也可以不调用`close`方法，因为 goroutine 会自然结束，也就不需要手动关闭了。

### 1.3 使用 Context

第三种方法，可以借助 Go 预约的上下文（context）来做 goroutine 的控制和关闭：

```go
func main() {
  ch := make(chan struct{})
  ctx, cancel := context.WithCancel(context.Background())
  
  go func(ctx context.Context) {
    for {
      select {
      case <-ctx.Done():
        ch <- struct{}{}
        return
      default:
        fmt.Println("煎鱼还没到锅里...")
      }
      
      time.Sleep(500 * time.Millisecond)
    }
  }(ctx)
  
  go func() {
    time.Sleep(3 * time.Second)
    cancel()
  }()
  
  <-ch
  fmt.Println("结束")
}
```

在 Context 中可以借助`ctx.Done()`获取一个只读的 Channel，类型为结构体，可用于识别当前 Channel 是否已被关闭，其原因肯能是到期，也可能是被取消了。

Context 对于跨 goroutine 控制有自己的灵活之处，可以调用`context.WithTimeout`来根据时间控制，也可以自己主动的调用`cancel`方法来手动关闭。

## 二、关闭其他 Goroutine

在 Go 语言中，goroutine 只能自己主动退出，不能被外界的其他 goroutine 关闭，也没有 goroutine 句柄的显式概念。

在 Go issues/32610 中也有人提出类似的问题，Dave Cheney 给出了一些思考：

* 如果一个 goroutine 被强行停止了，它所拥有的的资源会发生什么？堆栈被解开了吗？defer 是否被执行？

    - 如果执行 defer，该 goroutine 依旧可能继续无限期地生存下去；
    - 如果不执行 defer，该 goroutine 原本的应用程序系统设计逻辑将会被破坏，这肯定不合理。

* 如果允许强制停止 goroutine，是要释放所有东西，还是直接把它从调度器中踢出去？想通过这来解决什么问题？

而且，一旦放开这种限制，很有可能就不知道 goroutine 的句柄被传到了哪里，又是在何时何地被什么程序给莫名其妙的关闭了，非常的糟糕。


