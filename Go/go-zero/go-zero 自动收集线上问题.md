> 转摘：[Go 服务自动收集线上问题现场](https://mp.weixin.qq.com/s/vB9ElJCfgZeQHtB596XHpA)

### 1. 前言

Go 语言中，可以使用 pprof 来收集线上数据，快速定位问题。但是由于其对性能上的影响，一般线上项目并不会开启 pprof。这就导致线上出现问题时，不能很方便的进行问题数据收集。

go-zero 针对这个需求，采用了信号的方式来开启、关闭线上的 pprof，类似于开关配置一样。这样，就可以配合运维监控，当出现 CPU、内存等异常情况的时候，自动开启收集。

### 2. 启停源码

go-zero 中启停 pprof 的源码位于 [core/proc/signals.go](https://github.com/zeromicro/go-zero/blob/master/core/proc/signals.go) 中，关键代码如下：

```go
func init() {
  go func() {
    signals := make(chan os.Signal, 1)
    signal.Notify(signals, syscall.SIGUSR1, syscall.SIGUSR2, syscall.SIGTERM)
    
    for {
      v := <-signals
      switch v {
      ...
      case syscall.SIGUSR2:
        if profile == nil {
          profiler = StartProfile()
        } else {
          profiler.Stop()
          profiler = nil
        }
      ...
      }
    }
  }()
}
```

可以看到，其核心逻辑就是在`init`函数中初始化了一个监听信号的操作(`gracefulStop`也是通过这里通过`syscall.SIGTERM`信号通知的)。

在接收到`syscall.SIGUSR2`信号的时候，如果是第一次收到该信号，则开启 pprof 收集；如果是第二次收到，则停止收集。也就是说，只需要在服务器中执行`kill -usr2 <服务进程 ID>`就可以开始收集这个服务器的 pprof 信息了，再执行一次这个命令，就可以停止收集了。

### 3. 收集源码

go-zero 收集 pprof 信息的源码位于 [core/proc/profile.go](https://github.com/zeromicro/go-zero/blob/master/core/proc/profile.go) 中。

开始收集的源码如下：

```go
var stared uint32

func StartProfile() Stopper {
  if !aotmic.CompareAndSwapUint32(&stared, 0, 1) {
    logx.Error("profile: Start() already called")
    return noopStopper
  }
  
  var prof Profile
  prof.startCpuProfile()
  prof.startMemProfile()
  prof.startMutexProfile()
  prof.startBlockProfile()
  prof.startTraceProfile()
  prof.startThreadCreateProfile()
  
  go func() {
    c := make(chan os.Signal, 1)
    signal.Notify(c, syscall.SIGINT)
    <-c
    
    logx.Info("profile: caught interrupt, stopping profiles")
    prof.Stop()
    
    signal.Reset()
    syscall.Kill(os.Getpid(), syscall.SIGINT)
  }()
  
  return &prof
}
```

> 其中 Stopper 是一个 interface，拥有一个`Stop()`方法，而`noopStopper`则是一个空实现的 Stopper 实例。

其收集的 pprof 信息有 CPU、Memory、Mutex、Block、Trace、Thread Create 六方面的信息，每一类信息都会单独生成一个文件，存放在服务器的`/tmp`文件夹中，文件名称的生成源码如下：

```go
func createDumpFile(kind string) string {
  command := path.Base(os.Args[0])
  pid := syscall.Getppid()
  return path.Join(os.TemDir(), fmt.Srpintf("%s-%d-%s-%s.pprof", command, pid, kind, time.Now().Format(timeFormat)))
}
```

### 4. 线上实战

线上有一个 mq 的服务监控告警，提示内存占用比较高，可以按照如下步骤收集 pprof 并分析问题：

1. 到线上找到这个服务器，执行下面的命令找到 mq 服务对应的进程 ID：

    ```shell
    ps aux | grep xxx_mq
    ```
    
    假定 mq 服务对应的进程 ID 为 假定为 21181。

2. 执行如下的命令开启 go-zero 的 pprof 收集：

    ```shell
    kill -usr2 21181
    ```
    
    此时可以在服务对应的日志（如`access.log`）中看到有 enable pprof 相关的提示，表示开始收集了。
    
3. 经过一段时间后，再停止 go-zero 的 pprof 收集：

    ```shell
    kill -usr2 21181
    ```

    此时可以在服务对应的日志（如`access.log`）中看到有 disable pprof 相关的提示，表示收集完成了。

4. 在`/temp`文件夹下找到并下载相关的 pprof 文件，类似如下：

    ```text
    xxxx-mq-cpu-xxx.pprof
    xxxx-mq-memory-xxx.pprof
    xxxx-mq-mutex-xxx.pprof
    xxxx-mq-block-xxx.pprof
    .......
    ```

5. 由于是内存占用异常，所以可以分析其中的 memory 相关的信息：

    ```shell
    go tool pprof xxxx-mq-memory-xxx.pprof
    ```

    也可以配合`graphviz`使用 web ui 查看。

### 5. 其他

go-zero 使用信号量的方式实现了 pprof 的启停开关，可以方便实现线上数据的收集和问题定位，这种方式也适合在其他项目中引入。

线上项目的问题并非发生时都可以快速的进行人工开启和关闭 pprof 的收集，对此，可以配合运维监控实现自动的启停 pprof 收集和文件保存。比如，在内存、CPU 的指标连续 3 分钟超过 80% 就自动开启 pprof 开关进行数据收集。

