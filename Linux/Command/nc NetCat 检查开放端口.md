> 转摘：[手把手教你 3 个 Linux 中快速检测端口的小技巧](https://cloud.tencent.com/developer/article/1593973)

使用 NetCat(`nc`)可以扫描单个或范围端口，其使用 TCP 或 UDP 协议跨网络连接读取和写入数据来判断端口是否可用。常用来进行开放端口的扫描检测。

`nc` 命令的选项很多，常用的选项如下：

* `-z` 表示仅扫描打开的端口而不发送任何数据；
* `-u` 表示扫描 UDP 端口，默认扫描的是 TCP 端口；
* `-v` 获取更多的详细信息。

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
> ```shell
> nc -z -v 10.10.8.8 20-80 2>&1 | grep succeeded
> ```



