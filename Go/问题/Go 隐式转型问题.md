> [惊了！原来Go语言也有隐式转型](https://mp.weixin.qq.com/s/IYKx4NkHSLKn2s-0g5_TRw)

### 1. 问题

```go
package main

type MyInt int
type MyMap map[string]int

func main() {
  var x MyInt
  var y int
  x = y // 报错：cannot use y (type int) as type MyInt in assignment
  _ = x
  
  var m1 MyMap
  var m2 map[string]int
  m1 = m2 // 不报错
  m2 = m1 // 不报错
}
```

上面的代码中，`MyInt`和`int`是不同的类型，`MyMap`和`map[string]int`也是不同的类型。为何三个赋值操作只有`x = y`会报错？

### 2. 答案

Go 是强类型安全的静态变异性语言，一般情况下，不同类型的变量是不能在一起进行混合计算的。这是因为 Go 希望开发人员明确知道自己在做什么，这与 C 语言的“信任程序员”原则完全不同。因此需要**以显式的方式通过转型统一参与计算各个变量的类型**。

但同时，Go 中的类型会分为 defined type（具定义类型/具名类型）和非 defined type。**两个类型如果具有相同的底层类型，而且至少有一个是非 defined type，那么他们的实例是可以互相赋值的**。

### 3. 解释

在 Go 内置的原生类型中，**所有的数值类型、string、bool 都是 defined type 类型，用户通过`type`关键字声明的类型也是 defined type**。

比如，下面的`T1`和`T2`都是 defined type：

```go
type T1 int
type T2 T1
```

这也意味着 **map、array、slice、struct、channel 等原生复合类型(composite type)都不是 defined type**。

所以，前面示例代码中，`int`、`MyInt`、`MyMap`都是 defined type，而`map[string]int`不是 defined type。

Go 语言规范中，[关于 Assignability 的规则](https://go.dev/ref/spec#Assignability) 中有下面的一条规定：

> x's type V and T have identical underlying types and at least one of V or T is not a defined type.

意思是说：如果变量`x`的类型 V 与类型 T 具有相同的底层类型，而且 V 和 T 中至少有一个不是 defined type，那么 V 类型的`x`变量可以赋值给 T 类型的变量。

那么，对应上面的示例来说，因为`int`和`MyInt`都是 defined Type，所以它们之间直接赋值是会报错的；虽然`MyMap`是 defined type，但是`map[string]int`不是 defined type，而且`MyMap`的底层类型就是`map[string]int`，所以`m1`和`m2`变量之间相互赋值是没有问题的。

这种隐式转换赋值的情况和 Go 的无类型常量隐式转型很相似，虽然后背后的原理是不同的：

```go
type MyInt int
const a = 1234
var n MyInt = a
```

Go 总体来说是推崇显式哲学的，这种隐式转型一方面是在编译器确保类型安全性的前提下进行的，不会出现溢出或者未定义行为，另一方面也能一定程度的减少代码输入，对开发体验的提升有帮助。

### 4. 示例

下面的例子中，因为有非 defined type 的类型，所以这些赋值都不会编译报错：

```go
package main

type MyMap map[string]int
type MySlice []byte
type MyArray [10]int
type MyStruct struct {
  a int
  b string
}
type MyChannel chan int

func main() {
  var m1 MyMap
  var m2 map[string][int]
  m1 = m2
  m2 = m1
  
  var sl1 MySlice
  var sl2 []byte
  sl1 = sl2
  sl2 = sl1
  
  var arr1 MyArray
  var arr2 [10]int
  arr1 = arr2
  arr2 = arr1
  
  var s1 MyStruct
  var s2 struct {
    a int
    b string
  }
  s1 = s2
  s2 = s1
  
  var c1 MyChannl
  var c2 chan int
  c1 = c2
  c2 = c1
}
```

