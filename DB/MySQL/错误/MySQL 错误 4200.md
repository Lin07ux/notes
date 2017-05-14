## 错误

当给一个表的字段添加索引的时候，会发生错误，无法添加索引：

```
SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
```

## 原因

MySQL 中，索引的长度是有限制的：InnoDB 引擎中为 767 字节，MyISAM 引擎中为 1000 字节。

一般情况下，这个长度限制是没有问题的，但是如果添加索引的字段的编码格式为 UTF-16、UTF-8mb4，则很容易出现这个问题。

比如，字符编码格式为 UTF-16 时，一个字段为`varchar(255)`，添加索引的话，就需要 255 * 4 = 1020 字节了，这样就会出超出限制了。

## 解决

既然知道了原因，那么就可以知道有三种方式来处理这个问题了：

1. 减少字段的长度，或者减少索引的长度；
2. 修改数据库引擎为 MyISAM；
3. 修改数据库、数据表或者特定字段的字符编码，如改成 UTF-8(最多只需要三个字节)。

> 注意：如果数据库需要存储表情，则需要使用`utf8mb4`编码，此时每个字符最多就需要 4 个字节了，那么就有可能出现索引长度限制问题。

## 参考

[MySQL: Specified key was too long; max key length is 767 bytes](https://wildlyinaccurate.com/mysql-specified-key-was-too-long-max-key-length-is-767-bytes/)

