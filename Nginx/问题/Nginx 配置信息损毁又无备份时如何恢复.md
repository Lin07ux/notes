### 问题

假设`/etc/nginx/site-available`下有`a.example.com.conf`、`b.example.com.conf`两个配置文件，用于对两个域名做代理。当着两个文件中的内容被误操作覆盖或删除了，如何重新恢复？

### 解决

只是丢失了 Nginx 了配置，如果 Nginx 进程未关闭，进程中还有配置的副本，可以考虑将这个副本还原出来。

可以通过一个脚本来将 Nginx 进程中的配置导出来，如下：

```sh
# Set pid of nginx master process here
pid=339

# generate gdb commands from the process's memory mappings using awk
cat /proc/$pid/maps | awk '$6 !~ "^/" {split ($1,addrs,"-"); print "dump memory mem_" addrs[1] " 0x" addrs[1] " 0x" addrs[2] ;}END{print "quit"}' > gdb-commands

# use gdb with the -x option to dump these memory regions to mem_* files
gdb -p $pid -x gdb-commands

# look for some (any) nginx.conf text
grep worker_connections mem_*
grep server_name mem_*
```

这个脚本需要 GDB: The GNU Project Debugger 工具的支持。

先安装 GDB(`yum install gdb`)，安装之后，找到 Nginx master 的进程 ID，然后将上面命令中的`pid`变量设置成找到的经常 ID，然后执行脚本即可。

脚本中最后的两个命令用于找到包含`worker_connections`和`server_name`的文件。比如，有如下的输出：

```
[root@centos]# grep server_name mem_*
匹配到二进制文件 mem_558f03f58000
匹配到二进制文件 mem_558f0416f000
```

下载文件之后，用 Visual Studio Code (由于是二进制文件，不要用 Sublime 之类的打开，会是乱码)打开，全局检索一下，以我的博客为例，就能看到熟悉的配置信息了：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557648577091.png"/>

将配置拷贝出来恢复 Nginx 即可。


