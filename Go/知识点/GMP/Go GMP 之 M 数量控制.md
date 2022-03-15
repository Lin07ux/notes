> 转摘：
> 
> 1. [如何有效控制 Go 线程数？](https://mp.weixin.qq.com/s/HYcHfKScBlYCD0IUd0t4jA)
> 2. [Go 群友提问：Goroutine 数量控制在多少合适，会影响 GC 和调度？](https://mp.weixin.qq.com/s/uWP2X6iFu7BtwjIv5H55vw)

Go 对运行时创建的线程数量有一个限制，最大值默认是 10000。如果运行时总的线程数量达到了这个限制，程序就会挂掉。

### 1. GOMAXPROCS 和最大线程数量限制

Go 使用 GMP 模型进行协程调度，每个 P 都会有一个操作系统线程 M 来执行其上的 G，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635234331060-c0a894e8def8.jpg)

P 的最大数量可以通过 GOMAXPROCS 来设定。通过 GOMAXPROCS 的定义文档，可以看到，该变量只是限制了可以同时执行用户级 Go 代码的 OS 系统线程数量：

> The GOMAXPROCS variable limits the number of operating system threads that can execute user-level Go code simultaneously. There is no limit to the number of threads that can be blocked in system calls on behalf of Go code; those do note count against the GOMAXPROCS limit. This package's GOMAXPROCS function queries and changes the limit.

通俗的说：**Go 程序最多只能有 GOMAXPROCS 个系统线程同时运行。但是，在系统调用中被阻塞的线程不在此限制之中。**

### 2. 同步阻塞与空闲线程

系统调用可分为同步和异步两种方式：

* Go 网络编程模型就是一种异步系统调用，它使用网络轮序器进行系统调用，而调度器可以防止 G 在进行这些系统调用时阻塞 M，让 M 继续执行其他的 G 而不需要创建新的 M。

* 如果 G 要进行的是无法以异步完成的系统调用时，进行系统调用的 G 将会阻塞 M。在 Linux 下基于普通文件（Linux 下 epoll 只支持 socket，Windows 下的 iocp 可以支持 socket、file）的系统调用就是一个典型的例子。

如下图，运行在 M1 上的 G1 想要请求一个同步系统调用：

![同步系统调用 1](http://cnd.qiniu.lin07ux.cn/markdown/1635235181242-dc0f72057a05.jpg)

当发生同步系统调用并阻塞时，调度器将 M1 和仍然挂载在其之上的 G1 与 P 分离，并引入新的 M2 来运行 P 上其他的 G：

![同步系统调用 2](http://cnd.qiniu.lin07ux.cn/markdown/1635235331589-87c91fd19bbe.jpg)

当 G1 进行的阻塞式系统调用结束时，G1 重新回到 P 的 LRQ 队列中去，**但 M1 变成了闲置线程，不会被回收，而是留备后续复用**：

![同步系统调用 3](http://cnd.qiniu.lin07ux.cn/markdown/1635235355744-8a1b09efef69.jpg)

这样就存在一个问题了：如果在短时间内，Go 程序存在大量无法短暂结束的同步系统调用，那线程数岂不是会一直涨下去？

### 3. 最大线程数限制

线程数限制的问题，在官方 [issues#4056: "runtime: limit number of operating system threads"](https://github.com/golang/go/issues/4056) 中有过讨论，并最终将线程限制数值确定为 10000。

这个值存在的主要目的是：**限制可以创建无限数量线程的 Go 程序，在程序把操作系统干爆之前，干掉程序。**

当然，Go 也暴露了`debug.SetMaxThreads()`方法，允许用户修改最大线程数值：

```go
package main

import (
  "os/exec"
  "runtime/debug"
  "time"
)

func main() {
  debug.SetMaxThreads(10)

  for i := 0; i < 20; i++ {
    go func() {
      _, err := exec.Command("bash", "-c", "sleep 3").Output()
      if err != nil {
        panic(err)
      }
    }()
  }
  time.Sleep(time.Second * 5)
}
```

如程序所示，将最大线程数设置为 10，然后通过执行 shell 命令`sleep 3`来模拟同步系统调用过程。那么，执行`sleep`操作的 G 和 M 都会被同步阻塞，从而造成 M 和 P 的分离。当程序启动的线程 M 超过 10 个时，会得到类似如下的报错：

```shell
runtime: program exceeds 10-thread limit
fatal error: thread exhaustion
...
```

### 4. 解决方法：让闲置线程退出

闲置线程退出的问题，在官方 [issues#14592: "runtime: let idle OS threads exit"](https://github.com/golang/go/issues/14592) 中有过讨论，但是目前还没有一个完美的解决方案。

不过在这个 issue 中，有人提出了使用`runtime.LockOSThread()`方法来杀死线程。

简单了解下这个函数的特性：

1. 调用`LockOSThread()`函数会把当前 G 绑定在当前的系统线程 M 上，这个 G 总是在这个 M 上执行，并且阻止其他 G 在该 M 上执行。也就是让当前 G 独占当前 M。
2. 只有当前 G 调用了与之前调用`LockOSThread()`相同次数的`UnlockOSThread()`函数之后，G 与 M 才会解绑，释放对 M 的独占。
3. 如果当前 G 在退出时，调用`UnlockOSThread()`函数的次数少于其调用`LockOSThread()`函数的次数（也就是没有释放对 M 的独占），那这个 M 将会被终止，而非闲置。

所以，可以利用第三个特性，在启动 G 的时候，调用`LockOSThread()`来独占一个 M；在 G 退出的时候，不调用`UnlockOSThread()`，使得这个 M 一同被终止。

示例如下：

```go
package main

import (
  "fmt"
  "os/exec"
  "runtime/pprof"
  "time"
)

func main() {
  threadProfile := pprof.Lookup("threadcreate")
  fmt.Printf("init threads counts: %d\n", threadProfile.Count())

  for i := 0; i < 20; i++ {
    go func() {
      _, err := exec.Command("bash", "-c", "sleep 3").Output()
      if err != nil {
        panic(err)
      }
    }()
  }
  time.Sleep(time.Second * 5)
  fmt.Printf("end threads counts: %d\n", threadProfile.Count())
}
```

> 通过`threadProfile.Count()`可以实时获取到当前线程数量。

多运行几次上面的程序，可以看到，在发生了阻塞式系统调用后，程序的线程数目基本稳定在 25 左右，也就是 G 执行完毕之后，闲置线程并没有被释放：

```
init threads counts: 5
end threads counts: 25
```

如果在 goroutine 中添加一行代码`runtime.LockOSThread()`代码：

```go
go func() {
  runtime.LockOSThread()
  _, err := exec.Command("bash", "-c", "sleep 3").Output()
  if err != nil {
    panic(err)
  }
}()
```

然后再重新运行上述代码，得到的结果基本类似如下：

```
init threads counts: 5
end threads counts: 7
```

可以看到，由于 G 中调用了`runtime.LockOSThread()`函数，但是没有调用对应数量的`runtime.UnlockOSThread()`函数，所以在 G 执行完毕之后，M 也被终止了。

当然，这个 issue 中也提到了这个方法存在的问题：*当子进程由一个带有`PdeathSignal: SIGKILL`的 A 线程创建，A 变为空闲退出时，子进程将会收到 KILL 信号，从而引起其他问题。*

### 5. 总结

在 GMP 模型中，P 与 M 是一对一的挂载形式，通过设定 GOMAXPROCS 变量就能控制并行的线程数量。

当 M 遇到同步系统调用时，G 和 M 会与 P 分离。当 G 的同步系统调用完成后，G 会重新进入可运行状态并被加入到 P 的 LRQ 队列中，而 M 就会被闲置了。

Go 目前并没有对闲置线程做清理处理，它们被当做复用的资源以备后续需要。但是，如果在 Go 程序中积累大量的空闲线程，也会对资源造成浪费，同时也对操作系统产生了威胁。因此，Go 设定了 10000 的默认线程数量限制。

可以利用`runtime.LockOSThread()`函数的特性来限制空闲线程数：例如启动定期排查线程数的 goroutine，当发现程序的线程数超过某个阈值后，就回收掉一部分闲置线程。

当然，绝大多数情况下，Go 程序并不会遇到空闲线程数过多的问题。如果真的存在线程数暴涨的问题，那么就应该思考代码逻辑是否合理（为什么能允许短时间内如此多的系统同步调用），是否可以做一些例如限流一类的处理，而不是想着通过`runtime.LockOSThread()`方法来处理。

GMP 三者的数量限制如下：

* M：有限制，默认数量为 10000，可使用`debug.SetMaxThreads()`方法调整；
* G：没限制，但受内存影响，每个 G 需要 2K~4K 的栈空间；
* P：受本机核数影响，可大可小，不影响 G 的数量创建。


