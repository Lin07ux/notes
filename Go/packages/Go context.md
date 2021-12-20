> [Context源码，再度重相逢](https://mp.weixin.qq.com/s/hxy9P2-xJIBM-lgLInnVcA)

## 一、基础

context 的核心作用是存储键值对和取消机制。其中，存储键值对比较简单，而取消机制较为复杂。

## 二、源码

context 中抽象的`Context`接口如下：

```go
type Context interface {
  // 如果是 timerCtx 或者自定义的 ctx 实现了此方法，返回截止时间和 true，否则返回 false
  Deadline() (deadline time.Time, ok bool)
  // 监听取消信息
  Done() <-chan struct{}
  // ctx 取消时返回对应的错误，有 context canceled 和 context deadline exceeded
  Err() error
  // 返回 key 对应的 val
  Value(key interface{}) interface{}
}
```

### 2.1 ctx 存储键值对

键值对 ctx 比较简单，调用`context.WithValue()`方法即可得到一个`valueCtx`类型的对象。`valueCtx`结构体内部很简单，有一个`Context`接口和`k-v`键值对：

```go
type valueCtx struct {
  Context
  key, val interface{}
}
```

`valueCtx`结构体实现了`Value`方法，逻辑也非常的简单：

```go
func (c *valueCtx) Value(key interface{}) interface{} {
  if c.key == key {
    return c.val
  }
  return c.Context.Value(key)
}
```

`valueCtx`是个链表模型，所以从其中取值时：先判断当前 ctx 的 key 是否与要取值的 key 相同，相同就返回当前的 value；否则继续从其内部的`Context`上取值。

示例如下：

```go
func main() {
  ctx := context.Backgroun()
  
  ctx1 := context.WithValue(ctx, "name", "uutc")
  ctx2 := context.WithValue(ctx1, "age", "18")
  ctx3 := context.WithValue(ctx2, "traceID", "89asd7yu9asghd")
  
  fmt.Println(ctx3.Value("name"))
}
```

当从 ctx3 中查找`name`这个 key 时，最终要走到 ctx1 中才能返回对应的 value。图示如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639932703900-7b7ec13e613d.jpg)

虽然链表的查找效率是`O(n)`，但是一个请求里也并不会有上千个 ctx 层级，所以这样的链表查找倒也是可以接受的。

### 2.2 ctx 的取消机制

context 的取消机制可以分为两种：一种是**普通取消**，需要取消 ctx 时调用`cancel()`函数；另一种是根据**时间取消**，用户可以定义一个过期的 time 或者 deadline，到这个时间时就会自动取消。

时间取消的基础就是普通取消，只是一个是定时自动取消，另一个则是由用户自行控制取消。

#### 2.2.1 取消基本原理

创建 ctx 时一般都是在父级 ctx 的基础上封装一层，并添加自己的属性。不同的协程可能持有不同的 ctx，若想在请求层面做协程取消，就需要广播机制。

比如，在下图中：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639932957228-f4cd5d2d272b.jpg)

如果要取消 ctx2，应分为**向上取消**和**向下取消**两部分：

* 向下取消要把当前节点的子节点都取消掉，在这里需要取消掉 ctx4、ctx5；
* 向上取消需要把它在父节点中删除，在这里就是需要把其自身（ctx2）从父节点 ctx 的子节点列表中删除（如果父节点有子节点列表的话）。

取消这个动作本身并没有神奇的地方：ctx 创建一个 Channel，然后 goroutine 通过 select 语法去监听这个 Channel。没有数据时，goroutine 处于阻塞状态，当调用取消函数时，函数内部执行`close()`方法关闭 Channel，然后 select 监听到关闭信号时就会执行 return 返回，达到了取消的目的。

实例如下：

```go
func main() {
  done := make(chan struct{})
  
  go func() {
    close(done)
  }()
  
  select {
  case <-done:
    println("exit!")
    return
  }
}
```

Go 源码中，首先抽象出来一个`canceler`接口，这个接口里最重要的就是`cancel`方法，调用这个方法就可以发送取消信号。

```go
type canceler interface {
  cancel(removeFromParent bool, err error)
  Done() <-chan struct{}
}
```

普通取消`cancelCtx`和时间取消`timerCtx`都实现了这个接口。

#### 2.2.2 cancelCtx 普通取消

`cancelCtx`是 context 取消机制的基石，其结构体定义如下：

```go
type cancelCtx struct {
  Context
  
  mu       sync.Mutex
  done     atomic.Value
  children map[canceler]struct{}
  err      error
}
```

`cancelCtx`结构体中，除了包含一个父 Context 对象，还增加其他几个字段：

* `mu` 并发锁，用来实现并发安全；
* `done` 用于接收 ctx 的取消信号，之前的类型是`chan struct{}`，后面做个优化，改成了`atomic.Value`类型；
* `children` 存储此节点的实现取消接口的子节点。在当前节点取消时，会遍历该 map 为其全部子节点发送取消信号；
* `error` 调用取消函数时会赋值给这个字段。

生成一个可取消的 ctx，需要调用`WithcCancel`函数。这个函数的内部逻辑如下：

```go
func WithCancel(parent Context) (ctx Context, cancel CancelFunc) {
  if parent == nil {
    panic("cannot create context from nil parent")
  }
  c := newCancelCtx(parent)
  propagateCancel(parent, &c)
  return &c, func() { c.cancel(true, Canceled) }
}
```

该方法的逻辑很简单，就是调用`newCancelCtx()`方法基于父节点创建一个新的`cancelCtx`对象，并使用`propagateCancel()`方法将其挂载到父节点上。

##### 2.2.2.1 propagateCancel 挂载到父节点

`propagateCancel()`的源码如下：

```go
func propagateCancel(parent Context, child canceler) {
  // 判断父节点的 done 是否为 nil，是的话则为不可取消的 ctx，直接返回
  done := parent.Done()
  if done == nil {
    return
  }
  
  // 看能否从 done 中读取到数据，可以的话说明父节点已经取消，直接取消子节点返回即可
  select {
  case <-done:
    child.cancel(false, parent.Err())
    return
  default:
  }
  
  // 调用 parentCancelCtx 函数，看是否能找到 ctx 上层最接近的可取消的父节点
  if p, ok := parentCancelCtx(parent); ok {
     p.mu.Lock()
     // 可取消的祖先节点有 err，说明已经取消，直接取消子节点
     if p.err != nil {
       child.cancel(false, p.err)
     } else {
       // 否则把本节点挂载到可取消的祖先节点的 children map 中
       if p.children == nil {
         p.children = make(map[canceler]struct{})
       }
       p.children[child] = struct{}{}
     }
     p.mu.Unlock()
  } else {
    // 没有找到可取消的祖先节点的话，就起一个协程将其自身作为最顶级的可取消节点
    atomic.AddInt32(&goroutines, +1)
    go func() {
      select {
      // 监听到父节点取消时，将子节点也进行取消
      case <-parent.Done():
        child.cancel(false, parent.Err())
      // 监听到子节点取消时，什么都不用做，退出协程即可
      case <-child.Done():
      }
    }()
  }
}
```

从这段代码可以看到：

1. 挂载当前节点到父节点中时，分为两种情况：祖先节点中有可取消的和无可取消的 context；
2. 节点取消时，需要传入一个 bool 值，挂载到父节点时都是传入 false。

##### 2.2.2.2 parentCancelCtx 查找祖先可取消节点

在挂载到父节点过程中，调用了`parentCancelCtx`方法来从其祖先节点中逆序查找第一个可取消的节点。

该方法原先是使用`for{}`循环寻找合适的节点，之后改为使用`parent.Value()`方法查找父节点是否能找到特定的 key。由于`Context.Value()`方法是递归的，所以这里表面上是看不出来循环的特征的：

```go
func parentCancelCtx(parent Context) (*cancelCtx, bool) {
  done := parent.Done()
  if done == closedchan || done == nil {
    return nil, false
  }
  
  p, ok := parent.Value(&cancelCtxKey).(*cancelCtx)
  if !ok {
    return nil, false
  }
  
  pdone, _ := p.done.Load().(chan struct{})
  if pdone != done {
    return nil, false
  }
  
  return p, true
}
```

可以看出，只要从祖先节点中无法找到正确的`done`值，那么就是没有找到可取消的父节点，此时就要走`propagateCancel()`方法的`else`分支。

这里之所以可以从 ctx 中取到`cancelCtxKey`这个 key 的值，是因为`cancelCtx`结构体重写了`Value()`方法：

```go
func (c *cancelCtx) Value(key interface{}) interface{} {
  if key == &cancelCtxKey {
    return c
  }
  return c.Context.Value(key)
}
```

而且，在通过`parent.Value(&cancelCtxKey).(*cancelCtx)`找到对应的 cancelCtx 之后，后续的`p.done.Load()`得到的值还需要和父元素的`done`值相比较，两者不同的话也会判断失败。这样就能避免用户自定义实现的定制 cancelCtx 随意提供了一个 Done Channel 的情况。

比如，自定义一个 Context：

```go
type ContextCancel struct {
  context.Context
}

func (*ContextCancel) Done() <-chan struct{} {
  ch := make(chan struct{}, 100)
  return ch
}

func main() {
  ctx := context.Background()
  ctx1, _ := context.WithCancel(ctx)
  ctx2 := context.WithValue(ctx1, "hello", "world")
  ctx3 := ContextCancel{ctx2}
  ctx4, _ := context.WithCancel(&ctx3) // 这里就会走到 propageteCancel 的 else 分支
  
  println(ctx4)
}
```

由于自定义的`ContextCancel`结构体的`Done`方法只是简单返回一个新创建的 Channel，所以当使用这个`ContextCancel`来构建 cancelCtx 时，挂载到父节点就会走入到` propagateCancel()`方法的`else`分支。

##### 2.2.2.3 cancel 方法

在取消 ctx 的时候，调用的`cancel`方法的第一个参数是一个 bool 值，它其实表示的是否要将当前 ctx 从其父节点中移除。

`cancel`方法的定义如下：

```go
func (c *cancelCtx) cancel(removeFromParent bool, err error) {
  // 取消时必须传入 err
  if err == nil {
    panic("context: internal error:: missing cancel error")
  }
  
  c.mu.Lock()
  // 已经出错了，说明已经取消，直接返回
  if c.err != nil {
    c.mu.Unlock()
    return
  }
  
  // 将用户传入的 err 赋值给 ctx
  c.err = err
  d, _ := c.done.Load().(chan struct{})
  if d == nil {
    // 这里其实和关闭 Channel 差不多，因为后续会用 closedchan 作判断
    c.done.Store(closedchan)
  } else {
    // 关闭 Channel
    close(d)
  }
  
  // 这里是向下取消，依次取消此节点所有的子节点
  for child := range c.children {
    child.cancel(false, err)
  }
  
  // 清空子节点
  c.children = nil
  c.mu.Unlock()
  
  // 这里是向上取消，取消此节点和父节点的联系
  if removeFromParent {
    removeChild(c.Context, c)
  }
}
```

`removeChild`函数的逻辑也比较简单，核心就是调用`delete`方法，在父节点的子节点中清空自己：

```go
func removeChild(parent Context, child canceler) {
  p, ok := parentCancelCtx(parent)
  if !ok {
    return
  }
  p.mu.Lock()
  if p.children != nil {
    delete(p.children, child)
  }
  p.mu.Unlock()
}
```

所以，当 ctx 调用自身的`cancel`函数时，传入的是`true`，其他情况都传入的是`false`。

#### 2.2.3 时间取消

时间取消 ctx 可以传入两种时间：超时时间戳、ctx 持续时间。持续时间在实现上是在`time.Now()`的基础上加上了 timeout 后的时间戳，本质上都是调用`WithDeadline()`函数来生成。

`WithDeadline()`函数内部会新建一个`timerCtx`类型实例：

```go
type timeCtx struct {
  cancelCtx
  timer    *time.Timer  // 统一计时器，后续通过 time.AfterFunc 使用
  deadline time.Time    // 过期时间戳
}
```

可以看到，`timeCtx`内嵌了`cancelCtx`，实际上在**超时取消**这件事上，`timerCtx`更多负责的是**超时**相关的逻辑，而**取消**主要调用的是`cancelCtx.cancel`方法。

##### 2.2.3.1 WithDeadline

`WithDeadline` 函数的逻辑如下：

```go
func WithDeadline(parent Context, d time.Time) (Context, CancelFunc) {
  if parent == nil {
    panic("cannot create context from nil parent")
  }
  
  // 如果 parent 有超时时间且过期时间早于参数 d，那么 parent 取消时 child 一定要取消
  // 直接通过 WithCancel 包装父节点即可
  if cur, ok := parent.Deadline(); ok && cur.Before(d) {
    return WithCancel(parent)
  }
  
  // 构造一个 timerCtx，主要传入一个过期时间
  c := &timerCtx{
    cancelCtx: newCancelCtx(parent),
    deadline:  d,
  }
  
  // 把这个节点挂载到父节点上
  propagateCancel(parent, c)
  
  // 若子节点已过期，直接取消
  dur := time.Until(d)
  if dur <= 0 {
    c.cancel(true, DeadlineExceeded)
    return c, func() { c.cancel(false, Canceled) }
  }
  
  c.mu.Lock()
  defer c.mu.Unlock()
  
  // 否则设置一个定时执行的取消操作
  if c.err == nil {
    c.timer = time.AfterFunc(dur, func() {
      c.cancel(true, DeadlineExeceeded)
    })
  }
  
  // 返回 ctx 和一个取消函数
  return c, func() { c.cancel(true, Canceled) }
}
```

##### 2.2.3.2 timerCtx.cancel 方法

`timerCtx.cancel()`方法的源码如下：

```go
func (c *timerCtx) cancel(removeFromParent bool, err error) {
  // 取消其下游的 ctx
  c.cancelCtx.cancel(false, err)
  
  // 取消它上游的 ctx 的连接
  if removeFromParent {
    removeChild(c.cancelCtx.Context, c)
  }
  
  // 把 timer 暂停掉
  c.mu.Lock()
  if c.timer != nil {
    c.timer.Stop()
    c.timer = nil
  }
  c.mu.Unlock()
}
```

#### 2.2.4 其他 Context

常用的 Context 还有`Context.TODO`和`Context.Background`，它俩的其实都是`emptyCtx`实例，只是不是同一个而已。

```go
// An emptyCtx is never canceled, has no values, and has no deadline. It is not
// struct{}, since vars of this type must have distinct addresses.
type emptyCtx int

var (
	background = new(emptyCtx)
	todo       = new(emptyCtx)
)

```




