> 转摘：[Go 面试题：怎么避免内存逃逸？](https://mp.weixin.qq.com/s/RNHpyiiOP5YLIb3UHqtcrQ)

### 1. 问题

如何避免内存逃逸？

### 2. 方法

在`runtime/stubs.go`中有一个**`noescape()`方法**，可以在 Go 编译器进行逃逸分析时隐藏一个指针，**使这个指针在逃逸分析中不会被检测为逃逸**：

```go
// noescape hides a pointer from escape analysis.  noescape is
// the identity function but escape analysis doesn't think the
// output depends on the input.  noescape is inlined and currently
// compiles down to zero instructions.
// USE CAREFULLY!
//go:nosplit
func noescape(p unsafe.Pointer) unsafe.Pointer {
  x := uintptr(p)
  return unsafe.Pointer(x ^ 0)
}
```

`uintptr`类型是一个真正的指针类型，但是在编译器层面，它只是一个存储*指针地址*的`int`类型，不会被当做有效的指针，因为`uintptr()`产生的引用是编译器无法理解的。

所以，在`noescape()`方法中，先将入参强制转换成`uintptr`类型，然后重新将其包装成`unsafe.Pointer`类型，从而使得编译器认为入参参数不会发生逃逸。

### 3. 示例

对于下面的代码：

```go
package main

import "unsafe"

type A struct {
  S *string
}

func (a *A) String() string {
  return *a.S
}

type ATrick struct {
  S unsafe.Pointer
}

func (at *ATrick) String() string {
  return *(*string)(at.S)
}

func NewA(s string) A {
  return A{S: &s}
}

func NewATrick(s string) ATrick {
  return ATrick{S: noescape(unsafe.Pointer(&s))}
}

func noescape(p unsafe.Pointer) unsafe.Pointer {
  x := uintptr(p)
  return unsafe.Pointer(x ^ 0)
}

func main() {
  s := "hello"
  f1 := NewA(s)
  f2 := NewATrick(s)
  s1 := f1.String()
  s2 := f2.String()
  _ = s1 + s2
}
```

这段代码中，结构体类型`A`和`ATrick`是对同样的功能有两个实现，但是在创建的时候，`ATrick`结构体中的`S`字段的声明会被`noescape()`进行处理。

使用`go build -gcflags="-m" main.go`进行逃逸分析，可以得到类似如下的结果：

```
// ... 不相关的输出隐藏掉
./main.go:21:11: moved to heap: s
./main.go:25:16: s does not escape
./main.go:29:15: p does not escape
./main.go:40:10: s1 + s2 does not escape
```

这里指示的 L21 行就是`NewA()`方法，说明这个方法的参数发生了逃逸，进入到堆上；而 L25 表示的`NewATrick()`方法则是未发生逃逸的。

