MySQL 的字符集有 4 个级别的默认设置：服务器级、数据库级、表级和字段级。分别在不同的地方设置，作用也不相同。

### 服务器级别
 服务器字符集设定，在 MySQL 服务启动的时候确定。可以在`my.cnf`中设置：

```cnf
[mysqld]
# 设置服务器字符集为 utf8mb4
character-set-server=utf8mb4
# 设置服务器排序规则为 utf8mb4_unicode_ci
collation_server=utf8mb4_unicode_ci
# 设置连接服务器时默认设置字符集为 utf8mb4，避免其他链接时的默认字符集设置
init-connect="SET NAMES utf8mb4"

[mysql]
# 设置客户端的字符集为 utf8mb4
default-character-set=utf8mb4
```  如果没有特别的指定服务器字符集，默认使用 latin1(ISO-8859-1 的别名)作为服务器字符集。

上面三种设置的方式都只指定了字符集，没有去做校对，我们可以用`show variables like 'char%';`命令查询当前服务器的字符集和校对规则。

![查看服务器字符集](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1509676786650.png)

### 数据库级别

在创建数据库的时候，可以指定字符集和排序方式：

```sql
CREATE DATABASE my_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

创建之后可以通过`SHOW CREATE DATABASE my_db;`命令来查看数据库的字符集：

![查看数据库字符集](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1509677010701.png)

修改数据库的字符集则可以使用如下的方式：

```sql
ALTER DATABASE db_name DEFAULT CHARACTER SET character_name [COLLATE ...];
```

### 表级别

在创建数据表的时候，可以指定字符集和排序方式：

```sql
CREATE TABLE my_table DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

创建之后可以通过`SHOW CREATE TABLE my_table;`命令来查看数据库的字符集：

![查看表字符集](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1509677431226.png)

修改数据库的字符集则可以使用如下的方式：

```sql
ALTER TABLE table_name DEFAULT CHARACTER SET character_name [COLLATE ...];
```

### 字段级别

数据表中的字段默认会继承表的字符集。可以通过`SHOW FULL COLUMNS FROM tbl_name;`命令来查看数据库的字符集。

修改字段的字符集可以使用如下的方式：

```sql
ALTER TABLE table_name CHANGE column_name column_name CHARACTER SET character_name [COLLATE ...];
```

### 注意

1. 修改已有数据的编码

    上面修改任何级别的字符集的方式都无法更改已有数据的字符集，如果需要修改已有数据的字符集，需要先将数据导出，经过适当的调整后重新导入才可以完全修改编码。

2. 新建的数据表的字符集和服务器设置的不同

    一般是由于数据库的字符集和服务器字符集不同导致的。上述四个级别的字符集的设置的优先级是依次递增的，所以数据库的设置会高于服务器级别的设置。

