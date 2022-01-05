> 转摘：[Go 面试官：连 nil 切片和空切片一不一样都不清楚？](https://mp.weixin.qq.com/s/vEVsMa2WMT8x2yrcNyfFTg)

### 1. 问题

下面代码的输出结果是什么？

```go
package main

import (
  "fmt"
  "reflect"
  "unsafe"
)

func main() {
  var s1 []int
  s2 := make([]int, 0)
  s4 := make([]int, 0)
  
  fmt.Printf("s1 pointer:%+v, s2 pointer:%+v, s4 pointer:%+v\n", *(*reflect.SliceHeader)(unsafe.Pointer(&s1)), *(*reflect.SliceHeader)(unsafe.Pointer(&s2)), *(*reflect.SliceHeader)(unsafe.Pointer(&s4)))
  fmt.Printf("%v\n", (*(*reflect.SliceHeader)(unsafe.Pointer(&s1))).Data == (*(*reflect.SliceHeader)(unsafe.Pointer(&s2))).Data))
  fmt.Printf("%v\n", (*(*reflect.SliceHeader)(unsafe.Pointer(&s2))).Data == (*(*reflect.SliceHeader)(unsafe.Pointer(&s4))).Data)
}
```

### 2. 答案

输出结果如下：

```go
s1 pointer:{Data:0 Len:0 Cap:0}, s2 pointer:{Data:824634207952 Len:0 Cap:0}, s4 pointer:{Data:824634207952 Len:0 Cap:0}
false // nil 切片和空切片指向的数组地址不一样
true  // 两个空切片指向的数组地址是一样的，都是 824634207952
```

### 3. 解析

nil 切片和空切片指向的地址不一样：

* **nil 切片引用数组指针地址为 0**，表示不指向任何实际地址；
* **空切片的引用数组指针地址是有的，且固定为一个值**。

nil 切片和空切片最大的区别在于指向的数组引用地址是不一样的：

![](http://cnd.qiniu.lin07ux.cn/markdown/1640836650571-2873678c6ea6.jpg)

而所有的空切片指向的数组引用地址都是一样的：

![](http://cnd.qiniu.lin07ux.cn/markdown/1640836668897-3f646894b2a9.jpg)


