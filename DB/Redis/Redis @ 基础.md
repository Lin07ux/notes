## 概述

Redis(Remote Dictionary Server)是一个开源的，**基于内存**的结构化数据存储媒介，可以作为数据库、缓存服务或消息服务使用。

Redis 支持多种数据结构，包括字符串(String)、哈希表(Hash)、链表(List)、集合(Set)、有序集合(ZSet)、位图(Bitmap)、Hyperloglogs 等。

Redis 具备 **LRU 淘汰**、**事务实现**、以及不同级别的**硬盘持久化**等能力，并且支持副本集和通过 Redis Sentinel 实现的高可用方案，同时还支持通过 Redis Cluster 实现的数据自动分片能力。

Redis 的主要功能都基于**单线程**模型实现，也就是说 Redis 使用一个线程来服务所有的客户端请求。同时 Redis 采用了**非阻塞式 IO**，并精细地优化各种命令的算法时间复杂度，这些信息意味着：

* Redis 是线程安全的（因为只有一个线程），其所有操作都是原子的，不会因并发产生数据异常。
* Redis 的速度非常快（因为使用非阻塞式 IO，且大部分命令的算法时间复杂度都是`O(1)`)。
* 使用高耗时的 Redis 命令是很危险的，会占用唯一的一个线程的大量处理时间，导致所有的请求都被拖慢。（例如时间复杂度为`O(N)`的 KEYS 命令，严格禁止在生产环境中使用）。

### 高性能的原因

1. 纯内存访问：Redis 将所有数据放在内存中，这是 Redis 达到每秒万级别访问的重要基础。

2. 非阻塞 I/O：Redis 使用 epoll 作为 I/O 多路复用技术的实现，再加上 Redis 自身的事件处理模型将 epoll 中的连接、读写、关闭都转换为事件，不在网络 I/O 上浪费过多的时间。

3. 单线程：单线程避免了线程切换和竞态产生 的消耗。

4. 高效的数据结构：空间预分配、惰性空间释放。

5. 高效的数据编码：Redis 会根据存储的数据类型和格式，自动进行最好的存储方式，比如会将较少的 list、hash、zset 转成 ziplist 存储，节省空间，加快访问速度。


## 数据结构

Redis 支持多种数据类型，但采用的是 Key-Value 型的基本数据结构，任何二进制序列都可以作为 Redis 的 Key 使用（例如普通的字符串或一张 JPEG 图片）。

关于 Key 的一些注意事项：

* **不要使用过长的 Key**。例如使用一个 1024 字节的 key 就不是一个好主意，不仅会消耗更多的内存，还会导致查找的效率降低；
* **Key 应保证一定的可读性**，例如`u1000flw`比起`user:1000:followers`来说，节省了寥寥的存储空间，却引发了可读性和可维护性上的麻烦；
* **最好使用统一的规范来设计 Key**，比如`"object-type:id:attr"`，以这一规范设计出的 Key 可能是`"user:1000"`或`"comment:1234:reply-to"`；
* Redis 允许的**最大 Key 长度是 512MB**（对 Value 的长度限制也是 512MB）。

Redis 中的数据类型有多种实现方式：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558875364139.png)

### String

String 是 Redis 的基础数据类型，Redis 没有 Int、Float、Boolean 等数据类型的概念，所有的基本类型在 Redis 中都以 String 体现。

String 有三种编码格式：

* `int` 整数值，这个整数值可以使用`long`类型来表示
* `embstr` 字符串值，这个字符串值的长度小于 39 字节，也可以用于存储 float 浮点数。
* `raw` 字符串值，这个字符串值的长度大于 39 字节，也可以用于存储 float 浮点数。

`embstr`和`raw`的区别在于：

* `raw`分配内存和释放内存的次数是两次，`embstr`是一次；
* `embstr`编码的数据保存在一块连续的内存里面。

编码之间的转换：

* `int`类型如果存的不再是一个整数值，则会从`int`转成`raw`；
* `embstr`是只读的，在修改的时候回从`embstr`转成`raw`。

与 String 相关的常用命令：

* `SET`：为一个 key 设置 value，可以配合`EX/PX`参数指定 key 的有效期，通过`NX/XX`参数针对 key 是否存在的情况进行区别操作，时间复杂度`O(1)`。
* `GET`：获取某个 key 对应的 value，时间复杂度`O(1)`。
* `GETSET`：为一个 key 设置 value，并返回该 key 原本的 value，时间复杂度`O(1)`。
* `MSET`：为多个 key 分别设置 value，时间复杂度`O(N)`。
* `MSETNX`：同`MSET`，如果指定的 key 中有任意一个已存在，则不进行任何操作，时间复杂度`O(N)`。
* `MGET`：获取多个 key 对应的 value，时间复杂度`O(N)`。
* `INCR`：将 key 对应的 value 值自增 1，并返回自增后的值。只对可以转换为整型的 String 数据起作用。时间复杂度`O(1)`。
* `INCRBY`：将 key 对应的 value 值自增指定的整型数值，并返回自增后的值。只对可以转换为整型的 String 数据起作用。时间复杂度`O(1)`。
* `DECR/DECRBY`：同`INCR/INCRBY`，自增改为自减。

自增/自减系列的命令要求操作的 value 类型为 String，并可以转换为 64 位带符号的整型数字，否则会返回错误。也就是说，进行 INCR/DECR 系列命令的 value，必须在`[-2^63 ~ 2^63 - 1]`范围内。

由于 Redis 采用单线程模型，天然是线程安全的，所以自增/自减命令可以非常便利的实现高并发场景下的精确控制。

### List

Redis 的 List 是双向链表型的数据结构，可以使用`LPUSH/RPUSH/LPOP/RPOP`等命令在 List 的两端执行插入元素和弹出元素的操作。虽然 List 也支持在特定 index 上插入和读取元素的功能，但其时间复杂度较高（`O(N)`），应小心使用。

**如果不是想要实现一个双端出入的队列，那么尽量不要使用 Redis 的 List 数据结构。**

List 类型有两种编码格式：

* `ziplist` 字符串元素的长度都小于 64 个字节并且总数量少于 512 个，如果保存的数据长度太大或者元素数量过多，会转换成linkedlist 编码
* `linkedlist` 字符串元素的长度大于 64 个字节或者总数量大于 512 个

与 List 相关的常用命令：

* `LPUSH`：向指定 List 的*左侧*（即*头部*）插入 1 个或多个元素，返回插入后的 List 长度。时间复杂度`O(N)`，N 为插入元素的数量。
* `RPUSH`：向指定 List 的*右侧*（即*尾部*）插入 1 或多个元素，返回插入后的 List 长度。时间复杂度`O(N)`，N 为插入元素的数量。
* `LPOP`：从指定 List 的左侧（即头部）移除一个元素并返回，时间复杂度`O(1)`。
* `RPOP`：从指定 List 的右侧（即尾部）移除 1 个元素并返回，时间复杂度`O(1)`。
* `LPUSHX/RPUSHX`：与`LPUSH/RPUSH`类似，区别在于，`LPUSHX/RPUSHX`操作的 key 如果不存在，则不会进行任何操作。
* `LLEN`：返回指定 List 的长度，时间复杂度`O(1)`。
* `LRANGE`：返回指定 List 中指定范围的元素（双端包含，即`LRANGE key 0 10`会返回11个元素），时间复杂度`O(N)`。

应谨慎使用的 List 相关命令：

* `LINDEX`：返回指定 List 指定 index 上的元素。如果 index 越界，返回`nil`。index 数值是回环的，即 -1 代表 List 最后一个位置，-2 代表 List 倒数第二个位置。时间复杂度`O(N)`。
* `LSET`：将指定 List 指定 index 上的元素设置为 value，如果 index 越界则返回错误，时间复杂度`O(N)`，如果操作的是头/尾部的元素，则时间复杂度为`O(1)`。
* `LINSERT`：向指定 List 中指定元素之前/之后插入一个新元素，并返回操作后的 List 长度。如果指定的元素不存在，返回 -1。如果指定 key 不存在，不会进行任何操作，时间复杂度`O(N)`。

由于 Redis 的 List 是链表结构的，所以上述的三个命令的算法效率较低，需要对 List 进行遍历，命令的耗时无法预估，在 List 长度大的情况下耗时会明显增加，应谨慎使用。换句话说，**Redis 的 List 实际是设计来用于实现队列**，而不是用于实现类似 ArrayList 这样的列表的。

另外，应尽可能控制一次获取的元素数量，一次获取过大范围的 List 元素会导致延迟，同时对长度不可预知的 List，避免使用`LRANGE key 0 -1`这样的完整遍历操作。

为了更好支持队列的特性，Redis 还提供了一系列阻塞式的操作命令，如`BLPOP/BRPOP`等，能够实现类似于 BlockingQueue 的能力，即在 List 为空时，阻塞该连接，直到 List 中有对象可以出队时再返回。

### Hash

Hash 即哈希表，Redis 的 Hash 和传统的哈希表一样，是一种`field-value`型的数据结构，可以理解成将 HashMap 搬入 Redis。

**Hash 非常适合用于表现对象类型的数据**，用 Hash 中的 field 对应对象的 field 即可。

在上面的图我们知道hash类型有两种编码格式：

* `ziplist` key 和 value 的字符串长度都小于 64 字节且键值对总数量小于 512，否则会转化为 hashtable
* `hashtable` key 和 value 的字符串长度大于 64 字节或者键值对总数量大于 512

Hash 的优点包括：

* 可以实现二元查找，如"查找 ID 为 1000 的用户的年龄"；
* 比起将整个对象序列化后作为 String 存储的方法，Hash 能够有效地减少网络传输的消耗；
* 当使用 Hash 维护一个集合时，提供了比 List 效率高得多的随机访问命令。

与 Hash 相关的常用命令：

* `HSET`：将 key 对应的 Hash 中的 field 设置为 value。如果该 Hash 不存在，会自动创建一个。时间复杂度`O(1)`。
* `HGET`：返回指定 Hash 中 field 字段的值，时间复杂度`O(1)`。
* `HMSET/HMGET`：同`HSET/HGET`，可以批量操作同一个 key 下的多个 field，时间复杂度`O(N)`，N 为一次操作的 field 数量。
* `HSETNX`：同`HSET`，但如 field 已经存在，`HSETNX`不会进行任何操作，时间复杂度`O(1)`。
* `HEXISTS`：判断指定 Hash 中 field 是否存在，存在返回 1，不存在返回 0，时间复杂度`O(1)`。
* `HDEL`：删除指定 Hash 中的 field（1 个或多个），时间复杂度`O(N)`，N 为操作的 field 数量。
* `HINCRBY`：同`INCRBY`命令，对指定 Hash 中的一个 field 进行自增，时间复杂度`O(1)`。该 field 对应的值应该是可以转换成数值的 String。

应谨慎使用的 Hash 相关命令：

* `HGETALL`：返回指定 Hash 中所有的 field-value 对。返回结果为数组，数组中 field 和 value 交替出现。时间复杂度`O(N)`。
* `HKEYS/HVALS`：返回指定 Hash 中所有的 field/value，时间复杂度`O(N)`。

上述三个命令都会对 Hash 进行完整遍历，Hash 中的 field 数量与命令的耗时线性相关。对于尺寸不可预知的 Hash，应严格避免使用上面三个命令，而改为使用`HSCAN`命令进行游标式的遍历。

### Set

Redis **Set 是无序的、不可重复的 String 集合**。

Set 类型有两种编码格式：

* `intset` 保存的元素全都是整数且总数量小于 512，否则转化成 hashtable
* `hashtable` 保存的元素不是整数或总数量大于 512


与 Set 相关的常用命令：

* `SADD`：向指定 Set 中添加 1 个或多个 member，如果指定 Set 不存在，会自动创建一个。时间复杂度`O(N)`，N 为添加的 member 个数。
* `SREM`：从指定 Set 中移除 1 个或多个 member，时间复杂度`O(N)`，N 为移除的 member 个数。
* `SRANDMEMBER`：从指定 Set 中随机返回 1 个或多个 member，时间复杂度`O(N)`，N 为返回的 member 个数。
* `SPOP`：从指定 Set 中随机移除并返回 1 个或多个 member，时间复杂度`O(N)`，N 为移除的 member 个数。
* `SCARD`：返回指定 Set 中的 member 个数，时间复杂度`O(1)`。
* `SISMEMBER`：判断指定的 member 是否存在于指定 Set 中，时间复杂度`O(1)`。
* `SMOVE`：将指定 member 从一个 Set 移至另一个 Set。 
慎用的 Set 相关命令：

* `SMEMBERS`：返回指定 Set 中所有的 member，时间复杂度`O(N)`。
* `SUNION/SUNIONSTORE`：计算多个 Set 的并集并返回/存储至另一个 Set 中，时间复杂度`O(N)`，N 为参与计算的所有集合的总 member 数。
* `SINTER/SINTERSTORE`：计算多个 Set 的交集并返回/存储至另一个 Set 中，时间复杂度`O(N)`，N 为参与计算的所有集合的总 member 数。
* `SDIFF/SDIFFSTORE`：计算 1 个 Set 与 1 个或多个 Set 的差集并返回/存储至另一个 Set 中，时间复杂度`O(N)`，N 为参与计算的所有集合的总 member 数。 
上述几个命令涉及的计算量大，应谨慎使用，特别是在参与计算的 Set 尺寸不可知的情况下，应严格避免使用。

### Sorted Set

Redis **Sorted Set 是有序的、不可重复的 String 集合**。

Sorted Set 中的每个元素都需要指派一个分数(score)，Sorted Set 会根据 score 对元素进行升序排序。如果多个 member 拥有相同的 score，则以字典序进行升序排序。

**Sorted Set 非常适合用于实现排名**。

SortSet 类型有两种编码格式：

* `ziplist` 元素长度小于 64 且总数量小于 128，否则转化成 skiplist
* `skiplist` 元素长度大于 64 或总数量大于 128

Sorted Set 的主要命令：

* `ZADD`：向指定 Sorted Set 中添加 1 个或多个 member，时间复杂度`O(Mlog(N))`，M 为添加的 member 数量，N 为 Sorted Set 中的 member 数量。
* `ZREM`：从指定 Sorted Set 中删除 1 个或多个 member，时间复杂度`O(Mlog(N))`，M 为删除的 member 数量，N 为 Sorted Set 中的 member 数量。
* `ZCOUNT`：返回指定 Sorted Set 中指定 score 范围内的 member 数量，时间复杂度`O(log(N))`。
* `ZCARD`：返回指定 Sorted Set 中的 member 数量，时间复杂度`O(1)`。
* `ZSCORE`：返回指定 Sorted Set 中指定 member 的 score，时间复杂度`O(1)`。
* `ZRANK/ZREVRANK`：返回指定 member 在 Sorted Set 中的排名，`ZRANK`返回按升序排序的排名，`ZREVRANK`则返回按降序排序的排名。时间复杂度`O(log(N))`。
* `ZINCRBY`：同`INCRBY`，对指定 Sorted Set 中的指定 member 的 score 进行自增，时间复杂度`O(log(N))`。 
慎用的 Sorted Set 相关命令：

* `ZRANGE/ZREVRANGE`：返回指定 Sorted Set 中指定排名范围内的所有 member，`ZRANGE`为按 score 升序排序，`ZREVRANGE`为按 score 降序排序，时间复杂度`O(log(N)+M)`，M 为本次返回的 member 数。
* `ZRANGEBYSCORE/ZREVRANGEBYSCORE`：返回指定 Sorted Set 中指定 score 范围内的所有 member，返回结果以升序/降序排序，min 和 max 可以指定为`-inf`和`+inf`，代表返回所有的 member。时间复杂度`O(log(N)+M)`。
* `ZREMRANGEBYRANK/ZREMRANGEBYSCORE`：移除 Sorted Set 中指定排名范围/指定 score 范围内的所有 member。时间复杂度`O(log(N)+M)`。 
上述几个命令，应尽量避免传递`[0 -1]`或`[-inf +inf]`这样的参数，来对 Sorted Set 做一次性的完整遍历，特别是在 Sorted Set 的尺寸不可预知的情况下。

### Bitmap 和 HyperLogLogs

Redis 的这两种数据结构相较之前的并不常用，只做简要介绍。

Bitmap 在 Redis 中不是一种实际的数据类型，而是一种将 String 作为 Bitmap 使用的方法。可以理解为将 String 转换为 bit 数组。使用 Bitmap 来存储`true/false`类型的简单数据极为节省空间。

HyperLogLogs 是一种主要用于数量统计的数据结构，它和 Set 类似，维护一个不可重复的 String 集合，但是 HyperLogLogs 并不维护具体的 member 内容，只维护 member 的个数。也就是说，HyperLogLogs 只能用于计算一个集合中不重复的元素数量，所以它比 Set 要节省很多内存空间。

## 命令

除了每种数据特定相关的命令，Redis 还有一些通用的命令，可以作用于所有的键值对。

* `EXISTS`：判断指定的 key 是否存在，返回 1 代表存在，0 代表不存在，时间复杂度`O(1)`。
* `DEL`：删除指定的 key 及其对应的 value，时间复杂度`O(N)`，N 为删除的 key 数量。
* `EXPIRE/PEXPIRE`：为一个 key 设置有效期，单位为秒或毫秒，时间复杂度`O(1)`。
* `TTL/PTTL`：返回一个 key 剩余的有效时间，单位为秒或毫秒，时间复杂度`O(1)`。
* `RENAME/RENAMENX`：将 key 重命名为 newkey。使用`RENAME`时，如果 newkey 已经存在，其值会被覆盖；使用`RENAMENX`时，如果 newkey 已经存在，则不会进行任何操作，时间复杂度`O(1)`。
* `TYPE`：返回指定 key 的类型，string, list, set, zset, hash。时间复杂度`O(1)`。
* `SCAN/HSCAN/SSCAN/ZSCAN`：分别用于对 String/Hash/Set/Sorted Set 中的元素进行游标式遍历。避免使用`KEYS`命令带来的性能问题。
* `CONFIG GET`：获得 Redis 某配置项的当前值，可以使用`*`通配符，时间复杂度`O(1)`。
* `CONFIG SET`：为 Redis 某个配置项设置新值，时间复杂度`O(1)`。
* `CONFIG REWRITE`：让 Redis 重新加载`redis.conf`中的配置。 
## 配置

### maxmemory 最大内存

`maxmemory`用于配置 Redis 可以使用的最大内存，类似如下：

```conf
maxmemory 100mb
```

在内存占用达到了 maxmemory 后，再向 Redis 写入数据时，Redis 会：

* 根据配置的数据淘汰策略尝试淘汰数据，释放空间；
* 如果没有数据可以淘汰，或者没有配置数据淘汰策略，那么 Redis 会对所有写请求返回错误，但读请求仍然可以正常执行。

默认情况下，在 32 位 OS 中，Redis 最大使用 3GB 的内存，在 64 位 OS 中则没有限制。在使用 Redis 时，应该对数据占用的最大空间有一个基本准确的预估，并为 Redis 设定最大使用的内存。否则在 64 位 OS 中 Redis 会无限制地占用内存（当物理内存被占满后会使用 swap 空间），容易引发各种各样的问题。

在为 Redis 设置 maxmemory 时，需要注意：如果采用了 Redis 的主从同步，主节点向从节点同步数据时，会占用掉一部分内存空间，设置的 maxmemory 不要过于接近主机可用的内存，留出一部分预留用作主从同步。

### maxmemory-policy 数据淘汰机制

Redis 的数据淘汰机制可以通过`maxmemory-policy`来进行配置，配置方法如下：

```conf
#默认是 noeviction，即不进行数据淘汰
maxmemory-policy noeviction
```

除了默认不淘汰数据外，Redis 还提供了 5 种数据淘汰策略：

* `volatile-lru`：使用 LRU 算法进行数据淘汰（淘汰上次使用时间最早的，且使用次数最少的 key），只淘汰设定了有效期的 key；
* `allkeys-lru`：使用 LRU 算法进行数据淘汰，所有的 key 都可以被淘汰。
* `volatile-random`：随机淘汰数据，只淘汰设定了有效期的 key。
* `allkeys-random`：随机淘汰数据，所有的 key 都可以被淘汰。
* `volatile-ttl`：淘汰剩余有效期最短的 key

最好为 Redis 指定一种有效的数据淘汰策略以配合 maxmemory 设置，避免在内存使用满后发生写入失败的情况。

一般来说，**推荐使用的策略是 volatile-lru**，并辨识 Redis 中保存的数据的重要性。对于那些重要的，绝对不能丢弃的数据（如配置类数据等），应不设置有效期，这样 Redis 就永远不会淘汰这些数据。对于那些相对不是那么重要的，并且能够热加载的数据（比如缓存最近登录的用户信息，当在 Redis 中找不到时，程序会去 DB 中读取），可以设置上有效期，这样在内存不够时 Redis 就会淘汰这部分数据。

### 慢日志

Redis 提供了 Slow Log 功能，可以自动记录耗时较长的命令。相关的配置参数有两个：

```conf
# 执行时间慢于 300 毫秒的命令计入 Slow Log
slowlog-log-slower-than 300ms
# Slow Log 的长度，即最大纪录多少条 Slow Log
slowlog-max-len 600
```

使用`SLOWLOG GET [number]`命令，可以输出最近进入 Slow Log 的 number 条命令。

使用`SLOWLOG RESET`命令，可以重置 Slow Log。

## 性能调优

针对 Redis 的性能优化，主要从下面几个层面入手：

* 避免让 Redis 执行耗时长的命令；
* 使用 Pipelining 将连续执行的命令组合执行；
* 操作系统的 Transparent huge pages 功能必须关闭：`echo never > /sys/kernel/mm/transparent_hugepage/enabled`；
* 选择合适的数据持久化策略；
* 考虑引入读写分离机制。


