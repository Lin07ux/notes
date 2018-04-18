当有 ORDER BY 子句的时候，会按照 ORDER BY 子句排序后返回结果。

如果没有 ORDER BY 子句，MySQL 对 SELECT 语句的返回结果有潜规则：

* 对于 MyISAM 引擎来说，其返回顺序是其物理存储顺序；
* 对于 InnoDB 引擎来说，其返回顺序是按照主键排序的。



