使用 Laravel migration 将数据库的字符串字段转成整数字段的时候，会出现错误。

比如，migration 文件内容类似如下：

```php
Schema::table('files', function(Blueprint $table) {
    $table->integer('app_id')->change();
});
```

执行的时候，会有类似如下的错误：

```
SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'CHARACTER SET utf8 DEFAULT 0 NOT NULL COLLATE utf8_unicode_ci' at line 1 (SQL: ALTER TABLE files CHANGE app_id app_id INT CHARACTER SET utf8 DEFAULT 0 NOT NULL COLLATE utf8_unicode_ci)
```

查看错误中提示的 SQL 语句可以发现，SQL 语句中出现了`CHARACTER SET utf8`和`COLLATE utf8_unicode_ci`语句，用来设置字段的字符集和排序方式，但是整数字段是不需要设置这两个属性的，所以报错了。

这个错误是在`doctrine/dbal > v2.10.0`开始引入的，官方 issue 地址为：[https://github.com/doctrine/dbal/issues/3714](https://github.com/doctrine/dbal/issues/3714)。

有三种解决方式：

1. 将`doctrine/dbal`降级到`v2.9.3`。

2. 通过 Laravel migration 设置空的字符集和排序方式：

    ```php
    Schema::table('files', function(Blueprint $table) {
        $table->integer('app_id')->charset(null)->collation(null)->change();
    });
    ```

3. 直接使用 SQL 语句来修改：

```php
DB::statement('ALTER TABLE files MODIFY app_id INTEGER;');
```

参考：

1. [How to convert string column type to integer using Laravel migration?](https://stackoverflow.com/questions/58976719/how-to-convert-string-column-type-to-integer-using-laravel-migration)
2. [Laravel migration table field's type change](https://stackoverflow.com/questions/32940495/laravel-migration-table-fields-type-change)

