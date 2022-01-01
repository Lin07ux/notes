## 一、基础

## 二、高级

### 2.1 结构体比较

如果结构体的全部成员都是可以比较的，那么结构体也是可以比较的，此时可以使用`==`或`!=`运算符对两个机构体进行比较。这会对两个结构体的每个成员都进行比较：如果每个成员都相等，则这两个结构体相等；否则他们不相等。

> [Golang Sepc Comparison_operators](https://go.dev/ref/spec#Comparison_operators) 描述常见类型比较运算的方式如下：
> 
> 	1. In any comparison, the first operand must be assignable to the type of the second operand, or vice versa.
> 
> 2. The equality operators == and != apply to operands that are comparable. The ordering operators <, <=, >, and >= apply to operands that are ordered.
> 
> 3. Struct values are comparable if all their fields are comparable. Two struct values are equal if their corresponding non-blank fields are equal.
> 
> 	4.	Slice, map, and function values are not comparable. However, as a special case, a slice, map, or function value may be compared to the predeclared identifier nil. Comparison of pointer, channel, and interface values to nil is also allowed and follows from the general rules above.

```go
type Point struct { X, Y int }

p := Point{1, 2}
q := Point{2, 1}

// 下面两个判断是等价的
fmt.Println(p.X == q.X && p.Y == q.Y) // false
fmt.Println(p == q)                   // false
```

和其他可比较类型一样，可比较的结构体类型也可以用于 map 的 key 类型：

```go
type Address struct {
  port int
  host string
}

hits := make(map[Address]int)
hits[address{443, "golang.org"}]++
```

### 2.2 结构体嵌入和匿名成员

一个结构体中可以包含另一个结构体作为其子成员，这就是结构体的嵌入。结构体可以嵌入任意的其他结构体类型，甚至是它自身的结构体类型。

比如，下面定义的是一些简单的几何形状：

```go
type Point struct {
  X, Y int
}

type Circle struct {
  Center Point
  Radius int
}

type Wheel struct {
  Circle Circle
  Spokes int
}
```

结构体的嵌入会使其定义更加清晰明了，但是也会造成成员的访问较为繁琐：

```go
var w Wheel
w.Circle.Center.X = 8
w.Circle.Center.Y = 8
w.Circle.Radius = 5
w.Spokes = 20
```

Go 语言有一个特性：**只声明一个成员对应的数据类型而不指定成员的名称**，这类成员就叫做**匿名成员**。匿名成员的数据类型必须是命名的类型或指向一个命名类型的指针。得益于**匿名嵌入**的特性，**可以直接访问叶子属性而不需要给出完整的属性路径**。这样就能简化嵌套类型属性的访问：

```go
type Circle struct {
  Point
  Radius int
}

type Wheel struct {
  Circle
  Spokes int
}

var w Wheel
w.X = 8       // equivalent to w.Circle.Point.X = 8
w.Y = 8       // equivalent to w.Circle.Center.Y = 8
w.Radius = 5  // equivalent to w.Circle.Radius = 5
w.Spokes = 20
```

匿名成员也有一个隐式的名称，就是其所属命名类型的名称，所以子结构体属性的完整访问路径依然有效。

但是结构体字面值并没有简短表示匿名成员的语法，因此下面的语句都不能编译通过：

```go
w = Wheel{8, 8, 5, 20} // compile error: unknown fields
w = Wheel{X: 8, Y: 8, Radius: 5, Spokes: 20} // compile error: unknown fields
```

实际上，**嵌入匿名成员的方式**，不仅仅是**获得了匿名成员类型的所有属性**，而且**也获得了该类型导出的全部方法**。这个机制可以用于将一些有简单行为的对象**组合成有复杂行为的对象**。

匿名成员还有如下特性：

1. 因为匿名成员其实也有名称，因此不能同时包含两个类型相同的匿名成员，这会导致命名冲突。
2. 同时，因为成员的名字是由其类型隐式地决定的，所以匿名成员也有可见性的规则约束。
3. 即便匿名成员的类型不是导出的（导致匿名成员的隐式名称也不是导出的，比如改成小写字母开头的`point`和`circle`），依然可以用间断性是访问匿名成员嵌套的成员。但是**在包外则不可访问（无论是全路径还是简短语法）**。
4. 匿名成员简短访问的特性只是对访问嵌套成员的点运算符提供了简短的语法糖。
5. 匿名成员并不要求必须是结构体类型，其他任何命名的类型都可以作为结构体的匿名成员。

## 三、特性

