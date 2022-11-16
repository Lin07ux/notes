> 转摘：[面试官问我ES读写数据的过程，结果。。。](https://mp.weixin.qq.com/s/QipV0xELU4H3011lIHRUMA)

## 一、ES 请求处理过程

ES 的请求主要就是写和读，而读又分为按 Document ID 读取和使用查询语句进行读取。

ES 的写请求是直接写入到 Primary Shard Node 中，然后由 Primary Shard Node 同步给其所有的 Replica Shard Node；而读请求则可以从 Primary Shard Node 和 Replica Shard Node 中进行读取，采用的是随机轮询算法。

而不管是读请求还是写请求，客户端都只需要随机连接一个 Node 来作为 Coordinating Node，实际的读写请求则由 Coordinating Node 来选择相关的节点来执行。也就是说，客户端不需要关心请求需要在哪个 Node 中执行，只知道连接任何一个 Node 就可以完成请求了。

### 1.1 ES 写数据过程

1. 客户端选择一个 Node 发送请求过去，这个 Node 就作为 Coordinating Node（协调节点）；

2. Coordinating Node 对 Document 进行路由，将请求转发给对应的 Node（有 Primary Shared）；

3. 被转发到的 Node 上的 Primary Shared 进行实际的写入请求处理，然后将数据同步到 Replica Node 中；

4. Coordinating Node 在 Primary Node 和所有 Replica Node 都搞定之后，就会返回响应结果给客户端。

流程图如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668584369)

### 1.2 ES 读数据过程

下面以 ES 通过 Document ID 来查询为例进行说明：

1. 客户端发送请求到任意一个 Node 上，该 Node 就成为 Coordinating Node；

2. Coordinating Node 对所请求的 Document ID 进行哈希路由，将请求转发到对应的 Node 上；

    哈希路由一般会使用 round-robin 随机轮询算法，在 Primary Shard 以及其所有的 Replica 中随机选择一个，使读请求实现负载均衡。

3. 被转发到的 Node 进行数据的读取，返回 Document 给 Coordinating Node；

4. Coordinating Node 返回 Document 给客户端。

### 1.3 ES 搜索数据过程

ES 最强大的是做全文检索，全文检索时支持相应规则的查询语句：

1. 客户端发送请求到一个 Node，该节点作为 Coordinating Node；

2. Coordinating Node 将搜索请求转发到所有的 Shard 对应的 Primary Shard Node 或 Replica Shard Node；

3. Query Phase：

    * 每个 Shard 将自己的搜索结果（其实就是一些 Document ID）返回给 Coordinating Node；
    * Coordinating Node 对得到的搜索结果进行合并、排序、分页等操作，产出最终结果。

4. Fetch Phase：Coordinating Node 根据汇总的 Document Id 列表去各个节点上拉取实际的 Document 数据，并返回给客户端。


## 二、写数据底层原理

### 2.1 写入流程

ES 写数据时，Primary Shard Node 的实现流程如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668585859)

1. 将数据写入内存 Buffer 中，同时将数据写入 translog 日志文件。

    在 Buffer 中的数据是不会被搜索到的。

2. 如果 Buffer 满了，或者达到一定的时间限制了，就会将 Buffer 中的数据刷新到一个新的 Segment File 中。
    
    ES 每隔 1 秒钟就会将 Buffer 中的数据写入到一个新的 Segment File，也就是每秒钟会产生一个新的磁盘文件，这个新的 Segment File 中就存储最近 1 秒内 Buffer 中写入的数据。这个过程就是 Refresh。
    
    当然，如果 Buffer 中没有数据就不会执行 Refresh 操作，也就不会生成新的 Segment File 了。
    
    另外，此时数据不是直接进入 Segment File 磁盘文件中的，而是先进入 OS Cache 中，然后由操作系统在一定条件将数据进行落盘，真实的写入到 Segment File 文件中。
    
    Refresh 之后，不论数据是已经落盘还是仍在 OS Cache 中，都可以被搜索到了。

3. 随着数据的写入，translog 会变得越来越大。当 translog 达到一定的大小（长度）的时候，或者达到一定时间时（默认 30 分钟），就会触发 ES 的 Commit 操作：

    * 第一步：将内存 Buffer 中现有数据 Refresh 到 OS Cache 中，并请求 Buffer；
    * 第二步：将一个 Commit Point 写入磁盘文件，里面标识着这个 Commit Point 对应的所有的 Segment File；
    * 第三步：强行将 OS Cache 中目前所有的数据都 fsync 到磁盘文件中去；
    * 第四步：清空现有的 translog 文件，重新一个新的 translog。

    这个 Commit 操作叫做 Flush。
    
    除了定时执行和因 translog 达到一定长度触发外，也可以通过 ES API 接口来手动执行 Flush 操作，将 OS Cache 中的数据 fsync 强制刷新到磁盘上去。
    
另外：**数据在写入 Segment File 的时候，就建立好了倒排序索引。**


### 2.2 为什么说 ES 是准实时的？

Near Real-time，即 NRT，准实时。

因为 ES 在写入数据的时候，会将数据先在内存 Buffer 中暂存 1 秒中，然后统一刷新到 Segment File / OS Cache 中。而在内存 Buffer 中的数据是无法被搜索到的。

也就是说，ES 中写入的数据一般会过 1 秒中后才能被搜索到，所以称 ES 为准实时。

### 2.3 translog 日志文件的作用是什么？

在数据写入后的一段时间内，数据要么是停留在 Buffer 中，要么是存在于 OS Cache 中。

无论是 Buffer 还是 OS Cache，都属于内存，一旦发生宕机，内存中的数据就会丢失。所以需要将数据对应的操作写入到一个专门的日志文件中，也就是 translog。

一旦此时机器宕机，再次重启的时候，ES 会自动读取 translog 日志文件中的数据恢复到内存 Buffer 和 OS Cache 中。

而且，translog 其实也是先写入到 OS Cache 中的，默认每隔 5 秒刷一次到磁盘中去。所以，默认情况下，可能有 5 秒的数据会仅仅停留在 buffer 或 OS Cache 中（translog、Segment File）。此时如果宕机了，就会丢失 5 秒钟的数据。

虽然可能会有 5 秒钟的数据丢失，但是这样性能比较好，属于性能和数据安全的平衡。

也可以更改默认配置，使得每次写入都将 translog 的内容 fsync 到磁盘中，但是性能会差很多。


## 三、其他

### 3.1 删除/更新数据的底层原理

如果是删除操作，Commit 的时候会生成一个`.del`文件，里面将被删除的 Document 标识为`deleted`状态。搜索的时候根据`.del`文件就知道这个 Document 是否被删除了。

如果是跟新操作，就是将原来的 Document 标识为`deleted`状态，然后生成一条新的数据。

### 3.2 Segment File Merge

写入 Buffer 每 Refresh 一次就会产生一个 Segment File。所以默认情况下是 1 秒钟一个 Segment File，这样 Segment File 就会越来越多。

因此，会定期执行 Merge 操作：

1. 将多个 Segment File 合并成一个文件；
2. 将标识为`deleted`的 Document 给物理删除掉；
3. 将新的 Segment File 写入磁盘。
4. 生成一个 Commit Point，标识所有新的 Segment File；
5. 打开 Segment File 供搜索使用；
6. 删除旧的 Segment File。

### 3.3 lucene

简单来说，lucene 是一个 jar 包，里面包含了封装好的各种建立倒排序索引的算法代码。使用 Java 开发的时候，引入 lucene jar，然后基于 lucene 的 API 做开发就可以了。

通过 lucene 可以将已有的数据建立索引，lucene 会在本地磁盘文件上层，组织索引的数据结构。

### 3.4 倒排序索引

在搜索引擎中，每个文档都有一个对应的文档 ID，文档内容被表示为一系列关键词的集合。

例如：文档 1 经过分词，提取了 20 个关键词，每个关键词都会记录它在文档中出现的次数和出现的位置。

那么，倒排索引就是关键词到文档 ID 的映射，每个关键词都对应着一系列的文件，这些文件中都包含了该关键词。

倒排序索引的两个重要细节：

1. 倒排序索引中的所有词项对应一个或多个文档；
2. 倒排序索引中的词项根据字典顺序升序排列。

比如，有以下的文档：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668588555)

对文档进行分词之后，得到以下的倒排序索引：

![](https://cnd.qiniu.lin07ux.cn/markdown/1668588576)

实际的倒排序索引还记录了更多的信息，比如文档频率信息（表示文档集合中有多少个文档包含某个词）。

有了倒排序索引，搜索引擎可以很方便的响应用户的查询。

比如，用户输入查询`Facebook`关键词，搜索系统查找倒排序索引，从中读出包含这个单词的文档就可以作为结果返回给用户了。

