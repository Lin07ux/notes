> 转摘：[Linux 中 CPU 利用率是如何算出来的？](https://mp.weixin.qq.com/s/40KWGKNBoa35s533YGWYIQ)

Linux 系统中，可以使用 top 命令查看当前系统的整体 CPU 利用率，如下所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1677593535)

CPU 利用率细分为了如下几项：

* us user
* sy system
* ni nice
* id idle
* wa iowait
* hi hardirq
* si softirq
* st

### 1. 如何采集

top 中输出的 CPU 利用率并不是长时间不变的，而是默认 3 秒刷新一次。

为了能动态的展示当前的 CPU 利用率数据，就需要不断的对 CPU 利用率进行取数。虽然是希望这个利用率是足够精确的，但依旧可以采用采样的方式来进行计算。

虽然是采样，但是采样的周期足够短，与数据的使用时间来说是非常小的了。在使用时间段内，不断的进行取样，然后将这段时间内的取样结果进行平均值。这样就能避免瞬时值剧烈震荡的问题，而且粒度更细，统计相对更准确。

默认情况下，Liunx 的取样周期是 1ms，也就是说，在 1s 内会进行 1000 次取样。类似如下：

![](https://cnd.qiniu.lin07ux.cn/markdown/1677595054)

> 虽然有可能在 1ms 内刚好经历两个进程，必然只会将这 1ms 统计到某一个进程上，造成统计结果有误差，但是对于长时间的统计和观测来说，这个误差并没有什么影响。

### 2. 数据存放

Liunx 在实现上是将瞬时采样值都累加到相关的数据上，这些数据值是内核通过`/prroc/stat`伪文件来对用户暴露。top 命令也是通过读取这个伪文件的数据来进行展示。

整体上看，top 命令工作的内部细节如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1677595043)

流程如下：

1. top 命令访问`/proc/stat`获取各项 CPU 利用率使用值；
2. 内核调用`stat_open`函数来处理对`/proc/stat`的访问；
3. 内核访问的数据来源于`kernel_cpustat`数组，并汇总；
4. 打印输出给用户态。

> 使用`strace`跟踪 top 命令的各种系统调用，会发现它除了读取`/proc/stat`文件，还会读取每个进程的`/proc/{pid}/stat`文件。这是因为 top 命令还要计算各个进程的 CPU 利用率。

内核为每个伪文件都定义了对应的处理函数，`/proc/stat`文件的处理方法是`proc_stat_operations`，其中包含了对该文件进行操作的各种方法。

当打开`/proc/stat`文件的时候，`stat_open`就会被调用到，它会循环每一个核的每种使用率，并最终将这些数据输出出来：

![](https://cnd.qiniu.lin07ux.cn/markdown/1677937643)

> 在内核中实际每个时间记录的是纳秒数，但是在输出的时候统一都转化成了节拍单位。

### 3. 如何统计

内核采样 CPU 使用率的周期依赖的是 Linux 时间子系统中的定时器。

Linux 内核每隔固定周期会发出 timer interrupt(IRQ 0)，而这个周期的时长是通过`CONFIG_HZ`来定义的。这个配置表示每一秒有几次 timerr interrupts。不同系统中这个周期的时长可能不同，通过在 1ms 到 10ms 之间。可以在 Linux config 文件中找到它的配置：

```
# grep ^CONFIG_HZ /boot/config-5.4.56.bsk.10-amd64
CONFIG_HZ=1000
```

没打过时间中断到来的时候，都会调用`update_process_times`来更新系统时间。更新后的时间都存储在 percpu 变量 kcpustat_cpu 中。

![](https://cnd.qiniu.lin07ux.cn/markdown/1677938056)

`update_process_times`函数会根据当前是内核态还是用户态分别将数据统计到用户态、内核态和空闲状态时间。

在用户态的时间处理中：

* 如果进程的 nice 值大于 0，那么将增加到 CPU 统计结构的 nice 字段中；
* 如果进程的 nice 值小于等于 0，则会增加到 CPU 统计结构的 user 字段中。

所以，用户态的时间不只是 user 字段，nice 也是。之所以要这样区分，是为了让 Linux 用户更一目了然的看到调用过 nice 的进程所占的 CPU 周期有多少。

在内核态的时间处理中：

* 如果当前处于硬中断执行上下文，那么统计到 irq（hi） 字段中；
* 如果当前处于软中断执行上下文，那么统计到 softirq 字段中；
* 否则统计到 system 字段中。

如果在采样的瞬间，CPU 既不在内核态也不在用户态，就表示处于空闲状态。在 CPU 空闲的情况下，进一步怕段当前是不是在等待 IO（例如磁盘 IO），如果是的话，这段空闲时间会加到 iowait 中，否则就加到 idle 中。

所以，iowait 其实是 CPU 的空闲时间，只不过是在等待 IO 完成而已。

### 4. 总结

Linux 系统统计 CPU 利用率的内部原理如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1677938773)

Linux 系统通过定时器以固定的频率对各个 CPU 核的使用情况进行采样，然后将当前周期时间累加到某一项中。

top 命令是通过读取伪文件`/proc/stat`来输出 CPU 各项利用率数据，这个数据就是内核根据`kernel_cpustat`来汇总得到的。

在统计的数据中，可以将 CPU 时间项目大致可以分为三类：

* 第一类：用户态消耗时间，包括 user 和 nice；
* 第二类：内核态消耗时间，包括 irq、softirq 和 system；
* 第三类：空闲时间，包括 io_wait 和 idle。