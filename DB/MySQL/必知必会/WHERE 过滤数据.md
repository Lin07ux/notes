## 简介

数据库中一般都存在大量的数据，如果直接进行选择并返回，那么会造成极大的资源浪费，而且也会给用户造成麻烦。这时候就可以通过`WHERE`来设置条件，从数据库端过滤掉不符合需求的数据。

在 MySQL，我们可以在`FROM`关键字之后给出`WHERE`关键字来筛选数据。简单的示例如下：

```mysql
SELECT * FROM my_user WHERE age = 25;
```

这条 SQL 我们检索出了所有 age 字段的值为 25 的记录。当然 MySQL 还可以执行更多的功能。

> **如果在 SQL 语句中需要使用`WHERE`和`ORDER BY`，请将`ORDER BY`放到`WHERE`之后，否则会报错。**

## 操作符

`WHERE`子句最重要的就是可以使用多种操作符来设定过滤条件。

操作符       | 说明
----------- | -------
=           | 等于
<>          | 不等于
!=          | 不等于
\>          | 大于
\>=         | 大于或等于(不小于)
<           | 小于
<=          | 小于或等于(不大于)
BETWEEN     | 指定的两个值之间
NOT BETWEEN | 不在指定的两个值之间
IN          | 指定的一系列值的集合中
NOT IN      | 不在指定的一系列值的集合中
LIKE        | 模糊查询

### BETWEEN

`BETWEEN`关键字后面需要跟上 2 个数值，表示一个范围(范围包含这两个边界值)，两个数值之间使用`AND`分割。

比如下面语句中的`BETWEEN 19 AND 22`就是 19、20、21、22 都可以。

```mysql
SELECT * FROM my_user WHERE age BETWEEN 19 AND 22;
```

### IN

`IN`关键字像一个函数，包含一个括号，在括号里面你可以写上使用逗号分割的数值，只要数值与括号内的任何一个值匹配，那么就符合条件。类似于多个`OR`连接的组合查询。

```mysql
SELECT * FROM my_user WHERE age IN(25,20);
-- 等同于
SELECT * FROM my_user WHERE age =20 OR age =25;
```

这个语句表示，查询 age 字段的值为 25 或者 20 的记录。

用 IN 的原因在于：

1. `IN`操作符的语法更加简洁，SQL 语句长度缩短，不容易出错。
2. 计算的优先级更加容易看出，不用考虑`OR`和`AND`优先级关系。
3. `IN`操作符执行速度快于`OR`。
4. `IN`还可以结合子查询使用，动态的生成查询值。

### NOT

`NOT`操作符就只有一个功能：否定后面跟的条件。一般会和`BETWEEN`或`IN`联合使用。

```mysql
SELECT * FROM my_user WHERE age NOT IN(20,25);
```

上面的语句表示查询 age 字段不为 20， 而且也不为 25 的记录。

### LIKE

> `LIKE`并不属于操作符，而属于谓词。

`LIKE`允许使用通配符进行过滤。MySQL 中通配符有两个：

* `%` 百分号通配符，表示包含一切字符出现任意的次数。
* `_` 下划线通配符，表示匹配任意字符出现一次。一般会当做占位符使用。

```mysql
-- 选择 name 字段以'小'开头的记录
SELECT * FROM my_user WHERE `name` LIKE '小%';
-- 选择 name 字段以'大'结束的记录
SELECT * FROM my_user WHERE `name` LIKE '%大';
-- 选择 name 字段中包含'空'字符的记录
SELECT * FROM my_user WHERE `name` LIKE '%空%';
-- 选择 name 字段以'小'开头，以'大'结尾的记录
SELECT * FROM my_user WHERE `name` LIKE '%空%';
-- 选择 name 字段包含两个字符，而且最后一个字符为'大'的记录
SELECT * FROM my_user WHERE `name` LIKE '_大';
```

需要注意的是：

1. `LIKE`后面匹配的内容需要使用单引号括起来，比如`LIKE '%小%'`。
2. MySQL 默认的配置中，模糊查询不区分大小写。但是修改设置，区分大小写，则小写字母不能匹配大写，反之亦然。
3. 由于通配符查询时间会比其他搜索时间长，所以不要过度使用。尽量优选其他解决方案。
4. 通配符放在开始处，搜索速度最慢，能不放在最前面就不要放在最前面。
5. `%`可以匹配很多东西，但是不可以匹配 NULL，即使使用`LIKE '%'`也不能匹配 NULL。
6. `%`可以匹配任意多个，包括 0 个字符。而`_`只能匹配一个字符，一个也不能多，一个也不能少。


### 组合查询

`WHERE`后面允许跟随多个条件，他们允许用`AND`或者`OR`进行连接：

* `AND`前者表示*并且*的意思，也就是逻辑与，会缩小数据范围；
* `OR`表示*或*的意思，也就是逻辑或，会增大范围。

```mysql
-- 查询 age 的值为 25，而且 user_id 的值为 4 的记录
SELECT * FROM my_user WHERE age = 25 AND user_id = 4;
-- 查询 age 的值为 25，或者 user_id 的值为 1 的记录
SELECT * FROM my_user WHERE age = 25 OR user_id = 1;
```

需要注意的是：**`AND`的优先级比`OR`的优先级高**。如果需要改变优先级，可以考虑使用括号`()`来重新组合。

```mysql
SELECT * FROM my_user WHERE age = 25 OR user_id = 1 AND user_id > 3;
```

可能查询出来的结果如下图所示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494922973754.png)

这是由于，`AND`的优先级高于`OR`优先级，所以会先执行`user_id = 1 AND user_id > 3`这个判断条件(没有符合的结果)，然后再和前面的`age = 25`查询出来的结果合并，最终得到的结果就都是 age 字段的值为 25 的结果了。

而如果我们使用括号来改变查询条件的顺序，那么结果可能就完全不同了：

```mysql
SELECT * FROM my_user WHERE (age = 25 OR user_id = 1) AND user_id > 3;
```

结果可能如下：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494923167342.png)

