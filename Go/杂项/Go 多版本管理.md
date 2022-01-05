> [简洁的 Go 多版本管理机制](https://mp.weixin.qq.com/s/P2LrviH2IVMCHo52H0BUBw)

## 一、多版本

### 1.1 安装

在进行开发或学习的时候，常需要切换使用不同的 Go 版本。此时可以使用 Go 版本包装器来进行管理。

需要两个先决条件：

* 已经安装好了某版本的 Go；
* 安装好了 Git。

然后就可以使用如下的命令来安装特定版本的 Go 包装器：

```shell
go install golang.org/dl/go<version>[@<label>]
```

再使用包安装器来安装特定的版本的 Go：

```shell
go<version> download
```

比如，安装`1.14.12`版本，可以这样执行：

```shell
go install golang.org/dl/go1.14.12@latest
go1.14.12 download
```

### 1.2 使用

安装好特定的版本之后，就可以继续使用`go<version>`来指定指定命令的 Go 版本。

比如，安装好`1.14.12`版本之后，就可以使用如下的命令来使用 Go 1.14.12 版本进行构建和测试：

```shell
$ go1.14.12 mod init hello
go: creating new go.mod: module hello

$ echo 'package main; import "fmt"; func main() { fmt.Println("Hello, World") }' > hello.go

$ go1.14.12 build

$ ./hello
Hello, World
```

### 1.3 其他操作

如果想**让安装的 Go 版本成为默认版本**，可以修改系统变量`GOROOT`和`PATH`：

```shell
$ go version
go version go1.17 darwin/amd64

$ export GOROOT=$(go1.14.12 env GOROOT)
$ export PATH=${GOROOT}/bin:$PATH

$ go version
go version go1.14.12 darwin/amd64
```

这里`go1.14.12 env GOROOT`指向的路径就是 Go 1.14.12 版本的内容，可以直接查看该路径下的`src/`来阅读该版本的源码。

如果要**删除这个版本**，只需要将`go1.14.12 env GOROOT`指向的路径删除即可。

这里有个特别的版本标记：**`gotip`，用于安装最新的开发版本**：

```go
$ go install golang.org/dl/gotip@latest
$ gotip download
```

## 二、实现思路

实现多版本下载安装的秘诀就在于[Go 仓库](https://go.googlesource.com/dl)，其在 GitHub 上还有一个镜像仓库[Go GitHub](https://github.com/golang/dl)。

### 2.1 仓库结构

查看改仓库代码，可以看到一系列的版本目录：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641385508101-79c81daab42b.jpg)

随意选择一个版本进入，会发现存在一个`main.go`文件，内容如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641385582495-0aae65724b5b.jpg)

通过`go install golang.org/dl/go1.14.12@latest`下载的 go1.14.12 包装器就是这个`main.go`文件编译而成的。

### 2.2 代码分析

`main.go`中的代码只有`version.Run("go1.14.12")`，所以在执行`go1.14.12`命令的时候，就是走入到了 [golang.org/dl/internal/version](https://go.googlesource.com/dl/+/refs/heads/master/internal/version/version.go) 中的`Run`方法了：

```go
// Run runs the "go" tool of the provided Go version
func Run(version string) {
  log.SetFlags(0)
  
  // 获取 Go 安装目录
  root, err := goroot(version)
  if err != nil {
    log.Fatalf("%s: %v", version, err)
  }
  
  // 执行 go<version> download 命令时的逻辑
  if len(os.Args) == 2 && os.Args[1] == "download" {
    if err := install(root, version); err != nil {
      log.Fatalf("%s: download failed: %v", version, err)
    }
    os.Exit(0)
  }
  
  // 判断该版本的 Go 安装状态
  if _, err := os.Stat(filepath.Join(root, unpackedOkay)); err != nil {
    log.Fatalf("%s: not downloaded. Run '%s download' to install to %v", version, version, root)
  }
  
  // 运行该版本的 Go
  runGo(root)
}
```

这个方法中会调用`install`方法来下载 Go 版本的相关文件并进行对应版本的安装，使用`runGo`方法来执行其他的命令。

### 2.3 其他

为了让每个版本都有一个 Go 包装器主程序，而且避免重复的手工操作，这里使用了一个帮助命令`genv`：可以快速生成对应版本的包装器代码`<version>/main.go`文件。

这个命令的实现可以查看 [golang.org/dl/internal/genv/main.go](https://go.googlesource.com/dl/+/refs/heads/master/internal/genv/main.go) 文件中的代码。


