> 转摘：[Go切片与技巧（附图解）](https://mp.weixin.qq.com/s/IQRHWNUnxiaCDleayNVRVg)

### 1. 定义

Go 中 Array **数组的定义包含类型和长度**，这两者同时组成了一个特定的数组类型：

```go
// 数组类型：定义指定长度和元素类型
var a [4]int

// 数组中数据的存取
a[0] = 1
i := a[0]

fmt.Println(i) // 1
fmt.Println(a[1]) // 0 (数组不需要显式的初始化，其元素默认被设置为零值)
fmt.Println(a[4]) // panic: invalid array index 4 (out of bounds for 4-element array)
```

### 2. 值类型

**Go 的数组是值类型**，不像 C 语言数组变量是指向第一个元素的指针。所以当把数组变量传递或者赋值的时候，其实是做 copy 操作。

比如，下面的例子 a 赋值给 b，修改 a 中的元素并不会影响 b 中的元素：

```go
a := [2]string{"johnny", "太白技术"}
b := a
a[0] = "xujiajun"
fmt.Println(a) // 输出：[xujiajun 太白技术]
fmt.Println(b) // 输出：[johnny 太白技术]
```

为了避免复制，可以传递一个指向数组的指针，例如：

```go
func double(arr *[3]int) {
  for i, num := range *arr {
    (*arr)[i] = num * 2
  }
}

a := [3]int{1, 2, 3}
double(&a)
fmt.Println(a) // [2 4 6]
```

