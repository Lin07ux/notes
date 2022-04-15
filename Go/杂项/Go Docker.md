### 1. 导入私有仓库的认证问题

> [在docker环境导入私有仓库的问题](https://mp.weixin.qq.com/s/iHX6ty2XDUwxF-G_aWDEQw)

在 GitLab 中使用 gitlab ci 来发布时，因为引入了私有仓库的依赖，导致在 build 容器的时候报错：

```
fatal: could not read Username for ‘https://git.domain.com’: terminal prompts disabled
```

这里的`git.domain.com`是一个私有仓库，报错内容也显示是因为无法正确的读取用户姓名而无法通过验证。

虽然可以通过共享 [GitLab SSH Key](https://vsupalov.com/build-docker-image-clone-private-repo-ssh-key/) 的方式来通过私有仓库的认证，但还有更简单的方式：在 ci 中使用`go mod vendor`命令。

因为 GitLab runner 本身已经缓存了 Git 认证信息，它可以访问所有的私有仓库。当执行`go mod vendor`后，项目依赖就都被放到了 vendor 目录里了。接下来当执行到 Dockerfile 的 COPY 指令时，项目依赖就被自然而然的拷贝到了容器里，从而不用再联网执行 Git 下载了。

对应的`.gitlab-ci.yaml`配置示例如下：

```yaml
build_job:
  stage: build
  script:
    - go mod vendor
    - make docker-build
```

### 2. 有效设置 GOMAXPROCS

> 转摘：[在 Go 容器里设置 GOMAXPROCS 的正确姿势](https://mp.weixin.qq.com/s/MWe5EsAYpU7F-FuXrbfFYA)

GOMAXPROCS 是 Go 提供的一个非常重要的环境变量。通过设定这个环境变量，用户可以调整调度器中 Processor（简称 P）的数量。

每个系统线程必须要绑定 P，才能够把 G 交给 M 执行，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635222029714-3638b935ab2e.jpg)

所以 P 的数量会很大程度上影响 Go runtime 的并发表现。在 Go 1.5 版本后，GOMAXPROCS 的默认值是机器的 CPU 核数。下面的代码可以获取当前机器的核心数和 GOMAXPROCS 的值：

```go
runtime.NumCPU()  // 获取机器的 CPU 核心数
runtime.GOMAXPROCS(0) // 参数为零时用于获取给 GOMAXPROCS 设置的值
```

**设置 GOMAXPROCS 高于真正可使用的核心数，会导致 Go 调度器不停的进行 OS 线程切换，从而给调度器增加很多不必要的工作。**

而以 Docker 为代表的容器虚拟化技术，会通过 cgroup 等技术对 CPU 资源进行隔离。比如，下面这个 PodTemplate 的容器的定义里，`limits.cpu = 1000m`就代表给这个容器分配 1 个核心的使用时间：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635222288797-83ae2179f55f.jpg)

这类技术对 CPU 的隔离限制，导致`runtime.NumCPU()`无法正确获取到容器被分配的 CPU 资源数，得到的依旧是宿主机的核心数。这样导致 Go 在容器中并不能真正的展现其并发优势。

目前 Go 官方并无好的方式来规避在容器里获取不到真正可使用的核心数这一问题，而 Uber 提出了一种 Workaround 方法，利用 [uber-go/automaxprocs](https://github.com/uber-go/automaxprocs) 这个包，可以在运行时根据 cgroup 为容器分配的 CPU 资源限制数来修改 GOMAXPROCS 的值（针对 Linux 系统）：

```go
import _ "go.uber.org/automaxprocs"

func main() {
  // Your application logic here.
}
```

### 3. Docker 编译

> 转摘：[构建 Go 应用 docker 镜像的几种姿势](https://mp.weixin.qq.com/s/LJ5mECUh-jRcBlVZ98q61A)

Go 程序需要进行编译后才可运行，而编译环境和运行环境的要求不同：编译环境需要较多的依赖，而运行环境只需要简单的操作系统即可。所以为了减少 Go 程序容器的尺寸，可以通过多阶段编译，将编译环境和运行环境区分，并选择 alpine 镜像作为运行镜像。

简单的 Dockerfile 文件如下所示：

```yaml
FROM golang:alpine AS builder

WORKDIR /build
ADD go.mod .
COPY . .
RUN go build -o hello hello.go

FROM alpine

WORKDIR /build
COPY --from=builder /builder/hello /build/hello
CMD ["./hello"]
```

其中，第一个`FROM`部分是作为构建镜像，在其中编译出可执行文件 hello，然后将其拷贝到第二个`FROM`定义的运行镜像中，并在运行镜像中执行。

也可以使用安装并使用 go-zero 中的`goctl docker`（[文档](https://go-zero.dev/en/goctl-other.html)）来自动生成这样的 Dockerfile。首先安装该工具：

```shell
# 安装 goctl 工具
GOPROXY=https://goproxy.cn/,direct go install github.com/zeromicro/go-zero/tools/goctl@latest
# 一键编写 Dockerfile
goctl docker -go hello.go
```

其生成的 Dockerfile 如下：

```yaml
FROM golang:alpine AS builder

LABEL stage=gobuilder

ENV CGO_ENABLED 0 # 禁用 cgo
ENV GOOS linux # linux 环境
ENV GOPROXY https://goproxy.cn,direct # 启用 GOPROXY

WORKDIR /build

ADD go.mod .
ADD go.sum .
RUN go mod download
COPY . .
RUN go build -ldflags="-s -w" -o /app/hello ./hello.go # 去掉调试信息


FROM alpine

# 运行环境中安装 ca-certificates 和 tzdata，以使用 TLS 证书，自动设置本地时区
RUN apk update --no-cache && apk add --no-cache ca-certificates tzdata
ENV TZ Asia/Shanghai

WORKDIR /app
COPY --from=builder /app/hello /app/hello

CMD ["./hello"]
```

