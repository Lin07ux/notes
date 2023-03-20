> 转摘：
> 
> 1. [Go精妙的互斥锁设计](https://mp.weixin.qq.com/s/YYvoeDfPMm8Y2kFu9uesGw)
> 2. [Go 的 TryLock 实现](https://mp.weixin.qq.com/s/Mro5XC8dS5ZAMvEVbJZsCw)

在并发变成中，为了避免多线程同时读写共享资源，需要进行互斥锁来进行读写保护。Go 标准库提供了互斥锁`sync.Mutex`来达到保护读写共享资源的目的。

## 零、介绍

### 0.1 竞争条件

多线程程序在多核 CPU 机器上访问共享资源时，难免会遇到问题。比如下面的例子：

```go
var Cnt int

func Add(iter int) {
  for i := 0; i < iter; i++ {
    Cnt++
  }
}

func main() {
  wg := &sync.WaitGroup
  for i := 0; i < 2; i++ {
    wg.Add(1)
    go func() {
      Add(100000)
      wg.Done()
    }
  }
  wg.Wait()
  fmt.Println(Cnt)
}
```

这段程序预期的结果是 200000，但是实际的输出却是不确定的，可能是 100910，也可能为 101364，或其他数值。这就是典型的多线程访问冲突问题。

![](https://cnd.qiniu.lin07ux.cn/1679289404-f8e6b577f02e5cbab8cc5c2fa867d1c1.png)

利用`go tool trace`分析工具可以看到该程序运行期间 goroutine 的执行情况如上图所示。其中 G20 和 G19 就是执行`Add()`函数的两个 goroutine，它们在执行期间并行地访问了共享变量`Cnt`，从而导致了最终结果的不准确性。

类似这种情况，即两个或多个线程读写某些共享数据，而最后的结果取决于程序运行的精确时序，这就是**竞争条件(Race Condition)**。

### 0.2 临界区与互斥

在计算机程序中，凡是涉及到共享内存、共享文件以及共享任何资源的情况都会引发类似上文例子的错误。要避免这种错误，避免竞争条件，关键是找出某种途径来阻止多线程同时读写共享的数据。换言之，需要的是互斥(Mutual Exclusion)，即以某种手段确保当一个线程在使用一个共享变量或文件时，其他线程不能做同样的操作。

对共享内存进行访问的程序片段称为临界区(Critical Section)，例如上例中的`Cnt++`片段。从抽象的角度看，希望的多线程的行为如下图所示：

![](https://cnd.qiniu.lin07ux.cn/1679289385-710ac9231f6b84becf9b4d4151dcc27a.png)

线程 A 在 t1 时刻进入临界区，执行一段时间后，在 t2 时刻线程 B 试图进入临界区，但是这是不能被允许的，因为同一时刻只能运行一个线程在临界区内，而此时已经有一个线程在临界区内了。通过某种互斥手段，将 B 暂时挂起直到线程 A 离开临界区，即 t3 时刻让 B 进入临界区。最后，B 执行完临界区代码后离开临界区。

如果能够合理的安排，使得两个线程不可能同时处于临界区，就能避免竞争条件。对于上面的例子做一些调整：

```go
var (
  Cnt int
  mu sync.Mutex
)

func Add(iter int) {
  mu.Lock()
  for i := 0; i < iter; i++ {
    Cnt++
  }
  mu.Unlock()
}
```

此时，程序执行就可以得到预期的结果 200000：

![](https://cnd.qiniu.lin07ux.cn/1679289372-c26db4d5bbfa497d1a7c41ae0b1f428b.png)

程序运行期间的执行情况如上图所示，其中 G8 和 G7 是执行`Add()`函数的两个 goroutine，通过加入`sync.Mutex`互斥锁，G8 和 G7 就不再存在竞争条件了。

需要明确的是：只有在多核机器上才会发生竞争条件，只有多线程对共享资源做了写操作时才有可能发生静态问题，只要资源没有发生变化，多个线程读取相同的资源就是安全的。

## 一、设计

Go `sync.Mutex`的设计是基于类型`Mutex`结构体的，而其中的`state`字段承担了多重功能，使得整体上保证了数据的简单，但是逻辑上并不简单。

### 1.1 预设定义

`Mutex`结构体和使用的常量定义如下：

```go
type Mutex struct {
  state int32
  sema  uint32
}

const (
  mutexLocked = 1 << iota
  mutexWoken
  mutexStarving
  mutexWaiterShift = iota // 值为 3
  starvationThresholdNs = 1e6 // 1ms，进入饥饿状态的等待时间
)
```

`state`字段表示当前互斥锁的状态信息，是`int32`类型，其第三位的二进制位均有相应的状态含义，而且分别对应着三个常量：

* `mutexLocked`是`state`中的低 1 位，用二进制表示为`0001`（只描述最低的 4 位），表示该互斥锁是否被加锁；
* `mutexWoken`是`state`的低 2 位，用二进制表示为`0010`，表示互斥锁上是否有被唤醒的 goroutine；
* `mutexStarving`是`state`的低 3 位，用二进制表示为`0100`，表示当前互斥锁是否处于饥饿模式。

`state`的高 29 位用于统计在互斥锁上的等待队列中的 goroutine 数据（waiter），也就是对应着常量`mutexWaiterShift = 3`，表示对`Mutex.state`右移 3 位即为当前互斥锁的 waiter 的数量。

`sema`字段是信号量，用于控制 goroutine 的阻塞与唤醒，常量`starvationThresholdNs`表示互斥锁从正常模式进入饥饿模式的等待时间，下文会对这两者进行介绍。

默认情况下，`state`字段（无锁状态）的值如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1653309784300-a4f0c88be4d9.jpg)

### 1.2 两种模式

`sync.Mutex`互斥锁有两种模式：**正常模式**和**饥饿模式**。

在正常模式下，waiter 按照先进先出（FIFO）的方式获取锁。但是一个刚被唤醒的 waiter 与新到达的 goroutine 竞争锁时，大概率是竞争不过的，因为新来的 goroutine 有一个优势：它已经在 CPU 上运行的。而且有可能新来的 goroutine 不止一个，因此 waiter 极有可能失败。在这种情况下，waiter 还需要在等待队列中继续排队。

为了避免 waiter 长时间抢不到锁，规定：当 waiter 超过 1ms（也就是常量`starvationThresholdNs = 1e6`）没有获取到锁，就会将当前互斥锁切换到饥饿模式，防止等待队列中的 waiter 被饿死。

在饥饿模式下，锁的所有权直接从解锁`unlocking`的 goroutine 转移到等待队列中的队头 waiter。新来的 goroutine 不会尝试去获取锁，也不会自旋，而是在等待队列的队尾排队。

如果某个 waiter 获取到了锁，并且满足以下两个条件之一，它就会将锁从饥饿模式切换回正常模式：

* 它是等待队列的最后一个 goroutine；
* 它等待获取锁的时间小于 1ms。

**与饥饿模式相比，正常模式下的互斥锁性能更好**，因为相较于将锁的所有权明确赋予给唤醒的 waiter，直接竞争锁能降低整体 goroutine 获取锁的延时开销。

饥饿模式是在 Go 1.9 版本引入的，它防止了队列尾部 waiter 一直无法获取锁的问题。

## 二、方法

`sync.Mutex`中，通过加锁`Lock()`方法和解锁`Unlock()`方法达到对共享资源的同步阻塞式的并发控制，而通过`TryLock()`方法作为一种非阻塞模式的加锁操作。

### 2.1 Lock

`sync.Mutex`的加锁操作为`Lock()`方法，代码如下：

```go
func (m *Mutex) Lock() {
  if atomic.CompareAndSwapInt32(&m.state, 0, mutexLocked) {
    if race.Enabled {
      race.Acquire(unsafe.Pointer(m))
    }
    return
  }
  m.lockSlow()
}
```

`Lock()`方法的代码非常简洁：首先通过 CAS 判断当前锁的状态。如果锁是完全空闲的，即`m.state = 0`，则对齐直接加锁，也就是设置`m.state = 1`，此时加锁后的`state`如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1653311073042-4c53c94e2aff.jpg)

如果当前锁不处于未加锁状态，则会进入`m.lockSlow()`逻辑中，下面对`m.lockSlow()`进行分段分析。

#### 2.1.1 初始化

进入`m.lockSlow()`方法之后，会先进行一些状态、标志的初始化：

```go
func (m *Mutex) lockSlow() {
  var waitStartTime int64 // 用于计算 waiter 的等待时间
  starving := false       // 饥饿模式标示
  awoke := false          // 唤醒标示
  iter := 0               // 统计当前 goroutine 的自旋次数
  old := m.state          // 保存当前锁的状态
  
  // ...
  
  if race.Enabled {
    race.Acquire(unsafe.Pointer(m))
  }
}
```

`m.lockSlow()`函数的剩余部分，也就是主体代码，是一个大的`for`循环，包括自旋和期望状态处理。

#### 2.1.2 自旋

自旋是自旋锁的行为，它通过忙等待，让线程在某段时间内一直保持执行，从而避免线程上下文的调度开销。**自旋锁对于线程只会阻塞很短时间的场景是合适的**。很显然，单核 CPU 是不适合使用自旋锁的，因为在同一时间只有一个线程能处于运行状态，如果线程自旋占用 CPU 等待锁，那占有锁的线程就没办法使用 CPU，自然无法释放锁了，这种情况下使用自旋锁的代价就很高了。

```go
func (m *Mutex) lockSlow() {
  // ....
  for {
    if old&(mutexLocked|mutexStarving) == mutexLocked && runtime_canSpin(iter) {
      // 将当前互斥锁的唤醒标识设置为 1
      // !awoke 判断当前 goroutine 不是在唤醒状态
      // old&mutexWoken == 0 表示没有其他正在唤醒的 goroutine
      // old>>mutexWaiterShift != 0 表示等待队列中有正在等待的 goroutine
      if !awoke && old&mutexWoken == 0 && old>>mutexWaiterShift != 0 &&
        atomic.CompareAndSwapInt32(&m.state, old, old|mutexWoken) {
        awoke = true  
      }
      // 自旋
      runtime_doSpin()
      iter++
      old = m.state
      continue
    }
    // ...
  }
  
  // ...
}

// runtime/proc.go
//go:linkname sync_runtime_canSpin sync.runtime_canSpin
//go:nosplit
func sync_runtime_canSpin(i int) bool {
  // sync.Mutex is cooperative, so we are conservative with spinning.
  // Spin only few times and only if running on a multicore machine and
  // GOMAXPROCS>1 and there is at least one other running P and local runq is empty.
  // As opposed to runtime mutex we don't do passive spinning here,
  // because there can be work on global runq or on other Ps.

  // active_spin = 4
  if i >= active_spin || ncpu <= 1 || gomaxrpocs <= int32(sched.npidle+sched.nmspinning)+1 {
    return false
  }
  if p := getg().m.p.ptr(); !runqempty(p) {
    return false
  }
  return true
}

```

在本场景中，之所以想让当前 goroutine 进入自旋行为，依据是：可以乐观的认为，**当前正在持有锁的 goroutine 能在较短的时间内归还锁**。

但是由于自旋本身是空转 CPU，所以如果使用不当，会降低程序运行性能，所以进入自旋行为之前需要进行一些准入判断。

结合代码来看，能够进入自旋的条件为：

* 当前互斥锁处于正常模式的加锁状态；
* 当前 gouroutine 的自旋次数小于 4
* 当前运行的机器是多核 CPU；
* GOMAXPROCS > 1；
* 至少存证一个其他正在运行的 P，且其本地运行队列（local runq）为空。

进行自旋的处理是`runtime_doSpin()`函数：

```go
// runtime/proc.go
//go:linkname sync_runtime_doSpin sync.runtime_doSpin
//go:nosplit
func sync_runtime_doSpin() {
  procyield(active_spin_cnt) // active_spin_cnt = 30
}
```

而`procyield()`是使用汇编实现的，下面是 AMD64 架构下的代码：

```asm
TEXT runtime·procyield(SB),NOSPLIT,$0-0
    MOVL   cycles+0(FP), AX
again:
    PAUSE
    SUBL   $1, AX
    JNZ    again
    RET
```

很明显，所谓的忙等待就是执行 30 次`PAUSE`指令，通过该指令来占用 CPU。

#### 2.1.3 计算期望状态

由于进入自旋状态是需要符合一定条件的。当不满足这些条件的时候，就会继续执行后续的逻辑。

首先是计算当前 goroutine 期望将锁设置成的状态：

```go
func (m *Mutex) lockSlow() {
  // ....
  for {
    // ...
    new := old
    // 加锁
    if old&mutexStarving == 0 {
      new |= mutexLocked
    }
    // 增加 waiter 数量
    if old&(mutexLocked|mutexStarving) != 0 {
      new += 1 << mutexWaiterShift
    }
    // 进入饥饿模式
    if starving && old&mutexLocked != 0 {
      new |= mutexStarving
    }
    // 重置唤醒标识
    if awoke {
      if new&mutexWoken == 0 {
        throw("sync: inconsistent mutex state")
      }
      new &^= mutexWoken
    }
  }
  // ...
}
```

这里计算期望状态分为以下几步：

1. 加锁：如果当前锁不是饥饿模式，则将期望状态的低 1 位的 Locked 状态位设置为 1；

2. 增加 waiter 数量：如果当前锁已被加锁，或处于饥饿模式，则将 waiter 数加 1，表示当前 goroutine 将被作为 waiter，置于等待队列的队尾；

3. 进入饥饿模式：如果当前 goroutine 的 starving 饥饿模式标识为 true，并且互斥锁已被加锁，则将互斥锁状态的低 3 位 Starving 状态位设置为 1，表示进入饥饿模式；

    > `starving`值的修改在后面的设置期望状态的逻辑中。

4. 重置唤醒标识：如果当前 goroutine 的 awoke 唤醒标识为 true，则表示当前 goroutine 在自旋逻辑中，已经成功将锁的 Woken 状态位设置为 1 了，此时需要将其设置会 0。

    因为，在后续的逻辑中，当前 goroutine 要么是拿到了锁，要么是被放在 waiter 队列中挂起了。如果是挂起状态，那就需要等待其他释放锁的 goroutine 来唤醒。假如其他 goroutine 在 Unlock 的时候，发现互斥锁的 Woken 状态位不是 0，就不会执行唤醒操作，那么，该 goroutine 就无法再醒来加锁了。

#### 2.1.4 更新期望状态

在计算好期望的状态之后，就需要通过 CAS 将锁的状态进行更新了：

```go
func (m *Mutex) lockSlow() {
  // ....
  for {
    // ...
    if atomic.CompareAndSwapInt32(&m.state, old, new) {
      // 如果互斥锁原先未加锁，且不处于饥饿模式，则说明当前 goroutine 已经获取到锁了，直接返回即可
      if old&(mutexLocked|mutexStarving) == 0 {
        break // locked the mutex with CAS
      }
      // 在这里就说明当前 goroutine 没有获取到锁，需要将其放到阻塞队列中，进行阻塞等待：
      // 已等待过则放在队列头，否则放在队列尾部
      queueLifo := waitStartTime != 0
      if waitStartTime == 0 {
        waitStartTime = runtime_nanotime()
      }
      runtime_SemacquireMutex(&m.sema, queueLifo, 1)
      // 被信号量唤醒之后检查当前 goroutine 是否应该处于饥饿模式（等待时间超过 1ms）
      starving = starving || runtime_nanotime()-waitStartTime > starvationThresholdNs
      // 重新获取互斥锁的状态
      old = m.state
      // 如果锁仍然是饥饿模式，则把锁直接交给被唤醒的 goroutine，也就是当前 goroutine
      if old&mutexStarving != 0 {
        // 如果锁当前既没有被加锁，也没有被唤醒的 goroutine，或者等待队列为空，则说明锁状态不一致了
        if old&(mutexLocked|mutexWoken) != 0 || old>>mutexWaiterShift == 0 {
          throw("sync: inconsistent mutex state")
        }
        // 因为当前 goroutine 已经获得锁了，所以要将等待队列数量-1
        delta := int32(mutexLocked - 1<<mutexWaiterShift)
        // 如果当前 goroutine 是等待队列的最后一个，则将互斥锁改为正常模式
        if !starving || old>>mutexWaiterShift == 1 {
          delta -= mutexStarving
        }
        atomic.AddInt32(&m.state, delta)
        // 拿到锁后需要退出，在业务逻辑处理完成之后调用 Mutex.Unlock() 方法释放锁
        break
      }
      // 锁不是饥饿模式了，因为当前 goroutine 已经被信号量唤醒了
      // 就要重置 awoke 标识和 iter 进行重置，使其能满足自旋条件，从而进入自旋等待
      awoke = true
      iter = 0
    } else {
      // CAS 更新失败，则重新获取锁状态，重新进行循环
      old = m.state
    }
  }
  // ...
}
```

从代码逻辑上来看，在计算好期望的状态之后，就使用 CAS 来尝试更新锁的状态。如果更新成功，则进行阻塞等待以及唤醒后的处理，否则就重新获取锁状态，进入下一轮循环。

在将其加入当前互斥锁的等待队列之前，会根据该 goroutine 是否已经等待过（`waitStartTime != 0`）来判断是加入到队头还是队尾：如果没有等待过，那么就先放在队尾；否则就放在队头。这样来尽量均衡每个 gouroutine 的等待时间。

同步阻塞等待是基于信号量的方式，这里就用到了 Mutex 的`sema`字段。进入等待调用的函数为`sync_runtime_SemacquireMutex()`，类似的还有`sync_runtime_Semacquire()`函数。它们的定义如下：

```go
// runtime/sema.go
//go:linkname sync_runtime_SemacquireMutex sync.runtime_SemacquireMutex
func sync_runtime_SemacquireMutex(addr *uint32, lifo bool, skipframes int) {
	semacquire1(addr, lifo, semaBlockProfile|semaMutexProfile, skipframes)
}

//go:linkname sync_runtime_Semacquire sync.runtime_Semacquire
func sync_runtime_Semacquire(addr *uint32) {
	semacquire1(addr, false, semaBlockProfile, 0)
}
```

这两个函数是用于同步库的 sleep 原语，它们都是原子递减`*s`（本场景中就是递减`m.sema`）。`runtime_SemacquireMutex()`函数用于分析竞争的互斥对象，如果参数`lifo`（本场景中为`queueLifo`）为 true，则将等待者排在队列的队头。`skipframes`是从该函数的调用方开始计数，表示在跟踪期间要忽略的帧数。

所以，运行到`runtime_SemacquireMutex()`就表明当前 goroutine 在前面的过程中获取锁时标了，需要 sleep 原语来阻塞挂起，并通过信号量来排队获取锁。

在当前 goroutine 被信号量唤醒之后，如果互斥锁当前处于饥饿模式，则说明当前 goroutine 就可以获得锁了，此时就需要将互斥锁的等待者数量减 1，并判断是否需要从饥饿模式切回正常模式。

### 2.2 Unlock

有加锁就有解锁，解锁之后要根据情况来判断是否需要唤醒等待的 goroutine：

```go
func (m *Mutex) Unlock() {
  if race.Enabled {
    _ = m.state
    race.Release(unsafe.Pointer(m))
  }
  
  // new 是解锁后的状态
  new := atomic.AddInt32(&m.state, -mutexLocked)
  if new != 0 {
    m.unlockSlow(new)
  }
}
```

解锁的时候，是通过`atomic.AddInt32()`将锁的低 1 位 Locked 状态位置为 0，然后判断新的`m.state`是否为 0。如果解锁后的状态值为 0，则表示当前锁已经完全空闲了，解锁流程结束；否则就要进入`m.unlockSlow()`逻辑中。

#### 2.2.1 空闲状态说明

这里需要注意，锁空闲有两种情况，第一种是完全空闲，它的状态就是锁的初始状态：

![](http://cnd.qiniu.lin07ux.cn/markdown/1653375535393-7cfe77a24dc6.jpg)

第二种空闲，是指当前锁没有被占用，但是会有等待拿锁的 goroutine，只是还未被唤醒。例如，下面状态的锁也是空闲的，但是有两个等待拿锁的 goroutine（未唤醒状态）：

![](http://cnd.qiniu.lin07ux.cn/markdown/1653375608814-4bd65bfbdff4.jpg)

#### 2.2.2 unlockSlow()

`m.unlockSlow()`方法代码如下：

```go
func (m *Mutex) unlockSlow(new int32) {
  // 如果前面 Unlock 解锁了一个没有上锁的锁，则会发生 panic
  if (new+mutexLocked)&mutexLocked == 0 {
    throw("sync: unlock of unlocked mutex")
  }
  
  // 正常模式
  if new&mutexStarving == 0 {
    old := new
    for {
      // 如果锁没有等待的 waiter，或者三个标志位中任何一个不为 0 则不需要进行处理了
      if old>>mutexWaiterShift == 0 || old&(mutexLocked|mutexWoken|mutexStarving) != 0 {
        return
      }
      // 到这里，表示锁是第二种空闲状态，此时将等待队列中的 waiter 数量减 1，并设置唤醒状态位
      new = (old - 1<<mutexWaiterShift) | mutexWoken
      if atomic.CompareAndSwapInt32(&m.state, old, new) {
        // 更新状态成功，则唤醒队尾的 goroutine
        runtime_Semrelease(&m.sema, false, 1)
        return
      }
      // 更新失败则重新获取锁的状态，再进行重试
      old = m.state
    }
  // 饥饿模式
  } else {
    // 信号量唤醒队头 goroutine
    runtime_Semrelease(&m.sema, true, 1)
  }
}
```

`unlockSlow()`方法分为三部分：

1. 特殊情况检测

    如果调用`Unlock()`方法来解锁一个未上锁的互斥锁，则会出现 panic。
    
2. 正常模式

    正常模式是一个大的 for 循环，里面也分为三部分逻辑：
    
    * 不做处理的情况：如果锁没有 waiter，或者锁的低三位状态位有任何一个被设置了，则不需要处理。
        - Locked 位被设置，表示互斥锁当前已经被其他 goroutine 加锁了；
        - Woken 位被设置，表示有等待的 goroutine 被唤醒了，不用再尝试唤醒其他的 goroutine 了；
        - Starving 位被设置，表示锁处于饥饿模式，那么锁之后会被直接交给等待队列的队头 goroutine。

    * 更新状态并唤醒：在这个部分，会尝试使用 CAS 将互斥锁的等待者数量减 1，然后唤醒等待队列的队尾 goroutine。

    * 更新失败：使用 CAS 更新互斥锁状态失败的时候，需要重新获取互斥锁的当前状态，然后再次尝试进行循环处理。

3. 饥饿模式

    饥饿模式下，直接通过信号量方式将等待队列的队头的 goroutine 唤醒即可。

> 疑问：在`lockSlow()`方法中，阻塞唤醒之后也会进行 waiter 数量的减一操作，`unlockSlow()`中为什么也进行减一？

这里信号量唤醒使用的是`runtime_Semrelease()`函数，它**是用于同步库的 wakeup 原语**，定于如下：

```go
//go:linkname sync_runtime_Semrelease sync.runtime_Semrelease
func sync_runtime_Semrelease(addr *uint32, handoff bool, skipframes int) {
	semrelease1(addr, handoff, skipframes)
}
```

`runtime_Semrelease()`函数会原子增加`*s`的值（本场景中是`m.sema`），并通知在`runtime_SemacquireMutex()`中阻塞等待的 goroutine。如果参数`handoff`为真，则将唤醒队头的 waiter，否则唤醒队尾的 waiter。参数`skipframes`是从调用方开始计算，表示在跟踪期间要忽略的帧数。


### 2.3 TryLock

并在 Go 1.18 中新增的方法`TryLock()`方法用于实现非阻塞模式的加锁操作，当调用该方法时，会简单的返回 true 或者 false，表示是否加锁成功。

也就是说，在加锁失败的时候，`TryLock()`方法并不会阻塞等待，而是直接返回 false。这样，就可以将原先的阻塞式加锁代码：

```go
m.Lock()
// 阻塞等待加锁成功后的逻辑
```

转变成非阻塞式的逻辑：

```go
if m.TryLock() {
  // 加锁成功的逻辑
} else {
  // 加锁失败的逻辑
}
```

`TryLock()`的实现非常简单：

```go
func (m *Mutex) TryLock() bool {
  old := m.state
  
  // 如果已经加锁，或者处于饥饿模式，立即返回 false
  if old&(mutexLocked|mutexStarving) != 0 {
    return false
  }
  
  // There may be a goroutine waiting for the mutex, but we are
  // running now and can try to grab the mutex before that
  // goroutine wakes up.
  if !aotmic.CompareAndSwapInt32(&m.state, old, old|mutexLocked) {
    return false
  }
  
  if race.Enabled {
    race.Acquire(unsafe.Pointer(m))
  }
  
  return true
}
```

可以看到，当锁已经被其他 goroutine 占有，或者当前锁正处于饥饿模式，或者加锁失败，就理解返回 false。特别是在加锁失败的时候，其并不会如`Lock()`方法那样自旋或者阻塞。

## 三、总结

从代码量而言，Go 的`sync.Mutex`互斥锁的代码非常轻量简洁，通过巧妙的位运算，仅采用一个`state`字段就实现了四个字段的效果，非常精彩。

但是其逻辑并不代表逻辑简单，互斥锁使用了两种不同的锁模式、信号量、自旋以及调度等，而且大量的使用位运算，需要逐句的阅读和理解。

**在正常模式下，waiter 按照先进先出的方式获取锁；在饥饿模式下，锁的所有权直接从解锁的 goroutine 转移等待队列中的队头 waiter。**

而从 Go 的互斥锁带有的自旋设计而言，如果通过`sync.Mutex`只锁定执行耗时很低的关键代码（例如锁定某个变量的赋值），性能是非常不错的，因为等待锁的 goroutine 不用被挂起，持有锁的 goroutine 会很快释放锁。所以，**在使用互斥锁时，应该只锁定真正的临界区**。而尽量避免如下方式写代码：

```go
mu.Lock()
defer mu.Unlock()
```

### 3.1 模式切换

如果当前 goroutine 等待锁的时间超过了 1ms，互斥锁就会切换到饥饿模式；

如果当前 goroutine 是互斥锁的最后一个 waiter，或者等待时间小于 1ms，互斥锁切换回正常模式。

### 3.2 加锁

1. 如果锁是完全空闲状态，则通过 CAS 直接加锁；

2. 如果锁处于正常模式，则会尝试自旋，通过持有 CPU 等待锁的释放；

3. 如果当前 goroutine 不再满足自旋条件，则会计算锁的期望状态，并尝试更新锁状态；

4. 在更新锁状态成功后，会判断当前 goroutine 是否能获取到锁，能获取锁则直接退出；

5. 当前 goroutine 不能获取到锁时，则会由 sleep 原语`SemacquireMutex`陷入睡眠，等待解锁 goroutine 发出信号进行唤醒；

6. 唤醒之后的 goroutine 发现锁处于饥饿模式，则能直接拿到锁，否则重置自旋迭代次数并标记唤醒位，重新进入步骤 2 中。

### 3.3 解锁

1. 如果通过原子操作`AddInt32`后，锁变为完全空闲状态，则直接解锁；

2. 如果解锁一个没有上锁的锁，则直接抛出异常；

3. 如果锁处于正常模式，则在有 waiter 等待锁，且锁处于空闲状态（Locked|Woken|Starving 标识都是空）时，尝试进行唤醒 waiter；

4. 如果锁处于饥饿模式，会直接将锁的所有权交给等待队列队头的 waiter，唤醒的 waiter 会负责设置 Locked 标志位。

