> 转摘：
> 
> 1. [Go 的 defer 的特性还是有必要要了解下的！！！](https://mp.weixin.qq.com/s/ZxObt_KSgPfr5ZPwG4rCVQ?forceh5=1)
> 2. [深入剖析 defer 原理篇 —— 函数调用的原理？](https://mp.weixin.qq.com/s/2iyrDewtM_V2Xs8WULQnTw)
> 3. [Golang 最细节篇 — 解密 defer 原理，究竟背着程序猿做了多少事情？](https://mp.weixin.qq.com/s/ZgTCSj-PZMTiMCR4FtBeyA)

## 一、基础

Go 中的 defer 是一种延迟调用方式，由`defer`关键字注册的函数调用会在当前所在的函数返回之前执行。

可以在一个函数中通过`defer`注册多个延迟调用，而且这些注册的延迟调用会按照注册的顺序倒序依次执行。

Go defer 的主要作用提现在如下两个方面：

1. 配套的两个行为代码放在一起：创建&释放、加锁&释锁、前置&后置，这样会使代码更易读，变成体验更优秀；
2. panic recover：由于 Go 特殊的异常处理方式，对于严重错误的恢复必须要在 defer 注册的函数中进行。

### 1.1 特性

Go defer 注册的函数有如下一些特性：

#### 1.1.1 延迟调用

defer 注册的函数会在其所在函数返回之前调用。核心点：

1. 延迟调用：`defer`语句本身不论在函数中的哪个位置，其注册的方法只会在函数返回前调用；
2. 上下文：`defer`关键字一定是处于函数上下文中，也就是说，**`defer`必须放在函数内部**。

比如：

```go
func main() {
  defer println("defer")
  println("main")
}
```

这个例子中，会先打印出`main`，然后再打印出`defer`。

#### 1.2 LIFO

一个函数中可以**多次使用`defer`注册函数**，这些注册的函数会**按照栈式执行，后入先出**。

比如：

```go
func main() {
  for i := 1; i <= 6; i++ {
    defer println("defer -->", i)
  }
}
```

注册时是按照 1、2、3、4、5、6 的顺序进行，执行的时候就是逆序执行，所以会打印出：

```
defer --> 6
defer --> 5
defer --> 4
defer --> 3
defer --> 2
defer --> 1
```

#### 1.3 绑定作用域

**`defer`和函数作用域绑定**，也就是说：

1. defer 注册的函数会绑定当前所在函数的作用域，而且只能使用在注册时，当前函数中已经声明的变量，但是变量的值是可以变化的；
2. defer 注册函数时，需要指定必须的参数，而且参数的值在注册时就已经确定了，不会随着当前函数的执行而发生改变；
3. defer 语句一定要在函数内使用，否则会报语法错误。

比如：

```go
func main() {
	c := 1
	a := 1

	defer func(i int) {
		fmt.Printf("%v %v", i, c) // 1 2
	}(a)

	a = 2
	c = 2
}
```

上面的代码中，defer 注册延迟函数时，指定了参数`i`的值为`a`，而且取的是当前值 1。而变量`c`使用的是 defer 所在函数作用域中的`c`变量，其值取的是执行时的值，也就是`main`方法执行完成时变量`c`的值。

#### 1.4 异常恢复

defer 注册的延迟调用在发生 panic 时依旧可以执行，这就使得 **defer 延迟调用能够进行 panic recover 操作**。 

Go 不鼓励异常的编程模式，但是也保留了 panic-recover 这个异常会捕获的机制，所以 defer 机制就显得尤为重要，甚至是必不可少的。因为如果没有一个无视异常、永保调用的 defer 机制，很有可能就会发生各种资源泄露、死锁等问题。

1. defer 在 panic 异常场景也能确保调用；
2. recover 必须和 defer 结合才有意义。

比如：

```go
func main() {
  defer func() {
    if e := recover(); e != nil {
      println("defer recover")
    }
  }()
  panic("throw panic")
}
```

`main`方法中抛出了 panic，但是 defer 注册的延迟调用还能被执行，于是这个 panic 就被捕获到了，所以会输出`defer recover`。

### 1.2 使用

#### 1.2.1 panic-recover

recover 必须和 defer 配合使用，常见示例如下所示：

```go
func main() {
  defer func() {
    if v := recover(); v != nil {
      _ = fmt.Errorf("Panic=%v", v)
    }
  }
}
```

#### 1.2.2 同步

在同步等待中，执行完相关的逻辑就需要解除等待。在最佳实践中，一般将加等待和减等待的代码写在相近的位置。

比如：

```go
var wg sync.WaitGroup

for i := 0; i < 2; i++ {
  wg.Add(1)
  go func() {
    defer wg.Done()
    // 其他逻辑
  }()
}

wg.Wait()
```

#### 1.2.3 锁

同样的，加锁之后也要有配套的锁释放。

比如：

```go
mu.RLock()
defer mu.RUnlock()
```

需要注意的是：加锁之后其后续的代码都会在锁内，所以加锁后的代码应该要足够精简和快速。如果加锁后的逻辑依旧比较复杂，就不能使用这种方式来释放锁了，而要采取手动释放的方式。

#### 1.2.4 资源释放

某些资源是临时创建的，作用域只存在于当前函数中，用完之后需要销毁，这种场景也适用 defer 来释放。

**释放就在创建的下一行**，这是个非常好的编程体验，能极大的避免资源泄露，因为可以尽可能的避免忘记释放，而且不论是否发生异常都会释放。

比如：

```go
// 新建一个客户端资源
cli, err := clientv3.New(clientv3.Config{Endpoints: endpoints})
if err != nil {
  log.Fatal(err)
}
// 释放客户端资源
defer cli.Close()
```

## 二、原理

简单那来讲，defer 关键字就是注册记录一个稍后执行的函数，而且在注册的时候把函数名和参数都确定并保存下来。等当前函数执行`return`之后再执行这个注册的延迟函数。

所以 Go 会记录 defer 注册的函数、参数，并设置返回数据空间，然后在合适的时候完成延迟函数的调用，并最终结束当前函数的执行。

### 2.1 数据结构

defer 注册延迟函数时，会生成一个`_defer`结构体实例，并挂载到当前 goroutine 上。

`_defer`结构体定义如下：

> 展示的为 Go 1.13 版本的定义，后续版本比这个稍微复杂一些，加了一些开放编码优化需要的字段。

```go
type _defer struct {
  siz     int32    // 参数和返回值的内存大小
  started bool
  heap    bool     // 区分该结构是在栈上分配还是在堆上分配的
  sp      uintptr  // sp 计数器值，栈指针
  pc      uintptr  // pc 计数器值，程序计数器
  fn      *funcval // defer 传入的函数地址，也就是延后执行的函数
  _panic  *_panic  // panic that is running defer
  link    *_defer  // 下一个 _defer 实例，组成单向链表
}
```

一个函数内可以有多次 defer 调用，一个 goroutine 可以有多个函数调用层级，所以`_defer`结构体中设置了`link`字段来指向下一个`_defer`实例，从而组成一个单向链表。`_defer`链表中的起始`_defer`是挂在 goroutine 的`_defer`字段上。最终组成的效果如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641553626649-35aa81fb95a9.jpg)

而且，还有一个重点：`_defer`结构只是一个 header，只包含一些基础信息和相关指针，结构紧跟的是延迟函数的参数和返回值的空间，大小由`_defer.siz`指定。这块内存的值在 defer 关键字执行的时候填充好。这也意味着，**延迟函数的参数是预计算的**。

![](http://cnd.qiniu.lin07ux.cn/markdown/1641703185208-f6dad9151cb7.jpg)

### 2.2 内存分配

编译器在遇到 defer 关键字时，会自动的添加一些“用户不可见”的函数，这些函数就是 defer 功能的实现：

1. deferproStack/deferproc 创建并分配`_defer`结构内存，把回调函数初始化进去，挂到链表中；
2. deferreturn 设定 defer 的返回处理

`deferproStack`函数是在栈内存上分配`_defer`结构，而`deferproc`是需要在堆上分配`_defer`结构的。栈上分配内存比堆上分配要快多了，而绝大部分的场景都是可以在栈上分配的，所以`deferproStack`函数会比`deferproc`的整体性能好很多。

>  Go 1.13 提升了 defer 的性能，就是将之前的`deferproc`函数在一般情况下替换为`deferprocStack`。

一个 defer 是分配在栈上还是分配在堆上，在编译阶段就能确定了。那么什么时候分配在栈上、什么时候分配在堆上，则可以在编译器相关的文件(`src/cmd/compile/internal/gc/ssa.go`)中看到：

```go
func (s *state) stmt(n *Node) {
  //...
  case ODEFER:
    d := callDefer
    if n.Esc == EscNever {
      d = callDeferStack
    }
  //...
}
```

可以看到，是分配在堆上还是栈上，是由`n.Esc`来决定的。`n.Esc`是`ast.Node`的逃逸分析的结果，其值是在逃逸分析的函数`esc`(src/cmd/compile/internal/gc/esc.go)里设定的：

```go
func (e *EscState) esc(n *Node, parent *Node) {
  //...
  case ODEFER:
    if e.loopdepth == 1 { // top level
      n.Esc = EscNever // force stack allocation of defer record (see ssa.go)
    }
  //...
}
```

这里`e.loopdepth`等于 1 时才会将`n.Esc`设置为`EscNever`。而`e.loopdepth`是用于检测嵌套循环作用域的。

所以，**defer 如果在嵌套作用域的上下文中，那么就可能导致`_defer`分配在堆上**。比如：

```go
func main() {
  for i := 0; i < 2; i++ {
    defer func() {
      _ = i
    }()
  }
}
```

这个例子编译后就会使用`deferproc`函数在堆上分配`_defer`内存空间。

#### 2.2.1 deferprocStack 栈上分配

`deferprocStack`代码很简答：

```go
// 进入到这个函数之前，就已经在栈上分配好了内存空间（编译器自动分配的，rsp 往下扩展即可）
func deferprocStack(d *_defer) {
  gp := getg()
  
  // siz 和 fn 在进行到这个函数之前就已经被赋值了
  d.started = false
  // 标明是栈上分配
  d.heap = false
  // 获取 caller 函数的 rsp 寄存器值
  d.sp = getcallerrsp()
  // 获取到 caller 函数的 pc(rip)寄存器值，pc 值就是 deferprocStack 的下一行指令
  d.pc = getcallerpc()
  
  // 把这个 _defer 结构作为链表的起始节点，挂载到 goroutine 的链表中
  *(*uintptr)(unsafe.Pointer(&d_panic)) = 0
  *(*uintptr)(unsafe.Pointer(&d.link)) = uintptr(unsafe.Pointer(gp._defer))
  *(*uintptr)(unsafe.Pointer(&gp._defer)) = uintptr(unsafe.Pointer(d))
  
  // 注意，特殊的返回，不会触发延迟调用的函数
  return0()
}
```

小结：

1. 由于是栈上分配内存的，所以其实调用到`deferprocStack`之前，编译器就已经把`_defer`结构的函数准备好了；
2. `_defer.heap`字段用来标识这个结构体分配在栈上；
3. 保存上下文，把 caller 函数的 rsp、pc(rip)寄存器的值保存到`_defer`结构体；
4. `_defer`作为一个节点挂载到链表的头部，并挂载到 goroutine 的`_defer`字段上。

一个 goroutine 上可能会有多个函数调用，一个函数调用也可能有多个 defer 延迟函数注册，在执行的时候会按照 defer 的 sp 来区分。

![](http://cnd.qiniu.lin07ux.cn/markdown/1641712982519-c7af51e0fc1f.jpg)

#### 2.2.2 deferproc 堆上分配

堆上分配的函数`deferproc`代码相对较多，但核心逻辑也与`deferprocStack`类似，简化逻辑如下：

```go
func deferproc(siz int32, fn *funcval) { //arguments of fn follow fn
  // 获取 caller 函数的 rsp 寄存器
  sp := getcallersp()
  argp := uintptr(unsafe.Pointer(&fn)) + unsafe.Sizeof(fn)
  // 获取 caller 函数的 pc(rip) 寄存器值
  callerpc := getcallerpc()
  
  // 分配 _defer 内存结构
  d := newdefer(siz)
  if d._panic != nil {
    throw("deferproc: d.panic != nil after newdefer")
  }
  
  // _defer 结构体初始化
  d.fn = fn
  d.pc = callerpc
  d.sp = sp
  switch siz {
  case 0:
    // Do nothing.
  case sys.PtrSize:
    *(*uintptr)(deferArgs(d)) = *(*uintptr)(unsafe.Pointer(argp))
  defer:
    memmove(deferArgs(d), unsafe.Pointer(argp), uintptr(siz))
  }
  
  // 注意，特殊的返回，不会触发延迟调用的函数
  return0()
}
```

小结：

1. 与栈上分配不同，`_defer`结构在该函数中分配时是调用`newdefer`分配结构体，会先从 pool 缓存池里查看是否有可以直接取用的，没有的话就调用`mallocgc`从堆上分配内存；
2. `deferproc`接受入参`siz`和`fn`，这两个参数分别标识延迟函数的参数和返回值的内存大小，以及延迟函数地址；
3. `_defer.heap`字段标识这个结构体分配在堆上；
4. 保存上下文，把 caller 函数的 rsp、pc(rip) 寄存器的值保存到`_defer`结构体；
5. `_defer`作为一个节点挂载到链表。

### 2.3 deferreturn 执行延迟调用

编译器遇到 defer 语句时还会插入一个`deferreturn`表示要执行注册的延迟调用。代码如下：

```go
func deferreturn(arg0 uintptr) {
  gp := getg()
  // 获取到最前的 _defer 节点
  d := gp._defer
  // 函数递归终止条件(d 链表遍历完成)
  if d == nil {
    return
  }
  // 获取 caller 函数的 rsp 寄存器
  sp := getcallersp()
  if d.sp != sp {
    // 如果 _defer.sp 和 caller 的 sp 值不一致，说明这个 _defer 结构
    // 不是该 caller 函数注册的，那么直接返回
    return
  }
  
  switch d.siz {
  case 0:
    // Do nothing.
  case sys.PtrSize:
    *(*uintptr)(unsafe.Pointer(&arg0) = *(*uintptr)(deferArgs(d))
  defer:
    memmove(unsafe.Pointer(&arg0), deferArgs(d), uintptr(d.siz))
  }
  
  // 获取到延迟回调函数地址
  fn := d.fn
  d.fn = nil
  
  // 把当前的 _defer 节点从链表中摘除
  gp._defer = d.link
  // 释放 _defer 内存（主要是堆上才会需要处理，栈上的内存随着函数执行完，栈收缩就回收了）
  freedefer(d)
  // 执行延迟回调函数
  jmpdefer(fn, uintptr(unsafe.Pointer(&arg0)))
}
```

小结：

1. 遍历 defer 链表，一个个的执行，顺序链表从前往后执行，执行一个摘除一个，直到链表为空；
2. `jmpdefer`负责跳转到延迟回调函数执行指令，执行结束之后，跳转回`deferreturn`继续执行；
3. `_defer.sp`的值可以用来判断哪些是当前 caller 函数注册的，这样就能保证只执行自己函数注册的延迟回调函数。

举个例子，`a() -> b() -> c()`，a 调用 b，b 调用 c，而 a、b、c 三个函数都有个 defer 注册延迟函数，那么自然是`c()`函数返回的时候，执行`c`注册的延迟函数。

### 2.4 jmpdefer 跳转到延迟函数

`jmpdefer`是一段非常简短的汇编代码，但是非常重要，实现的功能是：跳转到 defer 延迟函数执行指令。

在理解`jmpdefer`做了什么的时候，一定要理解函数调用的基础知识。当 Go 的`return`关键字执行的时候，触发`call`调用函数`deferreturn`，在`deferreturn`中摘除`_defer`节点，然后出个执行，执行的入口就是调用`jmpdefer`来实现。所以，明面上的调用关系是：

```
-> deferred function (延迟回调函数)
-> jmpdefer (汇编 diam)
-> deferreturn (执行调用链)
-> caller (defer 所在的 caller 函数)
```

但其实真实的栈帧是只有两个：

```
-> deferred function
-> caller
```

未说明栈帧只有两个？因为这里是特殊的、巧妙的实现：

```asm
TEXT runtime.jmpdefer(SB), NOSPLIT, $0-16
    // 取出延迟回调函数 fn 地址
    MOVQ  fv+0(FP), DX
    // 取出 caller 函数的 rsp 值
    MOVQ  argp+8(FP), BX
    // rsp 的值设置成 caller 的 rsp 值往下 8 字节
    // 也就是 after CALL，压栈的 8 字节算在栈帧以内
    LEAQ  -8(BX), SP
    // 还原 caller 栈帧寄存器
    // restore BP as if deferreturn returned (harmless if framepointers not in use
    MOVQ  -8(SP), BP
    // 重要操作！这个把压栈在栈顶的值修改了
    // 之前压的是 caller 函数内，调用 deferreturn 之后下一行指令
    // 现在压的是 call runtime.deferreturn 这行值
    SUBQ  $5, (SP)
    // 取出 fn 函数指令地址，存到 rbx 寄存器
    MOVQ  0(DX), BX
    // 跳到延迟回调函数执行
    JMP   BX
```

指令解析：

1. 汇编语句`$0-16`说明，数字 0 这个函数栈帧为 0（也就是说没有栈帧，因为没有局部变量或者其他的需要保存的），数字 16 说明入参数为 16 个字节。参数和返回值的大小是声明给调用者看的，调用者根据这个数字可以构造栈，caller 为 callee 准备需要的参数，callee 设置返回值到对应的位置；
2. 最前面两行的`MOVQ`指令，就是把入参取出来而已，第一个参数是延迟函数(deferred func)地址，保存到 rdx 寄存器，第二个参数是 caller 函数的 rsp 值，保存到 rbx 寄存器；
3. 然后回复 rbp 的值（恢复成 caller 的栈基）；
4. 然后再回复 rsp 的值，恢复成 caller 的栈顶值（调用 deferreturn 之前的值），并且（重点），要显式把 rsp 往下扩展 8 字节，类似 call 指令的压栈效果。而压栈的值要手动修改成 caller 里面`call deferreturn`的指令地址；
5. 最后使用`JMP`指令跳转到延迟函数的指令地址执行（注意了：`JMP`指令和`CALL`的最重要的区别就是前者只会跳转，不会压栈从而导致 rsp 变化）。

调用顺序：

```
caller ->deferreturn -> deferred() -> caller
```

注意一下`SUBQ  $5, (SP)`指令，在二进制反汇编代码中可以看到类似如下的`call runtime.deferreturn`调用代码：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641717910704-976a2c483094.jpg)

1. caller 调用 deferreturn 只会压栈的值是`0x48e8c1`，因为这个值是`call deferreturn`的下一行指令，call 调用的时候就是把当前指令的下一行压栈的。
2. 然后，因为在`SUBQ  $5, (SP)`指令之前，`$rsp`是会恢复到 caller 函数的栈顶值（并且已经往下减 0x8 了，模拟往下扩展），那么`SP`(`rsp`)里存储的值刚好就是`call deferreturn`的时候压栈的值，也就是`0x48e8c1`；
3. 然后把`0x48e8c1`减去`0x5`，结果就是`0x48e8bc`，这个地址刚好就指向了`call runtime.deferreturn`这行指令。

也就是说：`jmpdefer`最终会刚好使得调用链重新定位到了`deferreturn`函数上。这样就是先了`caller -> [deferreturn -> jmpdefer -> deferred func -> defererturn]`的递归循环，而当`gp._defer == nil`的时候则结束递归循环。

图示如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641718445317-8bfc15624231.jpg)

这个相当于编译器手动管理了函数栈帧，通过修改栈上的值，让延迟调用函数执行完调用`ret`的时候，重新跳转到`deferreturn`函数进行循环执行。

> 这个也是早年黑客尝试用的一种 hack 手段，修改函数压栈的值，跳转到一些 hack 的指令上去执行代码。

## 三、问题

### 3.1 defer 怎么传递参数

#### 3.1.1 预计算参数

在前面描述`_defer`数据结构的时候说到内存结构如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641718679973-88a958b1455e.jpg)

`_defer`作为一个 header，延迟回调函数`deferred`的参数和返回值紧接着`_defer`放置，而这个参数值是在`defer`执行的时候就设置好了，也就是预计算参数，而非等到执行 deferred 函数的时候才去获取。

举个例子，执行`defer func(x, y)`的时候，x、y 这两个实参是计算出来的，Go 中的函数调用都是值传递，会将 x、y 的值拷贝到`_defer`结构体之后。

示例如下：

```go
func main() {
  var x = 1
  defer println(x)
  x += 2
  return
}
```

这个程序执行的输出是 1，因为`defer`执行的函数是`println`，参数是`x`，其值在执行`defer`语句执行的时候就确认了。

#### 3.1.2 deferred 的参数准备

deferred 延迟函数执行的参数已经保存在和`_defer`一起的连续内存块了。那么执行 deferred 函数的时候，参数自然不是直接去`_defer`的地址找，因为这里走的是标准的函数调用。

在 Go 语言中，一个函数的参数由 caller 函数准备好，比如说：一个`main() -> A(7) -> B(a)`形成类似如下的栈帧：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641719206591-b5eb9b8fc84d.jpg)

所以，`deferreturn`除了跳转到 deferred 函数指令，还需要做一个时期：把 deferred 延迟回调函数需要的参数准备好（空间和具体的值）。那么就是如下 diam 来做的事情：

```go
func deferreturn(arg0 uintptr) {
  //...
  switch d.siz {
  case 0:
    // Do nothing.
  case sys.PtrSize:
    *(*uintptr)(unsafe.Pointer(&arg0)) = *(*uintptr)(deferArgs(d))
  default:
    memmove(unsafe.Pointer(&arg0), deferArgs(d), uintptr(d.siz))
  }
  //...
}
```

arg0 就是 caller 用来防止 deferred 参数和返回值的栈地址。这段代码的意思就是：把`_defer`预先准备好的参数 copy 到 caller 栈帧的某个地址（arg0）。

### 3.2 一个函数多个 defer 语句

由于`_defer.link`的存在，多个 defer 语句注册的延迟调用可以串成一个单向链表，表头存放在`goroutine._defer`中。在执行的时候，按照`_defer.rsp`来区分是否需要执行。并且，注册的时候是把新的 deferred 放在表头，执行的时候是从前往后执行，所以这里是按照 LIFO 特性执行的。

![](http://cnd.qiniu.lin07ux.cn/markdown/1641779625674-c78b45e16f10.jpg)

### 3.3 defer 和 return 返回值运行顺序

#### 3.3.1 函数调用过程

函数调用的过程总结如下：

1. Go 的一行函数调用语句其实并非原子操作，会对应多行汇编指令，包括参数设置和`call`指令执行。`call`汇编指令的内容也有两个：
    
    - 返回地址压栈（会导致 rsp 值往下增长`rsp - 0x8`）
    - callee 函数地址加载到 pc 寄存器。

2. Go 的一行函数返回`return`语句也并非原子操作，会对应多行汇编指令，包括返回值设置和`ret`指令执行。`ret`汇编指令的内容也有两个：

    - 指令寄存器 pc 恢复为 rsp 栈顶保存的地址，跳转到 caller 函数；
    - rsp 往上缩减`rsp + 0x8`。

3. 参数设置在 caller 函数里，返回值设置在 callee 函数里；

4. rsp、rbp 两个寄存器是栈帧的最重要的两个寄存器，这两个值划定了栈帧；

5. rbp 寄存器的常见的作用是栈基寄存器，也可以作为通用寄存器，常用于调试作用。

#### 3.3.2 先返回值还是先执行 defer 函数

defer 注册的延迟函数的调用时机在 Go 官方文档中有明确的说明：

> That is, if the surrounding function returns through an explicit return statement, deferred functions are executed **after any result parameters are set by that return statement** but **before the function returns to its caller**.

也就是说，defer 函数链调用是在设置了返回值之后，但是在运行指令上下文返回到 caller 函数之前。

所以有 defer 注册的函数，执行`return`语句之前，对应执行三个操作序列：

1. 设置返回值
2. 执行 deferred 链表
3. ret 指令跳转到 caller 函数。

这样就**允许在 deferred 函数中修改返回值了**。

#### 3.3.3 defer 和返回值示例

```go
func f1() (r int) {
  t := 1
  defer func() {
    t = t + 5
  }()
  return t
}

func f2() (r int) {
  defer func() {
    r = r + 5
  }(r)
  return 1
}

func f3() (r int) {
  defer func() {
    r = r + 5
  }()
  return 1
}
```

这几个函数的执行结果分别为：1、1、6。

* 函数 f1 执行`return t`之后：

    1. 设置返回值`r = t`，这个时候局部变量 t 的值等于 1，所以返回值 r 的值也为 1；
    2. 执行 deferred 函数，更新局部变量 t 的值为`t + 5`，也就是 6；
    3. 执行汇编`ret`指令，跳转到 caller 函数。
    4. 所以 f1 的返回值就是 1。

* 函数 f2 执行`return 1`语句之后：

    1. 设置返回值`r = 1`；
    2. 执行 deferred 函数，接收的参数是 r，而 r 在预计算参数的时候值为 0。由于 Go 传参为值传递，所以就是将值 0 赋值给了 deferred 函数的参数变量 r。所以执行之后 deferred 的参数变量 r 的值为 5；
    3. 执行汇编`ret`指令，跳转到 caller 函数；
    4. 所以 f2 的返回值就是 1。

* 函数 f3 执行`return 1`语句之后：

    1. 设置返回值`r = 1`；
    2. 执行 deferred 函数，`r = r + 5`，也就是设置 r 的值为 6。由于这里的 r 为 f2 的返回值 r 变量，所以也就是修改了 f2 的返回值 r 的值为 6；
    3. 执行汇编`ret`指令，跳转到 caller 函数；
    4. 所以 f3 的返回值就是 6。

## 四、总结

1. defer 关键字执行对应的是`_defer`数据结构。在 Go1.1 - Go1.12 期间一直是在堆上分配，在 Go1.13 之后优化成栈上分配`_defer`结构，性能提升明显。Go1.14 之后还有一个开放编码的优化，类似于内联，继续提升性能；
2. `_defer`数据结构大部分场景是分配在栈上，但是遇到循环嵌套的场景会导致结构分配在堆上。所以在使用 defer 的时候需要注意场景，避免嵌套设置，出现性能问题；
3. `_defer`对应一个注册的延迟回调函数 deferred，其参数和返回值空间都紧跟着`_defer`结构体设置，是一块连续的内存空间。`_defer`可以理解为 header，`_defer.siz`指明参数和返回值所占的空间大小；

4. 同一个协程里 defer 注册的函数都挂在一个链表中，表头为`goroutine._defer`：

    - 新元素插入在最前面，遍历执行的时候是从前往后执行，具有 LIFO 特性；
    - 不同的函数注册的 deferred 函数都在一个链表上，以`_defer.sp`字段值区分。

5. deferred 的参数是预计算的，也就是在 defer 关键字执行的时候参数值就已经确定下来了，并复制到`_defer`的内存块后面，执行的时候再 copy 到栈帧对应的位置上；

6. `jmpdefer`修改了默认的函数调用行为（修改了压栈指令），实现了一个 deferred 链表循环执行直至结束的调用链；

7. `return`对应 3 个动作的复合操作：设置返回值、执行 deferred 函数链表、`ret`指令跳转。

