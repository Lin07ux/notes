> 转摘：
> 1. [面试问烂的MySQL四种隔离级别，看完吊打面试官！](http://database.51cto.com/art/201904/595641.htm)
> 2. [高级玩家必备：深度剖析 MySQL 事务隔离！](https://mp.weixin.qq.com/s/E-iJRPt5YRdLUja8RwzZ3A)

## 一、介绍

事务是应用程序中一系列严密的操作，所有操作必须成功完成，否则在每个操作中所作的所有更改都会被撤消。也就是事务具有原子性，一个事务中的一系列的操作要么全部成功，要么一个都不做。

事务的结束有两种：当事务中的所以步骤全部成功执行时事务提交；如果其中一个步骤失败，将发生回滚操作，撤消撤消之前到事务开始时的所以操作。

事务具有四个特征：原子性( Atomicity )、一致性( Consistency )、隔离性( Isolation )和持续性( Durability )。这四个特性简称为 ACID 特性。

* 原子性：事务是数据库的逻辑工作单位，事务中包含的各操作要么都做，要么都不做。
* 一致性：事务执行的结果必须是使数据库从一个一致性状态变到另一个一致性状态。因此当数据库只包含成功事务提交的结果时，就说数据库处于一致性状态。如果数据库系统运行中发生故障，有些事务尚未完成就被迫中断，这些未完成事务对数据库所做的修改有一部分已写入物理数据库，这时数据库就处于一种不正确的状态，或者说是不一致的状态。
* 隔离性：一个事务的执行不能被其它事务干扰。即一个事务内部的操作及使用的数据对其它并发事务是隔离的，并发执行的各个事务之间不能互相干扰。
* 持续性。也称永久性，指一个事务一旦提交，它对数据库中的数据的改变就应该是永久性的。接下来的其它操作或故障不应该对其执行结果有任何影响。

SQL 定义了隔离级别来确保不同程度的隔离性。

### 1.1 隔离级别

SQL 标准定义了 4 类隔离级别，包括了一些具体规则，用来限定事务内外的哪些改变是可见的，哪些是不可见的。低级别的隔离级一般支持更高的并发处理，并拥有更低的系统开销。

**Read Uncommitted(读取未提交内容)**

在该隔离级别，所有事务都可以看到其他未提交事务的执行结果。本隔离级别很少用于实际应用，因为它的性能也不比其他级别好多少。读取未提交的数据，也被称之为脏读(Dirty Read)。

**Read Committed(读取提交内容)**

这是大多数数据库系统的默认隔离级别(但不是 MySQL 默认的)。它满足了隔离的简单定义：一个事务只能看见已经提交事务所做的改变。这种隔离级别也支持所谓的不可重复读(Nonrepeatable Read)，因为同一事务的其他实例在该实例处理其间可能会有新的提交，所以同一读取可能返回不同结果。

**Repeatable Read(可重复读)**

这是 MySQL 的默认事务隔离级别，它确保同一事务的多个实例在并发读取数据时，会看到同样的数据行。不过理论上，由于一般不会要求范围锁，所以会导致另一个棘手的问题：幻读 (Phantom Read)。简单的说，幻读指当用户读取某一范围的数据行时，另一个事务又在该范围内插入了新行，当用户再读取该范围的数据行时，会发现有新的“幻影” 行。InnoDB 和 Falcon 存储引擎通过多版本并发控制(MVCC，Multiversion Concurrency Control)机制解决了该问题。

**Serializable(可串行化)**

这是最高的隔离级别，它通过强制事务排序，使之不可能相互冲突，从而解决幻读问题。简言之，它是在每个读的数据行上加上共享锁。在这个级别，可能导致大量的超时现象和锁竞争。

InnoDB 存储引擎分别通过如下方式实现事务的 ACID 特性：

* 持久性是通过 redo log 来保证的；
* 原子性是通过 undo log 来保证的；
* 隔离性是通过 MVCC 和锁机制来保证的；
* 一致性则是通过持久性+原子性+隔离性来保证的。

### 1.2 隔离问题

不同的隔离级别采取不同的锁类型来实现，对同一个读取语句，可能会出现不同的问题。

* 脏读(Drity Read)：某个事务已更新一份数据，另一个事务在此时读取了同一份数据，由于某些原因，前一个 RollBack 了操作，则后一个事务所读取的数据就会是不正确的。
* 不可重复读(Non-repeatable read)：在一个事务的两次查询之中数据不一致，这可能是两次查询过程中间插入了一个事务更新的原有的数据。
* 幻读(Phantom Read)：在一个事务的两次查询中数据笔数不一致，例如有一个事务查询了几列(Row)数据，而另一个事务却在此时插入了新的几列数据，先前的事务在接下来的查询中，就有几列数据是未查询出来的，如果此时插入和另外一个事务插入的数据，就会报错。

其中，幻读和不可重复读的区别在于：

* **不可重复读的重点是修改**：在同一事务中，同样的条件，第一次读取的数据和第二次读取的数据不一样（因为中间有其他事务提交了修改）。
* **幻读的重点在于新增或删除**：在同一事务中，同样的条件，第一次和第二次读出来的记录数量不一样（因为中间有其他事务提交了插入/删除操作）。

MySQL 中不同隔离级别可能产生的问题如下表所示：

 隔离级别          | 脏读  | 不可重复读 | 幻读
----------------- | ---- | -------- | -----
 Read uncommitted | Yes  | Yes      | Yes
 Read committed   | No   | Yes      | Yes
 Repeatable read  | No   | No       | Yes
 Serializable     | No   | No       | No

## 二、测试

数据表如下：

```sql
create table `test` (
  `id` int(11) unsigned not null auto_increment,
  `num` int(11) not null default 0,
  primary key(`id`)
) engine=InnoDB auto_increment=1 default charset=utf8;
```

填充数据如下：

```shell
mysql> insert into test (`num`) values (1), (2), (3);
mysql> select * from test;
+----+-----+
| id | num |
+----+-----+
|  1 |   1 |
|  2 |   2 |
|  3 |   3 |
+----+-----+
3 rows in set (0.00 sec)
```

然后开两个命令行终端，称为 A 和 B，分别在其中进行 sql 语句的执行。

### 2.1 Read uncommitted

1. A 隔离级别设置为 Read uncommitted，然后查看数据：

    ```
    mysql> set session transaction isolation level uncommitted;
    Query OK, 0 rows affected (0.00 sec)
        
    mysql> select @@tx_isolation;
    +------------------+
    | @@tx_isolation   |
    +------------------+
    | READ-UNCOMMITTED |
    +------------------+
    1 row in set, 1 warning (0.00 sec)
        
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
        
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

2. B 启动事务，更新数据，但不提交：

    ```shell
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> update test set num = 10 where id = 1;
    Query OK, 1 row affected (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

3. A 查看数据，可以看到数据已经被修改了，这就是所谓的脏读：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

4. B 回滚事务：

    ```shell
    mysql> rollback;
    Query OK, 0 rows affected (0.01 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

5. A 再次读取数据，发现数据变回初始状态：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

经过上面的实验可以得出结论，事务 B 更新了一条记录，但是没有提交，此时事务 A 可以查询出未提交记录，造成脏读现象。未提交读是最低的隔离级别。

### 2.2 Read committed

1. A 将隔离级别设置为 Read committed，并开启事务：

    ```shell
    mysql> set session transaction isolation level read committed;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select @@tx_isolation;
    +----------------+
    | @@tx_isolation |
    +----------------+
    | READ-COMMITTED |
    +----------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

2. B 启动事务，更新数据，但不提交：

    ```shell
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> update test set num = 10 where id = 1;
    Query OK, 1 row affected (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

3. A 再次读取数据，可以看到数据未被修改：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

4. B 提交事务

    ```shell
    mysql> commit;
    Query OK, 0 rows affected (0.01 sec)
    ```

5. A 再次读取数据，会发现数据已经发生变化，说明 B 提交的修改事务在 A 的事务中已经可以读到了，这就是所谓的不可重复读：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

经过上面的实验可以得出结论，已提交读隔离级别解决了脏读的问题，但是出现了不可重复读的问题，即事务 A 在两次查询的数据不一致，因为在两次查询之间事务 B 更新了一条数据。已提交读只允许读取已提交的记录，但不要求可重复读。

### 2.3 Repeatable read

1. A 设置隔离级别为 Repeatable read，并开启事务：

    ```shell
    mysql> set session transaction isolation level repeatable read;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select @@tx_isolation;
    +-----------------+
    | @@tx_isolation  |
    +-----------------+
    | REPEATABLE-READ |
    +-----------------+
    1 row in set, 1 warning (0.00 sec)
    
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

2. B 启动事务，更新数据，但不提交：

    ```shell
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> update test set num = 10 where id = 1;
    Query OK, 1 row affected (0.00 sec)
    Rows matched: 1  Changed: 1  Warnings: 0
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.01 sec)
    ```

3. A 查看数据，可以看到未发生变化：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

4. B 提交事务，数据已更改：

    ```shell
    mysql> commit;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

5. A 查看数据，依旧未发生变化，说明是可重复读的了：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

6. B 插入一条新数据：

    ```shell
    mysql> insert into test (`num`) value (4);
    Query OK, 1 row affected (0.01 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    |  4 |   4 |
    +----+-----+
    4 rows in set (0.00 sec)
    ```

7. A 查看数据，依旧没有发生变化，说明没有幻读现象。这是由于 InnoDB 通过 MVCC 避免了幻读。如果出现幻读时，下面的语句的结果应该包含最新插入的数据：

    ```shell
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

8. A 提交事务，并查看数据，可以看到已经是最新数据了：

    ```shell
    mysql> commit;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    |  4 |   4 |
    +----+-----+
    4 rows in set (0.00 sec)
    ```

由以上的实验可以得出结论，可重复读隔离级别只允许读取已提交记录，而且在一个事务两次读取一个记录期间，其他事务部对该记录的更新不会被看到。虽然可重复读的隔离界别会出现幻读现象，但是 MySQL 的 InnoDB 引擎避免了幻读问题。

### 2.4 Serializable

1. A 设置隔离级别为 Serializable，并查看初始数据：

    ```shell
    mysql> set session transaction isolation level serializable;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    |  4 |   4 |
    +----+-----+
    4 rows in set (0.00 sec)
    ```

2. B 开启事务，并删除数据，可以看到此时进入了等待状态，因为 A 事务尚未提交(B 可能会发生超时)：

    ```shell
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> insert into test (`num`) value (5);
    
    ```

3. A 提交事务

    ```shell
    mysql> commit;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    |  4 |   4 |
    +----+-----+
    4 rows in set (0.00 sec)
    ```

4. B 插入提示成功

    ```shell
    mysql> insert into test (`num`) value (5);
    Query OK, 1 row affected (5.10 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |  10 |
    |  2 |   2 |
    |  3 |   3 |
    |  4 |   4 |
    |  5 |   5 |
    +----+-----+
    5 rows in set (0.00 sec)
    ```

Serializable 完全锁定字段，若一个事务来查询同一份数据就必须等待，直到前一个事务完成并解除锁定为止。是完整的隔离级别，会锁定对应的数据表格，因而会有效率的问题。

## 三、重复读和幻读

InnoDB 在可重复读的事务隔离级别中，会通过 MVCC 和`next-key`锁机制来保证重复读和幻读的问题：

* 利用 MVCC 实现一致性非锁定读，保证在同一个事务中多次读取相同的数据返回的结果是一样的，解决了不可重复读问题。
* 利用`Next-key`锁或`Gap Locks`(间隙锁)可以阻止其他事务在锁定区间内插入数据，解决了幻读问题。

### 3.1 MVCC

MVCC(Multi Version Concurrency Control)，即多版本并发控制。简单说就是为了查询一些正在被另一个事务更新的行，并且可以看到它们被更新之前的值。这是一个用来增强并发性的强大技术，可以使得查询不用等待另一个事务释放锁。

在 InnoDB 中，MVCC 机制给每行增加两个隐藏字段：一个用来记录数据行的创建事务版本号，另一个用来记录行的过期事务版本号。MySQL InnoDB 每开启一个新事务，事务的版本号就会递增。

增删改查中 MVCC 事务版本号的作用如下：

* `select` 读取创建版本小于或等于当前事务版本号，并且删除版本为空或大于当前事务版本的记录，这样可以保证在读取之前记录都是存在的。
* `insert` 将当前事务的版本号保存至行的创建版本号。
* `update` 新插入一行，并以当前事务版本号作为新行的创建版本号，同时将原记录行的删除版本号设置为当前事务版本号。
* `delete` 将当前事务版本号保存至行的删除版本号。

事务每次取数据的时候会将读取历史数据存一份快照，所以其他事务增加与删除的数据对于当前事务来说是不可见的。

### 3.2 next-key

MySQL 中的读其实是分为快照读(一致性读)和当前读两种类别，这两种读的方式和结果都不同，也会因此而造成不一样的效果。

* 快照读：读取的是快照版本(历史版本)，也就是按照 MVCC 事务版本号规则读取的数据。
* 当前读：读取的是最新版本数据，也就是被更新、删除、插入后的数据，使用`next-key`锁来实现。

普通的`select`是快照读，而`update`、`delete`、`insert`、`select ... lock in share mode`、`select...for insert`、`select...for update`、`select...for delete`就是当前读。

`next-key`是将当前数据行与上一条数据和下一条数据之间的间隙锁定，保证此范围内读取的数据是一致的。`next-key`锁包含两部分：

* 记录锁（行锁）：加在索引上的锁
* 间隙锁：加在索引之间的锁

假如一个索引包含以下几个值：10，11，13，20，那么这个索引的`next-key`锁将会覆盖以下区间：

```
(negative infinity, 10]
(10, 11]
(11, 13]
(13, 20]
(20, positive infinity)
```

如果对一个唯一索引使用了唯一的检索条件，那么只需要锁定相应的索引记录就好；如果是没有使用唯一索引作为检索条件，或者用到了索引范围扫描，那么将会使用间隙锁或者`next-key`锁来以此阻塞其他会话向这个范围内的间隙插入数据。

`next-key`虽然很好的解决了幻读问题，但是其所加的锁会导致并发性能较低。
    
### 3.3 示例

在上面的 Repeatable Read 测试中，如果使用如下的测试流程则会有不同的结果：

1. A 事务开启并查询数据：

    ```
    mysql> start transaction;
    Query OK, 0 rows affected (0.00 sec)
   
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    ```

2. B 中插入新数据：

    ```    
    mysql> insert into test (`num`) value (4);
    Query OK, 1 row affected (0.01 sec)
        
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    |  4 |   4 |
    +----+-----+
    ```

3. A 事务查看数据，依旧是只有三条，但如果使用范围更新，然后再查询，B 中插入的数据就会出现了：

    ```
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   1 |
    |  2 |   2 |
    |  3 |   3 |
    +----+-----+
    3 rows in set (0.00 sec)
    
    mysql> update test set num = num + 1;
    Query OK, 4 row affected (0.01 sec)
    
    mysql> select * from test;
    +----+-----+
    | id | num |
    +----+-----+
    |  1 |   2 |
    |  2 |   3 |
    |  3 |   4 |
    |  4 |   5 |
    +----+-----+
    4 rows in set (0.00 sec)
    ```

出现这种结果的原因就是 MySQL 对快照读和当前读采用的是不同的锁机制导致的：

**在快照读时，MySQL 使用 MVCC 机制来避免幻读，而在当前读读情况下，则是通过`next-key`来避免幻读。**

所以，对于上面的示例，由于 A 事务 update 前后的 select 分别是快照读和当前读，所以读取的结果就是不同的。如果一开始 A 事务使用共享锁方式读取，就会添加`next-key`锁，此时 B 事务是没有办法插入新的数据的，只能等到 A 事务提交之后 B 事务才能完成插入。


