> [深度细节 | Go 的 panic 秘密都在这](https://mp.weixin.qq.com/s/bOnc2sNx4jCImT550YM7Xw)

Go 的 panic 有三种产生方式：

* 主动调用：调用`panic()`函数；
* 编译器的隐藏代码：比如除零；
* 内核发送给进程信号：比如非法地址访问。

三种方式都归一到`panic()`函数的调用，说明 **Go 的 panic 知识一个特殊的函数调用结果，是语言层面的处理**。

### 1. panic 是什么？

panic 本质上是一个结构体，会挂载在当前的 goroutine 上，成为一个单向链表。

panic 对应的数据结构为`_panic`，定义如下：

```go
// runtime/runtime2.go
type _panic struct {
  argp      unsafe.Pointer
  arg       interface{} // panic() 函数调用时的参数
  link      *_panic // 指向下一个 _panic 结构体
  recovered bool // 是否已恢复
  aborted   bool // 是否以中断
}
```

`_panic`结构体中有两个字段较为重要：

* `link`：是一个指向`_panic`结构体的指针，表明`_panic`和`_defer`类似，可以串成一个单向链表；
* `recovered`：panic 是否恢复了就是看对应的`_panic`实例中的`recovered`字段是否为 true，而`recover()`方法就是修改这个字段的值的。

### 2. 什么情况下会有多个 panic？

panic 触发之后，会继续执行 goroutine 中的 defer，如果此时在 defer 中又触发了 panic，那么就会造成一个 goroutine 上有多个 panic。

这也是`_panic.link`字段存在的作用。

goroutine 中的 panic 是通过`_panic`字段进行挂载的，并形成一个链表：

```go
type g struct {
  // ...
  _panic  *_panic  // panic 链表，这是最里面的一个
  // ...
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1640432305987-216b3f093bdc.jpg)

### 3. panic 如何让 Go 进程退出？

panic 的实现在一个叫做`gopanic()`的函数，位于`runtime.panic.go`文件中。panic 机制最重要的就是`gopanic()`函数了，所有的细节尽在于此。

panic 之所以会显得晦涩，主要有两个原因：

1. 嵌套 panic 的时候，`gopanic()`函数会有**递归执行**的场景；
2. 程序指令跳转并不是常规的函数压栈、弹栈。在 recovery 的时候，是**直接修改指令寄存器的结构体**，从而直接越过了`gopanic()`函数后面的逻辑，甚至是多层`gopanic()`递归的逻辑。

`gopanic()`源码如下：

```go
// runtime/panic.go
func gopanic(e interface{}) {
  gp := getg()

  // ... 省略一些判断条件
  
  // 在栈上分配一个 _panic 结构体，并修改为当前 goroutine 的 panic 链表中最新的一个
  var p _panic
  p.link = gp._panic
  gp._panic = (*_panic)(noescape(unsafe.Pointer(&p)))
  
  // ... 省略一些其他处理
  
  for {
    // 取出当前最近的 defer 函数
    d := gp._defer
    if d == nil {
      break // 没有 defer 就没有 recover 的时机，直接跳到循环体外，退出进程
    }
    
    // 进入到这个逻辑就说明之前已经有 panic 了，现在又有 panic 发生，这里一定处于递归之中了
    if d.started {
      if d._panic != nil {
        d._panic.aborted = true
      }
      d._panic = nil
      // 把这个 defer 从链表中摘掉
      if !d.openDefer {
	     d.fn = nil
	     gp._defer = d.link
	     freedefer(d)
	     continue
      }
    }
    
    // 标记 _defer.started = true，在 panic 递归时用到
    d.started = true
    // 记录当前 _defer 对应的 panic
    d._panic = (*_panic)(noescape(unsafe.Pointer(&p)))
    
    // 执行 defer 函数
    reflectcall(nil, unsafe.Pointer(d.fn), deferArgs(d), uint32(d.siz), uint32(d.siz))
    
    // defer 执行完成，把这个 defer 从链表里摘掉
    gp._defer = d.link
    
    // 取出 pc、sp 寄存器的值
    pc := d.pc
    sp := unsafe.Pointer(d.sp)
    
    // 如果 _panic 被设置成恢复，那么就到此为止
    if p.recovered {
      gp._panic = p.link
      
      // 如果前面还有 panic，并且是标记了 aborted 的，那么也摘掉
      for gp._panic != nil && gp._panic.aborted {
        gp._panic = gp._panic.link
      }
      
      // 恢复到业务函数堆栈上执行代码
      gp.sigcode0 = uintptr(sp)
      gp.sigcode1 = pc
      
      // 注意：恢复的时候 panic 函数将从此处跳出，当前 gopanic 调用结束，再往后面的代码永远不会执行了
      mcall(recovery)
      throw("recovery failed") // mcall should not return
    }
  }
  
  // 打印错误信息和堆栈，并且退出进程
  preprintpanics(gp._panic)
  fatalpanic(gp._panic) // should not return
  *(*int)(nil) = 0 // not reached
}
```

> 这里简化了函数的逻辑，去除了很多判断条件等。

这段代码可以分为**循环内**和**循环外**两部分去理解：

* 循环内：程序不断的执行 defer 函数，是否恢复成正常的指令执行是在循环内决定的；
* 循环外：一旦走到循环外，说明这个`_panic`没有被处理，程序就会抛出异常错误并退出。

#### 3.1 循环内

循环内的一般流程为：

1. 遍历 goroutine 的 defer 链表，获取到最新（最后压入）的`_defer`实例；
2. 设置`_defer.started = true`，并绑定当前的 panic 到`_defer`上；
3. 执行`_defer.fn`函数；
4. 从 goroutine 的`_defer`链表中将当前的`_defer`摘掉；
5. 判断当前的`_panic`是否已被恢复(`_panic.recovered == true`)，然后进行相应的操作：
    - 如果已恢复，那么就重置 pc/sp 寄存器的值（一般从 deferreturn 指令前开始执行），将当前 goroutine 投递到调度队列，等待执行；
6. 重复以上步骤。

这里，只有在执行`_defer.fn`的时候有机会执行`recover()`函数，来将`_panic.recovered`的值设置为 true。所以`recover()`函数就必须在 defer 中才能生效。

同时，因为这个循环只会获取并执行当前 goroutine 中的 defer 函数，所以在其他的 goroutine 的 defer 中执行 recover 是不会将当前的 goroutine 中的 panic 进行恢复的。

#### 3.2 嵌套 panic

因为在 defer 中也有可能再次触发 panic，那么此时就会造成 panic 的嵌套，使得`gopanic()`函数在嵌套执行。所以在循环体内，获取到 defer 实例之后，还需要先判断下其是否已经开始执行了(`_defer.started`)。如果已经开始执行了，那么就要设置`_defer._panic.aborted = true`。

这也是为什么在 defer 函数执行完成之后，在判断 panic 已经恢复的时候，需要将前面已经设置了`aborted`标识的 panic 也从链表中摘除掉。

下面是一个嵌套 panic 的例子：

```go
func main() {
  println("=== begin ===")
  defer func() { // defer 0
    println("=== come in defer_0 ===")
  }()
  defer func() { // defer 1
    recover()
  }()
  defer func() { // defer 2
    panic("panic 2")
  }()
  
  panic("panic 1")
  println("=== end ===")
}
```

在执行的时候，panic 会被 recover 掉，输出结果如下：

```
=== begin ===
=== come in defer_0 ===
```

执行路线如下：

```
main
    gopanic // 第一次
        1. 取出 defer_2，设置 started
        2. 执行 defer_2 
            gopanic // 第二次
                1. 取出 defer_2，将其 panic 设置成 aborted
                2. 把 defer_2 从链表中摘掉
                3. 执行 defer_1
                    - 执行 recover
                4. 摘掉 defer_1
                5. 执行 recovery，重置 pc 寄存器，跳转到 defer_1 注册时候，携带的指令，一般是跳转到 deferreturn 上面几个指令

    // 跳出 gopanic 的递归嵌套，直接到执行 deferreturn 的地方；
    defereturn
        1. 执行 defer 函数链，链条上还剩一个 defer_0，取出 defer_0；
        2. 执行 defer_0 函数
    // main 函数结束
```

这个例子中因为 recover 是在第二个 panic 后面压入到 defer 链表的。所以当触发第二个 panic 后，会被 recover 给捕获。如果将 recover 和第二个 panic 压入的顺序进行调换，那么就会因为 panic 未被捕获而抛出错误。

#### 3.3 recovery 函数

在循环执行 defer 函数时，如果发现`_panic.recovered == true`，那么就会调用`mcall(recovery)`来执行所谓的恢复。

`recovery`函数很简单，就是恢复 pc、sp 寄存器，然后重新把 goroutine 投递到调度队列中：

```go
// runtime/panic.go
func recovery(gp *g) {
  // 取出栈寄存器和程序计数器的值
  sp := gp.sigcode0
  pc := gp.sigcode1
  // 重置 goroutine 的 pc、sp 寄存器
  gp.sched.sp = sp
  gp.sched.pc = pc
  // 重新投入调度队列
  gogo(&gp.shced)
}
```

由于 pc 寄存器指向指令所在的地址，所以更改 pc 的值之后，就会跳转到其他地方执行指令了，而不是继续顺序的执行`gopanic()`函数后面的指令了，不会回来了。

在注册 defer 延迟函数的时候，`_defer.pc`字段的赋值就是 new 一个`_defer`结构体的下一条指令。

举个例子：如果是栈上分配的话，就是在`deferprocStack`，在执行`mcall(recovery)`后就会跳转到这个位置。后续就走`deferreturn`的逻辑了，也就是继续执行就绪的`_defer`函数链上的延迟函数。

#### 3.4 for 循环外

走到 for 循环外的话，那么程序就 100% 要退出了。因为`fatalpanic()`函数里面打印一些堆栈信息之后，就会直接调用`exit()`方法来退出进程了。

`fatalpanic()`函数的源码如下：

```go
func fatalpanic(msgs *_panic) {
  // 1. 打印协程堆栈
  // ...
  
  // 2. 退出协程
  systemstack(func() {
    exit(2)
  })
  
  *(*int)(nil) = 0 // not reached
}
```

所以，panic 之所以会让 Go 进程退出，就是因为没有被 recover，然后走到了`fatalpanic()`函数中，调用了`exit(2)`被退出了。

### 4. 总结

1. `panic()`会使 Go 进程退出，是因为调用了`exit`函数；
2. **`recover()`**并不是说只能在 defer 里面调用，而是**只能在 defer 函数中才能生效**，因为在 defer 延迟函数中才有可能遇到`_panic`结构体；
3. `recover()`所在的 defer 函数必须和 panic 挂载在同一个 goroutine 上，不能跨协程，因为`gopanic()`函数只会执行当前 goroutine 上的延迟函数；
4. panic 的恢复本质上就是 pc 寄存器的重置，直接修改程序执行的指令位置，跳转到原本 defer 函数执行完后应该跳转到的位置（deferreturn）；
5. recovery 只会，因为已经跳转到别的指令处继续执行了，就不会继续`gopanic()`函数后续的对`fatalpanic()`方法的调用上了；
6. panic 对应的是一个普通的函数，因为函数能够嵌套，所以 panic 也能嵌套触发。而且 panic 可以通过其`link`字段串联成一个单向链表结构。


