> 转摘：[如何在 Go 函数中获取调用者的函数名、文件名、行号...](https://mp.weixin.qq.com/s/sqGs7USTdbyq0GeTW7OgBQ)

### 1. 简介

在 Go 程序中，如果想要获取当前函数的调用者的相关信息，可以使用 runtime 包中的`Caller()`函数，它会报告当前 Go 程序调用栈所执行的函数的文件和行号信息。

该方法的签名如下：

```go
func Caller(skip int) (pc uintptr, file string, line int, ok bool)
```

参数：

* `skip int` 为要上溯的栈帧数，0 表示`runtime.Caller()`的直接调用者，1 表示`runtime.Caller()`的直接调用者的调用者，依次类推。

返回值：

* `pc uintptr` 调用栈标识符，使用该标识符可以获得调用方的函数名；
* `file string` 带路径的完整文件名；
* `line int` 该调用在文件中的行号。
* `ok bool` 是否成功获取到相关信息，如果无法获得信息，返回值 ok 会被设置为 false。

关于参数`skip`的使用示例如下：

```go
func CallerA() {
  // 获取的是 CallerA 这个函数的调用栈
  pc, file, lineNo, ok := runtime.Caller(0)
  
  // 获取的是 CallerA 函数的调用者的调用栈
  pc1, file1, lineNo1, ok1 := runtime.Caller(1)
}
```

### 2. 获取调用者的函数名

`runtime.Caller()`函数的第一个返回值是一个调用栈标识，通过它可以拿到调用栈的函数信息：

```go
func FuncForPC(pc uintptr) *Func

func (*Func) Name
```

`runtime.FuncForPC`函数返回一个表示调用栈标识符`pc`对应的调用栈的`*Func`信息。如果该调用栈标识符没有对应的调用栈信息，该函数会返回`nil`。

`runtime.Func`的`Name()`方法返回该调用栈所调用的函数的名字。虽然`runtime.FuncForPC()`函数可能会返回一个 nil 值的`*Func`，但也是能安全的调用`Name()`方法的，因为`Name()`方法在实现的时候做了安全判断，能够避免出现 panic 的可能。

`runtime.Func.Name()`的源码如下：

```go
func (f *Func) Name() string {
  if f == nil {
    return ""
  }
  fn := f.raw()
  if fn.isInlined() { // inlined version
    fi := (*funcinl)(unsafe.Pointer(fn))
    return fi.name
  }
  return funcname(f.funcInfo())
}
```

### 3. 使用示例

下面是一个使用`runtime.Caller()`和`runtime.FuncForPC()`函数一起配合获取调用者信息的简单例子：

```go
package main

import (
  "fmt"
  "path"
  "runtime"
)

func getCallerInfo(skip int) (info string) {
  pc, file, lineNo, ok := runtime.Caller(skip)
  if !ok {
    info = "runtime.Caller() failed"
    return
  }
  funcName := runtime.FuncForPC(pc).Name()
  fileName := path.Base(file) // Base() 函数返回路径的最后一个元素
  return fmt.Sprintf("Funcname:%s, file:%s, line:%d", funcName, fileName, lineNo)
}

func main() {
  // 打印 getCallerInfo 函数自身的信息
  fmt.Println(getCallerInfo(0))
  // 打印出 main 函数的信息
  fmt.Println(getCallerInfo(1))
}
```

