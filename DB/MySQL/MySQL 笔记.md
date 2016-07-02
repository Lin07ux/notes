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

### ALTER 修改
```sql
# 修改字段类型
ALTER TABLE device_label MODIFY origin_model VARCHAR(32);
# 修改字段名称
ALTER TABLE device_label CHANGE origin_model device_model VARCHAR(16);
# 追加列
ALTER TABLE device_label ADD os_type VARCHAR(8) COLLATE utf8_bin NOT NULL COMMENT '操作系统' after id;
# 修改列之间的顺序
ALTER TABLE device_label MODIFY os_type VARCHAR(8) AFTER origin_model;
# 修改 primary key
ALTER TABLE device_label DROP PRIMARY KEY, ADD PRIMARY KEY (`origin_model`,`origin_vendor`);
```

### TRUNCATE 清空
TRUNCATE 为清空表，相当于`delete from`不指定`where`条件。

```sql
TRUNCATE device_label；
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

### REVOKE 撤销授权
使用 GRANT 授权后，还可以使用 REVOKE 来取消授权。

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

