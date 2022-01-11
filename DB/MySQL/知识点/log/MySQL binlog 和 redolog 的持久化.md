> [字节一面：事务还没提交的时候，redolog 能不能被持久化到磁盘呢？](https://mp.weixin.qq.com/s/GqYlRorRbYcnY_YwnCKZ-g)

数据持久化也就是将数据写到磁盘上，也称为落盘，目的就是可以在数据丢失后进行恢复，保证数据不丢失。

对于 MySQL 来说，只要 binlog 和 redolog 都能正确持久化到磁盘上，就可以保证数据不丢失了。

### 1. binlog 持久化

MySQL 为 binlog 设置了一个内存区域，充当缓存使用，也就是 binlog cache。每个线程都有自己的 binlog cache 区域。

**在事务运行的过程中，MySQL 会先把日志写到 binlog cache 中，等到事务真正提交的时候再统一把 binlog cache 中的数据写到 binlog 文件中**。（binlog cache 有很多个，但 binlog file 只有一个。）

事实上，这个从 binlog cache 写到 binlog file 的操作并不是已经落盘了，而仅仅是把 binlog 写到了文件系统的 page cache 上（这一步对应下图中的`write`操作）。所以最后需要把 page cache 中的数据同步到磁盘上，才算真正完成了 binlog 的持久化（这一步对应下图中的`sync`操作）。一般情况下，认为`fsync`才占磁盘的 IOPS(Input/Output Operations Per Second)：

![](http://cnd.qiniu.lin07ux.cn/markdown/1641865592593-48a3d28303e3.jpg)

> 简单解释下文件系统的 page cache：
> 
> CPU 如果要访问外部磁盘上的文件，需要首席那将这些文件的内容拷贝到内存中，由于硬件的限制，从磁盘到内存的数据传输速度是很慢的。为了提升性能，会使用一些空闲的物理内存来对文件进行缓存，这部分用作缓存磁盘文件的内存就叫做 page cache。

`write`和`fsync`的时机是由参数`sync_binlog`控制的：

* `sync_binlog = 0` 每次提交事务的时候，只进行 write 不进行 fsync；
* `sync_binlog = 1` 每次提交事务的时候，都执行 write 和 fsync；
* `sync_binlog = N (N > 1)` 每次提交事务的时候，执行 write，累积 N 个事务提交后再执行 fsync。

可以看出来，如果业务场景涉及到 IO 操作很多的话，可以适当增大`sync_binlog`的值以提高 IO 性能。但是这样也有一定的 binlog 数据丢失风险。比如设置成 100 时，如果在第 80 个事务提交的时候数据库宕机了，那么这些事务的 binlog 日志由于没有执行 fsync 就都丢失了。

### 2. redolog 持久化

类比 binlog，在事务执行过程中，redolog 也有类似 binlog cache 的内存区域，叫做 redolog buffer。但是 redolog buffer 只有一个，并非每个线程都单独一个。

**在事务运行的过程中，MySQL 会先把日志写到 redolog buffer 中，等到事务真正提交的时候，再同意把 redolog buffer 中的数据写到 redolog file 中**。

同样的，从 redolog buffer 写到 redolog file 的操作并不是真的就落盘了，也是写到文件系统的 page cache 上而已，最后还是需要执行`fsync`才能能够实现真正的落盘。

![](http://cnd.qiniu.lin07ux.cn/markdown/1641866071439-2d5d0721e99f.jpg)

为了控制 redo log 的写入策略，InnoDB 存储引擎提供了`innodb_flush_log_at_trx_commit`参数，有三个可用的值：

* `0` 每次事务提交的时候，都只是把 redolog 留在 redolog buffer 中；
* `1` 每次事务提交的时候，都执行`fsync`将 redolog 直接持久化到磁盘上；
* `2` 每次事务提交的时候，都只执行`write`将 redolog 写入到文件系统的 page cache 中。

### 3. 事务还没有提交的时候，redolog 能否被持久化到磁盘

那么事务还没有提交的时候，redolog 能不能被持久化到磁盘呢？是有可能的。

这是因为 InnoDB 有一个后台线程，每隔 1 秒轮询一次，具体的操作是这样的：调用`write`将 redolog buffer 中的日志写到文件系统的 page cache，然后调用`fsync`持久化到磁盘。

在事务执行中间过程的 redolog 都是直接卸载 redolog buffer 中的，也就是说，一个没有提交的事务的 redolog 也是有可能会被后台线程一起持久化到磁盘的。

另外，除了后台线程每秒一次的轮询操作外，还有两种场景会让一个没有提交的事务的 redolog 落盘：

1. `innodb_flush_log_at_trx_commit = 1`，这样并行的某个事务提交的时候就会顺带将这个事务的 redolog buffer 持久化到磁盘。

2. redolog buffer 占用的内存空间达到`innodb_log_buffer_size`参数设置的大小（默认为 8MB）一半的时候，后台线程就会主动写盘。不过由于这个事务并没有提交，所以这个写盘动作只是`write`到了文件系统的 page cache，仍然是在内存中，并没有调用`fsync`真正的落盘。


