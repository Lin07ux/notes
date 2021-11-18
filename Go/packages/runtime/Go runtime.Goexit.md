> 转摘：[动图图解！怎么让goroutine跑一半就退出？](https://mp.weixin.qq.com/s/iSdSoKbldZ-fFk5LPzsmxA)

### 1. 源码

`runtime.Goexit()`函数位于`src/runtime.panic.go`文件中：

```go
// Goexit terminates the goroutine that calls it. No other goroutine is affected.
// Goexit runs all deferred calls before terminating the goroutine. Because Goexit
// is not a panic, any recover calls in those deferred functions will return nil.
//
// Calling Goexit from the mani goroutine terminates that goroutine without
// func main returning. Since func main has not returned, the program
// continues execution of other goroutines. If all other goroutines
// exit, the program crashes.
func Goexit() {
  gp := getg()
  var p _panic
  ...
  for {
    d := gp._defer
    ...
    reflectcallSave(&p, unsafe.Pointer(d.fn), deferArgs(d), uint32(d.siz))
    ...
  }
  goexit1()
}

// Finishes execution of the current goroutine
func goexit1() {
  if raceenabled {
    racegoend()
  }
  if trace.enabled {
    traceGoEnd()
  }
  mcall(goexit0)
}

// goexit continuation on g0.
func goexit0(gp *g) {
  // 获取当前 G
  _g_ := getg()

  // 将当前 G 的状态设置为 _Gdead
  casgstatus(gp, _Gruning, _Gdead)
  
  // 全局协程数减一
  if isSystemGoroutine(gp, false) {
    atomic.Xadd(&sched.ngsys, -1)
  }
  
  // 省略各种清空重置逻辑
  ...
  
  // 清除 M 与当前 G 的关联
  dropg()
  
  // 把这个 G 放回到 P 的本地协程队列中，放不下就放到全局协程队列
  gfput(_g_.m.p.ptr(), gp)
  
  // 其他情况的清理
  ...
  
  // 重新调度
  schedule()
}
```

### 2. 作用

从`runtime.Goexit()`函数的注释和代码中可以知道，这个函数主要的作用就是结束一个 G 的执行，同时不会影响到其他的 G。

在使用该函数结束一个 G 时：

1. 先执行当前 G 中所有的 `defer` 方法

    因为 `Goexit` 与 `panic` 是不同的，所以 `defer` 中的 `recover` 将会得到 `nil`；

2. 然后执行`runtime.goexit0()`函数

    - 将当前 G 的状态设置为`_Gdead`；
    - 将当前 G 从 M 上摘下，放到 P 的本地队列中；
    - 重新调度别的可执行的 G。

![](http://cnd.qiniu.lin07ux.cn/markdown/3457d13cb3aec172c41944b40ec38c7d.gif)

状态为`_Gdead`的 goroutine 可以在后续被复用，或者在一定时候被 GC 清理掉。

### 3. goexit() 的用途

除了在代码中直接调用`runtime.Goexit()`函数来结束 goroutine，每个 G 在新建的时候也会由 runtime 自动其底部添加`runtime.goext()`函数栈，这个操作是在`runtime/proc.go`中的`newproc1()`方法中完成的：

```go
func newproc1(fn *funcval, argp unsafe.Pointer, narg int32, callergp *g, callerpc uintptr) {
    _g_ := getg()        // 获取当前 G
    _p_ := _g_.m.p.ptr() // 获取当前 G 所在的 P
    newg := gfget(_p_)   // 创建一个新 G
 
    // 底部插入goexit
    newg.sched.pc = funcPC(goexit) + sys.PCQuantum 
    newg.sched.g = guintptr(unsafe.Pointer(newg))

    runqput(_p_, newg, true) // 把新创建的 G 放到 P 中
 
    // ...
}
```

所以每次 debug 的时候，就能看到函数栈底部有个`runtime.goexit()`函数。因为`main()`函数也是一个 goroutine，所以它的栈底也是`runtime.goexit()`：

![](http://cnd.qiniu.lin07ux.cn/markdown/1637119319487-46258b4cfb1b.jpg)

### 4. os.Exit() 和 runtime.Goexit() 的区别

两者都有退出的含义，但是退出的对象是不同的：

* `os.Exit()` 退出整个进程
* `runtime.Goexit()` 退出当前的协程

所以，如果使用`os.Exit()`来退出，那么当前 G 中的 defer 函数就无法执行了。


