## MySQL 索引简介
### MyISAM、Innodb 索引对比
* MyISAM
    * 数据指针指向数据文件中的物理位置
    * 所有索引都是一样的（指向物理位置） 

* Innodb
    * 主键索引 (显式或隐式) - 直接将数据存储于索引的叶子节点，而不是指针
    * 二级索引 – 保存主键索引的值作为数据指针 
### BTREE 索引适用操作
* 点查询：查询所有 key=5 的记录
* 开合间：查询所有 key>5 的记录
* 闭合间：查询所有 5<key<10 的记录

**不适用于**：查询 key 最后一个数字等于 0 的所有记录。因为这不能定义为范围查询操作。

### 字符索引
字符索引和数值索引没有什么区别。collation 是为字符串定义的排序规则。

对于字符索引，前缀 LIKE 查询是一种特殊的范围查询：

* `LIKE "ABC%"`的意思是：`"ABC[最小值]" < KEY < "ABC[最大值]"`。
* `LIKE "%ABC"`无法使用索引查询。 
### 联合索引
联合索引(`KEY(col1,col2,col3)`)是这样进行排序的：比较首列，然后第二列，第三列以此类推。它使用一个 BTREE 索引，而不是每个层级一个单独的 BTREE 索引。 
### 索引的开销和成本
索引是昂贵的，不要添加多余的索引。所有多数情况下，扩展索引比添加一个新的索引要好。

所有的开销主要有如下两个方面：

* 写 - 更新索引常常是数据库写操作的主要开销；
* 读 - 需要在硬盘和内存开销空间; 查询优化中需要额外的开销。

索引的成本表现在：

* 长主键索引（Innodb） – 使所有相应的二级索引 变得更长、更慢
* “随机”主键索引（Innodb） – 插入导致大量的页面分割
* 越长的索引通常越慢
* Index with insertion in random order – SHA1(‘password’)
* 低区分度的索引是低劣的 – 在性别字段建的索引
* 相关索引是不太昂贵的– insert_time与自增id是相关的


## MySQL 使用索引
### 使用索引进行查询
简单的单索引查询(`KEY(LAST_NAME)`)：

```sql
SELECT * FROM EMPLOYEES WHERELAST_NAME="Smith";
```

或者复合索引(`KEY(DEPT,LAST_NAME)`)：

```sql
SELECT * FROM EMPLOYEES WHERELAST_NAME="Smith" AND DEPT="Accounting";
```

使用复合索引时，查询语句中的字段顺序会影响是否使用该复合索引。比如：

* 下列情形将会使用索引进行查询（全条件）：
    * A>5
    * A=5 AND B>6
    * A=5 AND B=6 AND C=7
    * A=5 AND B IN (2,3) AND C>5

* 以下情形使用索引的一部分：
    * A>5 AND B=2 - 第一个字段A的范围查询，导致只用上了索引中A字段的部分
    * A=5 AND B>6 AND C=2 - B字段的范围范围查询，导致只使用了索引中A和B两个字段的部分

* 下列条件将不会使用索引：
    * B>5 – 条件没有B字段前的A
    * B=6 AND C=7 - 条件没有B、C字段前的A 
**MySQL 优化器的第一法则**：在复合索引中，MySQL 在遇到返回查询`(<,>,BETWEEN)`时，将停止中止剩余部分（索引）的使用；但是使用`IN(…)`的"范围查询"则可以继续往右使用索引的更多部分。

### 使用索引进行排序
不使用索引将进行非常昂贵的“filesort”操作(externalsort)。

```sql
-- 使用单索引排序 key(score)
SELECT * FROM players ORDER BY score DESC LIMIT 10;

-- 使用复合索引排序 key(country, score)
SELECT * FROM players WHERE country="US" ORDER BY score DESC LIMIT 10;
```

使用复合索引排序时，也会由于查询语句中的字段顺序而影响是否使用索引。比如，对于索引`KEY(A,B)`：

* 以下情形将会使用索引进行排序
    * ORDER BY A - 对索引首字段进行排序
    * A=5 ORDER BY B - 对第一个字段进行点查询，对第二个字段进行排序
    * ORDER BY A DESC, B DESC - 对两个字段进行相同的顺序进行排序
    * A>5 ORDER BY A - 对首字段进行范围查询，并对首字段进行排序 
* 以下情形将不使用索引进行排序
    * ORDER BY B - 对第二个字段进行排序（未使用首字段）
    * A>5 ORDER BY B – 对首字段进行范围查询，对第二个字段进行排序
    * A IN(1,2) ORDER BY B - 对首字段进行IN查询，对第二个字段进行排序
    * ORDER BY A ASC, B DESC - 对两个字段进行不同顺序的排序

**MySQL 使用索引排序的规则**：

* 不能对两个字段进行不同顺序的排序。
* 对非 ORDER BY 部分的字段只能使用点查询(`=`)– 在这种情形下，`IN()`也不行。  


