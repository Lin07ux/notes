> 转摘：[昨天在 for 循环里 append 元素的同事，今天还在么？](https://mp.weixin.qq.com/s/flSXt6wgv9I_PYlgev7JPg)

### 1. 问题

下面这个代码是否会死循环：

```go
package main

import "fmt"

func main() {
  s := []int{1, 2, 3, 4, 5}
  for _, v := range s {
    s = append(s, v)
    fmt.Printf("len(s)=%v\n", len(s))
  }
}
```

### 2. 答案

不会死循环。

`for range`其实是 Go 的语法糖，在循环开始前会获取切片的长度`len(切片)`，然后再执行这些次数的循环。

### 3. 解释

`for range`的源码注释如下：

```go
// The loop we generate
//   for_temp := range
//   len_temp := len(for_temp)
//   for index_temp = 0; index_temp < len_temp; index_temp++ {
//     value_temp = for_temp[index]
//     index = index_temp
//     value = value_temp
//     original body
//   }
```

所以上面的代码会被编译器认为是如下的代码：

```go
func main() {
  s := []int{1,2,3,4,5}
  for_temp := s
  len_temp := len(for_temp)
  for index_temp := 0; index_temp < len_temp; index_temp++ {
    value_temp := for_temp[index_temp]
    _ = index_temp
    value := value_temp
    // 以下是 original body
    s =append(s, value)
    fmt.Printf("len(s)=%v\n",len(s))
  }
}
```

所以这个循环不会无限进行，输出结果如下：

```
len(s)=6
len(s)=7
len(s)=8
len(s)=9
len(s)=10
```

