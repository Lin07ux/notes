Gin 官方提供了 [CORS 中间件](https://github.com/gin-contrib/cors)，使用非常简单，使用 Gin 应用的`Use()`方法添加该中间件即可。

### 1. 基础

Gin CORS 中间件提供生成 CORS 中间件的配置结构，以及一个生成使用默认配置值的配置结构`DefaultConfig()`。另外，还提供了两个个函数，用来生成自定义配置的 CORS 中间件：

* `New()` 新建一个 CORS 中间件，接收 CORS 中间件配置；
* `Default()` 在`DefaultConfig()`的基础上设置`AllowAllOrigins`选项为 true，然后创建一个 CORS 中间件。

使用示例如下：

```go
router := gin.Default()
// CORS for https://foo.com and https://github.com origins, allowing:
// - PUT and PATCH methods
// - Origin header
// - Credentials share
// - Preflight requests cached for 12 hours
router.Use(cors.New(cors.Config{
  AllowOrigins:     []string{"https://foo.com"},
  AllowMethods:     []string{"PUT", "PATCH"},
  AllowHeaders:     []string{"Origin"},
  ExposeHeaders:    []string{"Content-Length"},
  AllowCredentials: true,
  AllowOriginFunc: func(origin string) bool {
    return origin == "https://github.com"
  },
  MaxAge: 12 * time.Hour,
}))
```

### 2. 配置项

CORS 中间件的配置项如下：

* `AllowAllOrigins bool` 允许所有请求源；
* `AllowOrigins []string` 指定允许请求源列表，如果列表中存在`*`则表示允许所有请求源，默认值为`[]`；
* `AllowOriginFunc func(origin string) bool` 设置自定义函数来判断请求的源是否为被允许的源。其优先级高于`AllowOrigins`，如果设置了该选项，则`AllowOrigins`配置项会被忽略；
* `AllowMethods []string` 允许的请求方法，默认值为`['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`；
* `AllowHeaders []string` 用在对预请求的响应中，指示实际的请求中可以使用哪些 HTTP 请求头；
* `AllowCredentials bool` 表示请求附带请求凭据时是否响应该请求，请求凭据包括 Cookie、HTTP Authentication 或客户端 SSL 证书；
* `ExposeHeaders []string` 可以在响应中显示的请求头；
* `MaxAge time.Duration` 指示预请求的结果能被缓存多久；
* `AllowWildcard bool` 添加请求源是否允许使用通配符，如`http://some-domain/*`、`https://api.`或`http://some.*.subdomain.com`；
* `AllowBrowserExtensions bool` 是否允许使用常用的浏览器扩展模式；
* `AllowWebSockets bool` 是否允许使用 WebSocket 协议；
* `AllowFiles bool` 是否允许使用`file://`协议。

### 3. 源码

Gin CORS 中间件的核心为一个内置的`cors`结构，在创建 CORS 中间件的时候，会生成一个 cors 实例，并包转成 gin.HandlerFunc 格式：

```go
func New(config Config) gin.HandlerFunc {
  cors := newCors(config)
  return func(c *gin.Context) {
    return cors.applyCors(c)
  }
}
```

生成的 CORS 中间件的核心就是对`cors.applyCors()`方法的调用，该方法中会获取请求的 origin 进行验证，如果验证通过则会向响应中添加相关的响应头：

```go
func (cors *cors) applyCors(c *gin.Context) {
  origin := c.Request.Header.Get("Origin")
  if len(origin) == 0 {
    // request is not a CORS request
    return
  }

  host := c.Request.Host
  if origin == "http://"+host || origin == "https://"+host {
    // request is not a CORS request but have origin header.
    // for example, use fetch api
    return
  }
  
  if !cors.validateOrigin(origin) {
    c.AbortWithStatus(http.StatusForbidden)
    return
  }
  
  if c.Request.Method == "OPTIONS" {
    cors.handlePreflight(c)
    // Using 203 is better than 200 when the request method is OPTIONS
    defer c.AbortWithStatus(http.StatusNoContent)
  } else {
    cors.handleNormal(c)
  }
  
  if !cors.allowAllOrigins {
    c.Header("Access-Control-Allow-Origin", origin)
  }
}

func (cors *cors) handleNormal(c *gin.Context) {
  header := c.Writer.Header()
  for key, value := range cors.normalHeaders {
    header[key] = value
  }
}
```

其中，`cors.normalHeaders`是在初始化 cors 实例的时候就根据传入的 config 生成好的：

```go
func generateNormalHeaders(c config) http.Header {
  headers := make(http.Header)
  if c.AllowCredentials {
    headers.Set("Access-Control-Allow-Credentials", "true")
  }
  if len(c.ExposeHeaders) > 0 {
    exposeHeaders := convert(normalize(c.ExposeHeaders), http.CannoicalHeaderKey)
    headers.Set("Access-Control-Expose-Headers", strings.Join(exposeHeaders, ",")
  }
  if c.AllowAllOrigins {
    headers.Set("Access-Control-Allow-Origin", "*")
  } else {
    headers.Set("Vary", "Origin")
  }
  return headers
}
```