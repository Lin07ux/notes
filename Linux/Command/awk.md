awk 是一个强大的文本分析工具，相对于 grep 的查找、sed 的编辑，awk 在其对数据分析并生成报告时，显得尤为强大。

简单来说，awk 就是把文件逐行的读入，并以空格作为默认分隔符将每行内容进行分片，再对切开的部分进行各种分析处理。

> 转摘：
> 1. [Linux生产环境上，最常用的一套“AWK“技巧](https://mp.weixin.qq.com/s/aRy3QlMUpSNOKf2pyN6Uuw)
> 2. [换一种视角理解 awk 命令](http://www.barretlee.com/blog/2019/10/29/awk/)
> 3. [linux awk详解](https://www.cnblogs.com/djoker/p/9408716.html)

## 一、简介

### 1.1 基本信息

awk 的名称来自于它的创始人：Alfred Aho、Peter Weinberger 和 Brian Kernighan 姓氏的首个字母。

awk 有 3 个不同的版本：awk、nawk、gawk，未做特别说明，一般指 gawk，这是 awk 的 GNU 版本。其使用方式很像编程语言，可以使用多种条件逻辑、循环等语句。实际上 awk 的确拥有自己的语言：AWK 程序设计语言，三维创建者已将它正式定义为“样式扫描和处理语言”。

awk 的使用方式如下：

```shell
awk [-F fs] [-v var=value] [ 'prog' | -f progfile ] [ file ... ] 
```

### 1.2 工作流程

awk 的主要处理逻辑在参数 prog 参数中设置，这个参数主要可以分为三段：BEGIN 前置段、数据处理段、END 后置段。每一段都要分别用`{}`包裹起来。

awk 的工作流程如下：

1. 如果处理模式 prog 中有 BEGIN 段，则先执行 BEGIN 段代码
2. 依次从各个输入的文件中读入由`\n`换行符分隔的一条记录
3. 使用域分隔符(默认为` `空格)对这条记录进行划分，生成多个域
4. 使用`$n`分别代表第 n 个域的值，如`$1`表示第一个域，`$2`表示第二个域，依次类推。而`$0`表示所有的域
5. 执行处理模式 prog 中后续的代码
6. 重复执行 2 ~ 5 步骤，直到最后一个文件的最后一行内容
7. 如果处理模式 prog 中存在 END 段，则执行 END 段代码

### 1.3 -F 指定域分隔符

awk 默认的域分隔符是空白符或 tab 键，但是也可以使用`-F`选项来指定分隔符。

比如，对于`/etc/passwd`文件的内容，总共有 10 行，每行有 7 份数据，并使用`:`连接符合并成一行：

```
root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
bin:x:2:2:bin:/bin:/usr/sbin/nologin
sys:x:3:3:sys:/dev:/usr/sbin/nologin
sync:x:4:65534:sync:/bin:/bin/sync
games:x:5:60:games:/usr/games:/usr/sbin/nologin
man:x:6:12:man:/var/cache/man:/usr/sbin/nologin
lp:x:7:7:lp:/var/spool/lpd:/usr/sbin/nologin
mail:x:8:8:mail:/var/mail:/usr/sbin/nologin
news:x:9:9:news:/var/spool/news:/usr/sbin/nologin
```

用 awk 命令使用`:`符号分隔上面的内容并全部展示出来：

```shell
> awk -F : '{ print $1, $2, $3, $4, $5, $6, $7 }' /etc/passwd
root x 0 0 root /root /bin/bash
daemon x 1 1 daemon /usr/sbin /usr/sbin/nologin
bin x 2 2 bin /bin /usr/sbin/nologin
sys x 3 3 sys /dev /usr/sbin/nologin
sync x 4 65534 sync /bin /bin/sync
games x 5 60 games /usr/games /usr/sbin/nologin
man x 6 12 man /var/cache/man /usr/sbin/nologin
lp x 7 7 lp /var/spool/lpd /usr/sbin/nologin
mail x 8 8 mail /var/mail /usr/sbin/nologin
news x 9 9 news /var/spool/news /usr/sbin/nologin
```

> 这里的`'{ print $1, $2, $3, $4, $5, $6, $7 }'`表示将分隔得到的全部 7 个域的内容打印出来，后面会介绍相关内容。

## 二、处理模式

awk 最核心的部分就是 prog 参数，其代表了对分隔出来的域进行何种处理。这个参数是一个字符串，里面可以使用一些简单的控制语句和循环语句进行操作，可以将其当做一份代码。如果 prog 参数的内容过多，可以将其写入一个文件中，并利用`-f progfile`方式引入 awk 命令。

### 2.1 print

对域内容最常用的处理是展示出来，这可以使用`print()`方法来实现，这是一个方法，也可以作为一个表达式。

比如：

```shell
awk -F : '{ print($1, $2, $3, $4, $5, $6, $7) }' /etc/passswd
# 等价于
awk -F : '{ print $1, $2, $3, $4, $5, $6, $7 }' /etc/passswd
```

这样就可以将从第一个域到第七个域打印出来。

### 2.2 printf

`print`打印的内容没有格式化，会感觉较为杂乱，可以使用`printf()`方法来格式化输出。

比如，可以设置各个域的内容的宽度：

```shell
cat /etc/passswd | awk -F : '{\
  printf("%8s %5s %5s %5s %5s %15s %20s\n", $1, $2, $3, $4, $5, $6, $7) \
}'
```

此时展示的效果如下：

```
    root     x     0     0  root           /root            /bin/bash
  daemon     x     1     1 daemon       /usr/sbin   /usr/sbin/nologin
     bin     x     2     2   bin            /bin    /usr/sbin/nologin
     sys     x     3     3   sys            /dev    /usr/sbin/nologin
    sync     x     4 65534  sync            /bin            /bin/sync
   games     x     5    60 games      /usr/games    /usr/sbin/nologin
     man     x     6    12   man  /var/cache/man    /usr/sbin/nologin
      lp     x     7     7    lp  /var/spool/lpd    /usr/sbin/nologin
    mail     x     8     8  mail       /var/mail    /usr/sbin/nologin
    news     x     9     9  news /var/spool/news    /usr/sbin/nologin
```

可以看到，这样各个域之间就有了明显的区隔，更易看清。

### 2.3 BEGIN

上面展示的都是数据，而没有对各个域的含义做出说明。awk 可以通过设置 BEGIN 段来在对文件内容进行处理之前进行相关处理。前置处理使用`BEGIN {}`来定义，这个前置处理只会在对数据处理之前执行一次。

比如，下面的代码会给每一个域设置一个名称，而对数据的处理则不变：

```shell
cat /etc/passwd | awk -F : '\
  BEGIN {\
    printf("%8s %5s %5s %5s %6s %15s %20s\n", "R1", "R2", "R3", "R4", "R5", "R6", "R7") \
  } \
  { printf("%8s %5s %5s %5s %6s %15s %20s\n", $1, $2, $3, $4, $5, $6, $7) } \
'
```

输出结果如下：

```
      R1    R2    R3    R4     R5              R6                   R7
    root     x     0     0   root           /root            /bin/bash
  daemon     x     1     1 daemon       /usr/sbin    /usr/sbin/nologin
     bin     x     2     2    bin            /bin    /usr/sbin/nologin
     sys     x     3     3    sys            /dev    /usr/sbin/nologin
    sync     x     4 65534   sync            /bin            /bin/sync
   games     x     5    60  games      /usr/games    /usr/sbin/nologin
     man     x     6    12    man  /var/cache/man    /usr/sbin/nologin
      lp     x     7     7     lp  /var/spool/lpd    /usr/sbin/nologin
    mail     x     8     8   mail       /var/mail    /usr/sbin/nologin
    news     x     9     9   news /var/spool/news    /usr/sbin/nologin
```

可以看到输出结果的第一行是一个表头

### 2.4 END

同样的，awk 命令可以在数据处理完成之后，再执行一段后置代码，后置代码使用`END {}`来定义，并且也只会在数据处理段执行完成之后执行，只执行一次。

比如，下面的代码在上面的示例基础上，增加了后置代码段：

```shell
cat /etc/passwd | awk -F : ' \
  BEGIN { \
    printf("%8s %5s %5s %5s %6s %15s %20s\n", "R1", "R2", "R3", "R4", "R5", "R6", "R7"); \
    printf("----------------------------------------------------------------------\n") \
  } \
  { printf("%8s %5s %5s %5s %6s %15s %20s\n", $1, $2, $3, $4, $5, $6, $7) } \
  END { \
    printf("----------------------------------------------------------------------\n") \
  } \
'
```

输出结果如下：

```
      R1    R2    R3    R4     R5              R6                   R7
----------------------------------------------------------------------
    root     x     0     0   root           /root            /bin/bash
  daemon     x     1     1 daemon       /usr/sbin    /usr/sbin/nologin
     bin     x     2     2    bin            /bin    /usr/sbin/nologin
     sys     x     3     3    sys            /dev    /usr/sbin/nologin
    sync     x     4 65534   sync            /bin            /bin/sync
   games     x     5    60  games      /usr/games    /usr/sbin/nologin
     man     x     6    12    man  /var/cache/man    /usr/sbin/nologin
      lp     x     7     7     lp  /var/spool/lpd    /usr/sbin/nologin
    mail     x     8     8   mail       /var/mail    /usr/sbin/nologin
    news     x     9     9   news /var/spool/news    /usr/sbin/nologin
----------------------------------------------------------------------
```

可以看到，在代码的最后面还输出了一行由`-`组成的横线。

### 2.5 正则匹配

awk 的 prog 参数中，还可以设置正则模式，用来匹配需要处理的行。如果当前行内容不符合正则模式，则不会被处理。

比如，下面的命令将会处理以`sy`开头的行，并输出改行的第一列数据：

```shell
awk -F : ' /^sy/ { print $1 } ' /etc/passwd
```

输出结果如下：

```
sys
sync
```

## 三、编程语言特性

awk 不仅仅是一个命令行工具，它的 prog 参数处理可以使用常见的`print()`、`printf()`方法，还有使用循环、条件、变量等。

### 3.1 内置变量

awk 在解析了文件的一行数据之后，会将各个域分别赋值给从`$1`开始的一系列变量，并且会将原始的整行数据赋值给`$0`。这些`$0`、`$1`、`$2`等变量就是内置变量，会由 awk 自动根据解析情况进行赋值。

除了这些代表各个域的变量之外，awk 还内置了其他一些变量，这些变量也会自动根据处理的数据来更新：

* `FS` 这个内置变量就表示分隔符，可以通过在 BEGIN 段中为该变量设置多个值来实现多个分隔符的设置。


    ```shell
    awk -F ':' ' { print $1 } ' file
    # 等价于
    awk ' BEGIN { FS=":" } { print $1 } ' file
    
    # 设置多个分隔符：, : |
    awk ' BEGIN { FS="[,:|]" } { print $1 } ' file
    ```

* `OFS` 输出内容分隔符，默认情况下就是空格。这个是和`FS`变量相对的，在列数非常多的时候可以大大简化操作。

    ```shell
    awk ' { print $1, " - ", $2 } ' file
    # 等同于
    awk ' BEGIN { FS=":"; OFS=" - "} { print $1, $2 } ' file
    ```

* `NF` 表示当前处理的行有几列。一般会用在条件过滤中。

    ```shell
    # 输出每行数据有几列
    awk -F : ' { print NF, $1 } ' file
    # 输出列数大于 3 的内容
    awk -F : ' { if (NF > 3) { print NF, $1 } } ' file
    ```

* `NR` 行号，表示当前处理的是第几行数据。

    ```shell
    # 输出行号和第一列数据
    awk -F : ' { print NR, $1 } ' file
    ```

* `RS` 原始数据每行的分隔符，一般是换行符`\n`。

* `ORS` 与`RS`相对应，表示输出的分隔符，一般是换行符`\n`。

* `FILENAME` 当前处理的文件的名称，在一次性处理多个文件的时候非常有用。

### 3.2 自定义变量

awk 中的处理代码也允许自定义变量，来记录使用者关心的值。自定义变量不需要声明，直接使用即可，但是一般建议进行初始化。而且 awk 中的变量不需要指定类型，是弱类型变量。

比如，下面的代码可以统计出无法登录的用户的数量：

```shell
awk -F : '\
  BEGIN { count = 0 } \
  { if ($7 == "/usr/sbin/nologin") { count = count + 1 } } \
  END { printf("total %s items with nologin\n", count) } \
' /etc/passwd
```

这里首先在 BEGIN 代码段中初始化了一个自定义变量`count`，然后在数据处理段中判断用户是否无法登录，是的话就将变量`count`增加 1，最后在 END 代码段中输出`count`的值。

### 3.3 数学运算

awk 可以执行一些简单的数学运算，除了常用的加减乘除，还支持其他的数学运算：

* 自增，如`count++`
* 自减，如`count--`
* `int`
* `log` 对数
* `sqrt` 开方
* `exp` 指数
* `sin` 正弦
* `cos` 余弦
* `atan2`
* `rand`
* `srand`

### 3.4 字符串操作

awk 本来就是处理字符串的，所以也内置了很多字符串操作函数：

* `length(str)` 获取字符串长度
* `split(inputString, outputArray, separator)` 分隔字符串
* `substr(inputString, location, length)` 子字符串

### 3.5 其他特性

awk 本就是一个编程语言，虽然小众，但是基本的循环、条件语句都存在，而且支持数组等数据类型，还有其他的内置函数。如果需要复杂一点的逻辑则需要从网络上进行学习。

> [The GNU Awk User's Guid](http://www.gnu.org/software/gawk/manual/gawk.html)

```awk
# logic
if (x = a) {}
if (x = a) {} else {}
while (x = a) { break; continue; }
do {} while (x = a)
for (;;) {}

# array
arr[key] = value
for (key in arr) { arr[key] }
delete arr[key]

# 简单排序
asort(arr)
```

## 四、常用示例

1. 输出第二列大于 0 的记录：
    
    ```shell
    awk ' $2 > 0 { print }' file
    ```

2. 输出以`tcp`开头的记录：

    ```shell
    awk ' /^tcp/ { print }' file
    ```

3. 过滤第七列中包含`nologin`的记录：

    ```shell
    awk ' $7 ~ /nologin/ { print } ' file
    ```

4. 输出前 7 行数据：

    ```shell
    awk ' NR <= 7 { print }' file
    ```

5. 过滤(去除)空白行：

    ```shell
    awk ' NF ' file
    ```

6. 输出奇数行：

    ```shell
    awk ' a = !a ' file
    ```

7. 输出行数：

    ```shell
    awk ' END { print NR } ' file
    ```

