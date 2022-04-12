> 转摘：[Mysql数据库查询好慢，除了索引，还能因为什么](https://mp.weixin.qq.com/s/CMo0Ih1vXpFCOETvc1Tshw)

要提升 MySQL 数据库查询效率，大都会优先考虑创建并使用合适的索引。但是除了索引因素之外，还会有其他的原因导致 MySQL 查询慢。

## 一、查询流程

### 1.1 连接和查询

有如下一张数据库表：

```sql
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名字',
  `age` int(11) NOT NULL DEFAULT '0' COMMENT '年龄',
  `gender` int(8) NOT NULL DEFAULT '0' COMMENT '性别',
  PRIMARY KEY (`id`),
  KEY `idx_age` (`age`),
  KEY `idx_gender` (`gender`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

当客户端带着账号密码与 MySQL 服务器端建立一条 TCP 长链接后，客户端要执行一条查询 SQL 语句，就会将 SQL 语句通过网络连接发给 MySQL 服务器。

### 1.2 服务器处理

MySQL 服务器收到 SQL 语句后，会在分析器中先判断下 SQL 语句有没有语法错误；然后就交给优化器，根据一定的规则选择应该使用什么索引；之后，通过执行器去调用存储引擎的接口函数。

这个流程如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649681135789-96f6c35d89e9.jpg)

### 1.3 buffer pool

存储引擎类似于一个个组件，是 MySQL 真正获取和存储一行行数据的地方。存储引擎是可以替换和更改的，在建库和建表的时候进行指定，当然也可以后续更改。

目前最常用的 MySQL 存储引擎是 InnoDB。因为直接操作磁盘会比较慢，所以在 InnoDB 存储引擎中，加了一层内存来提速，这部分内存就叫做 **buffer pool**。

在 buffer pool 中，存放了很多内存页，每一页 16KB。有些内存页存放的是数据库表中看到的一行行数据，有些则是索引信息。

![](http://cnd.qiniu.lin07ux.cn/markdown/1649681347166-695b8f2dbcaf.jpg)

### 1.4 索引

查询 SQL 到了 InnoDB 中，会根据前面优化器里计算得到的索引，去查询相应的索引页。如果要查询的索引页数据不在 buffer pool 中，则会从磁盘中将其加载到 buffer pool 中。

通过索引页加速查询后，即可得到数据页的具体位置。如果这些数据页不在 buffer pool 中，也需要将其从磁盘中加载进来，然后再返回给 Server 层。

![](http://cnd.qiniu.lin07ux.cn/markdown/1649681489817-db832569f470.jpg)

Server 层对数据进行适当的处理之后，就会返回给客户端。

## 二、慢查询分析

如果发现查询语句比较慢，可以通过开启 MySQL 服务器的 profiling 来确认流程慢的地方。

### 2.1 开启 profiling

MySQL profiling 是一个开关配置，通过如下命令即可开启：

```sql
mysql> set profiling=ON;
Query OK, 0 rows affected, 1 warning (0.00 sec)

mysql> show variables like 'profiling';
+---------------+-------+
| Variable_name | Value |
+---------------+-------+
| profiling     | ON    |
+---------------+-------+
1 row in set (0.00 sec)
```

开启之后，正常执行 SQL 语句，其执行的时间信息就会被记录下来。

### 2.2 查看 profile

首先可以通过`show profiles`命令来查看有哪些命令的 profiles 被记录下来了：

```sql
mysql> show profiles;
+----------+------------+---------------------------------------------------+
| Query_ID | Duration   | Query                                             |
+----------+------------+---------------------------------------------------+
|        1 | 0.06811025 | select * from user where age>=60                  |
|        2 | 0.00151375 | select * from user where gender = 2 and age = 80  |
|        3 | 0.00230425 | select * from user where gender = 2 and age = 60  |
|        4 | 0.00070400 | select * from user where gender = 2 and age = 100 |
|        5 | 0.07797650 | select * from user where age!=60                  |
+----------+------------+---------------------------------------------------+
5 rows in set, 1 warning (0.00 sec)
```

关注上面输出中的`query_id`，想要查看某条 SQL 语句的具体耗时，可以执行一些的命令：

```sql
mysql> show profile for query 1;
+----------------------+----------+
| Status               | Duration |
+----------------------+----------+
| starting             | 0.000074 |
| checking permissions | 0.000010 |
| Opening tables       | 0.000034 |
| init                 | 0.000032 |
| System lock          | 0.000027 |
| optimizing           | 0.000020 |
| statistics           | 0.000058 |
| preparing            | 0.000018 |
| executing            | 0.000013 |
| Sending data         | 0.067701 |
| end                  | 0.000021 |
| query end            | 0.000015 |
| closing tables       | 0.000014 |
| freeing items        | 0.000047 |
| cleaning up          | 0.000027 |
+----------------------+----------+
15 rows in set, 1 warning (0.00 sec)
```

通过上面的各个项，就可以看出具体的耗时在哪里了。比如，上面可以看出`Sending data`的耗时最大，这个是指执行器开始查询数据并将查询到的数据发送给客户端的耗时。

一般情况下，耗时大部分都是都是在`Sending data`阶段，而且这一阶段要提升效率，最容易想到的还是索引相关的原因。

## 三、索引相关问题

索引相关的问题，一般都能用`Explain`命令帮助分析。通过它能看到用了哪些索引，大概会扫描多少行之类的信息。

MySQL 会在优化器阶段里看选择哪个索引会使查询速度更快。一般主要考虑以下几个因素：

* 选择这个索引大概要扫描多少行；
* 为了把这些行取出来，需要读多少个 16kb 的页；
* 走普通索引需要回表，主键索引则不需要回表，回表的成本大不大？

### 3.1 EXPLAIN 示例

比如，执行如下 SQL：

```sql
explain select * from user where age>=60
```

假设得到结果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649737947407-0d0f57f7c912.jpg)

可以看到，上面这条语句，使用的`type = ALL`，这意味着执行时是全表扫描。虽然`prossible_keys`指示出可能用到`idx_age`索引，但是数据库实际上使用的索引`key = NULL`，也就说，这个 SQL 不会走索引，而是进行全表扫描处理的。

之所以会这样，是因为数据表中，符合条件的数据行数（rows）太多，如果使用 age 索引，就需要将它们从索引中读出来，然后进行回表才能找到对应的数据页。如此一来，还不如直接走主键划算，所以最终就选择了全表扫描。

从这个例子也能发现一些索引失效的情况，这会导致查询效率无法提升，甚至低下。

### 3.2 索引不符合预期

实际开发中有些情况比较特殊，比如有些数据库表一开始数量小，索引少，执行 SQL 时确实会使用符合预期的索引。但是随着功能开发和数据量累积，甚至加入一些其他重复多余的索引，可能就会是的 SQL 执行时用到了不符合预期的索引，从而导致查询变慢。

这种问题比较好解决，通过`force index <idx_name>`方式强制指定索引即可。

比如：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649738349969-a6634121ea36.jpg)

### 3.3 走了索引还是慢

有些 SQL 即便已经使用了索引，但还是很慢，一般有两种原因：

1. **索引区分度太低**。比如将网页全路径的 url 链接做索引，因为一个站点的域名基本都相同，索引如果前缀索引的长度不够长，那么使用这个索引就跟全表扫描似的。正确的方法是尽量让索引的区分度更高，比如去掉域名存储，只拿后面的 uri 部分做索引。

2. **索引匹配到的数据太大**。这时候需要关注 EXPLAIN 命令中的 rows 字段。它是用于预估这个查询语句需要查的行数的，不一定准确，但是可以体现大概的量级。

当 EXPALIN 展示的 rows 很大时，一般场景的是下面两种情况：

* 如果这个字段具有唯一性（比如电话号码），一般是不应该有大量重复的，很可能是代码逻辑出现了大量重复插入的操作。可以检查代码逻辑，或者使用唯一索引限制。
* 如果这个字段下的数据量确实很大，可以考虑添加 limit 限制。如果要获取全部的数据，额可以分批的获取，尽量避免一次性获取所有的数据。

## 四、其他原因

除了索引原因之外，还有一些其他因素会使得查询缓慢。

### 4.1 连接数过小

MySQL 的 Server 层有个连接管理器，它的作用是管理客户端和 MySQL 之间的长连接。

正常情况下，客户端和 Server 层之间如果只有一条连接，那么在执行 SQL 查询时，就只能阻塞等待结果返回，无法响应大量查询的并发请求。

![](http://cnd.qiniu.lin07ux.cn/markdown/1649738962090-b836960cf3ab.jpg)

为了避免后面的查询请求需要等待前面的请求执行完成才能执行，可以多建几条连接，这样就能并发执行了。

![](http://cnd.qiniu.lin07ux.cn/markdown/1649739046326-68d509e62941.jpg)

连接数过小的问题，受数据库服务器端和客户端两侧同时限制。

#### 4.1.1 服务器端连接数过小

MySQL 的最大连接数默认为 100，最大可以达到 16384。

可以通过设置 MySQL 的`max_connections`参数来更改数据库的最大连接数。

比如，下面的语句将服务器端的最大连接数改为了 500：

```sql
mysql> set global max_connections= 500;
Query OK, 0 rows affected (0.00 sec)

mysql> show variables like 'max_connections';
+-----------------+-------+
| Variable_name   | Value |
+-----------------+-------+
| max_connections | 500   |
+-----------------+-------+
1 row in set (0.00 sec)
```

#### 4.1.2 客户端连接数过小

在应用侧的客户端（代码）中也可能会出现连接数过小而导致查询阻塞的问题。

应用侧与 MySQL 服务器的连接，是基于 TCP 协议的长连接。而 TCP 协议需要经过三次握手和四次挥手来实现连接的建立和释放。如果每次执行 SQL 都需要重新建立一个新的连接的话，就需要不断的握手和挥手，这也很耗时。所以一把在应用程序中会建立一个长链接池，连接用过之后重新放回池中，下次要执行 SQL 的时候直接从池中获取即可。

![](http://cnd.qiniu.lin07ux.cn/markdown/1649740267115-937a8953e17f.jpg)

连接池的大小就控制了客户端的连接数最大值。如果连接池太小，还没有数据库服务器端的最大连接数大，那对服务器端连接数的调整并不会有作用。

比如，Go 语言里的 gorm 库可以这样设置连接池大小：

```go
func Init() {
  db, err := gorm.Open(mysql.Open(conn), config)
  sqlDB, err := db.DB()
  
  // 设置空闲连接池中连接的最大数量
  sqlDB.SetMaxIdleConns(200)
  // 设置打开的数据库连接的最大数量
  sqlDB.SetMaxOpenConns(1000)
}
```

### 4.2 buffer pool 太小

在 InnoDB 存储引擎中，有一层内存池 buffer pool，用于将磁盘数据页加载到内存页中。在查询时，只要 buffer pool 中有，就可以直接返回，否则就要走磁盘 IO，从而导致查询缓慢了。

也就是说：如果 buffer pool 比较大，那么能放的数据页就比较多，查询时就更可能命中 buffer pool，查询速度自然就更快了。

#### 4.2.1 调整 buffer pool 大小

可以通过下面的命令查询到 buffer pool 的大小，单位是 Byte：

```sql
mysql> show global variables like 'innodb_buffer_pool_size';
+-------------------------+-----------+
| Variable_name           | Value     |
+-------------------------+-----------+
| innodb_buffer_pool_size | 134217728 |
+-------------------------+-----------+
1 row in set (0.01 sec)
```

可以看到 buffer pool 的大小是 128MB。如果要调大一点，可以执行如下 SQL：

```sql
mysql> set global innodb_buffer_pool_size = 536870912;
Query OK, 0 rows affected (0.01 sec)

mysql> show global variables like 'innodb_buffer_pool_size';
+-------------------------+-----------+
| Variable_name           | Value     |
+-------------------------+-----------+
| innodb_buffer_pool_size | 536870912 |
+-------------------------+-----------+
1 row in set (0.01 sec)
```

这样就把 buffer pool 增大到了 512MB。


#### 4.2.2 如何判断 buffer pool 是否过小?

可以通过查看 buffer pool 的缓存命中率来判断 buffer pool 是否过小。

使用`show status like 'Innodb_buffer_pool_%';`语句来查看 buffer pool 的使用信息：

![](http://cnd.qiniu.lin07ux.cn/markdown/1649740922548-5537eb162021.jpg)

其中：

* `Innodb_buffer_pool_read_requests`表示读请求的次数；
* `Innodb_buffer_pool_reads`表示从物理磁盘中读取数据的请求次数。

所以 buffer pool 的读取命中率可以这样计算：

```
buffer pool 命中率 = 1 - (Innodb_buffer_pool_reads/Innodb_buffer_pool_read_requests) * 100%
```

比如，上面图示中的命令率计算得到为 99.98%，非常高了。

一般情况下，buffer pool 命中率保持在 99% 以上就是比较好了。如果低于这个值，就可以考虑加大 buffer pool 的大小了。

另外，还可以把这个命中率做成监控，自动记录下来。这样可以在 SQL 变慢时有对应的命中率记录信息可查。

## 五、总结

* MySQL 查询过慢一般是索引问题，可能是因为选错了索引，或者索引对应的数据太多；
* 客户端和服务器端连接数过小，会限制 SQL 查询的并发数，适当增大连接数可以提升速度；
* InnoDB 中的 buffer pool 用于提升查询速度，命中率一般应保持不小于 99%。如果低于这个值可以考虑增大 buffer pool 的大小；

