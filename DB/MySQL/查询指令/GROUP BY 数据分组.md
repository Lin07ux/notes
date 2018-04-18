## 一、简介

`GROUP BY`子句能够将查询到的数据进行分组，再进行处理。一般用于数据的统计、过滤中。

## 二、使用

### 2.1 过滤重复值

GROUP BY 子句可以根据某个字段来过滤重复值。这个字段比 DISTINCT 更方便，而且速度上也略胜一筹：

```sql
SELECT id, COUNT(1) AS total FROM fruits GROUP BY id;
```

### 2.2 合并重复记录的某个字段的全部值

在使用 GROUP BY 子句的时候，还可以使用`GROUP_CONCAT()`方法，来将那些重复记录的中的某个字段的值都合并起来，并使用`,`分割：

```sql
SELECT id, GROUP_CONCAT(name) AS NAMES FROM fruits GROUP BY id;
```

这样会根据 id 来过滤重复值，并将相同的 id 的记录中的 name 字段的值合并起来，每两个值之间使用英文逗号`,`分割。

### 2.3 分组后统计总数

还可以使用`WITH ROLLUP`关键字，用来在分组的统计数据的基础上再进行相同的统计（SUM,AVG,COUNT…）：

```sql
SELECT id, COUNT(1) AS total FROM fruits GROUP BY id WITH ROLLUP;
```

上面的这个语句会在分组统计的基础上，对结果中的 total 字段也使用一次 COUNT 方法，来计算总的数目。所以，结果可能会类似如下：

```
id     total
1001   1
1002   3
1003   2
NULL   6
```

其中，最后一行 id 为 NULL 的记录中，total 为 6，就是对前面的结果中的 total 进行统计之后得出的。

> 注意：当使用`WITH ROLLUP`时，不能同时使用`ORDER BY`子句进行结果排序，即`WITH ROLLUP`和`ORDER BY`是互相排斥的！



