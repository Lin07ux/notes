### CONCAT
语法：`CONCAT(str1,str2,...)`
参数：这个方法的参数可以是一个字符串、数字，也可以是当前操作的表中的某一列的名。 
效果：返回连接参数产生的字符串。如有任何一个参数为NULL ，则返回值为 NULL。
示例：更新一个字段的值为它本身的值连接上一个字符串，可以使用`CONCAT()`方法。

```sql
UPDATE tbl_name SET column=CONCAT(column, str1, str2, ...) WHERE ...
```

### REPLACE
语法：`REPLACE(str, from_str, to_str)`
参数：`str`是要被替换的原字符串，`from_str`是要被替换的那部分子字符串，`to_str`是要替换成的子字符串。这里的`str`可以是当前操作的表中的一个列的名称，表示操作对应的列的值。
效果：将字符串`str`中所有的`from_str`子串替换成`to_str`。
示例：将表中的 name 字段中开头的星号`*`去除掉。

```sql
UPDATE tbl_name SET name=REPLACE(name, '*', '') WHERE name like '*%';
```


