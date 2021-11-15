### 1. Mac 安装

直接使用 Homebrew 进行安装：

```shell
brew install gdb
```

然后还需要安装对应的证书，可以参见[GDB Installation on Mac OS X](https://www.ics.uci.edu/~pattis/common/handouts/macmingweclipse/allexperimental/mac-gdb-install.html)

### 2. 使用

安装好之后，即可从命令行中对指定的可执行程序进行调试：

```shell
gdb -tui demo
```

这里的`demo`就是一个可执行文件。

执行命令之后，就会打开一个命令行调试界面。

### 3. 指令

GDB 提供了很多调试指令，下面是一些常用的指令：

* `r`：run，执行成
* `n`：next，下一步（不进入函数内部）
* `s`：step，下一步（进入函数内部）
* `b`：breakpoint，设置断点
* `l`：list，查看源码
* `c`：contine，继续执行到下一断点
* `bt`：backtrace，查看当前调用栈
* `p`：print，打印查看变量
* `q`：quit，退出 GDB
* `whatis`：查看对象类型
* `info breakpoints`：查看所有的断点
* `info locals`：查看局部变量
* `info args`：查看函数的参数值及要返回的变量值
* `info goroutines`：查看 goroutine 信息（在使用前需要先执行`/usr/local/go/src/runtime/runtime-gdb.py`）
* `goroutine <b> bt`：查看指定序号的 goroutine 的调用栈
* <kbd>Enter</kbd>：重复执行上一次操作

### 4. 示例

上面的指令中，有一些使用起来比较灵活。

#### 4.1 l

```shell
# 查看指定行数上下5行(L8:±5)
(gdb) l 8

# 查看指定范围的行数(L5-L8)
(gdb) l 5:8

# 查看指定文件的行数上下5行(demo.go:L8±5)
l demo.go:8

# 可以查看函数，记得加包名
l main.main
```

#### 4.2 b

`b`指令和`l`指令的使用方法类似：

```shell
# 在指定行打断点
(gdb) b 8

# 在指定指定文件的行打断点
b demo.go:8

# 在指定函数打断点，记得加包名
b main.main
```

#### 4.3 p

```shell
# 查看变量
(gdb) p var

# 查看对象长度或容量
(gdb) p $len(var)
(gdb) p $cap(var)

# 查看对象的动态类型
(gdb) p $dtype(var)
(gdb) iface var

# 举例如下
(gdb) p i
$4 = {str = "cbb"}
(gdb) whatis i
type = regexp.input
(gdb) p $dtype(i)
$26 = (struct regexp.inputBytes *) 0xf8400b4930
(gdb) iface i
regexp.input: struct regexp.inputBytes *
```


