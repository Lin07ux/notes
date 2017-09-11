### 错误日志
可以使用下面这个语句来查看 MySQL 的错误日志路径：

```sql
show variables like 'log_error';
```

![error](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472474884018.png)

### 慢查询日志
所谓的慢查询日志就是用来记录在MySQL中运行速度缓慢的执行语句，所以说这个文件很方便整体的性能调优。

什么样的语句才能称为慢的语句呢，所以这里就需要有一个阀值来定义，一旦运行时间超过了这个值就会被记录到这个慢查询日志中。这个值可以通过`long_query_time`来设置，默认值为 10，意思是运行 10S 以上的语句。

```sql
show variables like 'long_query_time';
```

![slow query](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472475055116.png)

默认情况下，MySQL 数据库并不启动慢查询日志，需要我们手动来设置这个参数，当然，如果不是调优需要的话，一般不建议启动该参数，因为存在一定的性能影响。

```sql
show variables like 'slow_query%';
set global slow_query_log = ON;
```

![slow log](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472475475453.png)

同样的，还可以设置慢查询的日志路径和慢查询的时间界限。

### 未使用索引的慢查询
在 MySQL 的慢查询日志当中，为我们有贴心的添加了一个参数，用来记录没有使用索引的语句：

```sql
show variables like 'log_queries_not_using_indexes';
```

![slow not using](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472476130444.png)


### 慢查询日志输出方式
默认情况下，日志都是用文件来记录的，我们也可以将其改成使用数据库表记录的方式。MySQL 提供了一张系统的表可以对慢查询日志进行查看，表名称是`sloq_log`。

```sql
show variables like 'log_output';
set global log_output = 'TABLE';
select sleep(11);
select sleep(11);
select * from mysql.slow_log;
```

![table](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472475916156.png)

默认是关闭的，我们可以将该参数打开，进行详细的记录：

```sql
SET global log_queries_not_using_indexes = 1;
```

通过此参数的设置，就可以跟踪MySQL中没有使用索引并且运行时间比较长的语句了。

