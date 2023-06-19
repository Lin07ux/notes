Go 中如果在子协程中遇到了 panic，那么主协程也会被终止。在 Web 服务中遇到 panic 就会造成服务崩溃，在生产环境中是不可接受的，需要采用 recover 来捕获该 panic 让服务继续健康运行。

gin 框架中提供了一个默认的 recovery 中间件，通过该中间件来捕获 panic，并保证服务不中断。使用`gin.Default()`函数创建的 gin 对象，默认就注册了 Recovery 中间件。

Recovery 中间件的代码如下：

```go
func Recovery() HandlerFunc {
  return RecoverWithWriter(DefaultErrorWriter)
}

func RecoverWithWriter(out io.Writer, recovery ...RecoveryFunc) HandlerFunc {
  if len(recovery) > 0 {
    return CustomRecoveryWithWriter(out, recovery[0])
  }
  return CustomRecoveryWithWriter(out, defaultHandleRecovery)
}

func defaultHandleRecovery(c *Context, err any) {
  c.AbortWithStatus(http.StatusInternalServerError)
}
```

这里的`DefaultErrorWriter`就是默认的输出端，即`os.Stderr`，表示 panic 错误输出的地方。

可以看到，默认情况下，在发生 panic 的时候，gin 会返回一个状态码为 500 的响应，并结束本次请求。

关键的处理过程是在`CustomRecoveryWithWriter`的实现上：

```go
func CustomRecoveryWithWriter(out io.Writer, handle RecoveryFunc) HandlerFunc {
  var logger *log.Logger
  if out != nil {
    logger = log.New(out, "\n\n\x1b[31m", log.LstdFlags)
  }
  return func(c *Content) {
    defer func() {
      if err := recover(); err != nil {
        // ...
      }()
      c.Next()
    }
  }
}
```

`CustomRecoveryWithWriter`函数在确定好日志记录器之后，返回了一个 HandlerFunc 中间件函数，在该函数中使用 defer 方式进行 recover 调用，从而保证在每个请求中都能捕获到 panic，而避免了任何一个请求异常导致整个服务崩溃。

在默认情况下，panic 的获取会进行细致的原因处理，更偏向细节。而处理得到的错误原因和堆栈会记录到日志记录器中，实现日志保存。

参考 gin 的 Recovery/CustomRecoveryWithWriter 逻辑，可以实现自定义的 panic 捕获方式，并在发生 panic 的时候增加报警等措施。