修改 MySQL 数据存放路径需要修改 MySQL 的配置文件、修改新的路劲的属组、修改 sock 文件。

在执行如下操作前，建议先备份数据，避免数据丢失。然后再关闭 mysql 服务`systemctl stop mysqld`。

### 一、修改配置文件

首先修改 MySQL 的配置文件，默认情况下，配置文件为`/etc/my.cnf`：

```cnf
[mysqld]
# 修改数据存放路径
datadir=/mnt/mysql
# 修改 scok 文件路径
socket=/mnt/mysql/mysql.sock

[client]
# 修改 client 的 sock 路径
socket = /mnt/mysql/mysql.sock
```

### 二、新的数据路劲

首先将之前的数据文件全部拷贝到新的路径中：

```shell
cp -R /var/lib/mysql /mnt/
```

然后设置新的路径的属组为 mysql：

```shell
chown -R mysql:mysql /mnt/mysql
```

### 三、sock

经过上面的操作，基本上能够正常在服务器上进行登陆了，但是通过其他程序来连接 MySQL 还是会提示如下错误：

```
SQLSTATE[HY000] [2002] No such file or directory
```

这是由于 MySQL 连接用的 sock 文件的不存在造成的。

默认情况下，其他程序访问 MySQL 时使用的 sock 文件位于`/var/lib/mysql/mysql.sock`，那么我们就可以直接创建一个软连接到该位置即可：

```shell
sudo ln -s /mnt/mysql/mysql.sock /var/lib/mysql.sock
```

如果 PHP 访问数据库，依旧会出现该错误，则需要修改 PHP 的配置文件，设置如下两项：

```ini
pdo_mysql.default_socket=/path/to/mysql.sock
mysqli.default_socket=/path/to/mysql.sock
```

或者可以在链接数据库的时候，使用`217.0.0.1`替代`localhost`。

### 四、其他

配置完成之后，重启 MySQL，一般即可正常，但对数据库进行读写操作时，可能会出现如下的问题：

```
# 读写
ERROR 1146 (42S02): Table ** doesn't exist

# 创建表
ERROR 1005 (HY000): Can't create table 'runoob_tbl' (errno: 13)
```

解决办法就是，在新的目录中，删掉`ib_logfile*`文件，这样innoDB引擎的表就正常了。

然后再在 MySQL 中执行`REPAIR TABLE ***;`，执行完成后，MyISAM引擎的表也正常了。

### 五、参考

1. [mysql 5.7更改数据库的数据存储位置的解决方法](https://www.jb51.net/article/110313.htm)
2. [Centos 更改MySQL5.7数据库目录位置](https://blog.csdn.net/zyw_java/article/details/78512285)
2. [PHP连接MySQL的时候报错SQLSTATE[HY000] [2002] No such file or directory](https://blog.csdn.net/simplty/article/details/43350903)

