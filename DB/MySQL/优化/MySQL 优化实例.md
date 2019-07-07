> 转摘：[你应该避免的8种常见SQL错误用法！](https://mp.weixin.qq.com/s/5-IJz1xNvrocsZGX-mirpw)

### 1. 关联更新、删除

虽然 MySQL5.6 引入了物化特性，但需要特别注意它目前仅仅针对查询语句优化。对于更新或删除需要手工重写成`JOIN`。

比如下面`UPDATE`语句，MySQL 实际执行的是循环/嵌套子查询(DEPENDENT SUBQUERY)，其执行时间可想而知：

```sql
UPDATE operation o SET status = 'applying' WHERE o.id IN (
  SELECT id FROM (
    SELECT o.id, o.status FROM operation o
      WHERE o.group = 123 AND o.status <> 'done'
      ORDER BY o.parent, o.id 
      LIMIT 1
  ) t
);
```

执行计划：

```
+----+--------------------+-------+-------+---------------+---------+---------+-------+
| id | select_type        | table | type  | possible_keys | key     | key_len | ref   |
+----+--------------------+-------+-------+---------------+---------+---------+-------+
| 1  | PRIMARY            | o     | index |               | PRIMARY | 8       |       |
| 2  | DEPENDENT SUBQUERY |       |       |               |         |         |       |
| 3  | DERIVED            | o     | ref   | idx_2,idx_5   | idx_5   | 8       | const |
+----+--------------------+-------+-------+---------------+---------+---------+-------+
```

而如果改成`JOIN`方式：

```sql
UPDATE operation o JOIN (
  SELECT o.id, o.status FROM operation o
    WHERE o.group = 123 AND o.status <> 'done'
    ORDER BY o.parent, o.id 
    LIMIT 1
) t ON o.id = t.id 
SET status = 'applying' 
```

执行计划就没有了 DEPENDENT SUBQUERY，简化为：

```
+----+--------------------+-------+-------+---------------+---------+---------+-------+
| id | select_type        | table | type  | possible_keys | key     | key_len | ref   |
+----+--------------------+-------+-------+---------------+---------+---------+-------+
| 1  | PRIMARY            | o     | index |               | PRIMARY | 8       |       |
| 2  | DERIVED            | o     | ref   | idx_2,idx_5   | idx_5   | 8       | const |
+----+--------------------+-------+-------+---------------+---------+---------+-------+
```

### 2. 混合排序改用分别排序

MySQL 不能利用索引进行混合排序。比如，对于如下的 SQL 语句，分别使用两个字段进行升序和降序排列，会造成全表扫描：

```sql
SELECT * FROM my_order o 
  INNER JOIN my_appraise a ON a.orderid = o.id 
  ORDER BY a.is_reply ASC, a.appraise_time DESC 
  LIMIT 0, 20 
```

由于`is_reply`字段只有 0 和 1 两种状态，按照下面的方法重写后，执行时间可以大幅降低：

```sql
SELECT * FROM (
  (
    SELECT * FROM my_order o
    INNER JOIN my_appraise a ON a.orderid = o.id AND is_reply = 0 
    ORDER BY appraise_time DESC 
    LIMIT0, 20
  )
  UNION ALL 
  (
    SELECT * FROM my_order o
    INNER JOIN my_appraise a ON a.orderid = o.id AND is_reply = 1 
    ORDER BY appraise_time DESC 
    LIMIT 0, 20)
  )
) t 
  ORDER BY is_reply ASC, appraisetime DESC 
  LIMIT 20;
```

### 3. EXISTS 语句改为 JOIN

MySQL 对待`EXISTS`子句时，仍然采用嵌套子查询的执行方式。如下面的 SQL 语句：

```sql
SELECT * FROM my_neighbor n 
  LEFT JOIN my_neighbor_apply sra ON n.id = sra.neighbor_id AND sra.user_id = 'xxx' 
  WHERE n.topic_status < 4 AND EXISTS (
    SELECT 1 FROM message_info m WHERE n.id = m.neighbor_id AND m.inuser = 'xxx'
  ) AND n.topic_type <> 5；
```

这个语句的执行计划会用到 DEPENDENT SUBQUERY，所以会比较慢。

如果改成使用`JOIN`语句，则可以大大降低执行时间：

```sql
SELECT * FROM my_neighbor n 
  INNER JOIN message_info m ON n.id = m.neighbor_id AND m.inuser = 'xxx' 
  LEFT JOIN my_neighbor_apply sra ON n.id = sra.neighbor_id AND sra.user_id = 'xxx' 
  WHERE n.topic_status < 4 AND n.topic_type <> 5 
```

### 4. 提前缩小范围

下面这段代码的目的是：先做一系列的左连接，排序后取前 15 条记录：

```sql
SELECT * FROM my_order o 
  LEFT JOIN my_userinfo u ON o.uid = u.uid
  LEFT JOIN my_productinfo p ON o.pid = p.pid 
  WHERE o.display = 0 AND o.ostaus = 1
  ORDER BY o.selltime DESC 
  LIMIT 0, 15;
```

当`my_order`表较大的时候，这会导致连接结果很大，最终需要对大量的数据进行排序。

由于只需要根据`my_order`表的条件进行过滤和排序，那么可以先缩小连接的左表`my_order`的数据量，然后再进行连接操作，如下：

```sql
SELECT * FROM (
  SELECT * FROM my_order o
  WHERE o.display = 0 AND o.ostaus = 1
  ORDER  BY o.selltime DESC 
  LIMIT  0, 15
) o 
  LEFT JOIN my_userinfo u ON o.uid = u.uid 
  LEFT JOIN my_productinfo p ON o.pid = p.pid 
  ORDER BY o.selltime DESC
  LIMIT 0, 15;
```

### 5. 中间结果集下推

下面这个语句已经对左表进行了适当的优化，但是右表依旧是全表聚合查询，在表数量特别大的情况下会导致整个语句的性能下降：

```sql
SELECT a.*, c.allocated FROM ( 
  SELECT resourceid FROM my_distribute d 
  WHERE isdelete = 0 AND cusmanagercode = '1234567' 
  ORDER BY salecode
  LIMIT 20
) a 
LEFT JOIN ( 
  SELECT resourcesid, sum(ifnull(allocation, 0) * 12345) allocated FROM my_resources 
  GROUP BY resourcesid
) c 
  ON a.resourceid = c.resourcesid
```

其实对于右子查询，左连接最后结果集只关心能和主表`resourceid`能匹配的数据。因此可以重写语句如下：

```sql
SELECT a.*, c.allocated FROM ( 
  SELECT resourceid FROM my_distribute d
  WHERE isdelete = 0 AND cusmanagercode = '1234567' 
  ORDER BY salecode
  LIMIT 20
) a 
LEFT JOIN ( 
  SELECT resourcesid, sum(ifnull(allocation, 0) * 12345) allocated 
  FROM my_resources r, ( 
    SELECT resourceid FROM my_distribute d
    WHERE isdelete = 0 AND cusmanagercode = '1234567' 
    ORDER BY salecode
    LIMIT 20
  ) a 
  WHERE r.resourcesid = a.resourcesid 
  GROUP BY resourcesid
) c 
  ON a.resourceid = c.resourcesid;
```

但是子查询`a`在 SQL 语句中出现了多次，这种写法不仅存在额外的开销，还使得整个语句显的繁杂。使用`WITH`语句再次重写：

```sql
WITH a AS ( 
  SELECT resourceid FROM my_distribute d 
  WHERE isdelete = 0 AND cusmanagercode = '1234567' 
  ORDER BY salecode
  LIMIT 20
)
SELECT a.*, c.allocated FROM a 
LEFT JOIN ( 
  SELECT resourcesid, sum(ifnull(allocation, 0) * 12345) allocated 
  FROM my_resources r, a 
  WHERE r.resourcesid = a.resourcesid 
  GROUP BY resourcesid
) c
ON a.resourceid = c.resourcesid;
```


