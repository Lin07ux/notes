> 转摘：[深入理解MySQL执行过程及执行顺序](https://dockone.io/article/2434613)

### 1. 整体流程图

MySQL 整体的执行过程如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669108481)

### 2. 连接器

MySQL 中连接器处于 Server 层，主要职责是：

1. 负责与客户端的通信

    与客户端的通信是基于 TCP/IP 的半双工模式，这意味着某一固定时刻只能由客户端向服务器或者由服务器向客户端发送数据，而不能同时进行。

2. 验证请求用户的账户和密码

    如果账户密码不存在或匹配，会报错：`Access denied for user 'root'@'localhost' (using password: YES)`。
    
3. 验证请求用户的权限

    用户的账户和密码匹配的情况下，连接器会在 MySQL 自带的权限表中查询当前用户的权限。
    
MySQL 中存在 4 个控制权限的表，分别为：

* `user`表：存放用户账户信息以及全局级别（所有数据库）权限，决定了来自哪些主机的哪些用户可以访问数据库实例；
* `db`表：存放数据库级别的权限，决定了来自哪些主机的哪些用户可以访问指定的数据库；
* `tables_priv`表：存放表级别的权限，决定了来自哪些主机的哪些用户可以访问指定数据库的指定表；
* `columns_priv`表：存放列级别的权限，决定了来自哪些主机的哪些用户可以访问指定数据库的指定表中的指定列。

MySQL 权限验证过程为：

1. 身份验证：从 user 表中的 Host、User、Password 这 3 个字段中判断连接的 IP、用户名、密码是否存在，存在则通过验证；

2. 权限验证：身份验证通过后，按照 user、db、tables、columns 的顺序进行权限验证。

    * 先检查全局权限表 user，如果此账户对所有数据库的权限都为 Y，则无需后续的检查，直接通过；
    * 再检查 db 表中此账户对应的具体数据库的权限，如果权限为 Y，则无需后续的检查，直接通过；
    * 继续检查 tables_priv 表中此账户对应的指定数据库的具体表的权限，如果权限为 Y，则无需后续的检查，直接通过；
    * 最后检查 columns_priv 表中次账户对应的指定数据库中指定表的具体字段的权限，如果权限为 Y 则通过，否则说明此账户未通过 权限验证。

3. 如果在上述的任何一个过程中权限验证不通过，则会报错。

### 3. 缓存

MySQL 的缓存主要的作用是提升查询的效率，以 Key-Value 的哈希表形式存储，Key 为具体的 SQL 语句，Value 是结果的集合。

如果命中了缓存，则会直接将缓存的结果返回给客户端，否则会继续后续的流程。

MySQL 5.6 版本开始缓存功能已经默认关闭了，而 MySQL 8.0 版本后，缓存功能被官方删除掉了，因为查询缓存会很频繁的失效。

在一个写多读少的环境中，缓存会频繁的新增和失效。对于某些更新压力大的数据库来说，查询缓存的命中率就会非常的低，而 MySQL 为了维护缓存可能会出现一定的伸缩性问题。比较推荐的一种做法是将缓存存在客户端。

### 4. 分析器

分析器的主要作用是对客户端发过来的 SQL 语句进行分析，包括预处理和解析过程。

在这个阶段会解析 SQL 语句的语义，并进行关键词和非关键词的提取和解析，组成一个解析树。如果分析道语法错误，会直接给客户端抛出异常：`ERROR: You have an error in your SQL syntax`。

具体的关键词包括但不限于以下：`SELECT/UPDATE/DELETE/INSERT/OR/IN/WHERE/GROUP BY/HAVING/COUNT/LIMIT`。

比如，对于如下的 SQL 语句：

```sql
SELECT * FROM user WHERE user_id = 1234;
```

在分析器中通过语义规则器将`SELECT/FROM/WHERE`这些关键词提取出来，然后 MySQL 会自动判断关键词和非关键词，将用户的匹配字段和自定义语句标识出来。

这个阶段也会做一些校验，比如校验当前数据库是否存在 user 表，当前数据库的 user 表中是否存在`user_id`这个字段。如果 user 表中不存在`user_id`字段，同样会报错：`unknown column in field list`。

### 5. 优化器

能够进入到优化器阶段表示 SQL 是符合 MySQL 的标准语义规则的，并且可以执行的。

优化器的主要功能就是进行 SQL 语句的优化，根据执行计划进行最优的选择，匹配合适的索引，选择最佳的执行方案。

比如，一个典型的例子：

表 T 中，对列 A、B、C 建立联合索引。在进行查询的时候，当 SQL 语句为`SELECT xx FROM T WHERE B = x AND A = x AND C = x`时，优化器会自动的进行 WHERE 条件重排，变为`WHERE A = x AND B = x AND C = x`，从而使这个查询语句能够符合索引的最左匹配原则，从而使用上 A、B、C 三列上的联合索引。

这个阶段的优化是自动按照执行计划进行预处理，MySQL 会计算各个执行方法的最佳时间，最终确定一条执行 SQL 交给后面的执行器。

### 6. 执行器

执行器是 Server 层与存储引擎层进行交互的重要地方，会根据 SQL 语句来调用存储引擎的 API 获取或写入相关数据。

存储引擎之前的名字叫做表处理器，负责对具体的数据文件进行操作，分析 SQL 语句的语义并执行对应的具体操作。目前常用的存储引擎为 InnoDB。

![](https://cnd.qiniu.lin07ux.cn/markdown/1669734162)

在 InnoDB 存储引擎中，除了完成具体的数据操作，对于写入操作还会生成对应的 undo log、redo log、bin log 等，来保证事务的 ACID 性质。

### 7. 执行的状态

可以通过`SHOW full processlist`展示所有的处理进程，其状态主要包含如下几种：

![](https://cnd.qiniu.lin07ux.cn/markdown/1669734288)


