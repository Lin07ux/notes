> 转摘：[跟读者聊 Goroutine 泄露的 N 种方法，真刺激！](https://mp.weixin.qq.com/s/ql01K1nOnEZpdbp--6EDYw)

Goroutine 的使用门槛非常的低，使用关键字`go`随时就能启动一个。而且 goroutine 本身在 Go 语言的标准库、复合类型、底层源码中应用广泛。例如：HTTP Server 对每一个请求的处理就是启动一个 goroutine 去执行。

这也会造成 goroutine 的误用情况，出现 goroutine 泄露。泄露的原因大多集中在：

* goroutine 内正在进行 Channel、Mutex 等读写操作，但由于逻辑问题，某些情况下一会一直被阻塞；
* goroutine 内的业务逻辑进入死循环，资源一直无法释放；
* goroutine 内的业务逻辑进入长时间等待，有不断新增的 goroutine 进入等待。

接下来会使用一些例子来说明这些泄露的情况。

## 一、Channel 使用不当

goroutine + channel 是最经典的组合，因此不少泄露都出现于此。

下面的例子都比较简单，但在实际业务场景中，一般会更加复杂。基本是一大堆业务逻辑里，有一个 Channel 的读写操作出现了问题就造成 goroutine 的阻塞和泄露。

### 1.1 发送不接收

```go
func main() {
  for i := 0; i < 4; i++ {
    queryAll()
    fmt.Printf("goroutines: %d\n", runtime.NumGoroutine())
  }
}

func queryAll() int {
  ch := make(chan int)
  for i := 0; i < 3; i++ {
    go func() {
      ch <- query()
    }()
  }
  return <-ch
}

func query() int {
  n := rand.Intn(100)
  time.Sleep(time.Duration(n) * time.Millisecond)
  return n
}
```

输出结果：

```
goroutines: 3
goroutines: 5
goroutines: 7
goroutines: 9
```

在这个例子中，调用了多次`queryAll()`方法，并在 for 循环中利用 goroutine 调用`query()`方法。其重点在于调用`query()`方法后的结果会写入到`ch`变量中，接收成功后再返回`ch`变量。

但是由于`queryAll()`方法中会向`ch`中写入 3 次数据，却只取出一次，这就导致每调用一次`queryAll()`就会有 2 个 goroutine 出现泄露。

### 1.2 接收不发送

```go
func main() {
  defer func() {
    fmt.Println("goroutines: ", runtime.NumGoroutine())
  }()
  
  ch := make(chan struct{})
  go func() {
    ch <- struct{}{}
  }()
  
  time.Sleep(time.Second)
}
```

输出结果：

```
goroutines:  2
```

在这个例子中，与“发送不接收”是对等的：会从 Channel 中接收值，但是并没有向 Channel 中发送值。这就会造成接收的 goroutine 一直被阻塞。

### 1.3 nil channel

```go
func main() {
  defer func() {
    fmt.Println("goroutines: ", runtime.NumGoroutine())
  }()
  
  var ch chan int
  go func() {
    <-ch
  }()
  
  time.Sleep(time.Second)
}
```

输出结果：

```
goroutines:  2
```

这个例子中的`ch`变量是一个未被初始化的 nil Channel。对于一个 nil Channel，无论是读还是写操作，都会造成阻塞。

## 二、锁使用不当

### 2.1 互斥锁忘记解锁

```go
func main() {
  total := 0
  defer func() {
    time.Sleep(time.Second)
    fmt.Println("total: ", total)
    fmt.Println("goroutines: ", runtie.NumGoroutine())
  }()
  
  var mutex sync.Mutex
  for i := 0; i < 10; i++ {
    go func() {
      mutex.Lock()
      total += 1
    }
  }
}
```

输出结果：

```
total:  1
goroutines:  10
```

在这个例子中，每个 goroutine 中都对互斥锁`sync.Mutex`加锁了，但是没有任何一个地方释放锁，因此导致只有第一个 goroutine 能成功加锁，其他的 goroutine 都无法加锁而阻塞。

一般在 Go 工程中，建议在加锁之后立即使用`defer`来释放锁：

```go
func main() {
  total := 0
  defer func() {
    time.Sleep(time.Second)
    fmt.Println("total: ", total)
    fmt.Println("goroutines: ", runtie.NumGoroutine())
  }()
  
  var mutex sync.Mutex
  for i := 0; i < 10; i++ {
    go func() {
      mutex.Lock()
      defer mutex.Unlock()
      total += 1
    }
  }
}
```

### 2.2 同步锁使用不当

```go
func handle(v int) {
  var wg sync.WaitGroup
  wg.Add(5)
  for i := 0; i < v; i++ {
    go func() {
      fmt.Println("脑子进煎鱼了")
      wg.Done()
    }()
  }
  wg.Wait()
}

func main() {
  defer func() {
    fmt.Println("goroutines: ", runtime.NumGoroutine())
  }()
  
  go hanle(3)
  time.Sleep(time.Second)
}
```

在这个例子中，调用`sync.WaitGroup`的地方，`wg.Add`的数量和`wg.Done`的数量不匹配，因此在调用`wg.Wait()`方法后就一直陷入等待了。

在 Go 工程中，一般建议每次调用`wg.Add()`方法时只传入 1 作为参数，且在其后面尽早的调用`wg.Done()`方法：

```go
func handle(v int) {
  var wg sync.WaitGroup
  for i := 0; i < v; i++ {
    wg.Add(1)
    go func() {
      defer wg.Done()
      fmt.Println("脑子进煎鱼了")
    }()
  }
  wg.Wait()
}

func main() {
  defer func() {
    fmt.Println("goroutines: ", runtime.NumGoroutine())
  }()
  
  go hanle(3)
  time.Sleep(time.Second)
}
```

## 三、其他

### 3.1 http.Client 超时

下面的例子是使用 Go 的标准库 http 去获取网络数据：

```go
func main() {
  for {
    go func() {
      _, err := http.Get("https://www.xxx.com/")
      if err != nil {
        fmt.Printf("http.Get err: %v\n", err)
      }
      // do something
    }()
    
    time.Sleep(time.Second * 1)
    fmt.Println("goroutines: ", runtime.NumGoroutine())
  }
}
```

输出结果：

```
goroutines:  5
goroutines:  9
goroutines:  13
goroutines:  17
goroutines:  21
goroutines:  25
...
```

这段代码在业务逻辑上并没有太大的问题，但是没有考虑网络资源的获取时间问题。如果请求的资源长时间未返回，而 Go 中`http.Client`默认是没有设置超时时间的，那么就会造成 goroutine 一直阻塞在`http.Get()`方法的调用上。这样就会造成 goroutine 数量的持续增长。

在 Go 工程中，建议至少对`http.Client`设置超时时间：

```go
httpClient := http.Client{
  Timeout: time.Second * 15,
}
```

最好也最一些限流、熔断等错误，以防突发流量造成依赖崩塌。


