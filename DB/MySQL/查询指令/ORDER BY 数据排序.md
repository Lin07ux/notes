## 一、简介

`ORDER BY`子句用来给选择的结果根据指定的字段进行排序，默认情况下使用升序排列。

### 1.1 基本语法

`ORDER BY`可以指定一个排序字段，也可以同时指定多个排序字段，并给每个排序字段使用不同的排序方式。排序方式由`ASC`和`DESC`两个参数指出，默认是按照`ASC`来排序，即升序。

排序的基本语法示例如下：

```sql
SELECT id, name FROM fruits ORDER BY id ASC, name DESC;
```

### 1.2 无 order by 子句时的排序

当有 ORDER BY 子句的时候，会按照 ORDER BY 子句排序后返回结果。如果没有 ORDER BY 子句，MySQL 对 SELECT 语句的返回结果有潜规则：

* 对于 MyISAM 引擎来说，其返回顺序是其物理存储顺序；
* 对于 InnoDB 引擎来说，其返回顺序是按照主键排序的。

## 二、实现方式

> 转摘：[关于 order By 函数，你可以知道更多](https://mp.weixin.qq.com/s/jJt5jotnYGX-XlZD9d-gUw)

在 MySQL 中，order by 对结果的排序的实现方式有两种：利用索引排序、使用文件排序。使用索引排序性能相对较好，但维护索引也需要相应的代价。

### 2.1 文件排序

当使用`explain`命令查看 sql 语句的时候，如果最后的`Extra`列为`Using filesort`则说明使用了文件排序。

对于文件排序，MySQL 会为每个线程分配一块排序内存(sort buffer)，该排序内存的大小可以通过`sort_buffer_size`配置项来控制，默认值为 262144 字节。

在文件排序中，根据返回的字段的长度总和与`max_length_for_sort_data`配置项的大小关系，MySQL 提供了两种算法：

* **全字段排序**：如果返回的字段长度总和小于`max_length_for_sort_data`的值，会采用该算法，将所有的字段都放入到 sort buffer 中进行排序处理；
* **rowid 排序**：如果返回的字段长度总和大于`max_length_for_sort_data`的值，会采用该算法，只会将行记录的主键(如`id`)和排序字段放入到 sort buffer 中进行排序，然后再根据结果回到主键索引中查询所需数据。

可以看到，两种算法的区别在于：全字段排序方式在完成排序后不再回表查询，而 rowid 排序则还需要重新回表查询所需数据，造成磁盘读而降低效率。两相比较，自然是全字段排序方式有更高的效率了。这也从另一个方面说明在获取数据时应尽量只获取需要的数据，而非全部获取。

文件排序优先在 sort buffer 中完成，如果需要排序的数据量大于`sort_buffer_size`的值，那么就需要**借助磁盘临时文件来完成排序**，性能也就会有所下降。所以可以在内存允许的情况下，适当的增大`sort_buffer_size`的值来减少使用磁盘文件排序的几率。

### 2.2 索引排序

使用索引排序时，需要所创建的索引覆盖了`select`和`order by`的字段，这样就可以直接读取已排序好的索引的内容，而不需要再额外的进行排序了。

对于这条 SQL 语句：

```sql
select city, name, age from users where city = '江西' order by name limit 20;
```

可以创建一个`city`、`name`、`age`的联合索引，添加索引的 SQL 语句为：

```sql
alter table users add index city_name_age(city, name, age);
```

此时再用 Explain 查看前面的查询 SQL，就可以看到 Extra 的值为`Using index`了，说明该查询语句使用了索引排序。

此时这条语句的执行流程大概如下：

* 1、从索引`(city, name, age)`找到第一个满足`city = '江西'`条件的记录，取出其中的`city`、`name`和`age`这三个字段的值，作为结果集的一部分直接返回；
* 2、从索引`(city, name, age)`取下一个记录，同样取出这三个字段的值，作为结果集的一部分直接返回；
* 3、重复执行步骤 2，直到查到第 20 条记录，或者是不满足`city='江西'`条件时循环结束。

