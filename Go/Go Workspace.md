Go 1.18 正式引入了工作区的概念，其实就是将多个 Go Module 组成一个 Go Workspace，工作区的读取优先级是最高的。

### 1. 解决的问题

在未引入工作区之前，日常开发中会遇到类似如下的问题：

场景一：在本地会对一些三方依赖库进行特定修改，然后在项目中想使用本地修改后的版本，就需要在 go.mod 中使用 replace 命令进行替换，然后进行联调。这样虽然可以解决问题，但是直接修改了 go.mod 文件，有可能会将该修改误传到远程仓库中，影响其他开发和部署。

场景二：在本地开发的库，还未发到远程仓库，想进行本地测试联调，就需要在其他项目中引入该依赖库。但是引入之后执行`go mod tidy`就会提示远程库不存在或者版本不符合预期。

这些类似的问题引出了 Go Workspace 的概念。使用工作区，能够方便的引入本地开发的库和版本。在完成开发、联调之后删除工作区即可，不会影响代码效果，也不会影响远程仓库的内容。

也就是说，Go Workspace 的引入主要是用于方便本地调试，使得本地调试时不会因为修改 go.mod 而引入相关问题。

## 二、使用

使用`go help work`可以查看 Go Work 提供的功能：

```shell
$ go help work
Usage:

        go work <command> [arguments]

The commands are:

        edit        edit go.work from tools or scripts
        init        initialize workspace file
        sync        sync workspace build list to modules
        use         add modules to workspace file

Use "go help work <command>" for more information about a command.
```

在编译时，可以通过如下方式临时关闭工作区模式：

```shell
go build -workfile=off .
```

### 3. go.work

执行`go work init`命令初始化一个新的工作区，并在项目中生成一个`go.work`文件：

```go
go 1.18

// 多模块添加
use (...)

replace XXXXX => XXXX v1.4.5
```

go.work 文件的语法与 go.mod 一致，不过其支持三个指令：

* `go` 声明 Go 版本号；

* `use` 声明应用所以来模块的具体文件路径，可以是绝对路径，也可以是相对路径，即使路径是当前项目目录之外也可以；

* `replace` 声明替换某个模块依赖的导入路径，优先级高于 go.mod 中的 replace 指令。

