### 1. 超时控制介绍

Go 一般是用来写后端服务的，一般一个请求是由多个串行或并行的子任务来完成的，每个子任务可能是另外的内部请求。那么当请求超时时，就需要快速返回，释放占用的资源（goroutine、文件描述符等）。

比如，如果一个页面的访问时间很长，导致用户不再继续等待直接关闭了页面。如果后端程序没有实现超时控制，那么整个调用链路上一堆资源的占用都会被白白浪费了。

服务端常见的需要进行超时控制的场景如下：

* 进程内的逻辑处理；
* 读写客户端请求，比如 HTTP 或者 RPC 请求；
* 调用其他服务端请求，包括调用 RPC 或者访问 DB 等。

### 2. 简单控制实现

最简单的超时控制就是设置一个超时时间，在时间到了之后就关闭和释放相关资源。

```go
func requestWork(ctx context.Context, job interface{}) error {
  ctx, cancel := context.WithTimeout(ctx, time.Second*2)
  defer cancel()
  
  done := make(chan error)
  go func() {
    done <- hardWork(job)
  }()
  
  select {
  case err := <-done:
    return err
  case <-ctx.Done():
    return ctx.Err()
  }
}

func hardWork(job interface{}) error {
  time.Sleep(time.Second * 10)
  return nil
}
```

这里在`requestWork`方法中，包装一个超时的 Context，然后新建一个用来存储执行结果的 Channel，并使用`select...case`来等待执行结果或者等待超时。

下面模拟开启 1000 个处理程序的情况：

```go
func main() {
  const total = 1000
  var wg sync.WaitGroup
  
  wg.Add(total)
  now := time.Now()
  for i := 0; i < total; i++ {
    go func() {
      defer wg.Done()
      requestWork(context.Background(), "any")
    }()
  }
  wg.Wait()
  fmt.Println("elapsed:", time.Since(now))
}
```

效果如下：

```shell
$ go run main.go
elapsed: 2.005725931s
```

### 3. 解决 goroutine 泄露

上面的结果来看已经实现了 2s 超时控制。但问题在于，这种实现会出现 goroutine 泄露。

在`main`方法中添加如下两行，看看结束的时候有多少个 goroutine：

```go
time.Sleep(time.Minute*2)
fmt.Println("number of goroutines:", runtime.NumGoroutine())
```

这样就能看到 goroutine 的数量了：

```shell
$ go run main.go
elapsed: 2.005725931s
number of goroutines: 1001
```

可以看到，确实发生 goroutine 泄露了。这是因为`requestWork`函数在 2s 之后就退出了，这导致其内部的`done` Channel 就没有接收者了。而`done`是一个无缓冲的 Channel，没有接收者的时候，发送者就会一直阻塞住。所以最终的 goroutine 的数量其实是`requestWork`函数内部开启的 goroutine 被阻塞而导致的。

解决办法也很简单，就是将`done`设置为缓冲区为 1 的缓冲 Channel，这样即便没有接收方，发送方也不会阻塞住了：

```go
done := make(chan error, 1)
```

Channel 不像常见的文件描述符，打开之后必须关闭，而是可以被自动回收的。而且关闭 Channel 也只是说明当前没有需要继续写入 Channel 的数据了。

再运行一遍之后可以得到如下的结果：

```shell
$ go run main.go
elapsed: 2.005725931s
number of goroutines: 1
```

### 4. 接收 panic

如果把前面的`hardWork`函数实现为如下代码：

```go
func hardWork() {
  panic("Oops")
}
```

此时如果在`main`函数中进行`defer recover`是无法接收到这个 panic 的，因为这个 panic 是`requestWork`函数中启动的 goroutine 中抛出的，所以是无法在其他的 goroutine 中捕获的。

解决方法也很简单，就是抛出 panic 的 goroutine 中进行 recover，并将捕获到的 panic 写入到一个新的 Channel 中：

```go
func requestWork(ctx context.Context, job interface{}) error {
  ctx, cancel := context.WithTimeout(ctx, time.Second*2)
  defer cancel()
  
  done := make(chan error, 1)
  panicChan := make(chan interface{}, 1)
  go func() {
    defer func() {
      if err := recover(); err != nil {
        panicChan <- p
      }
    }()
    
    done <- hardWork(job)
  }()
  
  select {
  case err := <-done:
    return err
  case p := <-panicChan:
    return p
  case <-ctx.Done()
    return ctx.Err()
  }
}
```

这样就能在`requestWork`函数中将 panic 也转成 error 返回了。

### 5. 正确的超时时长

`requestWork`函数接收了一个`context.Context`参数，这个参数可能已经有超时设置了，所以是需要判断其超时时长是否大于 2s，最终需要使用其原有的超时时长和 2s 中的较小值。

[go-zero/core/contextx](https://github.com/tal-tech/go-zero) 已经提供了简单的方法来进行处理：

```go
ctx, cancel := context.ShirnkDeadline(ctx, time.Second*2)
```

### 6. Data race

前面的`requestWork`方法都只返回了一个 error 结果，如果需要返回多个结果，就需要注意有数据竞争。

这种情况可以使用锁来解决，具体实现可以参考[go-zero/zrpc/internal/serverinterceptors/timeoutinterceptor.go](https://github.com/tal-tech/go-zero/zrpc/internal/serverinterceptors/timeoutinterceptor.go)。

