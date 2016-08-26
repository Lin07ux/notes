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

### LENGTH
语法：`LENGTH(str)`

参数：`str`是要被统计字数的字符串，也可以指定为一个列名。

效果：返回字符串 str 的长度。一般用来计算普通字符(ASCII)的长度，处理其他字符的时候，会有问题，比如，它会把一个中文字符的长度计算为 2 或 3。如果要计算中文等其他字符的长度的时候，可以使用`CHAR_LENGTH()`方法。

示例：统计字符串`cover.jpg`和`中文`的字数。

```sql
SELECT LENGTH('cover.jpg');  # 9
SELECT LENGTH('中文');        # 6
```

### CHAR_LENGTH
语法：`CHAR_LENGTH(str)`

参数：`str`是要被统计字数的字符串，也可以指定为一个列名。

效果：返回字符串 str 的长度。这个函数能真实的反应字符的个数，比`LENGTH()`函数更安全。

示例：统计字符串`cover.jpg`和`中文`的字数。

```sql
SELECT CHAR_LENGTH('cover.jpg');  # 9
SELECT CHAR_LENGTH('中文');        # 2
```

### HEX/UNHEX
语法：`HEX(str)`，`UNHEX(hex_str)`

参数：`str`是要被转换为十六进制的字符串；`hex_str`是要恢复成正常字符串的十六进制字符串。

效果：HEX 会把字符串中的每一个字符转换成两个16进制数，UNHEX 会将字符串泛解析成一般的字符串。其中，UNHEX 和直接在字符串前面加上 0x 的效果相同。

示例：

```sql
SELECT HEX('this is a test str');
SELECT UNHEX('746869732069732061207465737420737472');
SELECT 0x746869732069732061207465737420737472;
```

![HEX/UNHEX](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472189845587.png)

## 数学方法
### MOD 取余数
语法：`MOD(x, y)`

参数：x 是被除数，y 是除数。

效果：返回 x 被 y 除后的余数。对于带有小数部分的数值也起作用，他返回除法运算后的精确余数。

示例：

```sql
SELECT MOD(30, 7), MOD(8.3, 3);
```

![MOD](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472189100843.png)

### ROUND 四舍五入
语法：`ROUND(x[, y])`

参数：x 是被舍入数，y 指定保留小数点后的位数，或小数点前的位数。

效果：返回最接近于参数 x 的整数，对 x 值进行四舍五入。如果指定了 y 参数：当 y 是非负数的时候，保留 x 到小数点后面 y 位；当 y 为负值的时候，则将保留 x 值到小数点左边 y 位。

示例：

```sql
SELECT ROUND(-2.34), ROUND(-4.56), ROUND(2.34), ROUND(4.56);
```

![ROUND](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472189450092.png)

### TRUNCATE 截取数值
语法：`TRUNCATE(x, y)`

参数：x 是待截取数，y 是保留小数点后的位数或者截取小数点前的位数。

效果：返回被舍去至小数点后 y 位的数字 x。若 y 的值为0，则结果不带有小数点或不带有小数部分。若 y 设为负数，则截去（归零）x 小数点左边起第 y 位开始后面所有低位的值。

> TRUNCATE 是直接截取(归零)，而不是和 RUND 那样进行四舍五入。

示例：

![TRUNCATE](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472188948152.png)

### LEAST
语法：`LEAST(v1, v2 [, ...])`

参数：可以有两个或更多个参数，也可以指定列名。其值可以是数值、字符等。

效果：当参数中是整数或者浮点数时，返回其中最小的值；当参数为字符串时，返回字母中顺序最靠前的字符；当比较值列表中有 NULL 时，不能判断大小，返回值为 NULL。

示例：

![LEAST](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472105123364.png) 

### GREATEST
语法：`GREATEST(v1, v2 [, ...])`

参数：可以有两个或更多个参数，也可以指定列名。其值可以是数值、字符等。

效果：当参数中是整数或者浮点数时，返回其中最大的值；当参数为字符串时，返回字母中顺序最靠后的字符；当比较值列表中有 NULL 时，不能判断大小，返回值为 NULL。

示例：

![GREAST](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472105323655.png)


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

