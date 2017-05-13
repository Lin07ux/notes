## 错误

在 Linux 中通过命令操作数据表的时候，总是提示表不存在，即便通过`show tables`来查看，确实是存在的，依旧会提示类似如下的错误：

```shell
mysql> show tables;
+-------------------------------------------+
| Tables_in_huan_db                         |
+-------------------------------------------+
| advertisement                             |
| message_queue                             |
| message_sys_user                          |
| message_user                              |
| opHistory                                 |
| opHistory_queue                           |
| opHistory_queue_result_log                |
| opHistory_queue_send_fail_log             |
+-------------------------------------------+
8 rows in set (0.00 sec)
 
mysql> desc opHistory;
ERROR 1146 (42S02): Table 'haun_db.opHistory' doesn't exist
mysql> desc opHistory_queue;
ERROR 1146 (42S02): Table 'haun_db.opHistory_queue' doesn't exist
mysql> desc opHistory_queue_result_log;
ERROR 1146 (42S02): Table 'haun_db.opHistory' doesn't exist
mysql> desc opHistory_queue_send_fail_log;
ERROR 1146 (42S02): Table 'haun_db.opHistory_queue_send_fail_log' doesn't exist
```

## 原因

在 MySQL 中，数据库对应文件系统中的目录。数据库中的每个表至少对应数据库目录中的一个文件(也可能是多个，取决于存储引擎)。因此，所使用操作系统的大小写敏感性决定了数据库名和表名的大小写敏感性。

因为 Linux 下 MySQL 默认是要区分表名大小写的，而 MySQL 是否区分大小写设置是由参数`lower_case_table_names`决定的，可以有 0、1、2 三种值：

> 关于`lower_case_table_names`参数的官方描述如下：
> 
> If set to 0, table names are stored as specified and comparisons are case sensitive. If set to 1, table names are stored in lowercase on disk and comparisons are not case sensitive. If set to 2, table names are stored as given but compared in lowercase. This option also applies to database names and table aliases. 
> 

* 0 表示：表在文件系统存储的时候，对应的文件名是按建表时指定的大小写存的，MySQL 内部对表名的比较也是区分大小写的；
* 1 表示：表在文件系统存储的时候，对应的文件名都小写的，MySQL 内部对表名的比较是转成小写的，即不区分大小写；
* 2 表示：表在文件系统存储的时候，对应的文件名是按建表时指定的大小写存的，但是 MySQL 内部对表名的比较是转成小写的，即不区分大小写。 
而之所以会出现找不到表的错误，就是因为在创建表的时候，和查询表的时候，`lower_case_table_names`的值发生了变化，此时就会导致表名因为是否区分大小写而不同。

## 解决

1、先在`my.cnf`里将`lower_case_table_names`参数再次调整为创建表时的值；
2、然后执行`mysqladmin -uroot -p shutdown`以安全模式关闭数据库；
3、最后再启动 MySQL 即可！

如果有需要，还可以重新命名表名，以使其不区分大小写。

## 补充

MySQL 在 Linux 下数据库名、表名、列名、别名大小写规则是这样的：

1）数据库名与表名是严格区分大小写的；

2）表的别名是严格区分大小写的；

3）列名与列的别名在所有的情况下均是忽略大小写的；

4）变量名也是严格区分大小写的；

5）MySQL 在 Windows 下都不区分大小写，但是在 Linux 下默认是区分大小写的。

6）如果想在查询时区分字段值的大小写，则字段值需要设置 BINARY 属性，设置的方法有多种：


a. 创建时设置：`CREATE TABLE T(A VARCHAR(10) BINARY);`。
b. 使用`alter`修改。

## 参考

* [MySQL 表名忽略大小写问题记录](http://blog.jobbole.com/111418/)
* [MySQL · 答疑释惑· lower_case_table_names 使用问题](https://yq.aliyun.com/articles/50846)

