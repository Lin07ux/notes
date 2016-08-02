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

