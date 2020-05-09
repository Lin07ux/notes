### 一、索引

1. 应尽量避免全表扫描，首先应考虑在`where`及`order by`涉及的列上建立索引。

2. 并不是所有索引对查询都有效，SQL 是根据表中数据来进行查询优化的，当索引列有大量数据重复时，SQL 查询可能不会去利用索引，如一表中有字段`sex`，`male`、`female`几乎各一半，那么即使在 sex 上建了索引也对查询效率起不了作用。

3. 索引并不是越多越好，索引固然可以提高相应的`select`的效率，但同时也降低了`insert`及`update`的效率，因为`insert`或`update`时有可能会重建索引，所以怎样建索引需要慎重考虑，视具体情况而定。一个表的索引数最好不要超过 6 个，若太多则应考虑一些不常使用到的列上建的索引是否有必要。

4. 应尽可能的避免更新`clustered`索引数据列，因为`clustered`索引数据列的顺序就是表记录的物理存储顺序，一旦该列值改变将导致整个表记录的顺序的调整，会耗费相当大的资源。若应用系统需要频繁更新`clustered`索引数据列，那么需要考虑是否应将该索引建为`clustered`索引。

5. MySQL 一次查询只能使用一个索引，如果要对多个字段使用索引，建立复合索引。

### 二、WHERE 子句

1. 应尽量避免在`where`子句中使用`!=`或`<>`操作符，否则将引擎放弃使用索引而进行全表扫描。

2. 应尽量避免在`where`子句中对字段进行`null`值判断，否则将导致引擎放弃使用索引而进行全表扫描。如：`select id from t where num is null;`。可以考虑在`num`上设置默认值 0，确保表中`num`列没有`null`值，然后这样查询：`select id from t where num = 0;`。

3. 尽量避免在`where`子句中使用`or`来连接条件，否则将导致引擎放弃使用索引而进行全表扫描，如：`select id from t where num=10 or num=20;`。可以这样查询：`select id from t where num=10 union all select id from t where num=20;`。

4. 尽量避免在`where`子句中使用`in`和`not in`来连接条件，否则将导致引擎放弃使用索引而进行全表扫描，如：`select id from t where num in(1,2,3);`。对于这种连续的数值，能用`between`就不要用`in`了：`select id from t where num between 1 and 3;`。

5. 如果在`where`子句中使用参数，也会导致全表扫描。因为 SQL 只有在运行时才会解析局部变量，但优化程序不能将访问计划的选择推迟到运行时；它必须在编译时进行选择。然而，如果在编译时建立访问计划，变量的值还是未知的，因而无法作为索引选择的输入项。如下面语句将进行全表扫描：`select id from t where num=@num;`可以改为强制查询使用索引：`select id from t with(index(索引名)) where num=@num;`。

6. 应尽量避免在 where 子句中对字段进行表达式操作，这将导致引擎放弃使用索引而进行全表扫描。如：`select id from t where num/2=100;`应改为：`select id from t where num=100*2;`。

7. 应尽量避免在`where`子句中对字段进行函数操作，这将导致引擎放弃使用索引而进行全表扫描。如：`select id from t where substring(name, 1, 3) = 'abc';` --`name`以`abc`开头的 id，或者`select id from t where datediff(day, createdate, '2005-11-30') = 0;` -–2005-11-30 日生成的 id。它们应改为:`select id from t where name like 'abc%';`和`select id from t where createdate >= '2005-11-30' and createdate < '2005-12-1';`。

8. 在使用索引字段作为条件时，如果该索引是复合索引，那么必须使用到该索引中的第一个字段作为条件时才能保证系统使用该索引，否则该索引将不会被使用，并且应尽可能的让字段顺序与索引顺序相一致。

### 三、JOIN 优化

MySQL 中有三种 JOIN 类别：LEFT JOIN、INNER JOIN、RIGHT JOIN：

![](http://cnd.qiniu.lin07ux.cn/markdown/1559622428902.png)

* `LEFT JOIN`中 A 表为驱动表
* `INNER JOIN` 中 MySQL 会自动找出那个数据少的表作用驱动表
* `RIGHT JOIN` 中 B 表为驱动表

1. MySQL 中没有 FULL JOIN，可以用以下方式来解决：`select * from A left join B on B.name = A.name where B.name is null union all select * from B;`。

2. 合理利用索引：使用被驱动表的索引字段作为`on`的限制字段。

3. 利用小表去驱动大表：小表驱动大表可以减少嵌套循环中的循环次数，从而减少 IO 总量及 CPU 运算的次数。一般建议使用 INNER JOIN 而非 LEFT JOIN，因为前者会由 MySQL 自动选择小表做为驱动表，后者则是遵循最左驱动的原则，可能会出现大表驱动小表的情况。

4. 巧用`STRAIGHT_JOIN`：INNER JOIN 是由 MySQL 选择驱动表，但是有些特殊情况需要选择另个表作为驱动表。`STRAIGHT_JOIN`可以用来强制指定连接顺序，在`STRAIGHT_JOIN`左边的表名就是驱动表，右边则是被驱动表。

> 使用`STRAIGHT_JOIN`的前提条件是该查询是内连接，也就 INNER JOIN，其他连接不推荐使用`STRAIGHT_JOIN`，因为可能造成查询结果不准确。

### 四、ORDER BY 优化

> 在`ORDER BY`操作中，MySQL 只有在排序条件不是一个查询条件表达式的情况下才使用索引。

1. 为`ORDER BY`中的字段建立索引可以优化排序性能，但是如果`ORDER BY`后的多个字段没有创建联合索引，就不会有性能优化。比如，对于`SELECT * FROM t1 ORDER BY key1, key2;`语句，如果为`key1, key2`两列创建了联合索引，则会有排序优化，如果对两列分别创建索引，则不会有性能优化。

2. 为`ORDER BY`和`WHERE`中的字段创建联合索引可以提升性能，但是如果`WHERE`中的条件列有多个值则无法实现优化。而且由于`WHERE`语句比`ORDER BY`语句先执行，所以创建联合索引的时候，应该将`WHERE`中的字段放在前面。比如，对于`SELECT * FROM [table] WHERE uid=1 ORDER x,y LIMIT 0,10;`语句，建立索引`(uid,x,y)`实现`order by`的优化比建立`(x,y,uid)`索引效果要好得多。

### 五、临时表

1. 避免频繁创建和删除临时表，以减少系统表资源的消耗。临时表并不是不可使用，适当地使用它们可以使某些例程更有效，例如，当需要重复引用大型表或常用表中的某个数据集时。但是，对于一次性事件，最好使用导出表。

2. 在新建临时表时，如果一次性插入数据量很大，那么可以使用`select into`代替`create table`，避免造成大量 log，以提高速度；如果数据量不大，为了缓和系统表的资源，应先`create table`，然后`insert`。

3. 如果使用到了临时表，在存储过程的最后务必将所有的临时表显式删除，先`truncate table`，然后`drop table`，这样可以避免系统表的较长时间锁定。

4. 尽量使用表变量来代替临时表。如果表变量包含大量数据，请注意索引非常有限（只有主键索引）。

### 六、游标

1. 尽量避免使用游标，因为游标的效率较差，如果游标操作的数据超过 1 万行，那么就应该考虑改写。

2. 使用基于游标的方法或临时表方法之前，应先寻找基于集的解决方案来解决问题，基于集的方法通常更有效。

3. 与临时表一样，游标并不是不可使用。对小型数据集使用 FAST_FORWARD 游标通常要优于其他逐行处理方法，尤其是在必须引用几个表才能获得所需的数据时。在结果集中包括“合计”的例程通常要比使用游标执行的速度快。如果开发时间允许，基于游标的方法和基于集的方法都可以尝试一下，看哪一种方法的效果更好。

### 七、其他

1. 不要写一些没有意义的查询，如需要生成一个空表结构：`select col1, col2 into #t from t where 1=0`。这类代码不会返回任何结果集，但是会消耗系统资源的，应改成这样：`create table #t(…)`。

2. 合理使用`exists`和`in`：前者适用于外表小而内表大的情况，后者适用于外表大而内表小的情况。`exists`以外层表为驱动表，先被访问外表；`in`以内表为驱动表，先执行子查询。很多时候用`exists`代替`in`是一个好的选择：`select num from a where num in (select num from b);`。用下面的语句替换：`select num from a where exists(select 1 from b where num = a.num);`。

3. 不要使用`select * from t;`，用具体的字段列表代替“*”，不要返回用不到的任何字段。

4. 尽量使用数字型字段，若只含数值信息的字段尽量不要设计为字符型，这会降低查询和连接的性能，并会增加存储开销。这是因为引擎在处理查询和连接时会逐个比较字符串中每一个字符，而对于数字型而言只需要比较一次就够了。

5. 在所有的存储过程和触发器的开始处设置`SET NOCOUNT ON`，在结束时设置`SET NOCOUNT OFF`。无需在执行存储过程和触发器的每个语句后向客户端发送`DONE_IN_PROC`消息。

6. 尽量避免向客户端返回大数据量，若数据量过大，应该考虑相应需求是否合理。

7. 尽量避免大事务操作，提高系统并发能力。

8. 尽量用`union all`代替`union`，前提条件是两个结果集没有重复数据。`union`要将结果集合并后再进行唯一性过滤操作，这就会涉及到排序，增加大量的 CPU 运算，加大资源消耗及延迟。

9. 不使用`ORDER BY RAND()`，比如可以将`select id from `dynamic` order by rand() limit 1000;`改成`select id from `dynamic` t1 join (select rand() * (select max(id) from `dynamic`) as nid) t2 on t1.id > t2.nidlimit 1000;`。


### 八、转摘

* [MySQL SQL语句优化技巧](http://www.uml.org.cn/sjjm/201610184.asp)
* [19 条立竿见影的 MySQL 优化技巧！](https://mp.weixin.qq.com/s/xqGL_oM8lsPV2mFcslkGCA)



