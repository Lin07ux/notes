> 转摘：
> 
> 1. [Go 源码里的这些 //go: 指令，你知道吗？](https://mp.weixin.qq.com/s/7OLTcAGlvCEU3OUfpw5BRA)
> 2. [golang 在 runtime 中的一些骚东西](https://www.purewhite.io/2019/11/28/runtime-hacking-translate/)

Go 中有一些伪指令，可以用来对编译器做一些指示，避免一些问题，或者提升性能。这些伪指令在日常的 Go 开发中是不会用到的，更多的是在底层开发上需要偶尔使用。

Go 的伪指令都是以注释形式存在的，并紧贴着对应的声明语句。伪指令都是以`//go:`形式开头。

### 1. go:linkname

**语法**：

```go
//go:linkname localname importpath.name
```

**作用**：

该指令指示编译器使用`importpath.name`作为源代码中声明为`localname`的变量或函数的目标文件符号名称。也就是说，在源代码中使用`importpath.name`的地方，编译器实际上会替换为对`localname`的引用。

由于这个伪指令可以破坏类型系统和包模块化，所以只有引用了`unsafe`包才可以使用。

**实例**：

*time/time.go*

```go
// Provided by package runtime.
func now() (sec int64, nsec int32, mono int64)
```

*runtime/timestub.go*

```go
import _ "unsafe" // for go:linkname

//go:linkname time_now time.now
//go:linkname time_now time.now
func time_now() (sec int64, nsec int32, mono int64) {
	sec, nsec = walltime()
	return sec, nsec, nanotime()
}
```

在这个实例中可以看到，`time.now()`方法并没有具体的实现，而是在`runtime.time_now`中被实现了。

由于`runtime.time_now()`方法前面使用了`go:linkman`注释，所以在调用`time.now()`的时候就会执行`runtime.time_now()`方法了。

可以看到，通过这种方式能够在一个包中使用别的包中未导出的方法和变量。

### 2. go:noescape

**语法**：

```go
//go:noescape
```

**作用**：

该指令指定下面的*有声明但没有主体*（意味着有可能不是 Go 实现）*的函数*，不允许编译器对其做*逃逸分析*。

一般情况下，该指令用于内存分配优化。编译器默认会进行逃逸分析，通过一定的规则判定一个变量是分配到堆上还是栈上。

但有时候，一些函数虽然逃逸分析的结果是放在堆上，但是人为的需要特别对待，就可以使用该指令强制要求编译器将其分配到函数的栈上。

**实例**：

```go
// memmove copies n bytes from "from" to "to".
// in memmove_*.s
//go:noescape
func memmove(to, from unsafe.Pointer, n uintptr)
```

这个代码中，函数`memmove`满足了该指令的常见特征：

* 只有声明没有主体，主体是由底层的汇编实现的；
* 该函数的功能在栈上处理，性能会更好。

### 3. go:nosplit

**语法**：

```go
//go:nosplit
```

**作用**：

指定声明下面的函数不得包含堆栈溢出检查。也就是说，这个指令会让函数跳过堆栈溢出的检查。

**实例**：

```go
//go:nosplit
func key32(p *uintptr) *uint32 {
  return (*uint32)(unsafe.Pointer(p))
}
```

### 4. go:nowritebarrierrec 和 go:yeswirtebarrierrec

**语法**：

```go
//go:nowritebarrierrec

//go:yeswirtebarrierrec
```

**作用**：

这两个指令与写屏障相关：

* `go:nowritebarrierrec`指示编译器，如果其下面的函数以及它调用的函数（递归下去），包含了一个写屏障的话，就触发一个错误。

* `go:yeswirtebarrierrec`则是指示编译器`go:nowritebarrierrec`的作用到此伪指令标记的函数为止。

在逻辑上，编译器会在生成的调用图上，从每个被标记了`go:nowritebarrierrec`的函数触发，直到遇到了一个被标记为`go:yeswritebarrierrec`的函数为止，如果其中遇到了一个函数包含写屏障，就会产生一个错误。

`go:nowritebarrierrec`就是针对写屏障的特定处理，防止死循环。

**实例**：

```go
//go:nowritebarrierrec
func gcFlushBgCredit(scanWork int64) {
  ...
}

//go:yeswritebarrierrec
func gchelper() {
  ...
}
```

### 5. go:noinline

**语法**：

```go
//go:noinline
```

**作用**：

编译器在进行编译处理的时候，默认会对一些简单的函数进行内联处理，这样可以减少函数调用的栈分配和回收操作多，提升性能。

而该指令表示禁止编译器内联指定的函数，因为在一些时候内联优化也会带来一些问题。

**实例**：

```go
//go:noinline
func unexportedPanicForTesting(b []byte, i int) byte {
  return b[i]
}
```

这个函数的处理逻辑很简单，编译器默认情况下会对其进行内联处理，但是使用了`go:noline`指令可以避免编译器对其进行内联。

### 6. go:norace

**语法**：

```go
//go:norace
```

**作用**：

该指令表示禁止进行静态检测。

常见的形式就是在启动程序时，执行`go run -race`，能够检测应用程序中是否存在双向的数据竞争，非常有用。

**实例**：

```go
//go:norace
func forkAndExecInChild(argv0 *byte, argv, envv []*byte, chroot, dir *byte, attr *ProcAttr, sys *SysProcAttr, pipe int) (pid int, err Errno) {
  ...
}
```

### 7. go:notinheap

**语法**：

```go
//go:notinheap
```

**作用**：

该指令常用于类型声明处，表示这个类型不允许从 GC 堆上申请内存。

在 runtime 中，常用来做较低层次的内部结构，避免调度器和内存分配中的写屏障，能够提升性能。

特别的，对于使用了该标记的类型 T：

1. `new(T)`、`make([]T)`、`append([]T, ...)`和隐式的对于 T 的堆上分配是不允许的（尽管隐式的分配在 runtime 中是从来不被允许的）。
2. 一个指向普通类型的指针（除了`unsafe.Pointer`）不能被转换成一个指向 T 类型的指针，就算它们有相同的底层类型。
3. 任何一个包含了 T 类型的类型自身也会成为`go:notinheap`的。如果结构体和数组包含 T 类型的元素，那么它们自身也是`go:notinheap`类型的。map 和 channel 不允许有 T 类型的元素。
4. 指向 T 类型的指针的写屏障可以被忽略。

**实例**：

```go
// notInHeap is off-heap memory allocated by a lower-level allocator
// like sysAlloc or persistentAlloc
// 
// In general, it's better to use real types marked as go:notinheap,
// but this serves as a generic type for situations where that isn't
// possible (like in the allocators).
//
//go:notinheap
type notInHeap struct{}
```

### 8. go:systemstack

**语法**：

```go
//go:systemstack
```

**作用**：

表明一个函数必须在系统栈上运行，这个会通过一个特殊的函数前引（prologue）动态地验证。

