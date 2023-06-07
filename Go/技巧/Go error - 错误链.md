> 转摘：[一文搞懂 Go 错误链](https://mp.weixin.qq.com/s/nvgNsZgnm_ymb-avI6Zg7Q)

### 1. error 和错误链

Go 语言中，`error`表示错误，是一个 Interface，只包含了一个`Error() string`方法约束，任何实现了该方法的类型都可以作为 error：

```go
type error interface {
  Error() string
}
```

但是这种方式也会使得在抛出错误的时候并不能带有触发错误的调用栈，对后续的错误追踪和分析造成很大的麻烦。而错误链则可以比较好的解决这个问题。

将错误逐步的包裹起来，并支持逆向的解包，这种由错误逐个包裹而形成的链式结构，就是错误链：

![](https://cnd.qiniu.lin07ux.cn/markdown/48ee8e548171ac42ff9f507c8e3f4f96.jpg)

Go 从 1.13 版本加入了对错误链的支持，并逐步完善，在此之前则是通过第三方提供的 package 来实现的。

### 2. 创建错误链

Go 中的错误链是通过包裹错误来创建的，目前 Go 标准库中提供了`fmt.Errorf()`函数和`errors.Join()`函数来进行错误包裹。

### 2.1 fmt.Errorf()

Go 1.13 中为`fmt.Errorf`提供了一个新的`%w`格式化动词，用于构建可以包裹其他错误的错误，以及从一个包裹了其他错误的错误中判断是否有某个指定错误，并从中提取错误信息。

`fmt.Errorf()`是最常用的用于包裹错误的函数，它接收一个现有的错误，并将其包装在一个新的错误中，并可以附着更多的错误上下文信息。示例如下：

```go
func processFile(filename string) error {
  data, err := readFile(filename)
  if err != nil {
    return fmt.Errorf("failed to read file: %w", err)
  }
  return nil
}
```

这段代码中，通过`fmt.Errorf()`方法的`%w`格式化动词创建了一个新的错误，新错误中包裹了一个读取文件方法抛出的错误，并附加了一些错误上下文信息（`failed to read file`）。这个新的错误可以在调用栈中传播并提供更多关于这个错误的上下文。

`fmt.Errorf()`也支持通过多个`%w`一次性打包多个 error，示例如下：

```go
func main() {
  err1 := errors.New("error1")
  err2 := errors.New("error2")
  err3 := errors.New("error3")

  err := fmt.Errorf("wrap multiple error: %w, %w, %w", err1, err2, err3)
  fmt.Println(err)
  e, ok := err.(interface{ Unwrap() []error })
  if !ok {
    fmt.Println("not imple Unwrap []error")
    return
  }
  fmt.Println(e.Unwrap())
}
```

运行的结果如下：

```go
wrap multiple error: error1, error2, error3
[error1 error2 error3]
```

可以看到，**通过`fmt.Errorf()`一次 wrap 多个 error 后，在输出字符串的时候，被包裹的多个 error 是在一行中输出的。**

#### 2.2 errors.Join()

`errors.Join()`用于将一组 errors 包裹成一个 error，但是它和`fmt.Errof()`一次包裹多个错误在输出上的表现不同：

```go
func main() {
  err1 := errors.New("error1")
  err2 := errors.New("error2")
  err3 := errors.New("error3")
  
  err := errors.Join(err1, err2, err3)
  fmt.Println(err)
  errs, ok := err.(interface{ Unwrap() []error })
  if !ok {
    fmt.Println("not imple Unwrap []error")
    return
  }
  fmt.Println(errs.Unwrap())
}
```

运行输出如下：

```text
error1
error2
error3
[error1 error2 error3]
```

可以看到，通过`errors.Join()`来包裹多个 error 时，在输出的时候这些 error 是每个占用一行。

当然，由于 Go error 就是一个类型，所以可以通过自定义 Error 类型，并实现`String() string`和`Unwrap() error`或`Unwrap() []error`即可自定义输出格式。

### 3. 错误检索

为了从错误链中检索原始错误，Go 在 errors 包中提供了`Is()`、`As()`和`Unwrap()`函数：

* `Is()`函数用于判断某个 error 是否存在于错误链中；
* `As()`函数用于从错误链中获取出指定的 error；
* `Unwrap()`返回错误链中的下一个直接错误。

#### 3.1 errors.Is()

示例程序如下：

```go
func readFile(filename string) ([]byte, error) {
  data, err := os.ReadFile(filenam)
  if err != nil {
    return nil, err
  }
  return data, nil
}

func processFile(filename string) error {
  data, err := readFile(filename)
  if err != nil {
    return fmt.Errorf("failed to read file: %w", err)
  }
  fmt.Println(string(data))
  return nil
}

func main() {
  err := processFile("1.txt")
  if err != nil {
    fmt.Println(err)
    fmt.Println(errors.Is(err, os.ErrNotExist))
    err = errors.Unwrap(err)
    fmt.Println(err)
    err = errors.Unwrap(err)
    fmt.Println(err)
  }
}
```

当`1.txt`文件不存在的时候，执行这个程序，可以得到如下的结果：

```shell
$go run demo1.go
failed to read file: open 1.txt: no such file or directory
true
open 1.txt: no such file or directory
no such file or directory
```

该示例中错误的 wrap 和 unwrap 关系如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/a7b3f232fbb62dea0d975177a1bbac1c.jpg)

#### 3.2 errors.As

`errors.As()`函数用于从错误链中提取特定类型的 error：

```go
type MyError struct {
  err string
}

func (e *MyError) Error() string {
  return e.err
}

func main() {
  err1 := &MyError{"temp error"}
  err2 := fmt.Errorf("2nd err: %w", err1)
  err3 := fmt.Errorf("3rd err: %w", err2)
  
  fmt.Println(err3)
  
  var e *MyError
  if ok := errors.As(err3, &e); ok {
    fmt.Println(e)
  }
}
```

这里就通过`errors.As()`函数将错误链中的`MyError`类型的错误给提取出来。

#### 3.3 errors.Unwrap

`errors.Unwrap()`方法用于对错误链进行 unwrap，每调用一次解包一层错误包裹，直到到达最原始的错误。

由于不确定错误链上的 error 的个数，以及每个 error 的特征，所以可以使用如下的方式来解包错误链，获取最原始的错误信息：

```go
func rootCause(err error) error {
  for {
    e, ok := err.(interface{ Unwrap() error })
    if !ok {
      return err
    }
    err = e.Unwrap()
    if err == nil {
      return nil
    }
  }
}
```

### 4. 总结

错误链是 Go 中提供调用栈等丰富信息的一种重要方式，通过额外的上下文包装错误，可以提供关于错误的更具体的信息，帮助开发人员更快地诊断出问题。

不过错误链在使用中需要注意避免嵌套错误链，这会使得代码难以调试，也难以理解错误的根本原因。

结合错误链，通过给错误添加上下文，创建自定义错误类型，并在适当的抽象层次上处理错误，可以写出简洁、可读和信息丰富的错误处理代码。
