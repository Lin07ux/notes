> 转摘：[MySQL事务中的redo与undo](https://blog.csdn.net/weixin_43093328/article/details/115318878)

## 一、undo log 基础

undo log 是一种逻辑日志，主要记录的是数据的逻辑变化，为了能在发生错误时能够进行回滚恢复之前的数据，需要将对应的操作记录下来，然后在发生错误的时候能够进行逆操作，也就是回滚。

### 1.1 作用

undo log 的作用主要有两个：

* 事务回滚
* MVCC

MVCC 相关的处理流程可以参考 InnoDB 存储引擎实现可重复读隔离级别和解决幻读的相关知识。

对于事务回滚，undo log 实际上做的是相反的工作。比如对于 INSERT 操作，undo log 中对应的是一个 DELETE 操作；UPDATE 操作对应的是一条相反的 UPDATE，将修改前的行放回去。

undo log 用于事务的回滚操作，进而保障了事务的原子性。

### 1.2 类型

InnoDB 存储引擎中，undo log 分为：

* `insert undo log` 因为 insert 操作的记录只对事务本身可见，对其他事务不可见，故该 undo log 可以在事务提交后直接删除，不需要进行 purge 操作；

* `update undo log` 记录的是 update 和 delete 操作的逻辑，其可能需要提供 MVCC 机制，因此不能在事务提交时就进行删除。在提交时将其放入到 undo log 链表，等待 purge 线程进行最后的删除操作。

> purge 线程有两个主要作用：清理 undo 页和清除 page 中带有`DELETE_BIT`标识的数据行。
> 
> 在 InnoDB 中，事务中的 Delete 操作实际上并不是真正的删除掉数据行，而是一种 Delete Mark 操作，在记录上标识`DELETE_BIT`，是一种假删除。而真正的删除工作则由后台的 purge 线程去完成。

### 1.3 生成时机

**undo log 的生成时机是在DML 操作修改聚簇索引前，二级索引记录的修改不会记录 undo log**。

需要注意的是：undo 页面的修改同样需要记录 redo log。

### 1.4 存储位置

在 InnoDB 存储引擎中，undo log 存储在回滚段（Rollback Segment）中：

* 每个 Rollback Segment 中记录了 1024 个 undo log segment；
* 每个 undo log segment 中进行 undo page 的申请。

> 在 MySQL 5.6.3 之前，Rollback Segment 是在共享表空间里的，之后可通过`innodb_undo_tablespace`设置 undo log 的存储位置。

### 1.5 与 redo log 的区别

undo log 是逻辑日志，对事物回滚时只是将数据库逻辑地恢复到原来的样子。

redo log 是逻辑日志，记录的是数据页的物理变化。

因此，undo log 和 redo log 并不是互为逆过程。


