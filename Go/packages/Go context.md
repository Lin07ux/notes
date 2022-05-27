> [Context源码，再度重相逢](https://mp.weixin.qq.com/s/hxy9P2-xJIBM-lgLInnVcA)

## 一、基础

context 的核心作用是存储键值对和取消机制。其中，存储键值对比较简单，而取消机制较为复杂。

### 1.1 整体结构

context 的整体结构如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1653479313772-f1d6c67b032d.jpg)

### 1.2 接口

context 中包含了两个接口，一个是可导出基础接口的，一个是不可导出的可取消接口：

* `Context` 基本键值 Context 接口，定义了 4 个方法；
* `canceler` 可取消的 Context 接口，定义了 2 个方法。

### 1.3 结构体

这些实现了 Context 接口的结构体都是包内结构，不能被外部感知。

* `emptyCtx` 实现了 Context 接口，每个方法都是空实现，底层是 int 类型；
* `valueCtx` 实现了 Context 接口，可以用于存储 key-value 信息，最为基本的 Context 实现；
* `cancelCtx` 实现了 Context 和 canceler 接口，除了能够存储 key-value，还能被取消；
* `timerCtx` 继承自 cancelCtx，实现了 Context 和 canceler 接口，除了能够存储 key-value，能够被取消，还能够超时取消。

### 1.4 函数类型

* `CancelFunc` 定义了用于取消可取消 Context 的函数的签名

### 1.5 函数

下面这些可导出的函数都是用于方便的进行 Context 的创建：

* `Background()` 返回一个固定的 emptyCtx 实例（空 Context），常用来作为根 Context；
* `TODO()` 返回一个固定的 emptyCtx 实例（空 Context，与`Background()`返回的不是同一个实例），常用于开发初期没有合适的 Context 可用的时候。
* `WithValue()` 基于父 Context 创建一个能存储 key-value 的 Context；
* `WithCacel()` 基于父 Context 创建一个可取消的 Context；
* `WithTimeout()` 基于父 Context 创建一个有超时时间的 Context；
* `WithDeadline()` 基于父 Context 创建一个有截止时间的 Context。

下面这些不可导出的函数则是用于包内部创建 Context 的辅助函数：

* `newCancelCtx()` 创建一个可取消的 Context，也就是`cancelCtx`类型实例；
* `propagateCancel()` 当父 Context 被取消时，向下传递取消事件，以取消子 Context；
* `parentCancelCtx()` 找出当前 Context 的最近的一个可被取消的父 Context；
* `removeChild()` 将当前的可取消的 Context 从其最近的可取消的父 Context 中移除；

### 1.6 变量

可导出变量是两个特定的错误：

* `Canceled` 一个 error，表示 Context 已被取消；
* `DeadlineExceeded` 一个 error，表示 Context 已超时。

不可导出的变量则是用于特殊的标记或预定义的 Context 实例：

* `background` 一个预声明的`emptyCtx`实例，用作`Background()`函数的返回；
* `todo` 一个预声明的`emptyCtx`实例，用于`Todo()`函数的返回；
* `closedchan` 一个与初始化的`chan struct{}`类型的变量，用于消息通知；
* `cancelCtxKey` 底层类型是 int，用作获取可取消 Context 实例的 key 的标识。

## 二、源码

### 2.1 接口

#### 2.1.1 Context 接口

`Context`接口定义了四个方法，如下：

```go
type Context interface {
  // 返回被取消的实际
  Deadline() (deadline time.Time, ok bool)
  
  // 监听取消信息
  Done() <-chan struct{}
  
  // 取消时返回对应的错误
  Err() error

  // 返回 key 对应的 val
  Value(key interface{}) interface{}
}
```

`Context`接口的这四个方法都是幂等的，连续多次调用同一个方法，得到的结果都是相同的：

* `Deadline()` 返回当前 Context 被取消的时间。如果没有设置截止时间，则`ok`为 false；

* `Done()` 返回一个只读的 Channel。当 Context 被主动取消或者超时自动取消时，该 Context 及其派生的子 Context 的 done Channel 都将会被关闭。而读取一个关闭的 Channel 会读出相应类型的零值，利用这点可以与 select 语法配合，实现协程控制或者超时退出等功能。

* `Err()` 返回一个 error 对象。当 done Channel 没有被关闭的时候，返回 nil；如果 done Channel 被关闭，则返回关闭的原因，一般常用的有预定义的`context.Canceled`和`context.DeadlineExceeded`两个错误。

* `Value()` 获取设置的 key 对应的 value。如果不存在对应的 key 则返回 nil。

#### 2.1.2 canceler 接口

`canceler`接口只定义了两个方法:

```go
type canceler interface {
  cancel(removeFromParent bool, err error)
  Done() <-chan struct{}
}
```

`canceler`接口中的`cancel()`方法则是一个不可导出的内部方法，用于取消当前 canceler；`Done()`方法则和`Context`接口中的`Done()`方法是相同的。

为什么不把`canceler`接口合并到`Context`接口中呢？可理解的说法是：源码作者认为`cancel()`方法并不是`Context`必须的，根据最小接口原则，将两者分开定义。

比如下面的`emptyCtx`和`valueCtx`不是可取消的，就只需要实现`Context`接口即可，`cancelCtx`和`timerCtx`是可取消的，就需要同时实现`Context`和`canceler`接口。

### 2.2 emptyCtx 实现

#### 2.2.1 定义

`emptyCtx`是一个空实现的 Context，其对 Context 接口的实现不是直接返回就是返回 nil，不能取消、不能存值、没有 deadline。通常将其用于创建根 Context 或者临时的占位 Context。

`emptyCtx`类型定义如下：

```go
// An emptyCtx is never canceled, has no values, and has no deadline. It is not
// struct{}, since vars of this type must have distinct addresses.
type emptyCtx int

func (*emptyCtx) Deadline() (deadline time.Time, ok bool) {
  return
}

func (*emptyCtx) Done() <-chan struct{} {
  return nil
}

func (*emptyCtx) Err() error {
  return nil
}

func (*emptyCtx) Value(key interface{}) interface{} {
  return nil
}

func (e *emptyCtx) String() string {
  switch e {
  case background:
    return "context.Background"
  case todo:
    return "context.TODO"
  }
  return "unknown empty Context"
}
```

可以看到，`emptyCtx`的底层是 int 类型，而不是 struct{}。这是因为每个`emptyCtx`实例都需要一个唯一的地址，而 struct{} 作为一个空对象，在分配内存空间的时候，都会指向同一个地址。

#### 2.2.2 使用

在`emptyCtx.String()`方法中用到的`background`和`todo`是 context 包中两个预声明的`emptyCtx`实例。它们分别通过包中的`Background()`函数和`TODO()`函数导出供外部使用。由于`emptyCtx`的特性，这两者都是无任何作用的、不可取消的 Context，通常是放在 main 函数或者流程处理的最顶层使用：

```go
var (
  background = new(emptyCtx)
  todo       = new(emptyCtx)
)

func Background() Context {
  return background
}

func TODO() Context {
  return todo
}
```

### 2.3 valueCtx 实现

#### 2.3.1 定义

`valueCtx`是一个 k-v 存储 Context。它通过内嵌一个 Context 实例的方式来实现 Context 接口的，其自身仅实现了 Context 接口中的`Value()`方法。

源码如下：

```go
type valueCtx struct {
  Context
  key, val interface{}
}

func (c *valueCtx) Value(key interface{}) interface{} {
  if c.key == key {
    return c.val
  }
  return c.Context.Value(key)
}

func (c *valueCtx) String() string {
  return contextName(c.Context) + ".WithValue(type " +
        reflectlite.TypeOf(c.key).String() +
        ", val " + stringify(c.val) + ")"
}
```

> `contextName()`和`stringify()`是 context 包中定义的两个辅助方法，分别用于获取 Context 的名称和对数据进行字符串化。

`valueCtx`是个链表模型，所以从其中取值时：

1. 先判断当前 Context 的 key 是否与要取值的 key 相同，相同就返回当前的 value；
2. 否则继续从其内部的`Context`上取值。

`valueCtx`查找的过程只能是从下往上的单向查找，也就是说，父 Context 不能查询子 Context 的值，同一个父级下的兄弟 Context 之间也不能互相查询对方的值。

虽然链表的查找效率是`O(n)`，但是一个请求里也并不会有上千个 Context 层级，所以这样的链表查找倒也是可以接受的。

#### 2.3.2 使用

由于`valueCtx`未导出，而且内部也含有未导出的字段，所以只能使用 context 包中的`WithValue()`函数创建并返回（返回的是`*valueCtx`指针）：

```go
func WithValue(parent Context, key, val interface{}) Context {
  if parent == nil {
    panic("cannot create context from nil parent")
  }
  if key == nil {
    panic("nil key")
  }
  if !reflectlite.TypeOf(key).Comparable() {
    panic("key is not comparable")
  }
  return &valueCtx{parent, key, val}
}
```

可以看到，在创建`valueCtx`的时候，需要保证 key 不是空，且可比较。

#### 2.3.3 示例

`valueCtx`的查值的示例如下：

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

### 2.4 cancelCtx 实现

#### 2.4.1 定义

`cancelCtx`是一种实现了普通取消机制的 Context，它是 context 取消机制的基石，其结构体定义如下：

```go
type cancelCtx struct {
  Context
  
  mu       sync.Mutex // protects following fields
  done     atomic.Value // of chan struct{}, created lazily, closed by first cancel call
  children map[canceler]struct{} // set to nil by the first cancel call
  err      error // set to non-nil by the first cancel call
}

func (c *cancelCtx) String() string {
  return contextName(c.Context) + ".WithCancel"
}
```

`cancelCtx`结构体中，除了包含一个父 Context 对象，还增加其他几个字段：

* `mu` 并发锁，用来实现并发安全；
* `done` 用于接收 ctx 的取消信号，之前的类型是`chan struct{}`，后面做个优化，改成了`atomic.Value`类型；
* `children` 存储此节点的实现取消接口的子节点。在当前节点取消时，会遍历该 map 为其全部子节点发送取消信号；
* `err` 调用取消函数时会赋值给这个字段。所以，当`done`没有被关闭时，`err = nil`；当`done`被关闭时，`err`的值就是关闭着指定的 error 实例了。

在 context 包中，预定了两个取消 context 的错误：

```go
// Canceled is the error returned by Context.Err when the context is canceled.
var Canceled = errors.New("context canceled")

// DeadlineExceeded is the error returned by Context.Err when the context's
// deadline passes.
var DeadlineExceeded error = deadlineExceededError{}

type deadlineExceededError struct {}
func (deadlineExceededError) Error() string   { return "context deadline exceeded" }
func (deadlineExceededError) Timeout() bool   { return true }
func (deadlineExceededError) Temporary() bool { return true}
```

其中，`Canceled`是主动关闭错误，`DeadlineExceeded`是超时关闭错误。

#### 2.4.2 使用

生成一个`cancelCtx`，需要调用`WithcCancel()`函数。源码如下：

```go
func WithCancel(parent Context) (ctx Context, cancel CancelFunc) {
  if parent == nil {
    panic("cannot create context from nil parent")
  }
  c := newCancelCtx(parent)
  propagateCancel(parent, &c)
  return &c, func() { c.cancel(true, Canceled) }
}

// newCancelCtx returns an initialized cancelCtx
func newCancelCtx(parent Context) cancelCtx {
  return cancelCtx{Context: parent}
}

// A CancelFunc tells an operation to abandon its work.
// A CancelFunc does not wait for the work to stop.
// A CancelFunc may be called by multiple goroutines simultaneously.
// After the first call, subsequent calls to a CancelFunc do nothing.
type CancelFunc func()
```

该方法的逻辑很简单，就是调用`newCancelCtx()`方法基于父节点创建一个新的`cancelCtx`对象，并使用`propagateCancel()`方法将其挂载到父节点上。

`newCancelCtx()`方法就是通过内嵌`parent` 父级 Context 的方式生成一个新的`cancelCtx`实例。

`propagateCancel()`方法是实现链式取消的核心方法，后续进行分析。

需要注意的是，`WithCancel()`函数的返回值有两个：一个是`cancelCtx`的实例，转成了 Context 接口类型；另一个是`CancelFunc`类型的方法，无参数，无返回值，仅用于取消当前返回的 Context 实例。

通过`CancelFunc`类型的注释可知，一个`CancelFunc`实例可能会在多个 goroutine 中同时被调用，但是除了在第一次被调用的时候会取消当前的 Context，其后的调用都不会做任何事情了。

#### 2.4.3 实现 Context 接口

`cancelCtx`自身实现了 Context 的三个方法，剩下的`Deadline()`方法则通过内嵌方式继承自父元素：

```go
// &cancelCtxKey is the key that a cancelCtx returns itself for
var cancelCtxKey int

func (c *cancelCtx) Value(key interface{}) interface{} {
  if key == &cancelCtxKey {
    return c
  }
  return c.Context.Value(key)
}

func (c *cancelCtx) Done() <-chan struct{} {
  d := c.done.Load()
  if d != nil {
    return d.(chan struct{})
  }
  c.mu.Lock()
  defer c.mu.Unlock()
  d = c.done.Load()
  if d == nil {
    d = make(chan struct{})
    c.done.Store(d)
  }
  return d.(chan struct{})
}

func (c *cancelCtx) Err() error {
  c.mu.Lock()
  err := c.err
  c.mu.Unlock()
  return err
}
```

`cancelCtx`实现的`Value()`方法中，提供了一个特殊的 key 来获取其自身。也就是可以使用 context 包中预声明的变量`cancelCtxKey`的地址作为 key 作为参数调用`Value()`，得到的就是当前`cancelCtx`实例自身。

> 由于`canceltxKey`是包内声明的，而且未导出，所以在包外是无法获得`&cancelCtxKey`的值的。

在`Done()`方法的实现中，采用了懒汉式初始其中的`done`字段。而且`done`字段由最初的`chan struct{}`类型调整为了`atomic.Value`类型。这样，在初始化`done`属性之后，利用原子操作的方式，可以不加锁取到`done`属性，避免了并发竞争锁导致的性能问题。

`Done()`方法返回的是一个`chan struct{}`的 Channel，这样当`done`未被关闭的时候，调用方可以阻塞在`done`上；当`done`关闭之后，调用方就能立即得到一个零值，而不会被阻塞住。

`Err()`方法就是简单的获取`cancelCtx.err`属性。在获取`err`属性之前也需要加锁，但是并没有像`Done()`方法中那样使用`atomic.Value`类型。这是可能是因为`Err()`方法调用的并没有`Done()`方法那么频繁吧。

#### 2.4.4 实现 canceler 接口

`cancelCtx`除了实现了 Context 接口，还实现了 canceler 接口。由于`Done()`方法在两个接口中都有，所以只需要关注 canceler 接口中`cancel()`方法的实现：

```go
// cancel closes c.done, cancels each of c's children, and, if
// removeFromParent is true, removes c from its parent's children
func (c *cancelCtx) cancel(removeFromParent bool, err error) {
  // 取消时必须传入 err
  if err == nil {
    panic("context: internal error:: missing cancel error")
  }
  
  c.mu.Lock()
  // 已经设置了错误，说明已经取消，直接返回
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

`cancelCtx.cancel()`方法的逻辑比较清晰：

1. 设置取消的错误原因；
2. 关闭`done`这个 Channel；
3. 依次取消其子级可取消 Context；
4. 将自己从其父级中移除。

向上从其父级中移除自己的操作是通过参数`removeFromParent`来控制的：当`cancelCtx`调用自身的`cancel`函数时，传入的是`true`，其他情况都传入的是`false`。

另外，关闭`done` Channel 这个地方，使用到了一个`closedchan`变量，这是在 context 包中预定于的 Channel，且在 context 包初始化的时候就被关闭了：

```go
// closedchan is a resuable closed channel.
var closedchan = make(chan struct{})

func init() {
  close(closedchan)
}
```

`cancel()`方法中最重要的一点是取消事件会向下广播和向上删除。也就是当一个`cancelCtx`被取消时，除了它自身的`done`被关闭，`err`被设置，其`children`中的所有可取消的 Context 都会被以相同的错误原因取消。而且，被取消之后，它还会主动的将自己从父级的`cancelCtx`实例的`children`中移除。

比如，在下图中：

![](http://cnd.qiniu.lin07ux.cn/markdown/1639932957228-f4cd5d2d272b.jpg)

如果要取消 ctx2，应分为**向上取消**和**向下取消**两部分：

* 向下取消要把当前节点的子节点都取消掉，在这里需要取消掉 ctx4、ctx5；
* 向上取消需要把它在父节点中删除，在这里就是需要把其自身（ctx2）从父节点 ctx 的子节点列表中删除（如果父节点有子节点列表的话）。

取消这个动作本身并没有神奇的地方：ctx 创建一个 Channel，然后 goroutine 通过 select 语法去监听这个 Channel。没有数据时，goroutine 处于阻塞状态，当调用取消函数时，函数内部执行`close()`方法关闭 Channel，然后 select 监听到关闭信号时就会执行 return 返回，达到了取消的目的。

### 2.5 timerCtx 实现

context 中除了 cancelCtx 实现的**普通取消**机制外，还有另一种根据**时间取消**机制的实现，就是 timerCtx。**时间取消的基础就是普通取消**，只是一个是定时自动取消，另一个则是由用户自行控制取消。

在 timerCtx 中，用户可以定义一个过期的 time 或者 deadline，到这个时间时就会自动取消。

#### 2.5.1 定义

timerCtx 的定义如下：

```go
type timeCtx struct {
  cancelCtx
  timer    *time.Timer  // Under cancelCtx.mu.

  deadline time.Time    // 过期时间戳
}

func (c *timerCtx) Deadline() (deadline time.Time, ok bool) {
  return c.deadline, true
}

func (c *timerCtx) String() string {
  return contextName(c.cancelCtx.Context) + ".WithDeadline(" +
    c.deadline.String() + " [" +
    time.Until(c.deadline).String() + "])"
}

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

可以看到，timerCtx 除了内嵌了一个 cancelCtx 外，还增加了一个定时器`timer`和一个截止时间`deadline`，这两者都是用于实现定时取消机制的。

实际上在**超时取消**这件事上，`timerCtx`更多负责的是**超时**相关的逻辑，而**取消**主要调用的是`cancelCtx.cancel`方法。

而 timerCtx 重写了`cancel()`方法，但仅是在 cancelCtx 的`cancel()`方法的基础上增加了停止计时器的相关操作。

#### 2.5.2 使用

有两种创建 timerCtx 的方式，分别是`WithDeadline()`函数和`WithTimeout()`函数。这两个函数分别接收超时时间戳和持续时间作为参数。而`WithTimeout()`函数则是通过将持续时间转为时间戳之后，调用`WithDeadline()`函数来实现的。

源码如下：

```go
func WithTimeout(parent Context, timeout time.Duration) (Context, CancelFunc) {
  return WithDeadline(parent, time.Now().Add(timeout))
}

func WithDeadline(parent Context, d time.Time) (Context, CancelFunc) {
  if parent == nil {
    panic("cannot create context from nil parent")
  }
  
  // 如果 parent 有超时时间且过期时间早于参数 d，那么 parent 取消时 child 一定会取消
  // 所以直接通过 WithCancel 包装父节点即可
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
  
  // 若当前节点已超时，就直接将其取消
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

### 2.6 辅助函数

在 context 包中还定义了一些辅助函数，用来实现挂载、取消挂载、Context 取消事件传播等功能。

#### 2.6.1 removeChild() 从父级 Context 中移除可 canceler

将一个可取消 Context 从父级 Context 中移除的函数`removeChild()`的逻辑比较简单，核心就是调用`delete`方法，在父节点的子节点中删除自己。

源码如下：

```go
func removeChild(parent Context, child canceler) {
  // 从祖先 Context 中逐级向上查找 cancelCtx 实例，没找到就不需要处理
  p, ok := parentCancelCtx(parent)
  if !ok {
    return
  }
  // 找到的话，就将自己从该 cancelCtx 的祖先 Context 的 children 中移除
  p.mu.Lock()
  if p.children != nil {
    delete(p.children, child)
  }
  p.mu.Unlock()
}
```

#### 2.6.2 propagateCancel() 把 canceler 挂载到父级 Context 中

`propagateCancel()`函数主要功能是：向上寻找可取消的 context，然后将当前的 canceler 挂载上去。

将可取消 context 向上挂靠是级联取消的前提，在调用父级的`cancel()`时，就可以通过向下广播的方式层层传递取消事件，从而完成子 context 的同时取消。

`propagateCancel()`的源码如下：

```go
// propagateCancel arranges for child to be canceled when parent is.
func propagateCancel(parent Context, child canceler) {
  // 父节点的 done 为 nil 时，说明 parent 为不可取消的 context，无需建立级联关系
  done := parent.Done()
  if done == nil {
    return // parent is never canceled
  }
  
  // 如果能从 done 中读取到数据，说明父节点已经取消，就应该立即取消子节点
  select {
  case <-done:
    // parent is already canceled
    child.cancel(false, parent.Err())
    return
  default:
  }
  
  // 找到上层最接近的可取消的父节点
  if p, ok := parentCancelCtx(parent); ok {
     p.mu.Lock()
     // 可取消节点有 err，说明已经取消，直接取消子节点
     if p.err != nil {
       // parent has already been canceled
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
    atomic.AddInt32(&goroutines, +1)
    // 没有找到可取消的祖先节点，将其自身作为最顶级的可取消节点
    // 并新起一个协程，监听自己的取消事件，然后将子节点给取消掉
    go func() {
      select {
      case <-parent.Done(): // 监听到父节点取消时，将子节点也进行取消
        child.cancel(false, parent.Err())
      case <-child.Done():  // 监听到子节点取消时，退出协程即可，可防止 goroutine 泄露
      }
    }()
  }
}
```

在将子 canceler 挂载到父 Context 的时候，会先进入两个前提判断：

1. 父 Context 的`done`是否存在，不存在说明肯定不是可取消的，不需要挂载了；
2. 父 Context 是否已经取消了，如果已经取消也不需要挂载了，直接将子 canceler 给取消掉即可。

完成这两个前提判断之后，会再根据能否找到父级中的 cancelCtx 来分别进行处理：

* 能找到：再次判断父级 cancelCtx 是否已取消（使用`p.err`判断），已取消的话就将子 canceler 也给取消掉；否则就需要将子 canceler 放到父级 cancelCtx 的`children`中，等待后续父级 cancelCtx 取消时被通知到。这整个过程都是加锁保护的，可以避免并发导致问题。

* 找不到：可能是因为父级节点中确实没有 cancelCtx，也可能是因为这是用户自定义实现的 Context 类型。此时就无法通过 parent Context 中的`children`建立关联，因为可能 parent 中根本就没有`children`字段。此时就需要使用单独的 goroutine 来监听 parent 的取消事件，在 parent 取消时也将子 canceler 给取消掉。而且还需要监听子 canceler 自己的取消事件，否则可能会因为 parent 一直不取消而造成 goroutine 泄露。

从这段代码可以看到：

1. 挂载当前节点到父节点中时，分为两种情况：祖先节点中有可取消的和无可取消的 context；
2. 节点取消时，需要传入一个 bool 值，挂载到父节点时都是传入 false。

#### 2.6.3 parentCancelCtx() 查找父级的 cancelCtx 实例

在 cancelCtx 将自己从其父级中删除的时候，以及将子 canceler 挂载到父级 Context 中的时候，都是通过`parentCancelCtx()`函数来查找父级中的 cancelCtx 实例的。

`parentCancelCtx()`实现如下：

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

`parentCancelCtx()`函数是从当前`cancelCtx`的祖先节点中逆序查找第一个可取消的节点。该方法原先是使用`for{}`循环寻找合适的节点，之后改为使用`parent.Value()`方法查找父节点是否能找到特定的 key。由于`Context.Value()`方法是递归的，所以这里表面上是看不出来循环的特征的。

这个函数的逻辑主要是三次取值判断：

1. 取出父级 Context 的`done`字段进行判断：

    * `done == nil` 表示父级 Context 是不可取消的，不符合条件，自然也不需要继续查找了；
    * `done == closedchan` 表示父级 Context 已经被取消了，也不需要继续查找了。

    通过这两个条件就能把非可取消的 Context 和已关闭的 Context 排除掉了。

2. 取出父级 Context 的`&cancelCtxKey`键对应的值进行判断：

    在调用父级 Context 的`Value()`方法时，传入的参数是前面提到的`cancelCtxKey`预声明变量的地址。结合`cancelCtx.Value()`方法的实现可以知道，`&cancelCtxKey`键会将 cancelCtx 自身返回。
    
    而对于非 cancelCtx 实例（比如 valueCtx 实例或自定义的 Context 实例），针对这个特定的`&cancelCtxKey`键可能会一直上递归查找到最终的 root context，或者是有其他的返回。
    
    结合下面的图会更好理解：`ctx3.Value(&cancelCtxKey)`会返回它本身的地址`&ctx3`；而由于`ctx2`是一个 valueCtx，所以`ctx2.Value(&cancelCtxKey)`肯定获取不到自己持有的 val，只能向上传递；对于`ctx1`这个 emptyCtx，`ctx1.Value(&cancelCtxKey)`返回的就是 nil 了。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1653627015374-d4acb4b13f77.jpg)
    
    最终，对于取到的值进行`*cancelCtx`类型化，如果不能正常转化，则说明这个父级 Context 并不是 cancelCtx。
    
3. 取出找到的 cancelCtx Context 的`done`与当前 Context 的`done`进行判断：
    
    这一次的判断是为了防止用户自定义的可取消 Context 造成的影响。
    
    比如，下图中的`ctx2`是自定义的可取消 Context 实例，`ctx3`和`ctx1`是 cancelCtx 实例。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1653627435328-7d89758df10c.jpg)

    当将`ctx3`作为参数来调用`parentCancelCtx()`函数的时候，`parent.Done()`得到的是`ctx.done`，而`parent.Value(&cancelCtxKey).(*cancelCtx)`取到的是`ctx1`，那么`pdone`就是`ctx1.done`了。此时`pdone`就和`done`不属于同一个 Context 了，自然也是取不到正确的父级 cancelCtx 的。


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

## 三、总结

### 3.1 综述

context 包的代码非常短，但却是并发控制的标准做饭，比如实现 goroutine 之间传递取消信号、截止时间以及一些 k-v 值等。

随便 Go 版本的迭代，context 的一些实现上也采用了更为优雅的实现方式，但是整体的流程和思路是没有太大变化的。明确 context 的目的和要解决的问题后，就能更好的从整体上理解 context 的实现和代码逻辑。

### 3.2 使用建议

* `context.Background`只应用在最高等级，作为所有派生 context 的根；
* context 取消是建议性的，这些函数可能需要一些时间来清理和退出；
* 不要把`Context`放在结构体中，要以参数的方式传递；
* 以`Context`作为参数的函数方法，应该把`Context`作为第一个参数，放在第一位；
* 给一个函数方法传递`Context`的时候，不要传递 nil，如果不知道传递什么，就使用`context.TODO`；
* `Context`的`Value`相关方法应该传递必须的数据，不要什么数据都使用这个传递。
* `context.Value`应该很少使用，它不应该被用来传递可选参数。这使得 API 隐式的并且可以引起错误。取而代之的是，这些值应该作为参数传递。
* `Context`是线程安全的，会不断的衍生新的 content，可以放心的在多个 goroutine 中传递和访问。
* `Context`结构没有取消方法，因为只有派生 context 的函数才应该取消 context。



