> 转摘：[如何解决MySQL order by limit语句的分页数据重复问题？](https://mp.weixin.qq.com/s/vA6AhT1rYACJT2gZfs43GA)

### 1. 问题描述

在 MySQL 中通常会采用`limit`来进行翻页查询，但当`limit`遇到`order by`的时候，可能会出现翻到第二页的时候，竟然又出现了第一页的记录。

如，下面的 SQL 查询结果很有可能出现和`LIMIT 0,5`相同的某条记录：

```sql
SELECT `post_title`,`post_date` FROM post WHERE `post_status`='publish' ORDER BY view_count desc LIMIT 5,5;
```

而如果使用如下方式，选择全部的字段，则不会出现重复的情况：

```sql
SELECT * FROM post WHERE post_status='publish' ORDER BY view_count desc LIMIT 5,5;
```

如果只要特定的两个字段，可以在`ORDER BY`后面增加一个排序条件来避免重复数据，如：

```sql
SELECT `post_title`,`post_date` FROM post WHERE `post_status`='publish' ORDER BY view_count desc,ID asc LIMIT 5,5;
```

按理来说，MySQL 的排序默认情况下是以主键 ID 作为排序条件的。也就是说，如果在`view_count`相等的情况下，主键 ID 作为默认的排序条件，不需要多此一举增加加`id asc`。但是事实就是，MySQL 在`order by`和`limit`混用的时候，出现了排序的混乱情况。

### 2. 问题原因

这个问题的出现，是因为在 MySQL 5.6 的版本上，优化器在遇到`order by limit`语句的时候做了一个优化，即：使用了 **priority queue**。

使用 priority queue 的目的，就是在不能使用索引有序性的时候，如果要排序，并且使用了`limit n`，那么只需要在排序的过程中，保留 n 条记录即可。这样虽然不能解决所有记录都需要排序的开销，但是只需要`sort buffer`少量的内存就可以完成排序。

而之所以 MySQL 5.6 出现了第二页数据重复的问题，是因为 **priority queue 使用了堆排序的排序方法，而堆排序是一个不稳定的排序方法**。也就是相同的值可能排序出来的结果和读出来的数据顺序不一致。

MySQL 5.5 没有这个优化，所以也就不会出现这个问题。

MySQL 执行 SQL 语句的时候，执行顺序依次为`form… where… select… order by… limit…`，对于前面的 SQL 语句，在进行`order by`时，仅把`view_count`值大的往前移动。但由于`limit`的因素，排序过程中只需要保留到 5 条记录即可。`view_count`并不具备索引有序性，所以当第二页数据要展示时，MySQL 见到哪一条就拿哪一条。

因此，当排序值相同的时候，第一次排序是随意排的，第二次再执行该 sql 的时候，其结果应该和第一次结果一样。

### 3. 解决方法

一种方式就是像上面一样增加一个索引字段进行排序，还有一种方式是在排序字段上添加上索引，这样就可以直接按照索引的有序性进行读取并分页，从而可以规避遇到的这个问题。

### 4. 其他

**不加 order by 的时候的排序问题**

不添加`order by`语句的时候，MySQL 总是有序的，Oracle 却很混乱，这个主要是因为 Oracle 是堆表，MySQL 是索引聚簇表。

**分页重复的问题**

分页是在数据库提供的排序功能的基础上，衍生出来的应用需求，数据库并不保证分页的重复问题。

**NULL 值和空串问题**

不同的数据库对于 NULL 值和空串的理解和处理是不一样的。比如 Oracle NULL 和 NULL 值是无法比较的，既不是相等也不是不相等，是未知的。而对于空串，在插入的时候，MySQL 是一个字符串长度为 0 的空串，而 Oracle 则直接进行 NULL 值处理。


