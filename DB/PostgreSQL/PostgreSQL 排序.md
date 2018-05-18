### null 排序规则

在数据库中 NULL 值是指 UNKNOWN 的值，不存储任何值，在排序时，它排在有值的行前面还是后面通过语法来指定：

```sql
nulls first  -- null 值排在最前
nulls last   -- null 值排在最后
```

默认情况下，NULL 会排在所有值前面。

示例：

```sql
-- 根据 id 列升序排列，null 排在所有行前
select * from tb order by id nulls first;
select * from tb order by id asc nulls first;

-- 根据 id 列升序排列，null 排在所有行前
select * from tb order by id nulls last;
select * from tb order by id desc nulls last;
```



