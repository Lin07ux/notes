> 转摘：[Go 如何利用 Linux 内核的负载均衡能力](https://mp.weixin.qq.com/s/_unz4kGQAKGAWUPaygXoOg)

**默认情况下，操作系统不允许多个进程监听监听具有相同的源地址和端口的套接字 socket**。比如，当重复监听同一个端口时，会有类似如下的报错：

```
listen tcp :8080: bind: address already in use
```

不过如果要开启多个服务进程监听同一个端口，其实也是可以的，这就涉及到操作系统内核的选项了。

### 1. socket 五元组

socket连接是通过五元组进行唯一标识的，任意两个连接，它们的五元组不能完全相同：

```
{<protocol>, <src addr>, <src port>, <dest addr>, <dest port>}
```

`<protocol>`指的是传输层 TCP/UDP 协议，在 socket 被创建的时候就已经确定了。`src addr/port`与`dest addr/port`分别标识着请求方与服务方的地址信息。

因此，只要请求方的`dest addr/port`信息不同，那么服务方即使使用同样的`src addr/port`，依然可以唯一的标识 socket 连接。

基于这个理论基础，就可以在同一个网络主机上复用相同的 IP 地址和端口号。

### 2. Linux SO_REUSEPORT

为了满足端口复用的需求，Linux 3.9 内核引入了`SO_REUSEPORT`选项，它支持多个进程或线程绑定到同一个端口，用于提高服务器程序的性能。

> 实际上，在此之前有一个类似的选项`SO_REUSEADDR`，但它没有做到真正的端口复用。

`SO_REUSEPORT`具有如下特性：

1. 允许多个套接字 bind 同一个 TCP/UDP 端口：

    - 每个线程拥有自己的服务器套接字；
    - 在服务器套接字上没有了锁的竞争。

2. 内核层面实现负载均衡。
3. 安全层面，监听同一个端口的套接字只能位于同一个用户下。

有了`SO_REUSEPORT`之后，每个进程可以 bind 相同的地址和端口，而且各自是独立平等的。

多个进程/线程虽然监听同一个端口，但是每一个进程/线程中`accept socket fd`的时候，监听的`fd`是不一样的。有新的连接建立时，内核只会调度一个进程来`accept`，并且保证调度的均衡性。也就是说，**通过端口复用，可以得到一种内核模式下的负载均衡能力。**

工作示意图如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1636001062626-4fcfbf4d0e17.jpg)

### 3. Go 设置 SO_REUSEPORT

在 Go 中要利用 Linux 的`SO_REUSEPORT`选项，就需要调用修改内核 socket 连接选项的接口，可以通过下面的方式来实现：

```go
import "golang.org/x/sys/unix"

unix.SetsockoptInt(int(fd), unix.SOL_SOCKET, unix.SO_REUSEPORT, 1)
```

一个持有`SO_REUSEPORT`特性的完整 Go 服务代码如下：

```go
package main

import (
  "context"
  "fmt"
  "net"
  "net/http"
  "os"
  "syscall"
  
  "golang.org/x/sys/unix"
)

var lc = net.ListenConfig{
  Control: func(network, address string, c syscall.RawConn) error {
    var opErr error
    err := c.Control(func(fd uintptr) {
      opErr = unix.SetsockoptInt(int(fd), unix.SOL_SOCKET, unix.SO_REUSEPORT, 1)
    }
    if err != nil {
      return err
    }
    return opErr
  },
}

func main() {
  pid := os.Getpid()
  l, err := lc.Listen(context.Background(), "tcp", ":8080")
  if err != nil {
    panic(err)
  }
  server := &http.Server{}
  http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
    w.WriteHeader(http.StatusOK)
    fmt.Fprintf(w, "Client [%s] Received msg from Server PID: [%d] \n", r.RemoteAddr, pid))
  })
  fmt.Printf("Server with PID: [%d] is running\n", pid)
  _ = server.Serve(l)
}
```

然后可以将其编译为 Linux 可执行文件：

```shell
CGO_ENABLED=0 GOOS=linux GOARCH=arm64 go build main.go
```

然后就可以在 Linux 主机上开启多个同时监听 8080 端口的进程了，输出类似如下：

```shell
$ ./main
Server with PID: [103] is running
$ ./main
Server with PID: [114] is running
$ ./main
Server with PID: [125] is running
```

然后再通过 curl 命令模拟多次 http 客户端请求：

```shell
$ for i in {1..20}; do curl localhost:8080; done
Client [127.0.0.1:48976] received msg from Server PID: [125]
Client [127.0.0.1:48978] received msg from Server PID: [114]
Client [127.0.0.1:48980] received msg from Server PID: [103]
Client [127.0.0.1:48982] received msg from Server PID: [114]
Client [127.0.0.1:48984] received msg from Server PID: [125]
Client [127.0.0.1:48986] received msg from Server PID: [103]
Client [127.0.0.1:48988] received msg from Server PID: [114]
Client [127.0.0.1:48990] received msg from Server PID: [103]
Client [127.0.0.1:48992] received msg from Server PID: [114]
Client [127.0.0.1:48994] received msg from Server PID: [125]
Client [127.0.0.1:48996] received msg from Server PID: [103]
Client [127.0.0.1:48998] received msg from Server PID: [114]
Client [127.0.0.1:49000] received msg from Server PID: [125]
Client [127.0.0.1:49002] received msg from Server PID: [114]
Client [127.0.0.1:49004] received msg from Server PID: [103]
Client [127.0.0.1:49006] received msg from Server PID: [125]
Client [127.0.0.1:48978] received msg from Server PID: [114]
Client [127.0.0.1:48980] received msg from Server PID: [103]
Client [127.0.0.1:48982] received msg from Server PID: [114]
Client [127.0.0.1:48984] received msg from Server PID: [125]
```

