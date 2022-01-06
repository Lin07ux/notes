> 转摘：[Go 的 defer 的特性还是有必要要了解下的！！！](https://mp.weixin.qq.com/s/ZxObt_KSgPfr5ZPwG4rCVQ?forceh5=1)

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


