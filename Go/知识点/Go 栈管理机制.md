> 转摘：[深入研究goroutine栈](https://studygolang.com/articles/22010)

Go 语言中一个最重要的特性就是 goroutine，非常轻量、廉价（开销极低）、便捷（通信简单）。Go 通过*连续栈*的方式，为单机上万个 goroutine 并存提供了坚实的内存管理基础。

Go 在栈管理上，类似于其他很多语言的管理方式，但是在实现细节上又有很多不同之处。

## 一、栈管理机制

### 1.1 C 语言栈管理方式

在 C 语言中，当要启动一个线程时，标准库会分配好一块内存作为该线程的栈，并告诉内核这块内存的位置，以便让内核处理线程执行。当这块内存区域不足时（例如，执行一个高度递归的函数），程序就会执行错误。

要想解决这个问题，可以有 2 种办法：

1. 修改标准库代码，将栈内存块的分配尺寸改大一些，但是这样会让所有的线程启动时都会分配更大的内存，造成浪费；
2. 根据不同的线程分配不同的内存块，但是这样每次创建线程时，都要确定好所需要的内存大小，很麻烦也很难确定。

### 1.2 Go 语言栈管理方式

Go 的 runtime 则尝试为 goroutine 按需分配栈空间，不需要程序员去决定。之前使用**分段栈**实现，现在的 Go 版本则用**连续栈**实现。

#### 1.2.1 分段栈

在以前的分段栈方式下，当一个 goroutine 创建时，runtime 会分配 **8KB** 区域作为栈供其使用。同时在每个 goroutine 函数入口处都会被插入一小段前置代码，它能够检查栈空间是否被消耗殆尽。如果用完了，这段代码就会调用`morestack()`函数来扩展空间。

**分段栈扩展的机理**（也就是`morestack()`函数的机理）：首先为栈空间分配一块新的内存区域，然后在这个新栈的底部的结构体中填充关于该栈的各种数据，包括刚刚来自旧栈的地址。当得到了一个新的栈分段之后，通过重试导致栈用完的函数来重启 goroutine。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635165718863-4475e9a274c9.jpg)

**分段栈回溯机理**：如上图所示，新栈会为`lessstack()`函数插入一个栈条目。这个函数并不实际显式调用。它会在耗尽旧栈的那个函数（上图中的`Foobar()`）返回的时候被触发。返回到`lessstack()`中后，它会查询栈底部的结构体信息，并调整栈指针`SP`，以便能够回溯到上一个栈分段。然后，就可以释放新栈段空间了。

**分段栈问题：热点分裂**：分段栈机制虽然实现了自动的按需扩展收缩，但是也有瑕疵。函数会使栈发生增长、分裂、返回（收缩）。如果在循环里面不断的执行这些操作，那么将会付出很大的开销，因为**栈的扩缩是一个相对昂贵的操作**。这就是熟知的 **hot split problem（热点分裂问题）**。

#### 1.2.2 连续栈（栈拷贝）

因为分段栈存在热点分裂的问题， Go 开发组从 Go1.14 之后，将栈管理方式切换为了**连续栈**方式，也被称为**栈拷贝**。

连续栈开始时很像分段栈：分配初始栈空间，协程运行，使用栈空间，当栈将要耗尽时，触发相同的栈溢出检测。

但是，不像分段栈里有一个回溯链接，**连续栈则是创建了一个新的分段，它是旧栈的两倍大小，然后把旧栈完全拷贝进来**。而当栈收缩为旧栈大小时，runtime 不会做任何事情，收缩就变成了一个`no op`的免费操作。此外，当栈再次增长时，runtime 也不需要做任何事情，直接重新使用刚才扩容的空间即可。

栈拷贝不像是听起来那么容易，而是一项艰巨的任务。由于栈中的变量在 Go 中能够获取其地址，因此最终会出现指向栈的指针。而如果轻易拷贝移动栈，任何指向旧栈的指针都会失效。

*Go 的内存安全机制规定，任何能够指向栈的指针都必须存于栈中*。所以可以通过垃圾收集器协助栈拷贝，因为垃圾收集器需要知道哪些指针可以进行回收，就可以查到栈上的哪些部分是指针。当进行栈拷贝时，会更新指针信息指向新目标，以及它相关的所有指针。

但是 runtime 中大量核心调度函数和 GC 核心都是 C 语言写的，这些函数都获取不到指针信息，那么它们就无法复制。这种逻辑都会在一个特殊的栈中执行，并且由 runtime 开发者分别定义栈尺寸。

## 二、从汇编角度剖析连续栈的实现

在机器架构层面，很多关于函数的公共操作都会被提取位固定代码。

在函数运行时插入到代码片段的前后部分的代码中：

* 插入到函数代码前面的部分称为`prolog`(序章)，一般只会有一个；
* 插入到函数代码后面的部分称为`epilog`(后记)，一般可以有多个。

Go 就是用`prolog + epilog`的方式来实现连续栈的检测和复制的。

### 2.1 示例代码

有如下 Go 代码：

```go
package main

// 为了避免 add 帧尺寸为 0，所以强行加入一个局部变量 tmp
func add(a, b int64) (int64, int64) {
  var tmp int64 = 1
  tmp = tmp + a
  return a + b, a - b
}

func main() {
  var c int64 = 10
  var d int64 = 12
  add(c, d)
}
```

编译成汇编代码：

```shell
GOOS=linux GOARCH=amd64 go tool compile -S -N -l main.go
```

其关于连续栈的汇编代码如下：

```asm
0x0000 00000 (main.go:9)	TEXT    "".main(SB), ABIInternal, $56-0
0x0000 00000 (main.go:9)	MOVQ    (TLS), CX
0x0009 00009 (main.go:9)	CMPQ    SP, 16(CX)
0x000d 00013 (main.go:9)	JLS     80
// ... omit function body code
0x0050 00080 (main.go:9)	CALL    runtime.morestack_noctxt(SB)
```

这里：

1. `MOVQ (TLS), CX`和`CMPQ SP, 16(CX)`这两句就是用来检测是否发生栈溢出的。
2. 如果发生了栈溢出，则通过`JLS 80`跳转到`CALL runtime.morestack_noctxt(SB)`执行栈扩展，这包含新栈分配和旧栈拷贝两个部分。

### 2.2 栈溢出检测实现

TLS 是一个伪寄存器，表示的是`thread-local storage`，其指向的是`g`结构体，而且它的值只能被载入到另一个寄存器中。

> 结构体`g`属于 Go GMP 调度范畴，它详细定义了 Go 协程栈的各种参数。

上面的代码中，先将 TLS 的值载入到 CX 寄出中，所以`16(CX)`也就是`16(TLS)`，指向的就是`g->stackguard`。准确的说，在这个程序中，它指向的是`g->stackguard0`。

这就是`prolog`检测的一个精髓：每一个 goroutine 的`g->stackguard0`都被设置为指向`stack.lo + StackGuard`的位置。所以每一个函数在真正执行前都会将 SP 和`g->stackguard0`进行比较，以判断是否发生栈溢出。

**栈溢出在整个函数执行之前就能被检测到，而不是函数内某条语句执行时才会被触发。**

各数据指向如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635173972987-0f71fc55daad.jpg)

相关结构体定义如下：

```go
type stack struct {
  lo uintptr // 64 位机器上占 8 字节
  hi uintptr // 64 位机器上占 8 字节
}

type g struct {
  stack       stack
  stackguard0 uintptr // 用于 golang 的栈溢出检测
  stackguard1 uintptr // 用于 clang 的栈溢出检测
}
```

### 2.3 栈溢出检测实现细节

在`runtime/stack.go`中定义了如下一些常量：

* `_StackGuard = 928*sys.StackGuardMultiplier + _StackSystem` 栈溢出门槛，表示在栈最低位加上`_StackGuard`的地址为触发栈溢出的地址。

* `_StackSmall = 128` 主要用于小函数优化，允许函数使用的栈空间突破`_StackGuard`防线之后，再向下占用最多`_StackSmall`个字节。

* `_StackBig = 4096` 主要针对大函数优化，对于这样的函数必须启用另一套栈溢出检测代码，会将`stackPreempt`赋值给`stackGuard`，保证该函数栈必然会栈溢出。

* `stackPreempt = uintptrMask & -1314` 是一个必然大于所有 SP 的值，16 进制表示为`0xFFFFFADE`。它是一个关于 Go 抢占调度范畴的参数，这个值赋予`_StackGuard`可以保证栈必然分裂。而`morestack()`函数在创建新栈时，如果发现`stackGuard == stackPreempt`则会触发调度。

* `_StackLimit = _StackGuard - _StackSystem - _StackSmall` 这个就是栈溢出检测时，栈低位低于`_StackSmall`剩余的那部分空间，这段空间表示了一个`NOSPLIT`拒绝栈溢出检测的函数最多还能使用的栈空间，例如留给 defer 函数使用。

> 这些常量的定义是 Go 1.16 版本的，不同版本的 Go 可能数值会有所调整。
> 
> 这些字段在`cmd/internal/objabi`中都会重复定义一次，提供给内部各模块使用。

栈溢出检测的逻辑如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1635176733632-9cd1127307aa.jpg)

所有栈溢出检测代码都在`cmd/internal/obj`包中。例如，对于 x86 机器架构，对应代码为：

```go
// cmd/internal/obj/x86/obj6.go

func stacksplit() {}
```

### 2.4 NOSPLIT 的自动检测

在编写函数时，编译出汇编代码会发现，在一些函数的执行代码中，编译器很智能的加上了`NOSPLIT`标记。这个标记可以禁用栈溢出检测`prolog`，即该函数运行不会导致栈分裂。由于不需要再执行栈溢出检测，所以会提升一些函数性能。

**当函数处于调用链的叶子节点，且栈帧小于`_StackSmall`字节时，会被自动标记为`NOSPLIT`。**

实现代码如下：

```go
// cmd/internal/obj/s390x/objz.go

if p.Mark&LEAF != 0 && autosize < objabi.StackSmall {
  // A leaf function with a small stack can be marked
  // NOSPLIT, avoiding a stack check.
  p.From.Sym.Set(obj.AttrNoSplit, true)
}
```

标记为`NOSPLIT`的函数，链接器就会知道该函数最多还会使用`_StackSmall`字节空间，不需要栈分裂检查了。

也可以手动使用`//go:nosplit`强制指定`NOSPLIT`属性。但如果函数实际真的溢出了，则会在编译期就报错：`nosplit stack overflow`。

```shell
$ GOOS=linux GOARCH=amd64 go build -gcflags="-N -l" main.go
# command-line-arguments
main.add: nosplit stack overflow
	744	assumed on entry to main.add (nosplit)
	-79264	after main.add (nosplit) uses 80008
```


