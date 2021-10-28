> 转摘：
> 
> * [Go并发编程(十二) Singleflight](https://lailin.xyz/post/go-training-week5-singleflight.html)
> * [原来sync.Once还能这么用](https://mp.weixin.qq.com/s/RxOrP2NFV_zTkS0jVZnCKQ)
> 
> 本文基于 [sync@v0.0.0-20210220032951-036812b2e83c](https://pkg.go.dev/golang.org/x/sync@v0.0.0-20210220032951-036812b2e83c/singleflight) 进行分析。

## 一、源码分析

### 1.1 类型结构定义

SingleFlight 这个库中，最主要的是三个类型结构`Group`、`Result`和`call`，分别用来对外提供功能、通过 channel 传递结果和对内实现调用信息保存和通知：

```go
type Group struct {
  mu sync.Mutex       // protects m
  m  map[string]*call // lazily initialized
}

type call struct {
  wg        sync.WaitGroup
  val       interface{}      // 函数的返回值，只会写入一次
  err       error
  forgotten bool             // 调用了 Forgot 方法
  dups      int              // 统计调用次数以及返回的 channel
  chans     []chan<- Result
}

type Result struct {
	Val    interface{} // 执行的结果
	Err    error       // 执行中的错误
	Shared bool        // 是否共享结果
}
```

这里的`Group`类型中，有一个`sync.Mutex`类型的属性，用来进行数据操作的锁操作，避免并发操作。

`Group.m`是一个 map 类型，键是 string 类型的，就是调用`Group.Do`和`Group.DoChan`方法传入的 key。另外，`Group.m`是延迟初始化的，也就是说在初始化`Group`实例的时候不需要声明`Group.m`的值就可以直接使用，在需要的时候`Group`的方法会自动完成初始化的。

`call`结构是一个库的内部结构，用来保存当前调用对应的信息，包括当前正在等待的组、用户操作（函数）的返回值和错误信息、统计数据等。

### 1.2 Do()

`Group.Do()`方法是最常用的一个方法，代码分析如下：

```go
func (g *Group) Do(key string, fn func() (interface{}, error)) (v interface{}, err error, shared bool) {
  g.mu.Lock()
  
  // 前面提到的延迟初始化
  if g.m == nil {
    g.m = make(map[string]*call)
  }
  
  // 先检查是否已存在 key，存在不需要实际执行了
  if c, ok := g.m[key]; ok {
    c.dups++
    g.mu.Unlock()
    c.wg.Wait()
    
    if e, ok := c.err.(*panicError); ok {
      panic(e)
    } else if c.err == errGoexit {
      runtime.Goexit()
    }
    return c.val, c.err, true
  }
  
  // 不存在说明这个 key 未执行对应的操作，需要执行
  c := new(call)
  c.wg.Add(1)
  g.m[key] = c
  g.mu.Unlock()
  
  g.doCall(c, key, fn)
  return c.val, c.err, c.dups > 0
}
```

`Group.Do()`方法的逻辑主要分为两部分：

1. 当前 key 已存在

    这表明已经有相同的操作在执行/执行过了，本次操作就不需要重复执行了，等待最终的执行结果即可。
    
    所以这里的操作也相对比较简单，就是从`Group.m`中取出对应的`call`实例后，累加`call.dups`后就立即释放锁（锁会限制并发量），然后通过`sync.WaitGroup.Wait()`进行等待，直到进行真正操作的地方通知执行结果。
    
    在得到结果之后还需要进行错误的处理。处理的时候会区分 panic 错误和 runtime 的错误，这是为了避免会出现死锁，后面会看到为什么要这样做。
    
    最后就可以返回执行的结果了，包括执行得到的结果值、执行返回的错误以及是否共享了结果（必然是共享的，总是为`true`）。

2. 当前 key 不存在

    这表明这个操作是第一次执行，那么就要做一些初始化操作，并进行真正的执行了。
    
    首先是生成一个新的`call`实例，并且通过`call.wg.Add(1)`来表示要进入阻塞等待了，其他来执行这个操作的就会在`call.wg.Wait()`的地方一直阻塞住了。
    
    然后将这个新的`call`实例存入到`Group.m`的 map 中，并释放`Group`的锁，之后就通过`Group.doCall()`方法来实际执行用户传入的`fn`方法了。
    
    执行完成之后即可返回最终的结果。而且返回的结果中，是否分享了最终的结果是通过`call.dups`的值是否大于 0 来生成的，因为一旦有别的执行进来这个值就会被累加了的。
    
### 1.3 DoChan()

`Group.DoChan()`方法和`Group.Do()`方法类似，只是后者是一个同步等待，而前者是一个异步返回。

主要实现上就是，`Group.DoChan()`会给`call.chans`中添加一个 channel。这样，在第一个调用执行完毕之后，就会循环向这些 channel 中写入数据，完成数据共享和通知的功能。

源码如下：

```go
func (g *Group) DoChan(key string, fn func() (interface{}, error)) <-chan Result {
  ch := make(chan Result, 1)
  g.mu.Lock()
  if g.m == nil {
    g.m = make(map[string]*call)
  }
  
  // 如果已存在调用则需要等待
  if c, ok := g.m[key]; ok {
    c.dups++
    c.chans = append(c.chans, ch)
    g.mu.Unlock()
    return ch
  }
  
  // 未存在调用则执行调用
  c := &call{chans: []<-chan Result{ch}}
  c.wg.Add(1)
  g.m[key] = c
  g.mu.Unlock()
  
  go g.doCall(c, key, fn)
  
  return ch
}
```

可以看到，`DoChan()`和`Do()`两者的主要区别在于如何构造`call`实例和如何进入等待：

1. `DoChan()`在一开始就初始化了一个容量为 1 的 channel；
2. 在判断已经存在调用时，就返回这个 channel，由调用方自行进入等待；
3. 如果还未执行调用，则开启一个 goroutine 同样使用`Group.doCall()`执行实际的调用，并返回 channel。

需要注意的是，在`DoChan()`方法中，当是第一次调用时也会执行`c.wg.Add(1)`，这是为了使用同一个 key 的发起方，在使用`Do()`方法时依旧会被阻塞。

### 1.4 Forget()

这个是用于手动释放某个 key，这样下次调用就不会阻塞等待了：

```go
func (g *Group) Forget(key string) {
  g.mu.Lock()
  if  c, ok := g.m[key]; ok {
    c.forgotten = true
  }
  delete(g.m, key)
  g.mu.Unlock()
}
```

这个方法很简单，主要功能就是从`Group.m`中删除相关的 key。不过在删除之前会判断这个 key 是否存在，如果存在的话，要需要将 key 对应的`call`实例的`forgotten`字段设置为`true`。这个属性在后面的`doCall()`方法中出现错误时会有用到，此时设置为 true 可以避免重复删除。

### 1.5 doCall()

这个是 SingleFlight 库中最主要的方法，而且也是最长的。它主要是用来执行真正的用户操作，但是也做了很多的错误处理和阻塞解除/结果通知的功能。

源码如下：

```go
func (g *Group) doCall(c *call, key string, fn func() (interface{}, error)) {
  normalReturn := false
  recovered := false
  
  // 第一个 defer 检查 runtime 错误
  defer func() { /*...*/ }()
  
  func() {
    // 第二个 defer 检查用户操作 fn 的错误
    defer func() {
      if !normalReturn {
        // Ideally, we would  wait to take a stack trace until we've determined
        // whether this is a panic or a runtime.Goexit.
        // 
        // Unfortunately, the only way we can distinguish the two is to see
        // whether the recover stopped the goroutine from terminating, and by
        // the time we know that, the part of the stack trace relevant to the
        // panic has been discarded.
        if r := recover(); r != nil {
          c.err = newPanicError(r)
        }
      }
    }()
    
    c.val, c.err = fn()
    normalReturn = true
  }()
  
  if !normalReturn {
    recovered = true
  }
}
```

在`doCall()`方法中，设置了两个 defer 操作，分别用于捕获 runtime 和 fn 的 panic，这样就避免了由于传入的方法的 panic 导致的死锁。这也是前面`Do()`方法在处理错误的时候，为何需要对错误进行区分的原因。

第一个 defer 的代码后续再进行分析，先看主流程中的代码。

`doCall()`方法使用了一个立即执行的闭包来实际调用用户的 fn 方法，这样主要是为了能够将前面说的两个 defer 的作用域和作用时机进行分开。

在这个闭包中，首先定义了一个 defer 用于将 fn 的 panic 转换成`panicError`错误复制给`call.err`，并且这个`panicError`中还包含了当前执行的调用栈信息，方便后续的使用。代码中的注释部分也解释了为何在这里就记录调用栈信息。

然后就是正常的执行用户的 fn，并将相关结果分别赋值给`call.val`和`call.err`。

匿名函数执行之后，还会在判断一次变量`normalReturn`的值是不是`false`来更新变量`recovered`的值。因为，如果`fn()`发生了 panic，那么匿名函数中的最后一句`normalReturn = true`就不会执行，其依旧保持为`false`。而匿名函数中的 defer 会 recover 掉`fn()`的 panic，使得`doCall()`的最后一个条件判断还能继续执行到。如果最后一个条件执行到了，也就说明`doCall()`没有触发 runtime exit，这样就可以将`recoverd`变量的值改成`true`。

总结来说：

1. 如果`fn()`没发生任何问题，那么必然有`normalReturn = true, recovered = false`；
2. 如果`fn()`发生了 runtime exit 而不是 panic，那么必然有`normalReturn = false, recovered = false`；
3. 其他时候，`normalReturn`和`recovered`必然不相等（不同为`true`或`false`）。

再来看第一个 defer 中的代码：

```go
defer func() {
  // the given function invoked runtime.Goexit
  if !normalReturn && !recovered {
    c.err = errGoExit
  }
  
  c.wg.Done()
  g.mu.Lock()
  defer g.mu.Unlock()  
  if !c.forgotten {
    delete(g.m, key)
  }
  
  if e, ok := c.err.(*panicError); ok {
    // In order to prevent the waiting channels from being blocked forever,
    // needs to ensure that this panic cannot be recovered.
    if len(c.chans) > 0 {
      go panic(e)
      select {} // Keep this goroutine around so that it will appear in the crash dump.
    } else {
      panic(e)
    }
  } else if c.err == errGoexit {
    // Already in the process of goexit, no need to call again
  } else {
    // Normal return
    for _, ch := range c.chans {
      ch <- Result{c.val, c.err, c.dups > 0}
    }
  }
}()
```

这个 defer 中有很多小技巧存在。

首先，判断用户传入的`fn()`是不是没有正常执行、但又没有被 recover（通过`normalReturn`和`recovered`变量的值）。如果是的话，就说明需要直接退出了，所以就把当前`call.err`设置为了`errGoexit`错误。

然后，使用`call.wg.Done()`来通知所有同步阻塞的调用方，调用执行完成了，可以进行结果处理了。

同时，还要判断当前这个`call`是不是已经被调用`Group.Forget()`方法给删除了。如果不是，则进行删除操作，已对 key 忽略，使其下次调用不用被阻塞；如果是，则说明它本身已经不存在于`g.m`中了，不能再删除了，否则可能会删掉新的调用方。

> 也就是说，调用`Group.Forget()`方法后，这个 key 对应的`call`示例已经被移除了。此时再用这个 key 来一次调用，就会重新向`Group.m`中插入一个新的`key-*call`键值对。如果这个时候，在`doCall()`中再次删除，就会将新增加进去的`key-*call`给删除掉了——因为它们的 key 相同。

最后，要对`call.err`进行判断：

1. 如果是`panicError`实例，则重新触发 panic；
2. 如果是`errGoexit`实例，则不需要做任何事情，因为已经准备退出了。
3. 否则，就作为正常情况，需要向`call.chans`中包含的所有 channel 都写入数据，使得异步阻塞的调用能正常得到结果。

需要注意的是第一种情况，重新触发 panic 的时候，为了避免异步等待的 channel 死锁，需要新开一个 goroutine 来触发 panic，然后使得当前的 goroutine 死锁。这样就能使得当前的 goroutine 会一直等待，直到 panic 的发生。

### 1.6 总结

通过上面的源码分析，可以知道，SingleFlight 的主要思想就是：

* 第一个调用先触发阻塞（同步`sync.WaitGroup`、异步`channel`），然后再执行调用；
* 后来者通过`sync.WaitGroup.Wait()`或`<-chan`来等待阻塞解除，然后共享第一个调用的结果。

这样就实现了同时的多个调用变成一个调用的目的。

## 二、自定义实现

### 2.1 使用通道同步阻塞

同步阻塞也是可以使用`<-chan`来实现的：

```go
package main

import (
  "fmt"
  "sync"
  "time"
)

type CacheEntry struct {
  data []byte
  err  error
  wait chan struct{}
}

type OrderServer struct {
  cache map[string]*CacheEntry
  mutex sync.Mutex
}

func (order *OrderServer) Query(key string) ([]byte, error) {
  order.mutex.Lock()
  if order.cache == nil {
    order.cache = make(map[string]*CacheEntry)
  }
  
  if entry, ok := order.cache[key]; ok {
    order.mutex.Unlock()
    // 等待实际调用方的完成通知
    <-entry.wait
    return entry.data, entry.err
  }
  
  entry := &CacheEntry{
    data: make([]byte, 0),
    wait: make(chan struct{})
  }
  order.cache[key] = entry
  order.mutex.Unlock()
  
  // 请求调用
  entry.data, entry.err = getOrder()
  // 请求完毕，通知其他调用方可以取数据了
  close(entry.wait)
  
  return entry.data, nil
}

func getOrder() ([]byte, error) {
  time.Sleep(50 * time.Millisecond)
  return []byte("hello world"), nil
}
```

### 2.2 使用 sync.Once 实现一次调用

`sync.Once`能够保证一个操作只执行一次，常用于单例对象的初始化场景，也可以用来实现 SingleFlight 这样的一次调用的需求：

```go
package main

import (
  "fmt"
  "sync"
  "time"
)

type CacheEntry struct {
  data []byte
  err  error
  once *sync.Once
}

type OrderServer struct {
  cache map[string]*CacheEntry
  mutex sync.Mutex
}

func (order *OrderServer) Query(key string) ([]byte, error) {
  order.mutex.Lock()
  if order.cache == nil {
    order.cache = make(map[string]*CacheEntry)
  }
  
  entry, ok := order.cache[key];
  
  // 不存在就初始化一个
  if !ok {
    entry = &CacheEntry{
      data: make([]byte, 0),
      wait: new(sync.Once)
    }
    order.cache[key] = entry
  }
  order.mutex.Unlock()
  
  // 请求调用（只会执行一次）
  entry.once.Do(func() {
    entry.data, entry.err = getOrder()
  })
  
  return entry.data, nil
}

func getOrder() ([]byte, error) {
  time.Sleep(50 * time.Millisecond)
  return []byte("hello world"), nil
}
```

其实查看`sync.Once`的源码就可以知道，它其实就是使用`sync.Mutex`来加锁，并通过原子变更整数的方式来保证只会执行一次。实现起来更简洁，功能也就更单一。


