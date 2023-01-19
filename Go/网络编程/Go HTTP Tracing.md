> 转摘：[Go的HTTP tracing](https://mp.weixin.qq.com/s/y8XRW7Bf38SDQwN1QQhNHA)

在 Go 1.7 中引入了 HTTP tracing，这是在 HTTP 客户端请求的整个生命周期中收集细粒度信息的工具，由`net/http/httptrace`包提供支持。

### 1. HTTP 事件

httptrace 包提供了很多钩子，用于在 HTTP 往返期间收集各种事件的信息。

这些事件包括：

* 连接创建
* 连接复用
* DNS 查询
* 将请求写入网络
* 读取响应

### 2. 使用

为了收集 HTTP 往返期间的信息，可以将包含钩子函数的`*httptrace.ClientTrace`对象放在请求的`context.Context`中来启用 HTTP tracing。在 HTTP 请求执行过程中，`http.RoundTripper`会通过查找请求的 content 中的`*httptrace.ClientTrace`对象来调用相关的钩子函数，报告内部事件，完成钩子函数的执行。

Go HTTP Client 客户端种中使用 HTTP Tracing 的示例如下：

```go
func main() {
  req, _ := http.NewRequest("GET", "https://google.com", nil)
  trace := &httptrace.ClientTrace{
    GotConn: func(connInfo httptrace.GotConnInfo) {
      fmt.Printf("Got Conn: %+v\n", connInfo)
    },
    DNSDone: func(dnsInfo httptrace.DNSDoneInfo) {
      fmt.Printf("DNS Info: %+v\n", dnsInfo)
    },
  }
  req = req.WithContext(httptrace.WithClientTrace(req.Context(), trace))
  _, err := http.DefaultTransport.RoundTrip(req)
  if err != nil {
    log.Fatal(err)
  }
}
```

在 roundtrip 中，`http.DefaultTransport`会在事件发生时调用每个钩子，比如 DNS 查找完成时就会触发`httptrace.ClientTrace`实例中定义的钩子函数（如果有的话）。上述代码的输出类似如下：

```text
DNS Info: {Addrs:[{IP:192.168.83.230 Zone:}] Err:<nil> Coalesced:false}
Got Conn: {Conn:0xc42001ce00 Reused:false WasIdle:false IdleTime:0s}
```

### 3. 跟踪 http.Client

跟踪机制旨在跟踪单个`http.Transport.RoundTrip`的生命周期中的事件。但是客户端可以进行多次往返以完成 HTTP 请求。比如，在 URL 重定向的情况下，客户端遵循 HTTP 重定向进行多个请求。，通过`httptrace.ClientTrace`注册的钩子将会被调用多次。用户需要在`http.Client`级别来识别这些事件。

> `net/http`包中的`Transport`支持跟踪 HTTP/1 和 HTTP/2 的 request。

下面的示例使用`http.RoundTripper`包装器来标识当前的请求：

```go
package main

import (
  "fmt"
  "log"
  "net/http"
  "net/http/httptrace"
)

// transport is an httpRoundTripper that keeps track of the in-flight
// request and implements hooks to report HTTP tracing events.
type transport struct {
  current *http.Request
}

// RoundTrip wraps http.DefaultTransport.RoundTrip to keep track of
// the current request
func (t *transport) RoundTrip(req *http.Request) (*http.Response, error) {
  t.current = req
  return http.DefaultTransport.RoundTrip(req)
}

// GotConn prints whether the connection has been used previously for
// the current request.
func (* t &transport) GotConn(info httptrace.GotConnInfo) {
  fmt.Printf("Connection reused for %v? %v\n", t.current.Url, info.Reused)
}

func main() {
  t := &transport{}
  
  req, _ := http.NewRequest("GET", "https://google.com", nil)
  trace := &httptrace.ClientTrace{
    GotConn: t.GotConn,
  }
  req = req.WithContext(httptrace.WithClientTrace(req.Context(), trace))
  
  client := &http.Client{Transport: t}
  if _, err := client.Do(req); err != nil {
    log.Fatal(err)
  }
}
```

这段代码在请求`google.com`的时候会被重定向到`www.google.com`，所以会有如下的输出：

```text
Connection reused for https://google.com? false
Connection reused for https://www.google.com.hk/?gfe_rd=cr&ei=olwkWd3BAa-M8Qfjs73IBA? false
```


