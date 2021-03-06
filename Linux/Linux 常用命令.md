### chown

`chown`用于改变文件或文件夹的所属用户和所属组。

```shell
chown [-R] user:group dir
```

其中选项`-R`表示将该文件夹和文件夹内的所有的文件和文件夹都改成同样的用户和属组。

### du

`du`可以用于查看文件或文件夹的大小。

* `du -h` 查看文件夹中每一个文件的大小，会列出来每一个文件的大小。使用该命令时，需要进入文件夹中，或者在后面加上文件夹的名称。
* `du -s -h 文件夹名` 查看该文件夹的大小。
* `du -ks [文件夹名]` 查看当前所在文件夹或指定文件夹的大小。
* `df -lh` 查看磁盘空间

### ls (List)
`ls`会列举出当前工作目录的内容（文件或文件夹），就跟你在 GUI 中打开一个文件夹去看里面的内容一样。

这个命令还可以结合`-l`、`-a`等参数来用不同的方式显示当前目录的内容。

![ls](http://cnd.qiniu.lin07ux.cn/markdown/1470977375989.png)

### mkdir (Make Directory)
新建一个新目录

### pwd (Print Working Directory)
显示当前工作目录

### cd (Change Directory)
对于当前在终端运行的会中中，`cd`将给定的文件夹（或目录）设置成当前工作目录。

> 使用`cd -`可以返回前一个进入的工作目录。

![cd](http://cnd.qiniu.lin07ux.cn/markdown/1470977556815.png)

### rm (Remove)
删除给定的文件或文件夹，可以使用`rm -r`递归删除文件夹，用`rm -f`删除不会进行确认。

### rmdir (Remove Directory)
删除给定的目录。

### cp (Copy)
对文件或文件夹进行复制，可以使用`cp -r`选项来递归复制文件夹。

### scp (远程文件拷贝)
scp 可以在两台电脑中进行文件、文件夹的拷贝：既可以从远程拷贝到本地，也可以从本地拷贝到远程电脑。

语法如下：

```shell
scp source dest # 将 source 拷贝到 dest
```

示例如下：

```shell
# 将远程机 202.112.23.12 上的 example.c 拷贝至本机的当前目录
scp root@202.112.23.12:/home/work/example.c

# 将远程机 202.112.23.12 上的 project 目录拷贝至本机的当前目录
scp -r root@202.112.23.12:/home/work/project
```


### mv (Move)
对文件或文件夹进行移动，如果文件或文件夹移动后和原先处于同一个父目录，则是对该文件或文件夹进行重命名。

### cat (concatenate and print files)
在标准输出（监控器或屏幕）上查看文件内容

### tail (print TAIL (from last))
`tail`默认在标准输出上显示给定文件的最后 10 行内容，可以使用`tail -n N`指定在标准输出上显示文件的最后 N 行内容。也可以使用`tail -f file_name`来监视文件的变化。

### less (print LESS)
`less`按页或按窗口打印文件内容。在查看包含大量文本数据的大文件时是非常有用和高效的。你可以使用`Ctrl + F`向前翻页，`Ctrl + B`向后翻页。

### grep
`grep ""`在给定的文件中搜寻指定的字符串。`grep -i ""`在搜寻时会忽略字符串的大小写，而`grep -r ""`则会在当前工作目录的文件中递归搜寻指定的字符串。

### tar
`tar`命令能创建、查看和提取 tar 压缩文件。

`tar -cvf` 创建对应压缩文件
`tar -tvf` 查看对应压缩文件
`tar -xvf` 提取对应压缩文件

### gzip/gunzip
前者用于创建和提取 gzip 压缩文件，还可以用`gzip -d`来提取压缩文件。后者用于解压 gzip 压缩文件。

压缩和解压缩后，默认情况下，原文件都没有了，只有压缩后的文件或解压后的文件。可以通过加上`-c`选项，并利用 Liinux 的重定向来保留原文件。
 
```shell
# 将文件压缩为文件 test.txt.gz，原来的文件则没有了
gzip test.txt
# 加上 -c 选项并利用 linux 的重定向可以保留源文件
gzip -c test.txt > /root/test.gz

gunzip test.txt.gz
gunzip -c /root/test.gz > ./test.txt  
```

### zip/unzip
前者将文件或目录打包成 zip 文档，后者将 zip 文件解压出来。

```shell
# 将 test.txt 文件压缩为 test.zip，也可以指定压缩包的目录，例如 /root/test.zip 
zip test.zip test.txt

# 默认将文件解压到当前目录
unzip test.zip
# 如果要解压到指定目录，可以加上 -d 选项 
unzip test.zip -d /root/ 
```
对 zip 文档进行解压。在解压之前，可以使用`unzip -l`命令查看文件内容。

### help
`--help`会在终端列出所有可用的命令，可以使用任何命令的`-h`或`--help`选项来查看该命令的具体用法。

### whatis (What is this command)
`whatis`会用单行来描述给定的命令。

### man (Manual)

`man <command>`会为给定的命令显示一个手册页面。可以通过`q`退出手册页面。

### exit
`exit`用于结束当前的终端会话。

### ping
`ping`通过发送数据包ping远程主机(服务器)，常用与检测网络连接和服务器状态。

### who (Who Is logged in)
`who`能列出当前登录的用户名。

### su (Switch User)
切换不同的用户。即使没有使用密码，超级用户也能切换到其它用户。

### uname
显示出关于系统的重要信息，如内核名称、主机名、内核版本、处理机类型等等，使用`uname -a`可以查看所有信息。

### free (Free memory)
`free`会显示出系统的空闲内存、已经占用内存、可利用的交换内存等信息，`free -m`将结果中的单位转换成 MB，而`free –g`则转换成 GB。

### df (Disk space Free)
`df`查看文件系统中磁盘的使用情况–硬盘已用和可用的存储空间以及其它存储设备。你可以使用`df -h`将结果以人类可读的方式显示。

### ps (ProcesseS)
`ps`显示系统的运行进程。

### top (TOP processes)
`top`命令会默认按照 CPU 的占用情况，显示占用量较大的进程,可以使用`top -u`查看某个用户的 CPU 使用排名情况。

### shutdown
`shutdown`用于关闭计算机，而`shutdown -r`用于重启计算机。

### sort
`sort`命令是根据不同的数据类型以行为单位对数据进行排序。默认比较规则是从首字符向后，按照 ASCII 码值进行比较，将结果按照升序输出。

基本格式如下：`sort [-bcfMnrtk] [source-file] [-o output-file]`

可使用的参数有：

1. `-b` 忽略每行前面的所有空格字符，从第一个可见字符开始比较。
2. `-c` 检查文件是否已经排好序，如果乱序，则输出第一个乱序行的相关信息，最后返回1。
3. `-C` 检查文件是否已经排好序，如果乱序，则不输出内容，仅返回1。
4. `-f` 排序时忽略大小写字母。
5. `-M` 将前面3个字母依照月份的缩写进行排序，比如 JAN 小于 FEB。
6. `-n` 依照数值的大小排序。
7. `-o` 将排序后的结果存入指定的文件。
8. `-r` 降序输出。
9. `-t <分隔字符>` 指定排序时所用的栏位分隔字符
10. `-u` 在输出行中去除重复行
11. `-k` 选择以哪个区间进行排序。

选项有：

* `-o` 重定向结果的输出到指定的文件中。默认是将结果输出到标准输出中。虽然可以使用`>`符号进行重定向，但是该方式对于将结果输出到原文件中的需求则会导致将原文件清空了。而使用这个`-o`选项就可以解决这个问题：`sort -r test.dat -o test.dat`。
* `-t` 指定原文件中数据列的分割符。
* `-k` 指定待排序的列的次序。次序从 1 开始。

比如，有如下格式的数据：

```
a   12
b   32
c   1
```

如果想以第二列数值大小降序输出，则需要使用`-t`和`-k`参数了：`sort -nr -t\t -k2 test.bat -o test.bat`。

### mount/unmount

前者用于将设备挂载到指定的路径中，后者则是取消挂载。

`mount /what /where` 其中，`what`是设备的名称，例如 U 盘。`where`是挂载点，通常是`/mnt`。

`mount --bind /where1 /where2` mount 命令不能直接挂载文件夹，需要使用`--bind`参数才行。

如果要保证开机自动挂载，可以修改`/etc/fstab`文件：

```vim
/dev/xvdb1       /mnt/ftpdate        ext3    defaults      0 0

# 如
/mnt/ftpdate/公司公共资料 /mnt/ftpdate/林胜利/公司公共资料 none bind 0 0
```
	
`umount /where` 卸载挂载在`where`的设备或者文件夹。


## 参考
[29个你必须知道的Linux命令](https://github.com/dwqs/blog/issues/24)

