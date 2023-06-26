### 将一个表里的数据填充在另一个表里 

```sql
INSERT INTO table_a (filed1,filed2...) SELECT f1,f2,... FROM table_b
```

使用这种方式迁移数据的时候，一定要确保 table_b 的后面的 where、order 或者其他条件，都有对应的索引，来避免 table_b 全部记录被锁定的情况。（参考：[一条 SQL 引发的事故，同事直接被开除！！](https://mp.weixin.qq.com/s/9UF6e2hQCRVo-b1Za9dMow)）

### 查询一个表中没有存在在另一个表的数据

```sql
SELECT * FROM A WHERE id NOT IN ( SELECT id FROM B);
 
# 或者
SELECT * FROM A WHERE NOT EXISTS ( SELECT 1 FROM B WHERE B.id = A.id );
 
# 或者
SELECT A.* FROM A LEFT JOIN B ON (A.id = B.id) WHERE B.id IS NULL
```

### 找出某个字段有重复记录的记录

列出 username 字段有重复的数据：

```sql
SELECT username, count(username) as count FROM test GROUP BY username HAVING count(username) >1 ORDER BY count DESC;

-- 或者下面这种方法，速度更快
SELECT username, count(username) as count FROM test WHERE username in (SELECT username FROM test GROUP BY username HAVING count(username) > 1);
```

### 更新表的某个字段为另一个表的某个字段的值

```sql
UPDATE tb_1 AS f LEFT JOIN tb_2 AS l ON l.user = f.id SET f.name = l.name WHERE f.age > 18;
```

### IN 查询操作的结果按 IN 集合顺序显示

使用`IN`来查询的时候，查询结果默认还是按照主键升序排列的。如果要按照`IN`中集合的顺序来排列结果，可以结合`FIND_IN_SET`或`SUBSTRING_INDEX`函数来实现：

```mysql
SELECT * FROM test WHERE id IN(3,1,5) ORDER BY FIND_IN_SET(id,'3,1,5'); 
SELECT * FROM test WHERE id IN(3,1,5) ORDER BY SUBSTRING_INDEX('3,1,5',id,1);
```

参考：[MySQL查询in操作 查询结果按in集合顺序显示](http://www.jb51.net/article/25639.htm)

### 查看库、表、索引大小

在 MySQL 的`information_schema`库中的`TABLES`表中，存储了每个库、每个表和相关索引的大小、行数等信息。该表的主要字段如下：

* `TABLE_SCHEMA` 数据库名
* `TABLE_NAME` 表名
* `ENGINE` 存储引擎
* `TABLES_ROWS` 记录数
* `DATA_LENGTH` 数据大小
* `INDEX_LENGTH` 索引大小

> 一个表占用的空间总大小为：**数据大小** + **索引大小**。

```sql
-- 查看所有库的大小
select concat(round(sum(DATA_LENGTH/1024/1024), 2), 'MB') as data  from TABLES;

-- 查看指定库大小
select concat(round(sum(DATA_LENGTH/1024/1024),2),'MB') as data  from TABLES where table_schema='jishi';

-- 查看指定库指定表大小
select concat(round(DATA_LENGTH/1024/1024), 2),'MB') as data  from TABLES where table_schema='jishi' and table_name='a_ya';

-- 查看指定库的索引大小
SELECT CONCAT(ROUND(SUM(index_length)/(1024*1024), 2), ' MB') AS 'Total Index Size' FROM TABLES  WHERE table_schema = 'jishi';

-- 查看指定库指定表的索引大小
SELECT CONCAT(ROUND(SUM(index_length)/(1024*1024), 2), ' MB') AS 'Total Index Size' FROM TABLES  WHERE table_schema = 'test' and table_name='a_yuser';

-- 查看指定库的统计情况
SELECT CONCAT(table_schema,'.',table_name) AS 'Table Name', CONCAT(ROUND(table_rows/1000000,4),'M') AS 'Number of Rows', CONCAT(ROUND(data_length/(1024*1024*1024),4),'G') AS 'Data Size', CONCAT(ROUND(index_length/(1024*1024*1024),4),'G') AS 'Index Size', CONCAT(ROUND((data_length+index_length)/(1024*1024*1024),4),'G') AS'Total'FROM information_schema.TABLES WHERE table_schema LIKE 'test';
```