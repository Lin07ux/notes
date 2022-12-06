> 转摘：[Go1.20 继续小修小补 errors 库。。。](https://mp.weixin.qq.com/s/gfUM4EjE1av_YBeUBFyKtA)

### 1.Go 1.13 Wrapping Error

在 Go 1.13 中，errors 标准库引入了 Wrapping Error 的概念，并增加了`Is/As/Unwrap`三个方法，用于对所返回的错误进行二次处理和识别。

**Error Wrap**

Go 并没有提供 Error 嵌套的`Wrap`方法，而是直接扩展了`fmt.Errorf()`方法，使用`%w`符号表示 Error 嵌套。

`fmt.Errorf()`方法可以把多个错误存进错误链中，其底层是基于`wrapError`结构体实现了 Error interface，然后一层层往上套 error：

```go
type wrapError struct {
  msg string
  err error
}

func Errorf(format string, a ...any) error {
  ...
  var err error
  if p.wrappedErr == nil {
    err = errors.New(s)
  } els e{
    err = &wrapError{s, p.wrappedErr}
  }
  p.free()
  return err
}
```

示例如下：

```go
func main() {
  e := errors.New("脑子进煎鱼了")
  w := fmt.Errorf("快抓住：%w", e)
  fmt.Println(w)
}
```

代码中将错误`e`包装生成了一个新的错误`w`，输出结果为：

```
快抓住：脑子进煎鱼了
```

**errors.Is()**

`errors.Is()`方法用于判断所传入的 err 和 target 是否是同一类型，如果是的话，则返回 true。

方法签名为：

```go
func Is(err, target error) bool
```

示例：

```go
func main() {
  if _, err := os.Open("non-existing"); err != nil {
    if errors.Is(err, os.ErrNotExist) {
      fmt.Println("file does not exist")
    } else {
      fmt.Println(err)
    }
  }
}
```

**errors.As**

`errors.As()`方法用于从 err 错误连中识别和 target 相同的类型的错误。如果可以赋值，则返回 true。

方法签名：

```go
func As(err error, target interface{}) bool
```

示例如下：

```go
func main() {
  if _, err := os.Open("non-existing"); err != nil {
    var pathError *os.PathError
    if errors.As(err, pathError) {
      fmt.Println("Failed at path:", pathError.Path)
    } else {
      fmt.Println(err)
    }
  }
}
```

**erros.Unwrap**

`errors.Unwrap()`方法的作用是将嵌套的 error 解析出来。若存在多级嵌套，则需要调用多次`errors.Unwrap()`方法。

方法签名如下：

```go
func Unwrap(err error) error
```

示例如下：

```go
func main() {
  e := errors.New("脑子进煎鱼了")
  w := fmt.Errorf("快抓住：%w", e)
  fmt.Println(w)  // 快抓住：脑子进煎鱼了
  fmt.Println(errors.Unwrap(w)) // 脑子进煎鱼了
}
```

### 2. Go 1.20 Wrapping multiple errors

虽然 Go 1.13 中的 Wrapping Error 能够实现错误的嵌套，但是并不支持将多个错误一起封装成一个错误的操作。而 Go 1.20 中 errors 标准库中新引入了`errors.Join()`方法可以实现这种操作。

`errors.Join()`方法的签名如下：

```go
func Join(errs ...error) error
```

由`errors.Join()`方法合成得到 error 在输出的时候，默认会通过换行符`\n`进行分隔。

引入`errors.Join()`方法后，原先的“错误链”就被改为了“错误树”，配套的`fmt.Errorf()`、`erros.Is()`、`errors.As()`和`errors.Unwrap()`方法都做了改造：

* `fmt.Errorf()` 可以把多个错误封装在用户定义的布局中；
* `errors.Is()` 如果能够匹配上任何错误，则返回 true；
* `errors.As()` 返回第一个匹配的错误；
* `errors.Unwrap()` 返回封装的多个错误。

其中`errors.Unwrap()`的签名改为如下方式：

```go
func Unwrap(err error) []error
```

示例如下：

```go
func main() {
  err1 := errors.New("err1")
  err2 := errors.New("err2")
  err := errors.Joinn(err1, err2)
  fmt.Println(err)
  if errors.Is(err, err1) {
    fmt.Println("err is err1")
  }
  if errors.Is(err, err2) {
    fmt.Println("err is err2")
  }
}
```

输出如下：

```
err1
err2
err is err1
err is err2
```

社区中也有对多个错误支撑比较好的包：

* [hashicorp/go-multierror](https://pkg.go.dev/github.com/hashicorp/go-multierror)
* [go.uber.org/multierr](https://pkg.go.dev/go.uber.org/multierr)
* [tailscale.com/util/multierr](https://pkg.go.dev/tailscale.com/util/multierr)


