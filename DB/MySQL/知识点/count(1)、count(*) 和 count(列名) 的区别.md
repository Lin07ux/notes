> 转摘：[MySQL里的COUNT](https://www.cnblogs.com/cnJun/p/11404567.html)

MySQL 中，`count`用于对字段数量进行统计，字段值非空值，总数加 1，字段值为空时则不增加总数。

### 1. 效率

`count`的效率与进行统计的列是否有索引、索引长度、字段值可否为空有关系。一般来说，有如下的特性：

```
count(*) ≈ count(1) > count(主键) ≈> count(加了索引不可为空字段) > count(加了索引可为空字段) > count(未加索引不可为空字段) > count(未加了索引可为空字段)
```

其中：

* `count(*)` InnoDB 遍历整张表，但不取值，因为`count(*)`肯定不为空，按行累加就行了。
* `count(1)` InnoDB 遍历整张表，但不取值，server 层对于每一行数据返回 1，判断 1 不可能空，按行累加。
* `count(id)` InnoDB 遍历整张表，把每一行的 id 取出来返回给 server 层，server 层判断不可能为空，按行累加。
* `count(不可为空字段)` InnoDB 遍历整张表，把每一行的这个字段取出来返回给 server 层，server 层判断不可能为空，按行累加。
* `count(可空字段)` InnoDB 遍历整张表，把每一行的这个字段取出来返回给 server 层，server 层判断是不是为空，不为空的按行累加。

由此可见，对于非空的字段进行统计可以少了该字段的值是否为空的判断，会比可为空的字段统计更快；而如果列上有索引，则可以直接通过索引树进行取值，自然会比无索引的列的统计更快。

另外，MySQL 会自动选择合适的索引进行统计，被选中的索引的数据和索引长度会尽量少。比如，表中如果除了自增主键索引 id 外，还有两个索引长度分别为 4 和 100 的字段 b 和 c，且都非空，那么在执行`count(*)`、`count(1)`、`count(id)`会自动选择字段 b 上的索引。而且`count(b)`会比`count(c)`更快一些。

### 2. count(判断 or null)

`count`计算的是除了 NULL 值外，其他数据都会加 1，例如 0 或 false 也都是会加 1。在某些情况下，就需要根据条件进行统计了。如果这个条件不能放在`where`部分，还可以将其放入到`count`中，使用如下方式进行统计：

```sql
SELECT SQL_NO_CACHE
    t.task_id AS taskId,
    count(t.task_child_id) AS taskChildNum,
    count(IF(t.STATUS = 4, true, NULL)) AS ongoingNum,
    count(IF(t.STATUS = 2, true, NULL)) AS archiveNum
FROM
    app_task_child t
GROUP BY
    t.task_id
```

如果要简单些，则可以使用如下方式：

```sql
SELECT
    t.task_id AS taskId,
    count(t.task_child_id) AS taskChildNum,
    count(t.STATUS = 4 or NULL) AS ongoingNum,
    count(t.STATUS = 2 or NULL) AS archiveNum
FROM
    app_task_child t
GROUP BY
    t.task_id
```

这里`t.STATUS = 2 or NULL`表示当`t.STATUS`列的值为 2 时，返回 1，否则返回 NULL。也就是说字段值为 2 的是参与统计，否则不会统计。

由于使用了表达式，所以即便为 status 列添加了索引也不会有什么效果。

