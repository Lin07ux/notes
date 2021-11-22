### 1. []byte 的打印

```go
package main

import "fmt"

func main() {
  x := []byte{}
  fmt.Printf("%#v %T\n", x, x)
}
```

输出结果为：

```
[]byte{} []uint8
```


