## 一、简介

`DISTINCT`关键字用来查询出某个字段不重复的记录。一般用于查询某个或某几个字段不完全相同的记录。

比如，下面的语句会获取 fruits 表中 name 字段不重复的记录：

```sql
SELECT DISTINCT name FROM fruits;
```

## 二、使用

### 2.1 多个字段

如果同时指定了多个字段，那么会按照这多个字段的值同时不重复的条件来过滤，而这一般并不是想要的效果，往往只用它来返回不重复记录的条数，而不是用它来返回不重记录的所有值。

但是如果同时想要其他的字段，比如 id 字段的值，使用下面的语句并不能获取到按照 name 字段过滤重复值后的结果：

```sql
SELECT DISTINCT name, id FROM fruits;
```

上面的这个语句会按照(name, id)两个字段同时不重复来过滤。

如果要只根据 name 字段来过滤重复值，但是还要获取其他字段的值，可以使用`GROUP BY`子句来完成：

```sql
SELECT id, name FROM fruits GROUP BY name;
```



