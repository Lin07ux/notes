> 转摘：[plan9 assembly 完全解析](https://segmentfault.com/a/1190000039978109)

> 由于汇编本身的性质决定，本文所使用的平台是 Linux AMD64，因为不同的平台指令集和寄存器都不一样，不能共同讨论。

## 一、基本指令

### 1.1 栈调整

Intel 或 AT&T 汇编提供了`push`和`pop`指令族，Plan9 中虽然也有这两个指令，但一般生成的代码中是没有的，栈调整大都是通过对硬件 SP 寄存器进行运算来实现的。例如：

```asm
SUBQ $0x18, SP // 对硬件 SP 做减法，为函数分配函数栈帧
...            // 省略无关的代码
ADDQ $0x18, SP // 对硬件 SP 做加法，清除函数栈帧
```

### 1.2 数据搬运

常数在 Plan9 汇编中使用`$num`格式表示，可以为负数，默认情况下为十进制。也可以使用`$0x`开头来表示十六进制数。

如：

```asm
MOVB $1,    DI  // 1 byte
MOVW $0x10, BX  // 2 bytes
MOVD $1,    DX  // 4 bytes
MOVQ $-10,  AX  // 8 bytes
```

可以看到，搬运的长度是由`MOV`指令的后缀决定的，这一点与 Intel 汇编稍有不同。下面是 x64 汇编：

```asm
mov rax, 0x1   // 8 bytes
mov eax, 0x100 // 4 bytes
mov ax,  0x22  // 2 bytes
mov ah,  0x33  // 1 byte
mov al,  0x44  // 1 byte
```

而且，Plan9 的汇编的操作数的方向是和 Intel 汇编相反的，但与 AT&T 类似：

```asm
MOVQ $0x10, AX ===== mov rax, 0x10
       |     |____________|    |
       |_______________________|
```

> 不过也总是有例外的，可参加[Go assembly language complementary reference](https://quasilyte.dev/blog/post/go-asm-complementary-reference/#external-resources)。

### 1.3 常见计算指令

常见的计算指令主要是加减乘法，如下：

```asm
ADDQ  AX, BX // BX += AX
SUBQ  AX, BX // BX -= AX
IMULQ AX, BX // BX *= AX
```

类似数据搬运指令，同样可以通过修改指令的后缀来对应不同长度的操作数，例如：`ADDQ/ADDW/ADDL/ADDB`。

### 1.4 跳转指令

跳转指令分为无条件跳转指令和有条件跳转指令，如下：

```asm
// 无条件跳转
JMP addr   // 跳转地址，地址可以为代码中的地址，不过实际上手写的汇编不会出现这种代码
JMP label  // 跳转到标签，可以跳转到同一函数内的标签位置
JMP 2(PC)  // 以当前指令为基础，向前跳转 x 行
JMP -2(PC) // 以当前指令为基础，向后跳转 x 行

// 有条件跳转
JZ  target // 如果 zero 标志位被设置，则跳转到指定位置
```

### 1.5 地址运算

地址运算指令是`LEA`，表示 Load Effective Address。在 AMD64 平台上，地址都是 8 个字节，所以直接使用`LEAQ`即可。

示例如下：

1. `LEAQ (BX)(AX*8), CX` 这是常见的寄存器地址运算的方式，它将 BX 和 AX 寄存器的值相加的和作为地址存入到 CX 寄存器中。
    
    这里的`8`表示扩增比例 scale，而且 sacle 只能是 0、1、2、4、8，如果写成其他值就会报错。比如：`LEAQ (BX)(AX*3), CX`在编译的时候会提示`bad scale: 3`的错误。
    
    如果想将两个寄存器的值直接想将，不进行扩增，也必须提供 scale，此时 scale = 1，也就是需要写成`LEAQ (BX)(AX*1), CX`。而写成`LEAQ (BX)(AX), CX`则会报`bad address 0/2064/2067`这样的错误。

2. `LEAQ 16(BX)(AX*1), CX` 在寄存器运算的基础上，可以加上额外的 offset。

    当然，也可以直接是一个寄存器加上额外的 offset，比如`LEAQ 16(BX), CX`。
    
    但是不能用三个寄存器进行运算，如果写成`LEAQ DX(BX)(AX*8), CX`就会报错：`excepted end of operand, found (`。

使用`LEAQ`的好处比较明显：可以节省指令数。如果用基本算术指令来实现`LEAQ`的功能，需要两三条以上的计算指令才能实现完整的功能。

### 1.6 指令集

Go 支持的 Plan9 的指令集可以参考源码中的[arch](https://github.com/golang/arch/blob/master/x86/x86.csv)部分。

另外，Go 1.10 添加了大量的 SIMD 指令支持，所以在该版本之后不需要人肉填 byte 了，编写汇编会更简单一些。

## 二、寄存器

### 2.1 通用寄存器

AMD64 的通用寄存器如下：

```asm
(lldb) reg read
General Purpose Registers:
       rax = 0x0000000000000005
       rbx = 0x000000c420088000
       rcx = 0x0000000000000000
       rdx = 0x0000000000000000
       rdi = 0x000000c420088008
       rsi = 0x0000000000000000
       rbp = 0x000000c420047f78
       rsp = 0x000000c420047ed8
        r8 = 0x0000000000000004
        r9 = 0x0000000000000000
       r10 = 0x000000c420020001
       r11 = 0x0000000000000202
       r12 = 0x0000000000000000
       r13 = 0x00000000000000f1
       r14 = 0x0000000000000011
       r15 = 0x0000000000000001
       rip = 0x000000000108ef85
    rflags = 0x0000000000000212
        cs = 0x000000000000002b
        fs = 0x0000000000000000
        gs = 0x0000000000000000
```

这些寄存器在 Plan9 汇编中都是可以使用的，应用代码层面会用到的通用寄存器主要是：`rax`、`rbx`、`rcx`、`rdx`、`rdi`、`rsi`、`r8~r15`这 14 个寄存器。

虽然`rbp`和`rsp`这两个寄存器也可以用，但是因为`bp`和`sp`会被用来管理栈顶和栈底，所以最好不要拿来进行运算。

Plan9 中使用寄存器不需要带前缀`r`或`e`，例如`rax`只需要写成`AX`即可：

```asm
MOVQ $101, AX = mov rax, 101
```

下面是通用寄存器的名称在 x64 和 Plan9 中的对应关系：

|  x64  |  Plan9  |
|:-----:|:-------:|
| rax   | AX      |
| rbx   | BX      |
| rcx   | CX      |
| rdx   | DX      |
| rdi   | DI      |
| rsi   | SI      |
| rbp   | BP      |
| rsp   | SP      |
| r8    | R8      |
| r9    | R9      |
| r10   | R10     |
| r11   | R11     |
| r12   | R12     |
| r13   | R13     |
| r14   | R14     |
| rip   | PC      |

### 2.2 伪寄存器

Go 的汇编还引入了 4 个伪寄存器，官方文档的描述如下：

* `FP`: Frame pointer: arguments and locals.
* `PC`: Program counter: jumps and branches.
* `SB`: Static base pointer: global symbols.
* `SP`: Stack pointer: top of stack.

官方的猫叔稍微有一些问题，对此进行一些扩充说明：

* `FP`：使用形如`symbol+offset(FP)`的方式引入函数的输入参数。

    使用示例：`arg0+0(FP)`、`arg1+8(FP)`。
    
    使用 FP 时不加 symbol 无法通过编译。在汇编层面来讲，symbol 并没有什么用，加上 symbol 主要是为了提升代码的可读性。
    
    另外，官方文档虽然将伪寄存器 FP 称之为 Frame Pointer，但实际上它并不是 Frame Pointer。假如当前的 callee 函数是`add`，在`add`的代码中引用 FP 时，该 FP 指向的位置不在 callee 的 stack frame 之内，而是在 caller 的 stack frame 中。具体可参见后面的**栈结构**一章。
    
* `PC`：实际上就是在体系结构的知识中常见的 PC 寄存器。

    该伪寄存器在 x86 平台上对应的是 IP 寄存器，在 AMD64 上则是 rip 寄存器。
    
    除了个别跳转之外，手写 Plan9 代码与 PC 寄存器打交道的情况较少。
    
* `SB`：全局静态基指针，一般用来声明函数或全局变量，在之后的函数知识和示例部分会看到具体用法。

* `SP`：Plan9 中的这个 SP 伪寄存器指向当前栈帧的局部变量的开始位置。

    该寄存器用来使用形如`symbol+offset(SP)`订单方式引用函数的局部变量，`offset`的合法取值范围是`[-framesize, 0)`，左闭右开。
    
    假如局部变量都是 8 字节，那么第一个局部变量就可以用`localvar0-8(SP)`来表示。
    
    这也是一个词不表意的寄存器，与硬件寄存器 SP 是两个不同的东西。只有在栈帧的 size 为 0 的情况下，伪寄存器 SP 和硬件寄存器 SP 指向同一个位置。
    
    手写汇编代码时，如果是`symbol+offset(SP)`形式，则表示伪寄存器 SP；如果是`offset(SP)`则表示硬件寄存器 SP。务必注意：对于编译输出(`go tool compile -S`或`go tool objdump`)的代码来讲，目前所有的 SP 都是指硬件 SP，无论是否带有 symbol。

这里比较容易混淆的地方有：

1. 伪 SP 和硬件 SP 不是一回事。在手写代码时，伪 SP 和硬件 SP 的区分方法就是看该 SP 前面是否有 symbol：如果有 symbol 那么就是伪 SP，否则就是硬件 SP。
2. SP 和 FP 的相对位置是会变的，所以不应该尝试用伪 SP 寄存器去找那些应 FP + offset 来引用的值，例如函数的入参和返回值。
3. 官方文档中说的伪 SP 指向 stack 的 top，应该理解为栈顶（除了 caller 的 BP 之外）。因为栈是从大向小的地址增长的，所以也可以理解是在底部。
4. 在编译和反汇编的结果中，只有真实的 SP，而且没有 FP 伪寄存器。也就是说，在`go tool objdump`和`go tool compile -S`输出的代码中，是没有伪 SP 和 FP 寄存器的。上面说的区分伪 SP 和硬件 SP 寄存器的方法，对于这两个命令的输出结果是没有办法使用的。
5. FP 和 Go 的官方源代码里的 Frame Pointer 不是一回事，源代码里的 Frame Pointer 指的是 caller BP 寄存器的值，在这里和 caller 的伪 SP 值是相等的。

## 三、变量声明

在汇编里所谓的变量，一般是存储在`.rodata`或者`.data`段中的只读值。对应到应用层的话，就是已初始化过的全局的`const`、`var`、`static`变量/常量。

### 3.1 变量声明指令

定义一个变量会用到`DATA`和`GLOBL`指令：

* `DATA`：该指令的用法为`DATA symbol+offset(SB)/width, value`。

    这里的`offset`是指该值相对于符号 symbol 的偏移，而不是相对于全局某个地址的偏移。一般来说，在声明变量时，其`offset`一般都是 0。
    
* `GLOBL`：该指令将变量声明为 global，用法为`GLOBL divtab(SB), flag, size`。

    其中，`flag`参数表示该变量的属性修饰，其取值是固定的几种；`size`则表示变量的总大小。

### 3.2 声明示例

**`GLOBL`指令必须跟在`DATA`指令之后。**下面是一个定义了多个 readonly 的全局变量的完整例子：

```asm
DATA  age+0x00(SB)/4, $18
GLOBL age(SB), RODATA, $4

DATA  pi+0(SB)/8, $3.1415926
GLOBL pi(SB), RODATA, $8

DATA  birthYear+0(SB)/4, $1988
GLOBL birthYear(SB), RODATA, $4
```

如果想要在全局变量中定义数组或字符串，就需要用上非 0 的 offset 了。例如：

```asm
DATA  bio<>+0(SB)/8, $"oh yes i"
DATA  bio<>+8(SB)/8, $"am here "
GLOBL bio<>(SB), RODATA, $16
```

这里引入了一个新的标记**`<>`：这个标记跟在符号名之后，表示该全局变量只在当前文件中生效**，类似于 C 语言中的`static`。如果在另外的文件中引用改变量的话，会报错：`relocation target not found`。

### 3.3 GLOBL 的 flag

`GLOBL`指令支持的 flag 有如下的取值：

* `NOPROF = 1`

    > (For `TEXT` items.) Don't profile the marked function. This flag is deprecated.

* `DUPOK = 2`

    > It is legal to have multiple instances of this symbol in a single binary. The linker will choose one of the duplicates to use.

* `NOSPLIT = 4`

    > (For `TEXT` items.) Don't insert the preamble to check if the stack must be split. The frame for the routine, plus anything it calls, must fit in the space at the top of the stack segment. Used to protect routines such as the stack splitting code itself.

* `RODATA = 8`

    > (For `DATA` and `GLOBL` items.) Put this data in a read-only section.

* `NOPTR = 16`

    > (For `DATA` and `GLBOL` items.) This data contains no pointers and therefor does not need to be scanned by the garbage collector.

* `WRAPPER = 32`

    > (For `TEXT` items.) This is a wrapper function and should not count as disabling `recover`.

* `NEEDCTXT = 64`

    > (For `TEXT` items.) This function is a closure so it uses its incoming context register.

当使用这些`flag`的字面量时，需要在汇编文件中引入头文件`#inclue "textflag.h"`。

### 3.4 .s 和 .go 文件的全局变量互通

**在`.s`文件中是可以直接使用`.go`中定义的全局变量的**。示例如下：

refer.go 文件内容如下：

```go
package main

var a = 999
func get() int

func main() {
    println(get())
}
```

refer.s 文件内容如下：

```asm
#include "textflag.h"

TEXT ·get(SB), NOSPLIT, $0-8
    MOVQ ·a(SB), ax
    MOVQ AX, ret+0(FP)
    RET
```

其中，`·a(SB)`表示该符号需要链接器来进行重定向。如果找不到该符号，则会输出`relocation target not found`的错误。

> `·`是 Unicode 中的中点字符，在 Mac 中的输入方法是`Option + Shift + 9`。在程序被链接之后，所有的中点`·`都会被替换为英文句点`.`。

## 四、函数声明

### 4.1 声明指令

Plan9 中声明一个函数是通过`TEXT`指令来完成的。

> 因为代码是存储在二进制文件中的`.text`段中的，所以就按照约定俗称的起名方式命名为`TEXT`指令。

`TEXT`指令的用法如下：

```asm
TEXT pkgname·funcname(SB), flags, $framesize-argsize
```

其中：

* `pkgname` 表示包名，是可以省略的，省略时自动使用当前的 package 名称。而且建议不写，因为这样在修改当前 package 后不需要更新汇编中的 pkgname；
* `flags` 是一些标志位，可以参加前面的`GLOBL`指令的标识值，常用的是`NOSPLIT`标识；
* `framesize` 表示改方法的栈帧大小，包括当前方法的局部变量大小和可能需要的额外调用函数的参数空间大小，但是不包括调用其他函数时返回地址的大小；
* `argsize` 表示当前函数需要的全部参数和返回值的大小。

如下是一个典型的 Plan9 的汇编函数定义：

```asm
// func add(a, b int) int
//   => 该声明定义在同一个 package 下的任意 .go 文件中
//   => 只有函数头，没有实现体
TEXT pkgname·add(SB), NOSPLIT, $0-8
    MOVQ a+0(FP), AX
    MOVQ a+8(FP), BX
    ADDQ AX, BX
    MOVQ BX, ret+16(FP)
    RET
```

在程序被链接之后，由于中点`·`会被替换为句点`.`，所以`pkgname·add`就会变成`pkgname.add`，也就是在 Go 程序中调用方法的常见方式。

### 4.3 framesize 的计算规则

在函数声明中：

```asm
TEXT pkgname·add(SB), NOSPLIT, $16-32
```

16 表示的就是函数的 framesize，也就是这个函数在执行过程中所需的局部变量的空间和调用其他函数所需要的参数和返回值空间。

函数 framesize 的计算有些复杂，手写代码的 framesize 不需要考虑由编译器插入的 caller BP，需要考虑如下因素：

1. 每个局部变量的 size；

2. 在函数中有对其它函数调用时，需要将 callee 的参数、返回值考虑在内。

    虽然`return address(rip)`的值也是存储在 caller 的 stack frame 上的，但是这个过程是由`CALL`指令和`RET`指令完成 PC 寄存器的保存和恢复的。在手写汇编时，同样也是不需要考虑这个 PC 寄存器在栈上所需占用的 8 个字节的。
    
3. 原则上来说，调用函数时只要不把局部变量覆盖掉就可以了。稍微多分配几个字节的 framezise 也是没关系的。

4. 在确保逻辑没有问题的前提下，覆盖局部变量也是没有问题的，只要保证进入和退出汇编函数时的 caller 和 callee 能正确拿到返回值就可以。

### 4.3 argsize 的计算规则

在函数声明中：

```asm
TEXT pkgname·add(SB), NOSPLIT, $16-32
```

32 表示的是 argsize，也就是这个函数所需参数和返回值的空间大小。

Go 在函数调用时，参数和返回值都是需要由 Caller 在其栈帧上备好空间的。callee 在声明时仍然需要知道这个 argsize。

argsize 的计算方法是：参数大小 + 返回值大小。例如，入参是 3 个 int64 类型，返回值是 1 个 int64 类型，那么：argsize = 3 * sizeof(int64) + 1 * sizeof(int64) = 32 字节。

真实的情况肯定是更复杂的，函数参数和返回值往往混合了多种类型，还需要考虑内存对齐问题。

如果不确定字节的函数签名需要多大的 argsize，可以通过简单实现一个相同签名的空函数，然后使用`go tool objdump`来你想查找应该分配多少空间。

## 五、栈结构

### 5.1 结构总览

下面是一个典型的函数栈结构图：

```txt
+-------------------+
| current func arg0 |
|-------------------| <----------- FP(pseudo FP)                
|  caller ret addr  |
+-------------------+                                           
|   caller BP(*)    |
|-------------------| <----------- SP(pseudo SP，实际上是当前栈帧的 BP 位置)
|     Local Var0    |
|-------------------|
|     Local Var1    |
|-------------------|
|     Local Var2    |                                           
|-------------------|
|     ........      |                                           
|-------------------|
|     Local VarN    |                                           
|-------------------|
|                   |
|                   |
|     temporarily   |
|    unused space   |
|                   |
|                   |
|-------------------|
|    call retn      |
|-------------------|
|    call ret(n-1)  |
|-------------------|
|    ..........     |
|-------------------|
|     call ret1     |
|-------------------|
|     call argn     |                                           
|-------------------|
|       .....       |
|-------------------|
|     call arg3     |                                           
|-------------------|
|     call arg2     |
|-------------------|
|     call arg1     |                                           
|-------------------| <------------  hardware SP 位置           
|    return addr    |
+-------------------+
```

### 5.2 相关说明

**return addr**

从原理上来讲，如果当前函数调用了其他函数，那么其`return addr`也是在 caller 的栈上的。不过往栈上插入`return addr`的过程是由`CALL`指令来完成的，在`RET`的时候，SP 又会恢复到图上位置。在计算 SP 和参数相对位置时，可以认为硬件 SP 指向的就是图上的位置。

**caller BP**

图中的`caller BP`指的是 caller 的 BP 寄存器值。把 caller BP 叫做 caller 的 frame pointer 的习惯是从 x86 架构沿袭过来的。Go 的 asm 文档中把伪寄存器 FP 也称为 frame pointer，但是这两个 frame pointer 根本不是一回事。

此外，还需要注意的是，caller BP 是在编译期由编译器插入的。用户手写代码时，计算 frame size 时是不包括这个 caller BP 部分的。

而是否插入 caller BP 的主要判断依据是：

1. 函数的栈帧大小大于 0；
2. `Framepointer_enabled()`函数返回 true。

`Framepointer_enabled()`函数代码如下：

```go
func Framepointer_enabled(goos, goarch string) bool {
    return framepointer_enabled != 0 && goarch == "arm64" && goos != "nacl"
}
```

如果编译器在最终的汇编结果中没有插入 caller BP（源代码中所称的 frame pointer）的请下，伪 SP 和伪 FP 之间只有 8 个字节的 caller 的 return address；而插入 BP 的话，就会多出额外的 8 字节。也就是说，伪 SP 和伪 FP 的相对位置是不固定的，有可能是间隔 8 个字节，也有可能间隔 16 个字节，并且判断依据会根据平台和 Go 的版本有所不同。

**FP**

从图中可以看到，**FP 伪寄存器指向函数的*传入参数*的开始位置**，因为栈是朝低地址方向增长，为了通过寄存器引用参数时方便，所以函数的参数的排布方向和栈的增长方向是相反的，即：

```
                              FP
high ----------------------> low
argN, ... arg3, arg2, arg1, arg0
```

假设所有参数均为 8 字节，这样就可以使用`argname+0(FP)`访问第一个参数，使用`argname+8(FP)`访问第二个参数，依次类推。

**SP**

同样的，用伪 SP 来引用局部变量，原理上来讲和伪 FP 一样。但是因为**伪 SP 指向的是*局部变量*的底部**，所以需要使用`localname-8(SP)`来访问第一个局部变量，使用`localname-16(SP)`访问第二个局部变量，依次类推。（当然，这里假设每个局部变量都占用 8 字节。）

**caller return address** 和 **current func arg0**

图中最上部的`caller return address`和`current func arg0`都是由 caller 来分配空间的，不算在当前的栈帧内。

### 5.3 全景图

因为官方文档本身比较模糊，下面展示一个函数调用的全景图，看一下这些 SP/FP/BP 的关系：

```txt
                              caller
                      +------------------+
                      |                  |
       +----------->  --------------------
       |              |                  |
       |              | caller parent BP |
       |              --------------------  BP(pseudo SP)
       |              |                  |
       |              |   Local Var0     |
       |              --------------------
       |              |                  |
       |              |   .......        |
       |              --------------------
       |              |                  |
       |              |   Local VarN     |
                      --------------------
caller stack frame    |                  |
                      |   callee arg2    |
       |              |------------------|
       |              |                  |
       |              |   callee arg1    |
       |              |------------------|
       |              |                  |
       |              |   callee arg0    |                        
       |              ----------------------------------------------+  FP(virtual register)
       |              |                  |                          |
       |              |   return addr    |  parent return address   |
       +----------->  +------------------+---------------------------  <-----------+
                                         |  caller BP               |              |
                                         |  (caller frame pointer)  |              |
                          BP(pseudo SP)  ----------------------------              |
                                         |                          |              |
                                         |     Local Var0           |              |
                                         ----------------------------              |
                                         |                          |              |
                                         |     Local Var1           |
                                         ----------------------------          callee stack frame
                                         |                          |
                                         |       .....              |              |
                                         ----------------------------              |
                                         |                          |              |
                                         |     Local VarN           |              |
                      SP(Real Register)  ----------------------------              |
                                         |                          |              |
                                         |                          |              |
                                         |                          |              |
                                         |                          |              |
                                         |                          |              |
                                         +--------------------------+  <-----------+
                                                  callee
```


