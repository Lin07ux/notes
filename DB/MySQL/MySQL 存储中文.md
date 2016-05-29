MySQL 默认情况下，无法存储中文字符，其实这和 MySQL 默认无法存储表情字符是同样的原因：数据库字符编码问题。

默认情况下，MySQL 使用的字符集是 Latin，所以无法存储中文或者其他的一些字符。

如果要存储中文，可以设置 MySQL 的字符集为支持中文的字符集，比如 GBK 或者 UTF-8。一般情况下，我们设置为 UTF-8，能有更好的兼容性。


1. 修改 MySQL 配置
修改数据库配置文件`/etc/my.cnf`，添加下面的配置，然后重启服务器：

```conf
[mysqld]
character-set-server=utf8 

[mysql]
default-character-set=utf8
```

2. 重启 mysqld 服务
为了使刚才的修改生效，需要重启数据库服务。

3. 修改数据库中已有表格的字符集
如果数据库中已经建有数据表了，那么还需要将这些数据表的字符集做更改。

> 如果数据表有很多，建议导出数据库和数据，然后重新建库。

```sql
ALTER TABLE tbl_name CONVERT TO CHARACTER SET utf8
```

这样处理之后，就能存储中午字符了(包括其他的 utf8 字符)。


