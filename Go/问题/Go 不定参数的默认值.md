### 1. 问题

如下的代码，会打印出什么？

```go
package main

import "fmt"

func foo(a ...int) {
  fmt.Printf("%#v\n", a)
}

func main() {
  foo()
}
```

选项：

- A `[]int{}`
- B `[]int(nil)`
- C panic
- D 编译错误

### 2. 答案

B。

首先，参数`a`的类型是`[]int`，在调用`foo()`方法的时候没有传入任何参数，因此其值就相当于是`[]int`的空值，也就是`nil`。

在打印的时候，格式化参数是`%#v`，会同时打印类型和值。

所以结果就是 B：`[]int(nil)`。

