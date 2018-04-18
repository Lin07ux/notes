## 一、简介

`ORDER BY`子句用来给选择的结果根据指定的字段进行排序，默认情况下使用升序排列。可以同时指定多个排序字段，并给每个排序字段使用不同的排序方式。

```sql
SELECT id, name FROM fruits ORDER BY id ASC, name DESC;
```

排序方式由`asc`和`desc`两个参数指出，默认是按照`asc`来排序，即升序。

