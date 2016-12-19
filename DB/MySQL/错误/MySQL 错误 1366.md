## 问题
在字符集、语句等配置都正常的情况下，插入数据的时候总是提示 1366 错误：

```
SQLSTATE[HY000]: General error: 1366 Incorrect integer value:
```

## 原因
一般都是提示数值字段的格式不正确，如 integer、decimal 等字段。可以尝试传入严格符合格式要求的值进行插入。一般这时候就能正常了。

这是由于 MySQL 默认是工作在严格状态(`strict mode`)。在这种工作模式下，MySQL 不会自动将数值转换成要求的格式，而是触发错误。自然，在非严格模式(`no-strict mode`)下，MySQL 就会自动转换格式了。

比如，在非严格模式下，向一个 UNSIGNED 字段传入一个负数，MySQL 会自动将其转换成 0，因为这是最接近字段格式要求的数值；而在严格模式下，MySQL 就不会进行转换，而是直接抛出一个错误。

> 	this is pretty accurate: "In non-strict mode, the MySQL server converts erroneous input values to the closest legal values (as determined from column definitions) and continues on its way. For example, if you attempt to store a negative value into an UNSIGNED column, MySQL converts it to zero, which is the nearest legal value for the column." in strict mode it will directly skip those conversions and throw that error you are seeing. maybe you can post the mapping of the entity you are trying to update, and the actual data you are sending to it in your form

可以通过下面的 SQL 语句确认 MySQL 的工作模式：

```sql
SELECT @@GLOBAL.sql_mode;
SELECT @@SESSION.sql_mode;
```

如果这这两者中有任意一个的输出中，包含有`STRICT_TRANS_TABLES`，那 MySQL 就是工作在严格模式下了。

## 解决
可以通过下面的语句关闭 MySQL 的严格模式：

```sql
SET @@global.sql_mode= 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
SET @@session.sql_mode= 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
```

参考：[General error: 1366 Incorrect integer value with Doctrine 2.1 and Zend Form update](http://stackoverflow.com/questions/8874647/general-error-1366-incorrect-integer-value-with-doctrine-2-1-and-zend-form-upda)


