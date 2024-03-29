* [Go Colly](http://go-colly.org/) 快速、优雅的 Go 爬虫框架。

### 一、错误处理

Go 错误处理的原则：

1. 错误只在逻辑的最外层处理一次，底层只返回错误；
2. 底层除了返回错误外，要对原始错误进行包装，增加错误信息、调用栈等，这些有利于排查上下文信息。

#### 1.1 一个 goroutine：合并多个 error 为一个标准的 error

* [go-multierror - HashiCorp](https://github.com/hashicorp/go-multierror)
* [multierr - Uber](https://github.com/uber-go/multierr)

`go-multierror`的性能和代码优雅性方面更优于`multierr`。

```go
func main() {
 var errs error
 reader := csv.NewReader(bytes.NewBuffer(data))
 for {
   if _, err := reader.Read(); err != nil {
     if err == io.EOF {
       break;
     }
     errs = multierror.Append(errs, err)
   }
 }
 if errs != nil {
   log.Printf(errs.Error())
 }
 // 输出类似如下：
 // errors occurred:
 //   * record on line10: wrong nunber of fields
 //   * record on line 12: parse error on line 5, column 0: extraneous or missing
}
```

### 1.2 多个 goroutine：错误传播

* [errgroup](https://pkg.go.dev/golang.org/x/sync/errgroup?tab=doc)

这个包可以在一组 goroutine 之间进行错误和上下问传播，实现多个 goroutine 错误同步操作：

```go
func main() {
  g, ctx := errgroup.WithContext(context.Background())
  for i := 0; i < 4; i++ {
    g.Go(func() error {
      if err := action(ctx); err != nil {
        return err
      }
      if err := action(ctx); err != nil {
        return err
      }
      if err := action(ctx); err != nil {
        return err
      }
      return nil
    })
  }
  if err := g.Wait(); err != nil {
    log.Printf(err.Error())
  }
}
```

上面代码中，使用`errgroup`开启了三个 goroutine，每个 goroutine 中都会执行一个可能会抛出错误的函数`action`。

这样，当三个 gotoutine 中的任何一个抛出了错误而终止时，其他的两个也会同时被取消了，因为 error 传播了取消的上下文。因此，这样执行会更快的结束。

### 1.3 错误包装

* `fmt.Errorf()`
* [errors](github.com/pkg/errors)

当需要在一个调用链中传递错误时，建议的做法的是每一层调用都为错误包装相关的追踪信息，以方便后续的追踪查找。

一种简单的包装错误的方法就是使用`fmt.Errorf()`函数，给错误添加信息：

```go
func WriteConfig(w io.Writer, conf *Config) error {
  buf, err := json.Marshal(conf)
  if err != nil {
    return fmr.Errorf("could not marshal config: %v", err)
  }
  if err = WriteAll(w, buf); err != nil {
    return fmt.Errorf("could not write config: %v", err)
  }
  return nil
}
func WriteAll(w io.Writer, buf []byte) error {
  if _, err := w.Write(buf); err != nil {
    return fmt.Errorf("write failed: %v", err)
  }
  return nil
}
```

`fmt.Errorf()`方法只能给错误添加简单的注解信息，而`errors`包则可以在添加信息的同事加上错误的调用栈：

* `func WithMessage(err error, message string) error` 只附加新的信息
* `func WithStack(err error) error` 只附加调用栈信息
* `func Wrap(err error, message string) error` 同时附加堆栈和信息
* `func Cause(err error) error` 返回包装错误对应的最原始错误（即会递归地进行解包）

示例：

```go
func ReadFile(path string) ([]byte error) {
  f, err := os.Open(path)
  if err != nil {
    return nil, errors.Wrap(err, "open failed")
  }
  defer f.Close()
  
  buf, err := ioutil.ReadAll(f)
  if err != nil {
    return nil, errors.Wrap(err, "read failed")
  }
  return buf, nil
}

func ReadConfig() ([]byte, error) {
  home := os.Getenv("HOME")
  config, err := ReadFile(filepath.Join(home, ".settings.xml"))
  return config, errors.WithMessage(err, "could not read config")
}

func main() {
  if _, err := ReadConfig(); err != nil {
    fmt.Printf("original error: %T %v\n", errors.Cause(error), errors.Cause(err))
    // %+v 是用来展开符合类型值，比如结构体的字段值明细等
    fmt.Printf("stack trace:\n%+v\n", err)
  }
}
```


## 二、配置

### 2.1 Viper

[viper](https://github.com/spf13/viper) 是一个强大的 Go 配置操作框架，功能特点有：

* 支持从配置文件自动加载配置
* 写配置文件
* 监事配置文件的变动并热加载
* 支持多种配置文件类型（Json、Toml、Yaml、Hcl、ini 等）
* 支持从环境变量和命令行参数读取配置

Viper 按照一定的优先级次序从多个来源获取配置数据，如下是从高到低的优先级进行排序：

* 明确调用`Set()`方法
* flag
* env
* config
* key/value store
* default

## 三、工具库

### 3.1 gopsutil

[gopsutil](https://github.com/shirou/gopsutil) 是 Python 工具库 [psutil](https://link.zhihu.com/?target=https%3A//github.com/giampaolo/psutil) 的 Go 语言移植版，可以方便的获取各种系统和硬件信息。

gopsutil 屏蔽了各个系统之间的差异，具有非常强悍的可移植性，避免了需要针对不同的系统通过`syscall`调用对应的系统方法的操作。而且，gopsutil 的实现中没有任何`cgo`的代码，使得交叉编译成为可能。

gopsutil 将不同的功能划分到不同的子包中：

* `cpu` CPU 相关
* `disk` 磁盘相关
* `docker` Docker 相关
* `mem` 内存相关
* `net` 网络相关
* `process` 进程相关
* `winservices` Windows 服务相关

使用示例：

```go
// 获取当前进程的 Process 对象
p, _ := process.NewProcess(int32(os.Getpid()))

// 获取指定时间内进程占全部 CPU 核心的时间比例
cpuPercent, _ := p.Percent(time.Second)

// 获取进程占用内存的比例
mp, _ := p.MemoryPercent()
```

### 3.2 panicparse

[maruel/panicparse](https://github.com/maruel/panicparse) 是一款 Panic 堆栈追踪解析器和调试工具，可以将堆栈信息更好的展示出来。

### 3.3 监听文件变动 fsnotify

[fsnotify/fsnotify](https://github.com/fsnotify/fsnotify) 库可以方便的对文件、目录做监控，因为一切皆文件，这代表着一切都可监控。

这个库其实是操作系统能力的浅层封装，Linux 通过 inotify 机制实现了文件监控和通知。

inotify 也是一个特殊句柄，属于匿名句柄之一，这个句柄用于文件的事件监控；fsnotify 用 epoll 机制对 inotify fd 的可读事件进行监控，实现 IO 多路复用的时间通知机制。

[Go 存储基础 — “文件”被偷偷修改？来，给它装个监控！](https://mp.weixin.qq.com/s/Vq5WxDyorMQ2nNkUAr6DjQ)

### 3.4 结构体字段对齐 fieldalignment

[fieldalignment](https://pkg.go.dev/golang.org/x/tools/go/analysis/passes/fieldalignment)是一个检测和对齐结构体字段的小工具，可以找到那些可以重新排序字段以减少内存占用的结构，并提供建议和编辑为最紧凑的顺序。

fieldalignment 会有鲁昂个不同的报告：一个是检查结构体的大小，另一个是报告所使用的指针字节数（是指 GC 会对 struct 中的这些字节进行潜在的指针扫描）。

比如：

* `struct { uint32, string }` 16 个指针字节，GC 会扫描字符串的内部指针；
* `struct { string, *uint32}` 24 个指针字节，GC 会进一步扫描`*uint32`；
* `struct { string, uint32}` 8 个指针字节，因为扫描到 string 会立马停止。

可以看出，最紧凑的顺序并非总是最有效的。在极少数情况下，它可能会导致两个变量分别被字节的 goroutine 更新占用同一个 CPU 缓存线，从而引起一种被称为“假共享”的内存争夺，这样会降低两个 goroutine 的速度。

另外，fieldalignment 自动调整字段顺序后会导致字段后的注释被丢掉了。

### 3.5 可视化 Go Runtime 指标 - statsviz

[statsviz](https://github.com/arl/statsviz) 可以方便的集成到 HTTP 服务中，然后在浏览器中就可以实时看到服务器的 runtime 指标信息：堆、对象、goroutine、GC、调度器等。

在 HTTP 服务中注册 statsviz 方式如下：

```go
// 注册到自定义的 HTTP Server
mux := http.NewServeMux()
statsviz.Register(mux)

// 注册到 Go 默认的 HTTP Server
statsviz.RegisterDefault()

// 注入到 gin 框架
router := gin.New()
router.GET("/debug/statsviz/*filepath", func(context *gin.Context) {
  if context.Param("filepath") == "/ws" {
    statsviz.Ws(context.Writer, context.Request)
    return
  }
  statsviz.IndexAction("/debug/statsviz").ServeHTTP(context.Writer, context.Request)
}
```

statsviz 默认会向 HTTP 服务中注入`/debug/stasviz`和`/debug/statsviz/ws`两个路由：前者用于 HTML 资源响应，后者提供 WebSocket 服务向页面提供实时数据。

## 四、其他

### 4.1 chromedp

[chromedp](github.com/chromedp/chromedp) 是一个更快、更简单的、支持 [Chrome DevTools Protocol](https://chromedevtools.github.io/devtools-protocol/) 协议的 Go 库。

它是目前最流行的 headless 浏览器库之一，可以使用它做很多只能通过浏览器才能执行的任务，如：网页截屏、网页渲染测试、下载视频、模拟登录等。

参考示例：[轻轻松松打印网页并生成pdf文档](https://colobu.com/2021/05/05/generate-pdf-for-a-web-page-by-using-chromedp/)

### 4.2 go-pretty & pterm 命令行美化

[jedib0t/go-pretty](https://github.com/jedib0t/go-pretty) 在终端输出漂亮的表格、列表、进度条等：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637492194772-5b457808617c.jpg)

[pterm/pterm](https://github.com/pterm/pterm) 用于美化控制台输出，100% 跨平台兼容：

![](http://cnd.qiniu.lin07ux.cn/markdown/dd7f9d85aea5421244c0ea10ba2d71a3.svg)

### 4.3 go-spew 查看对象内部数据

> [看透 Go 对象内部细节的神器](https://mp.weixin.qq.com/s/TUrT58ry1AF6KWLGFnYLww)

[davecgh/go-spew/spew](github.com/davecgh/go-spew/) 为 Go 结构体实现了一个深度打印机，可以将结构体的各个字段对应的内容（不论是指针还是一般值）都能很好的展示出来，而且可以处理循环引用的问题。安装如下：

```shell
go get -u github.com/davecgh/go-spew/spew
```

在调试 Go 程序时，常需要知道对象的内部数据是什么样的，一般情况下可以使用`fmt`包来打印，复杂场景下需要利用调试器 GDB、LLDB、Delve 等来完成。

但是这两种做法都有不足之处。`fmt`包能打印的信息并不不友好，尤其在结构体中含有指针对象时；通过调试器来调试程序也经常受限于各种因素，例如远程访问服务器。

比如，定义 Instance 和 Inner 结构体，其中 Instance 中的`C`属性字段是 Inner 类型指针：

```go
type Instance struct {
  A string
  B int
  C *Inner
}

type Inner struct {
  D string
  E string
}

ins := Instance{
  A: "AAAA",
  B: 1000,
  C: &Innner{
    D: "DDDD",
    E: "EEEE",
  },
}
```

在`fmt`中打印`ins`变量，结果如下：

```go
fmt.Println(ins) // {AAAA 1000 0xc000054020}
```

由于 C 字段是指针，所以打印出来的是一个地址，而地址背后的数据确被隐藏了。显然，这对程序排查很不友好。

使用 go-spew 时候，通过`spew.Dump(ins)`，打印的结果如下：

```
(main.Instance) {
 A: (string) (len=4) "AAAA",
 B: (int) 1000,
 C: (*main.Inner)(0xc0000ba0c0)({
  D: (string) (len=4) "DDDD",
  E: (string) (len=4) "EEEE"
 })
}
```


