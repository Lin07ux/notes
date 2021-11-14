> 转摘：[Go 每日一库之 net/http（基础和中间件）](https://mp.weixin.qq.com/s/vlwX48Z6JBj6A8g7r-9dgA)

Go 中的`net/http`标准库让编写 Web 服务器的工作变的很简单。下面将结合源码和实例对`net/http`库进行探索。

### 1. 使用实例

使用`net/http`编写一个简单的 Web 服务器非常简单：

```go
package main

import (
	"fmt"
	"net/http"
)

func index(w http.ResponseWriter, r *http.Request) {
	_, _ = fmt.Fprintln(w, "Hello world!")
}

func main() {
	http.HandleFunc("/", index)
	_ = http.ListenAndServe(":8080", nil)
}
```

在`main()`函数中，首先调用`http.HandleFunc("/", index)`注册路径处理函数，这里将路径`/`的处理函数设置为`index`函数。

处理函数的类型必须是`func (http.ResponseWriter, *http.Request)`，其中：

* `*http.Request`表示 HTTP 请求对象，包含请求的所有信息，如 URL、首部、表单内容、请求头等。

* `http.ResponseWriter`是一个接口类型，用于向客户端发送响应：

```go
// net/http/server.go
type ResponseWriter interface {
  Header() Header
  Write([]byte) (int, error)
  WriteHeader(statusCode int)
}
```

实现了`http.ResponseWriter`接口的类型就实现了`io.Writer`接口，所以可以在处理函数`index()`中调用`fmt.Fprintln()`向`http.ResponseWriter`写入响应信息。

### 2. 处理函数与路由注册

`net/http.HandleFunc()`函数的源码如下：

```go
func HandleFunc(pattern string, handler func(ResponseWriter, *Request)) {
  DefaultServeMux.HandleFunc(pattern, handler)
}
```

可以发现它直接调用了一个名称为`DefaultServeMux`对象的`HandleFunc()`方法，这个对象是一个默认的`ServeMux`类型的实例。`ServeMux`中保存了注册的所有路径和处理函数对应关系。

> 这种提供默认类型实例的用法在 Go 语言的各个库中非常常见，在默认参数就已经足够的场景中使用默认实现很方便。

```go
type ServeMux struct {
  mu    sync.RWMutex
  m     map[string]muxEntry
  es    []muxEntry // slice of entries sorted from longest to shortest.
  hosts bool       // whether any patterns contain hostnames
}

type muxEntry struct {
  h       Handler
  pattern string
}

var DefaultServeMux = &defaultServeMux
var defaultServeMux ServeMux

func (mux *ServeMux) HandleFunc(pattern string, handler func(ResponseWriter, *Request)) {
  if handler == nil {
	  panic("http: nil handler")
  }
  mux.Handle(pattern, HandlerFunc(handler))
}
```

`ServeMux.HandleFunc()`方法中，会将处理函数`handler`转为`HandlerFunc`类型，然后再调用`ServeMux.Handle()`方法注册路由，因为`ServeMux.Handle()`方法只接受类型为`Handler`接口的参数。

> 这里的`HandlerFunc(handler)`是类型转换，而非函数调用。
> 
> Go 语言允许为（基于）函数的类型定义方法。

相关定义如下：

```go
type HandlerFunc func(ResponseWriter, *Request)

func (f HandlerFunc) ServeHTTP(w ResponseWriter, r *Request) {
  f(w, r)
}

type Handler interface {
  ServeHTTP(ResponseWriter, *Request)
}

func (mux *ServeMux) Handle(pattern string, handler Handler) {
  // ... lock and panic
  
  if mux.m == nil {
    mux.m = make(map[string]muxEntry)
  }
  e := muxEntry{h: hanlder, pattern: pattern}
  if pattern[len(pattern)-1] == '/' {
    mux.es = appendSorted(mux.es, e)
  }
  mux.m[pattern] = e
}
```

通过上面的代码可以清楚：`HandlerFunc`类型实现了`Handler`接口，而`ServeMux.Handle()`方法通过将路由模式处理函数构造成一个内部的`muxEntry`结构，并注入到`ServeMux.m`和`ServeMux.es`数据中，为后续的请求接收和处理提供了路由配置基础。

### 3. 处理器

`HandlerFunc`类型只是为了方便注册函数类型的处理器，而如果定义一个实现了`Handler`接口的类型，就可以直接使用`ServeMux.Handle()`注册该类型的实例了：

```go
type greeting string

func (g greeting) ServeHTTP(w http.ResponseWriter, r *http.Request) {
  fmt.Fprintln(w, g)
}

http.Handle("/greeting", greeting("Welcome, DJ"))
```

上面这段代码基于 string 类型定义了一个新的`greeting`类型，并为其添加了`ServeHTTP`方法，所以`greeting`类型也实现了`http.Handler`接口，可以将其实例注册为路由处理器使用。

可以发现，通过`http.HanldeFunc()`和`http.Handle()`方法进行的路由注册，他们底层都行一样的运行逻辑。

### 4. HTTP 服务器监听

在`main()`方法中，注册完路由之后，调用`http.ListenAndServe(":8080", nil)`就开始监听本地的 8080 端口，并提供 HTTP 服务了。

`http.ListenAndServe()`方法的源码如下：

```go
func ListenAndServe(addr string, handler Handler) error {
  server := &Server{Addr: addr, Handler: handler}
  return server.ListenAndServe()
}
```

该方法的逻辑也很简单：使用传入的参数构建一个`http.Server`类型实例，然后调用该实例上的` ListenAndServe()`方法。

其中，`http.Server`类型定义如下：

```go
type Server struct {
  Addr string
  Handler Handler   // handler to invoke, http.DefaultServeMux if nil
  TLSConfig *tls.Config
  ReadTimeout time.Duration
  ReadHeaderTimeout time.Duration
  WriteTimeout time.Duration
  IdleTimeout time.Duration
  ...
}
```

`Server`类型的属性有很多，可以使用这些属性来调整 Web 服务器的行为。如：

* `Addr`用来设置服务器监听的地址和端口；
* `Handler`默认为`http.DefaultServeMux`实例，用来提供 HTTP 请求响应功能；
* `ReadTimeout/ReadHeaderTimeout/WriteTimeout/IdleTimeout`用于控制读写和空闲超时；

在`Server.ListenAndServe()`方法中，先调用了`net.Listen()`方法监听端口，提供 TCP 服务;然后将得到的监听器`net.Listener`提供给`Server.Serve()`方法，以实现 HTTP 功能：

```go
func (srv *Server) ListenAndServe() error {
  // ...
  addr := srv.addr
  // ...
  ln, err := net.Listen("tcp", addr)
  // ...
  return srv.Serve(ln)
}
```

在`Server.Serve()`方法中，主体逻辑较为简单，就是使用一个无限的 for 循环不停的调用`Listener.Accept()`方法来接受新连接，并开启新 goroutine 处理该连接：

```go
func (srv *Server) Serve(l net.Listener) error {
  // ... variable init
  for {
    rw, err := l.Accept()
    // ... handle error
    c := srv.newConn(rw)
    c.setState(c.rwc, StateNew, runHooks) // before Serve can return
    go c.serve(connCtx)
  }
}
```

获得新的连接后，会将其封装成一个`http.conn`对象，添加一些状态，然后创建一个 goroutine 运行其` serve()`方法，实现对连接的处理。

通过上述流程可以得知：*HTTP 服务器就是在底层的 TCP 连接之上，使用 HTTP 协议完成请求的接入和响应，整个过程可以作为一个自定义的、特殊的 TCP 连接服务*（可以参考 [Go TCP 连接](./Go%20TCP%20%E8%BF%9E%E6%8E%A5.md) 相关内容）。

整个流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635311585042-ad77be959c75.jpg)

### 5. 请求处理

在前面的分析中，当服务器取到一个 TCP 连接之后，会通过`http.conn.serve()`方法来完成后续的 HTTP 请求，其源码如下：

```go
func (c *conn) serve(ctx context.Context) {
  // ... defer recover
  // ... TLS connection
  // ... variables and attributes init
  
  for {
    w, err := c.readRequest(ctx)
    
    // ... error handle
    
    serverHandler{c.server}.ServeHTTP(w, w.req)
    
    // ... clean
    
    w.finishRequest()
    
    // ... change connection properties
  }
}
```

这个方法有很多的异常处理和资源清理工作，但是主体逻辑是：*使用一个无限的 for 循环不断的从 TCP 连接中读取请求，然后构建`http.serverHandler`类型实例完成请求处理，最后完成请求的响应，进行后续清理*。

这里展示的是 HTTP/1.x 的处理逻辑，HTTP/2 的逻辑是从其他地方进行的。HTTP/1.x 虽然可以复用一个 TCP 连接发出多个请求，但是请求之间都是顺序发起的，所以这里可以使用 for 循环每次读取一个请求并进行处理。而且这里没有考虑 HTTP 管道流的情况，因为这种情况比较少见，而且更好的并行请求解决方案是 HTTP/2。

> HTTP cannot have multiple simultaneous active requests.[\*]
> Until the server replies to this request, it can't read another,
> so we might as well run the handler in the goroutine.
> [\*] Not strictly true: HTTP pipelining. We could let them all process
> in parallel even if their responses need to be serialized.
> But we're not going to implement HTTP pipelining because it
> was never deployed in the wild and the answer is HTTP/2.

`serverHandler`只是一个中间的辅助结构，代码如下：

```go
type serverHandler struct {
  srv *Server
}

func (sh serverHandler) ServeHTTP(rw ResponseWriter, req *Request) {
  handler := sh.srv.Handler
  if hanlder == nil {
    handler = DefaultServeMux
  }
  if req.RequestURI == "*" && req.Method == "OPTIONS" {
    handler = globalOptionsHandler{}
  }
  handler.ServeHTTP(rw, req)
}
```

所以`serverHandler{c.server}.ServeHTTP(w, w.req)`这段代码就是对`Server.Handler`的调用。而这个`Handler`就是调用`http.ListenAndServe()`时传入的第二个参数，为`nil`时自动使用`http.DefaultServeMux`这个实例。也就是说，默认情况下，调用的就是`ServeMux.ServeHTTP()`方法，源码如下：

```go
func (mux *ServeMux) ServeHTTP(w ResponseWriter, r *Request) {
  // ... special case handling
  h, _ := mux.Handler(r)
  h.ServeHTTP(w, r)
}

func (mux *ServeMux) Handler(r *Request) (h Handler, pattern string) {
  // ... CONNECT case handling

  host := stripHostPort(r.Host)
  
  // ... special case handling
  
  return mux.handler(host, r.URL.Path)
}

func (mux *ServeMux) handler(host, path string) (h Handler, pattern string) {
  // ...
  h, pattern = mux.Match(path)
  // ...
}
```

`ServeMux.ServeHTTP`的主要功能就是通过其`Handler()`方法根据请求（路径）来查找到注册的处理器，然后使用该处理器的`ServeHTTP()`方法处理请求，生成响应。这样就把请求和和前面注册的路由处理器/处理函数联系起来了，实现了对特定路由的注册和处理。

### 6. 路由匹配

路由匹配的逻辑在`ServeMux.match()`方法中：

```go
func (mux *ServeMux) Match(path string) (h Handler, pattern string) {
  // Check for exact match first
  v, ok := mux.m[path]
  if ok {
    return v.h, v.pattern
  }
  
  // Check for longest valid match.  mux.es  contains all patterns
  // that end in / sorted form longest to shortest.
  for _, e := range mux.es {
    if strings.HasPrefix(path, e.pattern) {
      return e.h, e.pattern
    }
  }
  return nil, ""
}
```

可以看到，匹配主要分为两步：

1. 先使用请求的路径从`ServeMux.m`中进行精确匹配；
2. 匹配不到的情况下，从`ServeMux.es`中循环匹配路径的最长前缀。

也就是说，默认的 HTTP 服务器会优先使用全匹配方式从注册的路由中获取处理器，如果获取不到则使用注册时附加的有序的一个路由序列中查找到最长前缀，并使用该最长前缀路由对应的处理器。

这里用到的`mux.es`就是前面使用`ServeMux.Handle`注册路由时写入的：

```go
func (mux *ServeMux) Handle(pattern string, handler Handler) {
  // ...
  if pattern[len(pattern)-1] == '/' {
    mux.es = appendSorted(mux.es, e)
  }
  // ...
}

func appendSorted(es []muxEntry, e muxEntry) []muxEntry {
  n := len(es)
  i := sort.Search(n, func(i int) bool {
    return len(es[i].pattern) < len(e.pattern)
  })
  if i == n {
    return append(es, e)
  }
  // we now know that i points at where we want to insert
  es = append(es, muxEntry{}) // try to grow the slice in place, any entry works.
  copy(es[i+1:], es[i:])      // Move shorter entries down
  es[i] = e
  return es
}
```

在注册路由时，如果路径是以`/`结尾的，那么就会将它通过`appendSorted()`方法放在`ServeMux.es`中合适的位置中。`appendSorted()`方法会按照路径的长度，从大往小对路由进行排序，保证最长的路径总是在最前面。这使得在后面进行最长匹配时能有更好的性能。

根据这个逻辑，如果访问`/greeting/a/b/c`路径，将不会匹配到`/greeting`路径的处理器，而是匹配到了`/`：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635305477551-7acd8a18fd57.jpg)

而如果注册`greeting`路由时，使用的是`http.Handle("/greeting/", greeting("Welcome, DJ"))`，那么访问`/greeting/a/b/c`就能匹配到`/greeting/`的路由了。但是此时访问`/greeting`就无效了。

> 在浏览器中访问`/greeting`时会被自动重定向到`/greeting/`路径中，所以还是能够继续访问的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1635306058047-e0ff270f8673.jpg)

### 7. 自定义 HTTP 服务

调用`http.HandleFunc()/http.Handle()`时都是将路由注册到默认的`ServeMux`实例`DefaultServeMux`上，并使用简单初始化的`Server`实例开启监听。但是这种方式存在一些问题：

* `Server`实例都是用了默认值，有些参数的初始值可能并不适合；
* 一些第三方库也可能使用这个默认对象注册一些处理，容易冲突；
* 调用`http.ListenAndServe()`开启 Web 服务时，第三方库注册的路由和处理逻辑就会被公开了，可能存在极大的安全隐患；
* `ServeMux.ServeHTTP()`的路由匹配逻辑较为简单，对 RESTful API 的支持不是很好。

所以，除非在示例程序中，否则建议不要直接使用默认的 HTTP 服务实例。

在实际应用中，可以通过如下方式来自定义 HTTP 服务：

1. 使用`http.NewServeMux()`创建一个新的`http.ServeMux`实例，向其注册路由；
2. 使用定制的参数创建`http.Server`对象；
3. 使用自定义的类型实例初始化`Server.Handler`字段，当然也可以使用前面创建的`ServeMux`实例进行初始化;
4. 调用`Server.ListenAndServe()`方法开启 Web 服务。

示例代码如下：

```go
func main() {
  mux := http.NewServeMux()
  mux.HandleFunc("/", index)
  mux.Handle("/greeting", greeting("Welcome to go web frameworks"))
  
  server := &http.Server{
    Addr:         ":8080",
    Handler:      mux,
    ReadTimeout:  20 * time.Second,
    WriteTimeout: 20 * time.Second,
  }
  server.ListenAndServe()
}
```

这个程序与一开始的那个实例功能基本相同，但是还额外设置了读写超时时间。

