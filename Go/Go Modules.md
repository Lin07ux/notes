Go Modules 是 Go 官方依赖管理工具，用于处理 Go 项目的依赖包。

## 一、命令

Go Modules 有一些相关的操作命令，主要使用的有如下两个命令进行操作：`go mod`、`go get`。

### 1.1 go mod

该命令主要用于管理项目的依赖。主要用处如下：

* 初始化(生成)`go.mod`文件：`go mod init`；
* 下载`go.mod`文件中指明的所有依赖：`go mod download`；
* 整理现有的依赖：`go mod tidy`；
* 编辑`go.mod`文件：`go mod edit`；
* 查看现有的依赖结构：`go mod graph`；
* 校验一个模块是否被篡改过：`go mod verify`；
* 导出现有的所有依赖：`go mod vendor`。

> Go Modules 正在淡化 Vendor 的概念。

### 1.2 go get

该命令主要用于拉取依赖。主要用处如下：

* 拉取最新的版本(优先择取 tag)：`go get golang.org/x/text@latest`；
* 拉取`master`分支的最新 commit：`go get golang.org/x/text@master`；
* 拉取 tag 为 v0.3.2 的 commit：`go get golang.org/x/text@v0.3.2`；
* 拉取 hash 为 342b231 的 commit：`go get golang.org/x/text@342b231`；
* 更新现有依赖：`go get -u`；

### 1.3 其他

* 全局安装：`go install pkg@version`。这是从 Go 1.16 开始推荐的全局安装方式。

## 二、文件

Go Modules 会使用`go.mod`文件记录项目所需的依赖，并用`go.sum`对依赖进行依赖版本的验证。

### 2.1 go.mod

该文件中描述了当前项目（也就是当前模块）的原信息，主要记录了使用到的依赖以及关联依赖信息。

文件中每一行都以一个动词开头，目前有如下 5 个动词：

* `module` 用于定义当前项目的模块路径；
* `go` 用于设置预期的 Go 版本；
* `require` 用于设置一个特定的模块版本；
* `exclude` 用于从使用中排除一个特定的模块版本；
* `replace` 用于将一个模块版本替换为另一个模块版本。

### 2.2 go.sum

该文件中的每一行都是一个依赖的信息，以及其 SHA-256 哈希值。

记录依赖的哈希值主要用于 Go 在之后的操作中保证项目所依赖的那些模块版本不被篡改。

## 三、问题

### 3.1 go.mod file not found in current directory

强制启用了 Go Modules 机制（即环境变量中设置了`GO111MODULE=on`）后，需要先初始化模块：

```shell
go mod init
```

