> 转摘：
> 
> 1. [一文读懂｜Linux系统平均负载](https://mp.weixin.qq.com/s/aVCQvxAA8itQ3PheEUFBcg)
> 2. [Linux 中的负载高低和 CPU 开销并不完全对应](https://mp.weixin.qq.com/s/1Pl4tT_Nq-fEZrtRpILiig)

### 1. 引言

Linux 中的`top`命令可以用来查看系统的性能情况，其输出的第一行中有`load average`数据，用于指示系统的平均负载：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668850104)

load average 的值包含有 3 列，分别表示 1 分钟、5 分钟、15 分钟的系统平均负载。

系统平均负载用于指示系统所有资源的需求情况，包括 CPU 资源和其他资源（如 IO 资源）；而 CPU 负载表示的仅仅是 CPU 的消耗情况。所以，负载高低表示的是当前系统上对系统资源整体需求情况，如果负载变高，可能是 CPU 资源不够，也可能是磁盘 IO 资源不够了，或者其他资源竞争激烈。

### 2. 系统平均负载是什么

在 [Understanding Linux CPU Load - when should you be worried?](https://scoutapm.com/blog/understanding-load-averages) 一文中，对“系统平均负载”有非常通俗的解释，其说明如下。

将 CPU 比作是桥梁，那单核的 CPU 就相当于是单车道的桥梁，每次只能让一辆汽车通过，并且要以规定的速度通过。那么：

* 如果每个时刻都只有一辆汽车通过，那么所有汽车都不用排队，此时桥梁的使用率最高，以平均负载 1.0 表示，如下图所示：

    ![](https://cnd.qiniu.lin07ux.cn/markdown/1668850503)

* 如果每隔一段时间才有一辆汽车通过，那么表示桥梁部分时间处于空闲的状态，并且间隔的时间越长，表示桥梁空闲率越高。此时的平均负载小于 1.0，如下图所示：

    ![](https://cnd.qiniu.lin07ux.cn/markdown/1668850559)

* 当有大量的汽车通过桥梁时，有些汽车需要等待其他车辆通过后才能继续通行，这时表示桥梁超负荷工作。此时平均负载大于 1.0，如下图所示：

    ![](https://cnd.qiniu.lin07ux.cn/markdown/1668850606)

系统的平均负载与上面的例子一样，在单核 CPU 的环境下：

* 当系统平均负载等于 1.0 时，表示 CPU 使用率最高；
* 当系统平均负载小于 1.0 时，表示 CPU 使用率处于空闲状态；
* 当系统平均负载大于 1.0 时，表示 CPU 使用率已经超过负荷。

也就是说，对于单核 CPU 来说，平均负载 1.0 表示使用率最高。对于多核 CPU 来说，平均负载要乘以核心数。比如在 4 核 CPU 的系统中，当平均负载为 4.0 时才表示 CPU 的使用率最高。

### 3. 计算原理

在 Linux 系统中，系统负载表示**系统中当前正在运行的进程数量**，包括*可运行状态*的进程数和*不可中断休眠状态*的进程数的和。

> *不可中断休眠状态*的进程一般是在等待 I/O 完成的进程。

也就是说：

```text
系统负载 = 可运行状态进程数 + 不可中断休眠状态进程数
```

知道了什么是系统负载，那么系统平均负载就容易理解了。比如每 5 秒统计一次系统负载，1 分钟内会统计 12 次。然后把每次统计到的系统负载加起来，再除以统计次数，即可得出系统平均负载了。如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668851004)

但这种计算方式有些缺陷，就是预测系统负载的准确性不够高。因为时间越近的数据，对未来的预测准确性越高，而越老的数据越不能反映现在的情况。比如说，要预测某条公路今日的车流量，使用昨天的数据作为预测依据会比使用上个月的数据作为一句准确的多。

Linux 内核使用一种名为**指数平滑法**的算法来解决这个问题。指数平滑法的核心思想是对新老数据进行加权，越老的数据权重越低。而且采用指数平滑法能够避免存储大量的采样数据，并简化进行计算的数据量。

> 指数平滑法是由 Robert G.Brown 提出的一种加权移动平均法。

其计算公式如下（来源于 Linux 内核代码`kernel/sched/core.c`）：

```text
load1 = load0 * e + active * (1 - e)
```

其中：

* `load1`表示时间 t+1 的系统负载；
* `load0`表示时间 t 的系统负载；
* `e`表示衰减系统；
* `active`表示系统中的活跃进程数（可运行状态进程数 + 不可中断休眠状态进程数）。

在 Linux 内核中，计算 1 分钟内、5 分钟内、15 分钟内系统平均负载的衰减系数`e`的计算方式如下：

```text
1 / exp(5sec / 1min)
1 / exp(5sec / 5min)
1 / exp(5sec / 15min)
```

其中：

* `5sec`表示统计的时间间隔，5 秒；
* `1min`表示统计的时长，1 分钟；
* `exp`表示以自然常数`e`为底的指数函数。

Linux 内核已经把 1 分钟、5 分钟、15 分钟的衰减系数结果计算出来了，并且定义在`include/linux/sched.h`文件中了，如下所示：

```c
#define EXP_1       1884        /* 1/exp(5sec/1min) as fixed-point */
#define EXP_5       2014        /* 1/exp(5sec/5min) */
#define EXP_15      2037        /* 1/exp(5sec/15min) */
```

通过公式计算出来的衰减系数是个浮点数，而在内核中是不能进行浮点数运行的。解决方法是先对衰减系数进行扩大，然后在展示时做缩小。上面的衰减系数数值是经过扩大 2048 倍后的结果。

### 4. 计算实现

#### 4.1 数据存储

在 Linux 内核中，使用了`avenrun`数组来存储 1 分钟、5 分钟、15 分钟的系统平均负载，如下代码所示：

```c
unsigned long avenrun[3];
```

其中`avenrun[0]`用于存储 1 分钟内的系统平均负载，`avenrun[1]`用于存储 5 分钟的系统平均负载，`avenrun[2]`表示 15 分钟的系统平均负载。

#### 4.2 统计过程

由于统计需定时执行，所以内核把统计过程放置到时钟中断中进行。当时钟中断触发时，将会调用`do_timer()`函数，继而调用`calc_global_load()`来统计系统平均负载。

`calc_global_load()`函数的实现如下：

```c
void calc_global_load(unsigned long ticks)
{
    long active, delta;
    
    // 1. 如果还没有到统计的时间间隔，则不进行统计（5 秒统计一次）
    if (time_before(jiffies, calc_load_update + 10))
        return;
    
    // 2. 获取活跃进程数
    delta = calc_load_fold_idle();
    if (delta)
        atomic_long_add(delta, &calc_load_tasks);
    
    active = atomic_long_read(&calc_load_tasks);
    active = active > 0 ? active * FIXED_1 : 0;
    
    // 3. 统计各个时间段系统平均负载
    avenrun[0] = calc_load(avenrun[0], EXP_1, active);
    avenrun[1] = calc_load(avenrun[1], EXP_5, active);
    avenrun[2] = calc_load(avenrun[2], EXP_15, active);
    
    // 4. 更新下次统计的时间（增加 5 秒）
    calc_load_update += LOAD_FERQ;
    
    ...
}
```

可以看到，`calc_global_load()`函数主要完成 4 件事情：

1. 判断当前时间是否需要进行统计。如果话没有到统计的时间间隔，将不进行统计（5 秒统计一次）；
2. 获取活跃进程数（可运行状态进程数 + 不可中断休眠状态进程数）；
3. 统计各个时间段系统平均负载（1 分钟、5 分钟、15 分钟）；
4. 更新下次统计的时间（增加 5 秒）。

计算系统平均负载的函数为`calc_load()`，代码如下：

```c
/**
 * a1 = a0 * e + a * (1 - e)
 */
static unsigned long calc_load(unsigned long load, unsigned long exp, unsigned long active)
{
    load *= exp;
    load += active * (FIXED_1 - exp);
    load += 1UL << (FSHIFT - 1);
    
    return load >> FSHIFT;
}
```

该函数的三个参数意义如下：

* `load` t-1 时间点的系统负载
* `exp` 衰减系数
* `active` 活跃进程数

可以看出，`calc_load()`函数的实现就是按照指数平滑法来计算的。


