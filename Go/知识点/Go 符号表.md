> 转摘：[Go：符号表是什么？如何使用？](https://mp.weixin.qq.com/s/nH0v9wYe64--1HT_QJcKag)

符号表是由编译器生成和维护的，保存了与程序相关的信息，如函数和全局变量。

### 1. 符号表

Go 编译的所有二进制文件默认内嵌了符号表。比如，对于下面的代码：

```go
package main

import "fmt"

var AppVersion string

func main() {
  fmt.Println(`Version: `+AppVersion)
}
```

使用`go build`编译之后，使用`nm`命令来查看其符号表（由于表格较多，只展示部分）：

```shell
$ go build main.go

$ nm main | grep main
0000000001126140 s _main..inittask
000000000113d100 b _main.AppVersion
000000000108d140 t _main.main
0000000001033220 t _runtime.main.func1
00000000010335a0 t _runtime.main.func2
00000000010c59e8 s _runtime.mainPC
000000000116bf2b s _runtime.mainStarted
000000000113ce88 b _runtime.main_init_done
```

其中：

* `b`全称为`bss`，表示是非初始化的数据符号；
* `b`表示已初始化的数据符号；
* `t`表示文本符号，函数属于其中之一；
* `s`表示的是非初始化符号，面向小数据访问优化

> `nm`命令可以用来显示一个程序的符号表。Go 也封装了`nm`命令，可以用命令`go tool nm`来使用，生成的结果和`nm`命令相同。
> 
> nm - display name list (symbol table)

知道了暴露的符号名称之后，就可以通过一些方法与之交互。

### 2. 自定义变量

在执行命令`go build`的时候，经过了两个阶段：编译和构建。构建阶段通过编译过程中生成的对象文件生成了一个可执行文件，而且在构建的过程中，构建器会把符号表中的符号重定向到最终的二进制文件中。

在 Go 中可以使用`-X`选项来重写一个符号定义。`-X`选项两个入参：符号名称和符号值。

针对上面的代码，使用如下的方式进行构建：

```shell
go build -o ex -ldflags="-X main.AppVersion=v1.0.0"
```

构建并运行程序，会输出在命令行中设置的版本：

```text
Version: v1.0.0
```

此时再通过`nm`命令查看新构建出来的二进制文件`ex`的符号列表，可以看到`main.AppVersion`已经变成了已初始化的数据符号（标记类型从`b`变成了`d`）：

```shell
$ nm ex | grep main
0000000001126160 s _main..inittask
00000000011360d0 d _main.AppVersion
00000000010c58fc s _main.AppVersion.str
000000000108d140 t _main.main
0000000001033260 t _runtime.main
0000000001033220 t _runtime.main.func1
00000000010335a0 t _runtime.main.func2
00000000010c59f0 s _runtime.mainPC
000000000116bf2b s _runtime.mainStarted
000000000113cea8 b _runtime.main_init_done
```

### 3. 调试

符号表的存在是为了确保标识符在使用之前已被声明。这意味着，当程序被构建后，它就不再需要这个表了。但是，默认情况下符号表是被嵌入到了 Go 的二进制文件的，以便进行调试。

> 从 Go 1.11 版本之后，构建得到的二进制文件中的调试信息是被压缩的，老版本的 gdb 调试程序（如 Mac 版本）是无法识别这种压缩格式的。可以在构建的时候使用`-ldflags=-compressdwarf=false`参数来得到带有未压缩调试信息的构建结果文件。

先来理解如何利用它，再来看怎么把它从二进制文件中删除。

Go 程序的调试有很多工具，比如 gdb。对于前面编译出来的`ex`二进制文件，使用`gdb ex`命令就可以加载程序并进入到调试模式。可以使用`list`命令来展示源码：

```shell
$ go build -o ex -ldflags="-X main.AppVersion=v1.0.0 -compressdwarf=false" main.go

$ gdb ex
GNU gdb (GDB) 12.1
Copyright (C) 2022 Free Software Foundation, Inc.
License GPLv3+: GNU GPL version 3 or later <http://gnu.org/licenses/gpl.html>
This is free software: you are free to change and redistribute it.
There is NO WARRANTY, to the extent permitted by law.
Type "show copying" and "show warranty" for details.
This GDB was configured as "x86_64-apple-darwin21.5.0".
Type "show configuration" for configuration details.
For bug reporting instructions, please see:
<https://www.gnu.org/software/gdb/bugs/>.
Find the GDB manual and other documentation resources online at:
    <http://www.gnu.org/software/gdb/documentation/>.

For help, type "help".
Type "apropos word" to search for commands related to "word"...
Reading symbols from ex...
Loading Go Runtime support.
(gdb) list 10
5	var AppVersion string
6
7	func main() {
8	  fmt.Println(`Version:`+AppVersion)
9	}
(gdb)
```

gdb 初始的第一步就是读取符号表，提取程序中函数和符号的信息。通过上面的输出可以看到，gdb 能够通过符号表展示出 Go 源码的很多信息。

而如果在构建的时候，使用了`ldflags=-s -w`参数，那么构建程序就不会将符号表写入到最终的二进制文件中。下面是新的输出：

```shell
$ go build -o exs -ldflags="-s -w" main.go

$ gdb ex
GNU gdb (GDB) 12.1
[...]
Reading symbols from ex...
(No debugging symbols found in exs)
(gdb) list
No symbol table is loaded.  Use the "file" command.
```

> 但是在 Go 1.19 版本中，添加`-s`参数并没有效果，不确定是什么问题。而`-w`参数可以正常去掉 dwarf 信息。
> 
> ldflags 的参数即为链接器的参数，可以通过`go tool link`命令来查看，也可以直接查看 Go [cmd/link](https://go.dev/src/cmd/link/doc.go)的文档注释。

### 4. 二进制文件的大小

去掉符号表后会让调试器不能很好的工作，但是会减少二进制文件的大小。去掉符号表和 [dwarf 信息](https://golang.org/pkg/debug/dwarf/)可以让二进制文件减少 25% 的大小。

下面是有符号表和无符号表的二进制文件的区别：

```text
2,0M  7 f é v 15:59 ex
1,5M  7 f é v 15:22 ex-s
```

> 如果想了解未说明二进制文件会变小，可以阅读 WebKit 团队成员 Benjamin Poulain 的文章《[Unusual Speed Boost: Binary Size Matters](https://webkit.org/blog/2826/unusual-speed-boost-size-matters/)》（不寻常的加速：二进制文件大小）。


