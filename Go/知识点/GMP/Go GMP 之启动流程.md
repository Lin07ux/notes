> 转摘：[详解 Go 程序的启动流程，你知道 g0，m0 是什么吗？](https://mp.weixin.qq.com/s/YK-TD3bZGEgqC0j-8U6VkQ)

任何一个 Go 程序，启动过程中都会包含对应的基础环境设置，涉及到 Go runtime 的调度器启动、go/m0 的启动等。

下面以最简单的 Go 程序代码来介绍下 Go 程序的启动流程：

```go
import "fmt"

func main() {
  fmt.Println("hello world")
}
```

## 一、Go 引导阶段

### 1.1 查找入口

对上面的 Go 程序进行编译：

```shell
GOFLAGS="-ldflags=-compressdwarf=false" go build
```

> 这里设定了 GOFLAGS 参数，因为从 Go 1.1 起，为了减少二进制文件的大小，调试信息会被压缩。这会导致在 MacOS 上使用 gdb 时无法理解压缩的 DWARF 的含义是什么。因此需要在本次调试中将其关闭。

使用 gdb 进行调试：

```
$ gdb awesomeProject 
(gdb) info files
Symbols from "/Users/eddycjy/go-application/awesomeProject/awesomeProject".
Local exec file:
 `/Users/eddycjy/go-application/awesomeProject/awesomeProject', file type mach-o-x86-64.
 Entry point: 0x1063c80
 0x0000000001001000 - 0x00000000010a6aca is .text
 ...
(gdb) b *0x1063c80
Breakpoint 1 at 0x1063c80: file /usr/local/Cellar/go/1.15/libexec/src/runtime/rt0_darwin_amd64.s, line 8.
```

根据输出的 Entry point 进行调试，可以看到真正的程序入口在 runtime 包中。在不同的计算机架构中入口指向的文件不同，例如：

* MacOS 架构中会指向`src/runtime/rt0_darwin_amd64.s`
* Linux 架构中会指向`src/runtime/rt0_linux_amd64.s`

> 最终指向的文件名字中的`rt0`是 runtime0 的缩写，指代运行时的创世；`darwin`指代的是目标操作系统为 MacOS（GOOS）；`amd64`代表目标操作系统架构 64 位系统（GOHOSTARCH）。
>
> 而且 Go 语言还支持更多的系统架构，比如 AMD64、ARM、MIPS、WASM 等，每种架构都会对应不同的入口文件。可以在`src/runtime`目录中进行查看。

### 1.2 入口方法

在`rt0_linux_amd64.s`文件中，可以看到`_rt0_adm64_darwin`直接跳转到了`_rt0_adm64`方法：

```asm
TEXT _rt0_amd64_darwin(SB),NOSPLIT,$-8
  JMP _rt0_amd64(SB)
  ...
```

而`_rt0_amd64`方法会将程序输入的 argc 和 argv 从内存移动到寄存器中后，再次跳转到`runtime.rt0_go`：

```asm
TEXT _rt0_amd64(SB),NOSPLIT,$-8
  MOVQ 0(SP), DI   // argc
  LEAQ 8(SP), SI   // argv
  JMP  runtime.rt0_go(SB)
```

这里栈指针 SP 的前两个值分别表示的是 argc 和 argv，其对应参数的数量和具体各参数的值。

### 1.3 开启主线

程序参数准备就绪后，正式初始化的方法落在了`runtime.rt0_go`方法中：

```asm
TEXT runtime.rt0_go(SB),NOSPLIT,$0
  ...
  CALL runtime·check(SB) // 类型检查
  MOVL 16(SP), AX   // copy argc
  MOVL AX, 0(SP)
  MOVQ 24(SP), AX   // copy argv
  MOVQ AX, 8(SP)
  CALL runtime.args(SB)       // 系统参数转换
  CALL runtime.osinit(SB)     // 系统参数设置
  CALL runtime.schedinit(SB)  // 运行时组件初始化
  
  // create a new goroutine to start program
  MOVQ $runtime.mainPC(SB), AX // entry
  PUSHQ AX
  PUSHQ $0  // arg size
  CALL runtime.newproc(SB)
  POPQ AX
  POPQ AX
  
  // start this M
  CALL runtime.mstart(SB)
  ...
```

`runtime.rt0_go`方法中，主要是完成各类运行时的检查、系统参数设置和获取、并进行大量的 Go 基础组件初始化，主要包含如下步骤：

* `runtime.check` 运行时类型检查，主要是校验编译器的翻译工作是否正确，是否有“坑”。其代码主要逻辑为检查`int8`在`unsafe.Sizeof`方法下是否已等于 1 这类操作。
* `runtime.args` 系统参数传递，主要是将系统参数转换传递给程序使用。
* `runtime.osinit` 系统基本参数设置，主要是获取 CPU 核心数和内存物理页大小。
* `runtime.schedinit` 进行各种运行时组件的初始化，包含调度器、内存分配器、堆、栈、GC 等一大堆初始化工作。会进行 P 的初始化，并将 m0 和某一个 P 进行绑定。
* `runtime.main` 主要工作是运行 main goroutine。虽然在`runtime.rt0_go`方法中指向的是`$runtime.mainPC`，但实质指向的是`runtime.main`。
* `runtime.newproc` 创建一个新的 goroutine，且绑定`runtime.mian`方法（也就是应用程序中的入口 main 方法），并将其放入 m0 绑定的 P 的本地队列中去，以便后续的调度。
* `runtime.mstart` 启动 m，调度器开始进行循环调度。

初始化完毕后，就是进行主协程 main goroutine 的运行，并放入等待队列（GMP 模型），最后调度器开始进行循环调度。

### 1.4 小结

根据上述源码剖析，可以得出如下的 Go 应用程序引导的流程图：

![Go 程序引导过程](http://cnd.qiniu.lin07ux.cn/markdown/1647237745095-3078b5c49eba.jpg)

在 Go 语言中，实际的运行入口并不是用户日常编写的 main func，更不是 runtime.main 方法，而是从`rt0_*_*.s`开始，最终再一路 JMP 到 runtime.rt0_go 方法中，再在该方法中完成一系列的 Go 自身所需要完成的绝大部分初始化动作。

## 二、Go 调度器初始化

下面再了解下 Go runtime 中调度器是怎么流转的。

### 2.1 runtime.mstart

调度器的流转主要是从`runtime.mstart`方法开始的：

```go
func mstart() {
  // 获取 g0
  _g_ := getg()
  
  // 确定栈边界
  osStack := _g_.stack.lo == 0
  if osStack {
    size := _g_.stack.hi
    if size == 0 {
      size = 8192 * sys.StackGuardMultiplier
    }
    _g_.stack.hi = uintptr(noescape(unsafe.Pointer(&size)))
    _g_.stack.lo = _g_.stack.hi - size + 1024
  }
  _g_.stackguard0 = _g_.stack.lo + _StackGuard
  _g_.stackguard1 = _g_.stackguard0
  
  // 启动 m 进行调度器循环调度
  mstart1()
  
  // 退出线程
  if mStackIsSystemAllocated() {
    osStack = true
  }
  mexit(osStack)
}
```

这个方法做的事情也很简单：

* 调用`getg()`方法获取 GMP 模型中的 g，此处获取到的是 g0。
* 通过检查 g 的执行栈`_g_.stack`的边界（堆栈的边界正好是 lo、hi）来确定是否为系统栈。如果是的话，则根据系统栈初始化 g 执行栈的边界。
* 调用`mstart1()`方法启动系统线程 m，进行调度器循环调度。
* 调用`mexit()`方法退出系统线程 m。

可以看到，调度器相关的实质逻辑是在`mstart1()`方法中的。

### 2.2 runtime.mstart1

源码如下：

```go
func mstart1() {
  // 获取 g 并判断是否为 g0
  _g_ := getg()
  if _g_ != _g_.m.g0 {
    throw("bad runtime·mstart")
  }
  
  // 初始化 m 并记录调用方的 pc、sp
  save(getcallerpc(), getcallersp())
  asminit()
  minit()
  
  // 当前 g 绑定的是 m0 则设置信号监听器
  if _g_.m == &m0 {
    mstartm0()
  }
  
  // 如果 g 绑定了启动函数则运行启动函数
  if fn := _g_.m.mstartfn; fn != nil {
    fn()
  }
  
  // 如果当前 g 绑定的 m 不是 m0 则需要调用 acquirep 方法获取并绑定 P
  if _g_.m != &m0 {
    acquirep(_g_.m.nextp.ptr())
    _g_.m.nextp = 0
  }
  
  // 正式开启调度
  schedule()
}
```

这里如果当前的 g 如果不是 g0，则要抛出致命错误，因为调度器仅在 g0 上运行。

另外，设置信号监听器的`mstartm0`方法需要在`asminit`方法之后调用，这样可以提前准备好线程，以便能够处理信号。

前面做了这么多的处理和准备，都是为了最终通过`runtime.schedule()`来做真正的调度。

## 三、问题剖析

前面的这些代码中会经常出现`g0`和`m0`，它们本质上也是 G 和 M，但是也有一些特点。

### 3.1 m0

`m0`是 Go runtime 所创建的第一个系统线程，也叫做主线程。一个 Go 进程只有一个`m0`。它具有如下特点：

* 数据结构：`m0`和其他的 M 没有任何区别；
* 变量声明：`m0`和常规 M 一样，就是`var m0 m`这种方式创建的；
* 创建过程：`m0`是 Go 进程在启动时由汇编赋值设置数据的，其他后续的 M 都是在 Go runtime 内自行创建的。

### 3.2 g0

G 一般分为三种：

* 执行用户任务的 G；
* 执行`runtime.main`的 G，一般叫做 main goroutine；
* 执行调度任务的 G，叫做`g0`。

也就是说，`g0`比较特殊，它是每次启动一个 M 时第一个创建的 goroutine，不指向任何可执行的函数，仅负责 G 的调度。每个 M 都会绑定一个自己的`g0`，且只绑定一个`g0`。而且，全局变量的`g0`是`m0`上绑定的`g0`。

`g0`具有如下特点：

* 数据结构：`g0`和其他创建的 G 在数据结构上是一样的，但是存在栈的差别；
* 变量声明：`g0`和常规的 G 的声明方式一样，都是`var g0 g`这样的方式；
* 创建过程：`g0`也是通过汇编进行赋值的，其他的 G 的创建和赋值都是 Go runtime 内做的；
* 运行状态：`g0`和常规的 G 不一样，没有那么多种运行状态，也不会被调度程序抢占，调度本身就是在`g0`上运行的。

