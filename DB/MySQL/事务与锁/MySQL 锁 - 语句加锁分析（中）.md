> 转摘：[超全面MySQL语句加锁分析（中篇）（求转）](https://mp.weixin.qq.com/s/ODbju9fjB5QFEN8IIYp__A)

## 四、REPEATABLE READ 隔离级别下的锁定读

采用**加锁**方式解决并发事务产生的问题时，REPEATABLE READ 隔离级别与 READ UNCOMMITTED和READ COMMITTED这两个隔离级别相比，最主要的就是要解决**幻读**问题，而**幻读**问题的解决还要靠 **Gap 锁**。

### 4.1 使用主键进行等值查询的锁定读

#### 4.1.1 `SELECT ... LOCK IN SHARE MODE`语句

比如：

```sql
SELECT * FROM hero WHERE number = 8 LOCK IN SHARE MODE;
```

由于主键具有唯一性，所以在一个事务中执行上述语句时得到的结果集中包含一条记录，第二次执行上述语句前肯定不会有别的事务插入多条`number = 8`的记录。也就是说：一个事务中两次执行上述语句并不会发生幻读。这种情况和 READ UNCOMMITTED／READ COMMITTED 隔离级别一样，只需要为这条`number = 8`的记录加上一个 **S 型记录锁**就好了。

如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612082788508.png"/>

但是，如果要查询主键值不存的记录，比如：

```sql
SELECT * FROM hero WHERE number = 7 LOCK IN SHARE MODE;
```

由于`number = 7`的记录并不存在，为了禁止**幻读**现象（也就是避免在同一事务中下一次执行相同语句时得到的结果集中包含了`number = 7`的记录），在当前事务提交前，需要预防别的事务插入`number = 7`的新纪录，所以需要在`number = 8`的记录上加上一个 **Gap 锁**，也就是不允许别的事务插入 number 的值在`(3, 8)`这个开区间的新纪录。示意图类似如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612083135385.png"/>

而这种情况在 READ UNCOMMITTED／READ COMMITTED 隔离级别下则什么锁也不需要加，因为在 READ UNCOMMITTED／READ COMMITTED 隔离级别下并不需要禁止**幻读**的问题。

#### 4.1.2 `SELECT ... FOR UPDATE`/`UPDATE...`/`DELETE ...`语句

其他情况下，使用主键进行等值查询的情况与 READ UNCOMMITTED／READ COMMITTED 隔离级别下的情况类似。

### 4.2 使用主键进行范围查询的锁定读

#### 4.2.1 `SELECT ... LOCK IN SHARE MODE`语句

比如：

```sql
SELECT * FROM hero WHERE number >= 8 LOCK IN SHARE MODE;
```

因为要解决幻读问题，所以需要禁止别的事务插入符合`number >= 8`添加的记录。又因为主键本身就是唯一的，所以不用担心在`number = 8`的前边有新的记录插入，只需要保证不让新纪录插入到`number = 8`记录的后面就好了。

所以：

* 为`number = 8`的聚簇索引记录加上一个 **S 型记录锁**；
* 为`number > 8`的所有聚簇索引记录都加上一个 **S 型 Next-Key 锁**（包括 Supremum 伪记录）。

示意图类似如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612083677950.png"/>

> 为什么不给 Supremum 记录加 Gap 锁，而是加 Next-Key 锁呢？其实 InnoDB 在处理 Supremum 记录上的 Next-Key 锁时就是当做 Gap 锁看待的，只不过为了节省锁结构（锁的类型不一样的话不能被放到一个锁结构中）才这么做的而已。

与READ UNCOMMITTED/READ COMMITTED 隔离级别类似，在 REPEATABLE READ 隔离级别下，下边这个范围查询也是有点特殊的：

```sql
SELECT * FROM hero WHERE number <= 8 LOCK IN SHARE MODE;
```

在 READ UNCOMMITTED/READ COMMITTED 隔离级别下，这个语句会为 number 的值为 1、3、8、15 这 4 条记录都加上 **S 型记录锁**，然后由于`number = 15`的记录不满足边界条件`number <= 8`，随后就把这条记录的锁释放掉。

在 REPEATABLE READ 隔离级别下的加锁过程与之类似，不同的地方在于，会为 1、3、8、15 这 4 条记录加上 **S 型 Next-Key 锁**。而且需要注意的是：**REPEATABLE READ 隔离级别下，在判断`number = 15`的记录不满足边界条件`number <= 8`后，并不会去释放加在该记录上的锁！**

所以在 REPEATABLE READ 隔离级别下，该语句的加锁示意图就如下所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612084393532.png"/>

这样，如果别的事务想要插入的新的记录的 number 的值在`(-∞, 1)`、`(1, 3)`、`(3, 8)`、`(8, 15)`之间的话，是会进入等待状态的。

> 很显然。这么粗暴的做法导致的一个后果就是别的事务竟然不允许加入 number 的值在`(8, 15)`这个区间中的新纪录，甚至不允许别的事务再获取 number 的值为 15 的记录上的锁。而理论上只需要禁止别的事务插入 number 的值在`(-∞, 8)`质检的新纪录就好。

#### 4.2.2 `SELECT ... FOR UPDATE`语句

和`SELECT ... LOCK IN SHARE MODE`语句类似，只不过需要将上面提到的 **S 型 Next-Key 锁**替换成 **X 型 Next-key 锁**。

#### 4.2.3 `UPDATE ...`语句

如果`UPDATE`语句未更新二级索引列，比如：

```sql
UPDATE hero SET country = '汉' WHERE number >= 8;
```

这个语句加锁的方式和上边所说的`SELECT ... FOR UPDATE`语句一致。

如果`UPDATE`语句中更新了二级索引列，比如：

```sql
UPDATE hero SET name = 'cao曹操' WHERE number >= 8;
```

对聚簇索引记录加锁的情况和`SELECT ... FOR UPDATE`语句一致，也就是对`number = 8`的聚簇索引记录加 **X 型记录锁**，对 number 的值为 15 和 20 的聚簇索引记录以及 Supremum 记录加 **X 型 Next-key 锁**。

但是因为也要更新二级索引`idx_name`，所以也会对 number 的值为 8、15、20 的聚簇索引记录对应的`idx_name`二级索引记录加 **X 型记录锁**。

示意图如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612085139600.png" width="320"/>

如果是下边这个语句：

```sql
UPDATE hero SET name = 'cao曹操' WHERE number <= 8;
```

则会对 number 的值为 1、3、8、15 的聚簇索引记录加 **X 型 Next-Key 锁**，其中`number = 15`的聚簇索引记录不满足`number <= 8`的边界条件，虽然在 REPEATABLE READ 隔离级别下不会将它的锁释放掉，但是也并不会对这条聚簇索引记录对应的二级索引记录加锁。也就说：只会为 number 的值为 1、3、8 的聚簇索引记录对应的`idx_name`二级索引记录加 **X 型记录锁**。

加锁示意图如下所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612085354611.png" width="320"/>

#### 4.2.4 `DELETE ...`语句

对于下面的两种语句：

```sql
DELETE FROM hero WHERE number >= 8;
DELETE FROM hero WHERE number <= 8;
```

它们的加锁情况和更新带有二级索引列的`UPDATE`语句一致，只是会将对应的所有二级索引记录都加锁。

### 4.3 使用唯一二级索引进行等值查询的锁定读

由于 hero 表没有唯一耳机索引，所以先将原先的`idx_name`修改为一个唯一耳机索引`uk_name`：

```sql
ALTER TABLE hero DROP INDEX idx_name, ADD UNIQUE KEY uk_name (name);
```

#### 4.3.1 `SELECT ... LOCK IN SHARE MODE`语句

比如：

```sql
SELECT * FROM hero WHERE name = 'c曹操' LOCK IN SHARE MODE;
```

由于二级索引列具有唯一性，如果在一个事物中第一次执行上述语句时得到一条记录，第二次执行上述语句前肯定不会有别的事务插入多条`name = 'c曹操'`的记录。也就说：一个事务中两次执行上述语句并不会发生**幻读**。这种情况下和 READ UNCOMMITTED／READ COMMITTED 隔离级别下一样，只需要为这条`name = 'c曹操'`的二级索引记录加一个 **S 型记录锁**，然后再为它对应的聚簇索引记录加一个 **S 型记录锁**就好了。

注意加锁顺序，是先对二级索引记录加锁，在对聚簇索引记录加锁。示意图如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612085846910.png"/>

如果对唯一二级索引列进行等值查询的记录并不存在，比如：

```sql
SELECT * FROM hero WHERE name = 'g关羽' LOCK IN SHARE MODE;
```

为了禁止幻读，所以需要保证别的事务不能在插入`name = 'c关羽'`的新纪录。在唯一二级索引 un_name 中，值比`g关羽`大的第一条记录的值为`l刘备`，所以需要在这条二级索引记录上加一个 **Gap 锁**。如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612086054567.png" width="320"/>

注意：**这里只对二级索引记录进行加锁，并不会对聚簇索引记录进行加锁**。

#### 4.3.2 `SELECT ... FOR UPDATE`语句

和`SELECT ... LOCK IN SHARE MODE`语句类似，只不过加的是 **X 型记录锁**。

#### 4.3.3 `UPDATE ...`语句

与`SELECT ... FOR UPDATE`的加锁情况类似，只不过如果被更新的列中还有别的二级索引列的话，这些对应的二级索引记录也会被加 **X 型记录锁**。

#### 4.3.4 `DELETE ...`语句

与`SELECT ... FOR UPDATE`的加锁情况类似，不过如果表中还有别的二级索引列的话，这些对应的二级索引记录也会被加 **X 型记录锁**。

### 4.4 使用唯一二级索引进行范围查询的锁定读

#### 4.4.1 `SELECT ... LOCK IN SHARE MODE`语句

比如：

```sql
SELECT * FROM hero FORCE INDEX(uk_name) WHERE name >= 'c曹操' LOCK IN SHARE MODE;
```

这个语句的执行过程是：先到二级索引中定位到满足`name >= 'c曹操'`的第一条记录，也就是`name = 'c曹操'`的记录，然后就可以沿着由记录组成的单向链表一路向后找。

从二级索引 uk_name 的示意图中可以看出，所有的用户记录都满足`name >= 'c曹操'`的这个条件，所有所有的二级索引记录都会被加 **S 型 Next-Key 锁**，它们对应的聚簇索引记录也会被加 **S 型记录锁**。而且，二级索引的最后一条 Supremum 记录也会被加 **S 型 Next-Key 锁**。

不过需要注意一下加锁顺序：对一条二级索引记录加锁完成后，会接着对它相应的聚簇索引记录加锁，完成后才会对下一条二级索引进行加锁，以此类推。

示意图如下所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612086671263.png"/>

> uk_name 是唯一二级索引，唯一二级索引本身就能保证其自身的值是唯一的，那为啥还要给`name = 'c曹操'`的记录加上 **S 型 Next-Key 锁**，而不是 **S 型记录锁**呢？按理说只需要给这条二级索引记录加 **S 型记录锁**就好了。具体的原因并不清楚。

再看下边的语句：

```sql
SELECT * FROM hero WHERE name <= 'c曹操' LOCK IN SHARE MODE;
```

这个语句会先为`name = 'c曹操'`的二级索引记录加 **S 型 Next-Key 锁**，然后给它对应的聚簇索引记录加 **S 型记录锁**。之后还要给`name = 'l刘备'`的二级索引记录加 **S 型 Next-Key 锁**。由于`name = 'l刘备'`的二级索引记录不满足索引条件下推的`name <= 'c曹操'`条件，压根不会释放掉该记录的锁，就直接报告 Server 层查询完毕了。这样可以禁止其他事务插入 name 值在`('c曹操', 'l刘备')`之间的新记录，从而防止幻读产生。

这个过程的加锁示意图如下所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612087093615.png"/>

需要注意的是：InnoDB 在这里给`name = 'l刘备'`的二级索引记录加的是 **S 型 Next-Key 锁**，而不是简单的 **Gap 锁**。

#### 4.4.2 `SELECT ... FOR UPDATE`语句

和SELECT ... LOCK IN SHARE MODE语句类似，只不过加的是 **X 型记录锁**。

#### 4.4.3 `UPDATE ...`语句

比如：

```sql
UPDATE hero SET country = '汉' WHERE name >= 'c曹操';
```

假设该语句执行时使用了 uk_name 二级索引来进行锁定读（如果二级索引扫描的记录太多，也可能因为成本过大直接使用全表扫描的方式进行锁定读），而这条`UPDATE`语句并没有更新二级索引列，那么它的加锁方式和上面说的`SELECT ... FOR UPDATE`语句一致。

如果还有其他二级索引列也被更新，那么也会为这些二级索引记录进行加锁。

不过还有一个情况需要注意，比如说：

```sql
UPDATE hero SET country = '汉' WHERE name <= 'c曹操';
```

由于索引条件下推这个特性只适用于`SELECT`语句，也就说`UPDATE`语句中无法使用，这样就会需要进行回表操作。这个语句在执行的时候，会为 name 值为`c曹操`和`l刘备`的二级索引记录加 **X 型 Next-Key 锁**，对它们对应的聚簇索引记录加 **X 型记录锁**。不过之后在判断边界条件时`name = 'l刘备'`的二级索引记录并不符合`name <= 'c曹操'`的边界条件，但是**在 REPEATABLE READ 隔离级别下并不会释放该记录上加的锁**。

整个过程的加锁示意图如下所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612087935960.png"/>

#### 4.4.4 `DELETE ...`语句

比如：

```sql
DELETE FROM hero WHERE name >= 'c曹操';
DELETE FROM hero WHERE name <= 'c曹操';
```

这两个语句采用二级索引来进行锁定读时，它们的加锁情况和更新带有二级索引列的`UPDATE`语句一致，不过如果表中还有别的二级索引列的话，这些对应的二级索引记录也会被加锁。


### 4.5 使用普通二级索引进行等值查询的锁定读

再将上边的唯一二级索引 uk_name 改回普通二级索引 idx_name：

```sql
ALTER TABLE hero DROP INDEX uk_name, ADD INDEX idx_name (name);
```

#### 4.5.1 `SELECT ... LOCK IN SHARE MODE`语句

比如：

```sql
SELECT * FROM hero WHERE name = 'c曹操' LOCK IN SHARE MODE;
```

由于普通的二级索引没有唯一性，所以一个事务在执行上述语句之后，要组织别的事务插入`name = 'c曹操'`的新纪录，InnoDB采用下边的方式进行加锁：

* 对所有`name = 'c曹操'`的二级索引加 **S 型Next-Key 锁**，它们对应的聚簇索引记录加 **S 型记录锁**；
* 对最后一个`name = 'c曹操'`的二级索引记录的下一条二级索引记录加 **Gap 锁**。

整个加锁所示意图如下所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612088324194.png"/>

如果对普通二级索引等值查询的值并不存在，比如：

```sql
SELECT * FROM hero WHERE name = 'g关羽' LOCK IN SHARE MODE;
```

此时的加锁方式和前面的唯一二级索引等值查询时值不存在的情况是一样的。

#### 4.5.2 `SELECT ... FOR UPDATE`语句

和`SELECT ... LOCK IN SHARE MODE`语句类似，只不过二级索引记录上加的是 **X 型 Next-Key 锁**，对应的聚簇索引记录上加的是 **X 型记录锁**。

#### 4.5.3 `UPDATE ...`语句

与`SELECT ... FOR UPDATE`的加锁情况类似，不过如果被更新的列中还有别的二级索引列的话，这些对应的二级索引记录也会被加锁。

#### 4.5.4 `DELETE ...`语句

与`SELECT ... FOR UPDATE`的加锁情况类似，不过如果表中还有别的二级索引列的话，这些对应的二级索引记录也会被加锁。

### 4.6 使用普通索引进行范围查询的锁定读

与唯一二级索引列的加锁情况类似。

### 4.7 全表扫描的锁定读

#### 4.7.1 `SELECT ... LOCK IN SHARE MODE`语句

比如：

```sql
SELECT * FROM hero WHERE country  = '魏' LOCK IN SHARE MODE;
```

由于 country 列上未建立索引，所以只能采用全表扫描的方式来执行这条查询语句，存储引擎每读取一条聚簇索引记录，就会为这条记录加锁一个 **S 型 Next-Key 锁**，然后返回给 Server 层。如果 Server 层判断`country = '魏'`这个条件成立，则将其发送给客户端，否则会向 InnoDB 存储引擎发送释放掉该记录上的锁的消息。不过，在 REPEATABLE READ 隔离级别下，InnoDB 存储引擎并不会真正的释放掉锁，所以聚簇索引的全部记录都会被加锁，并且在事务提交前不释放。

如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1612089134823.png"/>

可以看到。**全部的记录都被加了 Next-Key 锁**！此时别的事务不仅无法向表中插入新的记录，就是对某条记录加 X 锁都不可以。这种情况下会极大地影响访问该表的并发事务处理能力。如果可能的话，要尽可能的建立合适的索引。

#### 4.7.2 其他语句

使用`SELECT ... FOR UPDATE`进行加锁的情况与上边类似，只不过加的是 **X 型记录锁**。

对于`UPDATE ...`语句来说，加锁情况与`SELECT ... FOR UPDATE`类似，不过如果被更新的列中还有别的二级索引列的话，这些对应的二级索引记录也会被加 **X 型记录锁**。

对`DELETE ...`的语句来说，加锁情况与`SELECT ... FOR UPDATE`类似，不过如果表中还有别的二级索引列的话，这些对应的二级索引记录也会被加 **X 型记录锁**。


