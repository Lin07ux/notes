### 数据库返回的整型数据被偷换成了字符串类型

Laravel 从 MySQL 获取的整型数据被转换成了 String 类型，而这经常会造成奇怪的结果。但是这个现象并不是在所有机器上都能出现，只有在服务器上才会。

如，在测试机器上，返回如下：

```json
{
    "id": 1,
    "level": 1
}
```

但是在服务器上，返回如下的结果：

```json
{
    "id": "1",
    "level": "1"
}
```

可以看到，本来是整型数据，变成了字符串数据。

确认了表结构没有问题，说明问题出在了数据被取出来的过程中。于是 Google 了一下，发现问题出在了 PHP 的 MySQL 驱动上：

* [MySQL integer field is returned as string in PHP](http://stackoverflow.com/questions/5323146/mysql-integer-field-is-returned-as-string-in-php)
* [laravel eloquent integers returned as strings in mssql](http://stackoverflow.com/questions/26974914/laravel-eloquent-integers-returned-as-strings-in-mssql)

检查服务器端的 MySQL 驱动，发现装的是`php71w-mysql`，改成对应的`php71w-mysqlnd`驱动之后，重启 PHP-FPM 就 OK 了。

> 转摘：[数据库返回的整型数据被偷换成了字符串类型](https://www.sunzhongwei.com/php-mysqlnd)

