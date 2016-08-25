## DDL
数据定义语言（Data Definition Lanuage, DDL）定义了数据库模式，包括`CREATE`、`ALTER`、`DROP`、`TRUNCATE`、`COMMENT`与`RENAME`语句。

### CREATE 创建
```sql
CREATE TABLE `device_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键-自增长ID',
  `origin_model` varchar(64) COLLATE utf8_bin NOT NULL COMMENT '收集机型',
  `origin_vendor` varchar(64) COLLATE utf8_bin NOT NULL COMMENT '收集厂商',
  `vendor` varchar(32) COLLATE utf8_bin NOT NULL COMMENT '标注厂商',
  `model` varchar(32) COLLATE utf8_bin NOT NULL COMMENT '标注品牌',
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_key` (`origin_model`,`origin_vendor`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='设备标注表';
```

还可以使用已存在的表结构来创建一个新的表，但是不包含原表的数据：

```sql
CREATE TABLE new_table LIKE old_table
```

### ALTER 修改
```sql
# 修改字段类型
ALTER TABLE device_label MODIFY origin_model VARCHAR(32);

# 修改字段名称
ALTER TABLE device_label CHANGE origin_model device_model VARCHAR(16);

# 追加列
ALTER TABLE device_label ADD os_type VARCHAR(8) COLLATE utf8_bin NOT NULL COMMENT '操作系统' after id;

# 删除列
ALTER TABLE device_label DROP COLUMN os_type;

# 修改列之间的顺序
ALTER TABLE device_label MODIFY os_type VARCHAR(8) AFTER origin_model;

# 修改 primary key
ALTER TABLE device_label DROP PRIMARY KEY, ADD PRIMARY KEY (`origin_model`,`origin_vendor`);

# 添加唯一性约束
ALTER TABLE device_label ADD UNIQUE(`origin_model`);

# 修改表引擎
ALTER TABLE device_label engine=innodb;
```

### TRUNCATE 清空
TRUNCATE 为清空表，相当于`delete from`不指定`where`条件。

```sql
TRUNCATE device_label；
```

### 其他
```sql
# 查看表结构，会列出表中每一列的详细信息
DESC table_name;
```


## DCL
数据控制语言（Data Control Language, DCL）用于用户权限的管理，包括`GRANT`与`REVOKE`。

### GRANT 授权
MySQL 有很精细的权限控制：

* 细致的权限分类
* DB -> 表 -> 列，权限的粗细粒度
* 对 host（可通配符匹配）控制

```sql
# 创建 hive 用户，并赋予从 localhost 上访问 db1 所有表的权限：
CREATE USER 'hive'@'localhost' IDENTIFIED BY 'myPass';
GRANT ALL ON db1.* TO 'hive'@'localhost';

# 可简写为
GRANT ALL ON db1.* TO 'hive'@'localhost' IDENTIFIED BY 'myPass';

# 也可以只赋予某个表的 select 权限
GRANT SELECT ON db2.invoice TO 'hive'@'localhost';
```

### 修改用户密码
MySQL 的用户信息都存在 mysql 这个数据中的，所以修改用户密码的话，就直接更新这个数据库中 user 表中的相应数据即可：

```sql
# 切换数据库
use mysql;

# 更改密码
UPDATE user SET password=PASSWORD("new password") WHERE user='username';
# 或
SET PASSWORD FOR username PASSWORD('new password');

# 刷新权限
FLUSH PRIVILEGES;
```

### REVOKE 撤销授权
使用 GRANT 授权后，还可以使用 REVOKE 来取消授权。

### 删除用户

```sql
use mysql;
delete from user where User="用户名" and Host="主机地址";
```

## DML
数据定义语言（Data manipulation language, DML）主要用于表达数据库的查询与更新，主要包括增删改查（`INSERT`，`UPDATE`，`DELETE`，`SELECT`）。

### INSERT INTO 增加
```sql
# 增加一行数据
INSERT INTO device_label (origin_model, origin_vendor, vendor, model) VALUES ('h9', 'bbk', '步步高', 'H9');

# 复制一个表到另外一个表
INSERT INTO device_label_copy (`origin_model`, `origin_vendor`, `vendor`, `model`) SELECT `origin_model`, `origin_vendor`, `vendor`, `model` FROM device_label;
```

此外，MySQL 支持以 load data 方式将结构化的纯文本入库：

```sql
LOAD DATA LOCAL INFILE 'dvc-label.csv' INTO TABLE device_label FIELDS TERMINATED BY ',' IGNORE 1 lines (origin_model, origin_vendor, vendor, model);
```

> 若出现`ERROR 1148 (42000)`错误，则用`mysql --local-infile -u user -ppasswd`命令进入 mysql。

### UPDATE 更新
```sql
UPDATE device_label SET origin_model = 't2', origin_vendor = 'xiami' WHERE vendor = '锤子';
```

### DELETE 删除
```sql
DELETE FROM device_label WHERE origin_vendor = 'alps';
```

### SELECT 查询
```sql
SELECT vendor, COUNT(distinct model) AS models FROM device_label GROUP BY vendor HAVING models > 10
```


## 数据库、表、字段字符集
```sql
# 列出 MYSQL 支持的所有字符集
SHOW CHARACTER SET;

# 当前 MYSQL 服务器字符集设置
SHOW VARIABLES LIKE `character_set_%`;

# 当前 MYSQL 服务器字符集校验设置
SHOW VARIABLES LIKE `collation_%`;

# 显示某数据库字符集设置(database_name 是已存在的数据库)
SHOW CREATE DATABASE database_name;
# 有类似如下的输出
# CREATE DATABASE `ze_serv` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */

# 显示某数据表字符集设置(table_name 是已存在的数据表)
SHOW CREATE TABLE table_name;

# 修改数据库字符集
ALTER DATABASE database_name DEFAULT CHARACTER SET 'utf8';

# 修改数据表字符集
ALTER TABLE table_name DEFAULT CHARACTER SET 'utf8';

# 建库时指定字符集
CREATE DATABASE database_name CHARACTER SET 'gbk' COLLATE 'gbk_chinese_ci';

# 建表时指定字符集
CREATE TABLE `mysqlcode` (
    `id` TINYINT( 255 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `content` VARCHAR( 255 ) NOT NULL
) TYPE = MYISAM CHARACTER SET gbk COLLATE gbk_chinese_ci;
```

需要注意的是，MySQL 设置数据库和数据表的字符集的时候，还需要在 my.cnf 中做相应的设置：

```cnf
[client]

default-character-set=utf8

[mysqld]

default-character-set=utf8
```

如果不加以上代码，那么即便 MYSQL 编译安装时指定的编码是 UTF8，那么在建库时其默认编码仍是 LATIN1，而由于字符集的继承性，库中的表也是 LATIN1 的了。


## 索引
### 查看索引
```sql
SHOW INDEX FROM table_name;
# 或者
SHOW KEYS FROOM table_name;
```

### 创建索引
使用`ALTER TABLE`语句来创建：

```sql
# 普通索引
ALTER TABLE table_name ADD INDEX index_name (column_list);

# 唯一索引
ALTER TABLE table_name ADD UNIQUE index_name (column_list);

# 主键索引
ALTER TABLE table_name ADD PRIMARY KEY (column_list);
```

> `column_list`指出对哪些列进行索引，多列时各列之间用逗号分隔。
> 索引名`index_name`可选，缺省时，MySQL 将根据第一个索引列赋一个名称。这个可以随意命名。
> 另外，`ALTER TABLE`允许在单个语句中更改多个表，因此可以同时创建多个索引。

另外，还可以使用`CREATE INDEX`语句对数据表添加索引，这种方式只能够增加*普通索引*和 *UNIQUE 索引*两种，而不能添加主键：

```sql
# 创建普通索引
CREATE INDEX index_name ON table_name (column_list);

# 创建唯一索引
CREATE UNIQUE INDEX index_name ON table_name (column_list);
```

> `table_name`、`index_name`和`column_list`具有与`ALTER TABLE`语句中相同的含义，索引名必须。

当然，我们还能在创建表的时候，就指定索引：

```sql
create table `table_name` (
	`id` int(11) NOT NULL AUTO_INCREMENT ,
	`title` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	`content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
	`time` int(10) NULL DEFAULT NULL ,
	# 主键索引
	PRIMARY KEY (`id`),
	# 普通索引
	INDEX index_name (title(length))
	# 唯一索引
	UNIQUE indexName (title(length))
);
```

> MySQL 中，无论 innodb 引擎还是 MYISAM 引擎的表中，只能有一个自增列，并且自增列一定是索引列，无论是二级索引还是主键索引。

### 删除索引
删除索引和删除其他东西一样，使用`DROP`关键字：

```sql
# 删除索引(普通索引或者唯一索引)
DROP INDEX index_name ON table_name;
# 或者使用 ALTER TABLE 语句
ALTER TABLE table_name DROP INDEX index_name;

# 删除主键
ALTER TABLE table_name DROP PRIMARY KEY;
```

## 外键
参考：[外键](http://www.phpddt.com/db/mysql-foreign-key.html)

语法：

```sql
[CONSTRAINT [symbol]] FOREIGN KEY
    [index_name] (index_col_name, ...)
    REFERENCES tbl_name (index_col_name,...)
    [ON DELETE reference_option]
    [ON UPDATE reference_option]
 
reference_option:
    RESTRICT | CASCADE | SET NULL | NO ACTION
```

查看表的主键外键信息：
`SELECT * FROM information_schema.KEY_COLUMN_USAGE a WHERE a.TABLE_NAME='tablename'`


## 全局设置
### 开启查询日志
默认情况下，mysql 没有开启普通的查询日志，只记录了错误日志。

在开发的时候，为了方便，一般需要即时查看程序运行时执行的语句查询，此时可以开启 mysql 的一般日志记录功能：

```sql
# 查看当前普通查询日志的开启状态
SHOW GLOBAL VARIABLES LIKE 'general_log%';
# 会有如下的输出
# +------------------+--------------------+
# | Variable_name    | Value              |
# +------------------+--------------------+
# | general_log      | OFF                |
# | general_log_file | /var/log/query.log |
# +------------------+--------------------+

# 设置开启
SET GLOBAL general_log='on';
# 还可以设置查询日志的文件路径，但要保证文件已经存在，而且mysql有权限写入
SET GLOBAL general_log_file='/var/log/mysql-query.log';
# 此时再查看是否开启，会有如下的输出，表示已经开启
# +------------------+--------------------------+
# | Variable_name    | Value                    |
# +------------------+--------------------------+
# | general_log      | ON                       |
# | general_log_file | /var/log/mysql-query.log |
# +------------------+--------------------------+
# 然后我们就能够在 /var/log/mysql-query.log 文件中看到每次执行的 sql 语句了
```

> 参考：[general_log](https://dev.mysql.com/doc/refman/5.7/en/query-log.html)

### ### 查看数据库文件位置
```sql
SHOW GLOBAL VARIABLES LIKE '%datadir%';
```


