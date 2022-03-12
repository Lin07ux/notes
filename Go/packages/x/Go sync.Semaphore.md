> 转摘：[教妹子学 Go 并发原语：啥是 Semaphore ？](https://mp.weixin.qq.com/s/mXLJBmjMl05s-aMEcECeBw)

信号量是并发编程中常见的同步机制，在 Go 标准库的并发原语中使用频繁，比如 Mutex、WaitGroup 等。这些并发原语的实现都有信号量的影子。

Go 在扩展包`golang.org/x/sync/semaphore`中提供了 Semaphore 信号量的相关功能。

## 一、介绍

### 1.1 信号量是什么

维基百科上是这样解释信号量的：

> 信号量的概念是计算机科学家 Dijkstra (Dijkstra 算法的发明者)提出来的，广泛应用在不同的操作系统中。系统中，会给每一个进程一个信号量，代表每个进程当前的状态。未得到控制权的进程，会在特定的地方被迫停下来，等待可以继续进行的信号到来。

下文中用 G 代表 goroutine。

通俗点解释就是，信号量通常使用一个整型变量 S 表示一组资源：当 G 完成对此信号量的等待（wait）时，S 就减 1；当 G 完成对此信号量的释放（release）时，S 就加 1。当计数值为 0 时，G 调用 wait 等待该信号量会阻塞，除非 S 又大于 0，等待的 G 才会解除阻塞，成功返回。

举个例子，假如图书馆有 10 本《Go 语言编程之旅》，有 1 万个人都想读这本书，“僧多粥少”。所以，图书管理员会先让这 1 万个人进行登记，按照登记的顺序借阅此书。如果书全部被借走，那么其他想看此书的人就需要等待。如果有人还书了，图书管理员就会通知下一位同学来借阅这本书。这里的资源就是《Go 语言编程之旅》这十本书，想读此书的同学就是 goroutine，图书管理员就是信号量。

从上面的解释中可以得知什么是信号量，其实信号量就是一种变量或者抽象数据类型，用于控制并发系统中多个进程对公共资源的访问，访问具有原子性。

信号量主要分为两类：

* **计数信号量**：上面说的图书馆借书的例子就是计数信号量，它的计数可以是任意一个正整数；
* **二值信号量**：其实这是一种特殊的计数信号量，其值只有 0 和 1，相当于互斥量。当值为 1 时资源可用，当值为 0 时资源被锁住，进程阻塞无法继续执行。

### 1.2 有什么操作

信号量定义有两个操作：

* **P** 操作：减少信号量的计数值；
* **V** 操作：增加信号量的计数值。

通常初始化时，将信号量 S 指定数值为 n，就像是一个有 n 个资源的池子。P 操作相当于请求资源，如果资源可用就立即返回，如果没有资源或者资源不足，那么 G 就会阻塞等待。V 操作则会释放其所持有的的资源，把资源返回给信号量。

信号量的值除了初始化的操作以外，只能由 P/V 操作改变。

一般用信号量保护一组资源，比如数据库连接池、几个打印机资源等。如果信号量蜕变成二值信号量，那么它的 P/V 就和互斥锁的 Lock/Unlock 一样了。

## 二、实现

### 2.1 Semaphore.Weighted

Go 语言中在`src/runtime/sema.go`文件中实现了信号量功能 Semaphore，其 P/V 操作对应的信号量的函数如下：

```go
func runtime_Semacquire(s *uint32)
func runtime_SemacquireMutex(s * uint32, lifo bool, skipframes int)
func runtime_Semrelease(s *uint32, handoff bool, skupframes int)
```

不过这几个函数是 Go 运行时内部使用的，并没有封装暴露成一个对外的信号量并发原语，不能直接使用。

Go 在其扩展包中提供了信号量 semaphore，但是这个信号量的类型并不叫 Semaphore，而是叫 **Weighted**。

这是一个带权重的信号量，其是使用互斥锁 + List 来实现的：互斥锁实现其它字段的保护，而 List 实现了一个等待队列，等待者的通知是通过 Channel 的通知机制来实现的。

### 2.2 结构体

`Weighted`结构体的定义如下：

```go
type Weighted struct {
  size    int64      // 最大资源个数，初始化的时候指定
  cur     int64      // 计数器，当前已使用的资源数
  mu      sync.Mutex // 互斥锁，对字段保护
  waiters list.List  // 等待者列表，当前处于阻塞等待的请求者 gouroutine
}
```

其中`waiters`存储的数据是 waiter 类型对象，定义如下：

```go
type waiter struct {
  n     int64           // 调用者申请的资源数
  ready chan<- struct{} // 
}
```

`waiter.ready`是一个 Channel，在资源不足时，申请资源的请求者会监听这个 Channel，从而实现阻塞。而当调用者可以获取到信号量资源时，close chan，调用者便会收到通知，从而成功返回。

初始化一个权重信号量的方法是`NewWeighted`，定义如下：

```go
func NewWeighted(n int64) *Weighted {
  w := &Weighted{size: n}
  return w
}
```

### 2.3 Acquire() 阻塞获取资源

`Acquire()`方法用于阻塞式的获取资源，也就是说，如果当前可用资源足够，则可以立即申请到，否则会被阻塞，直到资源足够当前调用者申请的资源数。

源码如下：

```go
func (s *Weighted) Acquire(ctx context.Context, n int64) error {
  s.mu.Lock()
  
  // 有可用资源就直接申请并返回
  if s.size-s.cur >= n && s.waiters.Len() == 0 {
    s.cur += n
    s.mu.Unlock()
    return nil
  }
  
  // 后面就是没有足够的资源的情况
  
  // 如果申请的资源超过总资源数量，则直接返回错误
  if n > s.size {
    s.mu.Unlock()
    <-ctx.Done()
    return ctx.Err()
  }
  
  // 资源不足，构造 waiter，将其加入到等待队列的末尾
  // ready channel 用于通知阻塞的调用者有资源可用
  // 由释放资源的 goroutine 负责 close，起到消息通知的作用
  ready := make(chan struct{})
  w := waiter{n: n, ready: ready}
  elem := s.waiters.PushBack(w)
  s.mu.Unlock()
  
  // 让调用者陷入 select 阻塞，除非收到外部 ctx 的取消信号或者被通知有消息可用
  select {
  case <-ctx.Done(): // 收到外部 ctx 的控制信号
    err := ctx.Err()
    s.mu.Lock()
    select {         // 再次对 ready 信号进行判断
    case <-ready:
      err = nil
    default:         // 依旧未能申请到资源则删除自身
      isFront := s.waiters.Front() == elem
      s.waiters.Remove(elem)
      if isFront && s.size > s.cur {
        s.notifyWaiters()
      }
    }
    s.mu.Unlock()
    return err
  }
  case <-ready:       // 获取到资源则可以让调用者继续执行其后续逻辑了
    return nil
}
```

`Acquire()`方法的逻辑主要就是在资源足够的时候为调用者分配资源数（`s.cur += n`），不够的时候，通过 Channel 将调用者进行阻塞，并在合适（有足够的的资源或被外部取消）的时候重新继续唤醒调用者进行后续执行。

主要的难点在于让调用者先入阻塞和恢复的过程。`Acquire()`方法通过使用`select`语法，可以在外部控制器`ctx`没有取消，而且信号量资源也不够的时候，保持等待，从而阻塞了调用者的调用。

当有别的 goroutine 将其申请的资源归还时，会主动的触发`notifyWaiters()`操作，该操作会将 waiters 队列中排在第一个的 waiter 的 ready Channel 关闭，从而唤醒该 waiter 对应的 goroutine 继续执行。

`Acquire()`方法中`select`语句中的`<-ready` case 就是在监听这种情况的。但需要注意的是：在`<-ctx.Done()` case 中，还会再次判断一下`<-ready`是否准备好了。这是因为 select 语法在不止一个 case 准备好的时候，会随机选择一个 case 进行执行。所以如果`<-ctx.Done()`和`<-ready`同时准备好时，可能会执行到了`<-ctx.Done()` case 上。这样就需要再次判断一下`<-ready`是不是也准备好了，以避免错过 ready 信号的通知。

而在确定`<-ready`没有准备好时，除了将自身从 waiters 列表中移除，还需要判断下自己是不是 waiters 列表中的第一个等待者。如果自己是第一个等待者，而且当前还有可用的资源，也需要触发一下`notifyWaiters()`的调用。因为如果自己申请的资源太多而使后续的申请都阻塞，那当自己取消申请而且还有可用资源的时候，就有必要通知后续的 waiter，否则会使队列中的其他 waiter 白白阻塞等待本已够用的资源。

通过`Acquire()`方法的源码也能看到，它相当于信号量的 P 操作，而且可以一次获取多个资源，如果没有足够的资源则会被阻塞。

### 2.4 TryAcquire() 非阻塞获取资源

`TryAcquire()`方法也是用于申请资源，但是在资源不足的时候，不会阻塞：

```go
func (s *Weighted) TryAcquire(n int64) bool {
  s.mu.Lock()
  success := s.size-s.cur >= n && s.waiters.Len() == 0
  if success {
    s.cur += n
  }
  s.mu.Unlock()
  return success
}
```

这个方法比较简单，非阻塞地获取指定数量的资源。如果当前没有足够的空闲资源，就直接返回 false。

### 2.5 Release() 释放占用的资源

有申请就要有释放，否则信号量资源将永远被占用。

`Release()`方法就相当于信号量的 V 操作，让申请者主动释放占用的资源。源码如下：

```go
func (s *Weighted) Release(n int64) {
  s.mu.Lock()
  s.cur -= n // 释放占用的资源
  if s.cur < 0 {
    s.mu.Unlock()
    panic("semaphore: released more than held")
  }
  s.notifyWaiters() // 唤醒等待请求资源的 goroutine
  s.mu.Unlock()
}
```

可以看出，释放资源的逻辑也很简单，而且能够允许一次释放多个资源数量，但是需要确保释放的资源数量不能超出全部已申请的资源数量，否则就会触发 panic。

而且，释放资源时会主动的执行`notifyWaiters()`方法，来唤醒后面等待资源申请的 goroutine，避免无限等待。

### 2.6 notifyWaiters 通知等待者

这个方法是用在当有足够的资源的时候，就唤醒排在队首的 waiter。在前面的`Acquire()`方法中就涉及到了`notifyWaiters()`方法的调用。

源码如下：

```go
func (s *Weighted) notifyWaiters() {
  for {
    next := s.waiters.Front() // 获取队首元素
    if next == nil {
      break
    }
    
    w := next.Value.(waiter)
    if s.size-s.cur < w.n {   // 资源数量不满足申请的数量
      break
    }
    
    // 否则，说明数量足够，则为 waiter 分配资源，并将其移出等待列表，然后唤醒
    s.cur += w.n
    s.waiters.Remove(next)
    close(w.ready)
  }
}
```

`notifyWaiters()`方法的主体就是一个无限的`for`循环，从 waiters 队列的头部开始遍历并分配资源，直到队列中没有等待者，或者等待者申请的资源数量超出可用的资源数量。

在`notifyWaiters()`方法中，对于资源数量满足等待者申请数量时，在唤醒申请者之前会先为调用者分配资源（`s.cur += w.n`），然后将其移出等待队列，这是因为调用者被唤醒之后，并不会做这些操作（见`Acquire()`方法中的`select`代码）。

另外，`notifyWaiters()`方法的执行并不需要加锁，这是因为触发该方法的地方，都已经提前申请好锁了，所以这里不需要也不能再申请锁。

可以发现，`notifyWaiters`方法是按照 FIFO 方式唤醒调用者的。这样做的目的是为了避免调用者出现“饿死”的情况。比如，当释放 10 个资源的时候，如果第一个等待者需要 11 个字眼，那么队列中的所有调用者都会继续等待，即使队列中有的等待者只需要 1 个资源。如果不采用 FIFO 方式，那么资源就可能总是被那些请求资源数小的调用者获取，这样就导致请求资源数量巨大的调用者就没有机会获得资源了。

## 三、使用

### 3.1 使用示例

下面使用官方提供的乌拉兹猜想进行信号量的使用说明。

“乌拉兹猜想”说的是：对于任意一个正整数，如果它是奇数，则对它乘以 3 再加 1；如果它是偶数，则对它除以 2。如此循环，最终都能够得到 1。

下面的例子需要实现的是，对于给出的正整数，计算循环多少次之后能得到 1。

代码如下：

```go
func mian() {
  var (
    maxWorkers = runtime.GOMAXPROCS(0) // worker 数量
    sem        = semaphore.NewWeighted(int64(maxWorkers)) // 信号量
    out        = make([]int, 32) // 任务数
  )
  ctx := context.TODO()
  
  for i := range out {
    if err := sem.Acqure(ctx, 1); err != nil {
      log.Printf("Failed to acquire semaphore: %v", err)
      break
    }
    
    go func() {
      defer sem.Release(1)
      out[i] = collatzSteps(i + 1)
    }(i)
  }
  
  // 等待所有的任务执行完成，也可以通过 WaitGroup 实现
  if err := sem.Acquire(ctx, int64(maxWorkers)); err != nil {
    log.Printf("Failed to acquire semaphore: %v", err)
  }
  
  fmt.Println(out)
}

func collatzSteps(n int) (steps int) {
  if n <= 0 {
    panic("nonpositive input")
  }
  
  for ; n > 1; steps++ {
    // 整数溢出
    if steps < 0 {
      panic("too many steps")
    }
    
    if n%2 == 0 {
      n /= 2
      continue
    }
    
    const maxInt = int(^uint(0) >> 1)
    if n > (maxInt-1)/3 {
      panic("overflow")
    }
    
    n = 3*n + 1
  }
  
  return steps
}
```

上面的 diam 创建数量与 CPU 核数相同的 worker，假设是 4，相当于池子中只有 4 个资源可用。每个 worker 处理完一个整数，才能继续处理下一个，相当于控制住了并发数量。

在等待最终结果的地方，是一次性申请全部的资源数量，这样就相当于在 Weighted 的 waiters 的末尾添加了一个等待全部资源的调用者。只有在前面全部的申请中都释放了占用的资源时，最后的这个等待者才会被激活。

最终的输出如下：

```
[0 1 7 2 5 8 16 3 19 6 14 9 9 17 17 4 12 20 20 7 7 15 15 10 23 10 111 18 18 18 106 5]
```

### 3.2 注意事项

在使用 Go 的 Semaphore 的过程中，很容易就出现错误。比如：请求的资源数比最大的资源数还大，就会直接触发 panic；释放资源时，如果释放的数量比当前全部被占用的数量还大，也会出现 panic；而且如果释放一个负数时，就会导致资源永久被持有。

使用时需要注意防范以下的错误：

* 请求的资源数量大于最大资源数；
* 请求了资源但是未释放；
* 长时间持有资源，即便未使用；
* 释放了未请求过的资源。

使用一项技术，保证不出错的前提是正确的使用它。对于信号量来说也是一样，使用信号量时应该格外小心，**确保正确地传递参数，请求多少资源就释放多少资源**。

