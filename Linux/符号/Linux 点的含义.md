> 转摘：[Linux 工具：点的含义](https://linux.cn/article-10465-1.html)

## 一、路径

### 1.1 当前目录

`.`放在一个需要一个目录名称的命令的参数处时，表示“当前目录”：

```shell
find . -name "*.jpg"
```

意思就是“在当前目录（包括子目录）中寻找以`.jpg`结尾的文件”。

`ls .`和`cd .`分别列举和“进入”到当前目录，虽然在这两种情况下这个点都是多余的。

### 1.2 上级目录

`..`两个点在当命令期望一个文件目录的时候，表示“当前目录的父目录”。如果当前在`/home/your_directory`下并且运行：

```shell
cd ..
```

就会进入到`/home`。

### 1.3 隐藏文件和目录

如果在一个文件或目录的开头加上点，它表示这个文件或目录会被隐藏：

```shell
$ touch somedir/file01.txt somedir/file02.txt somedir/.secretfile.txt
$ ls -l somedir/
total 0
-rw-r--r-- 1 paul paul 0 Jan 13 19:57 file01.txt
-rw-r--r-- 1 paul paul 0 Jan 13 19:57 file02.txt
# 注意上面列举的文件中没有 .secretfile.txt
$ ls -la somedir/
total 8
drwxr-xr-x 2 paul paul 4096 Jan 13 19:57 .
drwx------ 48 paul paul 4096 Jan 13 19:57 ..
-rw-r--r-- 1 paul paul 0 Jan 13 19:57 file01.txt
-rw-r--r-- 1 paul paul 0 Jan 13 19:57 file02.txt
-rw-r--r-- 1 paul paul 0 Jan 13 19:57 .secretfile.txt
# 这个 -a  选项告诉 ls 去展示“all”文件，包括那些隐藏的
```

## 二、其他

### 2.1 source 命令

可以将`.`当作命令，它是`source`命令的代名词，所以可以用它在当前 shell 中执行一个文件，而不是以某种其它的方式去运行一个脚本文件（这通常指的是 Bash 会产生一个新的 shell 去运行它）。

比如，创建一个名为`myscript`的脚本，内容包含下面一行：

```shell
myvar="Hello"
```

然后通过常规的方法执行它，也就是用`sh myscript`（或者通过`chmod a+x myscript`命令让它可执行，然后运行`./myscript`）。现在尝试并且观察`myvar`的内容，通过`echo $myvar`（理所当然你什么也得不到）。那是因为，当脚本赋值`"Hello"`给`myvar`时，它是在一个隔离的 bash shell 实例中进行的。当脚本运行结束时，这个新产生的实例会消失并且将控制权转交给原来的 shell，而原来的 shell 里甚至都不存在`myvar`变量。

然而，如果这样运行`myscript`：

```shell
. myscript
echo $myvar
```

此时就会打印 Hello 到命令行上。

当`.bashrc`文件发生变化后，经常会用到`.`（或`source`）命令，就像要扩展`PATH`变量那样。在你的当前 shell 实例中，可以使用`.`来让变化立即生效。

### 2.2 扩展与步进

两个点`..`也可以用于构建序列：

```shell
echo {1..10}
```

它会打印出从 1 到 10 的序列。在这种场景下，`..`表示“从左边的值开始，计数到右边的值”。

如果使用下面的方式：

```shell
echo {1..10..2}
```

会得到`1 3 5 7 9`。`..2`这部分命令告诉 Bash 输出这个序列，不过不是每个相差 1，而是相差 2。换句话说，就是会得到从 1 到 10 之间的奇数。

它反着也仍然有效：

```shell
echo {10..1..2}
```

也可以用多个 0 填充你的数字：

```shell
echo {000..121..2}
```

会这样打印出从 0 到 121 之间的偶数（填充了前置 0）：

```
000 002 004 006 ... 050 052 054 ... 116 118 120
```

这样的序列发生器有啥用呢？当然，假设需要创建目录，以对过去 10 年的数字发票进行分类：

```shell
mkdir {2009..2019}_Invoices
```

或者从`frame_43`到`frame_61`每隔三帧删除一帧：

```shell
rm frame_{43..61..3}
```

序列的神奇之处不在于双点，而是花括号（`{}`）的巫术。看看它对于字母是如何工作的。这样做：

```shell
touch file_{a..z}.txt
```

它创建了从`file_a.txt`到`file_z.txt`的文件。

但是必须要注意，使用像`{Z..a}`这样的序列将产生一大堆大写字母和小写字母之间的非字母、数字的字符（既不是数字或字母的字形）。其中一些字形是不可打印的或具有自己的特殊含义。使用它们来生成文件名称可能会导致一系列意外和可能令人不快的影响。

最后一件值得指出的事：包围在`{...}`的序列，它们也可以包含字符串列表：

```shell
touch {blahg,splurg,mmmf}_file.txt
```

将创建了`blahg_file.txt`、`splurg_file.txt`和`mmmf_file.txt`。


