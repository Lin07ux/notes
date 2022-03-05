> [跟着 Go 作者掌握泛型](https://mp.weixin.qq.com/s/uIUOa5o4veejamIY9MUycQ)

在 GopherCon 2021 年大会上，Go 两位作者 Robert Griesemer 和 Ian Lance Taylor 做了泛型相关的演讲（[演讲的视频](https://www.youtube.com/watch?v=Pa_e9EeCdy8)）。

Go 1.18 关于泛型部分，主要有三个特性：

* Type parameters for functions and types 函数和类型的类型参数
* Type sets defined by interfaces 由接口定义的类型集合
* Type interface 类型推断

## 一、类型参数

### 1.1 类型参数列表 Type parameter lists

类型参数列表看起来是带括号的普通参数列表。通常，类型参数以大写字母开头，以强调它们是类型：

```go
[P, Q constraint1, R constraint2]
```

比如，非泛型版本的求最小值的方法：

```go
func min(x, y float64) float64 {
  if x < y {
    return x
  }
  return y
}
```

如果有 int 类型的 min 函数版本需求，得另外写一个类似的函数，这完全是重复代码。

使用泛型版本可以解决该问题：

```go
func min[T constraints.Ordered](x, y T) T {
  if x < y {
    return x
  }
  return y
}
```

泛型版本的 min 和普通版本的 min 的区别如下：

* 泛型版本的 min 多了一个`[T constraints.Ordered]`，这就是类型参数列表，声明了一个类型 T，它的约束是`constraints.Ordered`，即类型 T 满足它规定的条件；
* 参数 x、y 的类型变成了 T，而不再是具体的某个类型(float64)。

这个泛型版本的 min 的调用方式如下：

```go
m := min[int](2, 3)
```

和普通版本的 min 的调用方式也有区别：多了类型参数的传参，也就是`[int]`，对应泛型函数声明中的`[T constraints.Ordered]`。这跟普通的函数参数有点像，调用的时候提供的`int`表明普通函数参数是`int`类型。

### 1.2 实例化

在调用的时候，会进行实例化过程：

1. 用类型实参(type arguments)替换类型形参(type parameters)；
2. 检查类型实参(type arugments)是否实现了类型约束。

如果第 2 步失败，实例化（调用）就失败了。

所以调用过程可以分解为以下两步：

```go
fmin := min[float64]
m := fmin(2.3, 3.4)

// 和下面等价
m := min[float64](2.3, 3.4)
// 相当于 m := (min[float64])(2.3, 3.4)
```

所以，**实例化会产生一个非泛型函数**。

### 1.3 类型的类型参数

类型也可以有类型参数。

比如，下面是一个泛型版的二叉树：

```go
type Tree[T interface{}] struct {
  left, right *Tree[T]
  data        T
}

func (t *Tree[T]) Lookup(x T) *Tree[T]

var stringTree Tree[string]
```

在定义类型`Tree`的时候，还有一个类型参数列表`[T interface{}]`，这跟函数的类型参数语法是一样的，T 相当于是一个具体的类型。所以，之后用到 Tree 类型的地方，T 都要跟着，即使用`Tree[T]`替代`Tree`，包括方法的接收者(receiver)和返回值。

而且实例化泛型类型的时候，也要传入具体的类型，`var stringTree Tree[string]`，和上面两个实例化步骤中的第一步是一样的。

## 二、类型集合 Type sets

函数普通参数列表中的每个值都有一个类型，叫做值参数的类型(the type of value paramters)，这个类型定义了值的集合。比如`float64`定义了浮点数值的集合。

相应的有类型参数的类型(the type of type paramters)，也就是说，类型参数列表中的每个类型参数都有一个类型，这个类型定义了类型的集合，这叫做类型约束(type constraint)：

```go
func min[T contraints.Ordered](x, y T) T
```

这里的`contraints.Ordered`是类型参数列表中的 T 参数的类型，定义了类型的集合，即类型约束。

`contraints.Ordered`是 Go 1.18 内置的一个类型约束，有两个功能：

* 只有值支持排序的类型才能传递给类型参数 T；
* T 类型的值必须支持`<`操作符，因为函数体中使用了该操作符。

### 2.1 类型约束是接口

接口定义了方法集(method sets)：

![method sets](http://cnd.qiniu.lin07ux.cn/markdown/1646102253917-4533a19727b6.jpg)

根据 Go 的规则，类型 P、Q、R 方法中包含了 a、b、c 三个方法，因此它们实现了接口。

所以，反过来可以说，接口也定义了类型集(type sets)：

![type sets](http://cnd.qiniu.lin07ux.cn/markdown/1646102336251-05eadca0f967.jpg)

上图中，类型 P、Q、R 都实现了左边的接口（因为都实现了接口的方法集），因此可以说该接口定义了类型集。

既然接口是定义类型集，只不过是间接定义的：类型实现接口的方法集。而类型约束是类型集，因此完全可以重用接口的语义，只不过这次是直接定义类型集：

![interface defines type sets](http://cnd.qiniu.lin07ux.cn/markdown/1646102417689-f7a11113a346.jpg)

这就是类型约束的语法，通过接口直接定义类型集：

```go
type Xxx interface {
  int | string | bool
}
```

而`constraints.Ordered`的定义如下：

```go
// Ordered is a constraint that permits any ordered type: any type
// that supports the operators < <= >= >.
// If future releases of Go add new ordered types,
// this constraint will be modified to include them.
type Ordered interface {
  Integer | Float | ~string
}
```

`constraints.Ordered`定义了所有整数、浮点数和字符串类型的集合，所以`<`操作符也是支持的。这其中的`Integer`、`Float`也在`constraints`包中有定义。

`~T`意味着包含底层类型 T 的所有类型集合。比如`~string`就包含了 string 类型，以及由 string 类型派生的自定义类型。

> 如果约束中的所有类型都支持一个操作，则该操作可以与相应的类型参数一起使用。

### 2.2 字面量声明约束

除了将约束单独定义为类型外，还可以写成字面值的形式，比如：

```go
[S interface{~[]E}, E interface{}]

// 等同于
[S ~[]E, E interface{}]
```

Go 1.18 中，`any`是`interface{}`的别名，因此可以进一步写为：

```go
[S ~[]E, E any]
```

`E`是切片元素的类型，`~[]E`表示底层是`[]E`切片类型的都符合该约束。

## 三、类型推断

在调用泛型函数时，提供类型实参感觉有点多余。Go 虽然是静态类型语言，但是擅长类型推断。因此 Go 也实现了泛型的类型推断。

调用泛型版的`min`函数时，可以不提供类型实参，而是直接由 Go 进行类型推断：

```go
var a, b, m float64
m := min(a, b)
```

类型推断的细节很复杂，但使用起来还是很简单的。大部分时候，跟普通函数调用没有区别。

### 3.1 推断示例

下面是一个类型推断的示例：

```go
func Scale[E constraints.Integer](s []E, c E) []E {
  r := make([]E, len(s))
  for i, v := range s {
    r[i] = v * c
  }
  return r
}
```

这个函数的目的是希望对 s 中的每个元素都乘以参数 c，最后返回一个新的切片。

接着定义一个类型：

```go
type Point []int32

func (p Point) String() string {
  // 实现细节不重要，忽略
  return "point"
}
```

很显然，`Point`类型的切片可以传递给`Scale`函数：

```go
func ScaleAndPrint(p Point) {
  r := Scale(p, 2)
  fmt.Println(r.String())
}
```

希望对 p 执行`Scale`，得到一个新的 Point 实例，但是发现返回的结果并不是 Point 类型：

```go
func main() {
  p := Point{3, 2, 4}
  ScaleAndPoint(p)
}
```

这会报错：`r.String undefined (type []int32 has no field or method String)`。

可见调用`Scale`来处理 Point 类型后，返回的是`[]int32`类型了。所以应该这样修改`Scale`函数：

```go
func Scale[S ~[]E, E constraints.Integer](s S, c E) S {
  r := make(S, len(s))
  for i, v := range s {
    r[i] = v * c
  }
  return r
}
```

这里加入了泛型`S`以及额外的类型约束`~[]E`，并且返回值类型修改为了`S`。

调用`Scale`函数时，不需要指定约束：`r := Scale[Point, int32](p, 2)`。因为 Go 会进行类型推断。

### 3.2 完整代码

正确的完整代码如下：

```go
package main

import (
  "constraints"
  "fmt"
)

func Scale[S ~[]E, E constraints.Integer](s S, c E) S {
  r := make(S, len(s))
  for i, v := range s {
    r[i] = v * c
  }
  return r
}

type Point []int32

func (p Point) String() string {
  // 实现细节不重要，忽略
  return "point"
}

func ScaleAndPrint(p Point) {
  r := Scale(p, 2)
  fmt.Println(r.String())
}

func main() {
  p := Point{3, 2, 4}
  ScaleAndPrint(p)
}
```

## 四、总结

泛型是一把双刃剑，泛型的加入，无疑增加了复杂度。有些代码写出来可读性会非常差，应该按没有泛型的时候写代码。在出现 Repeat Yourself 的时候，再考虑能不能用泛型重构。

### 4.1 什么时候使用泛型

一般情况，能不用泛型就尽量不用泛型

在演讲中，两位 Go 的开发大佬提到，在以下场景下可以考虑使用泛型：

* 对于 slice、map、channel 等类型，如果它们的元素类型是不确定的，操作这类类型的函数可以考虑使用泛型；
* 一些通用目的的数据结构，比如前面提到的二叉树等；
* 如果一些函数行为相同，只是类型不同，可以考虑使用泛型重构精简。

> 注意：目前 Go 的方法不支持类型参数，所以，如果方法有需要泛型的场景，可以转为函数的形式。

此外，不要为了泛型而泛型。比如，下面的泛型就很糟糕：

```go
func ReadFour[T io.Reader](r T) ([]byte, error)
```

使用非泛型版本即可：

```go
func ReadFour(r io.Reader) ([]byte, error)
```

