在批量插入数据的时候，可能会出现主键或者唯一键冲突的情况，从而导致插入操作失败。此时，可以有如下几种办法来避免，分别对应不同的需求场景。

> 在 MySQL 中 UNIQUE 索引将会对 null 字段失效，也就是说(a 字段上建立唯一索引)：
> `insert into test(a) values(null);` 和 `insert into test(a) values(null)`
都是可以插入的（联合唯一索引也一样）。

### 1. 使用 ignore 关键字
避免重复插入主键或唯一键冲突的记录，可以使用下面的语句：

```sql
INSERT IGNORE INTO table_name(fields...) VALUES(values...);
```

这样当有重复记录时就会忽略这个记录继续插入后面的记录。执行后返回数字为插入的总记录数减去重复记录的数量。

适用于保持当前表的数据，插入的数据不能覆盖原有数据的情况。比如，应用在复制表时，避免重复记录：

```sql
INSERT IGNORE INTO table(name) SELECT  name FROM table2;
```

### 使用 replace into
**语法格式**

1. `replace into table_name(col_name, ...) values(...);`
2. `replace into table_name(col_name, ...) select ...;`
3. `replace into table_name set col_name=value, ...;`

**说明**

REPLACE 的运行与 INSERT 很相像，但是如果旧记录与新记录有相同的值，则在新记录被插入之前，旧记录被删除，即：

1. 尝试把新行插入到表中 
2. 当因为对于主键或唯一关键字出现重复关键字错误而造成插入失败时：
    - 从表中删除含有重复关键字值的冲突行
    - 再次尝试把新行插入到表中

> 需要注意的是：REPLACE 语句在发生冲突的时候，是先删除原先的冲突行的，所以插入冲突的数据后，这条记录的主键可能会发生变化。
> 比如，对于主键是自增的，而由于其他的唯一键导致有冲突，在插入数据的时候，旧数据整行被删除，包括主键，然后插入数据的时候，主键会被设置为插入数据中的主键值，或者是自动生成。

*这个语句起作用的前提是：表有一个 PRIMARY KEY 或 UNIQUE 索引*。否则，使用一个 REPLACE 语句没有意义，会与 INSERT 作用相同，因为没有索引被用于确定是否新行复制了其它的行。

所以，这个方案适用于用新数据替换旧数据的情况。注意：这里的替换是删除原有重复记录，然后插入新的记录。如果这个表有字段是其他表的外键，那么就可能会导致失败。

**返回值**

REPLACE 语句会返回一个数，来指示受影响的行的数目。该数是*被删除和被插入的行数的和*。

受影响的行数可以容易地确定是否 REPLACE 只添加了一行，或者是否 REPLACE 也替换了其它行：检查该数是否为1（添加）或更大（替换）。

**示例**

```sql
# 必有某个字段为唯一索引或主键
REPLACE INTO table_name(...) VALUES(...)

# 在 SQL Server 中可以这样处理(phone字段为唯一键)：
IF NOT EXISTS (SELECT phone FROM t WHERE phone= '1') 
    INSERT INTO t(phone, update_time) VALUES('1', getdate()) 
else 
    UPDATE t SET update_time = getdate() WHERE phone= '1'
```

下面是一个另例子，其中 id 是自增主键，name 是唯一键，age 是普通字段：

```sql
# 初始数据
# 数据： 1  Lin07ux  26
INSERT INTO test VALUE (1, 'Lin07ux', 26);

# 插入无冲突数据，效果和 INSERT INTO 语句一样
# 数据： 2  lin  25
REPLACE INTO test (`name`, `age`) VALUE ('lin'， 25);

# 插入 id 冲突数据，和 UPADTE 语句效果类似
# 数据： 1  L7  26
REPLACE INTO test VALUE (1, 'L7', 26);

# 插入 name 冲突数据，数据的 id 发生了变换
# 数据： 3  lin  26
REPLACE INTO test (`name`, `age`) VALUE ('lin'， 26);
```

从这个例子可以看出，REPLACE INTO 对待冲突是先删除旧的整行数据，然后重新插入数据(像插入新的没有冲突的数据一样)，也即是先`DELETE`，然后`INSERT INTO`。


### 使用 ON DUPLICATE KEY UPDATE
如果您指定了`ON DUPLICATE KEY UPDATE`，并且插入行后会导致在一个 UNIQUE 索引或 PRIMARY KEY 中出现重复值，则执行旧行 UPDATE 操作。

也就是说，如果有重复记录，新数据会被用来更新旧数据，这样原有记录的主键不会改变，而且一般也不会因存在某字段是其他表的外键而导致的失败。

返回值是一个数字：新数据的总记录数 + 原有记录被更新数。

例如，如果列 a 被定义为 UNIQUE，并且包含值 1，则以下两个语句具有相同的效果：

```sql
INSERT INTO table (a,c) VALUES (1,3) ON DUPLICATE KEY UPDATE c=c+1; 
# 效果等价于
UPDATE table SET c=c+1 WHERE a=1;
```

*可以在 UPDATE 子句中使用`VALUES(col_name)`函数从`INSERT...UPDATE`语句的`INSERT`部分引用列值。*

换句话说，如果没有发生重复关键字冲突，则 UPDATE 子句中的 VALUES(col_name) 可以引用被插入的 col_name 的值。本函数特别适用于多行插入。VALUES() 函数只在`INSERT...UPDATE`语句中有意义，其它时候会返回 NULL。

```sql
INSERT INTO table (a,b,c) VALUES (1,2,3), (4,5,6) ON DUPLICATE KEY UPDATE c = VALUES(a) + VALUES(b);

# 以下两个语句作用相同：
INSERT INTO table (a,b,c) VALUES (1,2,3) ON DUPLICATE KEY UPDATE c = 3;
INSERT INTO table (a,b,c) VALUES (4,5,6) ON DUPLICATE KEY UPDATE c = 9;
```

下面是一个更实际的例子，将一个表的数据导入到另外一个表中，导入过程中需要考虑重复记录问题。唯一索引为`email`：

```sql
INSERT INTO table_name1(title, first_name, last_name, email, phone, user_id, role_id, status, campaign_id)

    SELECT '', '', '', t2.email, t2.phone, NULL, NULL, 'pending', 29

    FROM table_name2 as t2

    WHERE t2.status = 1 

ON DUPLICATE KEY UPDATE table_name1.status = 'pending'
```

**转摘：**[mysql忽略主键冲突、避免重复插入的几种方式](http://my.oschina.net/leejun2005/blog/150510)


