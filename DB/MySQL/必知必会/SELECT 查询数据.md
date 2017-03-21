## 简介

SELECT 数据查询是 SQL 中最常用的命令。通过该命令可以从数据库中获取到你想要的数据。

从 MySQL 检索数据至少需要提供两个信息：

1.	你需要查找什么
2.	你需要从哪个表中查询

也就是说，最基本的查询语句是由`SELECT`和`FROM`关键字组成。比如，最基本的查询语句就是如下：

```mysql
SELECT name FROM my_user;
```

上面语句指明了从 my_user 表查询了用户的名字 name 字段。

> 其实最简单的查询语句可以不包含`FROM`关键字，比如`SELECT now();`，就可以用于获取到当前的时间。

## 使用

### 多列检索

多列检索类似于上面一个例子，只不过查询的字段变成了多个。多个字段之间使用英文逗号分隔：

```mysql
SELECT name, age, code FROM my_user;
```

> 只能是字段之间使用逗号分隔，最后一个字段（这里是 code）之后不能有逗号，否则 MySQL 会报错。

### 检索全部字段

除了一个个的写出需要检索的字段外，MySQL 还提供了一个星号`*`代表检索所有字段：

```mysql
SELECT * FROM my_user;
```

这种方法十分简单，而且可以检索出数据库中你可能不知道存在的字段，在某些情况下十分有用。、

但是，开发中建议不要使用这种方式检索。原因就是会导致数据库检索性能下降。因为检索出来的所有数据都会放在内存。所以当你只需要比如文章标题时候，也会检索出文章内容，内存就会被文章内容严重消耗。

建议：**需要什么字段就检索什么字段，少用星号检索全部字段**。

### 去除重复记录

MySQL 提供了一个去除重复记录的关键字`DISTINCT`，只要将它加在检索的字段前面，即可去除重复记录。

```mysql
SELECT DISTINCT age FROM my_user;
```

但是这种方法只能用在一个字段时候，如果我们添加一个查询字段`name`那么，依然会显示所有的结果：

```mysql
SELECT DISTINCT age, `name` FROM my_user;
```

这样并不会仅仅显示 age 字段不同的记录。结果可能会如下所示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494921174663.png)

### 限制记录数量

数据库是可以存放千万条数据的地方，但是我们每次检索不可能将这么多数据。这时就需要`LIMIT`关键字作检索记录条数限制。

```mysql
SELECT age, `name` FROM my_user LIMIT 2;
-- 等同于
SELECT age, `name` FROM my_user LIMIT 0, 2;
```

1. `LIMIT`后面的第一个数字为从第几条开始检索。第一条为 0。
2. `LIMIT`后面第二个数字为本次检索记录的条数。
3. 当从首行记录开始检索时候，可以省略第一个数字，只写一个记录数。

### 数据排序

数据排序可以将选择的结果按照指定的列进行排序，从而更好的显示结果。排序可以根据单列、多列来进行。如下所示：

```mysql
-- 单列排序
SELECT id, age, `name` FROM my_user ORDER BY age DESC;
-- 多列排序
SELECT id, age, `name` FROM my_user ORDER BY age ASC, id DESC;
```

需要注意的是：

* 正序排列，则使用`ASC`，降序排列则使用`DESC`。不过`ASC`可以省略。
* 多列排序的时候先根据第一列进行排序，如果第一列的值相同，则根据后面的列排序。
* 如果在需要使用`LIMIT`的语句中也使用了`ORDER BY`关键字，那么`LIMIT`应该放在`ORDER BY`之后，否则数据库会报错。

### 条件过滤

数据库一般都包含大量数据，但是往往我们都不需要一次检索出全部，也不会说只用`LIMIT`来限制。我们通常会有针对性的筛选需要的数据，这时候就需要`WHERE`筛选数据，给出的搜索条件也被称为过滤条件。

```mysql
SELECT * FROM my_user WHERE age = 25;
```

注意：如果在 SQL 语句中需要使用`WHERE`和`ORDER BY`，请将`ORDER BY`放到`WHERE`之后，否则会报错。


