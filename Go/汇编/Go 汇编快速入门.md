> [A Quick Guide to Go's Assembler](https://golang.org/doc/asm)

## 1. A Quick Guide to Go's Assembler

This document is a quick outline of the unusual form of assembly language used by the *gc* Go compiler. The document is not comprehensive.

本文档简要的概述了 Go 编译器`gc`所使用的汇编语言形式，该形式与寻常所用汇编语言很不相同，这个文档并非全面详尽的介绍。

The assembler is based on the input style of the Plan 9 assemblers, which is documented in detail [elsewhere](). If you plan to write assembly language, you should read that document although much of it is Plan 9-specific. The current document provides a summary of the syntax and the differences with what is explained in that document, and describes the peculiarities that apply when writing assembly code to interact with Go.

该汇编语言形式是基于 [Plan 9 汇编语言](https://9p.io/sys/doc/asm.html)的输入形式。如果你打算使用汇编语言编写程序，你应该仔细阅读其文档，虽然它是针对 Plan 9 汇编语言的。本文档提供了 Go 汇编语言的语法摘要和其与 Plan 9 汇编语言的不同，并描述了在编写与 Go 交互的汇编代码时适用的特性。

The most important thing to know about Go's assembler is that it is not a direct representation of the underlying machine. Some of the details map precisely to the machine, but some do not. This is because the compiler suite(see [this description](https://9p.io/sys/doc/compiler.html)) needs no assembler pass in the usual pipeline. Instead, the compiler operates on a kind of semi-abstract instruction set, and instruction selection occurs partly after code generation. The assembler works on the semi-abstract form, so when you see an instruction like MOV what the toolchain actually generates for that operation might not be a instruction at all, perhaps a clear or load. Or it might correspond exactly to the machine instruction with that name. In general, machine-specific operations tend to appear as themselves, while more general concepts like memory move and subroutine call and return are more abstract. The details vary with architecture, and we apologize for the imprecision; the situation is not well-defined.

**关于 Go 汇编器，最重要的一点是它并非是底层机器码的直接表示**。一些指令信息能够刚好的对应着底层机器码，但是另一些则不会。这是因为 Plan 9 的编译器并不会像通常的汇编器那样接收通用汇编程序。相反，该编译器会抵用一种半抽象的指令集，并且部分具体的指令选择会在字节码生成之后进行。因为汇编器使用的是半抽象汇编语言，所以在使用 Go 工具链生成的结果中，一个指令可能与同名的机器指令相对应，也有可能完全不相同。比如，对于`MOV`指令，其可能并不是表示真实的移动指令，也有可能是清除或者加载指令。一般来说，特定于机器的操作往往表现为它们本身的意思，而其他更多的常用概念，如内存移动和子程序调用及返回，则表现的更加抽象。因机器架构的不同而会出现细节差异较大，对于描述不精确的地方，我们深表歉意，因为场景无法很好的进行明确定义。

The assembler program is a way to parse a description of that semi-abstract instruction set and turn it into instructions to be input to the linker. If you want to see what the instruction look like in assembly for a given architecture, say amd64, there are many examples in the sources of the standard library, in packages such as [runtime](https://golang.org/pkg/runtime/) and [math/big](https://golang.org/pkg/math/big/). You can also examine what the complier emits as assembly code(the actual output may differ from what you see here):

汇编程序是一种理解该半抽象指令集并将其转换为链接器输入指令的方法。Go 标准库中有很多示例，可以让你查看特定 CPU 架构（如 AMD64）中汇编指令是什么样的，比如 [runtime](https://golang.org/pkg/runtime/) 库和 [math/big](https://golang.org/pkg/math/big/) 库。也可以直接使用编译器将 Go 程序编译成汇编代码进行查看（实际输出会因 Go 编译器版本等原因与下面的示例不完全相同）：

```shell
$ cat x.go
package main

func main() {
	println(3)
}
$ GOOS=linux GOARCH=amd64 go tool compile -S x.go # or: go build -gcflags -S x.go
"".main STEXT size=74 args=0x0 locals=0x10
	0x0000 00000 (x.go:3)	TEXT    "".main(SB), $16-0
	0x0000 00000 (x.go:3)	MOVQ    (TLS), CX
	0x0009 00009 (x.go:3)	CMPQ    SP, 16(CX)
	0x000d 00013 (x.go:3)	JLS     67
	0x000f 00015 (x.go:3)	SUBQ    $16, SP
	0x0013 00019 (x.go:3)	MOVQ    BP, 8(SP)
	0x0018 00024 (x.go:3)	LEAQ    8(SP), BP
	0x001d 00029 (x.go:3)	FUNCDATA     $0, gclocals·33cdeccccebe80329f1fdbee7f5874cb(SB)
	0x001d 00029 (x.go:3)	FUNCDATA     $1, gclocals·33cdeccccebe80329f1fdbee7f5874cb(SB)
	0x001d 00029 (x.go:3)	FUNCDATA     $2, gclocals·33cdeccccebe80329f1fdbee7f5874cb(SB)
	0x001d 00029 (x.go:4)	PCDATA  $0, $0
	0x001d 00029 (x.go:4)	PCDATA  $1, $0
	0x001d 00029 (x.go:4)	CALL    runtime.printlock(SB)
	0x0022 00034 (x.go:4)	MOVQ    $3, (SP)
	0x002a 00042 (x.go:4)	CALL    runtime.printint(SB)
	0x002f 00047 (x.go:4)	CALL    runtime.printnl(SB)
	0x0034 00052 (x.go:4)	CALL    runtime.printunlock(SB)
	0x0039 00057 (x.go:5)	MOVQ    8(SP), BP
	0x003e 00062 (x.go:5)	ADDQ    $16, SP
	0x0042 00066 (x.go:5)	RET
	0x0043 00067 (x.go:5)	NOP
	0x0043 00067 (x.go:3)	PCDATA  $1, $-1
	0x0043 00067 (x.go:3)	PCDATA  $0, $-1
	0x0043 00067 (x.go:3)	CALL    runtime.morestack_noctxt(SB)
	0x0048 00072 (x.go:3)	JMP     0
...
```

The FUNCDATA and PCDATA directives contain information for use by the garbage collector; they are introduced by the compiler.

这里的`FUNCDATA`和`PCDATA`指令包含了用于垃圾回收的相关信息，它们是由编译器自动添加进去的。

To see what gets put in the binary after linking, use`go tool objdump`.

要查看链接之后生成的二进制文件的信息，可以使用`go tool objdump`命令。

```shell
$ go build -o x.exe x.go
$ go tool objdump -s main.main x.exe
TEXT main.main(SB) /tmp/x.go
  x.go:3		0x10501c0		65488b0c2530000000    MOVQ GS:0x30, CX
  x.go:3		0x10501c9		483b6110              CMPQ 0x10(CX), SP
  x.go:3		0x10501cd		7634                  JBE 0x1050203
  x.go:3		0x10501cf		4883ec10              SUBQ $0x10, SP
  x.go:3		0x10501d3		48896c2408            MOVQ BP, 0x8(SP)
  x.go:3		0x10501d8		488d6c2408            LEAQ 0x8(SP), BP
  x.go:4		0x10501dd		e86e45fdff            CALL runtime.printlock(SB)
  x.go:4		0x10501e2		48c7042403000000      MOVQ $0x3, 0(SP)
  x.go:4		0x10501ea		e8e14cfdff            CALL runtime.printint(SB)
  x.go:4		0x10501ef		e8ec47fdff            CALL runtime.printnl(SB)
  x.go:4		0x10501f4		e8d745fdff            CALL runtime.printunlock(SB)
  x.go:5		0x10501f9		488b6c2408            MOVQ 0x8(SP), BP
  x.go:5		0x10501fe		4883c410              ADDQ $0x10, SP
  x.go:5		0x1050202		c3	                   RET
  x.go:3		0x1050203		e83882ffff            CALL runtime.morestack_noctxt(SB)
  x.go:3		0x1050208		ebb6                  JMP main.main(SB)
```

## 2. Constants

Although the assembler takes its guidance from the Plan 9 assemblers, it is a distinct program, so there are some differences. One is in constant evaluation. Constant expressions in the assembler are parsed using Go's operator precedence, not the C-like precedence of the original. Thus `3&1<<2` is `4`, not `0` - it parse as `(3&1)<<2` not `3&(1<<2)`. Also, constants are always evaluated as 64-bit unsigned integers. Thus -2 is not the integer value minus two, but the unsigned 64-bit integer with the same bit pattern. The distinction rarely matters but to avoid ambiguity, division or right shift where the right operand's high bit is set is rejected.

尽管 Go 的编译器是基于 Plan 9 编译器进行开发的，但它依旧是一个独立的程序，所以它们之间是不同的。其中一个不同之处就是常量设置。汇编中的常量表达式在解析的时候会优先使用 Go 的操作符优先级，而不是 C 语言中优先级。因此，`3&1<<2`的值为 4 而不是 0，因为在 Go 语言中，它是被作为`(3&1)<<2`来解释，而不是`3&(1<<2)`。并且，常量总是被处理成 64 位无符号整数。因此，`-2`不是整数值负 2，而是具有相同位模式的无符号 64 位整数。区别很小，但是为了避免歧义，在除法和右移运算中，如果右操作数设置了高位，这是会被拒绝的。

## 3. Symbols

Some symbols, such as R1 or LR, are predefined and refer to registers. The exact set depends on the architecture.

汇编中的一些符号是预定义，用于表示特定的寄存器，比如`R1`、`LR`，但是它们具体的指定是与 CPU 架构相关的。

There are four predeclared symbols that refer to pseudo-registers. These are not real registers, but rather virtual registers maintained by the toolchain, such as a frame pointer. The set of pseudo-registers is the same for all architectures:

有 4 个预定义的是符号指代的是伪寄存器，它们并非真实的 CPU 寄存器，而是由 Go 编译工具链定义和维护的虚拟寄存器，比如帧指针。这些虚拟寄存器对所有的 CPU 架构都是相同的：

* FP: Frame pointer: arguments and locals. 帧指针，指向参数和本地变量。
* PC: Program counter: jumps and branches. 程序计数器，用于跳转和分支。
* SB: Static base pointer: global symbols. 静态基础指针，用于全局符号。
* SP: Stack pointer: the highest address within the local stack frame. 栈指针，指向当前栈帧的底部（高地址）。

All user-defined symbols are written as offset to the pseudo-registers FP (arguments and locals) and SB (globals).

所有用户自定义的符号都被伪寄存器`FP`和`SB`的偏移地址进行代替。

### 3.1 SB reigister

The SB pseudo-register can be thought of as the origin of memory, so the symbol `foo(SB)` is the name `foo` as an address in memory. This form is used to name global functions and data. Adding `<>` to the name, as in `foo<>(SB)`, makes the name visible only in the current source file, like a top-level `static` declaration in a C file. Adding an offset to the name refers to that offset from the symbol's address, so `foo+4(SB)` is four bytes past the start of `foo`.

SB 伪寄存器可以被当做内存的引用，所以`foo(SB)`就表示`foo`变量在内存中的地址。这种方式用于定义全局函数和数据。为名称添加`<>`符号（如`foo<>(SB)`），会使得这个名称仅能在当前的源文件中可见，类似于 C 文件中顶层的`static`声明的作用。为名称增加一个偏移，可以指代从这个名称地址偏移指定数值的地址，因此`foo+4(SB)`表示的是`foo`起始地址后面的 4 字节。

### 3.2 FP register

The FP pseudo-resister is a virtual frame pointer used to refer to function arguments. The compilers maintain a virtual frame pointer and refer to the arguments on the stack as offsets from that pseudo-register. Thus `0(FP)` is the first argument to the function, `8(FP)`is the second (on a 64-bit machine), and so on. However, when referring to a function argument this way, it is necessary to place a name at he beginning, as in `first_arg+0(FP)` and `second_arg+8(FP)`. (the meaning of the offset-offset from the frame pointer-distinct from its use with SB, where it is an offset from the symbol.) The assembler enforces this convention, rejecting plain `0(FP)` and `8(FP)`. The actual name is semantically irrelevant but should be used to document the argument's name. It is worth stressing that FP is always a pseudo-register, not a hardware register, even on architectures with a hardware frame pointer.

FP 伪寄存器是一个用于引用函数参数的虚拟帧指针。编译器维护着一个虚拟的帧指针，并使用该伪寄存器的偏移解析为栈上函数的参数的引用。因此，在 64 位架构上，`0(FP)`表示函数的第一个参数，`8(FP)`表示函数的第二个参数，以此类推。当然，不论如何，使用 FP 来引用函数参数的时候，需要将参数的名称放在 FP 偏移的开头处，例如：`first_arg+0(FP)`和`second_arg+8(FP)`（FP 指针的偏移的含义与 SB 指针的偏移的含义不同，表示的并非相对标识符的偏移）。Go 汇编器强制执行该约定，不会接受纯`0(FP)`和`8(FP)`方式的引用。虽然标识符的名称在语义上无关紧要，但是还是应该用来描述参数的意义。需要强调的是：FP 总是一个伪寄存器，而不是硬件寄存器，即便在有硬件帧指针的架构上也是如此。

For assembly functions with Go prototypes, `go vet` will check that the argument names and offsets match. On 32-bit systems, the low and high 32 bits of a 64-bit value are distinguished by adding a `_lo` or `_hi` suffix to the name, as in `arg_lo+0(FP)` or `arg_hi+4(FP)`. If a Go prototype does not name its result, the expected assembly name is `ret`.

对于 Go 原型的汇编函数，`go vet`将检查参数名称与对应的偏移量是否匹配。在 32 位的系统上，64 位的值的低 32 位和高 32 位使用`_lo`和`_hi`进行区分，例如：`arg_lo+0(FP)`、`arg_hi+4(FP)`。如果 Go 原型没有对其返回结果进行命名，那么预期的汇编程序中其名称会为`ret`。


### 3.3 SP register

The SP pseudo-register is a virtual stack pointer used to refer to frame-local variables and the arguments being prepared for function calls. It points to the highest address within the local stack frame, so references should use negative offsets in the range [-framesize, 0): x-8(SP), y-4(SP), and so on.

SP 伪寄存器是一个虚拟的栈指针，用于引用栈上函数的局部变量以及为函数调用准备的参数数据。它指向的是函数本地变量栈的最高地址，所以引用本地变量时应该使用负的偏移量，便宜量大小在`[-framesize, 0)`之间，不包括 0。例如：`x-8(SP)`、`y-4(SP)`。

On architectures with a hardware register named SP, the name prefix distinguishes references to the virtual stack pointer from references to the architectural SP register. That is, x-8(SP) and -8(SP) are different memory locations: the first refers to the virtual stack pointer pseudo-register, while the second refers to the hardware's SP register.

在具有硬件 SP 寄存器的架构上，符号的前缀是区分虚拟栈指针和硬件栈指针方式。比如，`x-8(SP)`和`-8(SP)`指向的是不同的内存地址：前者使用的是虚拟栈指针伪寄存器，而后者使用的是硬件 SP 寄存器。

On machines where SP and PC are traditionally aliases for a physical, numbered register, in the Go assembler the names SP and PC are still treated specially; for instance, references to SP require a symbol, much like FP. TO access the actual hardware register use the true R name. For example, on the ARM architecture the hardware SP and PC are accessible as R13 and R15.

在 SP 和 PC 通常被作为具有编号的物理寄存器的别名的机器上，GO 汇编程序中依旧会特殊对待 SP 和 PC  寄存器。例如：引用 SP 时需要和引用 FP 一样提供一个标识符，而要访问实际的物理寄存器时，需要使用其实际的以`R`开头的名称。比如，在 ARM 架构上，物理 SP 和 PC 寄存器需要使用`R13`和`R15`来访问。

### 3.4 PC register

Branches and direct jumps are always written as offsets to the PC, or as jumps to labels:

分支和直接跳转常会被写为 PC 的偏移量，或者具体的标签名称。

```asm
label:
    MOVW $0, R1
    JMP  label
```

Each label is visible only within the function in which it is defined. It is therefore permitted for multiple functions in a file to define and use the same label names. Direct jumps and call instructions can target text symbols, such as `name(SB)`, but not offsets from symbols, such as `name+4(SB)`.

每一个标签都只能在其定义的函数中可见，所以可以在一个文件中的多个方法中定义和使用具有相同名称的标签。直接跳转和调用指定可以指向文本标识符（比如`name(SB)`），但不能指向标识符的偏移量（比如`name+4(SB)`）。

Instructions, registers, and assembler directives are always in UPPER CASE to remind you that assembly programming is a fraught endeavor. (Exception: the `g` register renaming on ARM.)

指令、寄存器和汇编指令总是大写的，用于提醒你汇编变成是一项艰巨的工作。有一个例外：ARM 架构上`g`寄存器重命名。

### 3.5 period and slash

In Go object files and binaries, the full name of a symbol is the package path followed by a period and the symbol name: `fmt.Printf` or `math/rand.Int`. Because the assembler's parser treats period and slash as punctuation, those strings cannot be used directly as identifier names. Instead, the assembler allows the middle dot character U+00B& and the division slash U+2215 in identifiers and rewrites them to plain period and slash. Within an assembler source file, the symbols above are written as `fmt·Printf` and `math/rand·Int`. The assembly listings generated by the compilers when using the `-S` flag show the period and slash directly instead of the Unicode replacements required by the assemblers.

在 Go 的目标文件和文件之文件中，一个符号的全名为由句点分隔的包路径和符号名称组成，比如：`fmt.Printf`、`math/rand.Int`。因为在汇编器的解析中，句点和斜线会做为语法符号对待，所以标识符名称中不能直接使用句点和斜线。作为替代，汇编器允许在标识符中使用中间句点(`·`，Mac 上使用`Shift + Option + 9`可以输出)`U+00B7`和分隔斜线`U+2215`，并在后续重写成正常的句点和斜线。在汇编源码文件中，上面的示例符号会被写为`fmt·Printf`和`math/rand·Int`。不过，编译器在使用`-S`选项时生成的汇编代码列表中是直接显示句点和斜线的，而不是汇编器所需的 Unicode 替代码。

Most hand-written assembly files do not include the full package path in symbol names, because the linker inserts the package path of the current object file at the beginning of any name starting with a period: in an assembly source file within the math/rand package implementation, the package's Int function can be referred to as `·Int`. The convention avoids the need to hard-code a package's import path in its own source code, making it easier to move the code from on location to another.

大多数手写的汇编代码中并不会在标识符中包含完整的包路径，因为链接器会自动在标识符的前面添加上当前文件的包路径和句点。例如：在`math/rand`包的汇编实现源码文件中，包中的`Int`函数可以通过`·Int`来引用。这种约定可以避免将包的导入路径硬编码到包的源文件中，从而使得代码的迁移变动变的很简单。

## 4. Directives

### 4.1 TEXT

The assembler uses various directives to bind text and data to symbol names. For example, here is a simple complete function definition. The TEXT directive declares the symbol `runtime·profileloop` and the instructions that follow form the body of the function. The last instruction in a TEXT block must be some sort of jump, usually a RET (pseudo-)instruction. (If it's not, the linker will append a jump-to-itself instruction; there is no fallthrough in TEXTs.) After the symbol, the arguments are flags (see below) and the frame size, a constant (but see below):

汇编器会使用多个指令来将文本和数据绑定到标识符名称上。比如，下面是一个简单但是完整的函数定义。其中，`TEXT`指令声明了一个标识符`runtime·profileloop`，已经下面的组成该函数体的指令。`TEXT`块中的最后一条指令必须是某种跳转指令——一般回事`RET`（伪）指令。如果最后一条指令不是跳转指令，链接器会附加一条跳转到其自身的指令，`TEXT`块不允许代码穿透。在`TEXT`指令只会是函数参数的标识选项和帧大小（一个常量值）：

```asm
TEXT runtime·profileloop(SB), NOSPLIT, $8
    MOVQ   $runtime.profileloop1(SB), CX
    MOVQ   CX, 0(SP)
    CALL   runtime.externalthreadhandler(SB)
    RET
```

In the general case, the frame size is followed by an argument size, separated by a minus sign. (It's not a subtraction, just idiosyncratic syntax.) The frame size `$24-8` states that the function has a 24-byte frame and is called with 8 bytes of argument, which live on the caller's frame. If NOSPLIT is not specified for the TEXT, the argument size must be provided. For assembly functions with Go prototypes, `go vet` will check that the argument size is correct.

一般而言，帧空间大小后面会跟随着参数空间大小，两者使用`-`减号分隔（这个分隔符并非是减号的含义，而仅仅是一个独特的语法）。例如，`$24-8`表示的是这个函数有 24 字节的帧空间，并且有 8 字节的调用参数空间——参数存放在调用者的帧空间内。参数空间大小是可选的，但是如果没有在 TEXT 中指定 **NOSPLIT**，那么参数空间大小就必须要指定。对于 Go 原型汇编函数，`go vet`将会检查参数空间大小是否正确。

Note that the symbol name uses a middle dot to separate the components and is specified as an offset from the static base pseudo-register SB. This function would be called from Go source for package `runtime` using the simple name `profileloop`.

注意，这里的标识符名称中使用了中句点来进行分隔，并且指定为静态文本断伪寄存器 SB 的偏移量。在 Go 的源码文件中，会在`runtime`包中使用`profileloop`名称来调用该方法。

### 4.2 GLOBAL and DATA

Global data symbols are defined by a sequence of initializing DATA directives followed by a GLOBL directive. Each DATA directive initializes a section of the corresponding memory. The memory not explicitly initialized is zeroed. The general form of the DATA directive is:

全局数据是通过在一系列`DATA`指令定义数据后，添加`GLOBL`指令来声明的。每一个`DATA`指令都会初始化合法内存的一部分，未显示初始化的内存都被设置为零值。常见的`DATA`指令形式如下：

```asm
DATA symbol+offset(SB)/width, value
```

which initializes the symbol memory at the given offset and width with the given value. The DATA directives for a given symbol must be written with increasing offsets.

这会在给定的偏移量内存中初始化一个标识符号，并将其设置为给定的宽度。`DATA`指令声明的标识符的偏移量必须是依次递增的。

The GLOBL directive declares a symbol to be global. The arguments are optional flags and the size of the data being declared as a global, which will have initial value all zeros unless a DATA directive has initialized it. The GLOBL directive must follow any corresponding DATA directives.

`GLOBL`指令用于将一个标识符声明为全局性的。该指令的参数是由一些可选的参数和其数据的宽度组成。除非已经通过`DATA`指令将其初始化过，否则`GLOBL`指令声明的该标识符会被初始化为零值。`GLOBL`指令必须跟随在任何有效的`DATA`指令之后。

For Example,

例如：

```asm
DATA divtab<>+0x00(SB)/4, $0xf4f8fcff
DATA divtab<>+0x04(SB)/4, $0xe6eaedf0
...
DATA divtab<>+0x3c(SB)/4, $0x81828384
GLOBL divtab<>(SB), RODATA, $64

GLOBL runtime·tlsoffset(SB), NOPTR, $4
```

declares and initializes `divtab<>`, a read-only 64-byte table of 4-byte integer values, and declares `runtime·tlsoffset`, a 4-byte, implicitly zeroed variable that contains no pointers.

这段汇编代码声明并初始化了`divtab<>`全局变量：一个只读的 64 字节的列表，每个元素均为 4 字节的整型数值。另外，最后一行还声明了一个 4 字节的`runtime·tlsoffset`变量，但是并没有对其初始化，其值被默认设置为零，也就是不指向任何数据。

There may be one or two arguments to the directives. If there are two, the first is a bit mask of flags, which can be written as numeric expressions, added or or-ed together, or can be set symbolically for easier absorption by a human. 

`GLOBL`指令可以有一个或两个参数。如果提供了两个参数，则第一个参数是一个标志位掩码，可以写成相加或位或组合的数字表达式，也可以使用定义好的特定符号表示，以便更好的阅读和理解。

### 4.3 flags

Their values, defined in the standard `#include` file `textflag.h`, are:

这些特定的符号和值定义在标准的`#include`文件`textflag.h`中：

* `NOPROF = 1` 

    (For TEXT items.) Don't profile the marked function. This flag is deprecated.

    该标志已废弃。用于`TEXT`指令，指定不需要对标记的函数进行分析。

* `DUPOK = 2`

    It is legal to have multiple instances of this symbol in a single binary. The linker will choose one of the duplicates to use.
    
    该标志可以使一个标识符在一个单独的二进制文件中存在多个实例，而链接器会自动选取其中的一个来使用。
    
* `NOSPLIT = 4`

    (For TEXT items.) Don't insert the preamble to check if the stack must be split. The frame for the routine, plus anything it calls, must fit in the spare space remaining in the current stack segment. Used to protect routines such as the stack splitting code itself.
    
    用于`TEXT`指令。指示汇编器不要在此处插入检查栈是否必须拆分的标识符。每个子例程的所需的帧空间，加上所有它调用的内容，必须能够适配当前栈的剩余空间。这个指令用于保护子例程避免被栈拆分处理。
    
* `RODATA = 8`

    (For DATA and GLOBL items.) Put this data in a read-only section.
    
    适用于`DATA`和`GLOBL`指令，使数据成为只读数据。
    
* `NOPTR = 16`

    (For DATA and GLOBL items.) This data contains no pointers and therefor does not need to be scanned by the garbage collector.
    
    适用于`DATA`和`GLOBL`指令。表明该数据没有指向任何数据，因此也无需被 GC 进行扫描处理。
    
* `WRAPPER = 32`

    (For TEXT items.) This is a wrapper function and should not count as disabling `recover`.
    
    适用于`TEXT`指令。表示这是一个包装函数，不应该被统计到禁用恢复数据中。
    
* `NEEDCTXT = 64`

    (For TEXT items.) This function is a closure so it uses its incoming context register.
    
    适用于`TEXT`指令。表示这是一个闭包函数，所以它需要使用定义它的上下文寄存器。
    
* `LOCAL = 128`

    This symbol is local to the dynamic shared object.
    
    指示标识符是动态共享对象中的本地标识符。
    
* `TLSBSS = 256`

    (For DATA and GLOBL items.) Put this data in thread local storage.

    适用于`DATA`和`GLOBL`指令。用于将数据存储在线程的存储空间中。

* `NOFRAME = 512`

    (For TEXT items.) Do not insert instructions to allocate a stack frame and save/restore the return address, even if this is not a leaf function. Only valid on functions that declare a frame size of 0.
    
    适用于`TEXT`指令。指示编译器不要插入申请栈帧和保存/恢复返回值地址的指令，即便这个函数不是叶子函数。只有当这个函数声明时指定的帧空间大小为 0 时该标识才合法。
    
* `TOPFRAME = 2048`

    (For TEXT items.) Function is the outermost frame of the call stack. Traceback should stop at this function.
    
    适用于`TEXT`指令。表示当前方法是调用栈中最顶级的一帧。回溯时遇到该函数应该停止。
    
## 5. Interacting with Go types and constants

If a package has any .s files, then `go build` will direct the compiler to emit a special header called `go_asm.h`, which the .s file can then `#include`. The file contains symbolic `#define` constants for the offsets of Go struct fields, the sizes of Go struct types, and most Go `const` declarations defined in the current package. Go assembly should avoid making assumptions about the layout of Go types and instead use these constants. This improves the readability of assembly code, and keep it robust to changes in data layout either in the Go type definitions or in the layout rules used by the Go compiler.

如果包中含有任何的`.s`文件，当执行`go build`命令的时候，编译器会自动的添加一个`go_asm.h`的头，这样`.s`文件就能被`#include`引用了。`go_asm.h`文件中包含一些标识符的`#define`常量定义，用于表示当前包中 Go struct 字段的偏移量、Go struct 类型的大小、大多数的 Go 常量定义。Go 汇编程序中应避免对 Go 类型结构布局做出假设，而是使用这些常量定义。这样大大提高了汇编程序代码的可读性，并且在因 Go 类型定义发生变更或 Go 编译器排布规则发生变更导致数据结构布局发生变化时，依旧能够保证程序的健壮性。

Constants are of the form `const_name`. For example, given the Go declaration `const bufSize = 1024`, assembly code can refer to the value of this constant as `const_bufSize`.

常量在汇编程序中被命名为`const_name`的格式(`const_`是固定前缀)。比如，对于 Go 中定义的常量` const bufSize = 1024`，在汇编代码中，可以使用`const_bufSize`来引用该常量。

Field offsets are of the form `type_field`. Struct sizes are of the form `type__size`. For example, consider the following Go definition:

结构体的字段在汇编程序中会被命名为`type_field`形式；结构体的大小会被命名为`type__size`形式(`__size`为固定后缀)。例如，对于下面的 Go 结构体定义：

```go
type reader struct {
    buf [bufSize]byte
    r   int
}
```

Assembly can refer to the size of this struct as `reader__size` and the offsets of the two fields as `reader_buf` and `reader_r`. Hence, if register R1 contains a pointer to a `reader`, assembly can reference the `r` field as `reader_r(R1)`.

在汇编代码中，可以使用`reader__size`来引用`reader`结构体的大小，使用`reader_buf`和`reader_r`来分别指代`reader`结构体中的`buf`和`r`字段。所以，如果`R1`寄存器指向了一个`reader`结构实例，那么在汇编代码中可以使用`reader_r(R1)`来引用该实例的`r`值。

If any of these `#define` names are ambiguous (for example, a struct with a `_size` field), `#include "go_asm.h"` will fail with a "redefinition of macro" error.

如果有任何一个`#define`的名字出现歧义（比如，一个结构体中定义了一个`_size`字段），那么，`#include "go_asm.h`就会失败，并抛出`"redefinition of macro"`错误。

## 6. Runtime Coordination

For garbage collection to run correctly, the runtime must know the location of pointers in all global data and in most stack frames. The Go compiler emits this information when compiling Go source files, but assembly programs must define it explicitly.

为了使垃圾回收器运行正确，运行时必须知道所有全局数据和和帧栈中指针的指向。Go 编译器在进行 Go 源码编译时记录下这些信息，但前提是汇编程序必须有明确的定义。

A data symbol marked with the NOPTR flag (see above) is treated as containing no pointers to runtime-allocated data. A data symbol with the RODATA flag is allocated in read-only memory and is therefore treated as implicitly marked NOPTR. A data symbol with a total size smaller than a pointer is also treated as implicitly marked NOPTR. It is not possible to define a symbol containing pointers in an assembly source file; such a symbol must be defined in a Go source file instead. Assembly source can still refer to the symbol by name even without DATA and GLOBL directives. A good general rule of thumb is to define all non-RODATA symbols in Go instead of in assembly.

带有`NOPTR`标志（见上文）的数据符号被视为不包含任何指向运行时分配的数据的指针；带有`RODATA`的数据符号会被分配在只读内存中，并且也会隐式的被视为带有`NOPTR`标志；总大小小于指针大小的数据符号也被隐式的视为带有`NOPTR`标志。在汇编代码中，是不能定义一个包含有指针的标识符，应该在 Go 源码中对其进行定义。汇编源码中是可以使用对应的名称来直接引用 Go 源码中定义的标识符的，即便汇编源码中没有对这个标识符使用`DATA`或`GLOBL`指令进行声明。一个值得推荐的一般经验法则是：在 Go 中而非汇编程序中定义所有的非`RODATA`。

Each function also needs annotations giving the location of live pointers in its arguments, results, and local stack frame. For an assembly function with no pointer results and either no local stack frame or no function calls, the only requirement is to define a Go prototype for the function in a Go source file in the same package. The name of the assembly function must not contain the package name components (for example, function `Syscall` in package `syscall` should use the name `·Syscall` instead of the equivalent name `syscall·Syscall` in its TEXT directive). For more complex situations, explicit annotation is needed. These annotations use pseudo-instructions defined in the standard `#include` file `funcdata.h`.

每一个函数都需要注明其参数、返回结果、运行栈帧中实时指针的位置。对于不返回指针数据，并且没有本地栈帧或函数调用的汇编函数，唯一的要求是需要在同一个包中的 Go 源文件中为该函数定义一个 Go 原型。汇编函数的名称中不能包含其所在的报名（比如，包`syscall`中的函数`Syscall`，在汇编代码中的`TEXT`指令中，应该使用`·Syscall`作为其名称，而不能使用`syscall·Syscall`）。对于更复杂的场景中，提供明确的注释是必需的。这些注释可以使用伪指令定义在可以被`#include`的文件`funcdata.h`中。

If a function has no arguments and no results, the pointer information can be omitted. This is indicated by an argument size annotation of `$n-0` on the TEXT instruction. Otherwise, pointer information must be provided by a Go prototype for the function in a Go source file, even for assembly functions not called directly from Go. (The prototype will also let `go vet` check the arguments references.) At the start of the function, the arguments are assumed to be initialized but the results are assumed uninitialized. If the results will hold live pointers during a call instruction, the function should start by zeroing the results and then executing the pseudo-instruction `GO_RESULTS_INITIALIZED`. This instruction records that the results are now initialized and should be scanned during stack movement and garbage collection. It is typically easier to arrange that assembly functions do not return pointers or do not contain call instructions; no assembly functions in the standard library use `GO_RESULTS_INITIALIZED`.

如果一个函数没有参数也没有返回结果，那么这个函数的指针信息可以移除。这可以通过`TEXT`指令中的参数大小声明`$n-0`来达成。否则的话，指针信息必须通过 Go 源码中的 Go 原型来提供，即便这个汇编函数并没有在 Go 源码中被直接调用。这个 Go 原型也会由`go vet`来检查参数的引用。在函数开始时，会假定参数已经被初始化了，而返回结果尚未被初始化。如果在调用期间，返回的结果指向了一个动态指针，那么这个函数应该在开始的时候将返回结果初始化为零值，然后执行伪指令`GO_RESULTS_INITIALIZED`。这个伪指令记录了这个返回结果已经被初始化的信息，在后续的栈迁移和垃圾回收时需要扫描该指针。如果汇编函数不返回指针结果或者不包含调用指令，会使其更简单易懂，而且，Go 标准库中没有任何一个汇编函数使用了`GO_RESULTS_INITIALIZED`伪指令。

If a function has no local stack frame, the pointer information can be omitted. This is indicated by a local frame size annotation of `$0-n` on the TEXT instruction. The pointer information can also be omitted if the function contains no call instructions. Otherwise, the local stack frame must not contain pointers, and the assembly must confirm this fact by executing the pseudo-instruction `NO_LOCAL_POINTERS`. Because stack resizing is implemented by moving the stack, the stack point may change during any function call: even pointers to stack data must not be kept in local variables.

如果一个方法没有本地栈帧，那么其指针信息可以被移除。这可以通过`TEXT`指令中的本地帧大小声明`$0-n`来达成。如果函数没有调用指令，那么其指针信息也可以被移除。否则，本地栈帧中必须不能包含指针，并且需要在汇编代码中执行`NO_LOCAL_POINTERS`伪指令来确认该状态。因为栈扩容是通过迁移栈的方式来实现的，所以栈中的指针可能会在任意一个函数调用过程中发生变化，因此即使是指向栈数据的指针也不能保存在本地变量中。

Assembly functions should always be given Go prototypes, both to provide pointer information for the arguments and results and to let `go vet` check that the offsets being used to access them are correct.

应该总是为汇编函数提供 Go 原型，同时为其参数和返回结果提供指针信息，以便`go vet`检查其访问的偏移量是否正确。

## 7. Architecture-specific details

It is impractical to list all the instructions and other details for each machine. To see what instructions are defined for a given machine, say ARM, look in the source for the `obj` support library for that architecture, located in the directory `src/cmd/internal/obj/arm`. In that directory is a file `a.out.go`, it contains a long list of constants starting with A, like this:

把每个平台每种机器支持的汇编指令和其他信息都列举出来是不现实的。如果要看某种平台的指令定义，可以查看对应平台的源码文件。比如，对于 ARM 平台，可以在`src/cmd/internal/obj/arm`路径中看到相关的定义。在每个平台的路径中，都有一个`a.out.go`文件，对于 ARM 平台，有如下的一系列的常量定义，如下：

```go
const (
    AAND = obj.ABaseARM + obj.A_ARCHSPECIFIC + iota
    AEOR
    ASUB
    ARSB
    AADD
    ...
```

This is the list of instructions and their spellings as known to the assembler and linker for that architecture. Each instruction begins with an initial capital A in this list, so AAND represents the bitwise and instruction, AND (without the leading A), and is written in assembly source as AND. The enumeration is mostly in alphabetical order. (The architecture-independent AXXX, defined in the `cmd/internal/obj` package, represents an invalid instruction). The sequence of the A names has nothing to do with the actual encoding of the machine instructions. The `cmd/internal/obj` package takes care of that detail.

这是该架构的汇编器和链接器已知的指令列表及其拼写。此列表中的每条指令都以`A`作为开头，因此`AAND`其实等价于`AND`，表示按位和，并且在汇编程序中写为`AND`。这个列表是按照字母顺序排列的。（在`cmd/internal/obj`包中定义的、与平台无关的指令`AXXX`就相当于是无效的指令）。这些以 A 开头的指令的顺序与机器指令的实际编码无关，`cmd/internal/obj`包负责处理这些细节。

The instructions for both the 386 and AMD64 architectures are listed in `cmd/internal/obj/x86/a.out.go`.

386 和 AMD64 架构的指令列表都存放在`cmd/internal/obj/x86/a.out.go`文件中。

The architectures share syntax for common addressing modes such as (R1) (register indirect), 4(R1) (register indirect with offset), and `$foo(SB)` (absolute address). The assembler also support some (not necessarily all) addressing modes specific to each architecture. The sections below list these.

所有的架构都共享通用的寻址方式，比如：`R1`（寄存器直接寻址）、`4(R1)`（寄存器偏移寻址）、`$foo(SB)`（绝对寻址）。汇编器对不同的架构也支持一些（不一定全都支持）特定的寻址方式。后续的章节会对不同架构支持的特殊寻址方式进行介绍。

Here follow some descriptions of key Go-specific details for the supported architectures.

下面是一些不同架构支持的特殊 Go 编译器语法的描述。

### 7.1 32-bit Intel 386

The runtime pointer to the `g` structure is maintained through the value of an otherwise unused (as far as Go is concerned) register in the MMU. In the runtime package, assembly code can include `go_tls.h`, which defines an OS- and architecture-dependent macro `get_tls` for accessing this register. The `get_tls` macro takes one argument, which is the register to load the `g` pointer into.

指向`g`结构的运行时指针通过 MMU 中未使用的（就 Go 而言）寄存器的值来维护。在`runtime`包中，汇编代码可以引入`go_tls.h`文件，在这个文件中定义了一个依赖于具体的架构和操作系统的宏`get_tls`来访问这个寄存器。这个`get_tls`宏接收一个参数，用来指定加载`g`指针的寄存器。

For example, the sequence to load `g` and `m` using CX looks like this:

如下，这段汇编代码会使用`CX`寄存器来获取`g`和`g.m`指针:

```asm
#include "go_tls.h"
#include "go_asm.h"
...
get_tls(CX)
MOVL    g(CX), AX   // Move g into AX
MOVL    g_m(AX), BX // move g.m into BX
```

The `get_tls` macro is also defined on [amd64](#7.4%20AMD64).

`get_tls`宏也在 AMD64 架构上有定义。

Addressing modes:

* `(DI)(BX*2)`: The location at address DI plus BX*2.
* `64(DI)(BX*2)`: The location at address DI plus BX*2 plus 64. These modes accept only 1, 2, 4, and 8 as scale factors.

寻址模式：

* `(DI)(BX*2)` 位于`DI`地址加上`BX*2`的地址上。
* `64(DI)(BX*2)` 位于`DI`地址加上`BX*2`再加上 64 的地址上。

这两种模式都中，BX 都只能接受 1、2、4、8 的扩展倍数。

When using the compiler and assembler's `-dynlink` or `-shared` modes, any load or store of a fixed memory location such as a global variable must be assumed to overwrite CX. Therefore, to be safe for use with these modes, assembly sources should typically avoid CX except between memory references.

当使用编译器和汇编器的`-dynlink`或`-sahred`模式时，任何固定内存位置（例如全局变量）的加载或存储都必须假定会覆盖 CX 寄存器。因此，为了安全的使用这些模式，汇编源码中应该避免操作 CX 寄存器，除非是在内存引用的情况下。

### 7.2 64-bit Intel 386 (a.k.a amd64)

The two architectures behave largely the same at the assembler level. Assembly code to access the `m` and `g` pointers on the 64-bit version is the same as on the 32-bit 386, except it uses `MOVQ` rather than `MOVL`:

这两种架构体系结构在汇编器层面上，其行为大致相同。它们的汇编代码访问`g`和`g.m`指针的方式与 32 位 386 架构上的方式是一样的，只是使用的`MOVQ`指令替代了`MOVL`指令：

```asm
get_tls(CX)
MOVQ   g(CX), AX   // Move g into AX
MOVQ   g_m(AX), BX // Move g.m into BX
```

Register BP is callee-save. The assembler automatically inserts BP save/restore when frame size is large than zero. Using BP as a general purpose register is allowed, however it can interface with sampling-based profiling.

寄存器`BP`是由调用者进行保存。当函数的帧空间大于 0 时，汇编器会自动的插入`BP`寄存器的保存、恢复指令。也可以将`BP`作为一个通用寄存器来使用，但是这样会干扰基于采样的分析。

### 7.3 ARM

The registers R10 and R11 are reserved by the compiler and linker.

在 ARM 架构中，`R10`和`R11`寄存器是由编译器和链接器保留的。

R10 points to the `g` (goroutine) structure. Within assembler source code, this pointer must be referred to as `g`; the name `R10` is not recognized.

`R10`寄存器指向的是`g`（Go 协程）结构体。但是在汇编源码中，必须使用`g`来引用该指针，`R10`是不会被采用的。

To make it easier for people and compilers to write assembly, the ARM linker allows general addressing forms and pseudo-operations like `DIV` or `MOD` that may not be expressible using a single hardware instruction. It implements these forms as multiple instructions, often using the R11 register to hold temporary values. Hand-written assembly can use R11, but doing so requires being sure that the linker is not also using it to implement any of the other instructions in the function.

为了让人们和编译器能更容易的生成汇编代码，ARM 链接器允许使用单个硬件指令无法表达的通用寻址形式和伪指令，如`DIV`、`MOD`。它会将这些形式的操作展开为多条指令，并且常常会使用`R11`寄存器来存储中间值。手写的汇编代码可以使用`R11`寄存器，但是这样做的时候，需要确保链接器没有使用该寄存器来实现函数中的任何其他指令。

When defining a TEXT, specifying frame size `$-4` tells the linker that this is a leaf function that does not need to save LR on entry.

在使用`TEXT`指令的时候，指定帧大小为`$-4`可以告诉链接器，这是一个叶子函数，不需要在入口处保存`LR`寄存器的值。

The name SP always refers to the virtual stack pointer described earlier. For the hardware register, use R13.

`SP`这个名字总是表示前面讲述过的虚拟的栈指针。如果要使用硬件的栈指针，则需要使用`R13`。

Condition code syntax is to append a period and the one- or two-letter code to the instruction, as in `MOVW.EQ`. Multiple codes may be appended: `MOVM.IA.W`. The order of the code modifiers is irrelevant.

条件语法是在指令后面附加一个句点和一个或两个字母，如`MOVM.EQ`，也可以同时附加多段代码，如`MOVM.IA.W`。代码修饰符是顺序无关的。

Addressing modes:

* `R0->16`/`Ro>>16`/`R0<<16`/`R0@>16`

    For <<, left shift R0 by 16 bits. The other codes are -> (arithmetic right shift), >> (logical right shift), and @> (rotate right).

    `<<`表示将左移；`->`表示算术右移；`>>`表示逻辑右移；`@>`表示右旋。
    
* `R0->R1`/`R0>>R1`/`R0<<R1`/`R0@>R1`

    For <<, left shift R0 by the count in R1. The other codes are -> (arithmetic right shift), >> (logical right shift), and @> (rotate right).

    与前面类似，只是移动或旋转的位数由`R1`寄存器的值来决定。
    
* `[R0, g, R12-R15]`

    For multi-register instructions, the set comprising R0, g, and R12 through R15 inclusive.

    多寄存器指令，该命令包含`R0`寄存器、`g`指针、`R12`到`R15`的寄存器（`R12`、`R13`、`R14`、`R15`）。
    
* `(R5, R6)`

    Destination register pair.

    目标寄存器对。
    
### 7.4 ARM64

R18 is the "platform register", reserved on the Apple platform. To prevent accidental misuse, the register is named R_18_PLATFORM. R27 and R28 are reserved by the compiler and linker. R29 is the frame pointer. R30 is the link register.

`R18`是平台寄存器，被 Apple 平台保留使用。为防止误用，该寄存器被命名为`R18_PLATFORM`。`R27`和`R28`由编译器和链接器保留，`R29`是帧指针，`R30`是链接寄存器。

Instruction modifiers are appended to the instruction following a period. The only modifiers are P (postincrement) and W (preincrement): `MOVW.P`, `MOVW.W`.

指令修饰符跟随在指令后面，并通过句点链接。只支持两个修饰符：

* `P` 后增量，`MOVW.P`
* `W` 前增量，`MOVW.W`

Addressing modes:

* `R0->16`/`Ro>>16`/`R0<<16`/`R0@>16`

    These are the same as on the 32-bit ARM.
    
    与 32 位的 ARM 架构相同。
    
* `$(8<<12)`

    Left shift the immediate value 8 by 12 bits.
    
    将立即数 8 左移 12 位。
    
* `8(R0)`

    Add the value of R0 and 8.
    
    将寄存器`R0`的值加上 8。
    
* `(R2)(R0)`

    The location at R0 plus R2.
    
    将寄存器`R0`的值加上寄存器`R2`的值。
    
* `R0.UXTB`

    - `R0.UXTB` 从`R0`寄存器中取出低端的 8 位。
    - `R0.UXTB<<imm: UXTB` 取出`R0.UXTB`，并将其用 0 扩展到`R0`的位数大小。

        > extract an 8-bit value from the low-order bits of R0 and zero-extend it to the size of R0. 

    - `R0.UXTB<<imm` 取出`R0.UXTB`，并将其左移`imm`位。这里的`imm`的值可以为 0、1、2、3、4。

        > left shift the result of R0.UXTB by imm bits. The imm value can be 0, 1, 2, 3, or 4.

    The other extensions include UXTH(16-bit), UXTW(32-bit), and UXTX(64-bit).
    
    其他的扩展还有 UXTH(16 位)、UXTW(32 位)、UXTX(64 位)。

* `R0.SXTB`

    - `R0.SXTB` 从`R0`寄存区中取出低端的 8 位。
    - `R0.SXTB<<imm: SXTB` 取出`R0.SXTB`，并将其采用符号扩展将其扩展到`R0`的大小。

        > extract an 8-bit value from the low-order bits of R0 and sign-extend it to the size of R0.
        
    - `R0.SXTB<<imm` 取出`R0.SXTB`，并左移`imm`位。这里的`imm`的值可以为 0、1、2、3、4。

        > left shift the result of R0.SXTB by imm bits. The imm value can be 0, 1, 2, 3, or 4.

    The other extensions include SXTH(16-bit), SXTW(32-bit), SXTX(64-bit).
    
    其他的扩展还有 XXTH(16 位)、XXTW(32 位)、XXTX(64 位)。

* `r5, r6`

    Register pair for LDAXP/LDP/LDXP/STLXP/STP/STP.
    
    寄存器对：LDAXP/LDP/LDXP/STLXP/STP/STP。

Reference: [Go ARM 64 Assembly Instructions Reference Manual](https://pkg.go.dev/cmd/internal/obj/arm64)

### 7.5 PPC64

This assembler is used by GOARCH values ppc64 and ppc64le.

这个汇编器是用于 GOARCH 环境变量的值为`ppc64`和`ppc64le`的情况。

Reference: [Go PPC64 Assembly Instructions Reference Manual](https://golang.org/pkg/cmd/internal/obj/ppc64)

### 7.6 IBM z/Architecture, a.k.a. s390x

The registers R10 and R11 are reserved. The assembler uses them to hold temporary values when assembling some instructions.

寄存器`R10`和`R11`被保留。在汇编的时候，汇编器会使用他们来保存临时值。

R13 points to the `g` (goroutine) structure. This register must be referred to as `g`; the name R13 is not recognized.

`R13`寄存器指向 Go 协程的结构体实例`g`，这个寄存器必须通过`g`来引用，而不能直接使用`R13`。

R15 points to ths stack frame and should typically only be accessed using the virtual registers SP and FP.

`R15`寄存器指向栈帧，并且通常应该使用虚拟寄存器`SP`和`FP`来访问。

Load- and store-multiple instructions operate on a range of registers. The range of registers is specified by a start register and an end register. For example, LMG (R9), R5, R7 would load R5, R6 and R7 with the 64-bit values at 0(R9), 8(R9) and 16(R9) respectively.

多数据加载和存储指令可以操作一个范围内的全部寄存器。寄存器范围由起始和结束寄存器来指定。例如：`LMG (R9), R5, R7`指令会将`R5`、`R6`、`R7`三个寄存器中的 64 位值依次加载到`0(R9)`、`8(R9)`、`16(R9)`这三个地址上。

Storage-and-storage instructions such as MVC and XC are written with the length as the first argument. Fo example, XC $8, (R9), (R9) would clear eight bytes at the address specified in R9.

转存指令（如`MVC`、`XC`）会操作由第一个参数指定的长度的数据。例如：`XC $8, (R9), (R9)`命令会清除由寄存器`R9`指定的地址上的 8 字节。

If a vector instruction takes a length or an index as an argument then it will be the first argument. For example, VLEIF $1, $16, V2 will load the value sixteen into index one of V2. Care should be taken when using vector instructions to ensure that they are available at runtime. To use vector instructions a machine must have both the vector facility (bit 129 in the facility list) and kernel support. Without kernel support a vector instruction will have no effect (it will be equivalent to a NOP instruction).

如果向量指令将长度或者索引作为参数，那么它将是第一个参数。例如：`VLEIF $1, $16, V2`会将值 16 载入到`V2`向量的索引 1 的位置。需要注意的是，使用向量指令的话，需要确保他们在运行时是可以被使用的。如果要使用向量指令，机器必须通知具有向量工具（129 位工具列表）和内核的支持。如果没有内核支持，向量指令将不会有任何效果（其类似于一个`NOP`指令）。

Addressing modes:

	* `(R15)(R6*1)`

		The location at R5 plus R6. It is a scaled mode as on the x86, but the only scale allowd is 1.

		由寄存器`R5`和寄存器`R6`相加之后的值表示的位置。这里的伸缩方式类似 x86 架构，但是只支持 1 倍伸缩（就是原值）。

### 7.8 MIPS, MIPS64

General purpose registers are named R0 through R31, floating point registers are F0 through F31.

通用寄存器命名为`R0`到`R31`，浮点寄存器命名为`F0`到`F31`。

R30 is reserved to point `g`. R23 is used as a temporary register.

`R30`被保留，用于指向`g`，`R32`用于存储临时值。

In a TEXT directive, the frame size `$-4` for MIPS or `$-8` for MIPS64 instructs the linker not to save LR.

在`TEXT`指令中，MIPS 架构上使用`$-4`来提示链接器不需要保存`LR`值，而 MIPS64 上则使用`$-8`来进行指示。

SP refers to the virtual stack pointer. For the hardware register, use R29.

`SP`符号用于指代虚拟栈指针，而硬件的`SP`寄存器则使用`R29`指代。

Addressing modes:

寻址模式：

* `16(R1)`: The location at R1 plus 16。`R1`寄存器的值加上 16。
* `(R1)`: Alias for `0(R1)`。是`0(R1)`的别名。

The value of GOMIPS environment variable (`hardfloat` or `softfloat`) is made available to assembly code by predefining either GOMIPS_hardfloat or GIMPS_softfloat.

通过预定义`GOMIPS_hardfloat`或`GIMPS_softfloat`，可以在汇编代码中使用 GOMIPS 环境变量(`hardfloat`或者`softfloat`)的值可以用于汇编代码中。

The value of GOMIPS64 environment variable (`hardfloat` or `softfloat`) is made available to assembly code by predefining either GOMIPS64_hardfloat or GOMPIS64_softfloat.

通过预定义`GOMIPS64_hardfloat`或`GIMPS64_softfloat`，可以在汇编代码中使用 GOMIPS64 环境变量(`hardfloat`或者`softfloat`)的值可以用于汇编代码中。

## 8. Unsupported opcodes

The assemblers are designed to support the compiler so not all hardware instructions are defined for all architectures: if the compiler doesn't generate it, it might not be there. If you need to use a missing instruction, there are two ways to proceed. One is to update the assembler to support that instruction, which is straightforward but only worthwhile if it's likely the instruction will be used again. Instead, for simple one-off cases, it's possible to use the BYTE and WORD directives to lay down explicit data into the instruction stream within a TEXT. Here's how the 386 runtime defines the 64-bit atomic load function.

汇编器旨在支持编译器，因此并非所有的体系结构都支持所有的硬件指令：如果编译器没有生成相关指令，那么它就是不被支持的。如果需要使用这些缺失的指令，可以通过两种方式来实现。一种简单的方法是更新汇编器的版本，新版本的汇编器可能会支持该指令，但是这种方法只有在多次使用该指令的时候才是值的的。另一种方式则适合偶尔使用的场景：使用`BYTE`和`WORD`指令将显示的数据放到`TEXT`内的指令流中。下面是 386 架构上定义定义 64 位原子加载函数。

```asm
// uint64 atomicload64(uint64 volatile* addr);
// so actually
// void atomicload64(uint64 *res, uint64 volatile *addr);
TEXT runtime·atomicload64(SB), NOSPLIT, $0-12
	MOVL    ptr+0(FP), AX
	TESTL   $7, AX
	JZ      2(PC)
	MOVL    0, AX // crash with nil ptr deref
	LEAL    ret_lo+4(FP), BX
	// MOVQ (%EAX), %MM0
	BYTE    $0x0f; BYTE $0x6f; BYTE $0x00
	// MOVQ %MM0, 0(%EBX)
	BYTE    $0x0f; BYTE $0x7f; BYTE $0x03
	// EMMS
	BYTE    $0x0F; BYTE $0x77
	RET
```


