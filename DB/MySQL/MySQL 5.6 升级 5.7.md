MySQL 升级主要分为四个步骤：备份数据、删除旧版本 MySQL、安装新版本 MySQL、配置和恢复数据。

### 一、备份数据

升级 MySQL 数据库虽然可以保留数据不被删除，但是为了避免误操作或其他因素影响，还是建议先备份数据。备份数据可以使用如下命令：

```shell
mysqldump -u root –p -E -A > ~/db-backup.sql
```

> 加`-E`是因为 mysqldump 默认并不处理 MySQL 的事件，需要自己指明是否导出事件。

由于升级数据库之后，之前的备份也有可能被重置，所还需要先备份之前的配置：

```shell
cp /etc/my.cnf ~/my.cnf
```

### 二、删除旧版本

删除 MySQL 之前，需要先停止 MySQL 服务，再进行卸载：

```shell
# 停止服务
systemctl stop mysqld

# 卸载
yum remove mysql-*
```

### 三、安装新版本

> 参考：[Installing MySQL on Linux Using the MySQL Yum Repository](https://dev.mysql.com/doc/refman/5.7/en/linux-installation-yum-repo.html)

安装新版本时，首先需要更新 rpm 源。从[MySQL 官网](https://dev.mysql.com/downloads/repo/yum/)找到相应版本的 rpm 地址，然后在服务器上进行下载，如下：

```shell
wget https://dev.mysql.com/get/mysql80-community-release-el7-1.noarch.rpm
```

下载完成之后，即可安装该 rpm：

```shell
sudo yum localinstall mysql80-community-release-el7-1.noarch.rpm
```

上面安装的 rpm 默认启用的是 MySQL 8.0 版本，可以通过如下方式切换到 5.7 版本：

```shell
# 查看可用的版本
yum repolist all | grep mysql
# 禁用 8.0 版本
sudo yum-config-manager --disable mysql80-community
# 启用 5.7 版本
sudo yum-config-manager --enable mysql57-community
```

然后就可以安装 MySQL 5.7 了：

```shell
# 查看 mysql 的可用包
yum list | grep '^mysql'
# 安装
sudo yum install mysql-community-server
```

这样就会自动安装`mysql-community-server`、`mysql-community-client`、`mysql-community-common`、`mysql-community-libs`四个软件包。

### 四、设置和恢复数据

安装完成新版本之后，可以对其进行相关的配置。打开`/etc/my.cnf`文件，编辑相关内容：

```conf
[mysqld]
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock

symbolic-links=0

sql_mode=NO_ENGINE_SUBSTITUTION,STRICT_TRANS_TABLES

character-set-server=utf8mb4
collation_server=utf8mb4_unicode_ci
init-connect="SET NAMES utf8mb4"
max_connections = 800

log-error=/var/log/mysqld.log
pid-file=/var/run/mysqld/mysqld.pid

[mysql]
default-character-set=utf8mb4

[client]
socket = /var/lib/mysql/mysql.sock
```

如果之前的数据就存放在 MySQL 的`datadir`路径中，那么直接启动 MySQL 时之前的数据就还会存在的。如果不存在，则可以用前面备份的 sql 文件来进行恢复。

首先，登陆进入 MySQL：

```shell
mysql -uroot -p
```

然后执行 sql 语句：

```mysql
source ~/db-backup.sql
```

操作完成后，可以通过如下方式将 MySQL 服务加入自动启动目录中：

```shell
systemctl enable mysqld
```

