`iota`是 Go 中预声明的一个标识符，常用在常量、变量的声明中。**其初始值为 0**，一组多个常量/变量同时声明时，其**值逐行增加**。

`iota`可以看做自增的枚举变量，专门用来初始化。如下：

```go
// 类似枚举的 iota
const (
  c0 = iota // c0 == 0
  c1 = iota // c1 == 1
  c2 = iota // c2 = 2
)

// 简写模式
const (
  c0 = iota // c0 == 0
  c1        // c1 == 1
  c2        // c2 == 2
)

// 注意 iota 逐行增加
const (
  a = 1 << iota // a == 1 (iota == 0)
  b = 1 << iota // b == 2 (iota == 1)
  c = 3         // c == 3 (iota == 2, unused)
  d = 1 << iota // d == 8 (iota == 3)
)

const (
  u         = iota * 42 // u == 0 (untyped integer constant)
  v float64 = iota * 42 // v == 42.0 (float64 constant)
  w         = iota * 42 // w == 84 (untyped integer constant)
)
```

`iota`在连续的一组声明中是递增的，但是在分类的声明中则每次都从 0 开始：

```go
const x = iota // x == 0
const y = iota // y == 0
```



