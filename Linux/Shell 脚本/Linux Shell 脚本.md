## 基础
### 基本格式
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


### 保存与使用
编辑完毕，将脚本存盘为`filename.sh`，文件名后缀`sh`表明这是一个 Bash 脚本文件。执行脚本的时候，要先将脚本文件的属性改为可执行的：

```shell
chmod +x filename.sh
```

执行脚本的方法是：

```shell
./filename.sh
```

执行的时候不能只使用文件名，而需要添加路径名，否则不能执行。

### 命令
在 Shell 脚本中可以使用所有的 Linux 命令。比如：

* `exit`命令表示退出当前进程。
* `cat`命令表示输出信息到输出中。


## 变量
### 基础示例
Shell Script 是一种弱类型语言，使用变量的时候无需首先声明其类型。新的变量会在本地数据区分配内存进行存储，这个变量归当前的 Shell 所有，任何子进程都不能访问本地变量。

> 这些变量与环境变量不同，环境变量被存储在另一内存区，叫做用户环境区，这块内存中的变量可以被子进程访问。

变量赋值的方式是：

```shell
variable_name = variable_value
```

下面是一个简单的 Hello World 例子：

```shell
#!/bin/sh
# Hello World

a = "Hello World"
echo $a
```

每行代码都是作为一个完整的语句进行执行，其后不需要使用分号等标志表示行尾。

如果对一个已经有值的变量赋值，新值将取代旧值。取值的时候要在变量名前加`$`，`$variable_name`可以在引号中使用，这一点和其他高级语言是明显不同的。如果出现混淆的情况，可以使用花括号来区分。

例如，`echo "Hi, $as"`就不会输出`Hi, hello worlds`，而是输出`Hi，`。这是因为 Shell 把`$as`当成一个变量，而`$as`未被赋值，其值为空。正确的方法是：`echo "Hi, ${a}s"`。

**单引号中的变量不会进行变量替换操作。**

### 变量相关指令
关于变量，还需要知道几个与其相关的 Linux 命令：

* `env`用于显示用户环境区中的变量及其取值；
* `set`用于显示本地数据区和用户环境区中的变量及其取值；
* `unset`用于删除指定变量当前的取值，该值将被指定为`NULL`；
* `export`命令用于将本地数据区中的变量转移到用户环境区。


## 控制语句
Shell 脚本中的循环中，也可以使用类似 C 语言中的`break`和`continue`语句中断或跳过当前的循环操作。

### if 语句
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

`$#`表示包括`$0`在内的命令行参数的个数。在 Shell 中，脚本名称本身是`$0`，剩下的依次是`$0`、`$1`、`$2…`、`${10}`、`${11}`，等等。

`$*`表示整个参数列表，不包括`$0`，也就是说不包括文件名的参数列表。

### while 循环
格式如下：

```shell
while [ cond1 ] && { || } [ cond2 ] …; do
…
done
```

### for 循环
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

### until 循环
格式如下：

```shell
until [ cond1 ] && { || } [ cond2 ] …; do
…
done
```

### case 分支
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

### select 交互式
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

### 示例
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


## 函数
Shell Script 中也可以使用自定义的函数，其语法形式如下：

```shell
functionname()
{
    …
}
```


## 调试
们也可以在 Shell 下调试 Shell 脚本。当然最简单的方法就是用`echo`输出查看变量取值了。

Bash 也提供了真正的调试方法，就是执行脚本的时候用`-x`参数，这会执行脚本并显示脚本中所有变量的取值：

```shell
sh ?x filename.sh
```

也可以使用参数`-n`，它并不执行脚本，只是返回所有的语法错误。


## 参考
1. [Linux Shell编程入门](http://www.cnblogs.com/suyang/archive/2008/05/18/1201990.html)

