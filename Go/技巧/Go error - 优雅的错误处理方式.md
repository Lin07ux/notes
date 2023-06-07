> 转摘：[Go 只会 if err != nil？这是不对的，分享这些优雅的处理姿势给你！](https://mp.weixin.qq.com/s/L_Sy4_6BURL4XWDK6bpEwg)

Go 谚语中的“Don't just check errors, handle them gracefully”，和“Errors are value”关联性很强，是互为解答的。Dave Cheney 通过文章《[Don't just check errors, handle them gracefully](https://dave.cheney.net/2016/04/27/dont-just-check-errors-handle-them-gracefully)》对这条谚语进行了解释。

没有单一的方法可以完美处理错误，但是在 Go 中错误处理可以为归为三个核心策略。

## 一、哨兵错误

第一种错误处理的形式常称之为哨兵错误（Sentinel errors），形式如下：

```go
if err == ErrSomething { ... }
```

这个名字来源于计算机编程中使用特定值来表示无法进一步处理的做法。所以在 Go 中，常使用特定的值来表示错误。比如`io.EOF`和`syscall.ENOENT`等。

甚至还有一些标志着错误没有发生的哨兵错误，例如：

* `go/build.NoGoError`
* `path/filepath.SkipDir`

**使用哨兵值是最不灵活的错误处理策略**，因为调用者必须使用等式运算符将结果与预先声明的值进行比较。当想提供更多的错误背景时，这就出现了问题，因为返回不同的错误会破坏等式检查。

即使是像使用`fmt.Errorof`为错误添加一些上下文这样有意义的东西，也会破坏调用者的等式检查。相反，调用者将被迫查看错误的`Error`方法的输出，看它是否与一个特定的字符串相匹配。

### 1.1 不要检查 error.Error 的输出

作为一个旁观者，永远不应该检查`error.Error()`方法的输出。

错误接口上的`Error()`方法是为人类而存在的（也就是提升人在阅读时的可读性），而不是为代码存在的。

`error.Error()`方法输出的字符串的内容属于日志文件，或者显示在屏幕上，不应该试图通过检查它来改变程序的行为。虽然有时候这是不可能的，比如在编写测试时。但是应该尽量避免这种方式，因为比较错误字符串形式是坏代码的味道。

### 1.2 哨兵错误成为公共 API 的一部分

如果公共函数或方法返回一个特定值的错，那么这个值必须是公开的，当然也就需要在 API 文档中有所记录。

如果 API 定义了一个返回特定错误的接口，那么该接口的所有实现都应该被限制在仅返回该错误，即使它们可以提供一个更具有描述性的错误。

在`io.Reader`中可以看到这一点。像`io.Copy()`这样的函数，需要读取器来实现准确地返回`io.EOF`，以便向调用者发出没有数据的信号，但这并不是一个错误。

### 1.3 哨兵错误在两个包间建立了依赖

**哨兵错误值最糟糕的问题是它们在两个包之间产生了源代码的依赖性**。

举个例子：为了检查一个错误是否等于`io.EOF`，那么就必须引入`io`包。

这个具体的例子听起来并不坏，因为它很常见。但是当项目中的许多包导出错误值，而项目中的其他包必须导入这些错误值以检查特定的错误条件时，就会出现明显的耦合。

这种不良设计的“幽灵”很容易在大型项目中产生循环导入问题。而且这个问题在 Go modules 中很容易产生，因为 grpc、grpc-gateway、etcd 常年就存在各种包版本的兼容性问题。一旦有依赖就会被动升级，然后应用就会因为版本少了东西跑不起来了。

### 1.4 结论：避免哨兵错误

建议：**在代码中尽量避免使用哨兵错误值**。

虽然在标准库中有少数情况下会使用它们，但这并不是应该模仿的模式。

如果有人要求从包中导出一个错误值，应该礼貌的拒绝，并建议采用其他方法，比如下面要讨论的方式。

## 二、错误类型

第二种错误处理的形式是错误类型（Error types）方式，形式如下：

```go
if err, ok := err.(SomeType); ok { ... }
```

错误类型指的是创建的一个实现了错误接口的类型。

### 2.1 错误类型示例

在下面的这个例子中，`MyError`类型的三个字段分别代表为：文件、代码行、错误描述信息：

```go
type MyError struct {
    Msg  string
    File string
    Line int
}

func (e *MyError) Error() string {
    return fmt.Sprintf("%s:%d: %s", e.File, e.Line, e.Msg)
}

func something() *MyError {
  // ...
  return &MyError{"Something happened", "server.go", 42}
}
```

因为`MyError`错误是一个类型，调用者可以通过类型断言来从错误中提取出额外的上下文：

```go
err := something()
switch err := err.(type) {
case nil:
    // call succeeded, nothing to do
case *MyError:
    fmt.Println("error occurred on line:", err.Line)
default:
    // unknown error
}
```

与错误值相比，错误类型的一大改进是能够包装底层错误以提供更多的背景（上下文信息）。

一个更好的例子是`os.PathError`类型，它将试图要执行的文件操作和文件路径都记录在类型里：

```go
// PathError records an error and the operation
// and file path that caused it.
type PathError struct {
    Op   string
    Path string
    Err  error  // the cause
}

func (e *PathError) Error() string
```

### 2.2 错误类型的问题

因为调用者可以使用类型断言或类型转换，所以错误类型必须被公开。

如果代码实现了一个接口，而这个接口的契约需要一个特定的错误类型，那么这个接口的所有实现者都需要依赖定义错误类型的包。

这种对包的类型的深入了解，造成了与调用者的强耦合，使 API 变得很脆弱。

### 2.3 结论：避免使用错误类型

虽然错误类型比哨兵错误值要好，因为它可以捕获更多关于出错的上下文，但错误类型也有很多错误值的问题。

因此，也要避免使用错误类型，或者至少避免将其作为公共 API 的一部分。

## 三、不透明的错误

第三类错误处理方式是一种最灵活的错误处理策略，因为其要求的代码和调用者之间的耦合度最小。

这种风格可以称为不透明的错误处理（Opaque errors），因为调用者虽然能知道发生了错误，但不能看到错误的内部，所知道的关于操作结果的所有信息就是调用是成功还是失败。

这就是不透明的错误处理的全部内容——只是返回错误，而不对其内容做任何假设。如果采取这种立场，那么错误处理作为一种调试辅助手段就会变得非常有用。

示例如下：

```go
import "github.com/quux/bar"

func fn() error {
    x, err := bar.Foo()
    if err != nil {
        return err
    }
    // use x
}
```

例如，`Foo`的契约没有保证它在错误的上下文中会返回什么，其作者可以自由地用额外的上下文来注释通过它的错误，而不破坏它与调用者的契约。

## 四、优雅处理

### 4.1 为行为而不是类型断言错误

在少数情况下，使用二分法（是否有错误）来进行错误处理是不够的。

例如：与进程之外的交互，如网络活动，需要调用者查看错误的性质，以确定重试操作是否合理。

在这种情况下，与其断言错误是一个特定的类型或值，可以断言错误实现了一个特定的行为。

考虑下面的例子：

```go
type temporary interface {
    Temporary() bool
}

// IsTemporary returns true if err is temporary
func IsTemporary(err error) bool {
    te, ok := err.(temporary)
    return ok && te.Temporary()
}
```

可以将任何错误传递给`IsTemporary()`方法，以确定该错误是否可以被重试：

* 如果错误没有有实现`temporary`接口（也就是说它没有一个`Temporary()`方法），那么这个错误就不是临时的；
* 如果错误实现了`temporary`接口，而且其`Temporary()`方法返回了 true，那么调用者也许可以尝试重试操作。

这里的关键是：这个逻辑可以在不导入定义错误的包的情况下实现，也不需要知道 err 的底层类型，只是对其行为感兴趣。

### 4.2 不要只是检查错误，要优雅的处理它们

> Don't just check errors, handle them gracefully.

Go 中的这句谚语是说，不要只是检查错误，要优雅的处理它们。

#### 4.2.1 增加错误背景

对于下面的这段代码，可以看出什么问题吗？

```go
func AuthenticateRequest(r *Request) error {
    err := authenticate(r.User)
    if err != nil {
        return err
    }
    return nil
}
```

一个明显的建议是，函数体可以压缩成一行：

```go
return authenticate(r.User)
```

这是每个人都应该在代码审查中抓住的简单东西。而更为根本的是，这段代码的问题是无法判断原始错误来自哪里。

如果`authenticate()`方法返回一个错误，那么`AuthenticateRequest()`方法将把这个错误返回给它的调用者，后者也可以继续如此处理，以此类推。

在程序的顶部，程序的主体将把错误打印到屏幕或者日志文件中，而打印的内容是：No such file or directory。

这段输出没有任何产生错误的文件和行的信息，也没有错误的调用堆栈的跟踪的信息。这段 diam 的作者将被迫对他们的代码进行长时间的剖析，以发现哪个代码路径引发了文件未找到的错误。

Donovan 和 Kernighan 的《Go 编程语言》中建议使用`fmt.Errorf()`为错误路径添加上下文：

```go
func AuthenticateRequest(r *Request) error {
    err := authenticate(r.User)
    if err != nil {
        return fmt.Error("authenticate failed: %v", err)
    }
    return nil
}
```

但正如前面所看到的，这种模式与哨兵错误值或类型断言的使用不兼容，因为其将错误值转换为一个字符串，与另一个字符串合并，然后使用`fmt.Errorf()`将其转换为一个新的错误。这会破坏之前的等式检查或断言，还会破坏原始错误的任何上下文。

#### 4.2.2 注释错误

有一种为错误添加上下文的方法，也就是注释错误（Annotating errors），给错误增加注释。

包`github.com/pkg/errors`即可完成注释错误功能（在 Go1.13 起，类似的思路已经被 Go 官方引入）。

`errors`包有两个主要功能：为错误增加注释和解错误注释，这两个功能分别对应一个函数：

* 第一个函数是`Wrap()`，它接收一个错误和一个信息，并产生一个新的错误：

    ```go
    // Wrap annotates cause with a message
    func Wrap(cause error, message string) error
    ```

* 第二个函数是`Cause()`，它接收一个可能已经被包裹的错误，并将其解开以恢复原始错误：

    ```go
    // Cause unwraps an annotated error.
    func Cause (err error) error
    ```

使用这两个函数，就可以注释任何错误。如果要检查错误的话，可以恢复底层的错误。

考虑一下这个将文件内容读入内存的函数的例子：

```go
func ReadFile(path string) ([]byte, error) {
    f, err := os.Open(path
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
```

用这个函数来编写一个读取配置文件的函数，然后从 main 中进行调用：

```go
func ReadConfig() ([]byte, error) {
    home := os.Getenv("HOME")
    config. err := ReadFile(filepath.Join(home, ".settings.xml"))
    return config, errors.Wrap(err, "could not read config")
}

func main() {
    _, err := ReadConfig()
    if err != nil {
        fmt.Println(err)
        os.Exit(1)
    }
}
```

如果`ReadConfig()`函数代码中的代码路径失败，因为使用了`errors.Wrap()`，将会得到一个 K&D 风格的漂亮的错误注释：

```
could not read config: open failed: open /Users/dfc/.settings.xml: no such file or directory
```

因为`errors.Wrap()`产生一个错误堆栈，可以检查该堆栈以获得额外的调试信息。

下面是一个相同功能的例子，但是使用`errors.Print()`函数替代`fmt.Println()`函数：

```go
func main() {
    _, err := ReadConfig()
    if err != nil {
        errors.Print(err)
        os.Exit(1)
    }
}
```

此时可以得到如下的内容：

```
readfile.go:27: could not read config
readfile.go:14: open failed
open /Users/dfc/.settings.xml: no such file or directory
```

其中，第一行来自`ReadConfig()`函数，第二行来自`ReadFile`的`os.Open()`部分，其他内容则来自`os`本身，它没有携带位置信息。

上面是关于包裹错误以产生堆栈的概念，下面则是相反的情况，即解包粗我。这就是`errors.Cause()`函数的领域：

```go
// IsTemporary returns true if err is temporary
func IsTemporary(err error) bool {
    te, ok := errors.Cause(err).(temporary)
    return ok && te.Temporary()
}
```

在操作中，每当需要检查一个错误与一个特定的值或类型匹配的时候，应该首先使用`errors.Cause()`函数来恢复错误。

### 4.3 只处理一次错误

错误应该只被处理一次。处理一个错误意味着检查错误值，并作出决定。

如果做的决定少于一个，就会忽略掉这个错误。正如下面的例子中所示的，来自`w.Write(buf)`的错误被丢弃了：

```go
func Write(w io.Writer, buf []byte) {
    w.Write(buf)
}
```

但是针对一个错误做出多于一个的决定也是有问题的：

```go
func Write(w io.Writer, buf []byte) {
    _, err := w.Write(buf)
    if err != nil {
        // annotated error goes to log file
        log.Println("unable to write:", err)
        
        // unannotated error returned to caller
        return err
    }
    return err
}
```

在这个例子中，如果在写的过程中发生了错误，就会有一行写到日志文件中，指出这个错误发生的文件和行，并且错误也会返回给调用者。调用者可能会记录它，并返回它，一直到程序的顶部。

因此，就会在日志文件中得到一堆重复的行，但在程序的顶部得到了没有任何背景的原始错误。

此时可以考虑使用错误增加背景后返回即可：

```go
func Write(w io.Writer, buf []byte) {
    _, err := w.Write(buf)
    return errors.Wrap(err, "write failed")
}
```

通过`errors.Wrap()`函数就能为错误添加上下文，以一种人类和机器都可以检查的方式进行对值编程。

## 五、总结

错误是包的公共 API 的一部分，对待它们要像对待公共 API 的任何其他部分一样谨慎。

为了获得最大的灵活性，建议尽量把所有的错误都当做不透明的。在无法做到的情况下，为行为而不是类型或值断言错误。

在程序中尽量减少哨兵错误值的数量，并在错误发生时用`errors.Wrap()`函数将其转换为不透明的错误。如果需要检查的话，使用`errors.Cause()`来恢复底层错误。

