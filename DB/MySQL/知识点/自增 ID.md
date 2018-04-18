**问**：如果有一张表，里面有个字段为 id 的自增主键，当已经向表里面插入了10条数据之后，删除了 id 为8，9，10的数据，再把 mysql 重启，之后再插入一条数据，那么这条数据的 id 值应该是多少，是 8，还是 11？

**答**：如果表的类型为 MyISAM，那么是 11。如果表的类型为 InnoDB，则 id 为 8。

这是因为两种类型的存储引擎所存储的最大 ID 记录的方式不同，MyISAM 表将最大的 ID 记录到了数据文件里，重启 mysql 自增主键的最大 ID 值也不会丢失；

而 InnoDB 则是把最大的 ID 值记录到了内存中，所以重启 mysql 或者对表进行了`OPTIMIZE`操作后，最大 ID 值将会丢失。

顺便说一下 MYSQL 获取当前表的自增值的四种方法：

1. `SELECT MAX(id) FROM <table_name>` 针对特定表；
2. `SELECT LAST_INSERT_ID()` 针对任何表；
3. `SELECT @@identity` 针对任何表；
4. `SHOW TABLE STATUS LIKE ‘<table_name>’`  如果针对特定表，建议使用这一种方法。得出的结果里边对应表名记录中有个 Auto_increment 字段，里边有下一个自增 ID 的数值就是当前该表的最大自增 ID。

> `@@identity`是表示的是最近一次向具有`identity`属性(即自增列)的表插入数据时对应的自增列的值，是系统定义的全局变量。(一般系统定义的全局变量都是以`@@`开头，用户自定义变量以`@`开头。)使用`@@identity`的前提是在进行`insert`操作后，执行`select @@identity`的时候连接没有关闭，否则得到的将是`NULL(0)`值。






