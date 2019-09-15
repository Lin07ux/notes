> 转摘：[MySQL主从复制能完美解决数据库单点问题吗？](https://mp.weixin.qq.com/s/GoSq5Sh6uXpK8n9qBE606g)

## 一、MySQL 主从复制架构

MySQL 内建了主从库配置，可以很方便的实现主从集群。在进行主从配置的时候，建议主从数据库服务器采用相同的 MySQL 版本，并使用主库的全量备份来初始化从库。这样可以避免数据同步失败的问题。如果无法做到主从库的 MySQL 版本一致，应该要保证从库的版本比主库的版本更高。

MySQL 主从复制主要通过 bin log 和 relay log 来实现，主要有如下几个流程：

### 1.1 主库将变更写入到主库的 bin log 中

首先就是需要开启主库的 binlog 配置，将所有的表结构变更和数据变更写入到 binlog 中。

一些 MySQL 版本并不会默认开启二进制日志，所以一定要检查是否开启。如果刚开始没有开启，后面再进行开启的话，需要重启数据库才能生效，而且数据库的重启往往会对业务造成很大的影响。

尽管二进制日志对性能有稍许的影响，但还是建议大家无论是否使用复制功能，都要开启 MySQL 二进制日志，因为增量备份也需要二进制日志。

### 1.2 从库读取主库的 bin log 到本地的 relay log

为了实现在从库中复制主库的数据，从库需要将主库的 bin log 读取到本地，并放入到从库的中继日志 relay log 中。

> relay log 的格式和 binlog 格式是完全相同的，可以使用 mysqlbinlog 来读取 relay log 中的内容。

MySQL 从库会开启一个 IO 工作线程，用来完成二进制日志的读取传输。这个 IO 线程会跟主数据库建立一个普通的客户端连接，然后主库服务器上会启动一个特殊的二进制转储线程称为 binlogdown 线程。

从库上的 IO 线程通过这个二进制转储线程来读取主库上的二进制事件。如果该事件追赶上主库，则会进入 sleep 状态，直到主库发起信号通知有新事件产生时，才会被唤醒。

### 1.3 从库重放 relay log 实现数据复制

将主库的 bin log 读取到从库的 relay log 之后，从库就会重新执行这些日志中的 SQL 语句，从而在从库中重现主库的数据。

## 二、MySQL 主从配置步骤

目前 MySQL 支持两种复制类型：

* 基于二进制日志点的复制
* 基于 GTID 的复制（MySQL >= 5.7 推荐使用）

这两种复制方式对应的配置也有所区别，但整体的配置步骤是相同的。

有些参数配置后需要数据库重启才能生效，为了不影响数据库的正常使用，最好在服务器上线的同时就把参数都配置好。特别是 Master 服务器的参数，更应该作为服务器初始参数来进行配置。

### 2.1 服务器参数配置

Master 服务器主要就是配置开启 bin log，并为其配置一个唯一的服务 ID：

```my.conf
# 指定 mysql 的 binlog 的存放路径为 /data/mysql/sql_log，以及日志文件名前缀为 mysql-bin
# 如果设置值为 ON 而不指定存放路径，默认会存放到 mysql 的 data 目录下，也就是会把日志和数据文件存放在一起
# 指定路径分开存放可以提高 IO 性能，所以还是建议日志文件和数据文件分开存放
log_bin = /data/mysql/sql_log/mysql-bin

# mysql 的复制集群中通过 server_id 的值区分不同的服务器，建议使用服务器 ip 的后一段或后两段的值进行配置
# 比如 IP 为 192.168.3.100 就设置为 100 或 3100                                                     
server_id = 100
```

Slave 服务器除了配置 bin log 和服务 ID，还需配置 relay log 和其他相关参数：

```my.conf
log_bin = /data/mysql/sql_log/mysql-bin 
server_id = 101

# 指定 relay_log 日志的存放路径和文件前缀，不指定的话默认以主机名作为前缀
relay_log = /data/mysql/sql_log/relay-bin

# 使所有没有 server 权限的用户，在从服务器上不能执行写操作，不论这个用户是否拥有写权限
# mysql 5.7 可以使用 super_read_only = on ，限制 super 用户也不能在从服务器上执行写操作
read_only = on

# 在从服务器重启时，不会自动启动复制链路。
# 默认情况下从服务器重启后，会自动启动复制链路，如果这个时候存在问题，则主从链路会中断
# 所以正常情况下，应该在服务器重启后检查是否存在问题，然后再手动启动复制链路
skip_slave_start = on

# 下面两个参数是把主从复制信息存储到 innodb 表中
# 默认情况下主从复制信息是存储到文件系统中的，如果从服务器宕机，很容易出现文件记录和实际同步信息不同的情况，
# 存储到表中则可以通过 innodb 的崩溃恢复机制来保证数据记录的一致性
master_info_repository = TABLE
relay_log_info_repository = TABLE
```

MySQL 5.7 版本增加了 server-uuid 配置，默认情况下在`auto.conf`文件中。如果是使用的镜像的方式安装，可能大家的 server-uuid 一样，这样主从复制会出现问题，所以需要把`auto.cnf`文件删除掉。删掉后，MySQL 重启后会自动重新生成 uuid 的值，这样就可以保证不同服务器上的 MySQL 实例的 uuid 的值是不一样的。

### 2.2 复制账号创建

数据库中的用户角色一般建议按照使用分别创建。

对于主从复制来说，可以在 Master 服务器上专门创建一个复制用户用来同步数据，这需要为该用户授权`REPLICATION SLAVE`权限：

```sql
CREATE USER 'repl'@'ip段' identified by 'password';
GRANT REPLICATION SALVE ON *.* to 'repl'@'ip段';
```

### 2.3 用主库数据初始化从库

在主从库都配置好之后，先初始化主库，然后将主库的数据和表结构全量备份，并用该备份来初始化从库。

1. 全量备份主库数据
    
    ```sql
    mysqldump -uroot -p --master-data=1 --single-transaction --routines --triggers --events  --all-databases > all.sql
    ```

2. 拷贝备份 sql 文件到从库

    ```shell
    # 使用 scp 命令将主库的全量备份文件拷贝到从库中
    scp all.sql root@192.168.3.101:/root
    ```

3. 从库中进行初始化

    ```shell
    # 使用主库的全备份文件初始化从库
    mysql -uroot -p < all.sql
    ```

### 2.4 从服务器基于日志点的复制链路的配置

从服务器初始化完成之后，还需要根据复制类型的不同进行相应的配置。对于基于日志点的复制来说，登录从服务器之后，在从服务器上需要进行如下的操作：

```sql
-- 设置主服务器信息
CHANGE MASTER TO master_host='192.168.3.100', -- 主服务器的 IP
    master_user='dba_repl', -- 主服务器创建的用于复制的用户
    master_password='123456', -- 主服务器复制用户的密码
    MASTER_LOG_FILE='mysql-bin.000017', -- 从全备文件中的 CHANGE MASTER 中获取
    MASTER_LOG_POS=663; -- 从全备文件中的 CHANGE MASTER 中获取

-- 启动 slave
start slave

-- 检查是否启动成功状态
show slave status \G
```

如果启动状态信息显示类似如下就表示成功了：

```
Relay_Master_Log_File: mysql-bin.000017
Slave_IO_Running：Yes
Slave_SQL_Running: Yes
```


