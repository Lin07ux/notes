### 1. 什么是 Go 的 Finalizer 机制？

Go 的 Finalizer 机制与其 GC 机制相关。在 Go 中，程序员负责申请内存，Go runtime 中的 GC 机制负责回收。

在这个过程中，Go 语言提供了一个 Finalizer 机制，允许程序员在申请内存的时候指定一个回调函数，该回调函数会在 GC 回收到这个结构体内存的时候被调用一次。

注册回调函数的函数就是`runtime.SetFinalizer()`，其签名如下：

```go
func SetFinalizer(obj interface{}, finalizer interface{})
```

通过 Finalizer 机制能够比较安全的解决对象声明周期的问题，而且 Go 语言能保证一定是无人引用的结构体被 GC 才会执行回调。

### 2. 简单示例

下面是一个简单的 Finalizer 机制的使用示例：

```go
type TestStruct struct {
  name string
}

//go:noinline
func newTestStruct() *TestStrct {
  v := &TestStruct{"n1"}
  runtime.SetFinalizer(v, func(p *TestStruct) {
    fmt.Println("gc Finalizer")
  })
  return v
}

func main() {
  t := newTestStruct()
  fmt.Println("=== start ===")
  _ = t
  fmt.Println("=== ... ===")
  runtime.GC()
  fmt.Println("=== end ===")
}
```

这段代码中，给结构体 TestStruct 的释放设置了一个 Finalizer 回调函数，然后再主动调用`runtime.GC`来快速触发回收操作，这样就会使回调函数被执行，输出如下：

```text
=== start ===
=== ... ===
gc Finalizer
=== end ===
```

### 3. Finalizer 特点：串行化执行

> 转摘：[Go 细节篇-内存回收又踩坑了](https://mp.weixin.qq.com/s/KH-DJl5DhhnQ3Jwt2BPh-g)

Finalizer 很好用，但是它也有限定条件。在官网上有如下的[声明](https://golang.google.cn/pkg/runtime/#SetFinalizer)：

> A single goroutine runs all finalizers for a program, sequentially. If a finalizer must run for a long time, it should do so by starting a new gorutine.

这段话意思是：**一个程序中的所有的 Finalizer 回调函数都是通过一个 goroutine 串行化的执行**。

这也意味着：一旦某个 Finalizer 回调函数出现了问题，就会影响到全局的 Finalizer 回调函数的执行。所以官方文档中就指明了：如果一个 Finalizer 回调函数需要执行很久，那么就应该主动的开启一个 goroutine 来执行其具体的逻辑。

如下是一个因为某个 Finalizer 回调函数长久阻塞导致的资源泄露和各种异常的示例：

```go
var done chan struct{}

type A struct {
  name string
}

type B struct {
  name string
}

type C struct {
  name string
}

func newA() *A {
  v := &A{"n1"}
  runtime.SetFinalizer(v, func(p *A) {
    fmt.Println("gc Finalizer A")
  })
  return v
}

func newB() *B {
  v := &B{"n1"}
  runtime.SetFinalizer(v, func(p *B) {
    <-done
    fmt.Println("gc Finalizer B")
  })
  return v
}

func newC() *C {
  v := &C{"n1"}
  runtime.SetFinalizer(v, func(p *C) {
    fmt.Println("gc Finalizer C")
  })
  return v
}

func main() {
  a := newA()
  b := newB()
  c := newC()
  fmt.Println("=== start ===")
  _, _, _ = a, b, c
  fmt.Println("=== ... ===")
  for i := 0; i < 10; i++ {
    runtime.GC()
  }
  fmt.Println("=== end ===")
}
```

这段代码很简单，就是分别创建三个结构体变量，并分别设置对应的 Finalizer 回调函数。但是在 B 的 Finalizer 回调函数中有一个从 done Channel 中取值的调用。由于 done 为 nil Channel，所以 B 的 Finalizer 的回调函数的执行会被阻塞住。这就会影响剩下的 Finalizer 回调函数的执行。

该程序的输出如下：

```text
=== start ===
=== ... ===
gc Finalizer C
=== end ===
```

因为先释放 C，然后释放 B，最后释放 A，所以会先执行 C 的 Finalizer 回调函数，然后执行 B 的，最后执行 A 的。但是 B 的 Finalizer 回调函数阻塞住了，无法正常输出了。当 main 结束的时候，B 和 A 的 Finalizer 还没有执行，所以就没有对应的输出了。