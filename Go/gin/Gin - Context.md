使用 gin 框架的时候，定义的请求处理器的输入参数为一个`gin.Context`的指针类型，代表请求的上下文.其包含了 HTTP 请求和响应的相关资源，在处理器的业务逻辑中，常会用到其中的字段参与业务逻辑，如`Context.Request`（请求实例）、`Context.Writer`（响应输出）。

### 初始化

gin 的 Context 对象实际上是在`Engine`对象的`ServeHTTP`方法（该方法是 net/http 中定义的 HTTP 服务接口）中初始化的：

```go
// ServeHTTP conforms to the http.Handler interface
func (engine *Engine) ServeHTTP(w htt.ResponseWriter, req *http.Request) {
  c := engine.pool.Get().(*Context)
  c.writermem.reset(w)
  c.Request = req
  c.reset()
  
  engine.handleHTTPRequest(c)
  
  engine.pool.Put(c)
}
```

在该方法中，通过池化技术获取到一个新的 Context 对象，然后对该对象进行重置。

`Engine.ServerHTTP`方法的逻辑如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/af93404bc89b67b6c120c09d7defe779.jpg)

### Request 和 Writer

Context 对象在初始化的时候，将`Engine.ServeHTTP`方法中的`http.ResponseWriter`和`*http.Request`参数分别设置为了 Context 对象的`Writer`字段和`Request`字段，分别表示 HTTP 连接的响应和请求对象。

`Context.Request`表示 HTTP 的请求实例，其是由 net/http 库从 conn 对象中解析得到的，包含请求的参数、请求头等信息。另外，`Context`对象上为请求提供了诸如`Param`、`Form`、`Header`等方法，可以方便的从请求中读取信息：

![](https://cnd.qiniu.lin07ux.cn/markdown/afa6a2bf4a48e3e6130e37472f05bee1.jpg)

`Context.Writer`是`http.ResponseWriter`实例，用于向 HTTP 请求返回数据，所以 gin 中要返回客户端数据就可以使用该对象进行写入：

![](https://cnd.qiniu.lin07ux.cn/markdown/1f95ec1dde6fc2e2fbd561515b108443.jpg)

在 net/http 的底层实现中，`response.w`属性指向的是一个`bufio.Writer`实例，表示缓冲写入器，通过它可以高效的完成数据输出。最终的写入流程如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/db2e9da5490140a6f18d07eff5b62dd6.jpg)

