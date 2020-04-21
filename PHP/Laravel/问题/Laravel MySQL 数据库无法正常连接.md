Laravel 配置 MySQL 数据库之后，可能会出现一些连接问题，此时需要注意一些特别配置即可。

### SQLSTATE 1045

如果新创建了一个用户，但是在 Laravel 中配置了之后无法正常访问，提示类似如下的错误信息：

```
SQLSTATE[HY000] [1045] Access denied for user 'username'@'localhost' (using password: YES) (SQL: select count(*) as aggregate from `migrations`)'
```

首先确认在数据库中创建用户并授权之后，是否刷新了权限列表：

```sql
FLUSH PRIVILEGES;
```

如果刷新后依旧不行，可以参考[MySQL 错误 1045](https://github.com/Lin07ux/notes/blob/master/DB/MySQL/%E9%94%99%E8%AF%AF/MySQL%20%E9%94%99%E8%AF%AF%201045.md)来解决。

### SQLSTATE 2002

如果出现类似如下的错误：

```shell
SQLSTATE[HY000] [2002] No such file or directory (SQL: select * from `failed_jobs` where `id` = 1 limit 1)
```

表示连接了数据库服务，但是找不到对应的数据库或表。

此时，首先确认数据库和表是存在的。如果没问题，那么一般是由于配置了`DB_HOST=localhost`所致。这种情况一般出现在编译安装的 PHP 中。

MySQL 支持网络连接和 sock 连接，当配置`DB_HOST=localhost`时表示使用 sock 连接，此时需要 PHP 能找到正确的 MySQL sock 文件，或者能够手动配置好 MySQL 的 sock 文件路径。所以解决方法有三个：

1. 配置 php

    打开`php.ini`配置文件，为`pdo_mysql.default_socket`项配置值为 MySQL 的 sock 文件路径。

2. 配置 Laravel DB_SOCKET

    为 Laravel 项目的 DB 配置`DB_SOCKET`，值为 MySQL 的 sock 文件路径。

3. 配置 Laravel HOST

    将 Laravel 项目的 DB 的`DB_HOST`改为 IP 地址，比如`127.0.0.1`。这种解决方式需要确认所用的 MySQL 账户支持从所指定的 IP 地址进行连接，否则会出现 1045 拒绝访问的错误。

> 查看 MySQL 的 sock 文件路径可以通过查看 MySQL 的配置文件获得，也可以登录 MySQL 后执行如下的 SQL 语句获取：
> 
> ```sql
> show variables like '%sock%'
> ```

