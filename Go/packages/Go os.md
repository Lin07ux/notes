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

## 二、方法

### 2.1 环境变量

Go 中和环境变量相关的 API 主要在 os 包中，如下：

```go
// os/env.go
// Environ 以 key=value 形式返回所有的环境变量
func Environ() []string

// Getenv 获取 key 对应的环境变量的值。
// 如果该环境变量不存在，则会返回空字符串。
func Getenv(key string) string

// LookupEnv 获取 key 对应的环境变量的值。
// 如果环境变量存在，则返回对应的值（可能为空字符串），并且返回布尔值 true；
// 如果环境变量不存在，则返回空字符串，并且返回布尔值 false。
func LookupEnv(key string) (string, bool)

// Setenv 设置 key 这个键对应的环境变量的值。
// 如果出错则返回错误
func Setenv(key string) error

// Unsetenv 取消设置指定的环境变量。
// 如果出错则返回错误。
func Unsetenv(key string) error

// Clearenv 删除所有的环境变量
// 清除之后，当前进程的环境变量都清空了，包括 Go 自动设置的环境变量也不存在了
func Clearenv()

// ExpandEnv 根据当前环境变量的值替换字符串中形如 ${var} 或 $var 的占位符
// 对未定义的环境变量的引用将会被空字符串替换
func ExpandEnv(s string) string

// Expand 使用 mapping 函数替换 s 字符串中的形如 ${var} 或 $var 的占位符
// 比如：os.ExpandEnv(s) 就等同于 os.Expand(s, os.Getenv)
func Expand(s string, mapping func(string) string) string
```

需要注意的是，环境变量的 key 是区分大小写的。所以在获取或者设置的时候都需要注意参数的大小写问题。

另外，`Getenv()`方法和`LookupEnv()`方法都能获取环境变量的值，且不存在都返回空字符串。但是后者的第二个返回值可以判断是否存在该环境变量，这在有些时候是很有用的。

示例：

```go
func main() {
  println("NAME is: ", os.Getenv("NAME"))
  println("name is: ", os.Getenv("name"))
  println(os.ExpandEnv("My name is ${NAME}"))
}
```

使用如下方式运行：

```shell
$ NAME=Lin07ux go run main.go
NAME is:  Lin07ux
name is:
My name is Lin07ux
```


