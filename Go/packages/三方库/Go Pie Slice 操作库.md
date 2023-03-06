[elliotchance/pie](https://github.com/elliotchance/pie) 封装了对 Slice 和 Map 的常用操作，能满足工作中的大部分需求。在其实现中，对各函数的参数都做了类型的限制，具有高性能、类型安全的特点。

Pie 包目前有两个版本：

* v2：需要 Go 1.18+ 版本支持；
* v1：Go 1.17 及以下的版本使用。

### 1. 目标特性

* **类型安全**：无论是 v1 还是 v2 版本，都对类型做了限制，所以不会遇到运行时类型错误的问题；
* **高性能**：与原生 Go 实现一样快；
* **Nil 安全**：该库的所有函数都能接收 nil 参数，并将其视作空切片/空 Map，而不会引起 panic；
* **对原数据无影响**：该库所有的函数对传入的切片参数都不会做修改。

pie 做了很多策略以提升操作性能：

* 在已知切片长度的情况下尽可能给 slice 分配固定长度，减少 append 时的内存申请次数；
* 使用切片截取的形式，避免内存分配。

### 2. 功能方法

* 切片元素满足条件判断

    - `All()` 切片中的所有元素都满足指定条件则返回 true
    - `Any()` 切片中的任一元素满足指定条件则返回 true

* 切片排序

    - `AreSorted()` 切片是否已是有序的
    - `Sort()` 对切片元素进行排序
    - `SortUsing()` 使用指定的条件对切片进行排序，无稳定性保证
    - `SortStableUsing()` 使用指定的条件对切片进行排序，并且具有稳定性

* 切片去重

    - `AreUnique()` 切片中的元素是否没有重复的
    - `Unique()` 对切片的元素进行去重

* 切片截取

    - `Top()` 获取切片的前 n 个元素
    - `Bottom()` 获取切片的后 n 个元素
    - `DropTop()` 丢掉切片的前 n 个元素，并返回剩余的元素切片

* 切片集合运算

    - `Diff()` 计算两个切片的差集
    - `Intersect()` 计算两个或多个切片的交集

* 切片算术运算(只对 Integer 和 Float 类型的切片有效)

    - `Max()` 返回切片中的最大元素
    - `Min()` 返回切片中的最小元素
    - `Product()` 对切片所有元素进行乘积计算
    - `Sum()` 对切片所有元素进行求和计算
    - `Average()` 计算切片所有元素的平均值

* 切片元素处理

    - `Each()` 对切片每个元素都执行指定的操作
    - `Map()` 对切片每个元素都进行指定处理，返回由处理后的结果组成的切片
    - `Filter()` 使用指定条件过滤切片元素，返回满足条件的元素组成的切片
    - `Flat()` 铺平切片
    - `Reducer()` 对切片元素进行逐步迭代

* Map 操作

    - `Keys()` 获取 map 的所有键
    - `Values()` 获取 map 的所有值

### 3. 使用实例

Go 1.18+ 以上可以使用 Pie/v2 包，其使用泛型实现，使用更方便：

```go
package main

import (
  "fmt"
  "strings"
  
  "github.com/elliotchance/pie/v2"
)

func main() {
  name := pie.Of([]string{"Bob", "Sally", "John", "Jane"}).
    FilterNot(func(name string) bool {
      return strings.HasPrefix(name, "J")
    }).
    Map(strings.ToUpper).
    Last()
    
  fmt.Println(name) // "SALLY"
}
```

Go 1.17 及以下版本因为不支持泛型，所以需要使用 pie/v1 包，所有的函数都只能针对特定类型的切片。实际上，在 v1 包中，pie 定义了一组特定的切片类型，比如代表 string 切片的`pie.Strings`类型，代表 float64 切片的`pie.Float64s`类型。所以，使用 pie/v1 包的时候需要先定义要使用的类型变量：

```go
package main

import (
  "fmt"
  "strings"
  
  "github.com/elliotchance/pie/pie"
)

func main() {
  var names pie.Strings // 对应的底层类型为 []string
  names = []string{"Bob", "Sally", "John", "Jane"}
  
  name := names.FilterNot(func(name string) bool {
    return strings.HasPrefix(name, "J")
  }).
  Map(strings.ToUpper).
  Last()
  
  fmt.Println(name) // "SALLY"
}
```