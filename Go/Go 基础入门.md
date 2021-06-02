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

### 1.3 环境变量

Go 自身有一些环境选项可以进行配置：

* `go env -w GOPROXY=https://goproxy.cn,direct` 使用国内第三方包镜像

### 1.4 包与大小写

Go 代码都是属于某个包的，包是一种将相关的 Go 代码组合到一个的方式。

Go 项目中需要定义一个`main`包，并在其中定义一个`main`函数。

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

### 2.3 if

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

### 2.4 for

在 Go 中循环和迭代都只能使用`for`语法，因为 Go 语言中没有`while`、`do`、`until`这几个关键字。

```go
func Repeat(character string) string {
    var repeated string
    for i := 0; i < 5; i++ {
        repeated += character
    }
    return repeated
}
```


