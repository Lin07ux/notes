## 字符类型
MySQL 中字符类型有：`CHAR`、`VARCHAR`、`TINYTEXT`、`TEXT`、`MEDIUMTEXT`、`LONGTEXT`。

### CHAR
CHAR 类型是固定长度的，最长能存储 255 个字符。

固定长度意味着，如果你定义了一个`CHAR(10)`的字段，那么不论你存储的数据是否达到了 10 个字节，它都要占去 10 个字节的空间。

**CHAR 字段，存储数据时，会清除掉数据末尾的空格**：

```sql
# 创建表格
create table test (`tag` char(20));

# 插入数据
insert into test values('string1'),(' string2'),('string3 '),(' string4 ');

# 选择数据
select concat("'", tag, "'") from test;
+---------------------+
| concat("'",col,"'") |
+---------------------+
| 'string1'           |
| ' string2'          |
| 'string3'           |
| ' string4'          |
+---------------------+
4 rows in set (0.84 sec)
```


### VARCHAR
VARCHAR 是可变长度的，最长存储 65535 个字符(5.0 以上版本)。

可变长度意味着，VARCHAR 中存储的字符的占用的实际空间不一定就是定义时的数值。比如，一个字段可能的值是不固定长度的，我们只知道它不可能超过 10 个字符，把它定义为`VARCHAR(10)`是最合算的，*VARCHAR 类型的占用空间是它的值的实际长度 + 1*。

为什么要 +1 呢？这一个字节用于保存实际使用了多大的长度。从这个 +1 中也应该看到，如果一个字段，它的可能值最长是 10 个字符，而多数情况下也就是用到了 10 个字符时，用 VARCHAR 就不合算了：因为在多数情况下，实际占用空间是 11 个字节，比用 CHAR(10) 还多占用一个字节。

和 CHAR 不同，VARCHAR 不会删除数据前后的空格：

```sql
# 创建表格
create table tab2(varchar(10));

# 插入数据
insert into tab2 values('string1'),(' string2'),('string3 '),(' string4 '); 

# 查询数据
select concat("'", col, "'") from tab2;
+-----------------------+
| concat("'", col, "'") |
+-----------------------+
| 'string1'             |
| ' string2'            |
| 'string3 '            |
| ' string4 '           |
+-----------------------+
3 rowsin set (0.09 sec)
```


### TINYTEXT
最大长度为 255。占用空间也是实际长度+1

### TEXT
最大长度为 65535，占用空间是实际长度+2。


### MEDIUMTEXT
最大长度16777215，占用空间是实际长度+3。


### LONGTEXT
最大长度4294967295，占用空间是实际长度+4。


## BLOB 类型
BLOB 是一个二进制大对象，用来存储可变数量的数据。BLOB 类型分为 4 种：`TinyBlob`、`Blob`、`MediumBlob`、`LongBlob`。

这几个类型之间的唯一区别是在存储文件的最大大小上不同：

* TinyBlob      最大 255B
* Blob          最大 65K
* MediumBlob    最大 16M
* LongBlob      最大 4G

BLOB 类型和字符类型的区别：

* BLOB 列存储的是二进制字符串（字节字符串）；字符类型列存储的是非二进制字符串（字符字符串）。
* BLOB 列没有字符集，并且排序和比较基于列值字节的数值；字符类型列有一个字符集，并且根据字符集对值进行排序和比较
* BLOB 是二进制字符串，字符类型是非二进制字符串，两者均可存放大容量的信息。BLOB 主要存储图片、音频信息等，而字符类型只能存储文本。

