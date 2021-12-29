`bufio`包对输入输出的处理方便又高效。

## 一、类型

### 1.1 Scanner

`Scanner`是`bufio`包中最有用的特性之一，它可以读取输入，并将其拆分成行或单词，通常这是处理行形式的输入最简单的方法。

每次调用`Scanner.Scan()`方法就会读入下一行，并移除行末的换行符；读取到的内容可以通过`Scanner.Text()`得到。如果读取完全部的输入，则`Scanner.Scan()`方法就会返回 false，否则会返回 true。

比如，下面的代码可以从标准输入中读取每一行的内容：

```go
package main

import (
	"bufio"
	"fmt"
	"os"
)

func main() {
	counts := make(map[string]int)
	input  := bufio.NewScanner(os.Stdin)

	for input.Scan() {
		counts[input.Text()]++
	}

	fmt.Println()

	// Note: ignore potential errors from input.Err()
	for line, n := range counts {
		if n > 1 {
			fmt.Printf("%d\t%s\n", n, line)
		}
	}
}
```

