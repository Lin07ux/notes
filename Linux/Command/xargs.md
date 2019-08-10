> 转摘：[xargs 命令教程](http://www.ruanyifeng.com/blog/2019/08/xargs-tutorial.html)

## 一、使用

`xargs`命令的作用是将标准输入转为命令行参数。一般有两种使用方式：通过管道`|`将其他命令的输出转为另外命令的参数，或者允许用户直接输入数据作为命令的参数。

> `xargs`命令对处理后的对每个参数执行一次命令。

### 1.1 管道使用

通过管道符号`|`转换输出为其他命令的输入参数时，`xargs`命令的格式如下：

```
<some_command> | xargs [-options] [command]
```

这会将`some_command`命令的输出结果作为后面的`command`命令的参数，并运行后面的`command`命令。

`xargs`的作用在于，大多数命令（比如`rm`、`mkdir`、`ls`）与管道一起使用时，都需要`xargs`将标准输入转为命令行参数。

```shell
echo "one two three" | xargs mkdir
```

上面的代码等同于`mkdir one two three`。如果不加`xargs`就会报错，提示`mkdir`缺少操作参数。

### 1.2 单独使用

大多数时候，`xargs`命令都是跟管道一起使用的。但是它也可以单独使用。单独使用时，其后可以不跟随命令，此时使用默认的命令`echo`。

输入`xargs`按下回车以后，命令行就会等待用户输入，作为标准输入。此时可以输入任意内容，然后按下`Ctrl + D`表示输入结束，这时`echo`命令就会把前面的输入打印出来。

比如：

```
$ xargs
hello (Ctrl + D)
hello
```

又如：

```
$ xargs find -name
"*.txt" (Ctrl + D)
./foo.txt
./hello.txt
```

上面的例子输入`xargs find -name`以后，命令行会等待用户输入所要搜索的文件。用户输入`"*.txt"`，表示搜索当前目录下的所有 TXT 文件，然后按下`Ctrl + D`表示输入结束。这时就相当执行`find -name *.txt`。

## 二、参数

`xargs`命令有一些参数可以使用，通过这些参数可以调整默认行为。

### 2.1 -d 参数与分隔符

默认情况下，`xargs`将换行符和空格作为分隔符，把标准输入分解成一个个命令行参数。而`-d`参数可以更改分隔符。

比如：

```shell
echo "one two three" | xargs mkdir
```

这会新建三个子目录，因为`xargs`将`one two three`分解成三个命令行参数，从而执行`mkdir one two three`。

通过参数`-d`设置分隔符为`\t`，那么就可以有不同的参数分隔方式：

```shell
echo -e "a\tb\tc" | xargs -d "\t" echo
```

这时就会输出`a b c`。

> `echo`命令的`-e`参数表示解释转义字符。

### 2.2 -0 和 null 分隔符

`-d`参数可以让`xargs`将参数按照可以表示的字符进行分割，而`-0`参数则让`xargs`命令用 null 分割参数。这在处理文件路径中会比较有用。

由于`xargs`默认将空格作为分隔符，所以不太适合处理文件名，因为文件名可能包含空格。

比如，`find`命令有一个特别的参数`-print0`，指定输出的文件列表以 null 分隔。然后`xargs`命令的`-0`参数表示用 null 当作分隔符。这样就可以很好的处理文件路径：

```shell
find /path -type f -print0 | xargs -0 rm
```

上面命令删除`/path`路径下的所有文件。由于分隔符是 null，所以处理包含空格的文件名，也不会报错。

### 2.3 -p 和 -t 参数打印执行的命令

使用`xargs`命令以后，由于存在转换参数过程，有时需要确认一下到底执行的是什么命令。此时可以考虑使用`-p`或`-t`参数将最终要执行的命令打印出来。

这两个参数的区别在于：

* `-p`打印最终执行的命令后，不会立即执行这个命令，而是等待确认，确认执行后才会执行。
* `-t`打印最终执行的命令后，会立即执行这个命令，没有确认过程。

比如：

```
$ echo 'one two three' | xargs -p touch
touch one two three ?...
```

上面的命令执行以后，会打印出最终要执行的命令，让用户确认。用户按下回车以后，才会真正执行。

而对于`-t`参数：

```
$ echo 'one two three' | xargs -t rm
rm one two three
```

打印出最终要执行的命令后会直接执行，不需要用户确认。

### 2.4 -L 参数

如果标准输入包含多行，`-L`参数指定多少行输入作为一个命令行参数。

比如，对于如下命令，会报错：

```
$ xargs find -name
"*.txt"   
"*.md"
find: paths must precede expression: `*.md'
```

上面命令同时将`*.txt`和`*.md`两行作为命令行参数传给`find`命令，这就导致了报错。

此时使用`-L`参数，指定每行作为一个命令行参数，那么`xargs`就会对每行执行一次命令，就不会报错了。如下：

```
$ xargs -L 1 find -name
"*.txt"
./foo.txt
./hello.txt
"*.md"
./README.md
```

上面命令指定了每一行(`-L 1`)作为命令行参数，分别运行一次命令(`find -name`)。

又如：

```
$ echo -e "a\nb\nc" | xargs -L 1 echo
a
b
c
```

上面代码指定每行运行一次`echo`命令，所以`echo`命令执行了三次，输出了三行。

### 2.5 -n 参数

`-L`参数虽然解决了多行的问题，但是有时用户会在同一行输入多项，此时也有可能导致错误。比如：

```
$ xargs find -name
"*.txt" "*.md"
find: paths must precede expression: `*.md'
```

`-n`参数指定每次将多少项标准输入作为命令行参数，这就可以解决`-L`参数不能解决的问题。比如：

```shell
xargs -n 1 find -name
```

上面命令指定将每一项(`-n 1`)标准输入作为命令行参数，分别执行一次命令(`find -name`)，这就不会出错了。

又如：

```
$ echo {0..9} | xargs -n 2 echo
0 1
2 3
4 5
6 7
8 9
```

上面命令指定，每两个参数运行一次`echo`命令。所以 10 个阿拉伯数字运行了五次`echo`命令，输出了五行。

### 2.6 -I 参数

如果`xargs`要将命令行参数同时传给多个命令，可以使用`-I`参数。

`-I`指定每一项命令行参数的替代字符串，相当于定义了一个变量，每个命令都用这个变量作为参数进行执行。

比如：

```
$ cat foo.txt
one
two
three

$ cat foo.txt | xargs -I file sh -c 'echo file; mkdir file'
one 
two
three

$ ls 
one two three
```

上面代码中，`foo.txt`是一个三行的文本文件。希望对每一项命令行参数执行两个命令(`echo`和`mkdir`)，使用`-I file`表示`file`是命令行参数的替代字符串。执行命令时，具体的参数会替代掉`echo file; mkdir file`里面的两个`file`。

### 2.7 --max-procs 参数

`xargs`默认只用一个进程执行命令，如果命令要执行多次，必须等上一次执行完，才能执行下一次。

`--max-procs`参数指定同时用多少个进程并行执行命令，比如：

* `--max-procs 2`表示同时最多使用两个进程
* `--max-procs 0`表示不限制进程数。

对于如下的命令：

```shell
docker ps -q | xargs -n 1 --max-procs 0 docker kill
```

它会同时关闭尽可能多的 Docker 容器，这样运行速度会快很多。

