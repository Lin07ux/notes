## 一、简介

MySQL 的`EXPLAIN`命令用于 SQL 语句的查询执行计划(QEP)。这条命令的输出结果能够展示 MySQL 优化器是如何执行 SQL 语句的。这条命令并没有提供任何调整建议，但它能够提供重要的信息帮助做出调优决策。它仅对`SELECT`语句或者特定表有效，可以帮助**选择更好的索引**和**写出更优化的查询语句**。

> 如果`EXPLAIN`作用在表上，那么其等同于`DESC`表命令。

> 在 5.6.10 版本里面，是可以直接对 DML(增删改查等) 语句进行`EXPLAIN`分析操作的。

`UPDATE`和`DELETE`命令也需要进行性能改进，需要把它们改写成 SELECT 语句(以便对它们执行`EXPLAIN`命令)。如：

```sql
-- UPDATE 语句
UPDATE table1 SET col1 = X, col2 = Y WHERE id = 9 AND dt >= '2010-01-01';

-- 改写成 SELECT 语句，并进行 EXPLAIN
EXPLAIN SELECT col1, col2 FROM table1 WHERE id = 9 AND dt >= '2010-01-01';
```

## 二、使用

`EXPLAIN`一般都是用于查看`SELECT`语句的，使用方法很简单，直接在`SELECT`语句的最前面添加`EXPLAIN`关键字即可，和一般的查询语句写法基本相同：

```sql
EXPLAIN SELECT * FROM user WHERE id = 1;
```

> 默认情况下，EXPLAIN 的输出是一个横向的表格，如果想要竖向显示，可以在语句最后添加`\G`来更改。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1473954724661.png" width="817"/>

结果中各个参数解释如下：

### 2.1 id

这个是 SELECT 查询序列号，表示查询中执行 SQL 语句的顺序。它的值一般有三种情况：

* 全部相同：SQL 的执行顺序是由上至下；
* 全部不同：SQL 的执行顺序是根据值大的优先执行；
* 既存在相同，又存在不同：先根据值大的优先执行，相同时则是从上至下的执行。

### 2.2 select_type

SELECT 操作的类型，主要是用于区别普通查询、联合查询、嵌套的复杂查询。它的值有如下几种：

* `simple` 它表示简单的 SELECT 查询，查询中不包含 UNION 和子查询。
* `primary` 查询中若包含任何复杂的子查询，最外层查询则被标记为`primary`。这个类型通常可以在 DERIVED 和 UNION 类型混合使用时见到。
* `subquery` 在 SELECT 或 WHERE 的列表中包含了子查询。
* `derived` 当一个表不是一个物理表时，那么就被叫做 DERIVED。比如在 FROM 列表中包含的子查询就会被标记为`derived`(衍生)。
* `union` 出现在 UNION 语句的后面那个 SELECT 语句。
* `dependent union` UNION 中的第二个或后面的 SELECT 语句，取决于外面的查询。
* `union result` 从 UNION 表获取结果的 SELECT。

### 2.3 table

输出的行所用的表。也就是当前 SELECT 语句操作的表。这个值可能是表名、表的别名或者一个为查询产生临时表的标识符，如派生表、子查询或集合。

### 2.4 type

显示连接使用了何种类型。从最好到最差的连接类型为`system`、`const`、`eq_reg`、`ref`、`range`、`index`和`all`。

* `all` 全表扫描。通常可以增加更多的索引而不要使用 ALL，使得性能基于前面的表中的常数值或列值被检索出。
* `index`：全索引文件扫描。这通常比 ALL 快，因为索引文件通常比数据文件小。也就是说虽然 ALL 和 Index 都是读全表，但 Index 是从索引文件中读取的，而 ALL 是从数据文件中读的。
* `range`：给定范围内的检索，使用一个索引来检查行。由于缩小了范围，所以比 index 全索引文件扫描要快。SQL 语句中一般会有`between`、`in`、`>`、`<`等查询逻辑。
* `ref`：非唯一性索引(非 PRIMARY KEY 或 UNIQUE 索引)扫描，本质上也是一种索引访问，返回所有匹配某个单独值的行，查询结果是多个并非唯一值。如果使用的键仅仅匹配少量行，该联接类型是不错的。
* `eq_reg`：唯一性索引(UNIQUE 或 PRIMARY KEY 索引)扫描，对于每个索引键，表中有一条记录与之匹配。查询条件中会使用`=`比较带索引的列。
* `const`：表最多有一个匹配行，用于比较 PRIMARY KEY 或者 UNIQUE 索引。因为只匹配一行数据，所以很快。
* `system`：系统表，少量数据，往往不需要进行磁盘 IO；

### 2.5 possible_keys

显示查询中可能用到的索引，可以为一个或多个值，也可以为 null。

该值并不一定会被查询实际使用，仅供参考使用。

### 2.6 key

显示查询中实际使用的索引。如果为 null，则没有使用索引。

很少的情况下，MySQL 会选择优化不足的索引。这种情况下，可以在 SELECT 语句中使用`use index(indexname)`来强制使用一个索引或者用`ignore index indexname`来强制 mysql 忽略索引。

### 2.7 key_len

使用的索引的长度。在不损失精确性的情况下，长度越短越好。

`key_len`显示的值为索引字段的最可能长度，并非实际使用长度，即`key_len`是根据表定义计算而得，并不是通过表内检索出的。

### 2.8 ref

显示索引的哪一列或常量被用于查找索引列上的值。

### 2.9 rows

mysql 认为必须检查的用来返回请求数据的行数，数值越大越不好，说明没有用好索引。

### 2.10 filtered

一个百分比的值，和`rows`列的值一起使用，可以估计出查询执行计划(QEP)中的前一个表的结果集，从而确定 join 操作的循环次数。小表驱动大表，可以减轻连接的次数。

### 2.11 Extra

关于 mysql 如何解析查询的额外信息。`using temporary`和`using filesort`是最差的情况，意思 mysql 根本不能使用索引，结果是检索会很慢。

Extra 列各个值的意义描述：

* `distinct`：一旦 mysql 找到了与行相联合匹配的行，就不再搜索了。
* `not exists`：mysql 优化了 left join，一旦它找到了匹配 left join 标准的行，就不再搜索了
* `range checked for each record(index map:#)`：没有找到理想的索引，因此对于从前面表中来的每一个行组合，mysql 检查使用哪个索引，并用它来从表中返回行。这是使用索引的最慢的连接之一
* `using filesort`：mysql 需要进行额外的步骤来发现如何对返回的行排序。它根据连接类型以及存储排序键值和匹配条件的全部行的行指针来排序全部行。
* `using temporary`：mysql 需要创建一个临时表来存储结果，这通常发生在对不同的列集进行 order by 上，而不是 group by 上。
* `using index`：表示相应的 SELECT 操作中使用了覆盖索引，避免访问了表的数据行，效果不错！如果同时出现`Using where`，表明索引被用来执行索引键值的查找，否则表示索引用来读取数据而非执行查找动作。
* `Using index condition`：在 5.6 版本后加入的新特性，优化器会在索引存在的情况下，通过符合 RANGE 范围的条数和总数的比例来选择是使用索引还是进行全表遍历。
* `Using where`：表明使用了 where 过滤。
* `Using join buffer`：表明使用了连接缓存。
* `impossible where`：where 语句的值总是 false，不可用，不能用来获取任何元素。
* `system`：表只有一行：system 表。这是 const 连接类型的特殊情况
* `const`：表中的一个记录的最大值能够匹配这个查询（索引可以是主键或惟一索引）。因为只有一行，这个值实际就是常数，因为 mysql 先读这个值然后把它当做常数来对待
* `eq_ref`：在连接中，mysql 在查询时，从前面的表中，对每一个记录的联合都从表中读取一个记录，它在查询使用了索引为主键或惟一键的全部时使用
* `ref`：这个连接类型只有在查询使用了不是惟一或主键的键或者是这些类型的部分（比如，利用最左边前缀）时发生。对于之前的表的每一个行联合，全部记录都将从表中读出。这个类型严重依赖于根据索引匹配的记录多少—越少越好
* `range`：这个连接类型使用索引返回一个范围中的行，比如使用 > 或 < 查找东西时发生的情况
* `index`：这个连接类型对前面的表中的每一个记录联合进行完全扫描（比 all 更好，因为索引一般小于表数据）
* `all`：这个连接类型对于前面的每一个记录联合进行完全扫描，这一般比较糟糕，应该尽量避免

### 2.12 partitions

表所使用的分区。比如，将公司十年的订单数据分成十个区，每一年代表一个区，在查询的时候可以大大提升查询效率。此时 EXPLAIN 中就会出现`partitions`字段，表示从哪些分区中进行数据查询。

## 三、Type 字段值

> 转摘：[同一个SQL语句，为啥性能差异咋就这么大呢？（1分钟系列）](https://mp.weixin.qq.com/s/oWNrLHwqM-0ObuYbuGj98A)

EXPLAIN 结果中的`type`字段代表连接类型，它描述了找到所需数据使用的扫描方式。最为常见的扫描方式有：

* `system`：系统表，少量数据，往往不需要进行磁盘 IO；
* `const`：常量连接；
* `eq_ref`：主键索引(primary key)或者非空唯一索引(unique not null)等值扫描；
* `ref`：非主键非唯一索引等值扫描；
* `range`：范围扫描；
* `index`：索引树扫描；
* `all`：全表扫描(full table scan)。

上面各类扫描方式由快到慢：

```
system > const > eq_ref > ref > range > index > ALL
```

#### 3.1 system

从系统表中查询数据，由于系统表中的数据较少，一般都会被自动载入到内存中，所以查询速度非常快：

```sql
EXPLAIN SELECT * FROM mysql.time_zone;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562724096001.png)

上例中，从系统库 mysql 的系统表`time_zone`里查询数据，扫码类型为`system`，这些数据已经加载到内存里，不需要进行磁盘IO。这类扫描是速度最快的。

再举一个例子，内层嵌套(const)返回了一个临时表，外层嵌套从临时表查询，其扫描类型也是`system`，也不需要走磁盘 IO，速度超快：

```sql
EXPLAIN SELECT * FROM (SELECT * FROM user WHERE id = 1) tmp;
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1562724206825.png)

#### 3.2 const

`const`扫描效率极高，返回数据量少，速度非常快。扫描的条件为：

1. 命中主键(primary key)或者唯一(unique)索引；
2. 被连接的部分是一个常量(const)值。

所以不能有类型转换或者使用方法。

数据准备：

```sql
CREATE TABLE user (
  id int primary key,
  name varchar(20)
) engine=innodb;
 
INSERT INTO user VALUES(1, 'shenjian');
INSERT INTO user VALUES(2, 'zhangsan');
INSERT INTO user VALUES(3, 'lisi');
```

查询如下：

```sql
EXPLAIN SELECT * FROM user WHERE id = 1;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562724348859.png)

这里，`id`是 PK，连接部分是常量 1，所以就使用了`const`扫描类别。

#### 3.3 eq_ref

`eq_ref`扫描的条件为，对于前表的每一行(row)，后表只有一行被扫描。再细化一点：

1. `join`查询；
2. 命中主键(primary key)或者非空唯一(unique not null)索引；
3. 等值连接。

数据准备：

```sql
CREATE TABLE user (
  id int primary key,
  name varchar(20)
) engine=innodb;
 
INSERT INTO user VALUES(1, 'shenjian');
INSERT INTO user VALUES(2, 'zhangsan');
INSERT INTO user VALUES(3, 'lisi');
 
CREATE TABLE user_ex (
  id int primary key,
  age int
) engine=innodb;
 
INSERT INTO user_ex VALUES(1, 18);
INSERT INTO user_ex VALUES(2, 20);
INSERT INTO user_ex VALUES(3, 30);
INSERT INTO user_ex VALUES(4, 40);
INSERT INTO user_ex VALUES(5, 50);
```

查询如下：

```sql
EXPLAIN SELECT * FROM user, user_ex WHERE user.id = user_ex.id;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562725226756.png)

#### 3.4 ref

`ref`扫描，可能出现在`join`里，也可能出现在单表普通索引里，每一次匹配可能有多行数据返回，虽然它比`eq_ref`要慢，但它仍然是一个很快的`join`类型。

如果把上面`eq_ref`案例中的主键索引，改为普通非唯一(non unique)索引，那么查询的时候就会变成`ref`扫描方式了。

数据准备：

```sql
CREATE TABLE user (
  id int,
  name varchar(20),
  index(id)
) engine=innodb;
 
INSERT INTO user VALUES(1, 'shenjian');
INSERT INTO user VALUES(2, 'zhangsan');
INSERT INTO user VALUES(3, 'lisi');
 
CREATE TABLE user_ex (
  id int,
  age int,
  index(id)
) engine=innodb;
 
INSERT INTO user_ex VALUES(1, 18);
INSERT INTO user_ex VALUES(2, 20);
INSERT INTO user_ex VALUES(3, 30);
INSERT INTO user_ex VALUES(4, 40);
INSERT INTO user_ex VALUES(5, 50);
```

查询如下：

```sql
EXPLAIN SELECT * FROM user, user_ex WHERE user.id = user_ex.id;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562725410425.png)

由`eq_ref`降级为了`ref`，此时对于前表的每一行(row)，后表可能有多于一行的数据被扫描。

又比如，对于如下的查询：

```sql
EXPLAIN SELECT * FROM user WHERE id = 1;
```

由于`id`改为普通非唯一索引，常量的连接查询，也由`const`降级为了`ref`，因为也可能有多于一行的数据被扫描。

#### 3.5 range

`range`扫描就比较好理解了，它是索引上的范围查询，它会在索引上扫码特定范围内的值。需要注意的是，**必须是在索引上进行范围查询**，否则不能批量跳过。

数据准备：

```sql
CREATE TABLE user (
  id int,
  name varchar(20),
  index(id)
) engine=innodb;
 
INSERT INTO user VALUES(1, 'shenjian');
INSERT INTO user VALUES(2, 'zhangsan');
INSERT INTO user VALUES(3, 'lisi');
INSERT INTO user VALUES(4, 'wangwu');
INSERT INTO user VALUES(5, 'zhaoliu');
```

使用`between`、`in`、`>`都是典型的范围(range)查询。如下：

```sql
EXPLAIN SELECT * FROM user WHERE id BETWEEN 1 AND 4;
EXPLAIN SELECT * FROM user WHERE id IN(1, 2, 3);
EXPLAIN SELECT * FROM user WHERE id > 3;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562725617369.png)

#### 3.6 index

`index`类型，需要扫描索引上的全部数据。它仅比全表扫描快一点。

比如：

```sql
EXPLAIN COUNT(*) FROM user;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562725732404.png)


在这里，`count`查询需要通过扫描索引上的全部数据来计数，所以就是`index`方式了。

#### 3.7 all

如果查询中没有使用到索引，那么就需要使用全表扫描方式了，这时`type`就是`all`了。全表扫描代价极大，性能很低，是应当极力避免的。

数据准备：

```sql
CREATE TABLE user (
  id int,
  name varchar(20)
) engine=innodb;
 
INSERT INTO user VALUES(1, 'shenjian');
INSERT INTO user VALUES(2, 'zhangsan');
INSERT INTO user VALUES(3, 'lisi');
 
CREATE TABLE user_ex (
  id int,
  age int
) engine=innodb;
 
INSERT INTO user_ex VALUES(1, 18);
INSERT INTO user_ex VALUES(2, 20);
INSERT INTO user_ex VALUES(3, 30);
INSERT INTO user_ex VALUES(4, 40);
INSERT INTO user_ex VALUES(5, 50);
```

在这两个表中，都没有创建索引，那么查询的时候就会是全表扫描了：

```sql
EXPLAIN SELECT * FROM user, user_ex WHERE user.id = user_ex.id;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562725945016.png)

由于`id`上不建索引，对于前表的每一行(row)，后表都要被全表扫描。


