行行比较是 SQL92 规范中提出来的，MySQL 也已实现该功能。

行行比较常用于多组多个字段条件的查询，能够将`(... AND ...) OR (... AND ...) ...`方式的查询条件转换为 IN 查询条件。

例如，对于如下的表：

```sql
CREATE TABLE `sys_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `user_name` varchar(32) DEFAULT NULL COMMENT 'name',
  `account` varchar(200) NOT NULL COMMENT 'account',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='user';
```

对于如下的查询语句：

```sql
SELECT * FROM sys_user WHERE (id=1 AND user_name='u1') OR (id=2 AND user_name = 'u2';
```

改为行行比较查询方式：

```sql
SELECT * FROM sys_user WHERE (id, user_name) IN ((1, 'u1'), (2, 'u2'));
```