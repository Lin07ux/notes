> 转摘：[Gopher 需要知道的几个结构体骚操作](https://mp.weixin.qq.com/s/A4m1xlFwh9pD0qy3p7ItSA)

### 1. struct 的比较

在 Go 语言中，对于 struct 来说，只有所有字段全部都是可比较的（不限大小写是否导出），那么结构体才是可比较的。同时只比较 non-blank 的字段。

示例如下：

```go
type T struct {
  name string
  age  int
  _    float64
}

func main() {
  x := [1]T{{"foo", 1, 0}}
  y := [1]T{{"foo", 1, 1}}
  fmt.Println(x == y) // true
}
```

运行的结果为 true。虽然变量`x`和`y`的最后一个字段的值不同，但是这并不影响两者的比较结果。因为`T`类型的最后一个字段是空白字段。

### 2. DoNotCampare

因为 slice、map、function 类型是不可比较的，只能判断是否为 nil。所以，如果要让一个 struct 不能比较，那么就可以为其添加一个这三种类型的字段即可。

如下，定义了一个不可比较类型：

```go
// DoNotCompare can be embedded in a struct to prevent comparability.
type DoNotCompare [0]func()
```

这个不可比较类型是一个包含 0 个 function 元素的数组，所以占用空间为 0，而且是不可比较的。

将这个类型嵌入到其他的 struct，那么被嵌入的 struct 就是不可比较的：

```go
type DoNotCompare [0]func()

type T struct {
    name string
    age int
    DoNotCompare
}
func main() {
    // ./cmp.go:13:21: invalid operation: T{} == T{} (struct containing
    // DoNotCompare cannot be compared)
    fmt.Println(T{} == T{})
}
```

