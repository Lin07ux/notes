## 一、基础

### 1.1 基本格式

Shell 脚本的格式是固定的，如下：

```shell
#!/bin/sh
# comments

Your commands go here
```

首行中的符号`#!`告诉系统其后路径所指定的程序即是解释此脚本文件的 Shell 程序。如果首行没有这句话，在执行脚本文件的时候，将会出现错误。

> Linux 的 Shell 种类众多，常见的有：Bourne Shell（`/usr/bin/sh`或`/bin/sh`）、Bourne Again Shell（`/bin/bash`）、C Shell（`/usr/bin/csh`）、K Shell（`/usr/bin/ksh`）、Shell for Root（`/sbin/sh`），等等。不同的 Shell 语言的语法有所不同，所以不能交换使用。每种 Shell 都有其特色之处，基本上，掌握其中任何一种就足够了。

除第一行外，以`#`开头的行就是注释行，直到此行的结束。如果一行未完成，可以在行尾加上`"`，这个符号表明下一行与此行会合并为同一行。

后续的部分就是主程序，Shell 脚本像高级语言一样，也有变量赋值，也有控制语句。

需要注意的是：**每行代码都是作为一个完整的语句进行执行，其后不需要使用分号等标志表示行尾**。

### 1.2 保存与使用

编辑完毕，将脚本存盘为`filename.sh`，文件名后缀`sh`表明这是一个 Bash 脚本文件。执行脚本的时候，要先将脚本文件的属性改为可执行的：

```shell
chmod +x filename.sh
```

执行脚本的方法是：

```shell
./filename.sh
```

执行的时候不能只使用文件名，而需要添加路径名，否则不能执行。

### 1.3 命令

在 Shell 脚本中可以使用所有的 Linux 命令。比如：

* `exit`命令表示退出当前进程。
* `cat`命令表示输出信息到输出中。

### 1.4 配置

在开发和执行 Shell 脚本的时候，对一些错误、异常等会有不同的处理方式，可以通过在脚本中进行配置以便更好的进行处理。

* `set -o nounset` 使用到不存在的变量时，终止脚本的执行。默认情况下使用不存在的变量时，会忽略它并继续执行。
* `set -o errexit` 遇到执行出错时终止脚本的执行。默认情况下会跳过错误的代码并继续执行。

## 二、函数

Shell 中也能够自定义方法，以便在后续中进行调用。

### 2.1 函数

自定义方法也很简单，无需特殊的关键词，调用的时候也和 Shell 内置的方法一样：

```shell
# 定义函数 log
log() {
    local prefix = "[$(date +%Y/%m/%d\ %H:%M:%S)]: "
    echo "${prefix} $@"
}

# 调用函数 log
log "INFO" "a message"
```

### 2.2 参数

运行脚本或调用函数时，可以提供一系列的参数。Shell 的参数和其他的编程语言不同，没有名称，而是用`$n`表示，例如：`$1`表示第一个参数，`$2`表示第二个参数，依次类推。

对于 Shell 中的脚本参数和函数参数，有如下一些特殊表示：

* `$0` 对于脚本运行时，表示执行的脚本的名称。
* `$n` 传递给脚本或函数的参数。`n`是一个数字，表示第几个参数。
* `$#` 传递给脚本或函数的参数个数。
* `$*` 传递给脚本或函数的所有参数，不包括脚本文件名参数。
* `$@` 传递给脚本或函数的所有参数，不包括脚本文件名参数。被双引号包含时，与`$*`稍有不同。
* `$?` 上个命令的退出状态，或函数的返回值。
* `$$` 当前 Shell 进程 ID。对于 Shell 脚本，就是这些脚本所在的进程 ID。

`$*`和`$@`都表示传递给函数或脚本的所有参数，而且不包含脚本参数中的文件名(`$0`)，但是在被双引号包裹时，两者之间有些区别：`"$*"`会将所有的参数作为一个整体，以`"$1 $2 … $n"`的形式输出所有参数；`"$@"`会将各个参数分开，以`"$1" "$2" … "$n"`的形式输出所有参数。具体示例可以查看：[Linux Shell $* 和 $@ 的区别.md](./Linux%20Shell%20$*%20和%20$@%20的区别.md)。

`$?`可以获取上一个命令或函数的退出状态。所谓退出状态，就是上一个命令执行后的返回结果。退出状态是一个数字，一般情况下，大部分命令执行成功会返回 0，失败返回 1。不过，也有一些命令返回其他值，表示不同类型的错误。

## 三、变量

### 3.1 定义

Shell Script 是一种弱类型语言，使用变量的时候无需首先声明其类型。新的变量会在本地数据区分配内存进行存储，这个变量归当前的 Shell 所有，任何子进程都不能访问本地变量。

> 这些变量与环境变量不同，环境变量被存储在另一内存区，叫做用户环境区，这块内存中的变量可以被子进程访问。

Shell 支持以下三种定义变量的方式：

```shell
variable=value
variable='value'
variable="value"
```

`variable`是变量名，`value`是赋给变量的值：

* 如果`value`不包含任何空白符（例如空格、Tab 缩进等），那么可以不使用引号；
* 如果`value`包含了空白符，那么就必须使用引号包围起来；
* 使用双引号时可以组合使用其他变量来进行赋值。

Shell 变量的命名规范和大部分编程语言都一样：

* 变量名由数字、字母、下划线组成；
* 必须以字母或者下划线开头；
* 不能使用 Shell 里的关键字（通过 help 命令可以查看保留关键字）。

比如：

```shell
url=http://c.biancheng.net
name='C语言中文网'
author="严长生 $name"
```

已定义的变量，可以被重新赋值，与定义时一样即可。

### 3.2 使用

使用一个定义过的变量，只要在变量名前面加美元符号`$`即可。如果出现混淆的情况，可以使用花括号来区分。如果如：

下面是一个简单的例子：

```shell
#!/bin/sh
# Hello World

str="Hello World"
echo $str   # Hello World
echo ${str} # Hello World

echo "Hi, $strs"   # Hi，
echo "Hi, ${str}s" # Hi, Hello Worlds
```

需要注意的是：**单引号中的变量不会进行变量替换操作。**

关于变量，还需要知道几个与其相关的 Linux 命令：

* `env`用于显示用户环境区中的变量及其取值；
* `set`用于显示本地数据区和用户环境区中的变量及其取值；
* `unset`用于删除指定变量当前的取值，该值将被指定为`NULL`；
* `export`命令用于将本地数据区中的变量转移到用户环境区。

### 3.3 删除变量

使用`unset`命令可以删除变量。语法：

```shell
unset variable_name
```

变量被删除后不能再次使用，而且**不能删除只读变量**：

```shell
#!/bin/sh

myUrl="http://see.xidian.edu.cn/cpp/u/xitong/"
unset myUrl
echo $myUrl
```

这段代码不会有任何输出。

### 3.4 将命令的结果赋值给变量

Shell 也支持将命令的执行结果赋值给变量，常见的有以下两种方式：

```shell
variable=`command`
variable=$(command)
```

第一种方式把命令用反引号包围起来，反引号和单引号非常相似，容易产生混淆，所以不推荐使用这种方式；第二种方式把命令用`$()`包围起来，区分更加明显，所以推荐使用这种方式。

### 3.5 变量修饰符

变量可以使用不同的修饰符，以使其有不同的效果：

* `local` 局部变量，一般用在函数中，避免相同变量名的混淆。
* `readonly` 只读变量(类似其他语言中的常量)。

## 四、控制语句

Shell 脚本中的循环中，也可以使用类似 C 语言中的`break`和`continue`语句中断或跳过当前的循环操作。

### 4.1 if 语句

`if`语句和其他编程语言相似，都是流程控制语句。它的语法是：

```shell
if ...; then
...
elif ...; then
...
else
...
fi
```

与其他语言不同，Shell Script 中`if`语句的条件部分要以分号来分隔。条件中的`[]`表示条件测试，常用的条件测试有下面几种：

* `[ -f "$file" ]` 判断`$file`是否是一个文件；
* `[ $a -lt 3 ]` 判断`$a`的值是否小于 3，同样`-gt`和`-le`分别表示大于或小于等于；
* `[ -x "$file" ]` 判断`$file`是否存在且有可执行权限，同样`-r`测试文件可读性；
* `[ -n "$a" ]` 判断变量`$a`是否有值，测试空串用`-z`；
* `[ "$a" = "$b" ]` 判断`$a`和`$b`的取值是否相等；
* `[ cond1 -a cond2 ]` 判断`cond1`和`cond2`是否同时成立，`-o`表示`cond1`和`cond2`有一成立；

**要注意条件测试部分中的空格。在方括号的两侧都有空格，在`-f`、`-lt`、`=`等符号两侧同样也有空格。如果没有这些空格，Shell 解释脚本的时候就会出错。**

### 4.2 while 循环

格式如下：

```shell
while [ cond1 ] && { || } [ cond2 ] …; do
…
done
```

### 4.2 for 循环

格式如下：

```shell
for var in …; do
…
done
```

或：

```shell
for (( cond1; cond2; cond3 )) do
…
done
```

### 4.4 until 循环

格式如下：

```shell
until [ cond1 ] && { || } [ cond2 ] …; do
…
done
```

### 4.5 case 分支

Shell Script 中也有类似 C 语言中多分支结构的`case`语句，它的语法有所不同，语法如下：

```shell
case var in
  pattern 1 )
    … ;;
  pattern 2 )
    … ;;
  * )
    … ;;
esac
```

其中，每个分支的条件是`pattern val )`的方式书写的，后面需要跟随者一个右括号；每个分支块结束的部分需要使用两个分号标记；对于默认的分支，使用`* )`来表示条件；最终的结束部分，需要使用关键词`esac`。

下面是一个简单的示例：

```shell
while getopts vc: OPTION do
    case $OPTION in
        c ) COPIES=$OPTARG
            ehco "$COPIES";;
        v ) echo "suyang";;
        \? ) exit 1;;
    esac
done
```

上面这个循环的作用就是依次取出脚本名称后面的选项，进行处理，如果输入了非法选项，则进入"?指定的部分，退出脚本程序。

> `getopts`的语法较为复杂，可以查看相应的文档进行了解。

### 4.6 select 交互式

Bash 提供了一种用于交互式应用的扩展`select`，用户可以从一组不同的值中进行选择。其语法如下：

```shell
select var in …; do
break;
done
```

例如，有如下的示例：

```shell
#!/bin/bash
echo "Your choice?"
select var in "a" "b" "c"; do
break
done
echo $var
```

其会有如下的输出：

```
Your choice?
1) a
2) b
3) c
```

### 4.7 示例

下面是一个复杂的示例，综合的使用了 Shell 脚本中的多种语法：

```shell
#!/bin/bash
# we have less than 3 arguments. Print the help text:
if [ $# -lt 3 ]; then
cat<<HELP
    ren -- renames a number of files using sed regular expressions

    USAGE: ren 'regexp' 'replacement' files
    EXAMPLE: rename all *.HTM files in *.html:
    ren 'HTM$' 'html' *.HTM

    HELP
    exit 0
fi

OLD="$1"
NEW="$2"

# The shift command removes one argument from the list of
# command line arguments.
shift
shift

# $* contains now all the files:
for file in $*; do
    if [ -f "$file" ]; then
        newfile=`echo "$file" | sed  "s/${OLD}/${NEW}/g"`
        if [ -f "$newfile" ]; then
             echo "ERROR: $newfile exists already"
        else
             echo "renaming $file to $newfile "
             mv "$file" "$newfile"
        fi
    fi
done
```

这个示例中，前面一部分是一个条件判断，当传入的参数(包含脚本名)不足三个的时候，就提示错误信息并退出脚本。

错误信息是使用`cat`命令进行输出的。这里使用了 Shell 中的 Here 文档。Here 文档用于将多行文本传递给某一命令。Here 文档的格式是以`<<`开始，后跟一个字符串，在 Here 文档结束的时候，这个字符串同样也要出现，表示文档结束。在本例中，Here 文档被输出给cat命令，也即将文档内容打印在屏幕上，起到显示帮助信息的作用。

## 五、其他

### 5.1 调试

可以在 Shell 下调试 Shell 脚本。当然最简单的方法就是用`echo`输出查看变量取值了。Bash 也提供了真正的调试方法，通过不同的参数就可以实现相应的功能：

* `-x` 执行脚本并显示脚本中所有变量的取值。
* `-n` 并不执行脚本，只是返回所有的语法错误。
* `-v` 跟踪脚本里的每个命令的执行。

### 参考

1. [Linux Shell编程入门](http://www.cnblogs.com/suyang/archive/2008/05/18/1201990.html)
2. [Linux Shell脚本教程：30分钟玩转Shell脚本编程](http://c.biancheng.net/cpp/shell/)

