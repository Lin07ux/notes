> 转摘：[一道正确率只有15%的命名返回值和闭包的问题](https://mp.weixin.qq.com/s/wv2VPC-GP1fc4jyxaMq02Q)
> 
> 原文地址：[https://twitter.com/bwplotka/status/1495002204163678211](https://twitter.com/bwplotka/status/1495002204163678211)

### 1. 问题

下面这段代码的输出结果是多少？

```go
func aaa() (done func(), err error) {
  return func() { print("aaa: done") }, nil
}

func bbb() (done func(), _ error) {
  done, err := aaa()
  return func() { print("bbb: surprise!"); done() }, err
}

func main() {
  done, _ := bbb()
  done()
}
```

### 2. 答案

这段代码会陷入死循环，不会结束。

### 3. 解析

这道题考察的点就是命名返回值+闭包。

把上面的代码换成等效的匿名返回值代码就容易看明白了：

```go
func aaa() (func(), error) {
  var done func()
  done = func() {
    print("aaa: done")
  }
  return done, nil
}

func bbb() (func(), error) {
  var done func()
  done, err := aaa()
  done = func() {
    print("bbb: surprise!")
    done()
  }
  return done, err
}

func main() {
  done, _ := bbb()
  done()
}
```

可以看到，在`bbb`函数中，先从`aaa`函数的运行结果中初始化了`done`变量，然后又为`done`变量重新赋值了一个匿名函数，并在该匿名函数中再次执行了`done`变量对应的函数。这就导致`done`变量是一个包含它自己的函数，所以运行这段代码会导致死循环。

这其实是 Go 语言设计上的一个特性：当 Go 语言的返回值赋值给特殊的“返回参数”时，如果它们被命名了。在`return`之后，可以在函数主体完成后的任何执行过程中引用这些带有这些名称的值，比如在`defer`和闭包中。

这段代码的初始版本中，`bbb`函数内使用了命名返回值`done func(), _ error`，然后使用短变量语法接收了`aaa`函数的返回值，此时`done`并不是一个新的变量，而是返回值中的`done`变量。之后`return`的是一个闭包函数，闭包里的`done`值并不会被提前解析，在`bbb`函数结束后，实际对应的代码就成了命名返回值`done`，变成了递归。

> Go 在多变量声明中，如果其中一个变量是新的，就可以使用短变量声明符号`:=`声明，编译器会进行类型推断和赋值。已经声明的变量不会重新声明，直接在原变量上赋值。

### 4. 改正

如果把`bbb`函数改成如下代码，就能正常执行并输出`bbb: surprise!aaa: done`了：

```go
func bbb() (func(), error) {
  var done func()
  done, err := aaa()
  return func() {
    print("bbb: surprise!")
    done()
  }, err
}
```


