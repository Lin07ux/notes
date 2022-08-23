### 1. cp & scp

`cp`命令可以在单机上进行复制，`scp`命令则可以实现跨机器复制，比如：

```shell
scp -Crvp -l 1024 logs/ root@remoteserver:/opt/logs
```

其中，

* `-C`表示压缩
* `-r`是循环传输整个目录
* `-p`表示保留源文件的一些属性
* `-l`表示限制带宽，单位是 kb/s
* `-v` 表示显示详细信息

### 2. rsync

scp 命令虽然好用，但是如果传输的文件非常的大，比如每天上 T 的日志文件，不可能每次都把这些文件重新传输一遍，所以采用增量备份会成为一个首要的需求。当然，如果在传入过程中，能够排除一些文件，就更好了。

这就是 rsync 命令的适用场景了，例如：

```shell
rsync -prz --exclude 'bin' --bwlimit=1024 logs/ root@remoteserver:/opt/logs
```

同样的：

* `-r` 表示递归
* `-p` 表示保留属性
* `-z` 表示开启压缩
* `--bwlimit` 表示限制带宽
* `--exclude` 指定要忽略的文件
* `--progress` 显示拷贝的进度

当然，rsync 和 scp 两者还是有点小区别的：

* rsync 默认是只拷贝有变动的文件，scp 则是全量拷贝，所以 rsync 很适合做增量备份；
* scp 是加密传输，而 rsync 不是。


