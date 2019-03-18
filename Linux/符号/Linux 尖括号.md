> 转摘：
> * [理解 Bash 中的尖括号](https://linux.cn/article-10502-1.html)
> * [Bash 中尖括号的更多用法](https://linux.cn/article-10529-1.html)

## 一、 转移数据

### 1.1 覆盖写入

在 shell 脚本语言中，尖括号可以将数据从一个地方转移到另一个地方。例如可以这样把数据存放到一个文件当中：

```shell
ls > dir_content.txt
```

在上面的例子中，`>`符号让 shell 将`ls`命令的输出结果写入到`dir_content.txt`里，而不是直接显示在命令行中。

> 需要注意的是，如果`dir_content.txt`这个文件不存在，Bash 会为你创建；但是如果`dir_content.txt`是一个已有的非空文件，它的内容就会被覆盖掉。所以执行类似的操作之前务必谨慎。


### 1.2 追加写入

也可以使用`>>`把新的数据追加到文件的末端，而不会覆盖掉文件中已有的数据。例如：

```shell
ls $HOME > dir_content.txt;
wc -l dir_content.txt >> dir_content.txt
```

在这串命令里，首先将家目录的内容写入到`dir_content.txt`文件中，然后使用`wc -l`计算出`dir_content.txt`文件的行数（也就是家目录中的文件数）并追加到`dir_content.txt`的末尾。输出类似如下：

```
Applications
bin
cloud
Desktop
Documents
Downloads
Games
ISOs
lib
logs
Music
OpenSCAD
Pictures
Public
Templates
test_dir
Videos
17 dir_content.txt
```

### 1.3 引入数据

当使用反向尖括号，也就是`<`时，表示将尖括号后面的数据作为输入，提供给前面的命令。

加入存在一个`CBActors`文件，内容如下：

```shell
John Goodman 5
John Turturro 3
George Clooney 2
Frances McDormand 6
Steve Buscemi 5
Jon Polito 4
Tony Shalhoub 3
James Gandolfini 1
```

可以使用如下的命令来对这个文件中的内容按照字母顺序输出：

```shell
sort < CBActors
```

> 不过，`sort`命令本来就可以接受传入一个文件，因此在这里使用`<`会略显多余，直接执行`sort CBActors`就可以得到期望的结果。

### 1.4 假装文件

通过使用`<`，可以实现“欺骗”的效果，让其它命令认为某个命令的输出是一个文件。

例如，在进行备份文件的时候不确定备份是否完整，就需要去确认某个目录是否已经包含从原目录中复制过去的所有文件。可以试一下这样操作：

```shell
dirr <(ks /original/dir/) <(ls /backup/dir)
```

`diff`命令是一个逐行比较两个文件之间差异的工具。在上面的例子中，就使用了`<`让`diff`认为两个`ls`命令输出的结果都是文件，从而能够比较它们之间的差异。

> 要注意，在`<`和`(...)`之间是没有空格的。

假如输出结果类似如下：

```shell
5d4 < Dv7bIIeUUAAD1Fc.jpg:large.jpg
```

输出结果中的`<`表示`Dv7bIIeUUAAD1Fc.jpg:large.jpg`这个文件存在于左边的目录，但不存在于右边的目录中。也就是说，在备份过程中可能发生了问题，导致这个文件没有被成功备份。如果`diff`没有显示出任何输出结果，就表明两个目录中的文件是一致的。

### 1.5 Here 字符串

使用`echo`和管道（`|`）可以传递变量，比如，要把一个字符串变量转换为全大写形式，可以这样做：

```shell
myvar="Hello World"
echo $myvar | tr '[:lower:]' '[:upper:]'
# 输出：HELLO WORLD
```

`tr`命令可以将一个字符串转换为某种格式。在上面的例子中，就使用了`tr`将字符串中的所有小写字母都转换为大写字母。

这个传递过程的重点不是变量，而是变量的值，也就是字符串`Hello World`。这样的字符串叫做`HERE`字符串，含义是“这就是我们要处理的字符串”。

使用三个尖括号`<<<`也可以使用 Here 字符串，对于上面的例子：

```shell
myvar="Hello World"
tr '[:lower:]' '[:upper:]' <<< $myvar
```

## 二、示例

### 2.1 将文件内容处理排序后输出

对于上面的`CBActors`文件，如果要按照数值来排序，那么就需要先对其进行处理，然后对处理后的内容进行排序。

首先，重新处理文件内容：

```shell
while read name surname films;\
  do
    echo $films $name $surname >> filmsfirst;\
  done < CBActors
```

下面来分析一下这些命令做了什么：

* `while…; do…done`是一个循环结构。当`while`后面的条件成立时，`do`和`done`之间的部分会一直重复执行；
* `read`语句会按行读入内容。`read`会从标准输入中持续读入，直到没有内容可读入；
* `CBActors`文件的内容会通过`<`从标准输入中读入，因此`while`循环会将`CBActors`文件逐行完整读入；
* `read`命令可以按照空格将每一行内容划分为三个字段，然后分别将这三个字段赋值给`name`、`surname`和`films`三个变量，这样就可以很方便地通过`echo $films $name $surname >> filmsfirst;\`来重新排列几个字段的放置顺序并存放到`filmfirst`文件里面了。

执行完以后，查看`filmsfirst`文件，内容会是这样的：

```
5 John Goodman
3 John Turturro
2 George Clooney
6 Frances McDormand
5 Steve Buscemi
4 Jon Polito
3 Tony Shalhoub
1 James Gandolfini
```

这时候再使用`sort`命令：

```shell
sort -r filmsfirst
```

> `-r`参数表示降序排列。

### 2.2 使用假装文件功能简化排序

对于上面的例子，可以通过`<`假装文件功能来简化排序功能：

```shell
sort -r <(while read -r name surname films;do echo $films $name $surname ; done < CBactors)
```

这样，就不需要中间文件来进行中转，直接就可以排序了。

