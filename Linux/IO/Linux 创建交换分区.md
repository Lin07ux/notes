系统交换分区可以作为内存的一个缓存使用，虽然不是内存，但是也可以提高内存使用效率，增加系统可用内存空间。

交换分区一般设置为内存的 1-2 倍即可，太大也无用。

### 1. 查看交换分区的信息**

使用`swapon -s`命令就会显示系统上的交换分区的信息。类似如下：

```
[root@iZ28xvb5f81Z ~]# swapon -s
Filename                 Type           Size      Used    Priority
/swapfile                file           2097148   0       -1
```

> 如果没有创建任何交换分区，则不会有内容显示。
	
### 2. 添加交换文件

添加交换文件需要使用`dd`命令，该命令有如下的一些选项:

* `if` 指定挂载的位置
* `of` 指定交换文件的名称
* `bs` 分区单位大小
* `count` 指定交换分区的大小，单位是 kb，可以写成 2048000 或者 2048k
	
完整使用如下：

```shell
dd if=/dev/zero of=/swapfile bs=1024 count=2048k
```

输出类似如下：

```
[root@iZ28xvb5f81Z ~]# dd if=/dev/zero of=/swapfile bs=1024 count=2048k
2048000+0 records in
2048000+0 records out
2097152000 bytes (2.1 GB) copied, 3.09593 s, 347 MB/s
```

### 3. 创建交换分区

添加交换文件之后，还需要把该文件创建成交换分区才可被系统使用。创建交换分区使用`mkswap`命令。如下：

```shell
# /swapfile 就是上一步添加交换文件时指定的文件名
mkswap /swapfile
```

输出类似如下：

```
mkswap: /swapfile: warning: don’t erase bootbits sectors
on whole disk. Use -f to force.
Setting up swapspace version 1, size = 2097147 KiB
no label, UUID=9722999f-ae6c-4caa-ac3a-a74369740a17
```

### 4. 开启交换分区

交换分区创建完成之后，还需要开启才能发挥作用。一般希望将其设置为开机自启动，否则每次重启这个交换分区并不能起作用。

```shell
# /swapfile 就是上面创建的交换文件名称
swapon /swapfile

# 设置开机自启动
echo "/swapfile swap swap defaults 0 0" >>/etc/fstab
```

### 5. 检查是否生效

一切完成之后，可以通过查看内存的使用情况来确定交换分区是否起作用了。输出类似如下：

```
[root@iZ28xvb5f81Z ~]# free -m
             total       used       free     shared    buffers     cached
Mem:           994        927         67         78         45         96
-/+ buffers/cache:        786        208
Swap:         2047         33       2014
```



