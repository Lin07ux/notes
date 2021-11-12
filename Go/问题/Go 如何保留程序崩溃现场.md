> 转摘：[如何保留 Go 程序崩溃现场](https://mp.weixin.qq.com/s/RktnMydDtOZFwEFLLYzlCA)

线上 Go 程序突然崩溃后，当日志记录没有覆盖到错误场景时，可以使用 Linux 的 core dump（核心转储）功能做内存快照，作为分析使用。

### 1. core dump

core dump 简单来说就是程序意外终止时产生的内存快照，可以通过该文件来调试程序，找出其崩溃的原因。

在 Linux 平台上，可以通过`ulimit -c`命令查看核心转储配置，系统默认为 0，表明未开启 core dump 记录功能。

同样，可以使用`ulimit -c [size]`命令指定记录 core dump 文件的大小，也同时开启了 core dump 记录功能。

如果存储资源足够，为避免 core dump 丢失或记录不全，也可执行`ulimit -c unlimited`而不限制 core dump 文件的大小。

### 2. Go 开启 core dump

当 Go 发生未被捕获或不能被捕获的 panic 错误时，会打印出相关的堆栈信息。这些堆栈信息是由`GOTRACEBACK`变量来控制打印粒度的，有五种级别：

* `none` 不显示任何 goroutine 堆栈信息；
* `single` 默认级别，显示当前 goroutine 堆栈信息；
* `all` 显示所有 user（不包括 runtime）创建的 goroutine 堆栈信息；
* `system` 显示所有 user + runtime 创建的 goroutine 堆栈信息；
* `crash` 和`system`打印一致，但是会生成 core dump 文件（Unix 系统上，崩溃会引发`SIGABRT`以触发 core dump）。

所以，如果想获取 core dump 文件，就需要**将环境变量`GOTRACEBACK`的值设置为 crash**。也可以**通过`runtime/debug`包中的`SetTraceback()`方法来设置堆栈打印级别**。

> Mac amd64 架构下的 Go 限制了生成 core dump 文件，这个在 Go 源码`runtime/signal_unxi.go`中有相关说明：
> 
> ```go
> //go:nosplit
> func crash() {
>   // OS X core dumps are linear dumps of the mapped memory,
>   // from the first virtual byte to the last, with zeros in the gaps.
>   // Because of the way we arrange the address space on 64-bit systems,
>   // this means the OS X core file will be >128 GB and even on a zippy
>   // workstation can take OS X well over an hour to write (uninterruptible).
>   // Save users from making that mistake.
>   if GOOS == "darwin" && GOARCH == "amd64" {
>     return
>   }
> 
>   dieFromSignal(_SIGABRT)
> }
> ```

### 3. delve 调试 core dump

delve 是 Go 语言编写的 Go 程序调试器，可以通过`delev core`命令来调试 core dump 文件。

* 首先，通过以下命令安装`delve`：

    ```shell
    go get -u github.com/go-delve/delve/cmd/dlv
    ```

* 然后，通过设置环境变量`GOTRACEBACK=crash`来启动程序，得到 core dump 文件：

    ```shell
    GOTRACEBACK=crash ./main
    ```

* 使用 dlv 调试器来调试 core 文件：

    ```shell
    # 命令格式：dlv core <可执行文件名> <core dump 文件名>
    dlv core main core
    ```

* 使用 dlv 中的`goroutines`命令获取所有的 goroutine 相关信息：

    ```
    (dlv) goroutines
    * Goroutine 1 - User: ./main.go:21 main.main (0x45b81a) (thread 18061)
      Goroutine 2 - User: /usr/local/go/src/runtime/proc.go:367 runtime.gopark (0x42ed96) [force gc (idle)]
      Goroutine 3 - User: /usr/local/go/src/runtime/proc.go:367 runtime.gopark (0x42ed96) [GC sweep wait]
      Goroutine 4 - User: /usr/local/go/src/runtime/proc.go:367 runtime.gopark (0x42ed96) [GC scavenge wait]
    [4 goroutines]
    (dlv)
    ```
    
    > Goroutine 1 就是出问题的 goroutine。

* 通过`goroutine 1`切换到其栈帧：

    ```
    (dlv) goroutine 1
    Switched from 1 to 1 (thread 18061)
    (dlv)
    ```

* 使用`bt`(breakpoints trace)命令查看当前的栈帧详细信息：

    ```
    (dlv) bt
    0  0x0000000000454bc1 in runtime.raise
       at /usr/local/go/src/runtime/sys_linux_amd64.s:165
    1  0x0000000000452f60 in runtime.systemstack_switch
       at /usr/local/go/src/runtime/asm_amd64.s:350
    2  0x000000000042c530 in runtime.fatalthrow
       at /usr/local/go/src/runtime/panic.go:1250
    3  0x000000000042c2f1 in runtime.throw
       at /usr/local/go/src/runtime/panic.go:1198
    4  0x000000000043fa76 in runtime.sigpanic
       at /usr/local/go/src/runtime/signal_unix.go:742
    5  0x000000000045b81a in main.Modify
       at ./main.go:21
    6  0x000000000045b81a in main.main
       at ./main.go:25
    7  0x000000000042e9c7 in runtime.main
       at /usr/local/go/src/runtime/proc.go:255
    8  0x0000000000453361 in runtime.goexit
       at /usr/local/go/src/runtime/asm_amd64.s:1581
    (dlv)
    ```

    > 在`5  0x000000000045b81a in main.Modify`后就开始触发 panic，所以这个地方就是引发崩溃的地方。

* 使用`frame 5`进入到函数具体代码：

    ```
    (dlv) frame 5
    > runtime.raise() /usr/local/go/src/runtime/sys_linux_amd64.s:165 (PC: 0x454bc1)
    Warning: debugging optimized function
    Frame 5: ./main.go:21 (PC: 45b81a)
        16: }
        17:
        18: func Modify() {
        19:  a := "hello"
        20:  b := String2Bytes(a)
    =>  21:  b[0] = 'H'
        22: }
        23:
        24: func main() {
        25:  Modify()
        26: }
    (dlv)
    ```

这样就看到，是因为擅自修改了 string 类型变量的底层值引发的问题。

