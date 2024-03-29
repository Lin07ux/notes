> 转摘：
> 
> 1. [手把手教你分析Mysql死锁问题](https://www.cnblogs.com/jay-huaxiao/p/12685287.html)
> 2. [终于被我说清楚了！](https://mp.weixin.qq.com/s/Ef73pSWb_k6yiTTlNCrEjg)
> 3. [MySQL 全局锁、表级锁、行级锁，你搞清楚了吗？](https://mp.weixin.qq.com/s/SgteE94sZfAMv5mZJYrTqw)
> 4. [争议很大的问题](https://mp.weixin.qq.com/s/lHk6q0r2EU3pfthXQnFC7g)
> 5. [美团面试特有：写个 SQL 语句然后问加了哪些锁](https://mp.weixin.qq.com/s/36vYCusyO-67-2vvU_6RtQ)

> 下面的说明以 MySQL InnoDB 存储引擎为例。

## 一、锁类别

MySQL 中的锁根据不同的方式可以分为如下的几种类别：

![](http://cnd.qiniu.lin07ux.cn/markdown/1587716080800.png)

### 1.1 全局锁

全局锁是锁住整个数据库中的全部表，主要用于**做全库逻辑备份**。这样在备份数据库期间就不会因为数据或表结构的更新而出现备份文件的数据与预期的不一样。

可以通过如下的方式使用：

```sql
FLUSH TABLES WITH READ LOCK; -- 整个数据库处于只读状态
```

释放全局锁则使用如下的命令(会话断开时全局锁会被自动释放)：

```sql
UNLOCK TABLES;
```

全局锁会使整个数据库都被暂时冻结，会造成业务停滞。由于 InnoDB 存储引擎支持可重复读隔离级别，所以可以在备份的时候开启事务，借助 MVCC 避免加锁，提升备份时数据库的可用性：

```shell
$ mysqldump -single-transaction
```

### 1.2 表级锁

MySQL 中表级锁有如下几种：

* 表锁
* 意向锁
* 元数据锁（MDL）
* AUTO-Inc 锁

#### 1.2.1 表锁

表锁是将整个给锁住，分为读锁和写锁。而且，表锁除了会限制别的线程的读写之外，也会限制本线程接下来的读写操作。

比如，对 A 加加了共享表锁，那么本线程接下来对 A 表执行写操作时也会被阻塞。

表锁加锁方式为：

```sql
LOCK TABLES table_name READ; -- 表级别共享锁（读锁）
LOCK TABLES table_name WRITE; -- 表级别独占锁（写锁）
```

释放表锁方式为（会话断开时也会释放所有表锁）：

```sql
UNLOCK TABLES
```

#### 1.2.2 意向锁

关于意向锁，有如下的说明：

> Intention locks are *table-level locks* that indicate which type of lock (shared or exclusive) a transaction requires later for a row in a table.

也就是说，**意向锁是一个表级锁**，起作用就是**指明事务接下来会用到哪种锁（共享锁、排他锁）**。所以，意向锁有两种类型：

* 意向共享锁(IS 锁)：事务想要获得一张表中某几行的读锁（行级读锁）；
* 意向排他锁(IX 锁)：事务想要获得一张表中某几行的写锁（行级写锁）。

申请意向锁的动作是自动完成的，就是说，事务申请一行的行锁的时候，InnoDB 存储引擎会自动为事务先申请该表的意向锁，并不需要调用方显式的申请。

**意向锁的存在是为了快速判断表里是否有就被加锁，以简化加表锁时的判断**。

比如事务要申请该表的写锁，如果没有意向锁就需要遍历表中的行，以检查是否有行锁的存在。而通过表级别的意向锁则可以大大简化这个判断过程，提升效率：任何事务加行锁的时候都会申请该表的相关类型的意向锁，其他事务在申请表锁时只需要判断该表是否被加了某类型的意向锁即可。

**意向共享锁和意向独占锁**是表级锁，**不会和行级的共享锁和独占锁发生冲突**。而且**意向锁之间也不会发生冲突**，只会和共享表锁(`lock tables ... read`)和独占表锁(`lock tables ... write`)发生冲突。

#### 1.2.3 MDL 元数据锁

MDL 元数据锁是为了保证当用户对表执行 CRUD 操作的时候，防止其他线程对这个表结构进行变更。

并不需要显式的使用 MDL，因为在对数据库表进行操作的时候，会自动给这个表加上 MDL：

* 对一张表进行 CRUD 操作时，加的是 MDL 读锁；
* 对一张表做结构变更操作的时候，加的是 MDL 写锁。

MDL 是在事务提交后才会释放，这意味着**事务执行期间，MDL 是一直持有的。**

**MDL 锁的申请操作会形成一个队列，队列中写锁获取优先级高于读锁**。一旦出现 MDL 写锁等待，就会阻塞该表后续所有的 CRUD 操作。

#### 1.2.4 AUTO-Inc 锁

表中常会有被`AUTO_INCREMENT`修饰的自增列，且一般作为自增主键。插入数据时，如果没有指定自增列的值，那么该列的值就会通过为该表加`AUTO-INC`锁后生成。

**AUTO-Inc 锁**是特殊的表锁机制，其并不是在事务提交后才释放，而是在**插入语句执行完成后就会立即释放**。一个事务在持有 AUTO-INC 锁的过程中，其他事务如果要向该表插入语句就会被阻塞，从而保证插入数据时被`AUTO_INCREMENT`修饰的字段的值是连续递增的。

在进行大量数据插入的时候，AUTO-INC 锁会影响插入性能，因为其锁定的时长较长。因此，在 MySQL 5.1.22 版本开始，InnoDB 存储引擎提供了一种*轻量级的锁来实现自增*：

在插入数据的时候，会为被`AUTO_INCREMENT`修饰的字段加上轻量级锁，然后**为该字段赋值一个自增的值，然后就释放这个轻量级锁**。这样就不用等待整个插入语句执行完成后才释放锁。

InnoDB 存储引擎提供了`innodb_autoinc_lock_mode`系统变量来控制选择用 AUTO_INC 锁还是轻量级的锁：

* `innodb_autoinc_lock_mode = 0` 使用 AUTO_INC 锁
* `innodb_autoinc_lock_mode = 2` 使用轻量级锁
* `innodb_autoinc_lock_mode = 1`

    - 普通的 INSERT 语句使用轻量级锁；
    - 类似`INSERT...SELECT`的批量插入数据的语句使用 AUTO-INC 锁。

虽然`innodb_autoinc_lock_mode = 2`时性能最高，但是当 binlog 的日志格式为 statement 的时候，在主从复制的场景中会发生数据不一致的问题。

比如，对于如下的场景：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672646083)

session A 往表 t 中连续插入 4 行数据，然后 session B 创建了一个结构相同的的表 t2，之后两个 session 同时向表 t2 中插入数据。

如果`innodb_autoinc_lock_mode = 2`则意味着申请到自增主键的值后就会释放自增锁，此时就可能出现这样的情况：

* session B 先插入了两个记录`(1, 1, 1)`和`(2, 2, 2)`；
* 然后 session A 申请自增 ID 得到`id = 3`，插入了`(3, 5, 5)`；
* 之后 session B 继续执行，插入两条记录`(4, 3, 3)`和`(5, 4, 4)`。

可以看到，session B 插入的数据的 ID 并不连续。在`binlog_format = statement`的时候，记录的语句就是原始的 SQL 语句，记录的顺序要么是先 session A 的 insert 语句要么是先 session B 的 insert 语句。

但不论是哪种情况，用这个 binlog 在从库中执行时，都是按记录的顺序依次执行两条插入语句，而不会出现两条语句同时执行同时插入的情况。也就是说，在从库中 session B 的 insert 语句插入的数据其 ID 都是连续的。这时，主从库就发生了数据不一致的情况。

要解决这个问题，binlog 日志格式就要设置为 row，这样 binlog 中记录的就是主库分配的自增值，在从库中通过 binlog 同步得到的自增值就和主库一样了。

所以，**当`innodb_autoinc_lock_mode = 2`的时候需要设置`binlog_format = row`，既能提升并发性又不会出现数据一致性问题**。

### 1.3 行级锁

MySQL ISAM 存储引擎不支持行级锁，而 InnoDB 存储引擎支持行级锁。行级锁又可分为共享锁（S 锁）和独占锁（X 锁）：共享锁满足读读共享，读写互斥；独占锁则满足写写互斥、读写互斥。

行级锁的类型主要有：

* Record Lock 记录锁
* Gap Lock 间隙锁
* Next-Key Lock 临键锁
* Insert Intention 插入意向锁

### 1.3.1 Record Lock 记录锁

记录锁是最简单的行锁，仅仅锁住一行，阻塞其他事务对其插入、更新、删除。

记录锁有 S 锁和 X 锁之分：

* S 型记录锁：当一个事务对一行记录加了 S 型记录锁后，其他事务也可以继续对该记录加 S 型记录锁，但是不能对其加 X 型记录锁；
* X 型记录锁：当一个事物对一行记录加了 X 型记录锁后，其他事务不能继续对该记录加 S 型记录锁和 X 型记录锁。

如：`SELECT c1 FROM t WHERE id = 10 FOR UPDATE`，它会将`id = 1`这一行记录加上 X 型的记录锁，这样其他事务就无法对这条记录进行修改和删除了。

**记录锁永远都是加在索引上**的，即使一个表没有索引，InnoDB 也会隐式的创建一个索引，并使用这个索引实施记录锁。

### 1.3.2 Gap Lock 间隙锁

间隙锁是一种加在两个索引记录之间的锁，或者加在第一个索引之前，或最后一个索引之后的间隙。**间隙锁只存在于可重复读隔离级别，是为了解决该级别下的幻读现象**。

使用间隙锁锁住的是一个区间，而不仅仅是这个区间中的每一条数据。

*间隙锁只阻止其他事务插入到间隙中，它们不阻止其他事务在同一个间隙上获得间隙锁*，所以`gap x lock`和`gap s lock`有相同的作用。

### 1.3.3 Next-Key 锁

Next-Key 锁是记录锁和间隙锁的组合，它指的是**加在某条记录上以及这条记录前面间隙上的锁**。

比如，表中有一个 id 范围为`(3, 5]`的 Next-Key Lock，那么其他事务既不能插入`id = 4`的记录，也不能修改`id = 5`的这条记录：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672323101)

由于 Next-Key Lock 包含记录锁，而 X 型记录锁是排他的，所以 X 型的 Next-Key Lock 会阻止其他事务获取相同范围的 X 型的 Next-Key Lock 时。

### 1.3.4 插入意向锁(Insert Intention)

**插入意向锁并不是意向锁，而是一种特殊的间隙锁，属于行锁级别**。如果说间隙锁锁住的是一个区间，那么插入意向锁锁住的就是一个点。从这个角度来说，插入意向锁确实是一种特殊的间隙锁。

插入意向锁与间隙锁的另一个非常重要的差别是：尽管插入意向锁也属于间隙锁，但两个事务却不能在同一时间内，一个拥有间隙锁，另一个拥有该区间内的插入意向锁。当然，插入意向锁如果不在间隙锁区间内则是可以的。

一个事务在插入一条记录的时候，需要判断插入位置是否已被其他事务加了间隙锁（Next-Key Lock 也包含间隙锁）。如果有的话，插入操作就会发生阻塞，直到拥有间隙锁的那个事务释放了间隙锁（提交或回滚）。在此期间会生成一个插入意向锁，表明有事务想在该区间插入新记录，但是现在处于等待状态。

假设事务 A 已经对表加了一个 ID 范围为`(3, 5)`的间隙锁：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672649346)

当事务 A 还没有提交的时候，事务 B 向该表插入一条`id = 4`的新记录，这时会发现插入的位置已经被事务 A 加了间隙锁，于是事务 B 会生成一个插入意向锁，然后将该锁的状态设置为等待状态。此时事务 B 就会发生阻塞，直到事务 A 提交了事务。

> MySQL 加锁时，是先生成锁结构，然后设置锁的状态。如果锁状态是等待状态，并不是意味着事务成功获取到了锁，只有当锁状态为正常状态时，才代表事务成功获取到了锁。

间隙锁、插入意向锁、记录锁和 Next-Key 锁之间的兼容性如下表所示（横向是已持有锁，纵向是正在请求的锁）：

                  | GAP       | Insert Intention | Record    | Next-Key
------------------|-----------|------------------|-----------|-----------
 GAP              | 兼容       | 兼容              | 兼容      | 兼容
 Insert Intention | 冲突       | 兼容              | 兼容      | 冲突
 Record           | 兼容       | 兼容              | 冲突      | 冲突
 Next-Key         | 兼容       | 兼容              | 冲突      | 冲突


## 二、加锁说明

MySQL MyISAM 存储引擎只支持表锁，而 InnoDB 存储引擎同时支持行锁和表锁。

InnoDB 存储引擎在可重复读隔离级别下，**普通的 SELECT 语句属于快照读，是不会对记录加锁的，而是通过 MVCC 来实现的**。无锁的快照读取使得 MySQL InnoDB 的读效率提升了很多。

在 InnoDB 引擎的锁实现中：**行锁加锁是在索引记录上添加的**，如果查询条件没有使用索引字段的话，整张表都无法进行增删改，但这并不是因为加了表锁，而是因为表中所有记录之间都加了间隙锁，相当于把整个表给锁住了。由于没有使用索引，会进行全表扫描，在遍历索引的时候会为对应的记录加上 Next-Key Lock，而不是针对输出的结果加行锁。

在查找过程中，只有访问到的对象才会加锁，而且加锁的基本单位是 Next-Key Lock，但是在一些特定的情况下，Next-Key Lock 会退化为 Record Lock 或者 Gap Lock。

### 2.1 加锁场景

而在锁定读（当前读）时会对读取的记录加行级锁：

```sql
-- 对读取的记录加共享锁（S 锁）
SELECT ... LOCK IN SHARE MODE;

-- 对读取的记录加排他锁（X 锁）
SELECT ... FOR UPDATE
```

上这两个语句必须处于事务中才能起所用，因为**当事务提交了锁就会被释放**。所以在使用上面这两条语句的时候，需要加上`BEGIN`或者`START TRANSACTION`开启事务的语句。

**UPDATE 和 DELETE 操作都会为对应的行记录加排他锁（X 锁）**。

### 2.2 查看锁信息

在 MySQL 中，可以通过查询`performance_schema.data_locks`表来看当前数据库中的加锁信息，也可以再查询的时候通过数据库名和表名进行过滤。

如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672374750)

从上图可以看出，`test.user`表上加了两个锁：

* 表锁：X 类型的意向锁；
* 行锁：X 类型的记录锁。

其中，`LOCK_MODE`表示锁类型，X 表示排它锁，S 表示共享锁。比如：

* `LOCK_MODE: X`说明是 Next-Key Lock；
* `LOCK_MODE: X, REC_NOT_GAP`说明是记录锁；
* `LOCK_MODE: X, GAP`说明是间隙锁。

另外，MDL 锁信息存储在`performance_schema.metadata_locks`：

![](https://cnd.qiniu.lin07ux.cn/markdown/1672649881)

### 2.3 唯一索引等值查询

当使用唯一索引进行等值查询的时候，查询的记录在与不在也会影响加锁的规则：

* 存在：在索引树上定位到这条记录后为该记录添加记录锁 Record Lock；
* 不存在：在索引树上找到第一条大于该查询值的记录和第一条小于该查询值的记录，然后在这两条记录之间加上间隙锁 Gap Lock。

比如，假设 user 表中有主键 ID 为 20、25、30 的数据，那么对于下面的 SQL：

```sql
SELECT * FROM user WHERE id = 25 FOR UPDATE;
SELECT * FROM user WHERE id = 22 FOR UPDATE;
```

前者可以查找到 id 为 25 的记录，那么就会在该记录上加上记录锁；后者因为不存在 id 为 22 的记录，所以会向前找到比 22 小的记录（也就是 id 为 20 的记录），向后找到比 22 大的记录（也就是 id 为 25 的记录），然后在这两条记录之间加上间隙锁，锁的范围是`(20, 25)`。

### 2.4 唯一索引范围查询

范围查询和等值查询的加锁规则是不同的：当唯一索引进行范围查询时，会对每一个扫描到的索引加 Next-Key 锁。

而如果遇到下面这些情况，会退化成记录锁或间隙锁：

* *>=* 的范围查询：因为存在等值查询的条件，如果等值查询的记录是存在于表中，那么该记录的索引中的 Next_key 锁会退化成记录锁；
* *<=* 的范围查询：扫描到终止范围查询的记录时，当条件值的记录不在表中，该终止记录的索引上的 Next-Key 锁会退化成间隙锁，否则不会退化。而其他扫描到的记录都是在这些记录的索引上加 Next-Key 锁；
* *<* 条件的范围查询：不论条件值记录是否存在，扫描到终止范围查询的记录时，该记录的索引的 Next-Key 锁都会退化成间隙锁。

### 2.5 非唯一索引等值查询

当使用**非唯一索引进行等值查询**的时候，因为存在两个索引：**主键索引和非唯一索引**（二级索引），所以在加锁时候会**同时对这两个索引加锁**。但是对主键索引加锁的时候，只有满足查询条件的记录才会加锁。

非唯一索引等值查询时，查询的记录存不存在时加锁的规则也会不同：

* 存在：由于不是唯一索引，所以肯定存在索引值相同的记录，于是等值查询过程是一个扫描的过程，直到扫描到第一个不符合条件的二级索引记录就停止扫描。在扫描过程中，对扫描到的二级索引记录加的是 Next-Key 锁，而对于第一个不符合条件的二级索引记录，该二级索引的 Next-Key 锁会退化成间隙锁。同时在符合查询条件的记录的主键索引上加记录锁。
* 不存在：扫描到第一条不符合条件的二级索引记录，该二级索引的 Next-Key 锁会退化成间隙锁。因为不存在满足查询条件的记录，所以不会对主键索引加锁。

当非唯一索引上加了 X 型间隙锁时，插入值为边界值的新记录时，有些情况可以成功插入，有些情况则不行。这与二级索引树的记录存放方式有关。

二级索引树是按照二级索引值按顺序存放的，在二级索引值相同的情况下，再按主键 ID 的顺序存放。知道了这个前提才能知道插入语句的时候，插入位置的下一条记录是什么。

比如，事务 A 在二级索引`age = 39`上加了 X 型的间隙锁，范围为`(22, 39)`。假设该二级索引的第一条主键值为`id = 20`，此时其他事务插入一条新的`age = 39`记录的时候：

* 如果插入的值为`age = 39, id = 3`，由于该记录在二级索引树的插入位置在`age = 39, id = 20`之前，而该位置的二级索引上已经被加了间隙锁，所以这次插入将会被阻塞；
* 如果插入的值为`age = 39, id = 22`，由于该记录在二级索引树的插入位置在`age = 39, id = 20`之后，该位置的下一条记录不存在，也就没有间隙锁了，所以这条插入语句可以插入成功，不被阻塞。

### 2.6 非唯一索引范围查询

非唯一索引的范围查询和主键索引的范围查询的加锁也有所不同，主要在于：**非唯一索引范围查询，索引的 Next-Key Lock 不会退化成间隙锁或记录锁**。也就是说，非唯一索引进行范围查询时，对二级索引记录加锁加的都是 Next-Key 锁。

### 2.7 for update 加锁示例

在进行事务操作时，通过**`for update`**语句，MySQL 会**对查询结果集中的每一行数都添加排他锁（行锁、表锁）**，其他事务对该记录的更新与删除操作都会阻塞。

假设有个表 products，包含`id`、`type`和`name`三个列，其中`id`是主键，有如下几种加锁情况：

* 明确指定主键，并且有此行记录，为对应的行加行锁：

    ```sql
    SELECT * FROM products WHERE id = 3 FOR UPDATE;
    SELECT * FROM products WHERE id = 3 AND type = 1 FOR UPDATE;
    ```

* 明确指定主键，但无该记录，不加锁

    ```sql
    SELECT * FROM products WHERE id = -1 FOR UPDATE;
    ```

* 不指定主键，加表锁

    ```sql
    SELECT * FROM products WHERE name = 'Mouse' FOR UPDATE;
    ```

* 主键不明确，加表锁

    ```sql
    SELECT * FROM products WHERE id <> 1 FOR UPDATE;
    SELECT * FROM products WHERE id like '3' FOR UPDATE;
    ```


