## 错误
```
SQLSTATE[HY000] [2019] Can't initialize character set utf8mb4
```

系统是 CentOS 6.7，MySQL 5.6.29。

虽然已经设置了 MySQL 的配置文件中字符集为`utf8mb4`，但是在链接数据库(PHP 中使用 PDO 链接)的时候还是会出现这个错误。

## 原因
这是由于 MySQL 自身的驱动问题导致的。

## 解决
执行如下的命令：

```shell
yum erase php56w-mysql
yum install php56w-mysqlnd
```

参考：[stack overflow](http://stackoverflow.com/questions/33834191/php-pdoexception-sqlstatehy000-2019-cant-initialize-character-set-utf8mb4)


