> 转摘：
> 
> 1. [Go更细粒度的读写锁设计](https://mp.weixin.qq.com/s/CEIQUa7H43FR7M1ghkDSGg)
> 2. [这不会又是一个Go的BUG吧？](https://mp.weixin.qq.com/s/Dn2oM89mHEgoz8yVQnNEoQ)

## 一、简介

为了避免竞争条件，`sync.Mutex`互斥锁只允许一个线程进入代码临界区，这就会降低程序的执行效率。而引发竞态问题除了多线程并发的访问共享资源，还需要有写操作发生。所以只要共享资源没有发生变化，多线程并发读取相同的资源就是安全的。从而可以引申出更细粒度的锁：读写锁。

### 1.1 什么是读写锁

读写锁是一种多读单写锁，分读和写两种锁：多个线程可以同时加读锁，但是写锁和写锁、读写和读锁之间是互斥的。

![](https://cnd.qiniu.lin07ux.cn/markdown/1679375810-108614cd934127003bfa0a8d9f334dac.png)

读写锁对临界区的处理如上图所示：

* t1 时刻，由于线程 1 已加写锁，线程 2 的读锁就要被互斥等待写锁的释放；
* t2 时刻，线程 2 已经加读锁，线程 3 可以对其继续加读锁并进入临界区；
* t3 时刻，线程 3 加了读锁，线程 4 的写锁就要被互斥等待读锁的释放。

### 1.2 饥饿问题

根据读写锁的性质，其适用于读写分明的场景，或者说是读多写少的场景。读写锁根据优先级可以分为两种锁：

* 读优先锁：能允许最大并发，但是写线程可能会被饿死；
* 写优先锁：能最快的写入，但是读线程可能会被饿死。

相对而言，写锁饥饿的问题更为突出：因为读锁是共享的，如果当前临界区已经加了读锁，后续的线程可以继续加读锁。但是囚一直有读锁的线程去加锁，那尝试加写锁的线程就会一直获取不到锁。这样加写锁的线程就一直被阻塞，导致了写锁饥饿。

同时，虽然多读锁共享，但是也不能将读锁去除只保留写锁，只在写操作时加写锁。这是因为：如果当前临界区加了写锁，在写锁解开之前又有新的写操作线程进来，等到写锁释放后，新的写操作线程又加上了写锁。这种情况如果连续不断，那整个程序就只能执行写操作线程，读操作线程就会被饿死了。

所以，为了避免饥饿问题，通常的做法是实现公平读写锁，它将请求锁的线程用队列进行排队，保证了先入先出（FIFO）的原则进行加锁，这样就能有效避免线程饥饿的问题。

## 二、源码分析

Go 标准库中的`sync.RWMutex`实现了读写锁，采用公平读写锁方式避免了 goroutine 饥饿问题。

### 2.1 基本组成

Go 1.15.2 中`sync.RWMutex`的基本组成如下所示：

```go
type RWMutex struct {
  w           Mutex
  writerSem   uint32
  readerSem   uint32
  readerCount int32
  readerWait  int32
}
```

其字段的作用如下：

* `w` 互斥锁`sync.Mutex`，用于互斥的写操作
* `writerSem` 加写锁时 goroutine 阻塞等待信号量，最后一个阻塞加写锁的读锁在被释放的时候，会通知阻塞的加写锁的 goroutine；
* `readerSem` 加读锁时 goroutine 阻塞等待信号量，持有写锁的 goroutine 在释放写锁后，会通知阻塞的加读锁的 goroutine；
* `readerCount` 加读锁的 goroutine 数量；
* `readerWait` 阻塞加写锁的持有读锁的 goroutine 数量。

这几个字段会相互影响，在熟悉其相关代码之后才能有更清晰的了解。

`sync.RWMutex`对外提供了四个方法接口：

* `RLock()` 加读锁
* `RUnlock()` 解读锁
* `Lock()` 加写锁
* `Unlock()` 解写锁

另外，`sync.RWMutex`中还定义了一个常量：`rwmutexMaxReaders = 1 << 30`，表示读写锁所能接收的最大读锁加锁的 goroutine 数量。

`readerCount`代表当前加的读锁的数量，在没有加写锁的时候，每次加读锁都会使其值加 1，每次解读锁会使其值减 1。其最大值即为`rwmutexMaxReaders`，即最大支持`2^30`个并发读。**当加了写锁的时候，`readerCount`的值会被改为负值，以此表明当前有写锁存在，其他加读锁的请求就要被阻塞。**

> 以下的代码都是去除了竞态检测的逻辑部分，即`if race.Enabled {}`代码块。

### 2.2 RLock() 加读锁

`sync.RWMutex`加读锁的操作很简单：

```go
func (rw *RWMutex) RLock() {
  if atomic.AddInt32(&rw.readerCount, 1) < 0 {
    runtime_SemacquireMutex(&rw.readerSem, false, 0)
  }
}
```

其核心逻辑就是通过`atomic.AddInt32()`将`rw.readerCount`原子性的加 1：

* 如果增加之后小于 0，则通过`runtime_SemacquireMutex`原语阻塞在`rw.readerSem`信号量上，直到被唤醒；
* 否则，说明已经加读锁成功，直接返回。

这里实现的功能就是：如果已经写锁存在了，则当前 goroutine 需要等待写锁被释放；如果没有写锁，就直接增加读锁数量后返回。

这里的关键在于判断条件为何是`rw.readerCount < 0`上。`rw.readerCount`表示当前读操作的 goroutine 数量，正常来说是不会为负值的，但是`sync.RWMutex`对这个字段赋予了多重作用，使其在负值时表示有写锁存在，在后续的代码中会有更清晰的说明。

### 2.3 RUnlock() 解读锁

`sync.RWMutex`的解锁分两步：快速路径和慢速路径。快速路径就是直接减小`readerCount`即可，慢速路径则需要唤醒加写锁被阻塞的 goroutine。

对应的代码如下：

```go
func (rw *RWMutex) RUnlock() {
  if r := atomic.AddInt32(&rw.readerCount, -1); r < 0 {
    rw.rUnlockSlow(r)
  }
}

func (rw *RWMutex) rUnlockSlow(r int32) {
  if r+1 == 0 || r+1 === -rwmutexMaxReaders {
    race.Enable()
    throw("sync. RUnlock of unlocked RWMutex")
  }
  if atomic.AddInt32(&rw.readerWait, -1) == 0 {
    runtime_Semrelease(&rw.writerSem, false, 1)
  }
}
```

与加读锁相反，在解读锁的时候，需要将`readerCount`进行原子性的减 1。由于读锁会阻塞写锁，所以在释放读锁的时候需要判断是否有写锁被阻塞，有的话就判断当前是不是最后一个读锁持有者，是的话就调用`runtime_Semrelease`原语对写锁信号量`writerSem`执行 V 操作。

首先，当`readerCount`减 1 之后如果不小于 0，表示当前还有读锁没有被释放，那么当前的解读锁就什么也不用做，直接退出，表示解读锁成功。否则说明当前可能是有写锁被阻塞的，需要进入慢速通道去处理。

在`rUnlockSlow()`方法中，先判断是不是没有加读锁就去解读锁而导致`readerCount`变为负值的情况，是的话就要抛出异常了。判断的条件有两个：

* `r+1 == 0` 在没有加读锁和写锁的时候，`readerCount`的值就是 0，对其减 1 就变成了 -1，此时就会满足这个条件；
* `r+1 == -rwmutexMaxReaders` 在加写锁的时候，会将`readerCount`加上`-rwmutexMaxReaders`而变为负值，所以如果这个条件满足也说明当前并没有读锁存在。

没有异常的时候，就会判断当前是不是最后一个读锁，判断的条件是`readerWait == 1`（也就是代码中的`atomic.AddInt32(&rw.readerWait, -1) == 0`）。`readerWait`代表加写锁被阻塞时，读锁的数量，在写锁尝试加锁的时候会为该值赋值。如果该值为 1，代表当前是最后一个阻塞写操作的 goroutine，则要唤醒被阻塞的加写锁的 goroutine。

### 2.4 Lock() 加写锁

在读写锁中，写锁和读锁、写锁都互斥，所以加写锁的时候一旦存在任何锁，就会被阻塞住。下面是`sync.RWMutex`中加写锁`Lock()`方法的代码：

```go
func (rw *RWMutex) Lock() {
  rw.w.Lock()
  r := atomic.AddInt32(&rw.readerCount, -rwmutexMaxReaders) + rwmutexMaxReaders
  if r != 0 && atomic.AddInt32(&rw.readerWait, r) != 0 {
    runtime_SemacquireMutex(&rw.writerSem, false, 0)
  }
}
```

在加写锁的时候，首先会对互斥锁加锁，这样能保证只有一个写锁加锁成功，其他的写锁会被阻塞直到这个写锁被释放。

当互斥锁加锁成功之后，写锁加锁就会通过将`readerCount`改为负值来让读锁加锁操作感知到已加了写锁，从而阻止加读锁的操作。这也是前面`RLock()`加读锁时，判断`readerCount`是否为负值以及在负值时阻塞住自己的原因。

但是互斥锁加锁成功并不意味着加写锁成功，只能说明当前没有其他写锁存在。但是如果有读锁存在，也会阻塞加写锁的。所以这里还要判断当前是否有读锁存在，从而决定是否阻塞自己。

读锁存在的时候，会使如下的两个条件被满足：

1. `r != 0` 也就是当前的`readerCount`不为 0，因为读锁加锁成功时必然会修改`readerCount`的值，使其不为 0；
2. `readerWait + r != 0` 没有写锁时，`readerWait`为 0，所以其加上当前读锁数量就必然不为 0 了。

`readerWait`表示写锁加锁被阻塞时有效的读锁的数量，没有写锁时其值为 0。所以`atomic.AddInt32(&rw.readerWait, r)`就是将当前有效的读锁数量作为`readerWait`的值，用来表示在当前写锁能加锁成功前，需要等待这些读锁被释放。

在解读锁`RUnlock()`方法中，释放读锁的时候，如果判断有写锁存在就会进入慢路径，并将`readerWait`的值减 1，原因也在于此处。

在有读锁存在的时候，当前加写锁的 goroutine 就会被 sleep 原语`runtime_SemacquireMutex`给阻塞，直到其他 goroutine 在解读锁`RUnlock()`时唤醒它。

### 2.5 Unlock() 解写锁

解写锁与加写锁相对，其操作基本相反：

```go
func (rw *RWMutex) Unlock() {
  r := atomic.AddInt32(&rw.readerCount, rwmutexMaxReaders)
  if r >= rwmutexMaxReaders {
    race.Enable()
    throw("sync: Unlock of unlocked RWMutex")
  }
  for i := 0; i < int(r); i ++ {
    runtime_Semrelease(&rw.readerSem, false, 0)
  }
  rw.w.Unlock()
}
```

解写锁的时候主要的操作为：

1. 将`readerCount`变更为正值，因为加写锁的时候`readerCount`被设置为负值，以使加读锁时能感受到写锁的存在，自然在解写锁的时候就要将其改为正值；
2. 唤醒对应数量的等待加读锁的 goroutine，因为有写锁的时候，加读锁时会增加`readerCount`的数量，并阻塞自己，所以在解写锁的时候就要将这些数量的 goroutine 给唤醒；
3. 释放互斥锁，写锁加锁期间会一直持有互斥锁，这样其他的写锁就无法加锁成功。当前的写锁解锁之后就要将互斥锁释放掉，以让其他的写锁能加锁成功。

从这里也能看出，`sync.RWMutex`中，读锁的优先级其实是比写锁的优先级高的：在写锁加锁期间，又有其他的读锁和写锁加锁请求，虽然它们都会被阻塞，但是在当前写锁释放时，会优先让读锁加锁成功，然后再释放互斥锁让其他的写锁能去尝试加锁。

### 2.6 加锁顺序

通过对`sync.RWMutex`源码的分析，可以得到其解决饥饿问题的流程如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1679669267-d9b7f3bb192c0c9b01ce4eb735f0425b.png)

其中绿色为加读锁的 goroutine，红色为加写锁的 goroutine。

1. G1、G2、G3 正在共享读锁，此时`readerCount = 3`；

2. G4 尝试加写锁，把`readerCount`的值改为了负值，同时因为`readerCount`在改变之前不为 0，说明有读锁存在，G4 就要被阻塞住；

3. 在 G4 等待期间，G5 和 G6 尝试加读锁，G7 尝试加写锁，此时：

    - 由于`readerCount`为负值，所以 G5 和 G6 无法加锁成功，但是依旧会增加`readerCount`的值，表示在等待写锁释放；
    - 由于互斥锁被 G4 持有，所以 G7 无法获得互斥锁，需要继续等待获取互斥锁。

4. G4 释放写锁后：

    - 先唤醒 G5 和 G6，然后它们就可以成功共享读锁；
    - 再释放互斥锁，使 G7 能被唤醒并获得互斥锁，但是由于 G5 和 G6 已经加了读锁，所以 G7 就被阻塞住，进入第 2 步的处理。

### 三、总结

`sync.RWMutex`读写锁基于互斥锁实现，提供了更细粒度的控制，适用于读写分明的场景，准确而言是读操作远多于写操作的情况。在多度少写的场景中，使用读写锁替代互斥锁能有效的提高程序运行效率。

读读共享、读写互斥和写写互斥，在优先级方面，偏袒读锁或者写锁要分几种情况：

* 锁空闲：此时是完全公平的，谁先进来谁就可以成功加锁；
* 只有写锁：此时会退化成互斥锁，只有在互斥锁处于饥饿模式下才会公平；
* 只有读锁：读锁可以共享，此时读写锁退化成无锁设计（也并不是真正的无所，因为加解锁时均有原子操作）；
* 已有读锁时：如果没有 goroutine 尝试加写锁，则后续的读锁可以成功加锁；如果有 goroutine 尝试加写锁，则后续的读锁将会被阻塞；尝试加写锁时，goroutine 会被阻塞，直到这些已成功加锁的读锁都解锁；
* 已有写锁时：所有读锁和写锁加锁都会被阻塞，而且在当前写锁释放时优先唤醒读锁，然后再释放互斥锁让写锁可以尝试加锁。

因为读写锁是基于互斥锁之上的设计，不可避免的多做了一些工作。因此，并不是说使用读写锁的效益一定会比互斥锁高。在选择使用何种锁时，需要综合考虑读写操作的比例、临界区代码的耗时。

### 四、问题

### 4.1 同一个进程多次加锁造成死锁

由于`sync.RWMutex`的读与读之间不互斥，所以正常情况下 R 锁是可重入的。但是当**在同一个 goroutine 中，两次加读锁之间有写锁的申请，就可能会造成死锁**。

`RWMutex.Rlock()`的源码如下：

```go
// RLock locks rw for reading
//
// It should not be used for recursive read locking; a blocked Lock
// call excludes new readers from acquiring the lock. See the
// documentation on the RWMutex type.
func (rw *RWMutex) RLock() {
    if race.Enabled {
        _ = rw.w.state
        race.Disable()
    }
    if atomic.AddInt32(&rw.readerCount, 1) < 0 {
        // A writer is pending, wait for it.
        runtime_SemacquireMutex(&rw.readerSem, false, 0)
    }
    if race.Enabled {
        race.Enable()
        race.Acquire(unsafe.Pointer(&rw.readerSem))
    }
}
```

在`RLock()`方法中的第二个`if`中，会判断 rw 锁当前是否有写锁在等待，如果有的话，那么读锁就要等写锁。

这就意味着：如果一个协程已经拿到了读锁，另一个协程尝试加写锁，这时写锁是无法加锁成功的，属于正常表现；但是如果这个已经获取到了读锁的协程再去拿读锁，需要等写锁，这样就造成了死锁了！

比如，对于如下的示例：

```go
var wg sync.WaitGroup
wg.Add(1)

// 1
lock.RLock()
fmt.Println("rlock 1...")

// 2
go func() {
    lock.Lock()
    fmt.Println("lock ...")
    time.Sleep(time.Second)
    lock.Unlock()
    fmt.Println("unlock ...")
    wg.Done()
}()

// 3
lock.RLock()
fmt.Println("rlock 2...")
lock.RUnlock()
fmt.Println("runlock 1...")
lock.RUnlock()
fmt.Println("runlock 2...")
```

这段代码按照 1/2/3 的顺序执行：第 2 段的写锁需要等第 1 段的读锁的释放，然后第 3 段读锁需要等第 2 段的写锁的释放。但是第 3 段和第 1 段的读锁都是在同一个协程中申请的，所以就造成了死锁的情况。

这里面最关键的一点就是：**已经拿到了读锁的协程，再次进入读锁时需要等待写锁**。

在 Go 语言的 GitHub issue 中有一个与此相关的问题：[Read-locking shouldn't hang if thread has already a write-lock? #30657](https://github.com/golang/go/issues/30657)，有人有如下的回答：

> That's not how locks in Go work. Locks in Go know nothing about gouroutines or threads, just code calling order. Not can you upgrade or downgrade a RW lock.

也就是说，Go 的锁是不知道协程或线程信息的，只知道代码调用先后顺序，即读写锁无法升级或降级。所以，在 Go 中获取了读锁之后再次获取读锁，这里的逻辑是区分不了是持有者还是其他的协程，所以就统一处理。

这点其实在 RWMutex 的 Go 源码的注释中体现了：

```go
// If a goroutine holds a RWMutex for reading and another goroutine might
// call Lock, no goroutine should expect to be able to acquire a read lock
// until the initial read lock is released. In particular, this prohibits
// recursive read locking. This is to ensure that the lock eventually becomes
// available; a blocked Lock call excludes new readers from acquiring the
// locks.
type RWMutex struct {
    // ...  
}
```

