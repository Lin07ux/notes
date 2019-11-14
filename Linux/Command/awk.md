awk 是一个强大的文本分析工具，相对于 grep 的查找、sed 的编辑，awk 在其对数据分析并生成报告时，显得尤为强大。

简单来说，awk 就是把文件逐行的读入，并以空格作为默认分隔符将每行内容进行分片，再对切开的部分进行各种分析处理。

## 一、简介

### 1.1 基本信息

awk 的名称来自于它的创始人：Alfred Aho、Peter Weinberger 和 Brian Kernighan 姓氏的首个字母。

awk 有 3 个不同的版本：awk、nawk、gawk，未做特别说明，一般指 gawk，这是 awk 的 GNU 版本。其使用方式很像编程语言，可以使用多种条件逻辑、循环等语句。实际上 awk 的确拥有自己的语言：AWK 程序设计语言，三维创建者已将它正式定义为“样式扫描和处理语言”。

awk 的使用方式如下：

```shell
awk [-F fs] [-v var=value] [ 'prog' | -f progfile ] [ file ... ] 
```

### 1.2 工作流程

awk 的工作流程如下：

1. 从输入的文件中依次读入由`\n`换行符分隔的一条记录
2. 使用域分隔符(默认为` `空格)对这条记录进行划分，生成多个域
3. 使用`$n`分别代表第 n 个域的值，如`$1`表示第一个域，`$2`表示第二个域，依次类推。而`$0`表示所有的域
4. 对划分出来的域进行后续的处理

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

## 二、后续处理

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

### 2.3 BEGIN 表头

上面展示的都是数据，而没有对各个域的含义做出说明。awk 允许在内容输出之前添加一些内容，这些内容只会输出一次，而非向上面的程序一样对每一行都输出。

awk 的表头输出使用`BEGIN {}`段落来定义。

比如，下面的代码会给每一个域设置一个名称，而对数据的处理则不变：

```shell
cat /etc/passwd | awk -F : '\
  BEGIN {\
    printf("%8s %5s %5s %5s %5s %15s %20s\n", "R1", "R2", "R3", "R4", "R5", "R6", "R7") \
  } \
  { printf("%8s %5s %5s %5s %5s %15s %20s\n", $1, $2, $3, $4, $5, $6, $7) } \
'
```

输出结果如下：

```
      R1    R2    R3    R4    R5              R6                   R7
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

