> 转摘：[腾讯：汇编是深入理解 Go 的基础](https://mp.weixin.qq.com/s/2JQM1piaWPQW-uwD_P-3Cg)

## 0. 为什么写本文

在深入你如学习 Golang 的 runtime 和标准库的实现的时候发现，如果对 Golang 汇编没有一定了解的话，很那深入了解其底层实现机制。在这里整理总结了一份基础的 Golang 汇编入门知识，通过学习之后能够对其底层实现有一定的认识。

> 本文使用 Go 版本为 Go 1.14.1。

## 1. 为什么需要汇编

众所周知，在计算机的世界里，只有 2 中类型，就是 0 和 1。

计算机工作是由一系列的机器指令进行驱动的，这些指令又是一组二进制数字，其对应计算机的高低电平。而这些机器指令的集合就是机器语言，这些机器语言在最底层是与硬件一一对应的。

显而易见，这样的机器指令有一个致命的缺点：可阅读性太差。为了解决可读性的问题以及代码编辑的需求，于是就诞生了最接近机器的语言：汇编语言。这更像是一种助记符，这些人们容易记住的每一条助记符都映射着一条不容易记住的由 0、1 组成的机器指令。类似于域名与 IP 地址的关系。

以 C 语言为例来说，从`hello.c`的原版马文静到 hello 的可执行文件，经过编译器处理，大致分为如下几个阶段：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634129766785-b6cc59830261.jpg)

编译器在不同的阶段会做不同的事情，但是有一步是可以确定的，那就是：源码会被编译成汇编代码，最后才是编译成二进制。

## 2. 程序与进程

源码经过编译之后，得到一个二进制的可执行*文件*。*文件*这两个字也就表明，目前得到的这个文件跟其他文件对比，除了是具有一定的格式（Linux 中是 ELF 格式，即：可运行可链接格式，executable linkable formate）的二进制组成，并没有什么区别。

在 Linux 中文件类型大致分为 7 种：

- `b` 块设备文件
- `c` 字符设备文件
- `d` 目录
- `-` 普通文件
- `l` 链接
- `s` socket
- `p` 管道

![](http://cnd.qiniu.lin07ux.cn/markdown/1634130014402-979ff9ceb4eb.jpg)

通过上面的示例可以看到，可执行文件`main`与源码文件`main.go`都是同一种类型，属于普通文件。那么：

1. 什么是程序？
2. 什么是进程？

### 2.1 程序

维基百科中：*程序*是指一组指示计算机或其他具有消息处理能力设备每一步动作的指令，通常用某种程序设计语言编写，运行于某种目标体系结构上。

从某个层面来看，可以把程序分为：

* 静态程序：单纯的指具有一定格式的可执行二进制文件；
* 动态程序：是静态可执行程序文件被加载到内存之后的一种运行时模型（又称为进程）。

### 2.2 进程

首先，要知道的是，*进程*是分配系统资源的最小单位，*线程*（带有时间片的函数）是系统调度的最小单位。进程包含线程，线程所属于进程。

创建进程一般使用 fork 方法（通常会有个拉起程序，先 fork 自身生成一个子进程。然后，在该子进程中通过`exec`函数把对应程序加载进来，进而启动目标进程。当然，实现上会复杂得多），而创建线程则是使用`pthread`线程库。

以 32 位 Linux 操作系统改为例，进程经典的虚拟内存结构模型如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634175923709-13352d48a6e7.jpg)

其中，有两处结构是静态程序所不具有的，那就是*运行时堆(heap)*与*运行时栈(stack)*。

* **运行时堆**：从低地址向高地址增长，申请的内存空间需要程序员自己或者 GC 释放。
* **运行时栈**：从高地址向低地址增长，内存空间在当前栈帧调用结束之后自动释放（并不是清除其所占用内存中的数据，而是通过栈顶指针 SP 的移动来标识哪些内存是正在使用的）。

## 3. Go 汇编

对于 Go 编译器而言，其输出的结果是一种抽象可移植的汇编代码，这种汇编（Go 的汇编是基于 Plan 9 的汇编）并不是对应某种真实的硬件架构，Go 的汇编器会使用这种伪汇编程序再为目标硬件生成具体的机器指令。

*伪汇编*这一额外层可以带来很多好处，最主要的一点是方便将 Go 移植到新的架构上。

> 相关信息可以参考 [A Quick Guide to Go's Assembler](https://golang.org/doc/asm)。

Go 汇编使用的是 *caller-save* 模式，就是被调用函数的入参参数、返回值都是由调用者维护、准备的。因此，当需要调用一个函数时，需要先将这些工作准备好，才进行调用。也因此 Go 函数支持返回多个值。另外，这些操作都是需要进行内存对其的，对齐的大小是`sizeof(uintptr)`。

### 3.1 几个概念

在深入了解 Go 汇编之前，需要知道的几个概念：

* stack：栈，进程、线程、goroutine 都有自己的调用栈，先进后出（FILO）。
* stack frame：栈帧，可以理解是函数调用时，在栈上为函数所分配的内存区域。
* caller：调用者，比如，A 函数调用了 B 函数，那么 A 就是调用者。
* callee：被调者，比如，A 函数调用了 B 函数，那么 B 就是被调者。

### 3.2 Go 的核心伪寄存器

Go 汇编中有 4 个核心的伪寄存器：`SB`、`FP`、`PC`、`SP`，这 4 个寄存器是编译器用来维护上下文、特殊标识等作用的。如下图是一个调用链上栈空间和伪寄存器的内存模型示意图：

> 在大多数架构上，栈空间都是从高地址向低地址生长的，也就是先分配的空间比后分配的空间的栈地址更大。

![栈帧和伪寄存器内存模型示意图](http://cnd.qiniu.lin07ux.cn/markdown/1634180079682-c1d98dfd77c2.jpg)

在分析编译输出的汇编代码时，要重点看 SP、SB 寄存器（FP 寄存器在这里是看不到的）；在手写汇编代码中，要重点看 FP、SP 寄存器。

下面结合这个内存模型示意图来分别讲解这 4 个核心伪寄存器。

#### 3.2.1 FP

**FP**：帧指针，可以用来定位参数、返回值和本地变量。它总是指向当前函数所使用的第一个参数的栈地址。

在 Go 汇编代码中，可以使用`symbol+offset(FP)`的方式来引用 callee 函数的入参参数。使用 FP 必须加`symbol`，否则无法通过编译器编译，如：`arg0+0(FP)`引用第一个参数`arg1`、`arg1+8(FP)`引用第二个参数`arg2`。

> 从汇编层面来看，`symbol`并没有什么用，加`symbol`主要是为了提升代码可读性。

编写 Go 汇编代码时，要站在 callee 的角度来看 FP；但是 FP 指向的位置是在 caller 中分配的。也就是：FP指向的位置是在 caller 的栈帧中，而不是在 callee 的栈帧中。而且，caller 会按照参数列表逆序为其分配栈空间，所以 callee 的第一个参数在栈上是处于参数列表的最下面，也即是其栈地址最小，可以看上面栈帧内存模型示意图。

另外，由于

#### 3.2.2 SP

**SP** 寄存器分为伪 SP 寄存器和硬件 SP 寄存器。

SP 伪寄存器指向当前栈帧第一个局部变量的结束位置（为什么说是结束位置，可以看上面的寄存器内存布局图）。可以通过形如`symbol+offset(SP)`的方式，引用函数的局部变量。其中，offset 的合法取值是`[-framesize, 0)`，注意，这是一个左开右闭区间。例如：如果局部变量是 8 字节的，那么第一个局部变量就可以使用`localVar0-8(SP)`来表示。

硬件寄存器 SP 指向的是当前栈帧的顶部（也就是最小的地址）。在栈帧空间大小为 0 的情况下，伪寄存器 SP 和硬件寄存器 SP 指向的是同一位置。手写汇编代码时，如果是`symbol+offset(SP)`形式，则表示 SP 伪寄存器；如果是`offset(SP)`则表示 SP 硬件寄存。

> 务必注意：对于`go toll compile -S / go tool objdump`编译输出的汇编代码来说，所有的 SP 都是硬件 SP 寄存器，无论是否带有`symbol`。这一点非常具有迷惑性，需要慢慢理解。往往在分析编译输出的汇编时，看到的就是硬件 SP 寄存器。

#### 3.2.3 SB

**SB** 是全局静态基指针，一般用在声明函数、全局变量的地方。

#### 3.2.4 PC

**PC** 实际上就是在体系结构的知识中常见的 PC 寄存器，在 x86 平台下对应 IP 寄存器，在 AMD64 上则是 RIP 寄存器除了个别跳转之外，手写 Go 汇编代码时，很少会用到 PC 寄存器。

#### 3.2.5 其他说明

函数调用的返回地址`return addr`也是分配在 caller 的栈上的，是由 CALL 指令来完成往栈上写入`return addr`的。在分析汇编时，是看不到关于调用返回地址相关哦空间的信息的。在分配占空间时，调用返回地址所占用空间大小不包含在栈帧大小内。

在 AMD64 环境中，PC 伪寄存器其实是 IP 指令计数器寄存器的别名；FP 伪寄存器对应的是 caller 函数的帧指针，一般用来访问 callee 函数的入参参数和返回值；SP 伪寄存器对应的是当前 callee 函数栈帧的地步（不包括参数和返回值部分），一般用于定位局部变量；SP 硬件寄存器对应的是栈的顶部。

SP 伪寄存器和 FP 伪寄存器的相对位置是会变的，所以不应该尝试用 SP 伪寄存器去使用那些用 FP+offset 来引用的值，如函数的入参和返回值。

还有一些通用寄存也可以在 Go 汇编语言中使用，但是最好不要使用 bp(rbp) 和 sp(rsp) 寄存器，因为它们会被用来管理栈顶和栈底。

在 Go 汇编中使用寄存器粗需要带有`r`或`e`的前缀，如`rax`可以直接写为`AX`：`MOVQ $101, AX`相当于`mov rax, 101`。

下面是一些通用寄存器的名字在 IA64 和 Go 汇编中的对应关系：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634184385380-67419540206f.jpg)

### 3.3 常用操作指令

下面是一些常用的 Go 汇编指令（指令后缀`Q`说明是 64 位上的汇编指令）：

  助记词   |  指令种类  |  用途   |  示例
:--------:|:---------:|:------ | -------------------------------------
  MOVQ    | 传送      | 数据传送 | `MOVQ 48, AX` 把数值 48 放到 AX 寄存器中
  LEAQ    | 传送      | 地址传送 | `LEAQ AX, BX` 把 AX 寄存器数据的有效地址传送到 BX 中
  PUSHQ   | 传送      | 栈压入   | `PUSHQ AX` 把 AX 寄存器的内容压入到栈顶位置
  POPQ    | 传送      | 栈弹出   | `POPQ AX` 弹出栈顶数据到 AX 寄存器中并修改栈顶指针
  ADDQ    | 运算      | 相加并赋值 | `ADDQ BX, AX` 等价于`AX += BX`
  SUBQ    | 运算      | 相减并赋值 | `SUBQ BX, AX` 等价于`AX -= BX`
  CMPQ    | 运算      | 比较大小   | `CMPQ SI, CX` 比较 SI 和 CX 的大小
  CALL    | 转移      | 调用函数   | `CALL runtime.println(SB)` 发起函数调用
  JMP     | 转移      | 无条件转移 | `JMP 0x0185` 无条件跳转到`0x0185`地址处
  JLS     | 转移      | 条件转移   | `JLS 0x0185` 左边小于右边，则跳转到`0x0185`地址处

## 4. 汇编分析


### 4.1 如何输出 Go 汇编

对于写好的 Go 源码，生成对应的 Go 汇编代码，有如下几种方式：

1. `go tool compile -S -N -l main.go` 直接输出汇编代码
2. `go build -gcflags="-S -N -l" main.go` 直接输出汇编代码
3. `go build -gcflags="_N -l" main.go && go tool objdump -s "main\."main` 先得到可执行二进制文件，再反编译获取对应的汇编。

使用这些命令时，可以加上对应的 flag，否则一些逻辑会被编译器优化掉，看不到对应的完整的汇编代码：

* `-l` 禁止内联
* `-N` 禁止编译优化
* `-S` 输出汇编代码

### 4.2 Go 汇编示例

Go 源码如下：

```go
package main

func add(a, b int) int {
	sum := 0 // 不设置该局部变量，add 函数的栈空间大小会是 0
	sum = a + b
	return sum
}

func main() {
	println(add(1, 2))
}
```

然后使用命令得到对应的汇编代码：

```shell
go tool compile -N -S -l main.go
```

其对应的栈帧空间模型如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634202302484-81da6a6b6adb.jpg)

> 由于 Go 汇编使用的是 caller-save 模式，所以`add()`函数的返回值、参数、栈位置都由调用者进行准备，相关的空间都位于调用者(`main`函数)的栈空间中。

#### 4.2.1 add 函数

先来看`add()`函数对应的汇编代码：

```asm
"".add STEXT nosplit size=60 args=0x18 locals=0x10 funcid=0x0
	0x0000 00000 (main.go:3)	TEXT   "".add(SB), NOSPLIT|ABIInternal, $16-24
	0x0000 00000 (main.go:3)	SUBQ   $16, SP
	0x0004 00004 (main.go:3)	MOVQ   BP, 8(SP)
	0x0009 00009 (main.go:3)	LEAQ   8(SP), BP
	0x000e 00014 (main.go:3)	FUNCDATA   $0, gclocals·33cdeccccebe80329f1fdbee7f5874cb(SB)
	0x000e 00014 (main.go:3)	FUNCDATA   $1, gclocals·33cdeccccebe80329f1fdbee7f5874cb(SB)
	0x000e 00014 (main.go:3)	MOVQ   $0, "".~r2+40(SP)
	0x0017 00023 (main.go:4)	MOVQ   $0, "".sum(SP)
	0x001f 00031 (main.go:5)	MOVQ   "".a+24(SP), AX
	0x0024 00036 (main.go:5)	ADDQ   "".b+32(SP), AX
	0x0029 00041 (main.go:5)	MOVQ   AX, "".sum(SP)
	0x002d 00045 (main.go:6)	MOVQ   AX, "".~r2+40(SP)
	0x0032 00050 (main.go:6)	MOVQ   8(SP), BP
	0x0037 00055 (main.go:6)	ADDQ   $16, SP
	0x003b 00059 (main.go:6)	RET
...
```

* `TEXT "".add(SB), NOSPLIT|ABIInternal, $16-24`

    这行代码中，首先使用`TEXT`指令在`.text`代码段中声明了`"".add`方法，且在这个声明后的是函数的函数体内容。在链接期，`""`这个空字符会被替换为当前的包名，也就是说，链接器会将`"".add`替换成`main.add`。

    > 需要注意的是：如果是手写代码，那么`"".add`需要写成`""·add`，因为句点`.`在 Go 汇编中有其他含义，需要使用中句点`·`(Mac 上可以使用`Shift + Option + 9`键入)来代替。

    `(SB)`中的 SB 是前面提到的伪寄存器，保存静态基地址(static base)指针，也就是程序地址空间的开始地址。而`"".add(SB)`就表明`add`函数位于相对地址空间起始处的某个固定偏移位置（最终是由链接器计算得到的）。换句话说，`"".add(SB)`就表示一个直接的绝对地址，在这个地址中定义了全局函数`add`。

    `NOSPLIT` 标志用来向编译器表明不应该插入用于检查栈是否需要扩张的 stack-split 前导指令。这个标志是由编译器自动为`add()`函数添加的，因为它检查到`add()`方法中没有任何局部变量，也没有自己的帧栈，而且也没有其他函数调用，所以一定不会超出当前的栈。不然，每次调用这个函数时，都要检查是否需要栈扩展就是完全浪费 CPU 时间了。

    `$16-24` 这两个数字分别用来标明`add()`函数所需要的栈帧大小（16 字节）和参数及返回值大小（24 字节）。注意，这里的`-`并不表示算术运算中的减法，而仅仅是一个连接符。由于 callee 的栈帧中需要保存 caller 的 BP 地址（8 字节），以及其全部的本地变量，所以`add()`函数的栈帧大小就是：`8(caller BP) + 8(local variable sum) = 16`。`add()`函数有两个 int 类型的参数，以及一个 int 类型的返回值，所以对应的参数+返回值的空间大小就是：`8(int a) + 8(int b) + 8(result) = 24`。

* `SUBQ $16, SP`

	这里的`SP`表示的硬件 SP 指针，这里将硬件 SP 指针的值减去 16 后赋值给 SP，表示生成 16 字节大小的栈帧空间。

	> 因为栈是从大地址向小地址方向生长，所以需要使用减法。

* `MOVQ BP, 8(SP)` 和 `LEAQ 8(SP), BP`

	前一条命令先将 caller(也就是`main`函数) 的 BP 值存放到 callee(也就是`add`函数)的帧栈的底部，然后将帧栈底部的栈地址赋值给 BP。从而能够完成 BP 的保存和重新设置。

* `FUNCDATA`

	这两个`FUNCDATA`代码是 Go 编译器自动添加的 GC 相关命令，当前可以先忽略。

	另外还有一个`PCDATA`也是由编译器自动添加的用于 GC 的命令。

* `MOVQ $0, "".~r2+40(SP)`

	初始化返回值的结果为 0（因为返回的是 int 类型，所以默认设置为 0）。这里返回地址的 symbol 是有汇编器自动生成的`~r2`，而偏移量之所以是 40，是因为 SP 指向的是栈顶，而当前 callee 的栈帧大小为 16 字节，所需参数是 16 字节，而 caller 还需要在调用参数上面保存调用之后的下一条指令的地址（占用 8 字节），相加即为`40 = 16 + 8 + 16`。

* `MOVQ $0, "".sum(SP)`

	初始化 callee 本地变量`sum`的值为 0，因为它也是 int 类型。

	如果 Go 源码中没有`sum := 0`，那么得到的汇编结果中就不会有这一条语句了。

* `MOVQ "".a+24(SP), AX` 和 `ADDQ "".b+32(SP), AX`

	这里就是完成入参`a`和`b`两者的和，并且存入到 AX 寄存器。

	参数`a`和`b`的偏移量的计算与返回值的地址的计算类似，所以就分别是`24 = 16 + 8`和`32 = 16 + 8 + 8`（参数`b`分配的地址会在参数`a`的后面，也即是更大一些）。

* `MOVQ AX, "".sum(SP)` 和 `MOVQ	AX, "".~r2+40(SP)`

	这两条语句是将前面计算的结果依次存入到本地变量`sum`和该函数返回值的栈空间中。

	可以看到，如果`add()`函数中不设置本地变量，而是直接将计算结果返回，那么就可以减少本地变量的栈分配、初始化和值设置，可以有更好的性能。

* `MOVQ 8(SP), BP`

	在函数体执行完成之后，会自动恢复调用者的 BP，也就是进入`add()`时在`8(SP)`保存的 BP 值。

* `ADDQ $16, SP`

	这行代码就是用来释放 callee 的栈空间的。因为`add()`函数的栈空间大小为 16 字节，而是栈是从大地址向小地址生长，所以释放栈空间就加上 16 字节即可。

* `RET`

	进行调用返回。编译器会自动插入后续的相关处理。

#### 4.2.2 main 函数

编译后，`main`函数对应的汇编代码如下（省略了一些不相关代码）：

```asm
"".main STEXT size=110 args=0x0 locals=0x28 funcid=0x0
	0x0000 00000 (main.go:9)	TEXT	"".main(SB), ABIInternal, $40-0
	... // init
	0x000f 00015 (main.go:9)	SUBQ	$40, SP
	0x0013 00019 (main.go:9)	MOVQ	BP, 32(SP)
	0x0018 00024 (main.go:9)	LEAQ	32(SP), BP
	... // FUNCDATA
	0x001d 00029 (main.go:10)	MOVQ	$1, (SP)
	0x0025 00037 (main.go:10)	MOVQ	$2, 8(SP)
	... // PCDATA
	0x002e 00046 (main.go:10)	CALL	"".add(SB)
	0x0033 00051 (main.go:10)	MOVQ	16(SP), AX
	0x0038 00056 (main.go:10)	MOVQ	AX, ""..autotmp_0+24(SP)
	... // println
	0x005d 00093 (main.go:11)	MOVQ	32(SP), BP
	0x0062 00098 (main.go:11)	ADDQ	$40, SP
	0x0066 00102 (main.go:11)	RET
	0x0067 00103 (main.go:11)	NOP
	...
```

`main`函数的对应的汇编代码主要分为两部分：调用`add()`函数、调用`println()`函数。

* `TEXT "".main(SB), ABIInternal, $40-0`

	为`main()`函数分配的栈帧大小是 40 字节，参数大小为 0 字节（因为`main()`函数没有参数返回值和返回值）。

	`main()`函数的栈帧包含如下内容（从高地址到低地址）：

	- `BP` 父函数（即`main`函数的 caller）的 BP 数据，8 字节；
	- `""..autotmp_0+24(SP)` println 函数的参数空间，8 字节（这部分在上面的内存模型示意图中缺失了）；
	- `16(SP)` add 函数的返回值空间，8 字节；
	- `8(SP)` add 函数的参数`b`的空间，8 字节；
	- `(SP)` add 函数的参数`a`的空间，8 字节。

* `SUBQ $40, SP`、`MOVQ BP, 32(SP)`、`LEAQ 32(SP), BP`

	这三行代码是为`main()`函数分配栈空间，并保存其 caller 的 BP 的，与`add()`函数的操作类似。

* `MOVQ $1, (SP)`、`MOVQ $2, 8(SP)`、`CALL "".add(SB)`

	这三行是初始化`add()`函数的参数`a`、`b`，然后调用函数`add()`。

* `MOVQ 16(SP), AX`、`MOVQ AX, ""..autotmp_0+24(SP)`

	这两行代码是用`add()`函数的返回值初始化`println()`函数的参数，为后续的`println`函数调用做准备。

* `MOVQ 32(SP), BP`、`ADDQ $40, SP`、`RET`

	这三行代码是对`main()`函数调用完成之后栈空间的释放和其他清理操作，与`add()`函数类似。

### 4.3 Go 汇编语法

#### 4.3.1 函数声明

Go 汇编中，函数的声明会使用`TEXT`来完成，表示将函数存储在进程/线程的`.text`段中。然后在`TEXT`命令行之后，使用更多的操作指令来实现函数体。

可以参考前面的`add()`函数的声明和汇编代码。

#### 4.3.2 变量声明

Go 汇编里的全局变量一般是存储在`.rodata`或`.data`段中。对应到 Go 代码，就是已经初始化过的全局的 const、var 常量/变量。

变量的声明可以使用`DATA`和`GLOBL`命令来实现。`DATA`的用法为：

```asm
DATA symbol+offset(SB)/width, value
```

这里的参数都是其字面意思，不过要注意**`offset`：其含义是该值相对于符号`symbol`的偏移，而不是相对于全局某个地址的偏移**。

`GLOBL`汇编指令用于定义名为`symbol`的全局变量，其语法如下：

```asm
GLBOL ·symbol(SB), width
```

下面是定义了多个变量的例子：

```asm
DATA ·age+0(SB)/4, $8         ;; 定义一个变量 age，值为 8，内存宽度为 4 bytes
GLOBL ·age(SB), RODATA, $4    ;; 将变量 age 声明为全局的只读变量，内存宽度为 4 bytes

DATA ·pi+0(SB)/8, $3.1415926  ;; 定义一个浮点型变量 pi，值为 3.1415926，内存宽度为 8 bytes
GLBOL ·pi(SB), RODATA, $8     ;; 将变量 pi 声明为全局的只读变量，内存宽度为 8 bytes

DATA ·year+0(SB)/4, $2021     ;; 定义一个变量 year，值为 2021，内存宽度为 4 bytes
GLBOL ·year(SB), $4           ;; 将变量 year 声明为全局变量，内存宽度为 4 bytes

;; 变量 hello 使用 2 个 DATA 来定义
DATA ·hello+0(SB)/8, $"hello my"  ;; `hello my` 共8个字节
DATA ·hello+8(SB)/8  $"   world"  ;; `   world` 共8个字节(3个空格)
GLOBL ·hello(SB), RODATA, $16     ;; `hello my   world`  共16个字节

;; 变量 helloDup 使用 2 个 DATA 来定义
DATA ·helloDup+0(SB)/8, $"hello my"  ;; `hello my` 共8个字节
DATA ·helloDup+8(SB)/8  $"   world"  ;; `   world` 共8个字节(3个空格)
GLOBL ·helloDup<>(SB), RODATA, $16     ;; `hello my   world`  共16个字节
```

声明变量的时候，如果在标识符后面使用了`<>`标记符号，则表示该全局变量只在当前文件中生效，类似于 C 语言中的`static`。如果在其他的文件中引用改变量的话，则会报`relocation target not found`的错误。

## 5. 手写汇编代码

在 Go 源码中会看到一些汇编写的代码，这些代码在跟其他 Go 代码一起组成了整个 Go 的底层功能实现。

下面通过一个简单的 Go 汇编代码实例来实现两数相加功能。

### 5.1 使用 Go 汇编实现 add 函数

首先新建一个文件夹，如：

```shell
mkdir demo
cd demo
```

然后在文件`main.go`中编写 Go 源码：

```asm
package main

// 仅声明函数 add，不具体实现（使用 Go 汇编实现）
func add(a, b int64) int64

func main() {
	println(add(1, 2))
}
```

再在文件`add_asm.s`文件中用 Go 汇编实现`add()`函数：

```asm
TEXT ·add(SB), $0-24    ;; add 栈空间为 0，入参+返回值大小为 24 字节
    MOVQ x+0(FP), AX    ;; 从 main 中取参数 1
    ADDQ y+8(FP), AX    ;; 从 main 中去参数 2
    MOVQ AX, ret+16(FP) ;; 保存结果到返回值
    RET

```

> 注意：这里的注释都要去掉，并在文件最后保留一个空行，不包含任何字符（可以有空格）。

最后，将 Go 源码与汇编源码编译到一起：

```shell
go build -gcflags "-N -l" .
```

由于是在`demo`文件中编译的，所以会得到一个名为`demo`的可执行文件，执行之后就可以得到结果`3`。

### 5.2 反编译可执行程序

对上面得到的可执行文件`demo`使用 objdump 进行反编译，获取汇编代码：

```shell
go tool objdump -s "main\." demo
```

> 这里的`"main\."`是为了将符合`main.`的函数汇编源码进行输出，可以排除一些非相关函数的汇编代码的输出。

得到的汇编结果类似如下：

```asm
...
TEXT main.main(SB) /Users/lin07ux/code/go/src/github.com/lin07ux/learn/demo/main.go
  main.go:5		0x105e180		65488b0c2530000000	MOVQ GS:0x30, CX
  main.go:5		0x105e189		483b6110		CMPQ 0x10(CX), SP
  main.go:5		0x105e18d		7658			JBE 0x105e1e7
  main.go:5		0x105e18f		4883ec28		SUBQ $0x28, SP
  main.go:5		0x105e193		48896c2420		MOVQ BP, 0x20(SP)
  main.go:5		0x105e198		488d6c2420		LEAQ 0x20(SP), BP
  main.go:6		0x105e19d		48c7042401000000	MOVQ $0x1, 0(SP)
  main.go:6		0x105e1a5		48c744240802000000	MOVQ $0x2, 0x8(SP)
  main.go:6		0x105e1ae		e84d000000		CALL main.add(SB)
  main.go:6		0x105e1b3		488b442410		MOVQ 0x10(SP), AX
  main.go:6		0x105e1b8		4889442418		MOVQ AX, 0x18(SP)
  main.go:6		0x105e1bd		0f1f00			NOPL 0(AX)
  main.go:6		0x105e1c0		e8bb08fdff		CALL runtime.printlock(SB)
  main.go:6		0x105e1c5		488b442418		MOVQ 0x18(SP), AX
  main.go:6		0x105e1ca		48890424		MOVQ AX, 0(SP)
  main.go:6		0x105e1ce		e8ad10fdff		CALL runtime.printint(SB)
  main.go:6		0x105e1d3		e8680bfdff		CALL runtime.printnl(SB)
  main.go:6		0x105e1d8		e82309fdff		CALL runtime.printunlock(SB)
  main.go:7		0x105e1dd		488b6c2420		MOVQ 0x20(SP), BP
  main.go:7		0x105e1e2		4883c428		ADDQ $0x28, SP
  main.go:7		0x105e1e6		c3				RET
  main.go:5		0x105e1e7		e894b2ffff		CALL runtime.morestack_noctxt(SB)
  main.go:5		0x105e1ec		eb92			JMP main.main(SB)
...

TEXT main.add(SB) /Users/lin07ux/code/go/src/github.com/lin07ux/learn/demo/add_asm.s
  add_asm.s:2		0x105e200		488b442408		MOVQ 0x8(SP), AX
  add_asm.s:3		0x105e205		4803442410		ADDQ 0x10(SP), AX
  add_asm.s:4		0x105e20a		4889442418		MOVQ AX, 0x18(SP)
  add_asm.s:5		0x105e20f		c3				RET
```

可以看到，反编译得到的汇编与`add_asm.s`文件中的汇编操作大致相同，只是表达方式不同：

1. `FP` 伪寄存器在编写 Go 汇编代码时会使用，指向 caller 传递给 callee 的第一个参数；
2. 使用`go toll compile / go tool objdump`得到的汇编代码中，自动将 FP 伪寄存器改为了 SP 硬件寄存器的相对偏移地址。
