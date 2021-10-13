`os`包以跨平台的方式提供了一些雨操作系统交互的函数和变量。

## 一、属性

### 1.1 Args

程序执行时的命令行参数可以从`os.Args`变量中获取。

**`os.Args`变量是一个字符串切片类型，其第一个元素即`os.Args[0]`是命令本身的名称，后续的其他元素则是程序启动时传递给它的参数。**

比如，下面是 Unix 里`echo`命令的一份 Go 实现，可以将命令行参数打印成一行：

```go
package main

import (
	"fmt"
	"os"
	"strings"
)

func main() {
	fmt.Println(strings.Join(os.Args[1:], " "))
}
```

