## 一、简介

Case 是 SQL 查询中用得最多的逻辑判断。具有两种格式：*简单 Case 函数*和 *Case 搜索函数*。

```sql
-- 简单 case 函数
CASE sex
    WHEN '1' THEN '男'
    WHEN '2' THEN '女'
ELSE '其他' END;

-- case 搜索函数
CASE WHEN sex = '1' THEN '男'
     WHEN sex = '2' THEN '女'
ELSE '其他' END;
```

这两种方式，可以实现相同的功能。简单 Case 函数的写法相对比较简洁，但是和 Case 搜索函数相比，功能方面会有些限制，比如写判断式。

还有一个需要注意的问题：**Case 函数只返回第一个符合条件的值，剩下的 Case 部分将会被自动忽略**。比如说，下面这段 SQL，你永远无法得到“第二类”这个结果：

```sql
CASE WHEN col_1 IN ( 'a', 'b') THEN '第一类'
     WHEN col_1 IN ('a') THEN '第二类'
ELSE '其他' END
```

## 二、使用

### 2.1 将数据重新分组

有如下数据：(用国家名作为Primary Key)

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477036529144.png" width="466"/>

根据这个国家人口数据，统计亚洲和北美洲的人口数量。应该得到下面这个结果：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477036551942.png" width="400"/>

如果使用 Case 函数，SQL 代码如下：

```sql
SELECT CASE country
        WHEN '中国'   THEN '亚洲'
        WHEN '印度'   THEN '亚洲'
        WHEN '日本'   THEN '亚洲'
        WHEN '美国'   THEN '北美洲'
        WHEN '加拿大'  THEN '北美洲'
        WHEN '墨西哥'  THEN '北美洲'
    ELSE '其他' END,
    SUM(population)
FROM tbl_a
GROUP BY CASE country
        WHEN '中国'   THEN '亚洲'
        WHEN '印度'   THEN '亚洲'
        WHEN '日本'   THEN '亚洲'
        WHEN '美国'   THEN '北美洲'
        WHEN '加拿大'  THEN '北美洲'
        WHEN '墨西哥'  THEN '北美洲'
    ELSE '其他' END
```

### 2.2 用一个 SQL 语句完成不同条件的分组

有如下数据：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477037205504.png" width="551"/>

按照国家和性别进行分组，得出结果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477037226649.png" width="488"/>

普通情况下，用 UNION 也可以实现用一条语句进行查询。但是那样增加消耗(两个 Select 部分)，而且 SQL 语句会比较长。使用 Case 的话就能很好的解决：

```sql
SELECT country,
    SUM( CASE WHEN sex=1 THEN population ELSE 0 END ) AS 'male', -- 男性人口
    SUM( CASE WHEN sex=2 THEN population ELSE 0 END ) AS 'female' -- 女性人口
FROM tbl_a
GROUP BY country;
```

### 2.3 在 Check 中使用 Case 函数

在 Check 中使用 Case 函数在很多情况下都是非常不错的解决方法。

公司 A 有个规定，女职员的工资必须高于 1000 块。如果用 Check 和 Case 来表现的话，如下所示：

```sql
CONSTRAINT check_salary CHECK
    (CASE 
        WHEN sex = '2' THEN 
            CASE WHEN salary > 1000 THEN 1 ELSE 0 END
        ELSE 1 END
    = 1);
```

如果单纯使用 Check，如下所示：

```sql
CONSTRAINT check_salary CHECK
    (sex = 2 AND salary > 1000)
```

但是这样的话，女职员的条件倒是符合了，男职员就无法输入了。
        

### 2.4 根据条件有选择的 UPDATE

有如下更新条件：

1.	工资 5000 以上的职员，工资减少 10%；
2.	工资在 2000 到 4600 之间的职员，工资增加 15%。

很容易考虑的是选择执行两次 UPDATE 语句，如下所示：

```sql
--条件1
UPDATE Personnel SET salary = salary * 0.9 WHERE salary >= 5000;
--条件2
UPDATE Personnel SET salary = salary * 1.15 WHERE salary >= 2000 AND salary <= 4600;
```

但是事情没有想象得那么简单，假设有个人工资 5000 块。首先，按照条件1，工资减少 10%，变成工资 4500。接下来运行第二个 SQL 时候，因为这个人的工资是 4500 在 2000 到 4600 的范围之内，需增加 15%，最后这个人的工资结果是 5175，不但没有减少，反而增加了。如果要是反过来执行，那么工资 4600 的人相反会变成减少工资。

暂且不管这个规章是多么荒诞，如果想要一个 SQL 语句实现这个功能的话，我们需要用到 Case 函数。代码如下：

```sql
UPDATE Personnel SET salary = 
    CASE WHEN salary >= 5000 THEN salary * 0.9
        WHEN salary >= 2000 AND salary <= 4600 THEN salary * 1.15
        ELSE salary
    END
WHERE salary >= 5000 OR (salary >= 2000 AND salary <= 4600);
```

这里要注意一点，最后一行的`ELSE salary`是必需的，要是没有这行，不符合这两个条件的人的工资将会被写成 NUll。因为在 Case 函数中`Else`部分的默认值是`NULL`。

这种方法还可以在很多地方使用，比如说变更主键这种累活。一般情况下，要想把两条数据的 Primary key 交换，需要经过临时存储，拷贝，读回数据的三个过程，要是使用 Case 函数的话，一切都变得简单多了。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477040405810.png" width="505"/>

假设有如上数据，需要把主键`a`和`b`相互交换。用 Case 函数来实现的话，代码如下：

```sql
UPDATE SomeTable SET p_key = 
    CASE WHEN p_key = 'a' THEN 'b'
         WHEN p_key = 'b' THEN 'a'
         ELSE p_key
    END
WHERE p_key IN ('a', 'b');
```

同样的也可以交换两个 Unique key。需要注意的是，如果有需要交换主键的情况发生，多半是当初对这个表的设计进行得不够到位，建议检查表的设计是否妥当。

### 2.5 两个表数据是否一致的检查

Case 函数不同于 DECODE 函数。在 Case 函数中，可以使用`BETWEEN`、`LIKE`、`IS NULL`、`IN`、`EXISTS`等，可以进行子查询，从而实现更多的功能。

下面具个例子来说明。有两个表，`tbl_A`、`tbl_B`，两个表中都有 keyCol 列。现在我们对两个表进行比较，tbl_A 中的 keyCol 列的数据如果在 tbl_B 的 keyCol 列的数据中可以找到，返回结果’Matched’，如果没有找到，返回结果’Unmatched’。

要实现下面这个功能，可以使用下面两条语句：

```sql
-- 使用 IN
SELECT keyCol,
    CASE WHEN keyCol IN (SELECT keyCol FROM tbl_B) THEN 'Matched'
         ELSE 'Unmatched'
    END Label
FROM tbl_A;

-- 使用 EXISTS
SELECT keyCol,
    CASE WHEN EXISTS (SELECT * FROM tbl_B WHERE tbl_A.keyCol = tbl_B.keyCol)
            THEN 'Matched'
         ELSE 'Unmatched'
    END Label
FROM tbl_A;
```

使用 IN 和 EXISTS 的结果是相同的。当然也可以使用 NOT IN 和 NOT EXISTS，但是这个时候要注意 NULL 的情况。


### 2.6 在 Case 函数中使用合计函数

假设有下面一个表：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477041030721.png" width="631"/>

有的学生选择了同时修几门课程(100,200)也有的学生只选择了一门课程(300,400,500)。选修多门课程的学生，要选择一门课程作为主修，主修 flag 里面写入 Y。

只选择一门课程的学生，主修 flag 为 N(实际上要是写入Y的话，就没有下面的麻烦事了，为了举例子，还请多多包含)。

现在要按照下面两个条件对这个表进行查询

1.	只选修一门课程的人，返回那门课程的 ID
2.	选修多门课程的人，返回所选的主课程 ID

简单的想法就是，执行两条不同的 SQL 语句进行查询：

```sql
-- 条件1：只选择了一门课程的学生
SELECT std_id, MAX(class_id) AS main_class 
FROM Studentclass
GROUP BY std_id
HAVING COUNT(*) = 1

UNION

-- 条件2：选择多门课程的学生
SELECT std_id, class_id AS main_class
FROM Studentclass
WHERE main_class_flg = 'Y';
```

如果改成使用 Case 函数的话，代码如下：

```sql
SELECT std_id,
    CASE WHEN COUNT(*) = 1 THEN MAX(class_id) -- 只选择一门课程的学生的情况
         ELSE MAX(CASE WHEN main_class_flg = 'Y' THEN class_id
            ELSE NULL END)
    END AS main_class
FROM Studentclass
GROUP BY std_id;
```

### 2.7 查询出数据库中每天各种充值类型的金额

数据如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1477041685045.png" width="565"/>

```sql
SELECT PayTime as '支付时间（按天算）',
    SUM(CASE WHEN PayType = 0 THEN Money ELSE 0) AS '支付宝',
    SUM(CASE WHEN PayType = 1 THEN Money ELSE 0) AS '手机短信',
    SUM(CASE WHEN PayType = 2 THEN Money ELSE 0) AS '银行卡',
    SUM(CASE WHEN PayType = 3 THEN Money ELSE 0) AS '电话'
FROM WebGame
GROUP BY PayTime;
```

## 三、转摘

[你真的会玩SQL吗？Case也疯狂](http://blog.jobbole.com/95032/)

