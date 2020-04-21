* `localhot(local)` 不使用 TCP/IP 连接，而使用 Unix socket。它不受网络防火墙和网卡相关的的限制。
* `127.0.0.1` 是通过网卡传输，使用 TCP/IP 连接，依赖网卡，并受到网络防火墙和网卡相关的限制。

一般设置程序时本地服务用`localhost`是最好的，`localhost`不会解析成 IP，也不会占用网卡、网络资源。有时候用`localhost`可以链接，但用`127.0.0.1`不可以的情况原因就是在于此。使用`localhost`访问时，系统带的本机当前用户的权限去访问，而用 IP 的时候，等于本机是通过网络再去访问本机，可能涉及到网络用户的权限。

使用`localhost`的时候需要注意，一般应该配置`php.ini`文件中的数据库的 socket 配置，或者对特定的项目配置 DB SOCKET。比如，使用 MySQL 数据库时，应该配置`pdo_mysql.default_socket`，否则会出现`SQLSTATE[HY000] [2002] No such file or directory`，这是由于 PHP 无法正确找到 MySQL 的 scok 文件而无法连接数据库。

> 对于非编译安装的 PHP 和 MySQL，一般无需配置`pdo_mysql.default_socket`，它们会自动找到相关服务。



