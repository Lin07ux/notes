[gorilla](https://github.com/gorilla) web 开发包是 Go 语言中辅助开发 Web 服务器的工具包，包括 Web 服务器开发的各个方面。这些组件都能与`net/http`很好的结合使用。

### 1. 路由管理 gorilla/mux

[gorilla/mux](github.com/gorilla/mux) 是 gorilla Web 开发工具包中的路由管理库，有如下特点：

* 实现了标准的`http.Handler`接口，路由、中间件等都可以与`net/http`标准库结合使用；
* 可以根据请求的主机名、路径、路径前缀、协议、HTTP 首部、查询字符串和 HTTP 方法匹配处理器，还可以自定义匹配逻辑；
* 可以在主机名、路径和请求参数中使用变量，还可以为之指定一个正则表达式；
* 可以传入参数给指定的处理器让其构造出完整的 URL；
* 支持路由分组，方便管理和维护。

示例如下：

> 参考：[Go 每日一库之 gorilla/mux](https://mp.weixin.qq.com/s/lhuv27BuaX-J0gcKyJC0Bw)

```go
func main() {
    r := mux.NewRouter()
    
    // Books
    br := r.PathPrefix("/books").Subrouter()
    br.HandleFunc("/", BooksHandler).Name("books.list")
    // 符合这个正则表达式的路由才会进入到 BookDetailHandler 处理器中
    br.HandleFunc("/books/{isbn:\\d{3}-\\d-\\d{3}-\\d{5}-\\d}", BookHandler).Name("books.detail")
    
    // Movies
    mr := r.PathPrefix("/movies").Subrouter()
    mr.HandleFunc("/", MoviesHandler).Name("movies.list")
    mr.HandleFunc("/{imdb}", MovieHandler).Name("movies.detail")
    
    // 可以将所有请求通过 / 绑定到 mux router，也可以直接将服务器的请求处理绑定到 mux router
    // http.Handle("/", r)
    // http.ListenAndServe(":8088", nil)
    log.Fatal(http.ListenAndServe(":8088", r))
}
```

### 2. 中间件 gorilla/handlers

[gorilla/handlers](github.com/gorilla/handlers) 提供了一些很有用的中间件，能够在`net/http`中使用。

> 参考：[Go 每日一库之 gorilla/handlers](https://mp.weixin.qq.com/s/0gWmwOf2hhA-N3FJWCrQ7A)

* 日志

    - `LoggingHandler` 以 Apache 的 Common Log Format 日志格式记录 HTTP 请求日志；
    - `CombinedLoggingHandler` 以 Apache 的 Combined Log Format 日志格式记录 HTTP 请求日志，Apache 和 Nginx 默认使用这种日志格式。
    - `CustomLoggingHandler` 支持自定义的日志格式。

* 压缩

    - `CompressHandler` 解压使用客户端请求中的`Accept-Encoding`请求头启用对应的压缩算法。如果客户端未指定或请求头中有`Upgrade`，则不压缩。

* 内容类型

    - `ContentTypeHandler` 指定请求的`Content-Type`必须在给定的类型中。该中间件只对`POST/PUT/PATCH`方法生效。

* 方法分发

    - `MethodHandler` 可以为同一个路径的不同请求方法注册不同的处理器。

* 重定向

    - `CanonicalHost` 将请求重定向到指定的域名中，而请求路径保持不变。可以指定跳转使用的状态（301、302）。

* 错误恢复

    - `RecoveryHandler` 提供了从请求处理中的 panic 恢复的功能。

### 3. 序列化 gorilla/schema

> 转摘：[Go 每日一库之 gorilla/schema](https://mp.weixin.qq.com/s/88WNqhxq6RacbK2Ev9JDTg)

[gorilla/schema](https://github.com/gorilla/schema) 是 gorilla 开发工具包中用于处理表单的库。它提供了一个简单的方式，可以很方便的将表单数据转为结构体对象，或者将结构体对象转为表单数据。也就是说，`gorilla/schema`是一个用来在结构体对象和表单数据之间进行转换的工具。

`gorilla/schema`使用反射来对应表单和结构体字段，可以通过结构体标签来指定表单数据和字段的对应关系。**可以将它的解码器作为一个全局变量来使用，因为解码器会缓存一些结构体的元数据，并且是并发安全的。**

示例如下：

```go
type User struct {
  Username string `schema:"username"`
  Password string `schema:"password"`
}

var (
  decoder = schema.NewDecoder()
  encoder = schema.NewEncoder()
)  

func login(w http.ResponseWriter, r *http.Request) {
  r.ParseForm() // 解析表单数据
  u := User{}
  decoder.Decode(&u, r.PostForm) // 将表单数据解码到 User 类型的变量中
  if u.Username == "dj" && u.Password == "handsome" {
    http.Redirect(w, r, "/", 301)
    return
  }
  http.Redirect(w, r, "/login", 301)
}

func buildLoginData () {
  client := &http.Client{}
  form := url.Values{}
  
  u := &User{
    Username: "dj",
    Password: "handsome",
  }
  encoder.Encode(u, form) // 将 User 类型的变量的内容编码到 form 变量中
  
  res, _ := client.PostForm("http://localhost:8080/login", form)
  data, _ := ioutil.ReadAll(res.Body)
  fmt.Println(string(data))
  res.Body.Close()
}
```

目前`gorilla/schema`支持一下类型的编码和解码：

* 布尔类型：bool
* 浮点数：float32/float64
* 有符号整数：int/int8/int16/int32/int64
* 无符号整数：uint/uint8/uint16/uint32/uint64
* 字符串：string
* 结构体：由以上类型组成的结构体
* 指针：指向以上类型的指针
* 切片：元素为以上类型的切片，或指向切片的指针

也可以自定义类型的类型的转换函数，转换函数的类型为：`func(s string) reflect.Value`。

比如，有时候客户端会将一个切片拼成一个字符串传到服务器，服务器收到之后需要解析成切片：

```go
var decoder = schema.NewDecoder()

func init() {
  decoder.RegisterConverter([]string{}, func(s string) reflect.Value {
    return reflect.ValueOf(strings.Split(s, ","))
  })
}
```

这样，当需要从字符串数据中解码出字符串切片时，就会使用逗号分隔将字符串分割为一个切片。

