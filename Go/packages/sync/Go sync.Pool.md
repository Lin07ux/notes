> 转摘：[Go sync.Pool 浅析](https://mp.weixin.qq.com/s/X7xC7AlxoM6kepBtLLzOWA)

## 一、使用

### 1.1 基本示例

带 GC 功能的语言都存在垃圾回收 STW 问题，需要回收的内存块越多，STW 持续时间就越长。如果能让 new 出来的变量一直不被回收，而是重复利用，就可以减轻 GC 的压力。`sync.Pool`就是一个用来保持 new 出来的对象实例不被回收的池子。

正确的使用示例如下（选自 gin）：

```go
func (engine *Engine) ServeHTTP(w http.ResponseWriter, req *http.Request) {
  c := engine.pool.Get().(*Context)
  c.writermem.reset(w)
  c.Request = req
  c.reset()
  
  engine.handleHTTPRequest(c)
  
  engine.pool.Put(c)
}
```

一定要注意的是：是先 Get 获取对应的实例，基于这个实例做相关的处理，然后再将这个内存换回（Put）到池中。

### 1.2 使用注意点

业务开发中没有必要假想某块代码会有强烈的性能问题，一上来就用`sync.Pool`硬怼。`sync.Pool`主要是为了解决 Go GC 压力过大的问题的，所以一般情况下，当线上高并发业务出现 GC 问题需要被优化时，才需要用`sync.Pool`出场。

另外：

1. `sync.Pool`不能被复制；
2. `pool.Get`出来的实例需要进行数据的清空（reset）再 Put 回去，防止垃圾数据污染。

## 二、源码

`sync.Pool`结构如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645353622533-f5f5b6229522.jpg)

### 2.1 Get

流程图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645353693287-0295d2f66582.jpg)

简化成如下流程：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645354397227-58a17158aac2.jpg)

### 2.2 Put

流程图如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645354180934-559bc853dea4.jpg)

简化流程如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645354415560-43b4a7c16237.jpg)

### 2.3 GC

`sync.Pool`的 GC 流程如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1645354576206-0a1f06903fc5.jpg)

## 三、问题

### 3.1 sync.Pool 中的内容会清理吗？清理会造成数据丢失吗？

Go 会在每个 GC 周期内定期清理`sync.Pool`内的数据，要分几个方面来说明：

1. 已经从`sync.Pool` Get 的值，在`poolClean`时虽然已经将`pool.local`设置成了`nil`，Get 到的值已然是有效的，是被 GC 标记为黑色的，不会被 GC 回收，当 Put 后又重新加入到`sync.Pool`中；
2. 在第一个 GC 周期内 Put 到`sync.Pool`中的数据，在第二个 GC 周期没有被 GC 使用，就会被放在`local.victim`中。如果在第三个 GC 周期仍然没有被使用就会被 GC 回收。

### 3.2 `runtime.GOMAXPROCS`与`sync.Pool`之间的关系是什么？

`runtime.GOMAXPROCS(0)`是获取当前最大的 P 的数量。`sync.Pool`的 poolLocal 数量受 P 的数量影响，会开辟 P 个 poolLocal。某些场景下会使用`runtime.GOMAXPROCS(N)`来改变 P 的数量，会使`sync.Pool`的`pool.poolLocal`释放并重新开辟新的空间。

`pool.local`是个 poolLocal 结构，这个结构体是`private + shared`链表组成，在多 goroutine 的 Get/Put 下是有数据竞争的。如果只有一个 local 就需要加锁来操作。而每个 P 都有一个 local 就能减少加锁造成的数据竞争问题，所以要开辟`runtime.GOMAXPROCS`个 local。

### 3.3 `New()`的作用是什么？如果没有`New`会出现什么情况？

从`pool.Get`流程图可以看出来，从`sync.Pool`获取一个实例会尝试从当前 private、shared、其他的 P 的 shared 或 victim 中获取。如果实在获取不到时，才会调用`New()`函数来获取。也就是说，`New()`函数才是真正开辟新实例的。

如果`New()`函数没有被初始化那么`sync.Pool`就废掉了，因为没有了初始化实例的地方了，就不能进行后续的实例重复利用了。

### 3.4 先`Put`再`Get`会出现什么情况？

在`Pool.Get`注释里面有这么一句话：“Callers should not assume any relation between values passed to Put and the values returned by Get.”。也就是说，不能把值先`Pool.Put`到池中，再使用`Pool.Get`将其取出来，因为`sync.Pool`不是 map 或者 slice，放入的值是有可能拿不到的，它就不支持这么做。

下面就是一个错误的用法：

```go
func main() {
  pool := sync.Pool{
    New: func() interface{} {
      return item{}
    },
  }
  pool.Put(item{value: 1})
  data := pool.Get()
  fmt.Println(data)
}
```

这样做之所以会出现问题，是因为：

1. 情况 1：`sync.Pool`的 poolCleanup 函数在系统 GC 时会被调用，Put 到`sync.Pool`的值，由于有可能一直得不到利用，在某个 GC 周期内就有可能被释放掉了。
2. 情况 2：不同的 goroutine 绑定的 P 有可能是不一样的，当前 P 对应的 goroutine 放入到`sync.Pool`的值有可能被其他的 P 对应的 goroutine 获取到，导致当前 goroutine 再也取不到这个值。
3. 情况 3：使用`runtime.GOMAXPROCS(N)`来改变 P 的数量，会使`sync.Pool`的`pool.poolLocal`释放并重新开辟新的空间和实例，导致`sync.Pool`被释放掉。

还有很多其他的情况，也会导致先 Put 进去后，再 Get 取到的并非刚才放进去的实例。

### 3.5 只`Get`不`Put`会内存泄露吗？

通过前面的流程图可以看出来，`Pool.Get`的时候会尝试从当前 private、shared、其他的 P 的 shared 或 victim 中获取。如果还是获取不到就会调用`New`函数来获取，`New`出来的内容本身还是受系统的 GC 来控制的。所以如果提供的`New`函数不存在内存泄露的话，那么`sync.Pool`是不会出现内存蝎落的。当`New`出来的变量如果不再被使用，就会被系统 GC 给回收掉。

如果不 Put 回`sync.Pool`，会造成 Get 的时候每次都调用`New`来从堆栈申请空间，达不到减轻 GC 压力的目的。


