> 转摘：[Go 面试官：for select 时，如果通道已经关闭会怎么样？如果 select 中只有一个 case呢？](https://mp.weixin.qq.com/s/59qdNpqOzMXWY_jUOddNow)

### 1. 问题

`for`循环中使用`select`语句时，如果通道已经关闭会怎么样？如果`select`中只有一个`case`又会怎样？

### 2. 答案

1. `for...select`时，如果其中一个`case`的通道已经关闭，则每次都会执行到这个`case`；
2. 如果`select`里只有一个`case`，而这个`case`被关闭了，则会出现死循环。

### 3. 解析


#### 3.1 for...select 被关闭的通道

当一个 Channel 被关闭时，从其中取值（`value, ok := <-channel`）时，总是会取到零值，而且不会被阻塞。

![](http://cnd.qiniu.lin07ux.cn/markdown/1640067522112-04afe50eef59.jpg)

所以在`for...select`代码中读取关闭 Channel 的`case`总是会被选中执行：

```go
const fmat = "2006-01-02 15:04:05"

func main() {
  c := make(chan int)
  go func() {
    time.Sleep(1 * time.Second)
    c <- 10
    close(c)
  }()
  
  for {
    select{
    case x, ok := <-c:
      fmt.Printf("%v,通道读取到：x=%v,ok=%v\n", time.Now().Format(fmat), x, ok)
      time.Sleep(500 * time.Millisecond)
    default:
      fmt.Printf("%v,没读到信息进入default\n", time.Now().Format(fmat))
      time.Sleep(500 * time.Millisecond)
    }
  }
}
```

这段代码中，Channel c 是一个缓冲区为 0 的通道，并且开启了一个 goroutine 在 1s 后向其写入 10 然后关闭掉。在`main`中通过`x, ok := <-c`方式接收 Channel c 里的值，所以在读取到写入的 10 之后，后续会一直读取出 0 值。

执行的结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1640067055301-f1268faf97f4.jpg)

#### 3.2 不从关闭的通道中读取

如果不想从关闭的通道中读取出 0 值，可以将通道重置为 nil。这样，从一个 nil Channel 中读取数据时，总是会被阻塞住。

```go
const fmat = "2006-01-02 15:04:05"

func main() {
  c := make(chan int)
  
  go func() {
    time.Sleep(1 * time.Second)
    c <- 10
    close(c)
  }()
  
  for {
    select {
    case x, ok := <-c:
      fmt.Printf("%v,通道读取到：x=%v,ok=%v\n", time.Now().Format(fmat), x, ok)
      time.Sleep(500 * time.Millisecond)
      if !ok {
        c = nil
      }
    default:
      fmt.Printf("%v,没读到信息进入default\n", time.Now().Format(fmat))
      time.Sleep(500 * time.Millisecond)
    }
  }
}
```

这样，在从通道读取到数据之后，判断`ok`是否为 true。不为 true 的话表示通道已经关闭，此时就可以将 Channel c 置为 nil。之后再循环的时候，这个`case`就会被阻塞住，从而会自动执行`default`语句。这样就解决不断重复读取已关闭 Channel 的问题。

![](http://cnd.qiniu.lin07ux.cn/markdown/1640067745321-33f4afb2d717.jpg)

#### 3.3 如果 select 只有一个从已关闭的 Channel 读取数据的 case 会怎样

很明显，因为通道关闭后，一直能无阻塞的读取到零值，所以这时会一直执行这个 case，和 3.1 节中的结果很类似。

```go
const fmat = "2006-01-02 15:04:05"

func main() {
  c := make(chan int)
  
  go func() {
    time.Sleep(1 * time.Second)
    c <- 10
    close(c)
  }()
  
  for {
    select {
    case x, ok := <-c:
      fmt.Printf("%v,通道读取到：x=%v,ok=%v\n", time.Now().Format(fmat), x, ok)
      time.Sleep(500 * time.Millisecond)
    }
  }
}
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1640067929211-0573ea65f2a3.jpg)

#### 3.4 select 中只有一个 case 且在 Channel 关闭后置为 nil 会怎样

这个就是结合 3.3 和 3.2 节的情况。因为 nil Channel 的读取会一直阻塞，所以这个`select`就会被一直阻塞住。这回导致整个成功死锁超时而崩溃。

```go
const fmat = "2006-01-02 15:04:05"

func main() {
  c := make(chan int)
  
  go func() {
    time.Sleep(1 * time.Second)
    c <- 10
    close(c)
  }()
  
  for {
    select {
    case x, ok := <-c:
      fmt.Printf("%v,通道读取到：x=%v,ok=%v\n", time.Now().Format(fmat), x, ok)
      time.Sleep(500 * time.Millisecond)
      if !ok {
        c = nil
      }
    }
    fmt.Printf("%v,打印些东西\n", time.Now().Format(fmat))
  }
}
```

这段代码会先打印出通道中的内容，然后会因为通道变为 nil 而导致死锁，进而崩溃。执行的结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1640068110624-a5598e072add.jpg)

### 4. 总结

* `select`中如果任意某个通道有值可以读取时，其就会被执行，其他的会被忽略；
* 如果没有`default`语句，`select`将有可能阻塞，直到某个通道有值可以运行。所以`select`中最好设置一个`default`，否则将会有一直阻塞的风险。

