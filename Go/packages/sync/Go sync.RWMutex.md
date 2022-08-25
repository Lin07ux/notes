> 转摘：[这不会又是一个Go的BUG吧？](https://mp.weixin.qq.com/s/Dn2oM89mHEgoz8yVQnNEoQ)

`sync.RWMutex`读写锁的特点是：

* 读与读之间不互斥；
* 读与写、写与写之间互斥。

由于`sync.RWMutex`的读与读之间不互斥，所以正常情况下 R 锁是可重入的。但是当在两次读锁之间有写锁的申请，就可能会造成死锁。

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

