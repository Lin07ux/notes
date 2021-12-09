> 转摘：[透过一个编译报错，总结两个 Go 程序编译的重要知识](https://mp.weixin.qq.com/s/u0gfKNNjNmhq4UJy2Hl9Cw)

## 一、交叉编译

**交叉编译是用来在一个平台上生成另一个平台的可执行程序。**

Go 的命令集是原生支持交叉编译的，使用方法也很简单，如下所示：

```shell
CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build main.go
```

### 1.1 交叉编译参数

交叉编译命令的环境参数说明：

* `CGO_ENABLED`：CGO 是 Go 中的工具，这个选项表示是否启用 CGO，值为 0 时表示禁用。*交叉编译中不能启用 CGO*。

* `GOOS`：编译的目标系统

    - MacOS 对应的是`darwin`
    - Linux 对应的是`linux`
    - Windows 对应的是`windows`

* `GOARCH`：编译的目标 CPU 体系架构

    - `386` 也称为`x86`，对应的是 32 位操作系统
    - `amd64` 也称为`x64`，对应的是 64 位操作系统，目前市面上的个人电脑一般都是这种架构的
    - `arm` 这种架构一般用于嵌入式开发，如 Android、iOS、Windows Mobile、TiZen 等

### 1.2 交叉编译示例

在 Mac 中编译，Linux/Windows 中执行：

```shell
# 编译 Linux 中可执行的程序
CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build main.go
# 编译 Windows 中可执行的程序
CGO_ENABLED=0 GOOS=windows GOARCH=amd64 go build main.go
```

在 Linux 中编译，Mac/Windows 中执行：

```shell
# 编译 Mac 中可执行的程序
CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build main.go
# 编译 Windows 中可执行的程序
CGO_ENABLED=0 GOOS=windows GOARCH=amd64 go build main.go
```

在 Windows 中编译，Mac/Linux 中执行：

> 因为 Windows 中的 Terminal 不支持 shell，所以需要写一个批处理程序进行编译。

```shell
# 编译 Mac 中可执行的程序
SET CGO_ENABLED=0
SET GOOS=darwin
SET GOARCH=amd64
go build main.go

# 编译 Linux 中可执行的程序
SET CGO_ENABLED=0
SET GOOS=linux
SET GOARCH=amd64
go build main.go
```

## 二、条件编译

交叉编译只是为了能够在一个平台上编译出其他平台可运行的程序，Go 作为一个跨平台的语言，它提供的类库势必也是跨平台的。比如说程序的系统调用相关的功能，就能够根据所处环境选择对应的源码进行编译。

**让编译器只针对满足条件的带你进行编译，将不满足条件的代码舍弃，这就是条件编译。**在 Go 中，条件编译也被称为 Build Constraints（编译约束）。

Go 中添加编译约束的方式有两种：

* 使用编译标签(Build Tag)
* 使用文件名后缀

### 2.1 编译标签

编译标签是一种通过在源码文件顶部添加注释，来决定文件是否参与编译的约束方式。其格式如下：

```go
// +build <tags>
```

编译标签一般会写在 Go 源码文件的最顶部。而且编译标签要独占一个注释行，并且该行的下一行必须是空行，否则它将会被解析为包注释。

示例如下：

```go
// +build <tags>

// main package comment
package main
```

可以同时指定多个编译标签，多个编译标签的合并逻辑如下：

* 以空格` `分开，表示 AND
* 以逗号`,`分开，表示 OR
* 以`!`修饰，表示 NOT

每一个编译标签可以指定为以下内容：

* 操作系统：值为环境变量 GOOS 的可选值，如`linux`、`darwin`、`windows`等；
* CPU 架构：值为环境变量 GOARCH 的可选值，如`amd64`、`x86`、`i386`等；
* 使用的编译器：值可以为`gc`或`gccgo`；
* 是否开启 CGO：值为`cgo`或`!cgo`；
* Go 版本号：值为 Go 的版本表示，比如 Go Version 1.1 为`go1.1`，Go Version 1.12 版本为`go1.12`，以此类推；
* 其他自定义标签：这些标签可以通过构建指令`go build -tags`指定相应的值。

例如，编译条件为`(linux AND 386) OR (darwin AND (NOT cgo))`：

```go
// +build linux,386 darwin,!cgo
```

除了在一个编译注释行中写多个编译条件，还可以将它们分开在多个编译注释行中，此时这多个编译注释行中的编译条件为 AND 关系。比如，下面的注释表示的编译条件为`(linux OR darwin) AND amd64`：

```go
// +build linux darwin
// +build amd64
```

也可以使用`ignore`标签将一个文件从编译中排除：

```go
// +build ignore
```

### 2.2 文件后缀

另一种指定编译条件的方式是通过源码文件的文件名来实现。这种方案比编译标签方案更简单。编译器会根据文件名后缀来自动选择编译文件。

格式如下：

```
$filename_$GOOS.go
$filename_$GOARCH.go
$filename_$GOOS_$GOARCH.go
```

格式说明：

* `$filename` 为源文件名称
* `$GOOS` 表示操作系统，从环境变量中获取
* `$GOARCH` 表示系统架构，从环境变量中取值

这种方案的后缀顺序记住不要颠倒：在后缀中同时出现系统和架构类别时，需要保持系统在前架构在后的顺序。

在 Go 的每个内置库里都有很多以不同系统名结尾的文件。下面是 Go 的 os 内置库源代码的部分截图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1638955516856-c1862db60e26.jpg)

### 2.3 如何选择

构件标签和文件名后缀在功能上是重叠的。比如。一个名为`mypkg_linux.go`的文件，再在其中包含编译标签`// +build linux`就会显得多余。

通常来说，**当只有一个特定平台需要指定时，应该选择文件名后缀的方式**。比如：

```shell
mypkg_linux.go  # 只在 linux 系统编译
myokg_windows_amd64.go  # 只在 Windows amd64 平台编译
```

相反，如果源文件需要指定给多个平台或体系架构使用，或者需要排除某个特定场景时，应该选择编译标签的方式。比如：

```go
// 在所有类 unix 平台编译
// +build darwin dragonfly freebsd linux netbsd openbsd

// 在非 Windows 平台编译
// +build !windows
```


