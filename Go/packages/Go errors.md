> 转摘：
> 
> 1. [Go1.20 继续小修小补 errors 库。。。](https://mp.weixin.qq.com/s/gfUM4EjE1av_YBeUBFyKtA)
> 2. [Go版本大于1.13，程序里这样做错误处理才地道](https://mp.weixin.qq.com/s/SFbSAGwQgQBVWpySYF-rkw)

### 1. error interface

error 类型是 Go 的一个内建接口类型，其之规定了一个返回字符串值的`Error()`方法：

```go
type error interface {
  Error() string
}
```

常用的生成一个 error 的方式如下：

```go
errors.New("something is error")
fmt.Errorf("something is error")
```

当然，也可以自定义 error 类型，只要实现了`Error() string`方法即可。

### 2. Wrapping Error(Go 1.13)

在 Go 1.13 中，errors 标准库引入了 Wrapping Error 的概念。不过 Go 并没有提供 Error 嵌套的`Wrap`方法，而是直接**扩展了`fmt.Errorf()`方法，使用`%w`符号表示 Error 嵌套**。

`fmt.Errorf()`方法可以把多个错误存进错误链中，其底层是基于`wrapError`结构体实现了 error interface，然后一层层往上套 error：

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

### 3. errors.Is()

Go 1.13 中，与 Error Wrapping 一同引入了`Is/As/Unwrap`三个方法，用于对所返回的错误进行二次处理和识别。

引入 Error Wrapping 后，之前直接比较两个错误是否等同的方式可能就不行了，因为不知道这个 error 是不是一个嵌套的 error，也不知道嵌套了几层。

基于这种情况，新增了`errors.Is()`方法来判断所传入的 err 和 target 是否是同一类型。

方法签名为：

```go
func Is(err, target error) bool
```

其判断逻辑为：

* 如果`err`和目标错误`target`是同一个，那么返回 true；
* 如果`err`是一个包装错误，目标错误`target`也包含在这个嵌套错误链中，那么也返回 true；
* 否则返回 false。

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

### 4. errors.As()

在没有 Error Wrapping 之前，想把 error 转换成一个具体类型的 error，一般都是使用类型断言或者`type switch`：

```go
if pathErr, ok := err.(*os.PathError); ok {
  fmt.Println(pathErr.Path)
}
```

但是有了包装错误之后，得到的 error 可能是已经被嵌套的了，就不能直接使用类型断言了。所以新增了`errors.As()`方法，来从 err 错误链中识别和 target 相同的类型的错误。

方法签名：

```go
func As(err error, target interface{}) bool
```

其逻辑为：

* 遍历 err 错误链，从中寻找和 target 类型相符的 error；
* 如果找到了，则将该 error 赋值给 target，并返回 true；
* 否则，遍历完整个错误链都没有找到相符的 error，返回 false。

需要注意的是，因为`errors.As()`方法会对 target 进行赋值，所以 target 必须是一个指针。这个算是 Go 内置包里的一个惯例了，像`json.Unmarshal`也是这样。

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

### 5. erros.Unwrap()

`errors.Unwrap()`方法的作用是将嵌套的 error 解析出来。若存在多级嵌套，则需要调用多次`errors.Unwrap()`方法。

方法源码如下：

```go
func Unwrap(err error) error {
  // 先判断是否是 wrapping error
  u, ok := err.(interface {
    Unwrap() error
  })
  // 如果不是则返回 nil
  if !ok {
    return nil
  }
  // 否则就调用该 error 的 Unwrap() 方法以得到被嵌套的 error
  return u.Unwrap()
}
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

### 6. Wrapping multiple errors(Go 1.20)

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


