MySQL 中查询语句一般都是使用`SELECT`语句，基本格式如下：

```sql
SELECT [DISTINCT] 属性列表
FROM 表名和视图列表
[WHERE 条件表达式]
[GROUP BY 属性名 [HAVING 条件表达式]]
[ORDER BY 属性名 [ASC|DESC]]
[LIMIT <OFFSET>, COUNTS]
```

各个子句解释如下：

* `distinct`子句：限定结果只能是唯一的。
* `where`子句：按照“条件表达式”指定的条件进行查询。
* `group by`子句：按照“属性名”指定的字段进行分组。
* `having`子句：只有满足“条件表达式”中指定的条件的才能够输出。有`group by`才能有 having 子句。
* `group by`子句通常和count()、sum()等聚合函数一起使用。
* `order by`子句：按照“属性名”指定的字段进行排序。排序方式由“asc”和“desc”两个参数指出，默认是按照“asc”来排序，即升序。

> 注意：MySQL 中 SQL 语句是不区分大小写的，因此`select`和`SELECT`作用是相同的。

### DISTINCT
DISTINCT 关键字用来查询出某个字段不重复的记录，如果同时指定了多个字段，那么会按照这多个字段的值同时不重复的条件来过滤，而这一般并不是我们需要的效果，往往只用它来返回不重复记录的条数，而不是用它来返回不重记录的所有值。

比如，下面的语句会获取 fruits 表中 name 字段不重复的记录，是我们想要的结果。

```sql
SELECT DISTINCT name FROM fruits;
```

但是如果同时想要其他的字段，比如 id 字段的值，使用下面的语句并不能获取到我们想要的结果：按照 name 字段不重复过滤。

```sql
SELECT DISTINCT name, id FROM fruits;
```

上面的这个语句会按照(name, id)两个字段同时不重复来过滤。

如果要只根据 name 字段来过滤重复值，但是还要获取其他字段的值，可以使用`GROUP BY`子句来完成：

```sql
SELECT id, name FROM fruits GROUP BY name;
```


### WHERE
WHERE 子句用来设置查询的条件，一般是通过表中的某个字段的值来进行筛选。

#### 带 like 的字符匹配查询
* 百分号通配符`%`，匹配任意长度的字符，甚至包括零字符。

```sql
# 选择 name 字段的值是以 b 开头，以 y 结尾
SELECT id, name FROM fruits WHERE name LIKE 'b%y';
```
    
* 下划线通配符`_`,一次只能匹配任意一个字符。

```sql
# 选择 name 字段的值有五位字符，而且最后一位是 n
SELECT id, name FROM fruits WHERE name LIKE '____n';
```

#### 空值和非空值
如果某个字段是空值，需要使用`IS NULL`，如果不是空值，需要使用`IS NOT NULL`。

```sql
# 选择 customers 表中的 city 字段是空值的记录
SELECT * FROM customers WHERE city IS NULL;

# 选择 customers 表中的 city 字段不是空值的记录
SELECT * FROM customers WHERE city IS NOT NULL;
```

#### 逻辑条件
条件可以组合起来使用：`AND`表示“和”，`OR`表示“或”。而且条件可以使用`()`小括号来变更组合顺序。

```sql
SELECT id, name FROM fruits WHERE name LIKE '____n' AND id='bs2';
SELECT id, name FROM fruits WHERE name LIKE '____n' OR id='bs2';
```

#### 正则表达式
MySQL 中使用`REGEXP`关键字指定正则表达式的字符匹配模式。常用的正则表达式的功能在这里也都能使用：

* `^` 查询以特定字符或字符串开头的记录
* `$` 查询以特定字符或字符串结尾的记录
* `.` 匹配任意一个字符
* `*` 表示其前面的字符或分组重复 0 次、1 次或多次
* `+` 表示其前面的字符或分组至少重复 1 次
* `{n,}` 重复其前面的字符或分组至少 n 次
* `{n,m}` 重复其前的字符或分组至少 n 次，至多 m 次
* `|` 匹配多个字符串或分组
* `[字符集合]` 匹配指定字符集中的任意一个
* `[^字符集合]` 匹配指定字符以外的字符

```sql
# 查询字段 name 以 b 字符开头的记录
SELECT * FROM fruits WHERE name REGEXP '^b';

# 查询字段 name 以 y 字符结尾的记录
SELECT * FROM fruits WHERE name REGEXP 'y$';

# 查询字段 name 的值中含有 a、g 字符，且 a、g 之间还有其他任意一个字符的记录
SELECT * FROM fruits WHERE name REGEXP 'a.g';

# 查询 name 字段以 b 开头，且 b 后面重复 a 字符任意次的记录
SELECT * FROM fruits WHERE name REGEXP '^ba*';

# 查询 name 字段以 b 开头，且 b 后面重复 a 字符至少一次的记录
SELECT * FROM fruits WHERE name REGEXP '^ba+';

SELECT * FROM fruits WHERE name REGEXP 'on|ap';

SELECT * FROM fruits WHERE name REGEXP '[ot]';

SELECT * FROM fruits WHERE id REGEXP '[^a-e1-2]';

SELECT * FROM fruits WHERE name REGEXP 'b{1,}';

SELECT * FROM fruits WHERE name REGEXP 'ba{1,3}'
```


### GROUP BY
GROUP BY 子句是根据某个字段来过滤重复值。这个字段比 DISTINCT 更方便，而且速度上也略胜一筹。

```sql
SELECT id, COUNT(1) AS total FROM fruits GROUP BY id;
```

另外，在使用 GROUP BY 子句的时候，还可以使用`GROUP_CONCAT()`方法，来将那些重复记录的中的某个字段的值都合并起来，并使用`,`分割：

```sql
SELECT id, GROUP_CONCAT(name) AS NAMES FROM fruits GROUP BY id;
```

这样会根据 id 来过滤重复值，并将相同的 id 的记录中的 name 字段的值合并起来，每两个值之间使用英文逗号`,`分割。

在 GROUP BY 子句中，还可以使用`WITH ROLLUP`关键字，用来在分组的统计数据的基础上再进行相同的统计（SUM,AVG,COUNT…）：

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

### ORDER BY
ORDER BY 子句用来给选择的结果根据指定的字段进行排序，默认情况下使用升序排列。可以同时指定多个排序字段，并给每个排序字段使用不同的排序方式。

```sql
SELECT id, name FROM fruits ORDER BY id ASC, name DESC;
```

### LIMIT
LIMIT 子句用来限制返回的结果的数量。其格式如下：

`LIMIT [位置偏移量]，行数`

其中，位置偏移量可以省略，省略时表示偏移量为 0，也就是从选择到的结果中的第一条进行选择；行数用来限制返回的结果的数量，也即是返回的结果最多不能超过指定的行数，当然，可以少于指定的行数。

> 注意：位置偏移量是从 0 开始计数的，也即是，0 表示从第一条结果开始选择。

```sql
SELECT * FROM fruits LIMIT 4,3
```

上面的语句表示，从选择到的结果中的第 5 条开始，选择 3 条结果返回。

MySQL 5.6 之后，可以使用`LIMIT pos OFFSET rows`的方式来选择结果，和前面的方式的意义是相同的。


### 子查询
MySQL 的查询语句除了上面的基本操作之外，还可以使用子查询。子查询这个特性从 MYSQL4.1 开始引入。

子查询表示，外层的查询的条件和内层的查询的结果相关。子查询可以使用多个关键字来修饰查询条件。

#### ANY
ANY 关键字接在一个比较操作符的后面，表示若与子查询返回的任何值比较为 TRUE，则返回 TRUE。

下面的列子，返回 tbl2 表的所有 num2 列，然后将 tbl1 中的 num1 字段的值与之进行比较，只要大于 num2 的任何一个值，即为符合查询条件的结果：

```sql
SELECT num1 FROM tbl1 WHERE num1 > ANY(SELECT num2 FROM tbl2);
```

#### ALL
ALL 关键字接在一个比较操作符的后面，表示与子查询返回的所有值比较为 TRUE，则返回 TRUE。

```sql
SELECT num1 FROM tbl1 WHERE num1 > ALL(SELECT num2 FROM tbl2);
```

### 合并查询
使用 UNION 关键字，可以将两个或多个查询的结果合并起来返回。合并结果时，两个查询对应的列数和数据类型必须相同。

各个 SELECT 语句之间使用`UNION`或`UNION ALL`关键字分隔。

* `UNION`：执行的时候删除重复的记录，所有返回的行都是唯一的；
* `UNION ALL`：不删除重复行也不对结果进行自动排序。

```sql
SELECT id, name, price FROM fruits WHERE price < 9.0
UNION 
SELECT id, name, price FROM fruits WHERE id IN (101,103);
```



