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

