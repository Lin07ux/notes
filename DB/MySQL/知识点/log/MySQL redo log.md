> 转摘：
> 
> 1. [MySQL事务中的redo与undo](https://blog.csdn.net/weixin_43093328/article/details/115318878)
> 2. [字节一面：事务还没提交的时候，redolog 能不能被持久化到磁盘呢？](https://mp.weixin.qq.com/s/GqYlRorRbYcnY_YwnCKZ-g)
> 3. [美团二面：如何解决 bin log 与 redo log 的一致性问题](https://mp.weixin.qq.com/s/DyYQkaO-YLBqlpSXfbLJ0A)

## 一、redo log 基本介绍

### 1.1 作用

redo log 用来保证事务的持久性，即事务 ACID 中的 D，其主要作用是用于数据库的崩溃恢复，是由 InnoDB 存储引擎提供的能力。

通过 redo log 的两阶段提交，使得 MySQL 在崩溃后的恢复中，能够进行事务的恢复或者回滚，从而保证数据一致，也能保证主从一致。

### 1.2 类型

redo log 可以分为以下两种类型：

* **物理 redo log**：记录数据页的物理变化，DML 对页的修改操作都需要记录 redo log；
* **逻辑 redo log**：记录修改页面的操作，如新建数据页时，记录这个新建操作。

在 InnoDB 存储引擎中，大部分情况下，redo log 都是物理日志，而逻辑 redo log 涉及到的是更底层的内容。

> 在记录 undo log 时，也要记录对应的 redo log。

### 1.3 组成

redo log 可以简单分为以下两部分：

* redo log buffer：在内存中，易失的；
* redo log file：在磁盘中，持久的。

redo log 先在内存的 buffer 中生成，然后在一定的时机刷入到磁盘中。这样可以协调效率和安全性的制约。

### 1.4 整体流程

下面是一个 update 事务中，redo log 的整体流转过程：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669643532)

1. 将原始数据从磁盘中读入到内存中，然后修改数据的内存拷贝；
2. 生成一条 redo log 并写入到 redo log buffer 中，记录的是数据被修改后的值，此时这条 redo log 为 prepare 状态；
3. 当事务 commit 的时候，将 redo log buffer 中的内容采用追加方式刷入到 redo log file 中，此时这条 redo log 为 commit 状态。

> 将 redo log buffer 的内容刷入到 redo log file 的过程中，可能会经过操作系统的 page cache 处理，而不是立即写入到磁盘中的。但是这并不影响 redo log 的状态改为 commit。

### 1.5 与 bin log 的不同

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

redo log 是循环写，只会记录未刷入磁盘数据的日志，已经刷入磁盘的数据日志都会从 redo log 这个有限大小的日志文件里删除。而 bin log 是追加写，保存的是全量的日志。

## 二、redo log 工作原理

redo log 能够实现数据持久化和崩溃恢复，其核心是两阶段提交流程。

所谓的**两阶段提交，其实就是把 redo log 的写入分拆成两个步骤：prepare 和 commit**。这两个步骤对应着 redo log 的两个状态，InnoDB 存储引擎能够通过 redo log 状态来确定事务是否已经完成，并结合对应的 bin log 即可实现事务的持久化和崩溃恢复。

### 2.1 两阶段提交

下面以一个更新语句来说明 redo log 的两阶段提交流程，更新语句如下：

```sql
update table set age = age + 1 where id = 1
```

MySQL 中，更新语句也会按照查询语句的执行流程走一遍，然后进行数据更新操作，并生成对应的 bin log 和 redo log（以及 undo log）。下面是上述 SQL 语句在 MySQL 中的执行流程：

1. 执行器：调用存储引擎的 API 去取`id = 1`的记录；

2. 存储引擎：根据主键索引树找到这一行所在的页，如果该页已在内存池（buffer pool）中就直接返回给执行器，否则先从磁盘中将该页读入到内存池后再返回给执行器；

3. 执行器：拿到存储引擎返回的行记录，把`age`字段加上 1，得到一行新的记录，再调用存储引擎 API 写入这行新纪录；

4. 存储引擎：将这行新数据更新到内存中，同时为这个更新操作生成的 redo log，其状态设置为**prepare**。然后告知执行器，新纪录写入操作已执行，随时可以提交事务；

    > 这里的提交事务和 SQL 语句中的`commit`提交事务命令是不同的：这里是指事务提交过程中的一个小步骤，被称为 mini-transcation。

5. 执行器：生成这个操作的 bin log，并把 bin log 写入磁盘；

    > bin log 是由 Server 层生成的，和存储引擎层关系不大。

6. 执行器：调用存储引擎中的提交事务的 API；

7. 存储引擎：把前面生成的 redo log 的状态改为**commit**，更新完成。

流程如如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1642000486837-e12575911647.jpg)

### 2.2 崩溃恢复

崩溃恢复是指：在数据库宕机的情况下，通过一些方式对数据进行复原或回滚，不会出现操作一半的情况。

举个例子，bin log 记录了两条日志：

```
记录 1：给 id = 1 这一行的 age 字段加 1
记录 2：给 id = 1 这一行的 age 字段加 1
```

假设在记录 1 所在页面刷盘后，记录 2 所在页面还没有刷盘时，数据库崩溃。重启后，只通过 bin log，数据库是无法判断这两条记录哪条已经写入磁盘哪条还没有写入磁盘，因为 bin log 是 Server 层记录的日志，且没有相关标识。此时不论是两条都恢复至内存还是都不恢复，对`id = 1`这行数据来说，都是不对的。

但 redo log 不一样，根据两阶段提交，**崩溃恢复时的判断规则**是这样的：

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

* **实例 1**

    如下图所示，假设数据库在写入 redo log 阶段之后、写入 bin log 之前，发生了崩溃。此时 redo log 里面的事务处于 prepare 状态，bin log 还没写，所以崩溃的时候这个事务会回滚。

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1642034779305-b7420561ecf6.jpg)

    因为 bin log 还没写入，所以不会传到备库，之后从库进行同步的时候无法执行这个操作。如果不回滚的话，主库上就已经完成了这个操作。所以为了主备一致，在主库上需要回滚这个事务。

* **实例 2**

    如果数据库在写入 bin log 之后、redo log 状态修改为 commit 前发生崩溃。此时 redo log 里面的事务仍然是 prepare 状态，bin log 存在并完整。所以这个时刻数据库崩溃了，恢复后事务仍然会被正常提交。

    ![](http://cnd.qiniu.lin07ux.cn/markdown/1642035021452-c9e9ed53c061.jpg)

    因为 bin log 已经写入成功了，这样之后就会被从库同步过去。如果恢复后主库没有提交该事务，那么主库上就没有完成这个操作。为了主备一致，在主库上就需要提交这个事务。

### 2.3 两阶段提交的必要性

redo log 的两阶段提交是否是必要的？可不可以先 redo log 写完，再写 bin log，或者反过来？

1. 先写完 redo log 再写 bin log

    假设在 redo log 写完，bin log 还没有写完的时候，MySQL 崩溃。主库中的数据确实已经被修改了，但是这个时候 bin log 里面还没有记录这个语句。因此，从库同步的时候开就会丢失这个更新，和主库不一致。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1642035344658-97f3fe44a65c.jpg)
    
2. 先写完 bin log 再写 redo log

    如果 bin log 先写完，redo log 还没写的时候，MySQL 崩溃。因为 bin log 已经写入成功了，这样之后就会被从库同步过去，但是实际上 redo log 还没写完，主库并没有完成这一个操作。所以从库就会比主库多执行一个事务，导致主备不一致。
    
    ![](http://cnd.qiniu.lin07ux.cn/markdown/1642035433799-7621cc83387c.jpg)

## 三、redo log 的写入流程

InnoDB 存储引擎通过 Force Log at Commit 机制来实现事务的持久性，即：当事务提交时，先将 redo log buffer 写入到 redo log file 中进行持久化，待事务的 commit 操作完成时才算完成。这种做法也被称为 Write-Ahead Log（预先日志持久化），在持久化一个数据页之前，先将内存中相应的变更日志持久化。

redo log 生成时是先写在内存中的 redo log buffer 中的，其实现则跟 mini-transcation 密切相关。

### 3.1 mini-transaction

mini-transcation 是 InnoDB 存储引擎内部使用的一种机制，通过它来保证并发事务操作时和数据库异常时，数据页中数据的一致性，但是它不属于事务。

每个 mini-transcation 都对应一条 DML 操作，比如一条 update 语句。mini-transcation 对数据修改时，会将产生的 redo log 先放在其私有的 buffer 中，待语句执行结束后，再将 redo log 从私有 buffer 拷贝到共有的 redo log buffer 中。

而一个事务可能会有多条 DML 语句，每个 DML 语句对应的 mini-transcation 都按照相同的流程处理。当整个事务提交时，再统一将 redo log buffer 刷入到 redo log file 中。

![](https://cnd.qiniu.lin07ux.cn/markdown/1669645892)

mini-transcation 为保证数据页数据的一致性，遵循以下三种协议：

* **The FIX Rules**

    修改一个数据页时需要获得该页的 x-lock（排它锁），获取一个数据页时需要获得该页的 s-lock（读锁/共享锁）或 x-lock，并持有该锁直到修改或访问该页的操作完成。
    
* **Write-Ahead Log**

    在持久化一个数据页之前，必须先将内存中相应的日志持久化。每个页都有一个 LSN，当需要将数据页写入到持久化设备之前，要求内存中小于该页的 LSN 的日志先写入持久化设备。
    
    > LSN 即为 Log Sequence Number，代表日志序列号，占用 8 字节，单调递增。

* **Force-log-at commit**

    在一个事务中，可以修改多个页，Write-Ahead Log 可以保证单个数据页的一致性，但是无法保证事务的持久性。Force-log-at-commit 则要求当一个事务提交时，其产生的所有的 mini-transcation 日志必须刷新到磁盘中。
    
    若日志刷新完成后，在缓冲池中的页刷新到持久化存储设备前，数据库发生了宕机，那么数据库重启时，可以通过日志来保证数据的完整性。

### 3.2 redo log buffer

一个事务中的 mini-transaction 会将其生成的 redo log 都放入到 redo log buffer 中，然后在事务 commit 的时候再统一写入到 redo log file 中。

**redo log buffer 只有一个**，所有的线程都共用这个 buffer。也就是说，不同事务并发进行时，其 mini-transaction 产生的 redo log 可能会交错的存放到 redo log buffer 中。

由于不同事务共用一个 redo log buffer，所以就可能出现事务还没有提交，但是其对应的 redo log 已经被持久化到磁盘了：

* 设置`innodb_flush_log_at_trx_commit = 1`时，并行的某个事务提交时，会将其他事务的 redo log 一起从 redo log buffer 中持久化到磁盘中；
* redo log buffer 占用的内存空间达到`innodb_log_buffer_size`参数设置的大小（默认为 8MB）一半的时候，后台线程就会主动写盘。不过这个写盘动作只是写到了文件系统的 Page Cache，还没有真正的调用落盘。
* InnoDB 的后台线程每隔 1 秒轮询一次，将 redo log buffer 中的日志写到文件系统的 Page Cache，然后调用`fsync`持久化到磁盘。

### 3.3 redo log 持久化

因为 redo log file 没有打开`O_DIRECT`选项，所以 redo log 写入到存储设备之前，会先写入到文件系统缓存中。

将数据从内存中写入到磁盘中，一般会使用`write`操作将数据写到系统的页面缓存，然后再通过`fsync`操作将数据提交到硬盘中，强制硬盘同步，而且会一直阻塞到写入硬盘完成。

为了保证 redo log 能够正常的写入 redo log file 中，默认情况下，在每次将 redo log buffer 写入 redo log file 时，InnoDB 存储引擎都需要调用一次`fsync`系统调用。

> `O_DIRECT`选项是 Linux 系统中提供的，使用该选项后，可以对文件直接进行 IO 操作，不需要经过文件系统缓存，直接写入磁盘。
> 
> `fsync`作为一种系统调用操作，其效率取决于磁盘的性能，因此磁盘的性能也影响了事务提交的性能，即数据库性能。

其流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641866071439-2d5d0721e99f.jpg)

大量进行`fsync`操作会有性能瓶颈，所以对于写多的场景，每次 redo log file 的写入都需要调用`fsync`会造成数据库的吞吐量大大降低。为此，InnoDB 存储引擎提供了`innodb_flush_log_at_trx_commmit`参数来控制 redo log 刷新到磁盘的策略，其可选值如下：

* `1` 默认值，表示事务提交时必须调用一次`fsync`操作，是最安全的配置，保障持久性；
* `2` 表示事务提交时只进行`write`操作，只保证将 redo log buffer 写到系统的页面缓存中，不进行`fsync`操作。因此，如果 MySQL 出现宕机时并不会丢失事务，但操作系统宕机则可能会丢失事务；
* `0` 表示事务提交时不进行写入 redo log 的操作，这个操作仅在 master thread 中完成，其每 1 秒会进行一次 redo log 的`fsync`操作。因此，MySQL 宕机时最多会丢失 1 秒内的事务。

> master thread 负责将缓冲池中的数据异步刷新到磁盘，保证数据的一致性。

