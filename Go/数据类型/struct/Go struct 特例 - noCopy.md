> 转摘：
> 
> 1. [Gopher 需要知道的几个结构体骚操作](https://mp.weixin.qq.com/s/A4m1xlFwh9pD0qy3p7ItSA)
> 2. [为什么需要 noCopy](https://blog.lpflpf.cn/passages/golang-noCopy/)
> 3. [Go 的 noCopy 是什么机制？](https://mp.weixin.qq.com/s/B8WYSCkjoOoZP7a575yX7A)

### 1. 为何需要 noCopy

Go 语言中是使用按值传递的，所以如果直接将一个结构体实例作为参数传递给函数时，函数内部操作的就是一个新的实例了，而且新实例的字段值与原实例的字段值都相同，也就是会进行值拷贝。

但是有些情况下，是不能进行值拷贝的，因为这会破坏实例的一些特性，特别是当变量资源本身带有状态且操作需要配套的时候是不能拷贝的。

比如，对于 Go 中`sync.WaitGroup`，其内部的值维护了相关的状态。如果该类型的实例可以复制，那么状态也会被复制，而一个实例的状态的变更并不会影响到另一个实例的状态变化，这样就会造成死锁等各种问题。

为了避免复制对象实例引起的状态问题，就需要**禁止对带有状态且需要操作配套的实例对象的复制**。

### 2. noCopy 实现

在 Go 中对于实现了`sync.Locker`接口的类型来说，理论上其实例是不能再次被赋值给其他变量的。

而`sync.noCopy`结构体就实现了`sync.Locker`接口：

```go
// noCopy may be embedded into structs which must not be copied
// after the first use.
//
// See https://golang.org/issues/8005#issuecomment-190753527
// for details.
type noCopy struct{}

// Lock is a no-op used by -copylocks checker from `go vet`.
func (*noCopy) Lock()   {}
func (*noCopy) Unlock() {}
```

可以看到，`sync.noCopy`的定义非常简单，就是一个空结构体，而且实现`sync.Locker`接口的方式也就是两个简单的无操作方法。

由于`sync.noCopy`是一个空结构体，所以当其内嵌到其他的结构体中（不为嵌入结构体的最后一个字段）时，将不会占用空间，但是却可以为该结构体提供不允许拷贝的特性。

### 3. noCopy 实例

下面使用`sync.WaitGroup`做示例：

```go
package main

import "sync"

func test(wg sync.WaitGroup) {
  wg.Done()
}

func main() {
  var wg sync.WaitGroup
  wg.Add(1)
  go test(wg)
  wg.Wait()
}
```

在这个例子中，虽然`test`方法会将`wg`的等待数量减 1，但是由于其参数是按值传递的，所以`test`中操作的`wg`已经不是`main`中的`wg`了，这就会造成死锁：

```
fatal error: all goroutines are asleep - deadlock!
```

虽然 Go 不会对实现了`sync.Locker`接口的对象实现赋值时进行报错，但是在**使用`go vet`做静态语法分析时会提示相关的错误**：

```shell
$ go vet main.go
# command-line-arguments
./main.go:5:14: test passes lock by value: sync.WaitGroup contains sync.noCopy
./main.go:12:10: call of test copies lock value: sync.WaitGroup contains sync.noCopy
```

这里的报错已经很明确了：`sync.WaitGroup`中包含了`sync.noCopy`，而这是一个锁定值。

另外，在一些 IDE 中也会对这个代码有所提示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1640868928210-1a6b6203daa7.jpg)

改正的方法也很简单，就是**使用指针方式传递**。Mutex Lock、Cond、Pool、WaitGroup 这些资源都严格要求操作要配套所以这些变量都不应该按值赋值，而应该使用指针引用。

### 4. 引用 noCopy

`sync.noCopy`是未导出的，如果用户代码也想使用`sync.noCopy`有两种方式：一种是自行实现，另一种则是通过间接的方式引用`sync.noCopy`。

比如，grpc DoNotCopy 的实现如下：

```go
// DoNotCopy can be embedded in a struct to help prevent shallow copies.
// This does not rely on a Go language feature, but rather a special case
// within the vet checker.
type DoNotCopy [0]sync.Mutex
```

它使用了`sync.Mutex`的零长数组，不占用空间。而`sync.Mutex`也实现了`sync.Locker`接口。

由于 go vet checker 会检测`sync.Mutex`，就相当于实现了`noCopy`的功能。


