> 转摘：[Go 中神奇的 init 函数](https://mp.weixin.qq.com/s/V0tcspI3CsokfeT-tpELxg)

Go 中`init`函数是一个特殊的函数，可以在所有的程序执行开始前被调用，并且每个包下可以有多个`init`函数。

* `init`函数先于`main`函数自动执行；
* 每个包中可以有多个`init`函数；
* **每个源文件中也可以有多个`init`函数**；
* `init`函数没有输入参数、返回值，也未声明，所以无法引用；
* 不同包的`init`函数按照包导入的依赖关系决定执行顺序；
* 无论包被导入多少次，`init`函数都只会被调用一次，也就是只执行一次。

### 1. 执行顺序

`init`函数的执行顺序按照如下规则确定：

1. 按照包加载的顺序和依赖的顺序，先初始化依赖的包，然后再初始化当前包；
2. 如果当前包下有多个`init`函数，首先按照源文件名的字典顺序，从前往后的依次执行包内每个源文件中的`init`函数；
3. 若一个源文件中出现多个`init`函数，则按照出现顺序从前往后依次执行。

可以参考下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1634457653583-a3bd10af97e3.jpg)

另外，每个包中会按照`const > var > init`的顺序进行初始化：首先进行常量的初始化，然后是变量，最后才会执行`init`函数。

针对报级别的变量初始化顺序，Go 官方文档给出这样的一个例子：

```go
var (
  a = c + b // == 9
  b = f()   // == 4
  c = f()   // == 5
  d = 3     // == 5 after initialization has finished
)
```

**包源文件中变量的初始化按出现的顺序从前往后进行；如果某个变量需要依赖其他变量，则被依赖的变量会先进行初始化。**所以这个例子中，初始化顺序是：`d --> b --> c --> a`。

### 2. 注意问题

1. 编程时不要依赖`init`的顺序；
2. 一个源文件中可以包含多个`init`函数，代码比较长时可以考虑分为多个`init`函数；
3. 复杂逻辑不建议使用`init`函数，会增加代码的复杂性，导致可读性下降；
4. 包级别的变量初始化、`init`函数执行，这两个操作都是在同一个 goroutine 中调用的，按顺序调用，一次一个包；
5. 在`init`函数中也可以启动 goroutine，也就是在初始化的同时启动新的 goroutine，这并不会影响初始化顺序；
6. `init`函数不应该依赖任何在`main`函数里创建的变量，因为`init`函数的执行是在`main`函数之前；
7. `init`函数在代码中不能被显示的调用，也不能被引用（赋值给函数变量），否则会出现编译错误；
8. 导入包不要出现循环依赖，这样会导致编译失败；
9. Go 程序仅仅想要一个包的`init`函数执行，而不是用该包下的其他内容，可以这样使用：`import _ "test_package"`；

### 3. 使用场景

`init`函数由于会在包第一次被加载的时候自动执行，且只执行这一次，所以可以有很多的使用场景，例如：饿汉单例模式、服务注册、数据库或其他中间件初始化链接等。

Go 的标准库中也有许多地方使用到了`init`函数，比如`pprof`工具会在`init`函数里面进行路由注册：

```Go
// go/1.15.7/libexec/src/cmd/trace/pprof.go
func init() {
  http.HandleFunc("/io", serveSVGProfile(pprofByGoroutine(computePprofIO)))
  http.HandleFunc("/block", serveSVGProfile(pprofByGoroutine(computePprofBlock)))
  http.HandleFunc("/syscall", serveSVGProfile(pprofByGoroutine(computePprofSyscall)))
  http.HandleFunc("/sched", serveSVGProfile(pprofByGoroutine(computePprofSched)))

  http.HandleFunc("/regionio", serveSVGProfile(pprofByRegion(computePprofIO)))
  http.HandleFunc("/regionblock", serveSVGProfile(pprofByRegion(computePprofBlock)))
  http.HandleFunc("/regionsyscall", serveSVGProfile(pprofByRegion(computePprofSyscall)))
  http.HandleFunc("/regionsched", serveSVGProfile(pprofByRegion(computePprofSched)))
}
```

