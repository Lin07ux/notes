### 将一个表里的数据填充在另一个表里 
```sql
INSERT INTO table_a (filed1,filed2...) SELECT f1,f2,... FROM table_b
```

### 查询一个表中没有存在在另一个表的数据
```sql
SELECT * FROM A WHERE id NOT IN ( SELECT id FROM B);
 
# 或者
SELECT * FROM A WHERE NOT EXISTS ( SELECT 1 FROM B WHERE B.id = A.id );
 
# 或者
SELECT A.* FROM A LEFT JOIN B ON (A.id = B.id) WHERE B.id IS NULL
```

### 找出某个字段有重复记录的记录
列出 username 字段有重复的数据：

```sql
SELECT username, count(username) as count FROM test GROUP BY username HAVING count(username) >1 ORDER BY count DESC;

-- 或者下面这种方法，速度更快
SELECT username, count(username) as count FROM test WHERE username in (SELECT username FROM test GROUP BY username HAVING count(username) > 1);
```

### 更新表的某个字段为另一个表的某个字段的值

```sql
UPDATE tb_1 AS f LEFT JOIN tb_2 AS l ON l.user = f.id SET f.name = l.name WHERE f.age > 18;
```

