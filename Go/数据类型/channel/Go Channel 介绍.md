> 转摘：
> 
> 1. [一文带你解密 Go 语言之通道 channel](https://mp.weixin.qq.com/s/Ih4FOi4hx4GgS8Pq7bjRJA)
> 2. [多图详解Go中的Channel源码](https://mp.weixin.qq.com/s/S9zkYIE2U6Xjx9R4JwTJ_w)

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

Channel 中还分为无缓冲 channel 和缓冲 channel：

* 无缓冲 channel：声明方式类似`make(chan int)`，其缓冲区大小为 0。在功能上其发送者和接收者都会阻塞等待，直至通信双方都准备好了，可以发送和接受数据。

    - 发送数据的时候，如果没有对应的接收者，那么发送者就进入到等待发送队列中，等待有对应的发送者唤醒它。

    - 接收数据的时候，如果没有对应的发送者，那么接收者就进入到等待接收队列中，等待有对应的接收者唤醒它。

* 缓冲 channel：声明方式类似`make(chan int, 3)`，其缓冲区大小是根据所设置的值来调整。在功能上，若缓冲区未满则不会阻塞写入，写满之后发送者就会阻塞并等待；如果缓冲区不为空，则接收者可以一直读取数据，通道数据全部取出之后，接受者就需要阻塞并等待新的数据。

    - 对于发送者来说：只要缓冲区未满，发送者就可以继续发送数据，存放在缓冲区中；一旦缓冲工区满了，发送者就只能进入发送等待队列中，等待有对应的接收者接收数据之后唤醒它，然后它再把数据放在刚刚被取走数据的位置。
    
    - 对于接收者来说：只要缓冲区未空，接收者就可以继续从缓冲区接收数据；一旦缓冲区空了，接收者就只能进入接收等待队列中，等待有对应的发送者唤醒它，并为其提供数据。

无缓冲 Channel 常用于两个 goroutine 间互相同步等待的场景。

在实际的应用场景中，两者根据业务情况选用即可，不用纠结它们是否有性能差异。

## 二、本质

**Channel 的本质是一个有锁的环形队列**。

### 2.1 基本原理

Channel 在设计上使用了环形队列来缓存数据，且包含发送方队列、接收方队列以及互斥锁等结构。整体结构图如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639099133328-f226e2aff261.jpg)

### 2.2 数据结构

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
`sudogo`是 Go 语言中用于存放协程状态为阻塞的 goroutine 的双向链表抽象，可以直接理解为一个正在等待的 goroutine。

## 三、实现原理

Channel 有四大操作：创建、发送、接受、关闭。

### 3.1 创建 channel

创建 Channel 很简单：

```go
ch := make(chan string)
```

在编译后会对应`runtime.makechan`或`runtime.makchan64`方法。

`makechan64`是缓冲区长度类型为 int64 时对应的创建方法，其在`makechan`的基础上做了一些判断：

```go
// src/runtime/chan.go
func makechan64(t *chantype, size int64) *hchan {
  if int64(int(size) != size) {
    panic(plainError("makechan: size out of range"))
  }
  
  return makechan(t, int(size))
}
```

所以`makechan`才是真正的创建`hchan`实例的方法：

```go
// src/runtime/chan.go 省略部分错误检测
func makechan(t *chantype, int size) *hchan {
  elem := t.elem
  mem, overflow := math.MulUintptr(elem.size, uintptr(size))
  
  var c *hchan
  switch {
  case mem == 0:
    // chan 的 size 或者元素的 size 是 0，不需要创建 buf 的空间
    c = (*hchan)(mallocgc(hchanSize, nil, true))
    // Race detector
    c.buf = c.raceaddr()
  case elem.ptrdata == 0:
    // 元素不是指针，需要为 buf 创建空间
    c = (*hchan)(mallocgc(hchanSize+mem, nil, true))
    c.buf = add(unsafe.Pointer(c), hchanSize)
  default:
    // 元素是指针，则分别为 hchan 和 buf 申请空间
    c = new(hchan)
    c.buf = mallocgc(mem, elem, true)
  }
  
  c.elemsize = uint16(elem.size)
  c.elemtype = elem
  c.dataqsiz = uint(size)
  lockInit(&c.lock, lockRankHchan)
  
  return c
}
```

创建 Channel 的逻辑主要分为三大块：

* 当前 Channel 不存在缓冲区，也就是元素数量为 0 的情况下，会调用`mallocgc`方法分配一段连续的内存空间作为`hchan`结构体实例的空间，而不必创建 buf 的空间；
* 当前 Channel 存储的类型不存在指针引用，会申请一块连续的内存，作为`hchan`数据结构和 buf 的内存空间。其中，buf 的空间就直接紧跟着 hchan 结构体的空间；
* 其他情况下，也就是 Channel 存储的类型中存在指针引用，则需要分别为`hchan`和 buf 分配空间。

从整体上来说，`makechan`方法的逻辑比较简单，就是创建`hchan`并分配合适的`buf`大小的堆上内存空间。

另外，Channel 的创建都是调用`mallocgc`方法，也就是 Channel 都是创建在堆上的。因此 Channel 是会被 GC 回收的，所以并不总是需要用`close`方法来进行显式的关闭了。

### 3.2 发送数据

Channel 发送数据的示例代码如下：

```go
ch <- "煎鱼"
```

其在编译器翻译后，对应的是`runtime.chansend1`方法：

```go
func chansend1(c *hchan, elem unsafe.Pointer) {
  chansend(c, elem, true, getcallerpc())
}
```

对于 select 操作：

```go
select {
case c <- v:
  // ...
default:
  // ...
}
```

在编译后对应的是`runtime.selectnbsend`方法：

```go
func selectnbsend(c *hcahn, elem unsafe.Pointer) (selected bool) {
  return chansend(c, elem, false, getcallerpc())
}
```

可以看到，真实调用的都是`chansend`方法，签名如下：

```go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool
```

参数如下：

* `c *hcahn` Channel 实例
* `ep unsafe.Pointer` 发送数据的指针
* `block bool` 发送不能立即成功时是否需要阻塞
* `callrpc uintptr` 发送数据的调用方的 PC 值，用于后续的返回跳转

其中，`block`参数会指定在以下情况下是否需要阻塞发送：

1. 向无缓冲的 Channel 发送数据，且当前无接收者；
2. 向有缓冲的 Channel 发送数据，且缓冲 Channel 已满；
3. 向一个 nil Channel 发送数据（此时并不会引发 panic，只是后续无法再被唤醒了）。

该方法代码比较长，下面进行分段分析。

#### 3.2.1 前置处理

在第一部分中，先看下 Channel 发送的一些前置判断和处理：

```go
// src/runtime/chan.go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool {
  if c == nil {
    if !block {
      return false
    }
    gopark(nil, nil, waitReasonChanSendNilChan, traceEvGoStop)
    throw("unreachable")
  }
  
  if !block && c.closed == 0 && full(c) {
    return false
  }
  
  // 省略一些相关调试
  ...
}

func full(c *hchan) bool {
  if c.dataqsiz == 0 {
    return c.recvq.first == nil
  }
  
  return c.qcount == c.dataqsiz
}
```

一开始，`chansend`方法会在先判断当前的 Channel 是否为 nil。如果为 nil，在逻辑上来讲就是向 nil Channel 发送数据，此时就会调用`gopark`方法使得当前 Goroutine 休眠，进而出现死锁崩溃，表象就是出现`panic`事件来快速失败。

接着，对非阻塞的 Channel 进行一个上限判断，看看是否快速失败。失败的场景条件如下：

1. 非阻塞状态
2. 通道没有关闭
3. 无缓冲且无接收者，或者有缓冲但是缓冲区已满。

这一部分是不加锁就先进行的判断，所以被称为 fast path。因为加锁是一个很重的操作，所以能够在加锁之前返回的判断就在加锁之前进行处理是最好的。

#### 3.2.2 加锁

在完成了 Channel 的前置判断后，即将在进入发送数据的处理前，Channel 会先加上互斥锁，保证并发安全：

```go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool {
  ...
  // 加锁
  lock(&c.lock)
  // 再次判断是否已关闭
  if c.closed != 0 {
    unlock(&c.lock)
    panic(plainError("send on closed channel")
  }
  ...
}
```

在加锁之后，会再次判断一下 Channel 是否已经处于 closed 状态。这是因为，在前面的 fast path 判断之后、加锁之前，可能 Channel 的 closed 状态被修改过了，所以需要再次判断以避免状态问题。

#### 3.2.3 直接发送

在一切正常的情况下，会先尝试进行直接发送：

```go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool {
  ...
  if sg := c.recvq.dequeue(); sg != nil {
    send(c, sg, ep, func() { unlock(&c.lock) }, 3)
    return true
  }
  ...
}
```

这里是先从 recvq 中尝试取出一个接收者，如果取到了就直接将数据传递给该接收这。

这种情况是最为基础的，当前 Channel 有正在阻塞等待的接收方，说明此时的缓冲区是空的，那么就可以直接将当前的数据发送给第一个等待接收的接收者就可以了。

#### 3.2.4 缓冲发送

非直接发送就需要考虑第二种情况了，判断 Channel 缓冲区中是否还有空间：

```go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool {
  ...
  if c.qcount < c.dataqsiz {
    qp := chanbuf(c, c.sendx)
    typedmemmove(c.elemtype, qp, ep)
    c.sendx++
    if c.sendx == c.dataqsiz {
      c.sendx = 0
    }
    c.qcount++
    unlock(&c.lock)
    return true
  }
  
  if !block {
    unlock(&c.lock)
    return false
  }
  ...
}
```

这里会对缓冲区进行判定（`qcount`和`dataqsiz`字段），以此识别缓冲区的剩余空间。然后会进行如下操作：

* 调用`chanbuf`方法，以此获得底层缓冲数据中位于`sendx`索引的元素指针；
* 调用`typedmemmove`方法，将所需发送的数据拷贝到缓冲区中；
* 对`sendx`索引进行自增加 1，同时若`sendx`与`dataqsiz`大小一致，则归 0（环形队列）；
* 将队列中的总数据自增 1；
* 解除互斥锁，返回结果。

至此，针对缓冲区的数据操作完成。

但若没有走进缓冲区处理的情况，则会判断当前是是否阻塞 Channel。如果是非阻塞状态，将会解锁并直接返回失败。

图示如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639111055825-56ae15e793bd.jpg)

#### 3.2.5 阻塞发送

在进行了各式各样的层层筛选后，接下来进入阻塞等待发送的过程：

```go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool {
  ...
  gp := getg()
  mysg := acquireSudog()
  mysg.releasetime = 0
  if t0 != 0 {
    mysq.releasetime = -1
  }
  
  mysg.elem = ep
  mysg.waitlink = nil
  mysg.g = gp
  mysg.isSelect = false
  mysg.c = c
  gp.waiting = mysg
  gp.param = nil
  c.sendq.enqueue(mysg)
  
  atomic.Store8(&gp.parkingOnChan, 1)
  gopark(chanparkcommit, unsafe.Pointer(&c.lock), waitReasonChanSend, traceEvGoBlockSend, 2)
  
  KeepAlive(ep)
  ...
}
```

这段代码的主要作用是将当前要发送数据的 goroutine 和要发送的数据一起包装成一个 sudog 对象，加入到 Channel 的阻塞发送队列中，然后将当前的 goroutine 转为 waiting 状态。

处理流程如下：

1. 调用`getg`方法获取当前 goroutine 的指针，用于后续的发送数据；
2. 调用`acquireSudog`方法获取一个`sudog`结构体，并设置当前`sudog`具体的待发送数据信息和状态；
3. 调用`c.snedq.enqueue`方法将刚刚获取到的`sudog`加入待发送的等待队列；
4. 调用`gopark`方法挂起当前 goroutine（会记录执行位置），状态为`waitReasonChanSend`，阻塞等待 Channel；
5. 调用`KeepAlive`方法保证待发送的数据值是活跃状态，也就是分配在堆上，避免被 GC 回收。

图示如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639111427298-e6a12e66e354.jpg)

#### 3.2.6 恢复发送

当前 goroutine 在经过上面的程序之后已经被挂起了。如果能够继续执行后续的`chansend`代码，就说明 Channel 能够发送数据了，当前的 goroutine 被唤醒了：

```go
func chansend(c *hchan, ep unsafe.Pointer, block bool, callerpc uintptr) bool {
  ...
  // 从这里开始唤醒，并恢复阻塞的发送操作
  if mysg != gp.waiting {
    throw("G waiting list is corrupted")
  }
  gp.waiting = nil
  gp.activeStackChans = false
  closed := !mysg.success
  gp.param = nil
  if mysg.releasetime > 0 {
	  blockevent(mysg.releasetime-t0, 2)
  }
  mysg.c = nil
  releaseSudog(mysg)
  if closed {
	  if c.closed == 0 {
		 throw("chansend: spurious wakeup")
	  }
	  panic(plainError("send on closed channel"))
  }
  return true
}
```

在唤醒 goroutine （调度器在停止 g 时会记录运行线程和方法内执行的位置）并完成 Channel 的阻塞数据发送动作后，进行基本的参数检查，确保是符合要求的（纵深防御）。接着开始取消`mysq`上的 Channel 绑定，完成`sudog`的释放。

至此完成所有类别的 Channel 数据发送管理。

### 3.3 接收数据

Channel 中的数据接收代码类似如下：

```go
msg := <-ch

msg, ok := <-ch
```

这两种方式在编译器翻译后分别对应`runtime.chanrecv1`和`runtime.chanrecv2`两个入口方法，内部会再进一步的调用`runtime.chanrecv`方法，签名如下：

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool)
```

参数列表如下：

* `c *hchan` Channel 底层实例
* `ep unsafe.Pointer` 接收数据的位置
* `block bool` 指示不能立即从 Channel 中获取数据时，是否需要阻塞

其中，`block`参数用来指示在如下几种情况下，是否需要阻塞：

1. 从无缓冲的 Channel 中接收数据，且当前无发送者；
2. 从有缓冲的 Channel 中接收数据，且当前 Channel 缓冲区为空；
3. 从一个 nil Channel 中接收数据（后续将无法被唤醒了）。

发送和接收 Channel 是相对的，也就是其核心实现也是相对的，因此在理解时可以相互结合来看。

#### 3.3.1 前置处理

在发送之前，也会进行一些前置校验，判断 Channel 是否为 nil：

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool) {
  if c == nil {
    if !block {
      return
    }
    gopark(nil, nil, waitReasonChanReceiveNilChan, traceEvGoStop, 2)
    throw("unreachable")
  }
  ...
}
```

若 Channel 是 nil Channel，则：

* 如果是非阻塞模式，则直接返回；
* 如果是阻塞模式，则调用`gopark`方法挂起当前 goroutine。

接下来对于非阻塞模式的 Channel 会进行快速失败检查，检测 Channel 是否已经准备好接收的数据：

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool) {
  ...
  if !block && empty(c) {
    if atomic.Load(&c.closed) == 0 {
      return
    }
    
    if empty(c) {
      if ep != nil {
        typedmemclr(c.elemtype, ep)
      }
      return true, false
    }
  }
  ...
}  
```

这里先用`empty`方法判断 Channel 是否为空，Channel 为空分为以下几种情况：

* 无缓冲区：循环队列为 0 以及发送等待队列`sendq`内没有 goroutine；
* 有缓冲区：缓冲区数组为空，没有待接收的数据。

Channel 为空的时候，先对 Channel 的`closed`字段的状态进行判断，因为 Channel 是无法重复打开的，需要确定当前 Channel 是否为未关闭状态:

- Channel 未关闭时，直接返回结果；
- Channel 已经关闭时，如果不存在缓存数据了，则会清理`ep`指针中的数据并返回。

#### 3.3.2 直接接收

当发现 Channel 上有正在阻塞等待的发送方时，则直接取出该发送方，进行接收：

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool) {
  ...
  lock(&c.lock)
  
  if sg := c.sendq.dequeue(); sg != nil {
    // Found a waiting sender. If buffer is size 0, receive value
    // directly from sender. Otherwise, receive from head of queue
    // and add sender's value to the tail of the queue (both map to
    // the same buffer slot because the queue is full).
    recv(c, sg, ep, func() { unlock(&c.lock) }, 3)
    return true, true
  }
  ...
}
```

这里如果能从等待发送队里中取到 sudog，则说明 Channel 当前是有被阻塞发送的发送者的。这也意味着：

* 如果 Channel 是无缓冲的，那么就应该将阻塞发送队列的第一个 sudog 发送的数据给到当前的接收方；
* 如果 Channel 是有缓冲的，那么缓冲区肯定是满的（否则发送方可以直接将数据写入到缓冲区而不会被阻塞），此时除了给当前的接收者传递缓冲区的第一个数据，还需要将阻塞队列中的第一个发送方的数据再写入到缓冲区，并唤醒发送方对应的 goroutine。

从代码注释中也可以确认上面的逻辑，在接收时：

* 如果 Channel 的缓冲区大小为 0，那么接收方就会直接从发送方中取值；
* 否则的话，接收方从缓冲区的头部获取一个数据，然后将取出的发送方的数据写入到缓冲区的末尾。

#### 3.3.3 缓冲接收

经过上面的判断处理，此时就说明缓冲区中有数据，而且没有被阻塞的发送者，则只需要接收缓冲区数据即可：

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool) {
  ...
  if c.qcount > 0 {
    qp := chanbuf(c, c.recvx)
    if ep != nil {
      typedmemmove(c.elemtype, ep, qp)
    }
    typedmemclr(c.elemtype, qp)
    c.recvx++
    if c.recvx == c.dataqsiz {
      c.recvx = 0
    }
    c.qcount--
    unlock(&c.lock)
    return true, true
  }
  
  if !block {
    unlock(&c.lock)
    return false, false
  }
  ...
}
```

在缓冲区中有数据时，处理方式相对较为简单：

1. 使用`hanbuf`方法根据`recvx`的索引位置取出数据，找到要接收的元素进行处理。
2. 如果所接收到的数据和所传入的变量均不为空，则会调用`typedmemmove`方法将缓冲区中的数据拷贝到所传入的变量中。
3. 完成数据的拷贝之后，进行索引和队列总数的自增自减，并调用`typedmemclr`方法进行内存数据的清扫。

如果缓冲区中没有数据，而且不是阻塞方式获取，则会直接返回结果。

#### 3.3.4 阻塞接收

当发现 Channel 上既没有待发送的 goroutine，缓冲区也没有数据时，将会进入到最后一个阶段，阻塞接收：

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool) {
  ...
  gp := getg()
  mysg := accquireSudog()
  mysg.releasetime = 0
  if t0 != 0 {
    mysg.releasetime = -1
  }
  
  mysg.elem = ep
  mysg.waitlink = nil
  gp.waiting = mysg
  mysg.g = gp
  mysg.isSelect = false
  mysg.c = c
  gp.param = nil
  c.recvq.enqueue(mysg)
  
  atomic.Store8(&gp.parkingOnChan, 1)
  gopark(chanparkcommit, unsafe.Pointer(&c.lock), waitReasonChanReceive, traceEvGoBlockRecv, 2)
  ...
}
```

这一块接收逻辑和发送逻辑基本类似，主体都是获取当前 goroutine，构建 sudog 结构，保存当前待接收数据（发送方）的地址信息，并将 sudog 加入等待接收（发送）队列。最后调用`gopark`方法挂起当前 goroutine，等待唤醒。

```go
func chanrecv(c *hchan, ep unsafe.Pointer, block bool) (selected, received bool) {
  ...
  // 被唤醒后从此处开始执行
  if mysg != gp.waiting {
    throw("G waiting list is corrupted")
  }
  gp.waiting = nil
  gp.activeStackChans = false
  if mysg.releasetime > 0 {
    blockevent(mysg.releasetine-t0, 2)
  }
  closed ：= gp.param == nil
  gp.param = nil
  mysg.c = nil
  releaseSudog(mysg)
  return true, !closed
}
```

可以看到，在接收方被唤醒后，也是一样的恢复现场，回到对应的执行点，完成最后的扫尾工作。

### 3.4 关闭 channel

关闭 Channel 主要涉及到`close`方法：

```go
close(ch)
```

其对应的编译器翻译结果是`closechan`方法：

```go
func closechan(c *hchan)
```

#### 3.4.1 前置处理

在关闭 Channel 之前也要进行一些状态检测：

```go
func closechan(c *hchan) {
  // 未初始化的 chan 是不能被关闭的
  if c == nil {
    panic(plainError("close of nil channel"))
  }
  
  lock(&c.lock)
  
  // 已关闭的 chan 是不能被重复关闭的
  if c.closed != 0 {
    unlock(&c.lock)
    panic(plainError("close of closed channel"))
  }
  
  c.closed = 1
  ...
}
```

这里就是检查 Channel 是否为 nil，以及是否已经关闭。一切正常的话，会先申请加锁，并将 Channel 的`closed`值设置为 1，表示已关闭。

#### 3.4.2 释放接收方

在完成了异常边界判断和标志设置后，会将接收者的 sudog 等待队列`recvq`加入到待清除队列`glist`中：

```go
func closechan(c *hchan) {
  ...
  var glist gList
  for {
    sg := c.recq.dequeue()
    if sg == nil {
      break
    }
    if sg.elem != nil {
      typedmemclr(c.elemtype, sg.elem)
      sg.elem = nil
    }
    if sg.releasetime != 0 {
      sg.releasetime = cputicks()
    }
    gp := sg.g
    gp.param = nil
    if raceenabled {
      raceacquireg(gp, c.raceaddre())
    }
    glist.push(gp)
  }
  ...
}
```

这里所去除并加入`glist`的 goroutine 状态均要为`_Gwaiting`，以保证后续的新一轮调度。

#### 3.4.3 释放发送方

同样的，会将发送方也加入到待清除队列`glist`中：

```go
func closechan(c *hchan) {
  ...
  // release all writers (they will panic)
  for {
    sg := c.sendq.dequeue()
    if sg == nil {
      break
    }
    sg.elem = nil
    if sg.releasetime != 0 {
      sg.releasetime = cputicks()
    }
    gp := sg.g
    gp.param = nil
    if raceenabled {
      raceacquireg(gp, c.raceaddr())
    }
    glist.push(gp)
  }
  unlock(&c.lock)
  ...
}
```

#### 3.4.4 协程调度

接下来，会将所有`glist`中的 goroutine 状态从`_Gwaiting`设置为`_Grunnable`状态，等待调度器的调度：

```go
func closechan(c *hchan) {
  ...
  // Ready all Gs now that we've dropped the channel lock
  for !glist.empty() {
    gp := glist.pop()
    gp.schedlink = 0
    goready(gp, 3)
  }
}
```

后续所有的 goroutine 允许被重新调度后，若原本还在被动阻塞的发送方或接收方，将重获自由，后续该干啥就干啥了，继续其所属的应用流程。

## 四、channel send/recv 分析

### 4.1 send

`send`方法承担向 Channel 发送具体数据的功能：

```go
func send(c *hchan, sg *sudog, ep unsafe.Pointer, unlockf func(), skip int) {
  if sg.elem != nil {
    // 直接把要发送的数据 copy 到 receiver 的栈空间
    sendDirect(c.elemtype, sg, ep)
    sg.elem = nil
  }
  gp := sg.g
  unlockf()
  gp.param = unsfe.Pointer(sg)
  if sg.releasetime != 0 {
    sg.releasetime = cputicks()
  }
  // 唤醒接收者对应的 goroutine
  goready(gp, skip+1)
}

func snedDirect(t *_type, sg *sudog, src unsafe.Pointer) {
  dst := sg.elem
  typeBitsBulkBarrier(t, uintptr(dst), uintptr(src), t.size)
  memmove(dst, src, t.size)
}
```

该方法的参数中，sg 是一个打包好的 gorouitne 对象，ep 是要发送到 Channel 中的数据指针。

这段代码的流程如下：

* 如果接收方的 sudog 符合条件，则调用`sendDirect`方法将待发送数据直接拷贝到待接收变量的内存地址上（执行栈）。例如，`msg := <-ch`语句，就是将数据从`ch`直接拷贝到了`msg`的内存地址上。
* 然后通过`sg.g`属性获取等待接收数据的 goroutine，并传递后续唤醒所需的参数。
* 调用`goready`方法唤醒需接收数据的 goroutine，将其从`_Gwaiting`状态调度为`_Grunable`状态。

### 4.2 recv

`recv`方法承担在 Channel 中接收具体数据的功能：

```go
func recv(c *hchan, sg *sudog, ep unsafe.Pointer, unlockf func(), skip int) {
  // 无缓冲区的 chan
  if c.dataqsiz == 0 {
    if ep != nil {
      recvDirect(c.elemtype, sg, ep)
    }
  } else {
    // 有缓冲区的 chan
    
    // 获取第一个要被接收的数据指针，并将其指向的数据拷贝给接收者
    qp := chanbuf(c, c.recvx)
    if ep != nil {
      typememmove(c.elemtype, ep, qp)
    }
    // 将发送者的数据拷贝到 chan 的缓冲区中，位置就是刚发送给接收者的数据的位置
    typedmemmove(c.elemtype, qp, sg.elem)
    c.recvx++
    if c.recvx == c.dataqsiz {
      c.recvx = 0
    }
    c.sendx = c.recvx // c.sendx = (c.sendx+1) % c.dataqsiz
  }
  sg.elem = nil
  gp := sg.g
  unlock()
  gp.param = unsafe.Pointer(sg)
  if sg.releasetime != 0 {
    sg.releasetime = cputicks()
  }
  goready(gp, skip+1)
}
```

该方法在接收时分为两种情况，分别是直接接收和缓冲接收：

* 直接接收（不存在缓冲区）：

    - 调用`recvDirect`方法，其作用与`sendDirect`方法相对，会直接从发送方的 goroutine 调用栈中将数据拷贝到接收方的 goroutine 中。

* 缓冲接收（存在缓冲区）：

    - 调用`chanbuf`方法，根据`recvx`索引的位置读取缓冲区元素，并将其拷贝到接收方的地址；
    - 拷贝完成后，对`sendx`和`recvx`索引位置进行调整。因为 Channel 的缓冲区是环形的，当缓冲区满了的时候，先将第一个待接收的数据发给接收者，然后将发送者的数据填在这里，最后将第一个待接收数据的索引移动到下一个去，并保持下一个要写入缓冲区的位置为待接受的索引位置，表示缓冲区还是满的。

最后还是常规的 goroutine 调度动作，会调用`goready`方法来唤醒当前所处理的 sudog 的对应的 goroutine。那么在下一轮调度中，既然已经接收了数据，自然发送方也就会被唤醒了。

### 4.3 recv 缓冲区的操作

对于 Channel 缓冲区满的时候的发送数据操作，用如下图示进行说明：

1. 将缓冲区填满。

    每一次发送数据到 Channel 缓冲区，都是将数据拷贝到缓冲区的末尾。当缓冲区填满了之后，会将 sendx 的指针位置设置为 0，此时 sendx 等于 recvx。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1652021519635-47490161ddc2.jpg)

2. 发送数据给接收者并将待发送者数据写入缓冲区中。

    在缓冲区满了之后，如果还有发送者在发送数据，就会将发送者阻塞，并封装成 sudog 对象挂在 Channel 的发送者队列中。
    
    此时接收方来接收数据时，会将 Channel 缓冲区中的第一个数据发送给接收者。因为缓冲区的数据被取走了一个，那么下一个接收者要接收的数据就应该下一个了，所以需要将 recvx 移动到下一个位置上。
    
    因为还有等待发送数据的发送方，而缓冲区空出了一个位置，所以此时就可以将阻塞发送队列中的第一个发送方的数据写入到缓冲区中，从而尽早的解除发送方的阻塞状态。因为缓冲区中只有刚被接收者接收的数据的位置是空的，所以就要写入到那个位置上。写入之后 sendx 的位置也要移动到下一个。
    
    缓冲区满的时候 sendx 和 recvx 是相同的。而取走并写入一个数据，那缓冲区就还是满的，所以 sendx 和 recvx 就还是相同的。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1652021999502-a57bb6278465.jpg)
    
这就是为什么 recv 方法中，在有缓冲区时那样处理的原因。

## 五、总结

通过上述的代码分析和图示，不难发现，Go Channel 设计并不复杂，本质上就是一个带锁的环形队列，再加上对称的`sendq`、`recvq`等双向链表的辅助属性，就能勾画出 Channel 的基本逻辑流转模型。

Channel 的发送-接收流程如下图所示：

![无缓冲 Channel](http://cnd.qiniu.lin07ux.cn/markdown/1639481525400-8f58ddacf6bd.jpg)

![缓冲 Channel](http://cnd.qiniu.lin07ux.cn/markdown/1639481770524-10c401856ca0.jpg)

在具体的数据传输上，都是围绕着“边界上下限处理，使用互斥锁，阻塞/非阻塞，缓冲/非缓冲，缓存出队列，拷贝数据，解互斥锁，协程调度“在不断的流转处理。在基本逻辑上也是相对重合的，因为发送和接收、创建和关闭总是相对的。

