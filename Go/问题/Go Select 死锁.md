> 转摘：[Go select 竟然死锁了。。。](https://mp.weixin.qq.com/s/4cM4MmGux1odyIxbq3a0BQ)

### 1. 问题

对于如下的代码：

```go
package main

import "sync"

func main() {
  var wg sync.WaitGroup
  foo := make(chan int)
  bar := make(chan int)
  wg.Add(1)
  go func() {
    defer wg.Done()
    select {
    case foo <- <-bar:
      println("foo bar")
    default:
      println("default")
    }
  }()
  wg.Wait()
}
```

在实际执行中会造成死锁。可以通过该链接测试：[Go Play](https://play.studygolang.com/p/kF4pOjYXbXf)。

### 2. 原因

对于 select 语句，运行时会按源码顺序对每一个 case 子句进行求值，而且这个求值的处理是只针对发送或接收操作的额外表达式。

也就是说，在执行 select 相关代码时，会对每个 case 中不涉及最终从通道中接收或者发送到通道的操作进行求值。

在[Go Spec](https://golang.org/ref/spec#Select_statements)中对此有相关的说明：

> For all the cases in the statement, the channel operands of receive operations and the channel and right-hand-side expressions of send statements are evaluated exactly once, in source order, upon entering the "select" statement. The result is a set of channels to receive from or send to, and the corresponding values to send. Any side effects in that evaluation will occur irrespective of which (if any) communication operation is selected to proceed. Expressions on the left-hand side of a RecvStmt with a short variable declaration or assignment are not yet evaluated.

所以，对于上面的代码，因为在执行 select 语句的时候，需要先对`case foo <- <-bar`子句进行求值，也就是对`<-bar`进行求值。但是因为`bar`是一个空的 channel，从其中取值时会造成阻塞。于是，整个 select 语句都被阻塞在了`<-bar`语句中。

那么，避免阻塞的方式也就明确了：在另一个 gorouteine 中（如 main goroutine）对`bar`这个 channel 传入一个值。

### 3. 验证

select 语句执行时会对每个 case 都进行求值，可以使用如下的代码进行验证([Go Play](https://play.studygolang.com/p/DkpCq3aQ1TE))：

```go
package main

import "fmt"

func main() {
  ch := make(chan int)
  go func() {
    select {
    case ch <- getVal(1):
      fmt.Println("in first case")
    case ch <- getVal(2):
      fmt.Println("in second case")
    default:
      fmt.Println("default")
    }
  }()
  
  fmt.Println("The val:", <-ch)
}

func getVal(i int) int {
  fmt.Println("getVal, i=", i)
  return i
}
```

这段代码执行时，可能会执行到第一个 case，也可能会执行到第二个 case。但是无论最终 select 选择了哪个 case，`getVal(1)`和`getVal(2)`都会依次执行，且都有如下的两行输出：

```
getVal, i= 1
getVal, i= 2
```

### 4. 提升

对于如下的代码([Go Play](https://play.studygolang.com/p/zoJtTzI7K5T))：

```go
package main

import (
  "fmt"
  "time"
)

func talk(msg string, sleep int) <-chan string {
  ch := make(chan string)
  go func() {
    for i := 0; i < 5; i++ {
      ch <- fmt.Sprintf("%s %d", msg, i)
      time.Sleep(time.Dutation(sleep) * time.Millisecond)
    }
  }()
  return ch
}

func fanIn(input1, input2 <- chan string) <-chan string {
  ch := make(chan string)
  go func() {
    select {
    case ch <- <-input1:
    case ch <- <-input2:
    }
  }()
  return ch
}

func main() {
  ch := fanIn(talk("A", 10), talk("B", 1000))
  for i := 0; i < 10; i++ {
    fmt.Printf("%q\n", <-ch)
  }
}
```

这里一样也会阻塞住，但是阻塞的位置是在 main 中的`for`循环中：

* select 语句选择执行某个 case 前，会对`<-input1`和`<-input2`分别进行求值，得到相应的`A x`和`B x`（`x`的值为 0~5）。
* 这两者都取到值之后，select 会选定一个 case 进行执行，所以`<-input1`和`<-input2`的结果中必然有一个被丢弃了，也就是不会被写入到`ch`通道中。所以一共只会写入 5 次，也就是只能输出 5 次。
* main 函数中对`ch`要循环十次，每次都要等待从`ch`中取值，但是只能取到 5 次，所以后面就卡在这里了。

可以将`talk`方法中增加一行打印，看到具体的执行过程：

```go
func talk(msg string, sleep int) <-chan string {
  ch := make(chan string)
  go func() {
    for i := 0; i < 5; i++ {
      fmt.Println("talk:", msg, i) // 输出执行信息
      ch <- fmt.Sprintf("%s %d", msg, i)
      time.Sleep(time.Dutation(sleep) * time.Millisecond)
    }
  }()
  return ch
}
```

多次执行输出的结果并不稳定，但是可以得到类似如下的输出：

```
talk:  A 0
talk:  B 0
"A 0"
talk:  A 1
talk:  A 2
talk:  B 1
"A 1"
talk:  A 3
talk:  B 2
"B 2"
talk:  A 4
talk:  B 3
"A 3"
talk:  B 4
"A 4"
```

可以看到，A 和 B 在每次执行 select 的时候都被执行了，直到迭代完成。但是每次执行完 select 之后，只会得到一个输出，也就是只有一个结果通过 select 语句写入到了`ch`中。

可以通过修改`fanIn`方法来得到正确的输出：

```go
func fanIn(input1, input2 <- chan string) <-chan string {
  ch := make(chan string)
  go func() {
    select {
    case t := <-input1:
      ch <- t
    case t := <-input2:
      ch <- t
    }
  }()
  return ch
}
```

此时，因为 select 语句中没有除从通道中读取或写入的额外操作，也就是不需要先对`<-input2`和`<-input2`进行取值再决定选择哪个 case 执行了。

执行程序，可以得到如下的结果：

```
talk:  A 0
"A 0"
talk:  B 0
"B 0"
talk:  A 1
"A 1"
talk:  A 2
"A 2"
talk:  A 3
"A 3"
talk:  A 4
"A 4"
talk:  B 1
"B 1"
talk:  B 2
"B 2"
talk:  B 3
"B 3"
```

可以看到，在这种情况下，select 会等到两个 case 中的任何一个满足条件时就选定对应的 case 进行执行，从而使得`ch`中可以写满 10 个值，整个程序也就不会被阻塞死了。


