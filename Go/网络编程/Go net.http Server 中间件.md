> 转摘：[Go 每日一库之 net/http（基础和中间件）](https://mp.weixin.qq.com/s/vlwX48Z6JBj6A8g7r-9dgA)

有时候需要在请求处理代码中增加一些通用的逻辑，如统计处理耗时、记录日志、捕获宕机等等。如果在每个请求处理函数中添加这些逻辑，代码很快就会变的不可维护，添加新的处理函数也会变的非常繁琐。所以就有了中间件的需求。

中间件有点像面向切面的编程思想，但是也有不同：在 Java 中，通用的处理逻辑（也可以称为切面）可以通过反射插入到正常逻辑的处理流程中，在 Go 语言中基本不这样做，而是通过函数闭包来实现的。

Go 语言中的函数是第一类值，即可以作为参数传递给其他函数，也可以作为返回值从其他函数返回。可以利用闭包封装已有的处理函数，从而达到中间件的目的。

### 1. 编写中间件

首先，基于函数类型`func(http.Handler) http.Handler`定义一个中间件类型：

```go
type Middleware func(handler http.Handler) http.Handler
```

编写一个在请求前后各输出一条日志的中间件：

```go
func WithLogger(handler http.Handler) http.Handler) {
  return http.HandlerFunc(func(w http.ResponseWriter, r *Request) {
    log.Printf("path: %s process start...\n", r.URL.Path)
    defer func() {
      log.Printf("Path: %s process end...\n", r.URL.Path)
    }()
    handler.ServeHTTP(w, r)
  }
}
```

实现逻辑很简单，急速通过中间件封装原来的处理器对象，然后返回一个新的处理器函数。在新的处理器函数中，先输出开始处理的日志，然后用`defer`语句在函数结束后输出处理结束的日志。最后调用原处理器对象的`ServeHTTP()`方法执行原处理器逻辑。

类似的，在实现一个统计处理耗时的中间件和一个错误恢复中间件：

```go
func Metric(handler http.Handler) http.Handler {
  return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
    start := time.Now()
    defer func() {
      log.Printf("path: %s elapsed: %fs\n", r.URL.Path, time.Since(start).Seconds())
    }()
    time.Sleep(1 * time.Second)
    handler.ServeHTTP(w, r)
  })
}

func PanicRecover(handler http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		defer func() {
			if err := recover(); err != nil {
				log.Println(string(debug.Stack()))
			}
		}()
		handler.ServeHTTP(w, r)
	})
}
```

> 在`http.conn.serve()`方法中也有`recover()`操作，程序一般不会异常退出，但是自定义的异常恢复中间件可以添加一些定制的逻辑。

### 2. 注册中间件

中间件编写完成之后，就可以在路由注册的时候将中间件也一起附带进去：

```go
http.Handle("/", PanicRecover(WithLogger(Metric(http.HandlerFunc(index)))))
http.Handle("/greeting", PanicRecover(Metric(WithLogger(greeting("Welcome, Lin07ux")))))
```

这里将`/`和`/greeting`路径的中间件注册顺序进行了变更，可以了解中间件注册顺序对其执行顺序的影响。分别访问之后，得到的结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635319896882-83b0f1077307.jpg)

### 3. 辅助注册函数

上面的中间件注册方式有点繁琐，特别是需要注册的中间件较多时，层级容易嵌套太深，难以阅读。可以编写一个辅助函数，接收原始的处理器对象和可变的多个中间件。然后对处理器对应应用这些中间件，返回新的处理器对象：

```go
func applyMiddleware(handler http.Handler, middleware ...Middleware) http.Handler {
	for i := len(middleware) - 1; i >= 0; i-- {
		handler = middleware[i](handler)
	}

	return handler
}
```

在该函数内，使用逆序循环的方式将原处理器用要注册的中间件依次包裹，这样可以保证中间件按照列表中的注册顺序进行执行，与人的认知相符合。

然后使用该辅助注册函数重新注册中间件：

```go
middlewareList := []Middleware{
	PanicRecover,
	WithLogger,
	Metric,
}

http.Handle("/", applyMiddleware(http.HandlerFunc(index), middlewareList...))
http.Handle("/greeting", applyMiddleware(greeting("Welcome, Lin07ux"), middlewareList...))
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1635320579131-94f0d85567df.jpg)

可以看到，使用辅助注册函数添加的中间件，其执行顺序是由定义的顺序决定的。

### 3. 自定义 ServeMux 简化注册

上面的注册逻辑还是有点复杂，每次路由注册都需要调用`applyMiddleware()`函数。通过对`http.ServeMux`处理方式的了解（参考：[Go net.http Server 源码了解](./Go%20net.http%20Server%20源码了解.md)），可以考虑封装一个自定义的`ServeMux`结构，然后定义一个注册中间件的方法，并重写路由注册方法，在每次注册路由时自动将处理器进行封装：

```go
type MyServeMux struct {
	*http.ServeMux
	middleware []Middleware
}

func (m *MyServeMux) Use(middleware ...Middleware) {
	m.middleware = append(m.middleware, middleware...)
}

func (m *MyServeMux) Handle(pattern string, handler http.Handler) {
	handler = applyMiddleware(handler, m.middleware...)
	m.ServeMux.Handle(pattern, handler)
}

func (m *MyServeMux) HandleFunc(pattern string, handler http.HandlerFunc) {
	newHandler := applyMiddleware(handler, m.middleware...)
	m.ServeMux.Handle(pattern, newHandler)
}

func NewMyServeMux() *MyServeMux {
	return &MyServeMux{
		ServeMux:   http.NewServeMux(),
	}
}
```

注册时需要先实例化一个`MyServeMux`对象，调用其`User()`方法传入要应用的中间件，然后使用该对象开启一个 HTTP 服务：

```go
mux := NewMyServeMux()
mux.Use(PanicRecover, WithLogger)

mux.Handle("/", http.HandlerFunc(index))

mux.Use(Metric)
mux.Handle("/greeting", greeting("Welcome, Lin07ux"))

_ = http.ListenAndServe(":8080", mux)
```

这样就能注册全局中间件供所有的路由处理器使用了，但是也发现一个问题：只能在注册路由之前就把中间件注册好，否则后续注册的中间件将无法在已定义的路由上使用了。

比如，上面的注册方式，运行结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635322490704-ae5703534d73.jpg)


### 4. 随时注册中间件

为了解决上面中间件必须提前全部注册好的问题，可以改写`ServeHTTP()`方法，在确定了处理器之后再应用中间件，这样后续添加的中间件就能在全部的路由上生效了。很多第三方库都是采用这种方式的。

`http.ServeMux.ServeHTTP()`方法的源码如下：

```go
func (mux *ServeMux) ServeHTTP(w ResponseWriter, r *Request) {
  if r.RequestURI == "*" {
    if r.ProtoAtLeast(1, 1) {
      w.Header().Set("Connection", "close")
    }
    w.WriteHeader(StatusBadRequest)
    return
  }
  h, _ := mux.Hanlder(r)
  h.ServerHTTP(w, r)
}
```

在自定义的`MyServeMux`类型上重写`ServeHTTP()`方法，在获取到处理器之后，应用上当前的中间件即可：

```go
func (m *MyServeMux) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	if r.RequestURI == "*" {
		if r.ProtoAtLeast(1, 1) {
			w.Header().Set("Connection", "close")
		}
		w.WriteHeader(http.StatusBadRequest)
		return
	}
	h, _ := m.Handler(r)
	// 只需加上这一行
	h = applyMiddleware(h, m.middleware...)
	h.ServeHTTP(w, r)
}
```

然后将前面为`MyServeMux`重写的`Handle()`和`HandleFunc()`方法去除。再添加一个触发 panic 的处理函数：

```go
func panics(w http.ResponseWriter, r *http.Request) {
  panic("not implemented")
}

mux.HandleFunc("/panic", panics)
```

运行结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635322845109-3c31fedd4b6f.jpg)



