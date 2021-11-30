> 转摘：[Go1.18 快讯：新增的 Cut 函数太方便了](https://mp.weixin.qq.com/s/pZld4h2-9vLo6au355uZUw)

之前需要使用分隔符将字符串进行分隔的时候，需要先使用`strings.Index()`方法获取分隔符的位置，然后再通过 slice 切片的方式进行分隔。如下：

```go
addr := "192.168.1.1:8080"
pos := strings.Index(addr, ":")
if pos == -1 {
  panic("非法地址")
}
ip, port := addr[:pos], addr[pos+1:]
```

> strings 包中还有很多其他的`Index`相关函数可以使用。

Go 官方统计了 Go 源码相关函数使用场景，认为可以新增一个函数`strings.Cut()`来完成字符串的分割：

```go
func Cut(s, sep string) (before, after string, found bool) {
  if i := strings.Index(s, sep); i >= 0 {
    return s[:i], s[i+len(sep):], true
  }
  return s, "", false
}
```

这个方法会使用分隔符`sep`将字符串`s`一分为二，分割的位置在`s`中`sep`首次出现的地方。如果`sep`在分隔符中没有出现，那么`before`就等于字符串`s`，而`after`为空字符串，`found`为 false。

示例如下：

```go
package main

import (
 "fmt"
 "strings"
)

func main() {
  show := func(s, sep string) {
    before, after, found := strings.Cut(s, sep)
    fmt.Printf("Cut(%q, %q) = %q, %q, %v\n", s, sep, before, after, found)
  }
  show("Gopher", "Go") // "", "pher", true
  show("Gopher", "ph") // "Go", "er", true
  show("Gopher", "er") // "Goph", "", true
  show("Gopher", "Badger") // "Gopher", "", false
}
```

Go 官方因为这种情况使用的比较多所以增加了`strings.Cut()`方法。同样的，也因为根据分隔符在字符串的最后一个位置进行分割用的比较少，所以就没有增加`strings.LastCut()`方法。




