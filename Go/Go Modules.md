> 转摘：[Go Modules 终极入门](https://mp.weixin.qq.com/s/6gJkSyGAFR0v6kow2uVklA)

Go Modules 是 Go 官方依赖管理方案，发布于 Go 1.11，正式于 Go 1.14。Go Modules 目前集成在 Go 工具链中，只要安装了 Go 就可以使用 Go modules 了。

Go modules 主要解决了在 Go 1.11 前的几个常见争议问题：

1. Go 语言长久以来的依赖管理问题；
2. 淘汰现有的 GOPATH 的使用模式；
3. 同意社区中的其他依赖管理工具（提供迁移功能）。

## 一、前言 GOPATH

### 1.1 GOPATH 是什么？

在 Go 的全局环境变量中有一个`GOPATH`变量，值为当前电脑上的一个路径：

```shell
$ go env | grep GOPATH
GOPATH="/Users/lin07ux/code/go"
```

在这个路径中可以看到三个子目录：

```shell
$ ls $GOPATH
bin pkg src
```

* `bin` 存储所编译生成的二进制文件
* `pkg` 存储预编译的模板文件，以加快程序的后续编译速度；
* `src` 存储所有`.go`文件或源代码。

在开发 Go 程序时，一般会以`$GOPATH/src/github.com/foo/bar`的路径进行组织和存放。因此在使用 GOPATH 模式时，需要将应用代码存放在固定的`$GOPATH/src/`目录下。并且如果执行`go get`来拉取外部依赖会自动下载并安装到`$GOPATH`目录中。

这种组织和存储 Go 代码的方式被称为 GOPATH 模式。

### 1.2 为什么弃用 GOPATH 模式？

GOPATH 模式没有版本控制的概念，这至少会造成以下问题：

- 在执行`go get`的时候，无法传达任何的版本信息的期望。也就是说无法知道当前更新的是那一个版本，业务方通过指定来拉取所期望的具体版本。
- 在运行 Go 程序的时候，无法保证其他人与自己所期望依赖的第三方库是相同的版本。也就是说在项目依赖库的管理上，无法保证所有人的依赖版本都一致。
- 没办法处理 v1、v2、v3 等不同版本的引用。因为 GOPATH 模式下的导入路径都是一样的，都是类似于`github.com/foo/bar`的格式。

所以 Go 语言官方开始推进 Go Modules（前身为 vgo），以解决上面这些问题。

### 1.3 其他版本解决方案

在 Go Modules 方案出来之前，社区中已经涌现了大量的依赖解决方案，包括很多人数值的 vendor 目录模式方案以及 dep 依赖管理工具。

这些方案虽然都能解决一部分维他奶，但是官方团队成员在深入讨论之后认为都不能很好的契合 Go 的功能和理念。所以官方就重新开启 proposal 的方式来推进新的解决方案，并逐步演化成了当前的 Go Modules。

因此，与其说之前的方案是在 GOPATH 模式下的产物，不如说是历史为当前提供了重要的教训。

## 二、Go Modules 使用

### 2.1 开启 Go Modules

在最新的 Go 版本中已经默认开启了 Go Modules 了。可以通过如下命令来确定是否已经开启：

```shell
$ go env | grep "GO111MODULE"
GO111MODULE="on"
```

如果`GO111MODULE`的值不是`on`，则需要修改它的值以启用 Go Modules：

```go
go env -w GO111MODULE=on
```

也可以通过直接设置系统环境变量（写入对应的`.bash_profile`文件即可）来实现这个目的：

```shell
export GO111MODULE=on
```

### 2.2 初始化

在完成 Go Modules 的开启后，就可以使用如下命令来初始化项目的 Go Modules 功能：

```shell
$ mkdir -p $HOME/eddycjy/module-repo
$ cd $HOME/eddycjy/module-repo
$ go mod init github.com/eddycjy/module-repo
go: creating new go.mod: module github.com/eddycjy/module-repo
```

这里使用`go mod init`命令的之后，指定了当前模块的导入路径为`github.com/eddycjy/module-repo`。

### 2.3 安装模块

安装目录可以使用`go get`命令：

```shell
$ go get github.com/eddycjy/mquote
go: finding github.com/eddycjy/mquote latest
go: downloading github.com/eddycjy/mquote v0.0.0-20200220041913-e066a990ce6f
go: extracting github.com/eddycjy/mquote v0.0.0-20200220041913-e066a990ce6f
```

安装过模块之后，`go.mod`和`go.sum`中就会有对应的模块信息出现了。

## 三、命令

在 Go Modules 中，能够使用如下命令进行操作：

  命令              |  作用
------------------ | ----------
 `go mod init`     | 初始化 Modules，生成 go.mod 文件
 `go mod download` | 下载 go.mod 文件中指明的所有依赖
 `go mod tidy`     | 整理现有的依赖
 `go mod graph`    | 查看现有的依赖结构
 `go mod edit`     | 编辑 go.mod 文件
 `go mod vendor`   | 导出项目所有的依赖到 vendor 目录
 `go mod verify`   | 校验一个模块是否被篡改过
 `go mod why`      | 查看为什么需要依赖某模块

> Go Modules 正在淡化 Vendor 的概念。

### 3.1 go get

`go get`命令主要用于拉取依赖，功能主要有：

* `go get` 拉取依赖，进行指定性拉取（更新），并不会更新所依赖的其他模块；
* `go get -u` 更新现有的依赖，会强制更新它所依赖的其他全部模块，不包括自身。
* `go get -u -t` 更新所有直接依赖和间接依赖的模块版本，包括单元测试中用到的。

拉取具体版本的方式如下：

* 拉取最新的版本(优先择取 tag)：`go get golang.org/x/text@latest`；
* 拉取`master`分支的最新 commit：`go get golang.org/x/text@master`；
* 拉取 tag 为 v0.3.2 的 commit：`go get golang.org/x/text@v0.3.2`；
* 拉取 hash 为 342b231 的 commit：`go get golang.org/x/text@342b231`；

在拉取项目依赖时，拉取过程总共分为了三大步：finding（发现）、downloading（下载）以及 extracting（提取）。并且在拉取的信息上一共分为了三段内容：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637485340707-af222255b1bd.jpg)

拉取版本的 commit 时间是以 UTC 时区为准，而并非本地时区。有时候，`go get`命令所拉取到的版本是`v0.0.0`，这是因为是直接执行`go get -u`获取的，并没有指定任何的版本信息，就由 Go Modules 自行按照内部规则进行选择。

`go get`命令在拉取时，对版本的选择方式如下：

1. 所拉取的模块有发布 tags：

    * 如果只有单个模块，那么就取主版本号最大的那个 tag；
    * 如果有多个模块，则推算相应的模块路径，获取主版本号最大的那个 tag（子模块的 tag 的模块路径会有前缀要求）。

2. 所拉取的模块没有发布过 tags：

    * 默认取主分支最新一次 commit 的 hash，此时得到的拉取信息的版本就是`v0.0.0`。

一个 Go 项目中可以包含一个或多个模块，而每个子模块中也会存在对应的 go.mod 文件，所以也可以为子模块指定对应的版本。

比如，项目有如下的结构：

```
mquote
├── go.mod
├── module
│   └── tour
│       ├── go.mod
│       └── tour.go
└── quote.go
```

在这个项目的跟目录有一个 go.mod 文件，在`module/tour`目录下也有一个 go.mod 文件。为这个项目分别打两个 tag：`v0.0.1`和`module/tour/v0.0.1`。此时其模块导入和版本信息的对应关系如下：

* `v0.0.1` 表示该项目的 v0.0.1 版本，导入路径为`github.com/eddycjy/mquote`，拉取命令为`go get github.com/eddycjy/mquote@v0.0.1`；
* `module/tour/v0.01` 表示该项目下的子模块`module/tour`的 v0.0.1 版本，导入路径为`github.com/eddycjy/mquote/module/tour`，拉取命令为`go get github.com/eddycjy/mquote/module/tour@v0.0.1`。

### 3.2 其他

* 全局安装：`go install pkg@version`。这是从 Go 1.16 开始推荐的全局安装方式。


## 四、环境变量

在 Go Modules 中有如下常用环境变量（可以使用`go env`命令来查看）：

```shell
$ go env
GO111MODULE="auto"
GOPROXY="https://proxy.golang.org,direct"
GONOPROXY=""
GOSUMDB="sum.golang.org"
GONOSUMDB=""
GOPRIVATE=""
...
```

### 4.1 GO111MODULE

这是一个阶段性的环境变量，代表着在 Go 1.11 版本中添加的，针对 Module 的变量。

Go 语言提供`GO111MOUDLE`这个环境变量来作为 Go Modules 的开关，其允许设置为以下参数：

* `auto` 只要项目包含了 go.mod 文件就启用 Go Modules，目前在 Go 1.11 至 Go 1.14 中仍然是默认值。
* `on` 启用 Go Modules，推荐设置，且在 Go 1.16 中已经默认开启。
* `off` 关闭 Go Modules，不推荐设置。

GO111MOUDLE 这个变量的值经历了从 auto 变为 on 的过程，后续还会将这个变量去掉，表示必须要使用 Go Modules。

### 4.2 GOPROXY

这个环境变量主要是用于设置 Go 模块代理（Go Module Proxy），其作用是用于使 Go 在后续拉取模块版本时能够脱离传统的 VCS 方式，直接通过镜像站点来快速拉取。

GOPROXY 的值是一个以英文逗号`,`分隔的 Go 模块代理列表，允许设置多个模块代理。如果不想使用代理，也可以将其设置为`off`，这将会禁止 Go 在后续操作中使用任何 Go 模块代理。

GOPROXY 的默认值是`https://proxy.golang.org,direct`，但是在国内是无法正常访问`proxy.golang.org`的，所以必须要在开启 Go Modules 的时候，同时设置国内的 Go 模块代理：

```shell
go env -w GOPROXY=https://goproxy.cn,direct
```

GOPROXY 值中的`direct`是一个特殊指示符，用于指示 Go 回源到模块版本的源地址（比如 GitHub）去抓取。

GOPROXY 的值的使用场景如下：当值列表中上一个 Go 模块代理返回 404 或 410 错误时，Go 自动尝试列表的下一个；遇见`direct`时回源，也就是回到源地址去抓取；遇见 EOF 时终止并抛出类似`invalid version: unknown revision...`的错误。

### 4.3 GOSUMDB

它的值是一个 Go checksum database，用于在拉取模块版本时（无论是从源站拉取还是通过 Go Module Proxy 拉取），保证拉取到的模块版本数据未经过篡改。若发现不一致，也就是可能存在篡改，将会立即终止。

GOSUMDB 的值支持如下格式：

* `<SUMDB_NAME>+<PUBLIC_KEY>`
* `<SUMDB_NAME>+<PUBLIC_KEY> <SUBDB_URL>`

也可以将其设置为`off`，也就是禁止 Go 在后续操作中校验模块版本。

GOSUMDB 的默认值为`sum.golang.org`，在国内也是无法访问的，但是 GOSUMDB 可以被 Go 模块代理所代理。可以通过设置 GOPROXY 来解决，比如`goproxy.cn`就支持代理`sum.golang.org`。所以这个问题在设置 GOPROXY 后可以不需要过度关心。

### 4.4 GONOPROXY/GONOSUMDB/GOPRIVATE

这三个环境变量都是用在当前项目依赖了私有模块（例如公司的私有 git 仓库，或者 GitHub 中的私有库），需要进行设置的，否则会拉取失败。

更细致来讲，就是依赖了由 GOPROXY 指定的 Go 模块代理或由 GOSUMDB 指定 Go checksum database 都无法访问到的模块时的场景。

这三个环境变量的值都是一个以英文逗号`,`分隔的模块路径前缀，也就是可以设置多个，例如：

```shell
go env -w GOPRIVATE="git.example.com,github.com/eddycjy/mquote"
```

这样设置后，前缀为`git.example.com`和`github.com/eddycjy/mquote`的模块都会被认为是私有模块。

也可以使用通配符来表示泛匹配：

```shell
go env -w GOPRIVATE="*.example.com"
```

这样表示所有模块路径为`example.com`的子域名（例如`git.example.com`）都将不经过 Go Module Proxy 和 Go checksum database，*需要注意的是，这个通配符是不包括`example.com`本身的*。

GOPRIVATE 的值将作为 GONOPROXY 和 GONOSUMDB 的默认值，所以建议的**最佳姿势是直接使用 GOPRIVATE。**

## 五、文件

Go Modules 会使用`go.mod`文件记录项目所需的依赖，并用`go.sum`对依赖进行依赖版本的验证。

### 5.1 go.mod

在初始化项目的 Go Modules 时，会生成一个 go.mod 文件，这个文件是启用了 Go Modules 所必须的最重要的标识，同时也是 GO111MODULE 值为 auto 时的重要识别标识。

go.mod 文件描述了当前项目（也就是当前模块）的元信息，主要记录了使用到的依赖以及关联依赖数据。

文件中每一行都以一个动词开头，目前有如下 5 个动词：

* `module` 用于定义当前项目的模块路径；
* `go` 用于设置预期的 Go 版本；
* `require` 用于设置一个特定的模块版本；
* `exclude` 用于从使用中排除一个特定的模块版本；
* `replace` 用于将一个模块版本替换为另一个模块版本。

另外，有些依赖的后面还会有一个`indirect`标识，表示该模块为间接依赖，也就是在当前应用中的`import`语句中并没有发现这个模块的明确引用，有可能是先使用`go get`拉取下来的，也有可能是被项目所依赖的模块依赖的，还有其他的可能。

例如：

```
module github.com/eddycjy/module-repo

go 1.13

require (
    example.com/apple v0.1.2
    example.com/banana v1.2.3
    example.com/banana/v2 v2.3.4
    example.com/pear // indirect
    example.com/strawberry // incompatible
)

exclude example.com/banana v1.2.4
replace example.com/apple v0.1.2 => example.com/fried v0.1.0
replace example.com/banana => example.com/fish
```

### 5.2 go.sum

在进行第一次拉取模块依赖之后，就会自动出现一个 go.sum 文件，其中详细罗列了当前项目直接或简介依赖的所有模块版本，并写明了那些模块版本的 SHA-256 哈希值，以备 Go 在今后的操作中保证项目所依赖的那些模块版本不会被篡改。

例如：

```
github.com/eddycjy/mquote v0.0.1 h1:4QHXKo7J8a6J/k8UA6CiHhswJQs0sm2foAQQUq8GFHM=
github.com/eddycjy/mquote v0.0.1/go.mod h1:ZtlkDs7Mriynl7wsDQ4cU23okEtVYqHwl7F1eDh4qPg=
github.com/eddycjy/mquote/module/tour v0.0.1 h1:cc+pgV0LnR8Fhou0zNHughT7IbSnLvfUZ+X3fvshrv8=
github.com/eddycjy/mquote/module/tour v0.0.1/go.mod h1:8uL1FOiQJZ4/1hzqQ5mv4Sm7nJcwYu41F3nZmkiWx5I=
...
```

哈希值分为两种：

* 模块哈希：是 Go Modules 将目标模块版本的 zip 文件开包后，针对所有包内文件一次进行哈希，然后再把它们的哈希结果按照固定格式和算法组成总的哈希值；
* go.mod 哈希：该值必然存在，是将 go.mod 进行哈希计算得到的值。

模块哈希可能不存在：当 Go 认为肯定用不到某个模块版本的时候就会省略它的模块哈希，但是此时也依旧存在 go.mod 哈希。

### 5.3 全局缓存

在将依赖的模块拉取下来的时候，拉取的结果会缓存在`$GOPATH/pkg/mod`和`$GOPATH/pkg/sumdb`目录下，在`$GOPATH/pkg/mod`目录下会以包的前缀格式（如`github.com/foo/bar`）进行存放。

如下：

```
mod
├── cache
├── github.com
├── golang.org
├── google.golang.org
├── gopkg.in
...
```

需要注意的是，同一个模块版本的数据之缓存一份，所有其他模块共享使用。

如果希望清理所有已缓存的模块版本数据，可以执行`go clean -modcache`命令。

## 六、其他

### 6.1 Go Modules 的语义化版本控制

Go Modules 采用语义化版本声明，其格式为`主版本号.此版本号.修订号`。如果是先行版本号或特殊情况，可以将版本信息追加到声明后面，作为延伸：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637486974111-8bd995bc20b9.jpg)

* 主版本号：做了不兼容的 API 修改；
* 此版本号：做了向下兼容的功能性新增；
* 修订号：做了向下兼容的问题修正。

在发布 Go 模块的版本的时候，应按照这个格式打 tag，否则可能会导致无法进行拉取。

### 6.2 Go Modules 的最小版本选择

Go 的模块可以依赖其他的模块，而所依赖的模块也可以继续依赖别的模块，这就导致可能会出现不同依赖的模块依赖了同一个模块的不同版本。

比如：模块 A 依赖了模块 B 和模块 C，而模块 B 依赖了模块 D，模块 C 依赖了模块 D 和 F，模块 D 又依赖了模块 E。而且同模块的不同版本还依赖了对应模块的不同版本。

这个时候 Go Modules 会把每个模块的依赖版本清单都整理出来，最终得到一个构建清单，如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637487226895-d5e5d31f8e6d.jpg)

这里出现了 rough list 和 final list，两者的区别在于重复引用的模块 D（v1.3 和 v1.4），最终清单中选用了模块 D 的 v1.4 版本，主要原因：

1. 语义化版本的控制：因为模块 D 的 v1.3 和 v1.4 版本变更，都属于次版本号的变更，而在语义化版本的约束下，v1.4 必须是要向下兼容 v1.3 版本的，因此认为不存在破坏性的变更。

2. 模块导入路径的规范：主版本号不同，模块的导入路径不一样。因此若出现不兼容的情况，其主版本号会改变，模块的导入路径自然也就改变了，因此不会与第一点的基础相冲突。

### 6.3 go.sum 文件要不要提交

理论上 go.mod 和 go.sum 文件都应该提交到 Git 仓库中。

如果不上传 go.sum 文件，就会造成每个人执行 Go Modules 相关指令时，会生成新的一份 go.sum 文件，也就是会重新到上游拉取。而重新拉取的时候，可能就会得到被修改过的代码了，这存在很大的安全隐患，失去了与基准版本（第一个提交的人所期望的版本）的校验内容，因此 go.sum 文件是需要提交的。


