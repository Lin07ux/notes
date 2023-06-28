### 1. 简介

`OPTIMIZE TABLE`命令用来整理数据表中数据和索引的物理存储空间，以减少磁盘占用，提升访问表格时的 IO 效率。其具体行为由数据表的存储引擎类型决定，支持该操作的存储引擎有：InnoDb、MyISAM、Archive。

使用场景如下：

* 在配置为`innodb_file_per_table`选择的 InnoDB 表中进行了大量的增删改操作后，可使用该命令来优化该表的数据文件和索引文件；
* 对 InnoDB 表中作为 FULLTEXT 索引的列进行了大量的增删改操作后，可以使用该命令来优化索引，提升效率。在优化前，可以配置`innodb_ft_num_word_optimize`选项来指定每次优化的词数，避免优化时影响索引的使用，并通过多次执行该命令以完成索引的全部优化；
* 在 MyISAM/Archive 表中做了大量的数据删除或者变长数据(含有 VARCHAR/VARBINARY/BLOB/TEXT 列)的修改操作后，可以使用该命令来释放不需要的空间，因为删除后这些空间并没有被立即释放，而是会被后续 INSERT 重用。

### 2. 语法

```sql
OPTIMIZE [NO_WRITE_TO_BINLOG | LOCAL] TABLE tbl_name [, table_name] ...
```

OPTIMIZE TABLE 可以一次对一个或多个表进行空间优化，执行的时候需要对这些表有`SELECT`和`INSERT`权限。

默认情况下，MySQL 服务器会将 OPTIMIZE TABLE 命令写入到 binlog 中，这样其他的副本也会执行该命令。如果想要阻止生成 binlog，可以在执行该命令的时候加上`NO_WRITE_TO_BINLOG`或者它的简写`LOCAL`选项。

### 3. 输出

OPTIMIZE TABLE 命令执行完成后会返回包含如下字段的输出：

 Column   | Value       
----------|--------------
 Table    | 执行优化的表名
 Op       | 执行的操作，固定为`optimize`
 Msg_type | 消息类别：`status`、`error`、`info`、`note`、`warning`
 Msg_text | 消息内容

命令执行过程中，拷贝数据过程中发生的任何异常都会被抛出，比如`.MYD`或`.MYI`文件的所有者 ID 和 mysqld 进程的用户 ID 不同，那么 OPTIMIZE TABLE 命令就会产生`cannot change ownership of the file`错误，除非 mysqld 是被 root 用户启动的。

**InnoDb**

对于 InnoDB 表来说，`OPTIMIZE TABLE`命令等同于`ALTER TABLE ... FORCE`命令，会重建表的索引，并释放无用的空间。

OPTIMIZE TABLE 命令使用[online DDL](https://dev.mysql.com/doc/refman/8.0/en/innodb-online-ddl.html)方式来优化表空间，以减少对 DML 操作的影响。同时，对表数据和索引的重建是在当前空间中完成的(in place)，也就是说不会占用额外的磁盘空间。而且只在命令执行的准备阶段和提交阶段短暂的持有一个高级表锁。

在以下情况下，OPTIMIZE TABLE 会采用表拷贝的方式来重建表：

* 系统变量`old_alter_table`被启用；
* MySQL 服务器启动时使用了`--skip-new`选项；
* InnoDB 表中包含了 FULLTEXT 索引。

InnoDB 表执行 OPTIMIZE TABLE 的输出结果类似如下：

```sql
mysql> OPTIMIZE TABLE foo;
+----------+----------+----------+-------------------------------------------------------------------+
| Table    | Op       | Msg_type | Msg_text                                                          |
+----------+----------+----------+-------------------------------------------------------------------+
| test.foo | optimize | note     | Table does not support optimize, doing recreate + analyze instead |
| test.foo | optimize | status   | OK                                                                |
+----------+----------+----------+-------------------------------------------------------------------+
```

**MyISAM**

对于 MyISAM 表，OPTIMIZE TABLE 的执行步骤如下：

* 如果表中有删除或者分裂的行，则修改该表；
* 如果索引页不是有序的，则对其进行排序；
* 如果表的统计数据不是最新的，则更新数据。