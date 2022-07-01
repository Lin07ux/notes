> 转摘：[周刊题解：一道正确率很低的 Go 题目](https://mp.weixin.qq.com/s/D6cc11w1bdqZbOr8tm_Vjw)

### 1. 问题

下面的代码输出什么？

```go
package main


import (
  "fmt"
)

func main() {
  m := [...]int{
    'a': 1,
    'b': 2,
    'c': 3,
  }
  m['a'] = 3
  fmt.Println(len(m))
}
```

* A：3
* B：4
* C：100
* D：编译失败

### 2. 解析

这道题目的核心点在于对 rune 字面量的理解和数组的语法。

#### 2.2 数组声明语法

Go 中的数组声明中，可以明确的指定数组的长度，比如`[100]int`，也可以由编译器在编译时根据初始化定义自动推断长度，比如`[...]int{1, 2, 3}`。

而且，Go 数组的初始化赋值时，可以不按照下标顺序的赋值，而是通过指定下标的方式，对特定的元素进行赋值，其语法类似 struct 对象的赋值，只是每个 key 都是一个 int 数值。

Go 语言规范中，关于索引表达式有这么一句话：

> a constant index that is untyped is given type **int**.

比如：

```go
var a := [100]int{
  98: 1,
  99: 2,
}
```

这样，其他未被明确赋值的位置，值就为对应类型的零值。

#### 2.2 rune 字面量

在 Go 语言规范中有这么一句话：

> A run literal represents a **rune constant**, an integer value identifying a Unicode code point. A rune literal is expressed as one or more characters enclosed in single quotes, as in 'x' or '\n'.

也就是说：一个 rune 字面量代表一个 rune 常量。而且 rune 是 int32 类型的别名。

常量分为有类型常量(typed)和无类型常量(untyped)。而字面量属于无类型常量，只不过每个无类型常量都有一个默认类型。比如，`a`字面量是一个无类型常量，它的默认类型是 rune。

**当在上下文中需要一个无类型常量的值时，会进行隐式转换（或使用默认类型）**。

比如：

```go
// 这样赋值正常
const r = 'a'
var a int = r

// 这样不能编译
const r rune = 'a'
var a int = r
```

这就是因为前者没有明确指定`r`常量的类型，那么它就是一个无类型常量，在用它给别的变量`a`赋值时会进行隐式转换，变为 int 类型的值来赋值；而后者已经明确了`r`变量的类型是 rune，那么它就不能赋值给 int 类型的变量了。

### 3. 解答

本题代码中，变量`m`采用的是由编译器自动推断长度的方式定义的 int 数组。而`a`、`b`、`c`是 rune 字面量，但是被当做了 int 数组的下标（索引）。作为 int 类型时，`a`是 ASCII 码 97，`b`是 ASCII 码 98，`c`是 ASCII 码 99。

所以，这道题目就相当于如下的定义：

```go
package main

import "fmt"

func main() {
  m := [...]int{
    97: 1,
    98: 2,
    99: 3,
  }
  m[97] = 3
  fmt.Println(len(m))
}
```

很显然，`m`的长度为 100，输出就是 100。


