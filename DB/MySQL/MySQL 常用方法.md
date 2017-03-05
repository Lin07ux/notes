## 时间方法
### 获取当前日期和时间的
`CURDATE()`、`CURRENT_DATE()`、`CURRENT_TIMESTAMP()`、`LOCALTIME()`、`NOW()`、`SYSDATE()`。

![当前日期和时间](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472286403477.png)

上面的这些方法都是获取本地的日期和时间的。如果要获取 UTC 的日期和时间，需要分别使用下面的这两个方法：`UTC_DATE()`、`UTC_TIME()`。

![UTC的日期和时间](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472286494663.png)

### MONTHNAME 获取月份的名称
语法：`MONTHNAME(date)`

参数：`date`一个表示日期的字符串 

效果：返回日期 date 对应月份的英文全名。

示例：

```sql
SELECT MONTHNAME('2016-8-27');
```

![MONTHNAME](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472291085563.png)

### QUARTER 获取季度的名称
语法：`QUARTER(date)`

参数：`date`一个表示日期的字符串 

效果：返回日期 date 对应的季度。范围是 1 ~ 4。

示例：

```sql
SELECT QUARTER('2016-8-27'); # 8 月是第三季度，所有返回 3
```

![QUARTER](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472291179765.png)

### WEEKDAY 获取星期几
语法：`WEEKDAY(date)`

参数：`date`一个表示日期的字符串 

效果：返回日期 date 是一周中的第几天。范围是 0 ~ 6。其中，周一是 0，周日是 6。

示例：

```sql
SELECT WEEKDAY('2016-9-11 10:11:34'); # 2016-9-11 是周日，所以返回 6
```

![WEEKDAY](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1473560249766.png)

### MINUTE 获取分钟数
语法：`MINTUE(time)`

参数：`time`一个表示时间的字符串 

效果：返回时间 time 对应的分钟数。范围是 0 ~ 59。如果给出的 time 字符串中分钟数超过 59 则会返回 NULL。

示例：

```sql
SELECT MINUTE('11-02-03 10:10:06');
```

![MINUTE](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472291322899.png)

### SECOND 获取秒数
语法：`SECOND(time)`

参数：`time`一个表示时间的字符串 

效果：返回时间 time 对应的分钟数。范围是 0 ~ 59。如果给出的 time 字符串中秒数超过 59 则会返回 NULL。

示例：

```sql
SELECT SECOND('10:23:12'), SECOND('10:23:60');
```

![SECOND](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472291465446.png)

### EXTRACT 获取日期中指定的值
语法：`EXTRACT(TYPE FROM time)`

参数：`type`表示获取时间中的哪一部分。`time`一个表示时间的字符串 

效果：返回时间 time 表示的时间中的年月日或时分秒部分。

示例：

```sql
SELECT EXTRACT(YEAR FROM '2016-08-31 16:34:09');
SELECT EXTRACT(MONTH FROM '2016-08-31 16:34:09');
SELECT EXTRACT(HOUR FROM '2016-08-31 16:34:09');
```

![EXTRACT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472291841640.png)

### TIME_TO_SEC/SEC_TO_TIME 时间和秒的互换
语法：`TIME_TO_SEC(time)`、`SEC_TO_TIME(second)`

参数：`time`表示时间字符串，`second`表示秒数的数字。 

效果：前者返回 time 时间对应的一天内的秒数，最大为`86399`，转换公式为`小时*3600+分钟*60+秒`；后者将秒数转成对应的时间，最大为`838:59:59`。

示例：

```sql
SELECT TIME_TO_SEC('2016-08-28 23:22:00'), SEC_TO_TIME(1472300766);
```

![TIME_TO_SEC/SEC_TO_TIME](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472300948197.png)

### 计算日期和时间
* 增加日期：`DATE_ADD(date,interval  expr type)`，`ADDDATE(date,interval  expr type)`
* 减去日期：`DATE_SUB(date,interval  expr type)`，`SUBDATE(date,interval  expr type)`
* 增加时间：`ADD_TIME(date,expr)`
* 减去时间：`SUBTIME(date,expr)`
* 时间差：`DATEDIFF()`
* 日期和时间格式化：`DATE_FORMAT(date,format)`，`TIME_FORMAT(time,format)`
* 返回日期时间字符串的显示格式：`GET_FORMAT(val_type,format_type)`


## 字符串方法
### CONCAT 链接字符串
语法：`CONCAT(str1,str2,...)`

参数：这个方法的参数可以是一个字符串、数字，也可以是当前操作的表中的某一列的名。 

效果：返回连接参数产生的字符串。如有任何一个参数为NULL ，则返回值为 NULL。

示例：更新一个字段的值为它本身的值连接上一个字符串，可以使用`CONCAT()`方法。

```sql
UPDATE tbl_name SET column=CONCAT(column, str1, str2, ...) WHERE ...
```

### CONCAT_WS 使用分隔符连接字符串
语法：`CONCAT_WS(sep, str1, str2,...)`

参数：sep 是分隔符，后面的参数是要进行连接的参数。 

效果：CONCAT_WS 代表`CONCAT with Separator`，是 CONCAT() 函数的特殊形式。返回连接参数量量之间用分隔符连接产生的字符串。如果分隔符为 NULL，则结果为 NULL。函数会忽略任何分隔符参数后的 NULL 值。

示例：

```sql
SELECT CONCAT_WS('-','1st','2nd','3rd'), CONCAT_WS('-','1st',NULL,'3rd');
```

![CONCAT_WS](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472282084419.png)

### REPLACE 替换字符串
语法：`REPLACE(str, from_str, to_str)`

参数：`str`是要被替换的原字符串，`from_str`是要被替换的那部分子字符串，`to_str`是要替换
成的子字符串。这里的`str`可以是当前操作的表中的一个列的名称，表示操作对应的列的值。

效果：将字符串`str`中所有的`from_str`子串替换成`to_str`。

示例：将表中的 name 字段中开头的星号`*`去除掉。

```sql
UPDATE tbl_name SET name=REPLACE(name, '*', '') WHERE name like '*%';
```

### LEFT 左截取字符串
语法：`LEFT(str, length)`

参数：`str`是要被截取的原字符串，`length`是要截取的长度

效果：从 str 字符串起始处开始向后截取字符串，截取长度为 length。

示例：截取出 content 字段的前 200 个字符。

```sql
SELECT LEFT(content, 200) as abstract FROM ... WHERE ...
```

### RIGHT 右截取字符串
语法：`RIGHT(str, length)`

参数：`str`是要被截取的原字符串，`length`是要截取的长度

效果：从字符串 str 的末尾处开始向前截取字符串，截取长度为 length。

示例：截取出 content 字段的最后的 200 个字符。

```sql
SELECT RIGHT(content, 200) as abstract FROM ... WHERE ...
```

### SUBSTRING 指定位置截取字符串
语法：`SUBSTRING(str, pos [, length])`

参数：`str`是要被截取的原字符串，`pos`是开始截取的起始位置(字符串起始位置为 1)，`length`是要截取的长度。

效果：从字符串 str 的 pos 处开始向后截取字符串，截取长度为 length。如果没有指定 length 则截取到字符串末尾。如果 pos 是负数，则表示从字符串末尾处倒数得出截取的起始位置。

示例：截取出 content 字段第 5 个字符后的所有字符，和截取第 5 个字符后的 200 个字符。

```sql
SELECT SUBSTRING(content, 5) as abstract FROM ... WHERE ...
SELECT SUBSTRING(content, 5, 200) as abstract FROM ... WHERE ...
```

### SUBSTRING_INDEX 指定分隔符截取字符串
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

### LENGTH 字符串长度
语法：`LENGTH(str)`

参数：`str`是要被统计字数的字符串，也可以指定为一个列名。

效果：返回字符串 str 的长度。一般用来计算普通字符(ASCII)的长度，处理其他字符的时候，会有问题，比如，它会把一个中文字符的长度计算为 2 或 3。如果要计算中文等其他字符的长度的时候，可以使用`CHAR_LENGTH()`方法。

示例：统计字符串`cover.jpg`和`中文`的字数。

```sql
SELECT LENGTH('cover.jpg');  # 9
SELECT LENGTH('中文');        # 6
```

### CHAR_LENGTH 多字节字符串长度
语法：`CHAR_LENGTH(str)`

参数：`str`是要被统计字数的字符串，也可以指定为一个列名。

效果：返回字符串 str 的长度。这个函数能真实的反应字符的个数，比`LENGTH()`函数更安全。

示例：统计字符串`cover.jpg`和`中文`的字数。

```sql
SELECT CHAR_LENGTH('cover.jpg');  # 9
SELECT CHAR_LENGTH('中文');        # 2
```

### HEX/UNHEX 十六进制化和反十六进制化
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

### LPAD / RPAD 左填充和右填充
语法：`LPAD(str1, len, str2)`，`RPAD(str1, len, str2)`

参数：`str1`填充的字段；`len`填充的长度；`str2`是作为填充的字符串。

效果：LPAD 首先将字符串 str1 用 str2 在 str1 的开头处向左填充，直到填充后的长度为 len，然后返回填充后的字符串。同样的，RPAD 是从 str1 的结尾处向右填充到指定的长度后，返回填充后的字符串。需要注意的是：**对于这两个方法，如果 len 的长度小于字符串 str1 的原始长度，那么会将 str1 从结尾处开始往起始处截断，使其符合 len 的长度。**

示例：

```sql
SELECT LPAD('hello',4,'??'), LPAD('hello',10,'??');
SELECT LPAD('hello',4,'?!'), LPAD('hello',10,'?!');
```

![LPAD/RPAD](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472282693452.png)

注意其中的填充字符串的顺序。

### TRIM 删除前后端空格或指定字符
语法：`TRIM(str)`，`TRIM(str1 FROM str2)`

参数：`str`是要被处理的字符串，`str1`是要删除的字符，`str2`是要被处理的字符串。

效果：第一种方式调用会清理字符串 str 前后端的空白，字符中的字符不会被处理；第二种方式会删除字符串 str2 前后端的 str1 字符，str2 中间的 str1 字符不会被清理。

示例：

```sql
SELECT TRIM(' book ');
SELECT TRIM('xy' FROM 'xyxboxyokxxyxy');
```

![TRIM](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472283212063.png)

### REPEAT 重复字符串
语法：`REPEAT(str, count)`

参数：`str`是要被重复的字符串，`count`重复的次数。

效果：返回字符串 str 重复 count 次后的字符串。如果 count 小于 1，则返回一个空字符串。如果 str 或 count 为 NULL，返回 NULL。

示例：

```sql
SELECT REPEAT('MySQL', 3), REPEAT('MySQL', 0), REPEAT('MySQL', NULL);
```

![REPEAT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472284258724.png)

### STRCPM 比较两个字符串的大小
语法：`STRCMP(str1, str2)`

参数：两个参数是要进行对比的两个字符串。

效果：若所有的字符串均相同，则返回 0；若根据当前分类次序，第一个参数小于第二个，则返回 -1，其他情况返回 1。

示例：

```sql
SELECT STRCMP('txt','txt2'), STRCMP('txt2','txt'), STRCMP('txt','txt');
```

![STRCPM](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472284405104.png)

### LOCATE/POSITION/INSTR 查找子字符串的开始位置
语法：`LOCATE(sub, str)`、`POSITION(sub IN str)`、`INSTR(str, sub)`

参数：`sub`子字符串，`str`父字符串。

效果：返回子字符串 sub 在字符串 str 中的开始位置。(位置从 1 开始计数)

示例：

```sql
SELECT LOCATE('ball','football'), POSITION('ball' IN 'football'), INSTR('football','ball');
```

![LOCATE/POSITION/INSTR](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472284645441.png)

### LOWER/UPPER 转换字符串大小写
语法：`LOWER(str)`、`UPPER(str)`

参数：`str`表示要进行大小写转换的字符串。

效果：前者将字符串转成小写，后者将字符串转成大写。

示例：

```sql
SELECT LOWER(name), UPPER(title) FROM table;
```


### ELT 返回指定位置的字符串
语法：`ELT(N, str1, str2, str3,…,)`

参数：`N`指定返回的字符串的索引，后面的参数是一个字符串列表，分别代表对应索引位置的字符串。

效果：若 N=1，则返回值为 str1，若 N=2，则返回值为 str2，以此类推。若 N 小于 1 或大于参数的数目，则返回值为 NULL。

示例：

```sql
SELECT ELT(3,'1st','2nd','3rd'), ELT(3,'net','os');
```

![ELT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472284938722.png)

### FIELD 返回指定字符串位置
语法：`FIELD(str, str1, str2, str3, …)`

参数：`str`要查找的字符串，后面的参数是一个字符串列表。

效果：返回字符串 str 在列表 str1，str2，…… 中第一次出现的位置，在找不到 str 的情况下，返回值为 0。

示例：

```sql
SELECT FIELD('hi','hihi','hey','hi','bas') AS coll, FIELD('hi','hihi','lo','hilo','foo') AS col2;
```

![FIELD](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472285079749.png)

### FIND_IN_SET 返回子串位置
语法：`FIND_IN_SET(str, str_set)`

参数：`str`要查找的字符串，`str_set`是一个字符串集合，字符串之间使用逗号分隔。(字符串集合是用一个逗号分隔的，如果逗号前后有空格，那么空格也会算进入字符串中。)

效果：返回字符串 str 在字符串列表 str_set 中出现的位置。如果 str 不在 str_set 或 str_set 为空字符串，则返回值为 0。如果任意一个参数为 NULL，则返回值为 NULL。

示例：

```sql
SELECT FIND_IN_SET('hi','hihi,hey,hi,bas');
SELECT FIND_IN_SET('hi','hihi, hey, hi, bas');
```

![FIND_IN_SET](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472285407598.png)

### MAKE_SET 选取字符串
语法：`MAKE_SET(bin, str1, str2, str3 ...)`

参数：`bin`要获取的字符串列表中的字符串的位置，会被解析为二进制数；后面的参数组成一个字符串列表。

效果：先将第一个参数 bin 解析成二进制数，然后将二进制数中，值为 1 的位的位置所对应的字符串列表中的字符取出来。如果取出来有多个，那么会用逗号分隔。

示例：1 的二进制表示为`0001`，5 的二进制表示为`0101`，那么当 bin 为 1 的时候，会取出字符串列表中的第一个字符串；bin 为 5 的时候，会取出字符串列表中的第 1 个和第 3 个字符串。

```sql
SELECT MAKE_SET(1,'a','b','c') AS col1, MAKE_SET(5, 'hello','nice','world') AS col2;
```

![MAKE_SET](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472285918191.png)


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

### FORMAT 四舍五入格式化
语法：`FORMAT(x, n)`

参数：`x`要被格式化的数字，`n`保留的小数点的位数。

效果：将数字 x 格式化，并以四舍五入的方式保留小数点后 n 位，并且整数部分插入逗号千分位，结果以字符串的形式返回。当 n 小于等于 0 的时候，返回不含小数点的结果。

> 注意：一旦你的数据经过千分位分隔后，就会变成字符串。能够给阅读上提供比较好的体验，但是在计算上却造成很大的困扰，所以如果只是保留小数，不建议使用这个函数。

示例：

```sql
SELECT FORMAT(12332.123465, 4), FORMAT(12332.123465, -4);
```

![FORMAT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472346919855.png)

### CONV
语法：`CONV(n, from_base, to_base)`

参数：`n`要被转化的数字，`from_base`原来的进制，`to_base`转换为的进制。

效果：进行不同进制数间的转换。

示例：将十六进制数 a 转换成二进制数。

```sql
SELECT CONV('a', 16, 2);
```

![CONV](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472347084451.png)

### ABS 绝对值
语法：`ABS(n)`

参数：`n`要被处理的数字。

效果：返回 n 的绝对值。

示例：

```sql
SELECT ABS(12.5), ABS(-23.3);
```

![ABS](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472431517321.png)

### SQRT 平方根
语法：`SQRT(n)`

参数：`n`要被处理的数字。

效果：返回 n 的平方根。如果 n 是负数或 NULL 则返回 NULL。（sqrt 是 sqruar(平方，矩形) ，root（根）的缩写。）

示例：

```sql
SELECT SQRT(16), SQRT(-36), SQRT(8);
```

![SQRT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472431707627.png)

### POWER 幂运算
语法：`POWER(n, p)`

参数：`n`要被处理的数字。

效果：返回 n 的平方根。如果 n 是负数或 NULL 则返回 NULL。（sqrt 是 sqruar(平方，矩形) ，root（根）的缩写。）

示例：

```sql
SELECT POWER(3, 2), POWER(3, 3), POWER(-2, 4);
```

![POWER](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472432993912.png)

### RAND 随机数
语法：`RAND()`

参数：无。

效果：生成随机数。范围是 [0 ~ 1]。

示例：

```sql
SELECT RAND(), RAND(), RAND();
```

![RAND](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472432674572.png)

### CEIL/FLOOR 向上、向下取整
语法：`CEIL(n)`、`FLOOR(n)`

参数：`n`要被处理的数字。

效果：这两个函数是镜子函数，不进行四舍五入：CEIL 会将数值小数部分去掉，如果是正数则 +1 后返回，否则直接返回；FLOOR 会将数值小数部分去掉，如果是正数则直接返回，如果是负数则 -1 后返回。

示例：

```sql
SELECT CEIL(2.13), CEIL(2.53), CEIL(-2.13), CEIL(-2.53);
SELECT FLOOR(2.13), FLOOR(2.53), FLOOR(-2.13), FLOOR(-2.53);
```

![CEIL/FLOOR](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472432466547.png)

### SIGN 正负号
语法：`SIGN(n)`

参数：`n`要被处理的数字。

效果：返回数值 n 的正负性。如果是负数返回 -1，如果是 0 返回 0，如果是正数，返回 1。

示例：

```sql
SELECT CEIL(2.13), CEIL(2.53), CEIL(-2.13), CEIL(-2.53);
SELECT FLOOR(2.13), FLOOR(2.53), FLOOR(-2.13), FLOOR(-2.53);
```

![SIGN](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472432867533.png)


## 条件判断
### IF
语法：`IF(expr, v1, v2)`

参数：`expr`是一个表达式，作为条件。`v1`和`v2`是作为结果返回的值。

效果：如果表达式 expr 是 TRUE（expr<>0 and expr<>NULL），则 IF() 的返回值为 v1；否则返回值为 v2。IF() 的返回值为数字值或字符串值，具体情况视其所在语境而定。

示例：

```sql
SELECT IF(1>2, 2, 3);
```

![IF](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472301305841.png)

### IFNULL
语法：`IFNULL(v1, v2)`

参数：`v1`和`v2`是作为结果返回的值。

效果：假如 v1 不为 NULL，则返回值为 v1；否则其返回值为 v2。IFNULL() 的返回值是数字或是字符串，具体情况视语境而定。

示例：

```sql
SELECT IFNULL(1, 2), IFNULL(NULL, 10);
```

![IFNULL](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472301445855.png)

### CASE


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

### CONNECTION_ID
查看当前用户的连接 ID。每个连接都有各自唯一的 ID。

```sql
SELECT CONNECTION_ID();
```

### PROCESSLIST
输出有哪些线程在运行，不仅可以查看当前所有的连接数，还可以查看当前的连接状态，帮助识别出有问题的查询语句等。

如果是 root 帐号，能看到所有用户的当前连接。如果是其他普通帐号，则只能看到自己占用的连接。`show processlist`只能列出当前 100 条。如果想全部列出，可以使用`SHOW FULL PROCESSLIST`命令。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472301719830.png)

各个列的含义：

* `id`列，用户登录 mysql 时，系统分配的 “connection_id”
* `user`列，显示当前用户。如果不是 root，这个命令就只显示用户权限范围的 sql 语句
* `host`列，显示这个语句是从哪个 ip 的哪个端口上发的，可以用来跟踪出现问题语句的用户
* `db`列，显示这个进程目前连接的是哪个数据库
* `command`列，显示当前连接的执行的命令，一般取值为休眠（sleep），查询（query），连接（connect）
* `time`列，显示这个状态持续的时间，单位是秒
* `state`列，显示使用当前连接的 sql 语句的状态，很重要的列，后续会有所有状态的描述，state 只是语句执行中的某一个状态。

一个 sql 语句，以查询为例，可能需要经过以下状态等状态才可以完成：

* copying to tmp table，
* sorting result，
* sending data

### 获取用户名
`USER()`、`CURRENT_USER()`、`CURRENT_USER`、`SYSTEM_USER()`、`SESSION_USER()`这几个函数返回当前被 MYSQL 服务器验证的用户名和主机名组合。这个值符合确定当前登录用户存取权限的 MYSQL 帐户。一般情况下，这几个函数的返回值是相同的。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472301992037.png)

### CHARSET
`CHARSET(str)`返回字符串 str 自变量的字符集。

```sql
SELECT CHARSET('abc'), CHARSET(CONVERT('abc' USING latin1)), CHARSET(VERSION());
```

![CHARSET](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472302149302.png)

### COLLATION
返回字符串 str 的字符排列方式。

```sql
SELECT COLLATION(_latin2 'abc'), COLLATION(CONVERT('abc' USING utf8));
```

![COLLATION](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472303460697.png)

### LAST_INSERT_ID
自动返回最后一个 INSERT 或 UPDATE 为 AUTO_INCREMENT 列设置的第一个发生的值。

```sql
SELECT LAST_INSERT_ID();
```

* 一次插入一条记录时，返回该条记录的 ID；
* 一次插入多条记录(用一个 INSERT INTO 语句)时，返回插入的第一条记录的 ID。

> 之所以这样，是**因为这使依靠其他服务器复制同样的 INSERT 语句变得简单**。

LAST_INSERT_ID 是与 table 无关的，如果向表 a 插入数据后，再向表 b 插入数据，LAST_INSERT_ID 返回表 b 中的 ID 值。


## 加密函数
### PASSWORD
`PASSWORD(str)`从原文 str 计算并返回加密后的密码字符串，当参数为 NULL 时，返回 NULL。

PASSWOR() 函数在 MYSQL 服务器的鉴定系统中使用；不应将他用在个人应用程序中，该函数加密是单向的（不可逆）。PASSWORD 执行密码加密与 UNIX 中密码加密方式不同。

### MD5
`MD5(str)`为字符串算出一个 MD5 128 比特校验和。该值以 32 位十六进制数字的二进制字符串形式返回，若参数为 NULL，则会返回 NULL。

### ENCODE
`ENCODE(str,pswd_str)`使用 pswd_str 作为密码，加密 str。可以使用 DECODE() 解密结果，结果是一个和 str 长度相同的二进制字符串。

```sql
SELECT ENCODE('nihao','123');
```
![ENCODE](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472346560109.png)

### DECODE
`DECODE(crypt_str,pswd_str)`使用 pswd_str 作为密码，解密加密字符串 crypt_str，crypt_str 是由 ENCODE() 返回的字符串。

```sql
SELECT DECODE(ENCODE('nihao','123'),'123')
```

![DECODE](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472346729913.png)

ENCODE() 和 DECODE() 互为反函数。


## 其他方法
### IP地址与数字相互转换
* `SELEC`给出一个作为字符串的网络地址的点地址表示，返回一个代表该地址数值的整数。地址可以是 4 或 8 比特地址。
* `INET_NTOA(expr)`给定一个数字网络地址（4 或 8 比特），返回作为字符串的该地址的点地址表示。

### 加锁和解锁
* `GET_LOCK(str,timeout)`设法使用字符串 str 给定的名字得到一个锁，超时为 timeout 秒。
* `RELEASE_LOCK(str)`解开被 GET_LOCK() 获取的、用字符串 str 所命名的锁。
* `IS_FREE_LOCK(str)`检查名为 str 的锁是否可以使用
* `IS_USED_LOCK(str)`检查名为 str 的锁是否正在被使用

### 重复执行指定操作
`BENCHMARK(count,expr)`函数重复 count 次执行表达式 expr。他可以用于计算 MYSQL 处理表达式的速度。结果值通常为 0（0 只是表示处理过程很快，并不是没有花费时间）

另一个作用是他可以在MYSQL客户端内部报告语句执行的时间。

BENCHMARK 报告的时间是客户端经过的时间，而不是在服务器端的 CPU 时间，每次执行后报告的时间并不一定是相同的。

### CONVERT 改变字符集
`CONVERT(str USING charset)`带有 USING 的 CONVERT() 函数被用来在不同的字符集之间转化数据。

```sql
SELECT CHARSET('string'), CHARSET(CONVERT('string' USING latin1));
```

![CONVERT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472347465763.png)

### 改变数据类型
`CAST(x AS type)`和`CONVERT(x, type)`函数将一个类型的值转换为另一个类型的值，可转换的 type 有：`BINARY`、`CHAR(n)`、`DATE`、`TIME`、`DATETIME`、`DECIMAL`、`SIGNED`、`UNSIGNED`。

```sql
SELECT CAST(100 AS CHAR(2)), CONVERT('2013-8-9 12:12:12', TIME);
```

![CAST/CONVERT](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472347577888.png)

