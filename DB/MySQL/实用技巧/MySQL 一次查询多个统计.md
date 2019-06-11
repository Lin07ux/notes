在查询中可能需要统计不同字段值的数量，可以通过 GROUP BY 来分别统计，也可以一次性统计得到一条数据。

如，需要区分不同颜色的商品数量：

```sql
SELECT color, COUNT(*) AS counts FROM items GROUP BY color;
 
SELECT COUNT(color = 'blue' OR NULL) as blue, COUNT(color = 'red' OR NULL) AS red FROM items;
```


