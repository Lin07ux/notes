> 转摘：
> 
> 1. [性能优化 | Go Ballast 让内存控制更加丝滑](https://mp.weixin.qq.com/s/gc34RYqmzeMndEJ1-7sOwg)
> 2. [Go memory ballast: How I learnt to stop worrying and love the heap](https://blog.twitch.tv/en/2019/04/10/go-memory-ballast-how-i-learnt-to-stop-worrying-and-love-the-heap/)

### 1. 问题

Go 可以使用如下两种方式调整 GC 的触发频率：

* 设置`GOGC`
* 设置`debug.SetGCPrecent()`

这两种方式的原理和效果是一样的，都是用来调整下次触发 GC 的 heap 的大小比例：新分配的数据与最近一次 GC 之后的 heap 的大小的比例。

默认情况下，`GOGC`的值为 100，也就是说：当新分配的数据的大小为上次 GC 之后 heap 的一倍，那么就触发 GC。

一般来说：如果想要减少在 GC 上花费的时长（也就是 CPU 占比），那么就增加`GOGC`的值，但是内存必须要足够大；如果内存比较小，只能用时间换空间，也就是减少`GOGC`的值以更频繁的进行 GC。

但是设置 GOGC 来调整 GC 频率的方式有一些弊端：

1. GOGC 设置比率的方式不精确，难以精确的控制想要的触发垃圾回收的阈值；
2. GOGC 的值难以量化：设置过小时会频繁的触发 GC 浪费 CPU，设置过大时又容易触发 OOM；
3. 某些程序本身占用内存较低时，就很容易触发 GC。

### 2. 解决

Go Ballast 是官方比较认可的来解决这个问题的方案，具体可以参见[issue 23044](https://github.com/golang/go/issues/23044)。很多开源程序，如[tidb](https://github.com/pingcap/tidb/pull/29121/files)、[cortex](https://github.com/cortexproject/cortex/blob/master/cmd/cortex/main.go#L148) 都实现了 Go Ballast。

Ballast 意思是压舱石。而 **Go Ballast 就是一个生命周期贯穿整个 Go 生命周期的超大 Slice**。

比如，下面的代码就初始化了一个 10G 大小的 Ballast，并利用`runtime.KeepAlive()`函数来保证这个 Ballast 不会被 GC 给回收掉。

```go
func main() {
  ballast := make([]byte, 10<<30) // 10G
  
  // do something
  
  runtime.KeepAlive(ballast)
}
```

因为 Ballast 被设置为了 10G，那么当`GOGC`为默认值时，GC 就至少要在 heap 达到 20G 的时候才会被触发。而实际的应用还会有用于业务处理的数据存在，所以触发 GC 的时候 heap 的大小一般都会超过 20G 了。

这也体现出来 Ballast 压舱石的含义：由于 Ballast 不会被 GC 回收，所以每次 GC 的触发条件都是要 heap 以此大小为基础进行翻倍的。

使用 Go Ballast 之后，GC 的频率大大降低了，CPU 占用也降低了。下面是一个 Web 服务的结果示例：

![Log base 2 scale graph showing GC cycles per minute](http://cnd.qiniu.lin07ux.cn/markdown/1637211665633-6a57efc34c23.jpg)

![Visage Application CPU utilization](http://cnd.qiniu.lin07ux.cn/markdown/1637211709601-56f6da0fdc54.jpg)

### 3. 说明

由于 Go Ballast 一般要求是超大的 Slice，那么是否会占用那么大的物理内存呢？答案是不会。

在现代操作系统中，程序使用的都是虚拟内存，通过 OS 的内存页表映射到实际内存上。当上面的代码运行时，Ballast 其实只是指向了程序的虚拟地址空间，只有当对它进行读写操作时，才会真正的申请物理内存。

比如，对于如下代码：

```go
func main() {
  _ = make([]byte, 100<<20) // 100M
  <-time.After(time.Duration(math.MaxInt64))
}
```

这里创建了一个大小为 100M 的 Ballast，当将这行代码保留和去除时分别运行，查看其内存占用情况可以发现基本没有什么变化，而且也不会真的占用 100M 以上的内存：

```shell
ps -eo pmem,comm,pid,maj_flt,min_flt,rss,vsz --sort -rss | numfmt --header --to=iec --field 4-5 | numfmt --header --from-unit=1024 --to=iec --field 6-7 | column -t | egrep "[t]est|[P]ID"
%MEM  COMMAND          PID    MAJFL  MINFL  RSS   VSZ
0.2   test_alloc       27826  0      1003   4.8M  108M
```

这里`RSS`(Resident Set Size)表示常驻内存，也就是实际占用的内存大小，为 4.8M；而`VSZ`(Virtual Size)虚拟内存大小则达到了 108M。

> Mac 上需要使用`ps`命令来查看。

