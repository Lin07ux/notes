> 转摘：[同一个SQL语句，为啥性能差异咋就这么大呢？（1分钟系列）](https://mp.weixin.qq.com/s/oWNrLHwqM-0ObuYbuGj98A)

EXPLAIN 结果中的`type`字段代表连接类型，它描述了找到所需数据使用的扫描方式。

最为常见的扫描方式有：

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

下面对各个类别分别举例说明。

### 1. system

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

### 2. const

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

### 3. eq_ref

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

### 4. ref

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

### 5. range

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

### 6. index

`index`类型，需要扫描索引上的全部数据。它仅比全表扫描快一点。

比如：

```sql
EXPLAIN COUNT(*) FROM user;
```

结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1562725732404.png)


在这里，`count`查询需要通过扫描索引上的全部数据来计数，所以就是`index`方式了。

### 7. all

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


