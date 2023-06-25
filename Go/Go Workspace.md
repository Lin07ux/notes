Go 1.18 正式引入了工作区的概念，其实就是将多个 Go Module 组成一个 Go Workspace，工作区的读取优先级是最高的。

### 1. 解决的问题

在未引入工作区之前，日常开发中会遇到类似如下的问题：

场景一：在本地会对一些三方依赖库进行特定修改，然后在项目中想使用本地修改后的版本，就需要在 go.mod 中使用 replace 命令进行替换，然后进行联调。这样虽然可以解决问题，但是直接修改了 go.mod 文件，有可能会将该修改误传到远程仓库中，影响其他开发和部署。

场景二：在本地开发的库，还未发到远程仓库，想进行本地测试联调，就需要在其他项目中引入该依赖库。但是引入之后执行`go mod tidy`就会提示远程库不存在或者版本不符合预期。

这些类似的问题引出了 Go Workspace 的概念。使用工作区，能够方便的引入本地开发的库和版本。在完成开发、联调之后删除工作区即可，不会影响代码效果，也不会影响远程仓库的内容。

也就是说，Go Workspace 的引入主要是用于方便本地调试，使得本地调试时不会因为修改 go.mod 而引入相关问题。

### 2. 命令

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

执行`go work init`命令初始化一个新的工作区，并在项目中生成一个`go.work`文件。

* `go work init`之后为需要本地开发的子模块的目录名；
* 多个子模块建议在一个目录下，能够更好的管理，否则`go work init`时需要提供正确的子模块路径；
* `go.work`不需要提交到 Git 中，只用于本地开发。

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

go.work 优先级高于 go.mod 文件。

### 4. 示例

1. 在本地创建两个项目，分属于两个不同的 module：`mypkg`和`example`。

    ```shell
    $ cd ~/
    $ mkdir polarisxu
    $ cd polarisxu
    $ mkdir mypkg example
    $ cd mypkg
    $ go mod init github.com/polaris1119/mypkg
    $ touch bar.go
    $ cd ~/polarisxu/example
    $ go mod init github.com/polaris1119/example
    $ touch main.go
    ```
    
2. 在`mypkg`的`bar.go`中增加如下方法：

    ```go
    package mypkg
    
    func Bar() {
      println("This is package mypkg")
    }
    ```
    
3. 在`example`的`main.go`中增加如下代码：

    ```go
    package main
    
    import (
        "github.com/polaris1119/mypkg"
    )
    
    func main() {
        mypkg.Bar()
    }
    ```

4. 此时在`example`中运行`go mod tidy`就会报错，因为`mypkg`包尚未提交到 GitHub 中，无法找到该依赖的。而且此时执行`go run main.go`自然也是不成功的：

    ```shell
    $ go mod tidy
    ....
    fatal: repository 'https://github.com/polaris1119/mypkg/' not found
    ```

5. 虽然可以将`mypkg`提交到 GitHub 中，但是每修改一次就提交一次，很繁琐，而且一些验证性的代码也要进行提交推送，很低效。

    这个问题可以通过在`go.mod`中使用 replace 将依赖进行重定向到本地位置来解决：

    ```go
    module github.com/polaris1119/example
    
    go 1.19
    
    require github.com/polaris1119/mypkg v1.0.0
    
    replace github.com/polaris1119/mypkg => ../mypkg
    ```

    此时运行`go run main.go`就可以正常执行了：
    
    ```shell
    $ go run main.go
    This is package mypkg
    ```

6. 使用 replace 方式虽然可以解决本地开发依赖的问题，但是在开发完成之后需要删除 replace，并执行`go mod tidy`之后才能提交，否则就会导致别人使用时出错。

    针对这个问题，Go 中新增了 Workspace Mode 工作区模式。通过工作区模式可以避免手动修改 go.mod 文件，而是由 Go 自动的匹配工作区中的 module 来替换依赖。
    
    对于上述的代码，可以如下方式初始化工作区：
    ```shell
    $ cd ~/polarisxu
    $ go work init mypkg example
    $ tree
    .
    ├── example
    │   ├── go.mod
    │   └── main.go
    ├── go.work
    └── mypkg
        ├── bar.go
        └── go.mod
    ```

    此时在`~/polarisxu`中就生成了`go.work`文件，内容如下：
    
    ```text
    go 1.19

    use (
        ./example
        ./mypkg
    )
    ```
    
    此时将`example`的`go.mod`中的 replace 命令删除，也依旧能够正常运行（可以在`example`目录下，也可以在`polarisxu`目录下运行）。