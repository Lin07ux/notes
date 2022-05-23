> 转摘：
> 
> 1. [Go 的 TryLock 实现](https://mp.weixin.qq.com/s/Mro5XC8dS5ZAMvEVbJZsCw)

在并发变成中，为了避免多线程同时读写共享资源，需要进行互斥锁来进行读写保护。Go 标准库提供了互斥锁`sync.Mutex`来达到保护读写共享资源的目的。

## 二、方法

`sync.Mutex`中，通过加锁`Lock()`方法和解锁`Unlock()`方法达到对共享资源的同步阻塞式的并发控制，而通过`TryLock()`方法作为一种非阻塞模式的加锁操作。

### 2.1 Lock

### 2.2 Unlock

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

