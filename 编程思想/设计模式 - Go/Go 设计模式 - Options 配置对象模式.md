> 转摘：[一些实用的 Go 编程模式 | Options模式](https://mp.weixin.qq.com/s/vtrquMO4J-cf2z8Fg5EsHg)

Options 模式是函数式编程中常用的模式，可以让具有多个可选参数的函数或方法更加整洁易扩展。

> 为避免代码太长，会适当使用一些伪代码。

### 1. 解决的问题

当一个函数有多个可选参数时，为了正常的调用，常常会需要对不需要的参数也传入零值。而且函数内部也需要不少的空值判断。

比如，要封装一个通用的发 HTTP 请求的工具函数，要做到通用就必然需要定义很多能配置 HTTP 客户端的参数，比如：

```go
func HttpRequest(method string, url string, body []byte, headers map[string]string, timeout time.Duration) ... {
  if body != nil {
    // 设置请求体
  }
  
  if headers != nil {
    // 设置请求头
  }
}
```

如果使用这个工具函数来发送一个 Get 请求，那其中的很多参数是不需要设置的，而且超时时间一般都会设置一个默认值。这样，在调用的时候就不得不传一些零值给不需要自定义的配置参数：

```go
HttpRequest("GET", "https://www.baidu.com", nil, nil, 2 * time.Second)
```

如果是 Java 等支持重载的语言的话，可以通过方法重载来解决这个问题；但是如果可选参数是十几个，甚至更多的时候，各个调用方对可选参数的顺序要求不一样的话，定义这个重载方法显然不是一个好的解决方案。

#### 1.1 配置对象方案

另一种常用的解决方案是，工具函数的签名定义时，不再定义哥哥可能需要配置的可选参数，转而定义一个配置对象：

```go
type HttpClientConfig struct {
  timeout time.Duration
  headers map[string]string
  body    []byte
}

func HttpRequest(method string, url string, config *HttpClientConfig) ...
```

这样对调用者来说比参数枚举定义的方式简洁了不少，如果全都是默认选项只需要给配置对象这个参数传递一个零值即可：

```go
HttpRequest("GET", "https://www.baidu.com", nil)
```

这种方案的问题是：对于函数的实现来说，仍然少不了那些选项参数非零值的判断。而且因为配置对象在函数外部可以改变，这就有一定几率配置对象在函数内部未被使用前就被外部程序改变了，在出现相关 Bug 时，排查起来较为麻烦。

#### 1.2 可变参数方案

这种方案是将可选的参数都收集到一个切片中：

```go
func HttpRequest(method string, url string, options ...interface{}) ...
```

此时虽然参数是可变的，但是实现方需要通过遍历设置 HTTP 客户端的不同选项，这就让可变参数固定了传递顺序，调用方如果想要设置某个可选项还得记住参数顺序，且无法直接通过函数签名就确定参数顺序。

### 2. Options 方案

下面来看如何使用 Options 模式来解决这种问题。

#### 2.1 gRPC 中的 Options

gRPC SDK 中客户端方法可以传递不少以`with`开头的闭包函数方法：

```go
client.cc, err = grpc.Dial(
  "127.0.0.1:12315",
  grpc.WithInsecure(),
  grpc.WithUnaryInterceptor(...),
  grpc.WithStreamInterceptor(...),
  grpc.WithAuthority(...)
)
```

这些配置方法的返回值都是一个`DialOption`的 interface：

```go
type DialOption interface {
  apply(*dialOptions)
}

func WithInsecure() DialOption {
  ...
}
```

#### 2.2 Options 实践

下面使用 Options 模式来对 HTTP 工具函数进行改造。

首先定义一个契约和配置对象：

```go
type requestOption struct {
  timeout time.Duration
  data    string
  headers map[string]string
}

type Option struct {
  apply func(option *requestOption)
}

// 默认请求选项
func defaultRequestOptions() *requestOption {
  return &requestOption{
    timeout: 5 * time.Second,
    data:    "",
    headers: nil,
  }
}
```

接下来定义配置函数，每个配置函数都会设置请求配置对象中的某个配置项：

```go
func WithTimeout(timeout time.Duration) *Option {
  return &Option{
    apply: func(option *requestOption) {
      option.timeout = timeout
    }
  }
}

func WithData(data string) *Option {
  return &Option{
    apply: func(option *requestOption) {
      option.data = data
    }
  }
}

func WithHeaders(headers map[string]string) *Option {
  return &Option)
    apply: func(option *requestOption) {
      option.headers = headers
    }
  }
}
```

然后使用可变参数方式重新定义工具函数的参数，并在具体的实现中遍历可变参数，循环调用其`apply`方法即可完成对默认配置对象的配置：

```go
func HttpRequest(method string, url string, options ...*Option) {
  reqOpts := defaultRequestOptions() // 默认的请求选项
  for _, opt := range options {      // 在默认的请求选项中依次应用配置设置
    opt.apply(reqOpts)
  }
  
  // 创建请求对象
  req, err := http.NewRequest(method, url, strings.NewReader(reqOpts.data))
  ...
  
  // 设置请求头
  for key, value := range reqOpts.headers {
    req.Header.Add(key, value)
  }
  
  // 发起请求
  ...
  
  return
}
```

这样，HTTP 工具函数就实现了，调用的时候可以根据需要分别配置对应的 Option 函数，而且不需要关注函数的顺序问题：

```go
HttpRequest("GET", url)

HttpRequest("POST", url, WithHeaders(headers))

HttpRequest("POST", url, WithTimeout(timeout), WithHeaders(headers), WithData(data))
```

而且后续如果要扩充配置，再增加一个对应的`With`方法即可，易于扩展。


