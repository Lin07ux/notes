### 1. 将特定的某个值排在最前面

有如下需求：

> 将获取到的结果中，某一列为特定的值时，将该条数据排在前面。

此时可以通过在`order by`子句中，使用等值判断来实现，如：

```sql
SELECT * FROM users ORDER BY type = 2 DESC, id ASC LIMIT 100;
```

这里，`order by`子句中的`type = 2`是一个等值判断，当某条记录的的`type`字段的值是 2 时，该等值判断的结果为 1，否则判断结果为 0。然后对这个等值判断的结果按照倒序排列，也就是对 1 和 0 进行倒序，自然 1 排在 0 前面。

这样就实现了某列为特定值的记录排在前面的需求。

> 有些数据库或者数据库中间件不支持该语法。

