> 转摘：[Go sync.Once：简约而不简单的并发利器](https://mp.weixin.qq.com/s/lO4J6cxbskac0xAnBmDeAw)

在 Go 语言中，实现资源初始化（如单例对象、配置等）的方法有多种，如：定义为`package`级别的变量、在`init`函数中初始化、在`main`函数中初始化等。这三种方式由 Go 语言的运行时来保证并发安全，并在程序启动时完成资源的初始化。

对于需要延迟初始化（使用到的时候再初始化），就需要考虑并发安全问题，此时可以使用 Go 语言中的`sync.Once`来完成初始化操作。

### 1. 简介

`sync.Once`是 Go 语言中的一个同步原语，用于确保某个操作或函数在并发环境下只被执行一次。

`sync.Once`实例只有一个导出的方法，即`Do()`，该方法接收一个函数参数，用于完成自定义的操作逻辑。

在`Do`方法被调用后，该函数将被执行，并且只会执行一次，即使在多个协程同时调用的情况下也是如此。

### 2. 应用场景

`sync.Once`主要用于以下场景：

* 单例模式：确保全局只有一个实例对象，避免重复创建资源；
* 延迟初始化：在程序运行过程中需要用到某个资源时，通过`sync.Once`动态地进行初始化；
* 只执行一次的操作：例如只需要执行一次的配置加载、数据清理等操作。

### 3. 使用实力

`sync.Once`在使用的时候需要先初始化一个`Once`实例，然后在需要的时候通过`Once`实例的`Do`方法来执行自定义的操作。一旦`Do`方法执行过一次，该`Once`实例将不会再执行别的操作。

下面是一个单例初始化的示例：

```go
package main

import (
  "fmt"
  "sync"
)

type Singleton struct{}

var (
  instance *Singleton
  once     sync.Once
)

func GetInstance() *Singleton {
  once.Do(func() {
    instance = &Singleton{}
  })
  return instance
}

func main() {
  var wg sync.WaitGroup{}

  for i := 0; i < 5; i++ {
    wg.Add(1)
    go func() {
      defer wg.Done()
      s := GetInstance()
      fmt.Printf("Singleton instance address: %p\n", s)
    }()
  }
  
  wg.Wait()
}
```

上述代码中，`GetInstance()`函数通过`once.Do()`方法确保只会初始化一次`instance`实例。在并发环境下，多个协程同时调用`GetInstance()`时，只有一个协程会执行到`once.Do()`中的匿名方法，所有协程最终得到的都是同一个实例。

### 4. 实现原理

`sync.Once`的实现中使用到了`sync.Mutex`锁以及`atomic`包的原子加载：通过原子加载来提升并发操作效率，通过`sync.Mutex`互斥锁来保证只被执行一次。

`sync.Once`的源码非常简单：

```go
type Once struct {
  // 表示是否已经执行过了操作
  done uint32
  // 互斥锁，确保多个协程并发访问时只有一个协程会执行自定义操作
  m    Mutex
}

func (o *Once) Do(f func()) {
  // 判断 done 的值是否为 0 来决定是否要指定自定义的 f 函数
  if atomic.LoadUint32(&o.done) == 0 {
    // 构建慢路径(slow path)，以允许对 Do 方法的快路径(fast path 进行内联)
    o.doSlow(f)
  }
}

func (o *Once) doSlow(f func()) {
  // 加锁
  o.m.Lock()
  defer o.m.Unlock()
  // 双重检查，避免 f 在加锁之前刚好被执行过
  if o.done == 0 {
    // 修改 done 的值
    defer atomic.StoreUint32(&o.done, 1)
    // 执行自定义操作
    f()
  }
}
```

`sync.Once`结构体中只包含了一个用来标识是否已经执行过操作的`done`字段，和一个用来避免多次执行的互斥锁`m`字段。而其唯一的`Do()`方法也很简单，就是判断未执行过自定义操作时加锁，然后执行自定义方法，执行之后修改其`done`字段的值。

之所以要将`Do`方法拆出一个`doSlow()`方法，主要是为了性能优化，这样可以将慢路径操作（也就是加锁和执行自定义函数）分离出来，使得`Do`方法能够被内联到调用处，从而提高性能。

在进入`doSlow()`方法之前，做了一次`done == 0`的检查，进入`doSlow()`后，加了互斥锁，再次检查`done`的值，是为了避免在第一次检查之后、加锁之前其他的协程完成了对`once.Do()`的调用，修改了`done`的值。因为`done`的值必然是获取到互斥锁的情况下才会被修改的。通过双重检测，可以在大多数情况下避免锁竞争，提高性能。

### 5. 增强 sync.Once

标准的`sync.Once`提供的`Do`方法并没有返回值，这意味着如果传入的函数发生了异常导致初始化失败时，后续对`Do`的调用也不能重新初始化了。

为了避免这个问题，可以实现一个增强型的`sync.Once`：

```go
type Once struct {
  done uint32
  m    sync.Mutex
}

func (o *Once) Do(f func() error) error {
  if atomic.LoadUint32(&o.done) == 0 {
    return o.doSlow(f)
  }
  return nil
}

func (o *Once) doSlow(f func() error) error {
  o.m.Lock()
  defer o.m.Unlock()
  
  var err error
  if o.done == 0 {
    err = f()
    // 只有没有发生 error 的时候，才修改 done 的值
    if err == nil {
      atomic.StoreUint32(&o.done, 1)
    }
  }
  return err
}
```

这个实现中，`Do`方法接收的函数类型为`func() error`，而且只有在自定义操作不返回 error 的时候才会修改`done`的值。这样，一旦自定义操作发生异常，后续调用`Do`的时候还能重新尝试进行初始化。

### 6. 注意事项

**死锁**

由于`sync.Once`中使用到了`sync.Mutex`，所以在使用`sync.Once`的时候需要避免死锁情况，例如下面的情况：

```go
func main() {
  once := sync.Once{}
  once.Do(func() {
    once.Do(func() {
      fmt.Println("init...")
    })
  })
}
```

**一次性**

`sync.Once`使用`done`字段来表示是否已经执行过自定义操作，所以如果对一个`sync.Once`实例调用多次`Do`方法，而且每次传入不同的函数参数，那么只有第一个获取到锁的函数被执行，其他的函数都不会执行。

**初始化失败**

对于初始化失败的情况，`sync.Once`的实现中未有支持，可以通过自定义的增强型`Once`来实现。