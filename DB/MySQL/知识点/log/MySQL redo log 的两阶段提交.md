> 转摘：[美团二面：如何解决 bin log 与 redo log 的一致性问题](https://mp.weixin.qq.com/s/DyYQkaO-YLBqlpSXfbLJ0A?forceh5=1)

MySQL 中 bin log 与 redo log 一致性的问题就是通过 redo log 的两阶段提交来解决的。

### 1. redo log 的崩溃恢复

bin log 是 MySQL Server 层提供的功能，只能用于归档，不足以实现崩溃恢复(crash-safe)，需要借助 InnoDB 存储引擎的 redo log 才能拥有崩溃恢复的能力。

所谓崩溃恢复就是：即使在数据库宕机的情况下，也不会出现操作一半的情况。

redo log 和 bin log 两者的区别在于：

1. **适用对象不同**

    * bin log 是 MySQL 的 Server 层实现的，所有引擎都可以使用；
    * redo log 是 InnoDB 存储引擎实现的。

2. **写入内容不同**

    * bin log 是逻辑日志，记录的是这个语句的原始逻辑，比如：给`id = 1`这一行的 age 字段加 1；
    * redo log 是物理日志，记录的是在某个数据页上做了什么修改。

3. **写入方式不同**

    * bin log 是可以追加写入的，也就是说 bin log 文件写到一定大小后会切换到下一个文件，并不会覆盖之前的日志内容；
    * redo log 是循环写的，空间固定，可能会被用完。

可以看到，redo log 是和 bin log 是一个很大的区别就在于：一个是循环写，一个是追加写。

也就说 redo log 只会记录未刷入磁盘数据的日志，已经刷入磁盘的数据日志都会从 redo log 这个有限大小的日志文件里删除。

而 bin log 是追加日志，保存的是全量的日志。这就会导致一个问题，那就是没有标志能让 InnoDB 从 bin log 中判断哪些数据是已经刷入磁盘了，哪些数据还没有。

举个例子，bin log 记录了两条日志：

```
记录 1：给 id = 1 这一行的 age 字段加 1
记录 2：给 id = 1 这一行的 age 字段加 1
```

假设在记录 1 刷盘后，记录 2 还没有刷盘时，数据库崩溃。重启后，只通过 bin log，数据库是无法判断这两条记录哪条已经写入磁盘哪条还没有写入磁盘。此时不论是两条都恢复至内存还是都不恢复，对`id = 1`这行数据来说，都是不对的。

但 redo log 不一样，只要刷入磁盘的数据，都会从 redo log 中被抹掉。数据库重启后，直接把 redo log 中的数据都恢复至内存就可以了。

这就是为什么说 redo log 具有崩溃恢复的能力，而 bin log 不具备。

### 2. redo log 两阶段提交

对于更新语句来说，会按照 MySQL 查询语句的执行过程走一遍。不同的是，更新流程还涉及到两个重要的日志模块 bin log 和 redo log。

以下面这条简单的 SQL 语句为例，解释下执行器和 InnoDB 存储引擎在更新时做了哪些事情：

```sql
update table set age = age + 1 where id = 1
```

1. 执行器：找存储引擎取到`id = 1`的记录；

2. 存储引擎：根据主键索引树找到这一行。如果`id = 1`这一行所在的数据页本就在内存池(buffer pool)中，就话自己返回给执行器；否则需先从磁盘读入内存池，然后再返回；

3. 执行器：拿到存储引起返回的行记录，把 age 字段加上 1，得到一行新的记录，然后再调用存储引起的接口写入这行新纪录；

4. 存储引擎：将这行新数据更新到内存中，同时将这个更新操作记录到 redo log 里，此时 redo log 处于 **prepare** 状态。然后告知执行器执行完成了，随时可以提交事务；

    > 这里的提交事务和 SQL 语句中的 commit 提交事务命令是不同的：这里说的提交事务指的是事务提交过程中的一个小步骤，也是最后一步。当这个步骤执行完成后，commit 命令就执行成功了。

5. 执行器：生成这个操作的 bin log，并把 bin log 写入磁盘；

6. 执行器：调用存储引擎的提交事务接口；

7. 存储引擎：把刚刚写入的 redo log 状态改成提交 **commit** 状态，更新完成。

整个流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1642000486837-e12575911647.jpg)

可以看到，**所谓两阶段提交，其实就是把 redo log 的写入拆分成了两个步骤：prepare 和 commit**。

### 3. redo log 两阶段提交的原因

为什么要设计成两阶段提交呢？这样为什么能够实现崩溃恢复呢？

根据两阶段提交，**崩溃恢复时的判断规则**是这样的：

1. 如果 redo log 里面的事务是完整的，也就是已经有了 commit 标识，则直接提交；

2. 如果 redo log 里面的事务处于 prepare 状态，则判断对应的事务 bin log 是否存在且完整：
    
    * 如果 bin log 存在且完整，则提交事务；
    * 否则，回滚事务。

> MySQL 的 bin log 是具有完整格式的：
> 
> * statement 格式的 bin log 最后会有 COMMIT；
> * raw 格式的 bin log 最后会有 XID event。
> 
> 对于 bin log 可能会在中间出错的情况，MySQL 5.6.2 版本以后引入了`binlog-checksum`参数来验证 bin log 内容的正确性。

下面使用几个实际的例子进行说明。

#### 3.1 实例 1

如下图所示，假设数据库在写入 redo log 阶段之后、写入 bin log 之前，发生了崩溃。此时 redo log 里面的事务处于 prepare 状态，bin log 还没写，所以崩溃的时候这个事务会回滚。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642034779305-b7420561ecf6.jpg)

因为 bin log 还没写入，所以不会传到备库，之后从库进行同步的时候无法执行这个操作。如果不回滚的话，主库上就已经完成了这个操作。所以为了主备一致，在主库上需要回滚这个事务。

#### 3.2 实例 2

如果数据库在写入 bin log 之后、redo log 状态修改为 commit 前发生崩溃。此时 redo log 里面的事务仍然是 prepare 状态，bin log 存在并完整。所以这个时刻数据库崩溃了，恢复后事务仍然会被正常提交。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642035021452-c9e9ed53c061.jpg)

因为 bin log 已经写入成功了，这样之后就会被从库同步过去。如果恢复后主库没有提交该事务，那么主库上就没有完成这个操作。为了主备一致，在主库上就需要提交这个事务。

### 4. redo log 两阶段提交是否是必要的

redo log 的两阶段提交是否是必要的？可不可以先 redo log 写完，再写 bin log，或者反过来？

#### 4.1 先写完 redo log 再写 bin log

假设在 redo log 写完，bin log 还没有写完的时候，MySQL 崩溃。主库中的数据确实已经被修改了，但是这个时候 bin log 里面还没有记录这个语句。因此，从库同步的时候开就会丢失这个更新，和主库不一致。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642035344658-97f3fe44a65c.jpg)

#### 4.2 先写完 bin log 再写 redo log

如果 bin log 先写完，redo log 还没写的时候，MySQL 崩溃。因为 bin log 已经写入成功了，这样之后就会被从库同步过去，但是实际上 redo log 还没写完，主库并没有完成这一个操作。所以从库就会比主库多执行一个事务，导致主备不一致。

![](http://cnd.qiniu.lin07ux.cn/markdown/1642035433799-7621cc83387c.jpg)



