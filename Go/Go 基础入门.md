## 一、运行和开发环境

### 1.1 安装

Go 预约官方安装指引可参见 [这里](https://golang.org/doc/install)。

在 Mac 中还可以使用 Homebrew 进行安装：

```shell
# Brew 依赖 Xcode
xcode-select --install

# 安装 Homebrew
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"

# 安装 Go
brew install go

# 查看 Go 的版本确定是否安装成功
go version
```

### 1.2 GOPATH

Go 有一套特别的惯例：

> 所有 Go 代码都存在与一个工作区（文件夹）内。这个工作区可以在机器的任何地方。如果不指定，Go 将假定`$HOME/go`为默认工作区。

Go 工作区由环境变量`GOPATH`标识和修改。建议设置 Go 环境变量，便于后续可以在脚本或 Shell 中使用它。

Go 假设工作区内包含一个特定的目录结构：

* `$GOPATH/src` 存放源代码
* `$GOPATH/pkg` 存放包对象
* `$GOPATH/bin` 存放编译好的程序

在命令行的配置中添加以下导出语句（在`~/.bash_profile`或`~/.zshrc`等配置文件中）：

```shell
export GOPATH=$HOME/go
export PATH=$PATH:$GOPATH/bin
```

然后执行如下命令使其生效：

```shell
source ~/.zshrc
```

### 1.3 包

Go 代码都是属于某个包的，包是一种将相关的 Go 代码组合到一个的方式。

Go 项目中需要定义一个`main`包，并在其中定义一个`main`函数。

### 1.4 大小写

在 Go 中，常量、方法、类型、interface 的名称如果是大写字母开头的，则表示其是公开的，可以被外部访问和使用的；如果是小写字母开头的，则表示其是私有的，外部不可访问和使用。

## 二、语法

### 2.1 声明变量

Go 是强类型语言，所以每个变量都要有指定的类型。这个类型可以在声明的时候指定，也可以让 Go 自动推断。

```go
var name1 string
name2 := name1
```

### 2.2 定义常量

定义常量使用`const`关键词，且无需为其指定类型。

```go
const spanish = "Spanish"
const (
    helloPrefix = "Hello, "
    spanishHelloPrefix = "Hola, "
)
```

### 2.3 定义函数

函数通过`func`关键字来声明，可以直接用来定义普通函数，也可以定义匿名函数：

```go
// 普通函数
func Hello() {
    fmt.Println("Hello")
}

// 匿名函数
foo := func() {
    fmt.Println("foo")
}
```

函数如果有参数，需要指定每个参数的类型；如果**连续的**多个参数的**类型相同**，可以只为**最后一个**同类型的参数指定类型。

函数如果有返回值，需要指定每个返回值的类型。如果为函数的返回值指定了名称，则函数体内相当于已经声明好了该名称的变量，并且`return`语句可以不指定该名称即完成返回。

> 函数如果返回多个值，可以很方便的使用一个`return`语句来完成，而无需包装成功其他的类型。

```go
func Hello(name, language string) string {
    if name == "" {
        name = "World"
    }

    return greetingPrefix(language) + name
}

func greetingPrefix(language string) (prefix string) {
    switch language {
    case french:
        prefix = frenchHelloPrefix
    case spanish:
        prefix = spanishHelloPrefix
    default:
        prefix = englishHelloPrefix
    }
    return
}
```

### 2.4 if

Go 的`if`非常类似于其他编程语言，用作于根据条件来决定是否执行代码块。

`if`的条件可以是变量、表达式，但是其类型需为 bool 类型。

另外，`if`语句不支持`else`及`else if`子句。

```go
const englishHelloPrefix = "Hello, "

func Hello(name string) string {
    if name == "" {
        name = "World"
    }
    return englishHelloPrefix + name
}
```

