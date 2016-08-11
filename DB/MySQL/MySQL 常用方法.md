## 字符串方法
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

参数：`str`是要被替换的原字符串，`from_str`是要被替换的那部分子字符串，`to_str`是要替换
成的子字符串。这里的`str`可以是当前操作的表中的一个列的名称，表示操作对应的列的值。

效果：将字符串`str`中所有的`from_str`子串替换成`to_str`。

示例：将表中的 name 字段中开头的星号`*`去除掉。

```sql
UPDATE tbl_name SET name=REPLACE(name, '*', '') WHERE name like '*%';
```

### LEFT
语法：`LEFT(str, length)`

参数：`str`是要被截取的原字符串，`length`是要截取的长度

效果：从 str 字符串起始处开始向后截取字符串，截取长度为 length。

示例：截取出 content 字段的前 200 个字符。

```sql
SELECT LEFT(content, 200) as abstract FROM ... WHERE ...
```

### RIGHT
语法：`RIGHT(str, length)`

参数：`str`是要被截取的原字符串，`length`是要截取的长度

效果：从字符串 str 的末尾处开始向前截取字符串，截取长度为 length。

示例：截取出 content 字段的最后的 200 个字符。

```sql
SELECT RIGHT(content, 200) as abstract FROM ... WHERE ...
```

### SUBSTRING
语法：`SUBSTRING(str, pos [, length])`

参数：`str`是要被截取的原字符串，`pos`是开始截取的起始位置(字符串起始位置为 1)，`length`是要截取的长度。

效果：从字符串 str 的 pos 处开始向后截取字符串，截取长度为 length。如果没有指定 length 则截取到字符串末尾。如果 pos 是负数，则表示从字符串末尾处倒数得出截取的起始位置。

示例：截取出 content 字段第 5 个字符后的所有字符，和截取第 5 个字符后的 200 个字符。

```sql
SELECT SUBSTRING(content, 5) as abstract FROM ... WHERE ...
SELECT SUBSTRING(content, 5, 200) as abstract FROM ... WHERE ...
```

### SUBSTRING_INDEX
语法：`SUBSTRING_INDEX(str, delim, count)`

参数：`str`是要被截取的原字符串，`delim`是分隔符，可以是任意字符，`count`指定字符串中第几个 delim 处是截取点。

效果：返回字符串 str 中第 count 个分隔符 delim 前的所有字符，不包含第 count 个 delim 字符。如果 count 为负数，那么就是从字符串 str 的末尾开始查找分隔符 delim，截取找到的分隔符右侧的所有字符。如果字符串中不存在指定的分隔符 delim，那么就会截取整个字符串。

示例：截取出字符串`www.mysql.com`中的第二个`.`之前的所有字符，倒数第二个`.`后面的所有字符，倒数第二个和倒数第一个`.`之间的所有字符。

```sql
SELECT SUBSTRING_INDEX('www.mysql.com', '.', 2);  # www.mysql
SELECT SUBSTRING_INDEX('www.mysql.com', '.', -2); # mysql.com

# mysql
SELECT SUBSTRING_INDEX(SUBSTRING_INDEX('www.mysql.com', '.', -2), '.' 1);
```

## 系统方法
### VERSION
查看数据库版本号。

```sql
SELECT VERSION();
```

### USER
查看当前的 user：

```sql
SELECT USER();
```

### DATABAE
查看当前操作的数据库名：

```sql
SELECT DATABASE();
```

