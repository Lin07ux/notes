> 转摘：[超全面MySQL语句加锁分析（下篇）（求转）](https://mp.weixin.qq.com/s/9WWBXLNoUcTkS4DJnM5ViA)

## 五、INSERT 语句

INSERT 语句一般情况下不加锁，不过当前事务在插入一条记录前需要先定位到该记录在 B+ 树中的位置，如果该位置的下一条记录已经被加了 Gap 锁（Next-Key 锁也包含 Gap 锁），那么当前事务会在该记录上加上一种类型为**插入意向锁**的锁，并且事务进入等待状态。

下面要看的就是两种 INSERT 语句遇到的特殊情况。

### 5.1 遇到重复键（duplicate key）

在插入一条新纪录时，首先要做的事情其实是定位到这条新纪录应该插入到 B+ 树的哪个位置。如果定位位置时发现了有已存在记录的主键或者唯一二级索引列与待插入记录的主键或者唯一二级索引列相同（不过可以有多条记录的唯一二级索引列的值同时为 NULL），那么此时是会报错的。

比如插入一条主键值已经被包含在 hero 表中的数据：

```sql
mysql> BEGIN;
Query OK, 0 rows affected (0.01 sec)

mysql> INSERT INTO hero VALUES(20, 'g关羽', '蜀');
ERROR 1062 (23000): Duplicate entry '20' for key 'PRIMARY'
```

当然，在生成报错信息之前，其实还需要做一件非常重要的事情——对聚簇索引中`number = 20`的记录加**S 型记录锁**，不过具体的行锁在不同隔离级别下是不一样的：

* 在`READ UNCOMMITTED/READ COMMITTED`隔离级别下，加的是 **S 型记录锁**。
* 在`REPEATABLE READ/SERIALIZABLE`隔离级别下，加的是 **S 型 Next-Key锁**。

如果是唯一二级索引列值重复，比如再把普通二级索引 idx_name 改成唯一二级索引 uk_name：

```sql
ALTER TABLE hero DROP INDEX idx_name, ADD UNIQUE KEY uk_name (name);
```

然后执行：

```sql
mysql> BEGIN;
Query OK, 0 rows affected (0.01 sec)

mysql> INSERT INTO hero VALUES(30, 'c曹操', '魏');
ERROR 1062 (23000): Duplicate entry 'c曹操' for key 'uk_name'
```

很显然，hreo 表中之前就包含`name = 'c曹操'`的记录，如果在插入一条`name = 'c曹操'`的新记录，虽然插入对应的聚簇索引记录没有问题，但是在插入 uk_name 唯一二级索引记录时就会报错。不过在报错之前还是会把`name = 'c曹操'`的那条二级索引记录加一个 **S 锁**。

需要注意的是：**不管哪个隔离级别，针对在插入新纪录时遇到重复的唯一二级索引列的情况，会对已经在 B+ 树中的唯一二级索引记录加 Next-Key 锁**。

> 按理说在 READ UNCOMMITTED/READ COMMITTED 隔离级别下，不应该出现 Next-Key 锁，这主要是考虑到如果只加正经记录锁的话，在一些情况下可能出现有多条记录的唯一二级索引列都相同的情况。当然，出现这种情况的原因比较复杂，这里就不多说了。

另外，如果使用的是`INSERT ... ON DUPLICATE KEY ...`这样的语法来插入纪录时，如果遇到主键或者唯一二级索引列值重复的情况，会对 B+ 树中已存在的相同键值的记录加 **X 锁**，而不是 **S 锁**。

### 5.2 外键检查

MySQL 还支持外键，比如再为三国英雄的战马键一个表：

```sql
CREATE TABLE horse (
    number INT PRIMARY KEY,
    horse_name VARCHAR(100),
    FOREIGN KEY (number) REFERENCES hero(number)
)Engine=InnoDB CHARSET=utf8;
```

这样 hero 表就算是一个父表，新建的 horse 表就算一个子表，其中 horse 表中的 number 列是参照 hero 表中的 number 列。

现在如果向子表中插入一条记录时：

* 如果带插入记录的 number 值在 hreo 表中能找到。

    比如说，为 horse 表中新插入的记录 number 的值为 8，而在 hero 表中 number 值为 8 的记录代表的是曹操，他的马是绝影：
        
    ```sql
    mysql> BEGIN;
    Query OK, 0 rows affected (0.00 sec)
    
    mysql> INSERT INTO horse VALUES(8, '绝影');
    Query OK, 1 row affected (5 min 58.04 sec)
    ```

    在插入成功之前，不论当前事务的隔离级别是什么，只需要直接给父表 hero 的 number 值为 8 的记录加一个 **S 型记录锁**。
    
* 如果带插入记录的 number 值在 hreo 表中找不到。

    比如为 horse 表中新插入的记录为 number 值为 2，而在 hero 表中不存在 number 值为 2 的记录：
        
    ```sql
    INSERT INTO horse VALUES(5, '绝影');
    ```

    虽然插入失败了，但这个过程中，如果是在 REPEATABLE READ/SERIALIZABLE 隔离级别下，需要对父表 hero 的某些记录加 **Gap 锁**，而在 READ UNCOMMITTED/READ COMMITTED 隔离级别下并不对记录加锁。
    
## 六、总结

MySQL 的语句加锁分析并没有把所有的情形都列举出来，只是给出来一个大致轮廓，可以在之后的工作学习中再多做总结。

