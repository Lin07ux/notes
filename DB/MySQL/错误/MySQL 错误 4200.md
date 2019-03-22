4200 错误一般都是长度过长问题引起的错误，一般会出现索引长度过长和字段长度过长问题。

## 一、索引错误

> 参考：[MySQL: Specified key was too long; max key length is 767 bytes](https://wildlyinaccurate.com/mysql-specified-key-was-too-long-max-key-length-is-767-bytes/)

### 1.1 错误信息

当给一个表的字段添加索引的时候，会发生错误，无法添加索引：

```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
```

### 1.2 原因

MySQL 中，索引的长度是有限制的：InnoDB 引擎中为 767 字节，MyISAM 引擎中为 1000 字节。

一般情况下，这个长度限制是没有问题的，但是如果添加索引的字段的编码格式为 UTF-16、UTF-8mb4，则很容易出现这个问题。

比如，字符编码格式为 UTF-16 时，一个字段为`varchar(255)`，添加索引的话，就需要 255 * 4 = 1020 字节了，这样就会出超出限制了。

### 1.3 解决

既然知道了原因，那么就可以知道有三种方式来处理这个问题了：

1. 减少字段的长度，或者减少索引的长度；
2. 修改数据库引擎为 MyISAM；
3. 修改数据库、数据表或者特定字段的字符编码，如改成 UTF-8(最多只需要三个字节)。

> 注意：如果数据库需要存储表情，则需要使用`utf8mb4`编码，此时每个字符最多就需要 4 个字节了，那么就有可能出现索引长度限制问题。

## 二、字段错误

> 转摘：[遇到的问题----Column length too big for column](https://blog.csdn.net/zzq900503/article/details/14520301)

### 2.1 错误信息

或者给一个字符字段设置较长的长度时，虽然长度依旧在文档所说的最大长度之内，但仍旧可能出现错误。比如，设置一个`varchar`字段的长度为 65535，此时就可能出现如下错误：

```
SQLSTATE[42000]: Syntax error or access violation: 1074 Column length too big for column 'content' (max = 16383); use BLOB or TEXT instead
```

### 2.2 原因

对于`varchar`类型的字段，其存储规则如下：

* 4.0 版本以下，`varchar(20)` 指的是 20 **字节**，如果存放 UTF8 汉字时，只能存 6 个（每个汉字 3 字节） 
* 5.0 版本以上，`varchar(20)` 指的是 20 **字符**，无论存放的是数字、字母还是 UTF8 汉字（每个汉字 3 字节），都可以存放 20 个，最大大小是 65532 **字节**。

所以，`varchar`字段具有如下的限制：

* 存储限制：`varchar`字段是将实际内容单独存储在聚簇索引之外，内容开头用 1 到 2 个字节表示实际长度（长度超过 255 时需要 2 个字节），因此最大长度不能超过 65535。

* 编码长度限制：`varchar`类型的字段，根据使用的字符集的不同，最大所能存储的字符数也是不同的。字符类型若为 gbk，每个字符最多占 2 个字节，最大长度不能超过 32766；字符类型若为 utf8，每个字符最多占 3 个字节，最大长度不能超过 21845。

* 表长度限制：MySQL 的一个表定义的时候，所有字段的总长度不超过 65535。如果使用的是 utf-8 字符集，那么总的字符长度可以有`65535 / 3 = 21845`个字符。但是每个字段都要用一个控制字符或者说是身份字符，所以创建一个字段的时候，它的最大长度是`21845 - 1 = 21844`。

对于以上限制，可以通过如下几个示例来理解：

```sql
CREATE TABLE `t` (                   
    `var` varchar(21845) default NULL   
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 会有类似如下提示：
-- Row size too large. The maximum row size for the used table type, not counting BLOBs, is 65535. This includes storage overhead, check the manual. You have to change some columns to TEXT or BLOBs
```

如果将上例中的字段长度改成 21844，就可以成功创建表。

如果一个表中有两个字段，那么这两个字段的总长度应该不能超过`21845 -2 = 21843`个字符，如下：

```sql
CREATE TABLE `t` (
    `var` varchar(10922) default NULL,
    `var2` varchar(10922)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 会报错，因为两个字段的总长度为 21844 个字符

CREATE TABLE `t` (
    `var` varchar(10922) default NULL,
    `var2` varchar(10921)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 此时可以正常创建
```

### 2.3 解决

明白了 MySQL 字段长度的限制之后，就好解决这个问题了：

将报错字段的长度适当修改，或者将其改成 TEXT、BLOB 类别。

