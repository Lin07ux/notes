> 转摘：
>
> 1. [详解 Go 程序的启动流程，你知道 g0，m0 是什么吗？](https://mp.weixin.qq.com/s/YK-TD3bZGEgqC0j-8U6VkQ)
> 2. [详解Go语言调度循环源码实现](https://www.luozhiyun.com/archives/448)

任何一个 Go 程序，启动过程中都会包含对应的基础环境设置，涉及到 Go runtime 的调度器启动、go/m0 的启动等。

下面以最简单的 Go 程序代码来介绍下 Go 程序的启动流程：

```go
import "fmt"

func main() {
  fmt.Println("hello world")
}
```

## 一、引导阶段

### 1.1 查找入口

对上面的 Go 程序进行编译：

```shell
GOFLAGS="-ldflags=-compressdwarf=false" go build
```

> 这里设定了 GOFLAGS 参数，因为从 Go 1.1 起，为了减少二进制文件的大小，调试信息会被压缩。这会导致在 MacOS 上使用 gdb 时无法理解压缩的 DWARF 的含义是什么。因此需要在本次调试中将其关闭。

使用 gdb 进行调试：

```
$ gdb awesomeProject 
(gdb) info files
Symbols from "/Users/eddycjy/go-application/awesomeProject/awesomeProject".
Local exec file:
 `/Users/eddycjy/go-application/awesomeProject/awesomeProject', file type mach-o-x86-64.
 Entry point: 0x1063c80
 0x0000000001001000 - 0x00000000010a6aca is .text
 ...
(gdb) b *0x1063c80
Breakpoint 1 at 0x1063c80: file /usr/local/Cellar/go/1.15/libexec/src/runtime/rt0_darwin_amd64.s, line 8.
```

根据输出的 Entry point 进行调试，可以看到真正的程序入口在 runtime 包中。在不同的计算机架构中入口指向的文件不同，例如：

* MacOS 架构中会指向`src/runtime/rt0_darwin_amd64.s`
* Linux 架构中会指向`src/runtime/rt0_linux_amd64.s`

> 最终指向的文件名字中的`rt0`是 runtime0 的缩写，指代运行时的创世；`darwin`指代的是目标操作系统为 MacOS（GOOS）；`amd64`代表目标操作系统架构 64 位系统（GOHOSTARCH）。
>
> 而且 Go 语言还支持更多的系统架构，比如 AMD64、ARM、MIPS、WASM 等，每种架构都会对应不同的入口文件。可以在`src/runtime`目录中进行查看。

### 1.2 入口方法

在`rt0_linux_amd64.s`文件中，可以看到`_rt0_adm64_darwin`直接跳转到了`_rt0_adm64`方法：

```asm
TEXT _rt0_amd64_darwin(SB),NOSPLIT,$-8
  JMP _rt0_amd64(SB)
  ...
```

而`_rt0_amd64`方法会将程序输入的 argc 和 argv 从内存移动到寄存器中后，再次跳转到`runtime.rt0_go`：

```asm
TEXT _rt0_amd64(SB),NOSPLIT,$-8
  MOVQ 0(SP), DI   // argc
  LEAQ 8(SP), SI   // argv
  JMP  runtime.rt0_go(SB)
```

这里栈指针 SP 的前两个值分别表示的是 argc 和 argv，其对应参数的数量和具体各参数的值。

### 1.3 开启主线

程序参数准备就绪后，正式初始化的方法落在了`runtime.rt0_go`方法中：

```asm
TEXT runtime.rt0_go(SB),NOSPLIT,$0
  ...
  CALL runtime·check(SB) // 类型检查
  MOVL 16(SP), AX   // copy argc
  MOVL AX, 0(SP)
  MOVQ 24(SP), AX   // copy argv
  MOVQ AX, 8(SP)
  CALL runtime·args(SB)       // 系统参数转换
  CALL runtime·osinit(SB)     // 系统参数设置
  CALL runtime·schedinit(SB)  // 运行时组件初始化
  
  // create a new goroutine to start program
  MOVQ $runtime·mainPC(SB), AX // entry
  PUSHQ AX
  PUSHQ $0  // arg size
  CALL runtime·newproc(SB)
  POPQ AX
  POPQ AX
  
  // start this M
  CALL runtime·mstart(SB)
  ...
```

`runtime.rt0_go`方法中，主要是完成各类运行时的检查、系统参数设置和获取、并进行大量的 Go 基础组件初始化，主要包含如下步骤：

* `runtime.check` 运行时类型检查，主要是校验编译器的翻译工作是否正确，是否有“坑”。其代码主要逻辑为检查`int8`在`unsafe.Sizeof`方法下是否已等于 1 这类操作。
* `runtime.args` 系统参数传递，主要是将系统参数转换传递给程序使用。
* `runtime.osinit` 系统基本参数设置，主要是获取 CPU 核心数和内存物理页大小。
* `runtime.schedinit` 进行各种运行时组件的初始化，包含调度器、内存分配器、堆、栈、GC 等一大堆初始化工作。会进行 P 的初始化，并将 m0 和某一个 P 进行绑定。
* `runtime.main` 主要工作是运行 main goroutine。虽然在`runtime.rt0_go`方法中指向的是`$runtime.mainPC`，但实质指向的是`runtime.main`。
* `runtime.newproc` 创建一个新的 goroutine，且绑定`runtime.mian`方法（也就是应用程序中的入口 main 方法），并将其放入 m0 绑定的 P 的本地队列中去，以便后续的调度。
* `runtime.mstart` 启动 m，调度器开始进行循环调度。

初始化完毕后，就是进行主协程 main goroutine 的运行，并放入等待队列（GMP 模型），最后调度器开始进行循环调度。

### 1.4 小结

根据上述源码剖析，可以得出如下的 Go 应用程序引导的流程图：

![Go 程序引导过程](http://cnd.qiniu.lin07ux.cn/markdown/1647237745095-3078b5c49eba.jpg)

在 Go 语言中，实际的运行入口并不是用户日常编写的 main func，更不是 runtime.main 方法，而是从`rt0_*_*.s`开始，最终再一路 JMP 到 runtime.rt0_go 方法中，再在该方法中完成一系列的 Go 自身所需要完成的绝大部分初始化动作。

## 二、调度器初始化

在开启调度之前，运行时会先对调度器进行初始化，这个过程中涉及到 P、M0、G0 等的初始化。

### 2.1 runtime.schedinit

```go
func schedinit() {
  // ...
  _g_ := getg()
  // ...
  // 最大线程数 10000
  sched.maxmcount = 10000
  // M0 初始化
  mcommoninit(_g_.m, -1)
  // ...
  // 垃圾回收器初始化
  gcinit()

  sched.lastpoll = uint64(nanotime())
  // 通过 CPU 核心数和 GOMAXPROCS 环境变量确定 P 的数量
  procs := ncpu
  if n, ok := atoi32(gogetenv("GOMAXPROCS")); ok && n > 0 {
    procs = n
  }
  // P 初始化
  if procresize(procs) != nil {
    throw("unknown runable goroutine during bootstrap")
  }
  // ...
}
```

在`runtime.schedinit()`函数中，会将 M 的最大数量设置为 10000，这就是为什么默认情况下，Go 的可用 M 最大数量为 10000 的原因。

另外，还会通过 CPU 核心数和 GOMAXPROCS 环境变量来确定 P 的数量，并调用`procresize()`函数对 P 进行初始化。

在初始化过程中，还会调用`mcommoninit()`函数，对 M0 进行初始化。

### 2.2 M0 初始化

`runtime.mcommoninit()`函数中主要就是初始 M0，源码如下：

```go
func mcommoninit(mp *m, id int64) {
  _g_ := getg()
  // ...
  lock(&sched.lock)
  // 如果传入 id 小于 0，那么 id 则从 mReserveID 获取，初次从 mReserveID 获取的 id 为 0
  if id >= 0 {
    mp.id = id
  } else {
    mp.id = mReserveID()
  }
  // random 初始化，用于窃取 G
  mp.fastrand[0] = uint32(int64Hash(uint64(mp.id), fastrandseed))
  mp.fastrand[1] = uint32(int64Hash(uint64(cputicks()), ^fastrandseed))
  if mp.fastrand[0]|mp.fastrand[1] == 0 {
    mp.fastrand[1] = 1
  }
  // 创建用于信号处理的 gsignal，只是简单的从堆上分配一个 g 结构体对象，然后把栈设置好就返回了
  mpreinit(mp)
  if mp.gsignal != nil {
    mp.gsignal.stackguard1 = mp.gsignal.stack.lo + _StackGuard
  }
  // 把 M 挂入全局链表 allm 之中
  mp.alllink = allm
  // ...
}
```

在`runtime.schedinit()`方法中调用`mcommoninit()`函数的时候，传入的参数是`-1`，所以初次调用会将 id 设置为 0。

这里并未对 m0 做什么关于调度相关的初始化，所以可以简单的认为这个函数只是把 m0 放入全局链表 allm 之中就返回了。

### 2.3 P 初始化

在完成一些前置的初始化后，就开始进行 P 的初始化了。在`runtime.schedinit()`函数中，通过`runtime.procresize()`即可进入到 P 初始化的流程中。

#### 2.3.1 runtime.procresize()

该方法主要是根据 GOMAXPROCS 对全局的 P 容量进行扩缩容处理：

```go
var allp []*p

func procresize(nprocs int32) *p {
  // 获取之前的 P 个数
  old := gomaxprocs
  // 更新统计信息
  now := nanotime()
  if sched.procresizetime != 0 {
    sched.totaltime += int64(old) * (now - sched.procresizetime)
  }
  sched.procresizetime = now
  // 根据 runtime.MAXGOPROCS 调整 p 的数量，因为 runtime.MAXGOPROCS 用户可以自行设定
  if nprocs > int32(len(allp)) {
    lock(&allLock)
    if nprocs <= int32(cap(allp)) {
      allp = allp[:nprocs]
    } else {
      nallp := make([]*p, nprocs)
      copy(nallp, allp[:cap(allp)])
      allp = nallp
    }
    unlock(&allLock)
  }

  // 初始化新的 P
  for i := old; i < nprocs; i++ {
    pp := allp[i]
    // 为空则申请新的 P 对象
    if pp == nil {
      pp = new(p)
    }
    pp.init(i)
    atomicstorep(unsafe.Pointer(&allp[i]), unsafe.Pointer(pp))
  }

  _g_ := getg()
  // P 不为空并且 id 小于 nprocs，那么可以继续使用当前 P
  if _g_.m.p != 0 && _g_.m.p.ptr().id < nprocs {
    _g_.m.p.ptr().status = _Prunning
    _g_.m.p.ptr().mcache.prepareForSweep()
  } else {
    // 释放当前 P，因为已经失效
    if _g_.m.p != 0 {
      _g_.m.p.ptr().m = 0
    }
    _g_.m.p = 0
    p := allp[0]
    p.m = 0
    p.status = _Pidle
    // P0 绑定到当前的 M0
    acquirep(p)
  }
  // 从未使用的 P 释放资源
  for i := nprocs; i < old; i++ {
    p := allp[i]
    p.destroy() // 不能释放 p 本身，因为它可能在 m 进入系统调用时被引用
  }
  // 释放完 P 之后重置 allp 的长度
  if int32(len(allp)) != nprocs {
    lock(&allLock)
    allp = allp[:nprocs]
    unlock(&allLock)
  }
  var runnablePs *p
  // 将没有本地任务的 P 放到空闲链表中
  for i := nprocs - 1; i >= 0; i-- {
    p := allp[i]
    // 当前正在使用的 P 略过
    if _g_.m.p.ptr() == p {
      continue
    }
    // 设置状态为 _Pidle
    p.status = _Pidle
    // P 的任务列表是否为空
    if runqempty(p) {
      // 放到空闲列表中
      pidleput(p)
    } else {
      // 获取空闲 M 绑定到 P 上
      p.m.set(mget())
      p.link.set(runablePs)
      runablePs = p
    }
  }
  stealOrder.reset(uint32(nprocs))
  var int32p *int32 = &gomaxprocs // make compiler check that gomaxprocs is an int32
  atomic.Store((*uint32)(unsafe.Pointer(int32p)), uint32(nprocs))
  return runnablePs
}
```

在`runtime.procresize()`函数中，主要做了如下一些处理：

1. allp 是一个全局的 P 资源池。如果 allp 的切片中的 P 数量少于期望数量，就会对切片进行扩容；

2. 扩容的时候会使用`new`申请一个新的 P，然后使用 P 结构的`init()`方法进行初始化。需要注意的是初始化的 P 的 id 就是传入的 i 的值，状态为`_Pgcstop`；

3. 通过`_g_.m.p`获取对应的 M（也即是 M0） 上的 P，如果 M 已经与有效的 P 绑定了，则将被绑定的 P 的状态修改为`_Prunning`；否则获取`allp[0]`来调用`runtime.acquirep()`方法，完成当前 M 和 P 的绑定。

4. 接着，会对超出处理器个数的 P 通过`p.destroy()`方法进行释放，这会释放掉与 P 相关的资源，并将 P 的状态设置为`_Pdead`；

5. 最后，通过阶段来改变全局变量 allp 的长度，使其与期望的处理器数量相等，并将 allp 中处于空闲状态的 P 放入到空闲列表中。

#### 2.3.2 p.init()

在新建 P 的时候，会通过`p.init()`方法对 P 的一些字段进行初始化，如设置 id、status、sudogcache、mcache、lock 等。

源码如下：

```go
func (pp *p) init(id int32) {
  pp.id = id
  pp.status = _Pgcstop
  pp.sudogcache = pp.sudogbuf[:0]
  for i := range pp.deferpool {
    pp.deferpool[i] = pp.deferpoolbuf[i][:0]
  }
  pp.wbBuf.reset()
  if pp.mcache == nil {
    if id == 0 {
      if mcache0 == nil {
        throw("missing mcache?")
      }
      pp.mcache = mcache0
    } else {
      pp.mcache = allocmcache()
    }
  }
  // ...
  lockInit(&pp.timersLock, lockRankTimers)
}
```

初始化时，设置的`p.sudogcache`这个字段存的是 sudog 的集合，与 Channel 相关。

每个 P 中会保存相应的 mcache，能快速的进行微对象和小对象的分配。对于 P0 来说，其 mcache 就是前面已经实例化过的 mcache0；其他的 P 则是重新初始化一个 mcache。

#### 2.3.3 runtime.acquirep()

完成 P 的初始化之后，就需要绑定 M 和 P，这是通过`runtime.acquire()`函数来完成的。

这个函数很简单，源码如下：

```go
func acquirep(_p_ *p) {
  wirep(_p_)
  // ...
}

func wirep(_p_ *p) {
  _g_ := getg()
  // ...
  // 将 P 与当前的 M 相互绑定
  _g_.m.p.set(_p_)
  _p_.m.set(_g_.m)
  _p_.status = _Prunning
}
```

#### 2.3.4 runtime.pidleput()

对于空闲的 P 需要将其放入到全局的空闲列表中。源码如下：

```go
func pidleput(_p_ *p) {
  // 如果 P 运行队列不为空，那么不能将其放入空闲列表
  if !runqempty(_p_) {
    throw("pidleput: P has non-empty run queue")
  }
  // 将 P 与 pidle 列表关联
  _p_.link = sched.pidle
  sched.pidle.set(_p_)
  atomic.Xadd(&sched.npidle, 1)
}
```

### 2.4 G 初始化

从前面的汇编代码中可以看出，在执行完`runtime.schedinit()`函数之后，就会执行`runtime.newproc()`函数来创建 G。

#### 2.4.1 runtime.newproc()

`runtime.newproc()`函数会获取当前 G 以及调用方的程序计数器，然后通过`runtime.newproc1()`函数获取新的 G 结构体实例，并将其放入到 P 的`runnext`字段中。

源码如下：

```go
func newproc(siz int32, fn *funcval) {
  argp := add(unsafe.Pointer(&fn), sys.PtrSize)
  gp := getg()
  pc := getcallerpc() // 获取调用者的程序计数器 PC
  systemstack(func() {
    newg := newproc1(fn, argp, siz, gp, pc) // 创建新的 G 结构体
    _p_ := getg().m.p.ptr()
    runqput(_p_, newg, true) // 将 G 放入到 P 的运行队列中
    // mainStarted 为 true 表示主 M 已经启动，可以唤醒 P 来执行 G 了
    if mainStarted {
      wakep()
    }
  })
}
```

#### 2.4.2 runtime.newproc1

```go
func newproc1(fn *funcval, argp unsafe.Pointer, narg int32, callergp *g, callerpc uintptr) *g {
  _g_ := getg()
  if fn == nil {
    _g_.m.throwing = -1 // do not dump full stacks
    throw("go of nil func value")
  }
  acquirem() // 加锁，禁止 G 的 M 被抢占
  
  siz := narp
  siz = (siz + 7) &^ 7
  _p_ := _g_.m.p.ptr()

  newg := gfget(_p_) // 从 P 的空闲列表 gFree 中获取空闲 G
  if newg == nil {
    newg = malg(_StackMin) // 创建一个栈大小为 2K 的 G
    // 将 G 状态改为 _Gdead，这是为了将其添加到全局 G 链表中后，避免 GC 扫描器对该 G 的未初始化的栈进行扫描
    casgstatus(newg, _Gidle, _Gdead)
    allgadd(newg)
  }
  // ...
  // 计算运行空间大小（对空间进行扩增，以读取略超栈帧空间的数据）
  totalSize := 4*sys.RegSize + uintptr(siz) + sys.MinFrameSize
  totalSize += -totalSize & (sys.SpAlign - 1) // align to SpAlign
  sp := newg.stack.hi - totalSize
  spArg := sp
  // ...
  if narg > 0 {
    memmove(unsafe.Pointer(spArg), argp, uintptr(narg)) // 从 argp 参数开始的位置复制 narg 个字节到 spArg 中（参数拷贝）
  }
  // 清理、创建并初始化 G
  memclrNoHeapPointers(unsafe.Pointer(&newg.sched), unsafe.Sizeof(newg.sched))
  newg.sched.sp = sp
  newg.stktopsp = sp
  newg.sched.pc = funcPc(goexit) + sys.PCQuantum // +PCQuantum so that previous instauction is in same function
  newg.sched.g = guintptr(unsafe.Pointer(newg))
  go startcallfn(&newg.sched, fn)
  newg.gopc = callerpc
  newg.ancestors = saveAncestors(callergp)
  newg.startpc = fn.fn
  if _g_.m.curg != nil {
    newg.labels = _g_.m.curg.labels
  }
  if siSystemGoroutine(newg, false) {
    atomic.Xadd(&sched.ngsys, +1)
  }
  // 将 G 状态变更为 _Grunnable 状态
  casgstatus(newg, _Gdead, _Grunnable)
  newg.goid = int64(_p_.goidcache)
  _p_.goidcache++
  // ...
  releasem(_g_.m) // 对应上面的 acquirem 调用

  return newg
}
```

`runtime.newproc1()`函数比较长，主要做了以下处理：

1. 从 P 的空闲列表 gFree 中查找空闲 G；

2. 如果获取不到 G 就通过`runtime.malg()`函数创建一个新的 G。需要注意的是，创建的 G 的栈上内存占用为 2K，并且设置状态为`_Gdead`，然后加入到全局的 allgs 列表中；

3. 根据要执行的函数的入口地址和参数，初始化执行栈的 SP 和参数的入栈位置，并通过`memmove()`函数进行参数拷贝；

4. 清理、初始化新创建的 G，将 G 的状态改为`_Grunnable`，然后返回该 G。

#### 2.4.3 runtime.gfget()

从 P 的空闲链表中获取一个 G 的时候，会从其链表的头部返回。而且如果 P 的空闲 G 列表为空，就会从 sched(全局) 持有的空闲 G 列表中一次性转移 32 个 G 过去。

源码如下：

```go
func gfget(_p_ *p) *g {
  retry:
    // 如果 P 的空闲列表 gFree 为空，且 sched 的空闲列表 gFree 不为空，则进行转移
    if _p_.gFree.empty() && (!sched.gFree.stack.empty() || !sched.gFree.noStack.empty()) {
      lock(&sched.gFree.lock)
      for _p_.gfree.n < 32 {
        gp := sched.gFree.stack.pop()
        if gp == nil {
          gp = sched.gFree.noStack.pop()
          if gp == nil {
            break
          }
        }
        sched.gFree.n--
        _p_.gFree.push(gp)
        _p_.gFree++
      }
      unlock(&sched.gFree.lock)
      gotoretry
    }
    // 如果此时 gFree 还是空，那么就直接返回空
    gp := _p_.gFree.pop()
    if gp == nil {
      return nil
    }
    // ...
    return gp
}
```

从 sched 中转移空闲 G 的时候，会从`sched.gFree.stack`和`sched.gFree.noStack`两个列表中进行转移。

#### 2.4.4 runtime.runqput()

回到`runtime.newproc()`函数，在调用完`runtime.newproc1()`函数之后，会再调用`runtime.runqput()`函数将获取到的 G 放入到 P 的运行列表中。

```go
func runqput(_p_ *p, gp *g, next bool) {
  if randomizeScheduler && next && fastrand()%2== 0 {
    next = false
  }
  if next {
  retryNext:
    oldnext := _p_.runnext
    // 将 G 放入到 runnext 中作为下一个处理器要执行的任务
    if !_p_.runnext.cas(oldnext, guintptr(unsafe.Pointer(gp))) {
      goto retryNext
    }
    if oldnext == 0 {
      return
    }
    // 将原来的 runnext 的 G 放入到运行队列中
    gp = oldnext.ptr()
  }

retry:
  h := atomic.LoadAcq(&_p_.runqhead)
  t := _p_.runqtail
  // 放入到 P 本地运行队列
  if t-h < uint32(len(_p_.runq)) {
    _p_.runq[t%uint32((len(_p_.runq))].set(gp)
    aotimc.StoreRel(&_p_.runqtail, t+1)
    return
  }
  // P 本地队列放不下了，就放入到全局的运行队列中
  if runqputslow(_p_, gp, h, t) {
    return
  }
  goto retry
}
```

`runtime.runqput()`函数中，会根据 next 来判断是否需要将 G 放入到 P 的 runnext 中。放在这个位置的话，会使得下次调度的时候立即开始执行该 G。

当 next 为 false 的时候，会先尝试将传入的 G 放入到 P 的本地队列中。P 的本地 G 队列是一个大小为 256 的环形链表。如果本地链表放不下，则会调用`runtime.runqputslow()`方法将 G 放入到全局队列的`runq`中。

综上可知，G 会优先放在 P 本地的队列中，放不下才会存到全局队列中。而且在一些情况下会优先将 G 加入到 runnext 位置，以便最快的执行加入的 G。

![](http://cnd.qiniu.lin07ux.cn/markdown/1648627566383-41e1c3a4b59b.jpg)

## 三、调度循环

下面再了解下 Go runtime 中调度器是怎么流转的。

### 3.1 runtime.mstart

在`runtime.rt0_go`中，完成初始化工作后，就会调用`runtime.mstart()`函数，开始调度 G：

```go
func mstart() {
  // 获取 g0
  _g_ := getg()
  
  // 确定栈边界
  osStack := _g_.stack.lo == 0
  if osStack {
    size := _g_.stack.hi
    if size == 0 {
      size = 8192 * sys.StackGuardMultiplier
    }
    _g_.stack.hi = uintptr(noescape(unsafe.Pointer(&size)))
    _g_.stack.lo = _g_.stack.hi - size + 1024
  }
  _g_.stackguard0 = _g_.stack.lo + _StackGuard
  _g_.stackguard1 = _g_.stackguard0
  
  // 启动 m 进行调度器循环调度
  mstart1()
  
  // 退出线程
  if mStackIsSystemAllocated() {
    osStack = true
  }
  mexit(osStack)
}
```

这个方法做的事情也很简单：

* 调用`getg()`方法获取 GMP 模型中的 g，此处获取到的是 g0。
* 通过检查 g 的执行栈`_g_.stack`的边界（堆栈的边界正好是 lo、hi）来确定是否为系统栈。如果是的话，则根据系统栈初始化 g 执行栈的边界。
* 调用`mstart1()`方法启动系统线程 m，进行调度器循环调度。
* 调用`mexit()`方法退出系统线程 m。

可以看到，调度器相关的实质逻辑是在`mstart1()`方法中的。

### 3.2 runtime.mstart1

源码如下：

```go
func mstart1() {
  // 获取 g 并判断是否为 g0
  _g_ := getg()
  if _g_ != _g_.m.g0 {
    throw("bad runtime·mstart")
  }
  
  // 初始化 m 并记录调用方的 pc、sp
  save(getcallerpc(), getcallersp())
  asminit()
  minit()
  
  // 当前 g 绑定的是 m0 则设置信号监听器
  if _g_.m == &m0 {
    mstartm0()
  }
  
  // 如果 g 绑定了启动函数则运行启动函数
  if fn := _g_.m.mstartfn; fn != nil {
    fn()
  }
  
  // 如果当前 g 绑定的 m 不是 m0 则需要调用 acquirep 方法获取并绑定 P
  if _g_.m != &m0 {
    acquirep(_g_.m.nextp.ptr())
    _g_.m.nextp = 0
  }
  
  // 正式开启调度
  schedule()
}
```

这里如果当前的 g 如果不是 g0，则要抛出致命错误，因为调度器仅在 g0 上运行。

另外，设置信号监听器的`mstartm0`方法需要在`asminit`方法之后调用，这样可以提前准备好线程，以便能够处理信号。

前面做了这么多的处理和准备，都是为了最终通过`runtime.schedule()`来做真正的调度。

### 3.3 runtime.schedule()

`runtime.schedule()`方法主要就是从一些地方获取要进行调度的 G。源码如下：

```go
func schedule() {
  _g_ := getg()
  if _g_.m.locks != 0 {
    throw("schedule: holding locks")
  }
  // ...
top:
  pp := _g_.m.p.ptr()
  pp.preempt = false
  // GC 等待
  if sched.gcwaiting != 0 {
    gcstopm()
    goto top
  }
  if pp.runSafePointFn != 0 {
    runSafePointFn()
  }

  // 如果 M 在 spinning 那么运行队列应该为空
  if _g_.m.spinning && (pp.runnext != 0 || pp.runqhead != pp.runqtail) {
    throw("schedule: spinning with local work")
  }
  // 运行 P 上准备就绪的 timer
  checkTimers(pp, 0)

  var gp *p
  var inheritTime bool
  // ...
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
  // ...
  // 执行 G 任务函数
  execute(gp, inheritTime)
}
```

从代码中可以看出，`runtime.schedule`函数会从下面几个地方查找待执行的 Goroutine：

1. 为了保证公平，当全局运行队列中有待执行的 Goroutine 时，通过 schedtick 保证有一定几率会从全局的运行队列中查找对应的 Goroutine；

2. 从 P 本地的运行队列中查找待执行的 Goroutine；

3. 如果前两种方法都没有找到 G，会通过`runtime.findrunnable()`函数去其他 P 里面“偷”一些 G 来执行；如果“偷”不到，就阻塞查找直到有可运行的 G。

### 3.4 runtime.globrunqget()

`runtime.globrunqget()`函数会从全局 runq 队列中获取出 n 个 G，其中第一个 G 用于调度执行，剩余的 n-1 个 G 则从全局队列放入到本地队列中。

源码如下：

```go
func globrunqget(_p_ *p, max int32) *g {
  // 如果全局队列中没有 G 则直接返回
  if sched.runqsize == 0 {
    return nil
  }
  
  // 计算 n 的个数
  n := sched.runqsize/gomaxprocs + 1
  if n > sched.runqsize {
    n = sched.runqsize
  }
  if max > 0 && n > max {
    n = max
  }
  if n > int32(len(_p_.runq))/2 {
    n = int32(len(_p_.runq)) / 2
  }

  sched.runqsize -= n
  // 先拿出全局队列队头的 G
  gp := sched.runq.pop()
  n--
  // 再将其余 n-1 个 G 从全局队列放入 P 的本地队列中
  for ; n > 0; n-- {
    gp1 := sched.runq.pop()
    runqput(_p_, gp1, false)
  }
  return gp
}
```

### 3.5 runtime.runqget()

不需要从全局队列获取，或者从全局队列中没有获取到 G 的时候，就会从 P 的本地队列中进行获取：

```go
func runqget(_p_. *p) (gp *g, inheritTime bool) {
  // 如果 runnext 不为空，则直接获取并返回
  for {
    next := _p_.runnext
    if next == 0 {
      break
    }
    if _p_.runnext.cas(next, 0) {
      return next.ptr(), true
    }
  }

  // 从本地队列头指针遍历本地队列
  for {
    h := atomic.LoadAcq(&_p_.runqhead)
    t := _p_.runqtail
    // 本地队列为空则直接返回
    if t == h {
      return nil, false
    }
    gp := _p_.runq[h%uint32(len(_p_.runq))].ptr()
    if atomic.CasRel(&_p_.runqhead, h, h+1) { // cas-release, commits consume
      return gp, false
    }
  }
}
```

从本地获取的时候，优先获取的是 P 的 runnext 中的 G；不存在 runnext 的时候才会从 P 的本地 G 列表中获取。

由于 P 的本地 G 列表是一个环形列表，所以会从本地队列头指针遍历本地队列，并且通过变更本地队列头指针的值来确认消费了这个 G。

### 3.6 runtime.findrunnable()

当从全局和本地队列中都没有获取到 G 的时候，就会尝试通过`runtime.findrunnable()`函数从其他的 P 的本地队列中偷取 P 了。

这个方法的代码很长，逻辑较多，源码如下：

```go
func findrunnable() (gp *g, inheritTime bool) {
  _g_ := getg()
top:
  _p_ := _g_.m.p.ptr()
  // 如果正在 GC，则需要休眠当前的 M，直到恢复后回到 top
  if sched.gcwaiting != 0 {
    gcstopm()
    goto top
  }
  // 运行安全点
  if _p_.runSafePointFn != 0 {
    runSafePointFn()
  }

  now, pollUntil, _ := checkTimers(_p_, 0)
  // ...
  // 从本地 P 的可运行队列中获取 G
  if gp, inheritTime := runqget(_p_); gp != nil {
    return gp, inheritTime
  }

  // 从去哪聚的可运行队列中获取 G
  if sched.runqsize != 0 {
    lock(&sched.lock)
    gp := globrunqget(_p_, 0)
    unlock(&sched.lock)
    if gp != nil {
      return gp, false
    }
  }

  // 从 I/O 轮询器获取 G
  if netpollinited() && atomic.Load(&netpollWaiters) > 0 && atomic.Load64(&sched.lastpoll) != 0 {
    if list := netpool(0); !list.empty() {
      gp := list.pop()
      injectglist(&list) // 将其余的 G 队列放入 P 的可运行 G 队列
      casgstatus(gp, _Gwaiting, _Grunnable)
      if trace.enabled {
        traceGoUnpark(gp, 0)
      }
      return gp, false
    }
  }
  // ...
  // 设置 spinning 表示正在窃取 G
  if !_g_.m.spinning {
    _g_.m.spinning = true
    atomic.Xadd(&sched.nmspinning, 1)
  }
  // 开始窃取
  for i := 0; i < 4; i++ {
    for enum := stealOrder.start(fastrand()); !enum.done(); enum.next() {
      if sched.gcwaiting != 0 {
        goto top
      }
      // i > 2 时，表示如果其他 P 运行队列中没有 G，将要从其他队列的 runnext 中获取
      stealRunNextG := i > 2 // first look for ready queues with more than 1 g
      p2 := allp[enum.position()] // 随机获取一个 P
      if _p_ == p2 {
        continue
      }
      // 从其他 P 的运行队列中获取一半的 G 到当前队列中
      if gp := runqsteal(_p_, p2, stealRunNextG); gp != nil {
        return gp, false
      }

      // 如果运行队列中没有 G，那么从 timers 中获取可执行的定时器
      if i > 2 || (i > 1 && shouldStealTimes(p2)) {
        tnow, w, ran := checkTimers(p2, now)
        now = tnow
        if w != 0 && (poolUntil == 0 || w < pollUntil) {
          pollUntil = w
        }
        if ran {
          if gp, inheritTime := runqget(_p_); gp != nil {
            return gp, inheritTime
          }
          ranTimer = true
        }
      }
    }
  }
  if ranTimer {
    goto top
  }
stop:
  // 处于 GC 阶段的话，获取执行 GC 标记任务的 G
  if gcBlackenEnabled != 0 && _p_.gcBgMarkWorker != 0 && gcMarkWorkAvailable(_p_) {
    _p_.gcMarkWorkerMode = gcMarkWorkerIdleMode
    gp := _p_.gcBgMarkWorker.ptr()
    casgstatus(gp, _Gwaiting, _Grunnable) // 将本地 P 的 GC 标记专用 G 的状态设置为 _Grunnable
    if trace.enabled {
      traceGoUnpark(gp, 0)
    }
    return gp, false
  }
  // ...
  // 放弃当前的 P 之前，对 allp 做一个快照
  allpSnapshot := allp
  lock(&sched.lock)
  // 进入了 gc，回到顶部并阻塞
  if sched.gcwaiting != 0 || _p_.runSafePointFn != 0 {
    unlock(&sched.lock)
    goto top
  }
  // 全局队列中又发现了任务
  if sched.runqsize != 0 {
    gp := globrunqget(_p_, 0)
    unlock(&sched.lock)
    return gp, false
  }
  if releasep() != _p_ {
    throw("findrunnable: wrong p")
  }
  // 将 P 放入 idle 空闲链表
  pidleput(_p_)
  unlock(&sched.lock)

  wasSpinning := _g_.m.spinning
  if _g_.m.spinning {
    // M 即将睡眠，不再处于 spnning 状态了
    _g_.m.spinning = false
    if int32(atomic.Xadd(&sched.nmspinning, -1)) < 0 {
      throw("findrunnable: negative nmspinning")
    }
  }

  // 休眠之前再次检查全局 P 列表，并检查它们的可运行的 G 队列，如果不为空则再次获取 G
  for _, _p_ := range allpSnapshot {
    if !runqempty(_p_) {
      lock(&sched.lock)
      _p_ = pidleget()
      unlock(&sched.lock)
      if _p_ != nil {
        acquirep(_p_) // 绑定 M 和 P
        if wasSpinning {
          _g_.m.spinning = true // 重新切换 M 为 spinning 状态
          atomic.Xadd(&sched.nmspinning, 1)
        }
        // 这时候是有 work 的，所以回到顶部寻找 G
        goto top
      }
      break
    }
  }

  // 休眠前再次检查 GC work
  if gcBlackenEnabled != 0 && gcMarkWorkAvailable(nil) {
    lock(&sched.lock)
    _p_ = pidleget()
    if _p_ != nil && _p_.gcBgMarkWorker == 0 {
      pidleput(_p_)
      _p_ = nil
    }
    unlock(&sched.lock)
    if _p_ != nil {
      acquirep(_p_)
      if wasSpinning {
        _g_.m.spinning = true
        atomic.Xadd()&sched.nmspinning, 1
      }
      goto top // Go back to idle GC check.
    }
  }

  // 休眠前再次检查 poll 网络
  if netpollinited() && (atomic.Load(&netpollWaiters) > 0 || pollUntil != 0) && atomic.Xchg64(&sched.lastpoll, 0) != 0 {
    // ...
    lock(&sched.lock)
    _p_ = pidleget()
    unlock(&sched.lock)
    if _p_ == nil {
      injectglist(&list)
    } else {
      acquirep(&list)
      if !list.empty() {
        gp := list.pop()
        injectglist(&list)
        casgstatus(gp, _Gwaiting, _Grunnable)
        if trace.enabled {
          traceGoUnpark(gp, 0)
        }
        return gp, false
      }
      if wasSpinning {
        _g_.m.spinning = true
        atomic.Xadd(&sched.nmspinning, 1)
      }
      goto top
    }
  } else if pollUntil != 0 && netpollinited() {
    pollerPollUntil := int64(atomic.Load64(&sched.pollUntil))
    if pollerPollUntil == 0 || pollerPollUntil > pollUntil {
      netpollBreak()
    }
  }
  stopm() // 休眠当前 M
  goto top
}
```

这个函数的逻辑主要是：

1. 首先检查是否正在进行 GC，如果是，则停止当前的 M 并阻塞，开始休眠；

2. 从本地运行队列、全局运行队列中查找 G；

3. 从网络轮询器中查找是否有 G 等待运行；

4. 将 M 的 spinning 设置为 true，表示开始窃取 G。窃取过程用了两个嵌套 for 循环，内层循环遍历 allp 中的所有 P，查看其运行队列是否有 G。有则窃取一半的 G 到当前 P 的运行队列，并返回；否则就继续遍历下一个 P。这里需要注意的是，遍历 allp 时是从随机位置获取 G 然后获取对应的 P，这样可以防止每次遍历时使用同样的顺序访问 allp 中的元素；

5. 所有的可能性都尝试过后，在准备休眠之前，进行一些额外的检查：

  * 首先检查此时是否是 GC mark 阶段，如果是则直接返回 mark 阶段的 G；
  * 再次检查全局 P 列表，遍历其中的 P，检查其可运行的 G 队列；
  * 再次检查是否有 GC mark 的 G 出现，如果有，则获取 P 并回到第一步，重新执行偷取工作；
  * 再检查是否存在 poll 网络的 G，如果有则直接返回。

6. 什么都没有找到，则休眠当前的 M。

### 3.7 runtime.execute()

在`runtime.schedule()`函数中，获取到要调度的 G 之后，就可以使用`runtime.execute()`函数来进行调度切换了。

源码如下：

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

### 3.8 runtime.goexit0()

`runtime.gogo()`函数会从`runtime.gobuf`中取出`runtime.goexit`的程序计数器和待执行函数的程序计数器，并将：

* `runtime.goexit`的程序计数器放到栈 SP 上；
* 待执行函数的程序计数器放到寄存器 BX 上。

对应的汇编代码如下：

```asm
MOVL gobuf_sp(BX), SP // 将 runtime.goexit 函数的 PC 恢复搭配 SP 中
MOVL gobuf_pc(BX), BX // 获取待执行函数的程序计数器
JMP  BX               // 开始执行
```

这样，当 Goroiutine 中运行的函数返回时，程序会跳转到`runtime.goexit`所在位置，最终在当前线程的 g0 的栈上调用`runtime.goexit0`函数：

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

`runtime.goexit0()`函数会将 G 转换为`_Gdead`状态，解绑 M 和 G 的关联关系，并清理其中的字段，然后调用`runtime.gfput`将 G 重新加入处理器的 Goroutine 空闲列表`gFree`中。

在该函数的最后，会再次调用`schedule()`，从而进入下一个调度循环。

## 四、总结

整个调度循环的简单流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1648640010216-5fa9ba22c0bb.jpg)

详细的调度过程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1648640157290-3a83196ecb5f.jpg)
