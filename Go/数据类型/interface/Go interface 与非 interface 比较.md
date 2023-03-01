### 1. 比较逻辑

在 [Go spec](https://go.dev/ref/spec#Operators) 中针对 interface 与非 interface 的比较有如下表述：

> A value x of non-interface type X and a value t of interface type T are comparable when values of type X are comparable and X implements T. The are equal if t's dynamic type is identical to X and t's dynamic value is equal to x.

**interface 类型变量与非 interface 类型变量进行判断时，首先要求非 interface 类型变量实现了对应的 interface 接口，否则编译不通过。其次要求非 interface 类型变量的值是可比较的**。

> 对于空 interface（也就是`interface{}`）来说，可以认为所有的类型都实现了该接口。

**在满足上面的要求下，interface 类型变量的动态类型、值均与非 interface 类型变量相同时，两个变量判断结果为 true。**

### 2. 问题

下面的代码输出什么？

```go
package main

import "fmt"

func main() {
  var p [100]int
  var m interface{} = [...]int{99: 0}
  fmt.Println(p == m)
}
```

* A：true
* B：false
* C：panic
* D：编译失败

### 3. 解答

首先，变量`m`是一个 interface 类型变量，而且是空 interface；变量`p`是一个长度为 100 的 int 类型数组。

在给`m`赋值的时候，其动态类型为`[100]int`，值为`[0, 0, ... 0]`。

当将`m`与`p`进行判等时：

1. 首先，因为`p`变量的类型是实现了空接口的，所以两者可以比较，能编译通过；
2. 其次，`p`变量的类型`[100]int`是可比较的，而且 array 类型的值在他们的类型、长度和各项值都相等的时候是相等的；
2. 最后，因为`m`的动态类型和`p`的动态类型相同，都是`[100]int`数组类型；而且`p`的零值就是长度为 100、且各项均为 0 的 int 数组，和`m`的动态值相等，故两者相等。

所以上面代码会输出的是 true。

