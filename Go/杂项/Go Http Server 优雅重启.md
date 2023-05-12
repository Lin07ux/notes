> 参考：
> 
> 1. [优雅地关机或重启](https://www.liwenzhou.com/posts/Go/graceful-shutdown/)
> 2. [go web应用的部署和热更新](https://mp.weixin.qq.com/s/M0zKN5u5ukfF_cl7R6Bq4Q)
> 3. [Gin如何使用endless平滑重启？](http://www.go-day.cn/detail/4.html)
> 4. [GoLang: Zero downtime deploys and Rollbacks | Go HTTP server−Echo Web Framework & Apache](https://medium.com/web-developer/golang%25E3%2583%25BCzero-downtime-deploys-and-rollbacks-go-http-server%25E3%2583%25BCecho-web-framework-apache-12a9a21bfc25)
> 5. [endless 如何实现不停机重启 Go 程序？](https://www.luozhiyun.com/archives/584)

生产环境中，Go HTTP 服务在日常迭代升级的部署中需要做到零下线，以避免影响服务的可用性。此时就需要使用一些工具，并使开发的 HTTP 服务也能支持优雅重启，从而实现如下的目标：

1. 服务重启不需要关闭监听的端口（还能继续接入请求）
2. 既有请求应当能继续处理完成或超时（不中断已有请求）

## 一、基础知识

为了实现 Go HTTP 服务零下线的重启/升级，需要了解如下的基础知识。

### 1. Linux Fork

Linux 中可以通过 Fork 方式创建子进程，而且 Fork 出来的子进程可以共享父进程已持有的资源，如文件、Socket等。

Go 中的`exec`标准库中已经封装好了 Fork 系统调用，并且可以通过`ExtraFiles`能够很好的继承父进程中一打开的文件，而监听的文件也能由`netListener.File()`通过系统调用`dup`来实现复制。

`dup`系统调用的描述如下：

> dup and dup2 create a copy of the file descriptor oldfd.
> After successful return of dup or dup2, the old and new descriptors may
be used interchangeably. They share locks, file position pointers and
flags; for example, if the file position is modified by using lseek on
one of the descriptors, the position is also changed for the other.
> 
> The two descriptors do not share the close-on-exec flag, however.

通过这段说明可知：复制后，返回的新文件描述符和被复制的文件描述符指向的是同一个文件，共享所有的属性、读写指针、权限和标志位。不过它们不共享关闭标志位，也就是说即便被复制的文件描述符已经关闭了，新文件描述符也能继续读写数据。

下图描述了 fork 一个子进程后，子进程复制了父进程的文件描述符表：

![](https://cnd.qiniu.lin07ux.cn/markdown/1683870301-d1ec2f2c3e0216334c39a6745676edaf.png)

### 1.2 Signal

Linux 系统提供了用户向正在执行的进程发送信号的方式，而且进程在捕获这些信号后能够做一些自定义处理，比如：重启、资源释放、终止等。

Go 语言也支持支持信号，从而使得正在运行的 Go 程序能响应用户的信号。Go 语言使用`os.Signal`和`signal.Notify`来实现信号注册和监听。

比如，下面的代码可以使的 Go 程序监听 SIGINT 和 SIGTERM 信号：

```go
func main() {
  sigs := make(chan os.Signal, 1)
  done := make(chan bool, 1)
  
  // 监听信号
  signal.Notify(sigs, syscall.SIGINT, syscall.SIGTERM)
  go func() {
    // 接收信号
    sig := <-sigs
    fmt.Println()
    fmt.Println(sig)
    done <- true
  }()
  
  // 等待信号
  fmt.Println("awaiting signal")
  <-done
  fmt.Println("exiting")
}
```

## 二、endless

### 2.1 使用

endless 支持不停机的运行 Go HTTP 服务，使用方式很简单，就是构建好 HTTP 处理器后，由 endless 来启动服务。以下是一个简单的示例：

```go
import (
    "log"
    "net/http"
    "os"
    "sync"
    "time"

    "github.com/fvbock/endless"
    "github.com/gorilla/mux"
)

func handler(w http.ResponseWriter, r *http.Request) {
    duration, err := time.ParseDuration(r.FormValue("duration"))
    if err != nil {
        http.Error(w, err.Error(), 400)
        return
    }
    time.Sleep(duration)
    w.Write([]byte("Hello World"))
}

func main() {
    mux1 := mux.NewRouter()
    mux1.HandleFunc("/sleep", handler)

    w := sync.WaitGroup{}
    w.Add(1)
    go func() {
        err := endless.ListenAndServe("127.0.0.1:5003", mux1)
        if err != nil {
            log.Println(err)
        }
        log.Println("Server on 5003 stopped")
        w.Done()
    }()
    w.Wait()
    log.Println("All servers stopped. Exiting.")

    os.Exit(0)
}
```

然后就可以通过如下操作来进行不停机验证：

```shell
# 第一次构建项目
go build main.go
# 运行项目，这时就可以做内容修改了
./main &
# 请求项目，60s后返回
curl "http://127.0.0.1:5003/sleep?duration=60s" &
# 再次构建项目，这里是新内容
go build main.go
# 重启，84636为pid
kill -HUP 84636
# 新API请求
curl "http://127.0.0.1:5003/sleep?duration=1s" 
```

运行上面的操作可以看到：

* 第一个请求在 60s 之后才会返回，而且返回的是修改前的内容`Hello world`
* 第二个请求在 2s 之后就返回了（比第一个请求更早返回），返回的是修改后的内容`Hello world2222`。

在重启之后，第一个请求返回之前，可以看到有两个进程在跑：

```shell
$ ps -ef | grep main
root      84636  80539  0 22:25 pts/2    00:00:00 ./main
root      85423  84636  0 22:26 pts/2    00:00:00 ./main
```

在第一个请求返回之后，就只剩下一个进程了：

```shell
$ ps -ef |grep main
root      85423      1  0 22:26 pts/2    00:00:00 ./main
```

### 2.2 原理

endless 的实现原理就是前面提到的 Linux Fork、Signal 的知识，流程如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1683872151-b986e314f9b26d6685a2a9653e933478.png)

1. endless 监听 SIGHUP 信号；
2. 收到 SIGHUP 信号时 fork 得到子进程（使用相同的启动命令），将服务监听的 Socket 文件描述符传递给子进程；
3. 子进程监听父进程的 Socket，此时父进程和子进程都可以接收请求；
4. 子进程启动成功之后给父进程发送 SIGTERM 信号，父进程接收到该信号之后停止接收新的链接；
5. 父进程等待旧链接处理完成或者超时后就退出；
6. 完成服务升级重启。

## 三、不停机部署

借助于 endless 能够实现 Go HTTP 服务的不停机升级，在部署的时候可以优化相关的流程，使其更方便的完成自动化部署。

### 3.1 ln 文件链接

Linux 中的`ln`命令可以为文件/文件夹创建一个软连接，相当于一个快捷方式。而且创建快捷方式的时候，如果当前已经存在该名称，可以先删除已存在的文件，然后再创建链接。

当程序运行起来之后，其存在于硬盘上的程序包发生改变并不影响当前运行的状态。

### 3.2 部署

在生产环境中，一般会使用 Nginx/Apache 等做 Go HTTP 服务的代理和负载均衡，在部署的时候也要对这些代理进行重启。

1. 构建并部署 v1 版本：

    ```shell
    # 构建
    go build -o releases/1/main
    
    # 软链
    ln -snf releases/1/main current_main
    
    # 启动
    ./current_main
    
    # nginx
    nginx -s reload
    ```

2. 热更新 v2 版本：

    ```shell
    # 构建
    go build -o releases/2/main
    
    # 软链
    ln -snf releases/2/main current_main
    
    # 重启
    kill -HUP <main.pid>
    
    # nginx
    nginx -s reload
    ```

3. 回滚版本

    ```shell
    # 软链
    ln -snf releases/1/main current_main
    
    # 重启
    kill -HUP <current_main.pid>
    
    # nginx
    nginx -s reload
    ```

这里`kill`命令中的`-HUP`参数即表示发送 SIGHUP 信号给`current_main`启动的进程。

`current_main`的进程 ID 号一般会写入到文件中，这样就能使用如下方式完成重启：

```shell
kill -HUP `cat current_main.pid`
```