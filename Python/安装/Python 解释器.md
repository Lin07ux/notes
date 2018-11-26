Python 解释器具有简单的行编辑功能。在 Unix 系统上，任何 Python 解释器都可能已经添加了 GNU readline 库支持，这样就具备了精巧的交互编辑和历史记录等功能。

> 在 Python 主窗口中输入`Control-P`可能是检查是否支持命令行编辑的最简单的方法。如果发出嘟嘟声（计算机扬声器），则说明你可以使用命令行编辑功能。如果没有任何声音，或者显示`^P`字符，则说明命令行编辑功能不可用；你只能通过退格键从当前行删除已键入的字符并重新输入。

## 安装

Python 解释器一般在安装 Python 的时候就会自动安装的。由于 Python 是跨平台的，所以不论是在 Unix/Linux 平台，还是在 Windows 平台，都可以进行安装。

> [官网下载](https://www.python.org/downloads/)

### Mac

在 Mac 中，默认已经安装了 Python2，所以是可以直接在命令行中通过输入`Python`命令来打开 Python 解析器的。

如果需要使用 Python3，那么可以使用 Homebrew 来安装(可以同时安装有 Python2 和 Python3，两者的安装目录名称不一样)。

```shell
# 搜索 python
brew search python

# 在搜索的结果中会显示有 python 和 python3
# 其中 python 边上会有对勾，表示已经安装
# 然后就可以使用下面的命令安装 python3 了
brew install python3

# 更新
brew upgrade python3

# 安装完成后，需要使用 python3 来代替 python
python3 --version  # 查看版本
python3 # 进入 python3 解释器
```

### Windows

在 Windows 中安装则需要在官网上下载对应的 msi 安装包，然后进行安装即可。

安装完成之后，如果在命令行中提示不存在 Python，则需要检查下系统的路径中是否包含 Python 的安装目录。

下载地址为：[Python v2.7.8](https://www.python.org/download/releases/2.7.8/)


## 启用解释器

启动 Python 解释器有三种方法，需要在命令行中输入不同格式的命令：

* 直接输入`python`后回车进入交互模式；

* 输入`python -c command [arg] ...`命令，这种方法可以在命令行执行 Python 语句，类似于 shell 中的`-c`选项。由于 Python 语句通常会包含空格或其他特殊 shell 字符，一般建议将要执行的 Python 命令用单引号包裹起来。

* 输入`python -m module [arg] ...`语句，这可以调用一些 Python 模块，这类似在命令行中键入完整的路径名执行模块源文件一样。

### 参数传递

调用解释器时，脚本名和附加参数传入一个名为`sys.argv`的字符串列表。可以通过`import sys`引入 sys 模块，就能够获取这个列表。列表的长度大于等于 1。

* 没有给定脚本和参数时，它至少也有一个元素：`sys.argv[0]`此时为空字符串；

* 脚本名指定为`'-'`（表示标准输入）时，`sys.argv[0]`被设定为`'-'`；

* 使用`-c 指令`时，`sys.argv[0]`被设定为`'-c'`；

* 使用`-m 模块`参数时，`sys.argv[0]`被设定为指定模块的全名。

> `-c 指令`或者`-m 模块`之后的参数不会被 Python 解释器的选项处理机制所截获，而是留在`sys.argv`中，供脚本命令操作。

### 交互模式

从 tty 读取命令时，我们称解释器工作于交互模式。这种模式下它根据*主提示符*来执行，主提示符通常标识为三个大于号(`>>>`)；继续的部分被称为*从属提示符*，由三个点标识(`...`)。在第一行之前，解释器打印欢迎信息、版本号和授权提示：

```Shell
$ python3.5
Python 3.5.2 (default, Mar 16 2014, 09:25:04)
[GCC 4.8.2] on linux
Type "help", "copyright", "credits" or "license" for more information.
>>>
```

输入多行结构时需要从属提示符了，例如，下面这个`if`语句：

```Shell
>>> the_world_is_flat = 1
>>> if the_world_is_flat:
...     print("Be careful not to fall off!")
...
Be careful not to fall off!
```


