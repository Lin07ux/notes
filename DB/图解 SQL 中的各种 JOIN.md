SQL 中的 JOIN 查找类似于数学中集合的概念，对应于集合的概念，SQL JOIN 可以有如下的集中方式：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229097188.png)

下面就使用图形加示例的方式来进行解释各种 JOIN 的效果。

### 约定

下文将使用两个数据库表 Table_A 和 Table_B 来进行示例讲解(基于 MySQL 数据库)，其结构与数据分别如下：

```shell
mysql> SELECT * FROM Table_A ORDER BY PK ASC;
+----+------------+
| PK | Value      |
+----+------------+
|  1 | FOX        |
|  2 | COP        |
|  3 | TAXI       |
|  4 | LINCION    |
|  5 | ARIZONA    |
|  6 | WASHINGTON |
|  7 | DELL       |
| 10 | LUCENT     |
+----+------------+
8 rows in set (0.00 sec)

mysql> SELECT * from Table_B ORDER BY PK ASC;
+----+-----------+
| PK | Value     |
+----+-----------+
|  1 | TROT      |
|  2 | CAR       |
|  3 | CAB       |
|  6 | MONUMENT  |
|  7 | PC        |
|  8 | MICROSOFT |
|  9 | APPLE     |
| 11 | SCOTCH    |
+----+-----------+
8 rows in set (0.00 sec)
```

### INNER JOIN

`INNER JOIN`一般被译作内连接。内连接查询能将左表（表 A）和右表（表 B）中能关联起来的数据连接后返回。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229236237.png)

**示例查询**：

```SQL
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
INNER JOIN Table_B B
ON A.PK = B.PK;
```

**查询结果**：

```shell
+------+------+------------+----------+
| A_PK | B_PK | A_Value    | B_Value  |
+------+------+------------+----------+
|    1 |    1 | FOX        | TROT     |
|    2 |    2 | COP        | CAR      |
|    3 |    3 | TAXI       | CAB      |
|    6 |    6 | WASHINGTON | MONUMENT |
|    7 |    7 | DELL       | PC       |
+------+------+------------+----------+
5 rows in set (0.00 sec)
```

> 注：其中 A 为 Table_A 的别名，B 为 Table_B 的别名，下同。

### LEFT JOIN

`LEFT JOIN`一般被译作左连接，也写作`LEFT OUTER JOIN`。左连接查询会返回左表（表 A）中所有记录，不管右表（表 B）中有没有关联的数据。在右表中找到的关联数据列也会被一起返回。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229352381.png)

**示例查询**：

```SQL
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
LEFT JOIN Table_B B
ON A.PK = B.PK;
```

**查询结果**：

```shell
+------+------+------------+----------+
| A_PK | B_PK | A_Value    | B_Value  |
+------+------+------------+----------+
|    1 |    1 | FOX        | TROT     |
|    2 |    2 | COP        | CAR      |
|    3 |    3 | TAXI       | CAB      |
|    4 | NULL | LINCION    | NULL     |
|    5 | NULL | ARIZONA    | NULL     |
|    6 |    6 | WASHINGTON | MONUMENT |
|    7 |    7 | DELL       | PC       |
|   10 | NULL | LUCENT     | NULL     |
+------+------+------------+----------+
8 rows in set (0.00 sec)
```

### RIGHT JOIN

`RIGHT JOIN`一般被译作右连接，也写作`RIGHT OUTER JOIN`。右连接查询会返回右表（表 B）中所有记录，不管左表（表 A）中有没有关联的数据。在左表中找到的关联数据列也会被一起返回。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229451176.png)

**示例查询**：

```sql
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
RIGHT JOIN Table_B B
ON A.PK = B.PK;
```

**查询结果**：

```shell
+------+------+------------+-----------+
| A_PK | B_PK | A_Value    | B_Value   |
+------+------+------------+-----------+
|    1 |    1 | FOX        | TROT      |
|    2 |    2 | COP        | CAR       |
|    3 |    3 | TAXI       | CAB       |
|    6 |    6 | WASHINGTON | MONUMENT  |
|    7 |    7 | DELL       | PC        |
| NULL |    8 | NULL       | MICROSOFT |
| NULL |    9 | NULL       | APPLE     |
| NULL |   11 | NULL       | SCOTCH    |
+------+------+------------+-----------+
8 rows in set (0.00 sec)
```

### FULL OUTER JOIN

`FULL OUTER JOIN`一般被译作外连接、全连接，实际查询语句中可以写作`FULL OUTER JOIN`或`FULL JOIN`。外连接查询能返回左右表里的所有记录，其中左右表里能关联起来的记录被连接后返回。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229553658.png)

**示例查询**：

```sql
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
FULL OUTER JOIN Table_B B
ON A.PK = B.PK;
```

由于 MySQL 中不支持`FULL OUTER JOIN`，所以需要使用`UNION`来模拟结果：

```SQL
SELECT * FROM Table_A
LEFT JOIN Table_B ON Table_A.PK = Table_B.PK

UNION ALL

SELECT * FROM Table_A
RIGHT JOIN Table_B  ON Table_A.PK = Table_B.PK
WHERE Table_A.PK IS NULL;
```

**查询结果**：

```shell
+------+------------+------+-----------+
| PK   | Value      | PK   | Value     |
+------+------------+------+-----------+
|    1 | FOX        |    1 | TROT      |
|    2 | COP        |    2 | CAR       |
|    3 | TAXI       |    3 | CAB       |
|    4 | LINCION    | NULL | NULL      |
|    5 | ARIZONA    | NULL | NULL      |
|    6 | WASHINGTON |    6 | MONUMENT  |
|    7 | DELL       |    7 | PC        |
|   10 | LUCENT     | NULL | NULL      |
| NULL | NULL       |    8 | MICROSOFT |
| NULL | NULL       |    9 | APPLE     |
| NULL | NULL       |   11 | SCOTCH    |
+------+------------+------+-----------+
11 rows in set (0.00 sec)
```

### LEFT JOIN EXCLUDING INNER JOIN

返回左表有但右表没有关联数据的记录集。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229746773.png)

**示例查询**：

```sql
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
LEFT JOIN Table_B B ON A.PK = B.PK
WHERE B.PK IS NULL;
```

**查询结果**：

```shell
+------+------+---------+---------+
| A_PK | B_PK | A_Value | B_Value |
+------+------+---------+---------+
|    4 | NULL | LINCION | NULL    |
|    5 | NULL | ARIZONA | NULL    |
|   10 | NULL | LUCENT  | NULL    |
+------+------+---------+---------+
3 rows in set (0.00 sec)
```

### RIGHT JOIN EXCLUDING INNER JOIN

返回右表有但左表没有关联数据的记录集。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229825948.png)

**示例查询**：

```sql
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
RIGHT JOIN Table_B B ON A.PK = B.PK
WHERE A.PK IS NULL;
```

**查询结果**：

```shell
+------+------+---------+-----------+
| A_PK | B_PK | A_Value | B_Value   |
+------+------+---------+-----------+
| NULL |    8 | NULL    | MICROSOFT |
| NULL |    9 | NULL    | APPLE     |
| NULL |   11 | NULL    | SCOTCH    |
+------+------+---------+-----------+
3 rows in set (0.00 sec)
```

### FULL OUTER JOIN EXCLUDING INNER JOIN

返回左表和右表里没有相互关联的记录集。

**文氏图**：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505229903471.png)

**示例查询**：

```sql
SELECT A.PK AS A_PK, B.PK AS B_PK,
       A.Value AS A_Value, B.Value AS B_Value
FROM Table_A A
FULL OUTER JOIN Table_B B ON A.PK = B.PK
WHERE A.PK IS NULL OR B.PK IS NULL;
```

因为使用到了`FULL OUTER JOIN`，MySQL 在执行该查询时会报错，所以需要使用`UNION`来模拟：

```sql
SELECT *FROM Table_A
LEFT JOIN Table_B ON Table_A.PK = Table_B.PK
WHERE Table_B.PK IS NULL

UNION ALL

SELECT * FROM Table_A
RIGHT JOIN Table_B ON Table_A.PK = Table_B.PK
WHERE Table_A.PK IS NULL;
```

**查询结果**：

```shell
+------+---------+------+-----------+
| PK   | Value   | PK   | Value     |
+------+---------+------+-----------+
|    4 | LINCION | NULL | NULL      |
|    5 | ARIZONA | NULL | NULL      |
|   10 | LUCENT  | NULL | NULL      |
| NULL | NULL    |    8 | MICROSOFT |
| NULL | NULL    |    9 | APPLE     |
| NULL | NULL    |   11 | SCOTCH    |
+------+---------+------+-----------+
6 rows in set (0.00 sec)
```

### 转摘

[图解 SQL 里的各种 JOIN](https://zhuanlan.zhihu.com/p/29234064)

