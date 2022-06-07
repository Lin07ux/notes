> 转摘：[Go 为什么要设计 iota 常量？](https://mp.weixin.qq.com/s/QMnr9p4fzbIv8bKtoZnUfw)

### 1. 基础说明

`iota`是 Go 中预声明的一个标识符，常用在枚举常量、枚举变量的声明中。**其初始值为 0**，在对一组多个常量/变量同时声明时，其**值逐行增加**。

在功能上，`iota`关键字表示从 0 开始的整数常量；在作用上可以简化使用自动递增数字的常量定义，非常方便。

实际上，`iota`不是某个方法的缩写，而是希腊字母表的第九个字母。它是数学符号的典型，代表一个非常小的东西。

其实`iota`已经是迭代器的一个约定式命名了。在 [The Go Programming Language Specification](https://go.dev/ref/spec#Iota) 中存在着对 iota 的明确定义和说明：

![](http://cnd.qiniu.lin07ux.cn/markdown/1654605619014-b5521c936a3d.jpg)

> 在一个常量声明中，**预先声明的标识符 iota 代表连续的无类型的整数常量。它的值是该常量声明中各 ConstSpec 的索引，从 0 开始。**

除 Go 语言外，在 APL、C++、Scheme 中均有 iota 常量的存在（设计）。比如，Scheme 的 iota 的签名如下：

```scheme
iota count [start step]
```

### 2. 使用

**基本使用：**

之前定义一个递增的枚举值需手动的依次指定其值：

```go
const (
  a = 0
  b = 1
  c = 2
)
```

使用`iota`则可以如下定义：

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
```

**跳跃使用：**

**`iota`在一组声明中其值是逐行增加的**，可以不使用它，但是它的值依旧会逐行增加：

```go
// 注意 iota 逐行增加
const (
  a = 1 << iota // a == 1 (iota == 0)
  b = 1 << iota // b == 2 (iota == 1)
  c = 3         // c == 3 (iota == 2, unused)
  d = 1 << iota // d == 8 (iota == 3)
)

const (
  a = iota // a= 0
  _        // unused (iota = 1)
  b        // b = 2
  c        // c = 3
)

const (
  u         = iota * 42 // u == 0 (untyped integer constant)
  v float64 = iota * 42 // v == 42.0 (float64 constant)
  w         = iota * 42 // w == 84 (untyped integer constant)
)
```

**一行多次使用**

由于`iota`是逐行增加，所以在一行中，`iota`的值是同一个，不会变化：

```go
const (
  bit0, mask0 = 1<<iota, 1<<iota - 1 // bit0 == 1, mask0 == 0  (iota == 0)
  bit1, mask1                        // bit1 == 2, mask1 == 1  (iota == 1)
  _, _                               //                        (iota == 2, unused)
  bit3, mask3                        // bit3 == 8, mask3 == 7  (iota == 3)
)
```

### 3. 注意点

`iota`的逐行递增是在连续的一组声明中才有效。如果在不同的声明组中（不同的`const/var`声明下），它的值每次都是从 0 开始的。如下：

```go
const x = iota // x == 0
const y = iota // y == 0
```


