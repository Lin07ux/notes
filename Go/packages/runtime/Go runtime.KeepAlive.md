> 转摘：
> 1. [什么是 Go runtime.KeepAlive?](https://mp.weixin.qq.com/s/5XMmVqdcji8jpRnC2p0F3w)
> 2. [Go: Keeping a Variable Alive](https://medium.com/a-journey-with-go/go-keeping-a-variable-alive-c28e3633673a)

`runtime.KeepAlive`函数能够阻止变量被 GC 回收，也就是在代码执行流程中保持变量都可用。

> 正常情况下，`runtime.KeepAlive`（以及`runtime.SetFinalizer`）都不应该被滥用，应该在必要的时候才使用。比如，在使用 Go Ballast 让内存控制更顺滑时就会使用`runtime.KeepAlive`方法。

### 1. 源码解析

`runtime.KeepAlive`函数位于`runtime/mfinal.go`文件中。其代码本身很简单，没有做任何事情：

```go
// Mark KeepAlive as noinline so that it is easily detectable as an intrinsic.
//go:noinline
//...//
func KeepAlive(x interface{}) {
  // Introduce a use of x that the compiler can't eliminate.
  // This makes sure x is alive on entry. We need x to be alive
  // on entry for "defer runtime.KeepAlive(x)"; see issue 21402.
  if cgoAlwaysFalse {
    println(x)
  }
}
```

在进行编译时，Go 编译器会使用多种方式来优化代码：函数内联，死代码移除，等等。对于`runtime.KeepAlive`方法，函数内联被禁止了，这就使得 Go 编译器很容易的就能拿个判断出调用的是`KeepAlive`方法。

Go 编译器会对`KeepAlive`方法使用特殊的 SSA 指令进行替换，从而使得它的参数不会被 GC 回收处理。

![runtime.KeepAlive generates a special SSA instruction](http://cnd.qiniu.lin07ux.cn/markdown/1638706276087-9a3b0ada1cff.jpg)

这个方法对应的 SSA 指令也能够在源码生成的 SSA 代码中看到：

![generated SSA by the Go compiler](http://cnd.qiniu.lin07ux.cn/markdown/1638706353184-6efe6d5a4bcd.jpg)

### 2. 使用示例

`runtime.SetFinalizer`可以用于模拟 PHP、Java 中的析构函数，在变量被回收时执行一些回收操作，加速一些资源的释放。这样能够在性能优化时提供一定的效果，不过也是有风险的，就是要释放的资源已经被回收了。

比如，下面这段代码中，初始化一个文件描述符，当 GC 发生时释放掉无效的文件描述符：

```go
type File struct { d int }

func openFile(path string) *File {
  d, err := syscall.Open(path, syscall.O_RDONLY, 0)
  if err != nil {
    panic(err)
  }
  
  p := &File{d}
  runtime.SetFinalizer(p, func(p *File) {
    syscall.Close(p.d)
  })
  
  return p
}

func readFile(descriptor int) string {
  doSomeAllocation()
  
  var buf [1000]byte
  _, err := syscall.Read(descriptor, buf[:])
  if err != nil {
    panic(err)
  }
  
  return string(buf[:])
}

func doSomeAllocation() {
  var a *int
  
  // memory increase to force the GC
  for i := 0; i < 1000000; i++ {
    i := 1
    a = &i
  }
  
  _ = a
}

func main() {
  p := openFile("t.txt")
  content := readFile(p.d)
  
  println("Here is the content: " + content)
}
```

这段代码中，`doSomeAllocation`会强制执行 GC。运行这段代码会出现下面的错误：

```
panic: no such file or directory

goroutine 1 [running]:
main.openFile(0x107a65e, 0x5, 0x10d9220)
        main.go:20 +0xe5
main.main()
        main.go:11 +0x3a
```

这是因为`syscall.Open`产生的文件描述符比较特殊，是个 int 类型，当以值拷贝的方式在函数间传递时，并不会让`File.d`产生引用关系，于是 GC 发生时就会调用`runtime.SetFinalizer(p, func(p *File))`导致文件描述符被关闭，然后就会发生文件写入失败的 panic。

如果要让文件描述符不被 GC 给释放掉，就可以使用`runtime.KeepAlive()`函数来主动说明：

```go
func main() {
  p := openFile("t.txt")
  content := readFile(p.d)
  
  runtime.KeepAlive(p)
  
  println("Here is the content: " + content)
}
```

这样就能阻止 GC 来回收文件描述符，也就是会推迟`runtime.SetFinalizer`的发生，从而保证在后续可以一直使用文件描述符。

