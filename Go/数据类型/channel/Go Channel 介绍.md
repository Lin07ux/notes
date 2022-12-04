> 转摘：
> 
> 1. [一文带你解密 Go 语言之通道 channel](https://mp.weixin.qq.com/s/Ih4FOi4hx4GgS8Pq7bjRJA)
> 2. [从鹅厂实例出发！分析Go Channel底层原理](https://mp.weixin.qq.com/s/nQ2SxT8dtRWjbDQccBaY1Q)

## 一、基础

Go 语言中的一大利器就是能够非常方便的使用`go`关键字开启协程进行并发处理，而并发后必然会涉及到数据通信。Go 对于协程间的通信的指导方针是：不要使用共享内存来通信，而是使用通信来共享内存。而 Go 协程进行通信的基础就是 Channel。

从使用模式上来看，就是在多个 goroutine 中借助 Channel 来传输数，实现了跨 goroutine 间的数据传输，时的各个 goroutine 都能独立运行，不会强关联，更不会相互影响对方的状态。

Channel 中的数据是按照先进先出的顺序进行存取的，严格保证取用顺序。

### 1.1 什么是 channel

在 Go 语言中，channel 可以称为通道或者管道，常见用于 goroutine+select 搭配使用，完成数据通信。

演示代码如下：

```go
func main() {
  ch := make(chan string)
  go func() {
    ch <- "煎鱼"
  }()
  
  msg := <-ch
  fmt.Println(msg)
}
```

这里，在主动开启的一个 goroutine 中将`煎鱼`字符串写入到通道变量`ch`中，然后在 main goroutine 中监听通道`ch`，并阻塞等待读取到值，也就是`煎鱼`，打印之后结束流程。

在此，Channel 承载着一个衔接器的作用：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639097927020-922978866f77.jpg)

### 1.2 基本特性

在 Go 语言中，Channel 的关键字为`chan`，数据流向的表现方式为`<-`，代码解释方向是从左到右。

通道数据有两个流向：

- 数据进入通道时，`<-`在通道变量右边
- 取出通道数据时，`<-`在通道变量左边

Channel 共有两种模式，分别是双向和单向，所以它有三种表现方式，分别是：

- 声明双向通道：`chan T`
- 声明只允许发送的通道：`chan <- T`
- 声明只允许接收的通道：`<- chan T`

Channel 中还分为无缓冲 channel 和有缓冲 channel：

* **无缓冲 Channel**：声明方式类似`make(chan int)`，其缓冲区大小为 0。在功能上其发送者和接收者都会阻塞等待，直至通信双方都准备好了，才可以发送和接收数据（内存拷贝），可以看做是**同步模式**。

    - 发送数据的时候，如果没有对应的接收者，那么发送者就进入到等待发送队列中，等待有对应的接收者唤醒它；
    - 接收数据的时候，如果没有对应的发送者，那么接收者就进入到等待接收队列中，等待有对应的发送者唤醒它。

* **有缓冲 Channel**：声明方式类似`make(chan int, 3)`，其缓冲区大小是根据所设置的值来调整。在功能上，若缓冲区未满则不会阻塞写入，写满之后发送者就会阻塞并等待；如果缓冲区不为空，则接收者可以一直读取数据，通道数据全部取出之后，接收者就需要阻塞并等待新的数据。可以看做是**异步模式**。

    - 对于发送者来说：只要缓冲区未满，发送者就可以继续发送数据，存放在缓冲区中；一旦缓冲工区满了，发送者就只能进入发送等待队列中，等待有对应的接收者接收数据之后唤醒它，然后它再把数据放在刚刚被取走数据的位置；
    - 对于接收者来说：只要缓冲区未空，接收者就可以继续从缓冲区接收数据；一旦缓冲区空了，接收者就只能进入接收等待队列中，等待有对应的发送者唤醒它，并为其提供数据。

在实际的应用场景中，两者根据业务情况选用即可，不用纠结它们是否有性能差异。无缓冲 Channel 常用于两个 goroutine 间互相同步等待的场景。

另外，Channel 的基本操作还有：

* 关闭：`close(chan)`
* 获取长度：`len(chan)`
* 获取容量：`cap(chan)`
* 非阻塞访问：`select...case...`

## 二、核心

### 2.1 设计思想

Go 语言的并发模型是 CSP(Communicating Sequential Processes，通信顺序进程)，提倡通过通信共享内存而不是通过共享内存实现通信。

goroutine 是 Go 程序并发的执行体，Channel 则是它们直接的连接，可以让一个 goroutine 发送特定值到另一个 goroutine。

在并发中交换数据无法有两种方式：加互斥锁来共享内存、先进先出（FIFO）将资源分配给等待时间最长的程序。

共享内存是 C++ 等其他语言采用的并发线程交换数据的方式，在高并发的场景下有时候难以正确的使用，特别是在超大型、巨型的程序中，容易带来难以察觉的隐藏问题。

Go 语言采用的是后者，引入 Channel 通信机制，采用 FIFO 方式将资源分配给等待时间最长的 goroutine，尽量消除数据竞争，让程序尽可能以顺序一致的方式运行。

关于让程序尽量顺序一致的含义，可以参考 Go 语言内存模型采用的一个传统的基于 happens-before 对读写竞争的含义：

1. 修改由多个 goroutines 同时访问的数据的程序必须串行化这些访问；
2. 为了实现串行访问，需要使用 Channel 操作或者其他同步原语（如 sync 和 sync/atomic 包中的原语）来保护数据；
3. go 语句创建一个 goroutine 一定发生在 goroutine 执行之前；
4. 往一个 Channel 中发送数据，一定发生在从这个 Channel 读取这个数据完成之前；
5. 从一个无缓冲 Channel 的读取数据，一定发生在往这个 Channel 发送数据完成之前；
6. 一个 Channel 的关闭，一定发生在从这个 Channel 读取到零值数据（这里是值因为 close 而返回的零值数据）之前；

如果违反了这种定义，Go 会让程序直接 panic 或阻塞，无法往后执行。

**Channel 的本质是一个有锁的环形队列**，所以它并不比通过互斥锁共享内存的性能更优秀，而是因为采用 Channel 进行协程之间的通信可以减少数据竞争。在大型程序、高并发的复杂场景下，以简单的原理实现的组件，更能让程序尽量按照符合预期的、不易出错的方式执行。

Go 中用于并发协程同步数据的组件主要分为两大类：一个是 sync 和 sync/atomic 包里面的同步原语（如`sync.Mutex`、`sync.RWMutex`、`sync.WaitGroup`等），另一个就是 Channel。Go 语言推荐的并发同步方式就是 Channel，它是一等公民。

### 2.2 整体构成

Channel 在设计上使用了环形队列来缓存数据，且包含发送方队列、接收方队列以及互斥锁等结构。整体结构图如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1670088280)

### 2.3 数据结构

Channel 的运行时的结构体是`hchan`：

```go
// src/runtime/chan.go
type hchan struct {
  qcount   uint  // total data in the queue 队列中的元素总数量
  dadaqsiz uint  // size of the circular queue 循环队列的长度
  // points to an array of dadaqsiz elements
  // 指向长度为 dataqsiz 的底层数组，仅当 Channel 为缓冲型的才有意义。
  buf      unsafe.Pointer
  elemsize uint16 // 能接收和发送的元素大小
  closed   uint32 // 是否关闭
  elemtype *_type // element type 能够接收和发送的元素类型
  sendx    uint   // send index 已发送元素在循环队列中的索引位置
  recvx    uint   // receive index 已接收元素在循环队列中的索引位置
  recvq    waitq  // list of recv waiters 接收者的 sudog 等待队列（缓冲区不足时阻塞等待的 goroutine）
  sendq    waitq  // list of send waiters 发送者的 sudog 等待队列
  
  // lock protects all fields in hchan, as well as several
  // fields in sudogs blocked on this channel.
  //
  // Do not change another G's status while holding this lock
  // (in particular, do not ready a G), as this can deadlock
  // with stack shrinking.
  lock mutex
}
```

在数据结构中，可以看到`recvq`和`sendq`，其表现为等待队列，类型为`runtime.waitq`的双向链表结构：

```go
type waitq struct {
  first *sudog
  last *sudog
}
```

而无论是队列中的`first`属性又或者是`last`属性，其类型都为`runtime.sudog`结构体，其主要的几个字段如下：

```go
// src/runtime/runtime2.go
type sudog struct {
  // The following fields are protected by the hchan.lock of the
  // channel this sudog is blocking on. shrinkstatck depends on
  // this for sudogs involved in channel ops.
  
  g *g // 指向当前的 goroutine
  
  next *sudog // 指向下一个 g
  prev *sudog // 指向上一个 g
  elem unsafe.Pointer // data element(may point to stack)
}
```

`sudog`是 Go 语言中用于存放协程状态为阻塞的 goroutine 的双向链表抽象，可以直接理解为一个正在等待的 goroutine。`sudog`是从 特殊池中分配出来的，使用`acquireSudog`和`releaseSudog`分配和释放。

G 与同步对象（指 Channel）是多对多的关系：一个 G 可以出现在许多等待队列上，因此一个 G 可能有多个 sudog；而且多个 G 可能正在等待同一个同步对象，因此一个对象可能有许多 sudog。

### 2.4 总结

1. Channel 本质上是由三个 FIFO(先进先出) 队列组成的，用于协程质检传输数据的协程安全的通道。

    FIFO 的设计是为了保障公平，让事情变的更简单，原则是让等待时间最长的协程最有资格先从 Channel 中发送或者接收数据。

2. 三个 FIFO 队列依次是 buf 循环队列、sendq 待发送者队列、recvq 待接收者队列。

    * buf 循环队列：是一个大小固定的用来存放 Channel 接收的数据的队列，其大小由初始化 Channel 时指定；
    * sendq 待发送者队列：用来存放等待发送数据到 Channel 的 goroutine 的双向链表；
    * recvq 待接收者队列：用来存放等待从 Channel 读取数据的 goroutine 的双向链表；

    其中 sendq 和 recvq 可以认为是不限大小的。
    
3. 跟函数调用传参本质上都是传值一样，Channel 传递数据的本质就是值拷贝，引用类型数据的传递也是地址拷贝。

    Channel 的数据发送和接收过程中，有从发送者 sender 栈内存地址拷贝数据到缓冲区 buf，也有从缓冲区 buf 地址拷贝数据到接收者 receiver 栈内存。
    
4. Channel 里面参数的修改不是并发安全的，包括对三个队列及其他参数的访问。

    Channel 内部的操作是需要加锁的，本质上，Channel 就是一个有锁队列。

5. Channel 的性能跟`sync.Mutex`差不多，并没有明显的优劣。

    Go 官方之所以推荐使用 Channel 进行并发协程的数据交互，是因为 Channel 的设计理念能让程序变得简单，在大型程序、高并发复杂的运行状况中也是如此。

## 三、流程

Go Channel 设计并不复杂，本质上就是一个带锁的环形队列，再加上对称的`sendq`、`recvq`等双向链表的辅助属性，就能勾画出 Channel 的基本逻辑流转模型。

无缓冲 Channel 的发送-接收流程如下图所示：

![无缓冲 Channel](http://cnd.qiniu.lin07ux.cn/markdown/1639481525400-8f58ddacf6bd.jpg)

有缓冲 Channel 的发送-接收流程如下图所示：

![缓冲 Channel](http://cnd.qiniu.lin07ux.cn/markdown/1639481770524-10c401856ca0.jpg)

在具体的数据传输上，都是围绕着“边界上下限处理，使用互斥锁，阻塞/非阻塞，缓冲/非缓冲，缓存出队列，拷贝数据，解互斥锁，协程调度“在不断的流转处理。在基本逻辑上也是相对重合的，因为发送和接收、创建和关闭总是相对的。

