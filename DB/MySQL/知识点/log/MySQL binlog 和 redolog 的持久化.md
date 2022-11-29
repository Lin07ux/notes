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
> CPU 如果要访问外部磁盘上的文件，需要首先那将这些文件的内容拷贝到内存中，由于硬件的限制，从磁盘到内存的数据传输速度是很慢的。为了提升性能，会使用一些空闲的物理内存来对文件进行缓存，这部分用作缓存磁盘文件的内存就叫做 page cache。

`write`和`fsync`的时机是由参数`sync_binlog`控制的：

* `sync_binlog = 0` 每次提交事务的时候，只进行 write 不进行 fsync；
* `sync_binlog = 1` 每次提交事务的时候，都执行 write 和 fsync；
* `sync_binlog = N (N > 1)` 每次提交事务的时候，执行 write，累积 N 个事务提交后再执行 fsync。

可以看出来，如果业务场景涉及到 IO 操作很多的话，可以适当增大`sync_binlog`的值以提高 IO 性能。但是这样也有一定的 binlog 数据丢失风险。比如设置成 100 时，如果在第 80 个事务提交的时候服务器宕机了，那么这些事务的 binlog 日志由于没有执行 fsync 就都丢失了。



