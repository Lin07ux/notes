> 转摘：[Go1.18 快讯：字符串 Clone 有什么用？](https://mp.weixin.qq.com/s/9y3er4imzjP3paJrWe_JkA)

`strings.Clone()`函数在 Go 1.18 版本中添加，用来深度复制一个字符串，使得新字符串和源字符串不使用相同的底层数据。

### 1. 源码

源码如下：

```go
// Clone returns a fresh copy of s.
// It guarantees to make a copy of s into a new allocation,
// which can be important when retaining only a small substring
// of a much larger string. Using Clone can help such programs
// use less memory. Of course, since using Clone makes a copy,
// overuse of Clone can make programs use more memory.
// Clone should typically be used only rarely, and only when
// profiling indicates that it is needed.
// For strings of length zero the string "" will be returned
// and no allocation is made.
func Clone(s string) string {
  if len(s) == 0 {
    return ""
  }
  
  b := make([]byte, len(s))
  copy(b, s)
  return *(*string)(unsafe.Pointer(&b))
}
```

在该方法的注释中可以看出：

1. `Clone()`函数会返回参数的一个全新副本；
2. `Clone()`函数适用于从一个很大的字符串中摘除小部分子字符串来使用的场景；
3. 对于长度为 0 的字符串，不会进行内存分配，而是直接返回空字符串`""`。

所以，使用`string.Clone()`的时候，一般是为了只使用一个很大的源字符串中的一小部分，而不需要继续保留源字符串。这样可以减少内存的占用。

### 2. 解析

Go 中 string 类型的底层结构定义如下：

```go
type string struct {
  ptr unsafe.Pointer
  len int
}
```

> `reflect.StringHeader`结构是对字符串底层结构的反射表示，与这个定义相同。

所以，当使用切片或者其他方式从源字符串中摘出一个字符串时，字符串和源字符串的`ptr`属性还是会指向同一个地址的：

```go
package main

import (
 "fmt"
 "reflect"
 "unsafe"
)

func main() {
 s := "abcdefghijklmn"
 s1 := s[:4]

 sHeader := (*reflect.StringHeader)(unsafe.Pointer(&s))
 s1Header := (*reflect.StringHeader)(unsafe.Pointer(&s1))
 fmt.Println(sHeader.Len == s1Header.Len)   // false
 fmt.Println(sHeader.Data == s1Header.Data) // true
}
```

而使用`strings.Clone()`方法得到的则是一个与源字符串指向的底层数据完全不同的地址。对于上面的示例，增加如下代码：

```go
s2 := strings.Clone(s[:4])

s2Header := (*reflect.StringHeader)(unsafe.Pointer(&s2))
fmt.Println(sHeader.Len == s2Header.Len)   // false
fmt.Println(sHeader.Data == s2Header.Data) // true
```

