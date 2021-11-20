> 转摘：[学会这几招让 Go 程序自己监控自己](https://mp.weixin.qq.com/s/ik4OuJffy_zqJZCE2fFJrw)

### 1. 问题

通过 [gopsutil](https://github.com/shirou/gopsutil) 库能够方便的实现获取系统和硬件信息，但是获取到的数据只有在虚拟机和物理机环境中才是准确的，在类似 Docker 这样的 Linux 容器中是不行的。因为它们是靠 Linux 的 Namespace 和 CGroups 技术实现的进程隔离和资源限制，但是 go 环境获取的依旧是宿主机的一些数据。

### 2. 解决

在 Linux 中，Cgroups 给用户暴露出来的操作系统接口是文件系统，以文件和目录的方式组织在操作系统的`/sys/fs/cgroup`路径下。在这个路径中，有很多诸如`cpuset`、`cpu`、`memory`这样的子目录，**每个子目录都代表系统当前可以被 Cgroups 进行限制的资源种类**。

比如，针对使用 Go 监控进程内存和 CPU 指标的需求，只需要知道`cpu.cfs_period_us`、`cpu.cfs_quota_us`、`memory.limit_in_bytes`就行。

示例代码如下：

```go
func main() {
  // 容器能使用的最大核心数
  cpuPeriod, _ := readUint("sys/fs/cgroup/cpu/cpu.cfs_period_us")
  cpuQuota, _ := readUint("sys/fs/cgroup/cpu/cpu.cfs_quota_us")
  cpuNum := float64(cpuQuota) / float64(cpuPeriod)
  
  // 使用 gopsutil 获取当前进程在 1s 内使用的 CPU 时长
  p, _ := process.NewProcess(int32(os.Getpid()))
  cpuPercent, _ := p.Percent(time.Second)
  cp := cpuPercent / cpuNum
  
  // 容器能使用的最大内存数
  memLimit, _ := readUint("/sys/fs/cgroup/memory/memory.limit_in_bytes")
  
  // 计算内存使用率(RSS 表示进程使用的物理内存的大小)
  memInfo, _ := p.MemoryInfo
  mp := memInfo.RSS * 100 / memLimit
}
```

这里的`readUint()`函数用来读取容器资源，是 containerd 组织在 [cgroups](https://github.com/containerd/cgroups/blob/318312a373405e5e91134d8063d04d59768a1bff/utils.go#L243) 实现中给出的方法：

```go
func readUint(path string) (uint64, error) {
  v, err := ioutil.ReadFile(path)
  if err != nil {
    return 0, err
  }
  return parseUint(strings.TrimSpace(string(v)), 10, 64)
}
func parseUint(s string, base, bitSize int) (uint64, error) {
  v, err := strconv.ParseUint(s, base, bitSize)
  if err != nil {
    intValue, intErr := strconv.ParseInt(s, base, bitsize)
    
    // 1. Handle negative values grater than MinInt64 (and)
    if intErr == nil && intValue < 0 {
      return 0, nil
    }
    
    // 2. Handle negative values lesser than MinInt64
    if intErr != nil &&
      intErr.(*strconv.NumError).Err == strconv.ErrRange &&
      intValue < 0 {
      return 0, nil
    }
    
    return 0, err
  }
  
  return v, nil
}
```



