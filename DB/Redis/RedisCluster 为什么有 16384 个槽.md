> 转摘：[面试官:知道为什么RedisCluster有16384个槽么?](https://mp.weixin.qq.com/s/vJdy3f_Aoc5mZsbWAR1uRw)

Redis Cluster 作为官方 Redis 集群架构，最多可以支持 16384(2^14) 个节点。

对于客户端请求的 key，根据公式`HASH_SLOT=CRC16(key) mod 16384`，计算出映射到哪个分片上，然后 Redis 会去相应的节点进行操作。

CRC16 算法产生的 hash 值有 16bit，该算法可以产生 2^16 = 65536 个值。换句话说，值是分布在 0~65535 之间。那作者在做取模运算的时候，为什么不`mod 65536`，而选择`mod 16384`呢？

对于这个问题，作者也有回答

> [why redis-cluster use 16384 slots? #2576](https://github.com/antirez/redis/issues/2576)
> 
> The reason is:
> 
>	1.	Normal heartbeat packets carry the full configuration of a node, that can be replaced in an idempotent way with the old in order to update an old config. This means they contain the slots configuration for a node, in raw form, that uses 2k of space with16k slots, but would use a prohibitive 8k of space using 65k slots.
>	2.	At the same time it is unlikely that Redis Cluster would scale to more than 1000 mater nodes because of other design tradeoffs.
>
> So 16k was in the right range to ensure enough slots per master with a max of 1000 maters, but a small enough number to propagate the slot configuration as a raw bitmap easily. Note that in small clusters the bitmap would be hard to compress because when N is small the bitmap would have slots/N bits set that is a large percentage of bits set.

这段解释主要就是说，**过多的节点会导致集群中的心跳数据太多，浪费带宽**，如 65535 个插槽会需要 8k 数据，而 16384 个插槽则只需要 2k。而且 16384 个节点已经足够使用了，**其他的设计权衡也使得节点一般都不会超过 1000 个**。

下面主要对这里的心跳信息的数据量进行分析。

### 1. 工作方式

当节点之间握手成功之后，两个节点之间会*定期*发送 ping/pong 消息，交换*数据信息*。

这里的信息交换在节点的生存期间会一直存在。

需要关注三个重点：

* 交换什么数据信息
* 数据信息究竟多大
* 定期的频率什么样

### 2. 交换什么信息

交换的信息由消息头和消息体两部分组成。

其中，消息体主要是一些节点标识，如 IP、端口号、发送时间等。这与本文关系不是太大，可以不考虑。

消息头的结构定义如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1568874509166.png)

消息头中的`type`表示消息类型，`myslots`是一个长度为 16384/8 = 2048 = 2kb 的 char 数组，表示发送节点负责的槽位信息。这其实是一个 bitmap，每一个位代表一个槽，如果某个位是 1，就表示这个槽是属于这个节点的。

> 定义`myslots[CLUSTER_SLOTS/8]`的`CLUSTER_SLOTS`就表示槽位数量，也就是 16384。

### 3. 信息有多大

消息体会携带一定数量的其他节点信息用于交换，其携带的节点信息的数量一般约为集群总节点数量的 1/10，至少携带 3 个节点的信息。所以**节点数量越多，消息体内容越大**。如果携带 10 个节点的状态消息，那么消息体约有 1kb。

在消息头中，最占空间的是`myslots[CLUSTER_SLOTS/8]`，也就是 16384 ÷ 8 ÷ 1024 = 2kb。其他的信息相对较小，可以忽略不计。

### 4. 发送频率有多大

Redis Cluster 集群内的节点每秒都在发心跳消息，规律如下：

1. 每秒会随机选取 5 个节点，找出最久没有通信的节点发送 ping 消息；
2. 每 100 毫秒(1 秒 10 次)都会扫描本地节点列表，如果发现节点最近一次接受 pong 消息的时间大于`cluster-node-timeout/2`则立刻发送 ping 消息。

因此，每秒单节点发出 ping 消息数量为：`1 + 10 * num(node.pong_received > cluster_node_timeout / 2)`。大致带宽损耗如下所示(图片来自《Redis开发与运维》)：

![](http://cnd.qiniu.lin07ux.cn/markdown/1568875194945.png)

### 5. 总结

综上：

1. 如果槽位为 65536，发送心跳信息的消息头达 8k(65536 / 8 / 1024 = 8kb)，发送的心跳包过于庞大。因为每秒钟，Redis 节点需要发送一定数量的 ping 消息作为心跳包，如果槽位为 65536，这个 ping 消息的消息头太大了，浪费带宽。

2. 集群节点越多，心跳包的消息体内携带的数据越多。如果节点过多就会导致网络拥堵。因此 Redis 作者不建议 Redis Cluster 节点数量超过 1000 个。

3. 槽位越小，节点少的情况下，压缩率高。Redis 主节点的配置信息中，它所负责的哈希槽是通过一张 bitmap 的形式来保存的，在传输过程中，会对 bitmap 进行压缩，节点数很少，而哈希槽数量很多的话，bitmap 的压缩率就很低。

所以 Redis Cluster 集群的最大节点数(哈希插槽数)设计为 16384 个。

