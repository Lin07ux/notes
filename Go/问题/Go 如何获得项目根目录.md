> 转摘：[Go：如何获得项目根目录？](https://mp.weixin.qq.com/s/OUx-xDmgMglX9s4B7mlBiQ)

### 1. 问题

项目中，特别是 Web 项目，常需要获得项目的根目录，进而可以访问到项目相关的其他资源，比如配置文件、静态资源文件、模板文件、数据文件、日志文件等。

> Go 1.16 之后，有些可以方便的通过 embed 内嵌进来。

比如下面的目录结构：

```
/Users/stdcwd
        ├── bin
            ├── cwd
        ├── main.go
        └── log
            ├── error.log
```

为了正确读取`error.log`文件，就需要获得项目根目录。

### 2. 解决方案

解决方案有很多种，各有优缺点和使用注意事项。

#### 2.1 os.Getwd()

Go `os`标准库中有一个函数**`Getwd()`**可以用来**返回当前工作目录**：

```go
func Getwd() (dir string, err error)
```

比如，对于上面的路径结构，使用下面的方式运行命令：

```shell
$ cd /Users/stdcwd
$ bin/cwd
```

得到的结果就是：`/Users/stdcwd`。

需要注意的是，这个函数返回的是当前工作目录，也就是运行命令时所在目录，而非命令所在的路径。因此，如果切换到别的目录下，再使用绝对/相对路径运行这个命令，那么得到的结果就会发生变化了。

#### 2.2 exec.LookPath()

`exec.LookPath()`函数可以获取到所运行的程序所在的目录与运行程序的工作目录的相对路径。返回的结果中包含可执行程序自身的名称。

这样，先将程序的相对路径转换成绝对路径，再根据程序所在目录和项目根目录的相对位置，就可以获取到项目的根目录了。

比如，对于上面的目录结果，使用如下面的语句可以项目的根路径了：

```go
binary, err := exec.LookPath(os.Args[0])
root := filepath.Dir(filepath.Dir(filepath.Abs(binary)))
```

这里`os.Args[0]`表示的是当前程序名。

如果是在项目的根目录执行程序`bin/cwd`，那么上面代码中的`binary`的结果就是`bin/cwd`了，也就是程序`cwd`的相对路径。

然后使用`filepath.Abs()`函数就可以得到程序`cwd`所在的绝对路径。

因为`cwd`程序处于项目根路径下的二级，所以在使用两次`filepath.Dir()`函数就可以得到项目的根路径了。

#### 2.4 os.Executable()

Go 1.8 中增加了一个函数，可以直接返回运行程序所在的绝对路径（返回的结果中包含可执行程序自身的名称）。这样就可以更方便的获取到项目根路径了：

```go
// Executable returns the path name for the executable that started the
// current process. There is no guarantee that the path is still pointing
// to the correct executable.
// If a symlink was used to start the process, depending on the operating
// system, the result might be the symlink or the path it pointed to. If a
// stable result is needed, path/filepath.EvalSymlinks might help.
// Executable returns an absolute path unless an error occurred. The main use
// case is finding resources located relative to an executable.
func Executable() (string, error)
```

这个方法返回的是运行程序的绝对路径，也就是相当于自动对`exec.LookPath()`方法执行了`filepath.Abs()`后的结果。

对于上面的路径结构，获取项目根目录的方式更简单了：

```go
binary, _ := os.Executable()
root := filepath.Dir(filepath.Dir(binary))
```

同时，这个方法中的注释中提到了符号链接的问题：使用符号链接运行程序时，因操作系统行为的不同，获得到的结果可能是符号链接自身的绝对路径，也可能是符号链接指向的可执行程序本身的绝对路径。

> `exec.LookPath()`函数也存在软链接的问题。

为了获得确定的结果，需要借助`path/filepath.EvalSymlinks`进行处理：

```go
package main

import (
  "os"
  "path/filepath"
)

func main() {
  ex, err := os.Executable()
  if err != nil {
    panic(err)
  }
  realPath, err := filepath.EvalSymlinks(ex)
  if err != nil {
    panic(err)
  }
  println(filepath.Dir(realpath))
}
```

这样，最后输出的就是项目根目录了。

注意：如果使用的是`go run`方式运行程序，结果会是临时文件的路径。因此，需要先编译，再运行程序。


