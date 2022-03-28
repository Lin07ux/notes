> 转摘：
> 
> 1. [详解Go语言调度循环源码实现](https://www.luozhiyun.com/archives/448)
> 2. [【Golang 技术分享】关于 Go 并发编程，你不得不知的“左膀右臂”——并发与通道！](https://mp.weixin.qq.com/s/Wwnnx9BLw5Z-ksgJYAe-Vw)

Go 语言的 goroutine 可以看做是对操作系统线程 thread 加的一层抽象，但是更轻量级。Goroutine 不仅减少了上下文切换带来的额外开销，占用的资源也更少。

Go 使用协程来代替线程实现轻量级的用户态调度切换，在提供高并发的情况下，也有较高的性能。进行协程调度的就是被称为 GMP 模型。

## 一、线程的实现模型

线程的实现模型主要有三个：**用户级线程模型**、**内核级线程模型**和**两级线程模型**。它们之间最大的差异在于用户线程与内核调度实体（KSE）之间的对应关系上。内核调度实体就是可以被操作系统内核调度器调度的对象，也称为内核级线程，是操作系统内核的最小调度单元。

### 1.1 用户级线程模型

![](http://cnd.qiniu.lin07ux.cn/markdown/1648366199129-54ff392b64e2.jpg)

用户线程与 KSE 为多对一（N:1）的映射关系，此模型下的线程由用户级别的线程库全权管理，线程库存储在进行的用户空间之中，这些线程的存在对于内核来说是无法感知的，所以这些线程也不是内核调度器调度的对象。一个进程中的所有创建的线程的都只和同一个 KSE 在运行时动态绑定，内核的所有调度都是基于用户进程的。

对于线程的调度则是在用户层面完成的，相较于内核调度不需要让 CPU 现在用户态和内核态之间切换，这种实现方式相比内核级线程模型可以做的很轻量级，对系统资源的消耗会小很多，上下文切换所花费的代价也会小得多。许多语言实现的协程库基本上都属于这种方式。但是此模型下的多线程并不能真正的并发运行。例如，如果某个线程在 I/O 操作过程中被阻塞，那么其所属进程内的所有线程都被阻塞，整个进程将被挂起。

### 1.2 内核级线程模型

![](http://cnd.qiniu.lin07ux.cn/markdown/1648368307083-f276a6562b6e.jpg)

用户线程与 KSE 为一对一（1:1）的映射关系。此模型下的线程由内核负责管理，应用程序对线程的创建、终止和同步都必须通过内核提供的系统调用来完成，内核可以分别为每一个线程进行调度。

所以，一对一线程模型可以真正的实现线程的并发运行，大部分语言实现的线程库基本上都属于这种方式。但是，此模型下线程的创建、切换和同步都需要话费更多的内核资源和时间。如果一个进程包含了大量的线程，那么它会给内核的调度器造成非常大的负担，甚至会影响到操作系统的整体性能。

### 1.3 两级线程模型

![](http://cnd.qiniu.lin07ux.cn/markdown/1648368456150-b7fbd0e61255.jpg)

用户线程与 KSE 为多对多（N:M）的映射关系。两级线程模型吸收前两种线程模型的优点并尽量规避了它们的缺点：

* 区别在于用户级线程模型，两级线程模型中的进程可以与多个内核线程 KSE 关联。也就是说，一个进程内的多个线程可以分别绑定一个自己的 KSE，这点和内核级线程模型相似。

* 区别于内核级线程模型，它的进程里的线程并不与 KSE 唯一绑定，而是可以多个用户线程映射到同一个 KSE。当某个 KSE 因为其绑定的线程的阻塞操作被内核调度出 CPU 时，其关联的进程中其余用户线程可以重新与其他 KSE 绑定运行。

所以，两级线程模型既不是用户级线程模型那种完全靠自己调度的，也不是内核级线程模型完全靠操作系统调度的，而是一种自身调度与系统调度协同工作的中间态，即**用户调度器实现用户线程到 KSE 的调度，内核调度器实现 KSE 到 CPU 上的调度。**

## 二、GMP 模型

在 Go 的并发编程模型中，不受操作系统内核管理的独立控制流不叫用户线程或线程，而称为 Goroutin。Goroutine 通常被认为是协程的 Go 实现，实际上 Goroutine 并不是传统意义上的协程，传统的协程库属于用户级线程模型，而 Goroutine 结合 Go 调度器的底层实现上属于两级线程模型。

Go 搭建了一个特有的两级线程模型，由 Go 调度器实现 Goroutine 到 KSE 的调度，由内核调度器实现 KSE 到 CPU 上的调度。Go 的调度器使用 G、M、P 三个结构体来实现 Goroutine 的调度，也称之为 **GMP 模型**。

### 2.1 G、M、P 之间的关系

G、M、P 三者的意义如下：

**G**：代表 Goroutine。每个 Goroutine 都有自己独立的栈存放当前的运行内存及状态，对应一个 G 结构体，存储 Goroutine 的运行堆栈、状态以及任务函数，可以重用。当 Goroutine 被调离 CPU 时，调度器代码负责把 CPU 寄存器的值保存在 G 对象的成员变量中；当 G 被调度起来运行时，调度器代码又负责把 G 对象的成员变量所保存的寄存器的值恢复到 CPU 的寄存器中。

**M**：表示内核线程，是 OS 底层线程的抽象。它本身就于一个内核线程进行绑定，每个工作线程都有唯一的一个 M 结构体的实例对象与之对应，它代表着真正执行计算的资源，由操作系统的调度器调度和管理。M 结构体对象除了记录着工作线程的诸如栈的起止位置、当前正在执行的 Goroutine 以及是否空闲等状态信息之外，还通过指针维持着与 P 结构体的实例对象之间的绑定关系。

**P**：代表一个虚拟的 Processer 逻辑处理器。对 G 来说，P 相当于 CPU 核，G 只有绑定到 P（在 P 的 local runq 中）才能被调度；对 M 来说，P 提供了相关的执行环境（Context），如内存分配状态（mcache）、任务队列（G）等。它维护一个局部的可运行的 G 队列，工作线程优先使用自己的局部运行队列，只有必要时才会去访问全局运行队列，这可以大大减少锁冲突，提高工作线程的并发性，并且可以良好的运行程序的局部性原理。

一个 G 的执行需要 P 和 M 的支持。一个 M 在与一个 P 关联之后，就形成了一个有效的 G 运行环境（内核线程+上下文）。每个 P 中的可运行队列（runq）中的 G 会被 依次传递给本地 P 关联的 M，从而获得运行时机。

M 于 KSE 之间总是一一对应的关系，一个 M 仅能代表一个内核线程。M 与 KSE 之间的关联非常稳固，一个 M 在其生命周期内，会且仅会与一个 KSE 产生关联，而 M 与 P、P 与 G 之间的关联都是可变的，M 与 P 也是一对一的关系，P 与 G 则是一对多的关系。

除此之外，每个 Go 程序中都有一个单一的、存放所有可运行 goroutine 的容器，这是一个共享的全局变量，每个工作线程都可以访问它以及它所拥有的 goroutine 队列。

下面是 GMP 以及 schedt 中的全局队列的关系：

![](http://cnd.qiniu.lin07ux.cn/markdown/1648443392980-eaa19f1d5ead.jpg)

### 2.2 G

运行时，G 在调度器的地位和线程在操作系统中差不多，但是它占用了更小的内存空间，也降低了上下文切换的开销。它是 Go 语言在用户态提供的线程，作为一种粒度更细的资源调度单元，使用得当，能够在高并发的场景下更高效的利用机器的 CPU。

G 结构体的部分定义如下（在`src/runtime/runtime2.go`文件中）：

```go
type g struct {
  stack        stack   // 当前 goroutine 的栈内存范围 [stack.lo, stack.hi)
  stackguard0  uintptr // 用于调度器抢占式调度
  
  _panic       *_panic
  _defer       *_defer
  
  m            *m     // 当前 goroutine 占用的线程
  sched        gobuf  // 存储 goroutine 的调度相关的数据
  atomicstatus uint32 // goroutine 的状态
  
  preemt        bool  // 抢占信号
  preemtStop    bool  // 抢占时将状态修改成 _Gpreempted
  preemptShrink bool  // 再同步安全点收缩栈
  // ...
}

type gobuf struct {
  sp   uintptr     // 栈指针
  pc   uintptr     // 程序计数器
  g    guintptr    // gobuf 对应的 goroutine
  ctx  unsafe.Pointer
  ret  sys.Uintreg // 系统调用的返回值
  lr   uintptr
  bp   uintptr     // 函数栈起始位置
}
```

G 对应的栈指针、程序计数器等会存储在`g.sched`字段中，其对应一个`gobuf`结构体。在调度器保存或恢复上下文时会使用这个结构体中的字段，以改变程序即将执行的代码。

运行过程中，Goroutine 的状态字段`atomicstatus`可能会发生变化，处于不同的状态下。具体状态可以参见[Go goroutine 状态和挂起原因](Go%20goroutine%20状态和挂起原因.md)。另外，进入死亡状态的 G 是可以被重新初始化并使用的。

Goroutine 的状态迁移是一个十分复杂的过程，触发状态迁移的方法也很多。可以将常见状态聚合成三种类别：

* **等待中**：Goroutine 正在等待某些条件满足，如系统调用结束等。包括有`_Gwaiting`、`_Gsyscall`和`_Gpreempted`几个状态。

* **可运行**：Goroutine 已经准备就绪，可以在线程运行。如果当前程序中有非常多的 Goroutine，那么每个 Goroutine 就可能会等待更多的时间。状态有`_Grunnable`。

* **运行中**：Goroutine 正在某个线程上运行，即`_Grunning`。

G 常见状态的转换图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1648443874837-ace446c6754c.jpg)

### 2.3 M

Go 语言并发模型中的 M 是操作系统线程。默认情况下，调度器最多可以创建 10000 个线程，但是最多只会有`GOMAXPROCS`(P 的数量)个活跃的线程能够正常运行。

> 默认情况下，运行时会将`GOMAXPROCS`设置成当前机器的核数，也可以在程序中使用`runtime.GOMAXPROCS`来改变最大的活跃线程数。

大多数情况下，都会使用 Go 的默认设置，也就是活跃线程数等于 CPU 核数，这样不会频繁的触发操作系统的线程调度和上下文切换，所有的调度都会发生在用户态，由 Go 语言调度器触发，能够减少很多额外开销。

每个 M 都对应一个运行时中的`runtime.m`结构体，部分定义如下（在`src/runtime/runtime2.go`文件中）：

```go
type m struct {
  g0        *g   // 一个特殊的 goroutine，执行一些运行时任务、调度
  gsignal   *g   // 处理 signal 的 G
  tls       [6]uintptr  // 线程本地存储（用于 x86 架构上的扩展寄存器）
  curg      *g   // 当前运行的 G
  caughtsig guintptr    // 在发生致命错误时运行的 G
  
  p      puintptr // 正在运行的 P
  nextP  puintptr // 与当前 M 潜在关联的 P
  oldp   puintptr // 执行系统调用之前使用的 P
  
  spinging bool   // 当前 M 是否正在寻找可运行的 G
  lockedg  *g     // 与当前 M 锁定的 G
  // ...
}
```

M 中的`g0`表示一个特殊的 Goroutine，由 Go 运行时系统在 M 启动时创建，它会深度参与运行时的调度过程（也就是控制协程切换），包括 Goroutine 的创建、大内存分配和 CGO 函数的执行。

`curg`是当前线程上运行的用户 Goroutine。

### 2.4 P

调度器中处理器 P 是线程 M 和用户 G 的中间层，它能提供线程需要的上下文环境，也会负责调度线程上的用户 G 等待队列。通过处理器 P 的调度，每一个内核线程都能够执行多个 Goroutine，且能让 M 在 Goroutine 进行一些 I/O 操作时及时让出计算资源，提高线程的利用率。

P 的数量等于`GOMAXPROCS`，设置`GOMAXPROCS`的值只能限制 P 的最大数量，对 M 和 G 的数量没有任何约束。当 M 上运行的 G 进入系统调用导致 M 被阻塞时，运行时系统会把该 M 和与之关联的 P 分离开来。这时，如果该 P 的可运行 G 对客上还有未被运行的 G，那么运行时系统就会找一个空闲的 M，或者新建一个 M，与该 P 关联，满足这些 G 的运行需要。因此，M 的数量很多时候都会比 P 多。

P 对应`runtime.p`结构体，部分定于如下（在`src/runtime/runtime2.go`文件中）：

```go
type p struct {
  id          int32
  status      uint32 // p 的状态
  schedtick   uint32 // 调度计数器（每次产生调度行为则+1）
  syscalltick uint32 // 系统调用计数器（每次产生系统调用则+1）
  
  m       muintptr   // 对应关联的 M
  mcache  pageCache  // m 的页面缓存
  
  deferpool    [5][]*_defer // defer 结构池
  deferpoolbuf [5][32]*_defer
  
  // 可运行的 goroutine 队列，可无锁访问
  runqhead uint32
  runqtail uint32
  runq [256]guintptr

  runnext guintptr // 缓存可立即执行的 G
  gFree strung {   // 可用的 G 列表，G 状态等于 Gdead
    gList
    n int32
  }
  // ...
}
```

P 的状态有如下几种：

* `_Pidle = 0` 表示 P 没有运行用户代码或者调度器
* `_Prunning = 1` 被线程 M 持有，并且正在执行用户代码或者调度器
* `_Psyscall = 2` 没有执行用户代码，当前线程陷入系统调用
* `_Pgcstop = 3` 被线程 M 持有，当前处理器由于来及回收 STW 停止
* `_Pdead = 4` 当前处理器已不能被使用

### 2.5 sched

sched 主要存放了调度器持有的全局资源，如空闲的 M 列表、P 链表、G 的全局队列等：

```go
type schedt struct {
  ...
  lock mutex
  
  midle      muintptr // 空闲的 M 列表
  nmidle     int32    // 空闲的 M 的数量
  nmspinning uint32   // 处于 spinning 状态的 M 的数量
  mnext      int64    // 下一个被创建的 M 的 ID
  maxmcount  int32    // 能拥有的 M 的最大数量
  
  pidle      puintptr // 空闲 P 列表
  npidle     uint32   // 空闲 P 的数量
  
  runq       gQueue // 全局 runnable G 的队列
  runqsize   int32
  
  gFree struct {
    lock     mutext
    stack    gList // Gs with stacks
    noStack  gList // Gs without stacks
    n        int32
  }
  
  // sudog 结构的集中缓存
  sudoglock  mutex
  sudogcache *sudog 
  
  // defer 结构的池
  deferlock mutex
  deferpool [5]*_defer 
  ...
}
```

## 三、调度元素

两级线程模型中的一部分调度任务会由操作系统之外的程序承担。在 Go 语言中，调度器就负责这一部分调度任务。调度的主要对象就是 G、M 和 P 的实例。每个 M（即每个内核线程）在运行过程中都会执行一些调度任务，他们共同实现了 Go 调度器的调度功能。

### 3.1 go 和 m0

运行时系统中的每个 M 都会拥有一个特殊的 G，一般称为 M 的 g0。M 的 g0 不是由 Go 用户程序中的代码间接生成的，而是由 Go 运行时系统在初始化 M 时创建并分配给该 M 的。M 的 g0 一般用于执行调度、垃圾回收、栈管理等方面的任务。

M 还会拥有一个专用于处理信号的 G，称为 gsignal。

除了 g0 和 gsignal 之外，其他由 M 运行的 G 都可以视为用户级别的 G，简称用户 G。对应的，g0 和 gsignal 可称为系统 G。

Go 运行时系统会进行切换，以使每个 M 都可以交替运行用户 G 和它的 g0。这就是前面所说的“每个 M 都会运行调度程序”的原因。

除了每个 M 都拥有属于它自己的 g0 外，还存在一个`runtime.g0`，用于执行引导程序，它运行在 Go 程序拥有的第一个内核线程之中。这个线程也称为`runtime.m0`，而`runtime.m0`的 g0 就是`runtime.g0`。

### 3.2 核心元素的容器

下面承载 G、M、P 元素实例的容器：

| 名称              | 结构            | 作用域    | 说明                |
|:----------------:|:---------------:|:--------:|:------------------:|
| 全局 M 列表       | `runtime.allm`  | 运行时系统 | 存放所有 M 的单向链表 |
| 全局 P 列表       | `runtime.allp`  | 运行时系统 | 存放所有 P 的数组    |
| 全局 G 列表       | `runtime.allgs` | 运行时系统 | 存放所有 G 的切片    |
| 调度器的空闲 M 列表 | `sched.midle`  | 调度器     | 存放空闲 M 的单向列表 |
| 调度器的空闲 P 列表 | `sched.pidle`  | 调度器     | 存放空闲 P 的单向链表 |
| 调度器的可运行 G 列表 | `sched.runqhead` | 调度器 | 存放可运行 G 的队列   |
| 调度器的自由 G 列表 | `sched.gfreeStack` | 调度器 | 存放自由 G 的单向链表 |
| P 的可运行 G 列表  | `p.runq`     | 本地 P | 存放当前 P 中可运行 G 的队列 |
| P 的自由 G 列表    | `p.free`     | 本地 P | 存放当前 P 中自由 G 的链表  |

这其中和 G 相关的容器值的特别注意：任何 G 都可以存在于全局 G 列表中，其余 4 容器只会存放当前作用域内的、具有某个状态的 G。

两个可运行的 G 列表中的 G 都拥有几乎平等的运行机会，只不过不同时机的调度会把 G 放在不同的地方。例如，从`Gsyscall`状态转移出来的 G 都会被放入调度器的可运行 G 队列，而刚刚被初始化的 G 都会被放入本地 P 的可运行 G 队列。

此外，这两个可运行 G 队列之间也会互相转移 G。例如，本地 P 的可运行 G 队列已满时，其中一半的 G 会被转移到调度器的可运行 G 队列（全局 G 列表）中。

调度器的空闲 M 列表和空闲 P 列表用于存放暂时不被使用的元素实例，运行时系统需要时，会从中获取相应元素的实例并重新启用它。

## 四、调度循环

### 4.1 获取调度的 G

通过调用`runtime.schedule`函数可以进入调度循环中：

```go
func schedule() {
  _g_ := getg()
  
top:
  var gp *p
  var inheritTime bool
  
  if gp == nil {
    // 为了公平，每调用 schedule 函数 61 次就要从全局可运行 G 队列中获取 G
    if _g_.m.p.ptr().schedtick%61 == 0 && sched.runqsize > 0 {
      lock(&sched.lock)
      gp = globrunqget(_g_.m.p.ptr(), 1)
      unlock(&sched.lock)
    }
  }
  
  // 从 P 本地获取 G 任务
  if gp == nil {
    gp, inheritTime = runqget(_g_.m.p.ptr())
  }
  
  // 运行到这个逻辑表示本地运行队列和全局队列都没有找到需要运行的 G
  if gp == nil {
    // 阻塞地查找可用的 G
    gp, inhertTime = findrunable()
  }
  
  // 执行 G 任务函数
  execute(gp, inheritTime)
}
```

从代码中可以看出，`runtime.schedule`函数会从下面几个地方查找待执行的 Goroutine：

* 为了保证公平，当全局运行队列中有待执行的 Goroutine 时，通过 schedtick 保证有一定几率会从全局的运行队列中查找对应的 Goroutine；

* 从 P 本地的运行队列中查找待执行的 Goroutine；

* 如果前两种方法都没有找到 G，会通过`runtime.findrunnable()`函数去其他 P 里面“偷”一些 G 来执行；如果“偷”不到，就阻塞查找直到有可运行的 G。

### 4.2 切换调度的 G

获取到的 G 会通过`runtime.execute()`来执行：

```go
func execute(gp *g, inheritTime bool) {
  _g_ := getg()
  
  // 将 G 绑定到当前 M 上
  _g_.m.curg = gp
  gp.m = _g_.m
  
  // 将 g 正式切换为 _Grunning 状态
  casgstatus(gp, _Grunnable, _Grunning)
  gp.waitsince = 0
  // 抢占信号
  gp.preempt = false
  gp.stackguard0 = gp.stack.lo + _StackGuard
  if !inheritTime {
    // 调度器调度次数增加 1
    _g_.m.p.ptr().schedtick++
  }
  // ...
  // gogo 完成从 g0 到 gp 的切换
  gogo(&gp.sched)
}
```

在执行`runtime.execute()`时，被调度运行的 G 会被切换到`_Grunning`状态，并将 M 和 G 进行绑定，最终调用`runtime.gogo()`函数将 Goroutine 调度到当前的线程上。

### 4.3 执行调度的 G

`runtime.gogo()`函数会从`runtime.gobuf`中取出`runtime.goexit`的程序计数器和待执行函数的程序计数器，并将：

* `runtime.goexit`的程序计数器放到栈 SP 上；
* 待执行函数的程序计数器放到寄存器 BX 上。

对应的汇编代码如下：

```asm
MOVL gobuf_sp(BX), SP // 将 runtime.goexit 函数的 PC 恢复搭配 SP 中
MOVL gobuf_pc(BX), BX // 获取待执行函数的程序计数器
JMP  BX               // 开始执行
```

这样，当 Goroiutine 中运行的函数返回时，程序会跳转到`runtime.goexit`所在位置，最终在当前线程的 g0 的栈上调用`runtime.goexit0`函数，该函数会将 Goroutine 转换为`_Gdead`状态，并清理其中的字段、移除 Goroutine 和线程的关联并调用`runtime.gfput`将 G 重新加入处理器的 Goroutine 空闲列表`gFree`中：

```go
func goexit0(gp *g) {
  _g_ := getg()
  
  // 设置当前 G 状态为 _Gdead
  casgstatus(gp, _Grunning, _Gdead)
  // 清理 G
  gp.m = nil
  // ...
  gp.writebuf = nil
  gp.waitreason = 0
  gp.param = nil
  gp.labels = nil
  gp.timer = nil
  
  // 解绑 M 和 G
  dropg()
  // ...
  // 将 G 扔进 gfree 链表中等待复用
  gfput(_g_.m.p.ptr(), gp)
  // 再次进行调度
  schedule()
}
```

在`runtime.goexit0`函数的最后，会重新调用`runtime.schedule`函数触发新一轮的 Goroutine 调度。这样，调度器从`runtime.schedule`函数开始，最终又回到`runtime.schedule`，这就是 Go 语言的调度循环。


