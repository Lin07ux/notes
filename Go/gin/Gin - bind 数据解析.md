> 转摘：[「Go框架」bind函数：gin框架中是如何将请求数据映射到结构体的？](https://mp.weixin.qq.com/s/-zBah_ZSDdri0dqDF5Nd9g)

在 gin 框架中，可以使用 Context 的`bind/bindXXX`方法将请求体中的参数绑定到对应的结构体。这类`bind`方法有很多个，如：`ShouldBind`、`ShouldBindQuery`、`ShouldBindHeader`、`ShouldBindJSON`等，分别用于从不同请求数据来源解析数据。

### 1. 基本作用

在 gin 框架或其他 web 框架中，`bind`类方法的作用就是**将请求体中的参数值（body/form/query/url/header）绑定到对应的结构体上**，以方便后续业务逻辑的处理。

下面的示例是期望客户端提供一个 JSON 格式的请求体，并通过 JSON 标签将其绑定到`LoginRequest`结构体上：

```go
package main

import (
  "fmt"
  "github.com/gin-gonic/gin"
)

type LoginRequest struct {
  Username string `json:"username"`
  Password string `json:"password"`
}

func main() {
  g := gin.New()
  g.POST("/login", func(ctx *gin.Context) {
    r := &LoginRequest{}
    ctx.ShouldBind(r)
    fmt.Printf("login-request: %+v\n", r)
  })
  
  g.Run(":9090")
}
```

这段代码中，使用的是`Context.ShouldBind()`方法来解析请求体的，该方法会自动根据请求的方法（GET 或 POST）以及请求头`Content-Type`来获取具体的用于解析数据的实例。比如，对于 POST 请求，且`Content-Type`的值为`application/json`的情况，就会使用`jsonBinding`结构体实例来完成数据解析。

### 2. 数据来源

bind 类方法进行解析绑定的数据，来源于客户端发来的请求。而一次 HTTP 请求中，可以 URL 查询参数、请求头信息、请求体中携带数据。而请求体中携带的数据可以由`Content-Type`请求体指定格式：

![](https://cnd.qiniu.lin07ux.cn/markdown/b8ba3b23e8b3cda571740792b0db024d.jpg)

请求体中的数据格式并不限于 JSON 和 FormData 两种格式，还可以是 XML、YAML、Protobuf message 等。

### 3. 相关方法

为了能方便解析不同来源的请求数据及不同格式的数据，在 gin 框架中就对应了不同的 bind 方法来完成对应的数据解析。

* **ShouldBindQuery** 该方法用于从 url 查询参数中解析数据，对应的结构体中需要给字段添加`query`标签；
* **ShouldBindHeader** 该方法用于从请求头中解析数据，对应的结构体中需要给字段添加`header`标签；
* **ShouldBindXXX** 其他的`ShouldBind`方法则是从请求体中解析数据，需要根据请求体的数据格式来确定如何解析，或者直接使用`ShouldBind`由 gin 自动判断如何解析。

    ![](https://cnd.qiniu.lin07ux.cn/markdown/f14b71fba442add77389991a58c0ab65.jpg)

* **ShouldBindWith** 该方法可以指定解析数据的来源，常用于 POST 请求中。

    由于 POST 请求可以指定编码方式(常见有`multipart/form-data`和`application/x-www-form-urlencoded`)，所以获取请求数据时会有所区别。
    
    在 net/http 中的 Request 结构体中，有 Form、PostForm、MultipartForm 对象，分别承载不同来源的请求参数：
    
    - Form 其值来源于 url 查询参数和表单中的值域，可以使用`ctx.ShouldBindWith(obj, binding.Form)`来解析；
    - PostForm 其值来源于表单中的值域，可以使用`ctx.ShouldBindWith(obj, binding.FormPost)`来解析；
    - MultipartForm 其值来源于表单中的值域和文件，可以使用`ctx.ShouldBindWith(obj, binding.MIMEMultipartForm)`来解析。

gin 中 bind 方法的完整层级结构如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/be01f9acf7fcd7aa17e4555f89daeadf.jpg)
