> 转摘：[通过pprof定位groutine泄漏](https://mp.weixin.qq.com/s/UcOwzNHqhPE0ZjKR1maWBw)

`pprof`是 Go 的性能分析工具，可以查看程序在运行过程中 CPU、内存、协程、锁等的详细信息。这对于定位程序中的 bug 非常有帮助。

下面以死锁造成的内存泄露为例，来看看怎样使用 pprof 定位到内存泄露的位置。

## 一、HTTP 服务

Go 中已经封装好了一个 pprof 服务，只需要引入该依赖，并在代码中开启默认 HTTP 服务，就可以访问对应的 url 就获取到程序运行时的资源情况了。

### 1.1 开启 HTTP 服务

如下代码即可在 HTTP 服务中开启 pprof 相关路由功能：

```go
package main

import (
	"fmt"
	"net/http"
	_ "net/http/pprof"
	"sync"
	"time"
)

func main() {
	mutexTest()
	pprofServer()
}

func pprofServer() {
	host := "0.0.0.0:6060"
	if err := http.ListenAndServe(host, nil); err != nil {
		fmt.Printf("start pprof failed on %s\n", host)
	}
}

func mutexTest() {
	mutex := sync.Mutex{}
	for i := 0; i < 10; i++ {
		go func(i int) {
			mutex.Lock()
			fmt.Printf("%d goroutine get mutex\n", i)
			// 模拟项目中后续代码耗时
			time.Sleep(10000 * time.Microsecond)
		}(i)
	}
}
```

pprof 包会自动的向 Go 默认的 http server 中注入相关的路由路径：`/debug/pprof/`。所以我们开启 HTTP 服务之后，就可以访问这个 url 来查看结果了：

> 上例代码对应的 url 即为：[http://127.0.0.1:6060/debug/pprof/](http://127.0.0.1:6060/debug/pprof/)

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647584246512-540014415c18.jpg)

### 1.2 信息查看

在 pprof 服务对应的页面中，常用的有如下五种信息：

* [goroutine](/debug/pprof/goroutine?debug=1) 展示所有 goroutine 的详细信息，包括协程数量、协程调用调用栈等；
* [heap](/debug/pprof/heap?debug=1) 程序堆内存信息（每 1000 次内存申请采样一次）
* [mutex](/debug/pprof/mutex?debug=1) 锁的调用信息
* [profile](/debug/pprof/profile?debug=1) 程序执行的 CPU 信息（每 1 秒采样 100 次）
* [trace](/debug/pprof/trace?debug=1) 程序调用栈信息

比如，点击 goroutine 链接，即可查看 goroutine 的基本信息：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647584944657-2939e950561d.jpg)

可以看到，一共有 13 个协程，其中有 9 个协程阻塞在了获取锁的位置。

将 url 中的`debug=1`改为`debug=2`，可以查看每个 goroutine 的详细信息：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647585053766-f29c478fbba6.jpg)

可以看到，有些 goroutine 被阻塞在了锁等待上面。

## 二、命令行交互

在实际开发中，有些程序是部署在内网服务器中，不能直接访问，而服务器上也没有浏览器提供可视化界面，这时候就需要通过命令来查看资源的使用情况了。

> 需要注意的是，使用命令行交互之前，也是需要在程序中开启 pprof 的 HTTP 服务的。

### 2.1 命令交互

可以使用`go tool pprof <url>`来获取相关的 pprof 信息，这里的 url 即为前面介绍的 HTTP 服务中的对应的 url 地址。

以下是 5 类信息的请求的方式：

```shell
# 下载 cpu profile 信息，默认从当前开始收集 30s 的 CPU 使用情况，需要等待 30s
go tool pprof http://localhost:6060/debug/pprof/profile
# 也可以指定等待的时间，如 60s
go tool pprof http://localhost:6060/debug/pprof/profile?seconds=60

# 下载 heap profile
go tool pprof http://localhost:6060/debug/pprof/heap

# 下载 goroutine profile
go tool pprof http://localhost:6060/debug/pprof/goroutine

# 下载 block profile
go tool pprof http://localhost:6060/debug/pprof/block

# 下载 mutex profile
go tool pprof http://localhost:6060/debug/pprof/mutex
```

等待收集完成后就可以通过命令查看响应的信息，所有的交互命令都可以通过`help`指令查看：

```
$ go tool pprof http://localhost:6060/debug/pprof/profile
Fetching profile over HTTP from http://localhost:6060/debug/pprof/profile
Saved profile in /pprof/pprof.samples.cpu.016.pb.gz
Type: cpu
Time: Mar 6, 2022 at 4:15pm (CST)
Duration: 30s, Total samples = 290ms ( 0.97%)
Entering interactive mode (type "help" for commands, "o" for options)
(pprof) help
  Commands:
  callgrind        Outputs a graph in callgrind format
  comments         Output all profile comments
  disasm           Output assembly listings annotated with samples
  dot              Outputs a graph in DOT format
  eog              Visualize graph through eog
  evince           Visualize graph through evince
  gif              Outputs a graph image in GIF format
  gv               Visualize graph through gv
  kcachegrind      Visualize report in KCachegrind
  list             Output annotated source for functions matching regex
```

常用命令有三种：`top`、`list`、`traces`。

### 2.2 top

`top <N>`命令可以虎丘程序中资源消耗最大的前 N 个函数。

例如，输入`top 10`可以查看 CPU 消耗前十的函数：

```
(pprof) top 10
Showing nodes accounting for 270ms, 93.10% of 290ms total
Showing top 10 nodes out of 19
      flat  flat%   sum%        cum   cum%
      90ms 31.03% 31.03%      130ms 44.83%  runtime.kevent
      40ms 13.79% 44.83%       40ms 13.79%  runtime.libcCall
      30ms 10.34% 55.17%       30ms 10.34%  runtime.kevent_trampoline
      20ms  6.90% 62.07%       50ms 17.24%  runtime.checkTimers
      20ms  6.90% 68.97%      250ms 86.21%  runtime.findrunnable
      20ms  6.90% 75.86%       20ms  6.90%  runtime.lock2
      20ms  6.90% 82.76%       30ms 10.34%  runtime.runtimer
      10ms  3.45% 86.21%       10ms  3.45%  runtime.(*randomEnum).next (inline)
      10ms  3.45% 89.66%       10ms  3.45%  runtime.runOneTimer
      10ms  3.45% 93.10%       10ms  3.45%  runtime.runqget
```

`top`命令会统计下面五种信息：

* `flat` 函数本身占用的 CPU 时间；
* `flat%` 本函数 CPU 占使用中 CPU 总量的百分比；
* `sum%` 前面每一行`flat`百分比的累加，比如第 2 行的 44.3% = 第 1 行的 31.03% + 当前函数的 13.79%；
* `cum` 是累计量，例如`main`函数调用子函数`mutexTest`，那么子函数`mutexTest`的 CPU 使用量也会被记进来；
* `cum%` 是累计量占总量的百分比。

### 2.3 list

`list <func>`命令可以查看某个函数的资源使用信息，函数匹配使用的是正则表达式：

```
(pprof) list mutexTest
Total: 7.73s
ROUTINE ======================== main.mutexTest.func1 in article/go/pprof/main.go
        0      3.82s (flat, cum) 49.42% of Total
        .          .     19:                   fmt.Printf("%d goroutine get mutex", i)
        .          .     20:                   //模拟实际开发中的操作耗时
        .          .     21:                   tick := time.Tick(time.Second / 100)
        .          .     22:                   var buf []byte
        .          .     23:                   for range tick {
        .      3.82s     24:                           buf = append(buf, make([]byte, 1024*1024)...)
        .          .     25:                   }
        .          .     26:                   time.Sleep(100 * time.Millisecond)
        .          .     27:                   wg.Done()
        .          .     28:           }()
        .          .     29:   }
```

显示该函数的占用 0s（保留小数点后两位），累计上子函数的调用占用 3.82s，占总 CPU 使用时间的 49.42%。耗时主要在第 24 行，占用了 3.82s。

在实际项目中可能会出出现不同包中函数名相同的情况，尤其是接口中函数的问题定位。如果使用模糊查找自己想看的函数会很麻烦，可以使用特殊的正则匹配方式来过滤（假设包括`work`的多个包中都有`show`方法，以`work`包的特殊处理为例）：

* 模糊匹配：输入函数名称，如`show`；
* 精确匹配：从根路径查找到包里的方法，使用`包.方法名`来精确匹配，如`artical/go/pprof/main.Test`；
* 指定包名：可以指定只查找指定包中的方法，如`list Show -focus = work*`（注意等号两边要有空格）；
* 忽略包名：可以指定忽略某些包中的相应方法，如`list Show -work*`这样就不会展示`work`包中的`Show`方法。

## 三、可视化工具

上面介绍的两种方式都是 pprof 自带的检测方法，虽然可以定位问题，但是交互界面都是密密麻麻的文字和代码，难以区分和辨认。

通过 graphviz 来解析 pprof 数据文件，可以带来可视化服务，使定位能够更加清晰。

> 使用前需要下载 graphivz 工具。

### 3.1 开启服务

首先将 pprof 信息导入到文件中：

```shell
curl -sK -v http://localhost:6060/debug/pprof/profile > cpu.out
```

然后打开一个本地的 http 服务：

```shell
go tool pprof -http=:8080 heap.out
```

这个命令会启动 HTTP 服务后自动跳转到浏览器上，并访问到对应的 UI 路径上：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647588702885-e90eb4799eb5.jpg)

节点中的数字表示`flat(函数使用量) of cum(包括子的使用量) cum%`，节点越大越红表示该节点的 flat 值越大，线越粗表示指向的节点 cum 值越大。

### 3.2 VIEW 视图

![](http://cnd.qiniu.lin07ux.cn/markdown/1647588967957-120e02f45407.jpg)

VIEW 下面有如下几项功能：

* `TOP` 和`top`命令相同，将函数按资源使用进行排名；
* `Graph` 如图的函数调用逻辑图以及节点使用；
* `Flame Graph` 火焰图，资源使用按从大到小排列，点击可看详细信息；
* `Peek` 打印每个调用栈的信息
* `Source` 显示具体函数的资源消耗信息，类似`list`命令；
* `Disassemble` 显示样本总量。

### 3.3 SIMPLE

在 SIMPLE 中，有可以查看内存相关的信息：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647589527508-3d9711ecde26.jpg)

* `alloc_objects` 已分配的对象总量（不管是否已释放）
* `alloc_space` 已分配的内存总量（不管是否已释放）
* `inuse_objects` 已分配且未释放的对象数量
* `inuse_sapce` 已分配但未释放的内存数量

### 3.4 REFINE

在 REFINE 中可以搭配搜索框使用正则表达式对内容进行过滤，类似于`list`命令：

![](http://cnd.qiniu.lin07ux.cn/markdown/1647589651780-345697011424.jpg)


