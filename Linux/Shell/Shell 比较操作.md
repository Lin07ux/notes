> 转摘：[你或许不知道，shell不能比较大小](https://liutos.github.io/2020/04/26/%E4%BD%A0%E6%88%96%E8%AE%B8%E4%B8%8D%E7%9F%A5%E9%81%93%EF%BC%8Cshell%E4%B8%8D%E8%83%BD%E6%AF%94%E8%BE%83%E5%A4%A7%E5%B0%8F/)

## 一、基础

### 1.1 简介

在 Shell 中进行比较操作时，一般会使用`[ expression ]`这样的代码，用来进行条件判断、分支运行等。

比如，如果想知道当前的 UNIX 时间是否已经以 16 开头，可以用下列的 Shell 代码：

```shell
#!/bin/bash
ts=$(date '+%s')
if [ "${ts}" -gt 1600000000 -a "${ts}" -lt 1700000000 ]; then
    echo '当前的UNIX时间戳已经以16开头啦。'
else
    echo '当前的UNIX时间戳还没以16开头哦。'
fi
```

如果`date '+%s'`的值为 1587901648，那么运行后走的是`else`的分支。

### 1.2 比较操作

Shell 中的比较操作支持很多运算符，包括但不限于如下几种：

  运算符    |  作用     |  示例代码
-----------|----------|----------------
`-ge`      | 大于或等于 | `[ 2 -ge 1 ]`
`-eq`      | 等于      | `[ 1 -eq 1 ]`
`-le`      | 小于或等于 | `[ 2 -le 3 ]`
`-lt`      | 小于      | `[ 3 -lt 4 ]`

还有一些“测试”类型的运算符，例如：

  运算符   |  作用                          |  示例代码
----------|-------------------------------|---------------------
`-b file` | 测试 file 是否存在并且是个块设备   | `[ -b /dev/disk0 ]`
`-c file` | 测试 file 是否存在并且是个字符设备 | `[ -c /dev/tty ]`

## 二、提升

### 2.1 `[`是 Shell 的语法么？

大部分写 Shell 代码的人或许会认为，`[]`是 Shell 语言用于实现一系列的比较操作的特殊语法。但实际上，`[]`并不是一个语法：

**`[`是一个独立的命令行程序，`]`则什么都不是，仅仅是一个普通的字符。**

在 bash 中使用`which`命令可以看到`[`的真面目：

```shell
> which [
[: shell built-in command
```

`[`是一个独立的程序，而且有它自己的 man 文档：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1589696521042.png" width="1036"/>

在 man 文档中出现了另外一个命令`test`，它和`[`的功能是一模一样的：`test`和`[`是同一个东西：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1589696630728.png" width="338"/>

### 2.2 `[`源代码

可以在 GitHub 上找到`[`和`test`的[源代码](https://github.com/freebsd/freebsd/blob/master/bin/test/test.c)，代码很短。

如果在 Shell 代码中使用`[`做比较运算，必须写上对应的右方括号`]`。但既然`[`是一个普通的外部程序，那么这个匹配括号的检查显然不会是 Shell 来做的——没错，`[`自己会检查是否有写上相应的右方括号，这一段逻辑在源文件的`main`函数开始不久就出现了。

![](http://cnd.qiniu.lin07ux.cn/markdown/1589702583396.png)

这个检查只有在程序被以`[`的名字启动的时候才会生效，所以`test 1 -eq 1`是不需要写括号的。

其实除了上文中给出的那些比较和测试运算符之外，`[`也支持复杂的逻辑运算表达式，比如文章开头的示例代码中的`-a`就是逻辑与的意思。在代码的注释中还贴心地给出了所接受的参数的 BNF：

![](http://cnd.qiniu.lin07ux.cn/markdown/1589703469516.png)

而解析参数的过程则是一个手写的递归下降语法分析器，在源代码中可以找到与上面的产生式对应的多个函数：`oexpr`、`aexpr`、`nexpr`、`primary`，以及`binop`。

由于在 Shell 语言中，0 表示逻辑真，而 1 表示逻辑假（与 C 语言相反），所以在`main`函数中，如果发现传入的第一个参数为感叹号（`!`，表示逻辑取反），则将`oexpr`的调用结果直接返回，否则需要将结果取反后再从`main`函数中返回——给操作系统。

![](http://cnd.qiniu.lin07ux.cn/markdown/1589703578376.png)

### 2.3 Shell 原生比较

尽管在 bash 中，`[`的确是作为一个外部程序存在的，但在 zsh 中却相反：

![](http://cnd.qiniu.lin07ux.cn/markdown/1589704480228.png)

而且，即使是 bash 也并非完全没有原生的比较操作——此处需要召唤`[[`。`[[`是 Shell 的保留字，它是一个 less suprise 版本的`[`，在 Stack Overflow 上有不少关于它的问答值得一看：

1. [https://stackoverflow.com/questions/3427872/whats-the-difference-between-and-in-bash](https://stackoverflow.com/questions/3427872/whats-the-difference-between-and-in-bash)
2. [https://stackoverflow.com/questions/669452/is-double-square-brackets-preferable-over-single-square-brackets-in-ba](https://stackoverflow.com/questions/669452/is-double-square-brackets-preferable-over-single-square-brackets-in-ba)

