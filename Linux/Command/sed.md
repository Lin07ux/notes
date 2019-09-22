Sed 代表流编辑器(Stream Editor)，在文件处理方面有着重要作用，可用于修改、删除、添加与给定模式匹配的特定行。主要有如下几种模式：

* `p` 显示
* `d` 删除
* `a` 添加
* `c` 替换
* `w` 写入
* `i` 插入

## 删除模式

> 转摘：[如何使用 sed 命令删除文件中的行](https://linux.cn/article-11276-1.html)

下面通过一些示例来演示 sed 命令删除模式的使用方式。

> 注意：为了方便演示，在执行 sed 命令时，不使用`-i`选项（因为这个选项会直接修改文件内容），被移除了行的文件内容将打印到 Linux 终端。

在执行 sed 命令之前，创建一个`sed-demo.txt`文件，内容如下：

```shell
> cat sed-demo.txt
1 Linux Operating System
2 Unix Operating System

3 RHEL
4 Red Hat
5 Fedora
6 Arch Linux
7 CentOS
8 Debian
9 Ubuntu
10 openSUSE
```

### 1. 删除文件的指定行

可以使用如下命令删除文件中指定的行：

```shell
sed 'Nd' file
```

其中：

* `N` 是一个正整数，表示文件中的第 N 行
* `d` 是 sed 命令的一个选项，表示删除行操作

如下，删除文件的第二行：

```shell
> sed '2d' sed-demo.txt
1 Linux Operating System

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 2. 删除文件的最后一行

文件中的最后一行可以使用`$`符号表示，这在不知道文件有多少行的时候非常有效：

```shell
> sed '$d' sed-demo.txt
1 Linux Operating System
2 Unix Operating System

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
10 Ubuntu
```

### 3. 删除指定范围内的行

可以使用`from,to`的格式指定一个范围，从而能够删除指定范围内的行：

```shell
> sed '5,7d' sed-demo.txt
1 Linux Operating System
2 Unix Operating System

4 RHEL
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 4. 删除非连续的多行

使用`nd;md;xd`方式可以一次指定多行，从而可以一次指定多行进行删除：

```shell
> sed '1d;3d;7d;$d' sed-demo.txt
2 Unix Operating System
4 RHEL
5 Red Hat
6 Fedora
8 CentOS
9 Debian
10 Ubuntu
```

### 5. 删除指定范围以外的行

要删除指定范围内的行，可以使用`from,to`格式配合`!`符号来实现。

比如，下面的命令会将 2 ~ 5 之外的内容全部删除：

```shell
> sed '2,5!d' sed-demo.txt
2 Unix Operating System

4 RHEL
5 Red Hat
```

### 6. 删除空行

sed 命令还可以使用正则表达式来匹配行。

> sed 命令中对正则表达式的支持较少，只能支持少量的几种模式。

比如，下面的命令可以用来删除空行：

```shell
> sed '/^$/d' sed-demo.txt
1 Linux Operating System
2 Unix Operating System
4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 7. 删除包含某个模式的行

同样的，可以设置更复杂的正则模式来删除相应的匹配的行。

比如，下面的命令会删除含有`System`字符串的行：

```shell
> sed '/System/d' sed-demo.txt

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 8. 删除匹配多个模式中的任意一个的行

和行数一样，也可以为 sed 指定多个正则模式，表示删除符合任意一个模式的行。

比如，下面的命令可以删除包含`Linux`或`System`字符串的行：

```shell
> sed '/Linux/d;/System/d' sed-demo.txt

4 RHEL
5 Red Hat
6 Fedora
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 9. 删除以指定字符开头的行

通过正则模式中的`^`可以删除指定字符开头的行。

比如，下面删除以 1 开头的行：

```shell
> sed '/^1/d' sed-demo.txt
2 Unix Operating System

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
```

还可以删除以 1 或 2 开头的行：

```shell
 sed '/^[12]/d' sed-demo.txt

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
```

### 10. 删除以指定字符结尾的行

自然，也可以使用正则中的`$`删除以字符结尾的行。

比如，删除`x`或者`m`字符结尾的所有行：

```shell
> sed '/[xm]$/d' sed-demo.txt

4 RHEL
5 Red Hat
6 Fedora
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 11. 删除指定范围内匹配模式的行

还可以将行数和正则结合在一起，删除指定行内符合指定模式的行。

比如，下面的命令删除前六行中，包含 Linux 字符串的行：

```shell
> sed '1,6{/Linux/d;}' sed-demo.txt
2 Unix Operating System

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

### 12. 删除匹配模式的行及其下一行

除了可以删除指定或者匹配的行，还可以同时删除其下一行。

比如，下面的命令删除包含有`System`的行及其下一行。

```shell
> sed '/System/{N;d;}' sed-demo.txt

4 RHEL
5 Red Hat
6 Fedora
7 Arch Linux
8 CentOS
9 Debian
10 Ubuntu
11 openSUSE
```

这里，前两行都符合`/System/`模式，但是在匹配到第一行的时候，就先删除了第一行和第二行，这导致在下一次匹配的时候，第二行已经没了，自然就不会删除第三行了。



