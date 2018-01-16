### 一般设置

1. 修改`/etc/my.conf`文件，在`[mysqld]`区块中，增加`max_connections = 1000`设置，其值为你需要的值。

2. 重启 mysqld 服务：`systemctl restart mysqld.service`。

3. 登录 mysql 后查看最大连接数：`show variables like '%max_connections%'`。如果显示的结果正常则说明已经设置成功，不需后续处理了。如果显示的值和设置的不同，则需继续更改配置。

### 无法增加最大连接数

如果经过上述的设置之后，依旧无法改变`max_connections`，则说明需要修改系统相关的配置。常见的现象就是，`max_connections`总是 214，无法增加。

首先需要明确的是，MySQL 的最大连接数是受限于操作系统的，必要时可以增大 [open-files-limit](http://dev.mysql.com/doc/refman/5.7/en/server-options.html?spm=5176.100239.blogcont47259.7.2484aa680DVi5r#option_mysqld_open-files-limit)。换言之，连接数与文件打开数有关。

那么，我们就可以通过如下的方式来进行调整：

1. 调整系统文件打开数量，在`/etc/security/limits.conf`文件中，检查是否有如下的两行设置，如果没有则添加：
    
    ```conf
    mysql hard nofile 65535
    mysql soft nofile 65535
    ```

2. 调整 mysqld 服务的文件打开限制，在`/usr/lib/systemd/system/mysqld.service`中，检查是否有如下的两行设置，如果没有则添加：
    
    ```conf
    LimitNOFILE=65535
    LimitNPROC=65535
    ```

3. 重启相关服务：
    
    ```shell
    systemctl daemon-reload
    systemctl restart  mysqld.service
    ```

这样操作之后，基本就正常了。

**参考**：

1. [RHEL\CentOS 7 下 MySQL 连接数被限制为214个](https://yq.aliyun.com/articles/47259)
2. [Mysql的配置max_connections不生效的问题](http://blog.csdn.net/junqing124/article/details/50669063)


