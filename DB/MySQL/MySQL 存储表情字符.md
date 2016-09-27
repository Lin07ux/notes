## emoji 表情与 utf8mb4
在 MySQL 中直接存储表情的时候，会出现无法插入数据的错误。

这是由于一般情况下，MySQL 的字符集是 utf8，而对于 emoji 表情的 mysql 的 utf8 字符集是不支持，需要修改设置为 utf8mb4 才行。

> 摘引：[mysql utf8mb4与emoji表情](http://my.oschina.net/wingyiu/blog/153357)
> MYSQL 5.5 之前， UTF8 编码只支持1-3个字节，只支持BMP这部分的unicode编码区（[BMP是从哪到哪](http://en.wikipedia.org/wiki/Mapping_of_Unicode_characters)），基本就是0000～FFFF这一区。 从MYSQL5.5开始，可支持4个字节UTF编码utf8mb4，一个字符最多能有4字节，所以能支持更多的字符集。
> `utf8mb4 is a superset of utf8`，utf8mb4兼容utf8，且比utf8能表示更多的字符。在做移动应用时，会遇到用户会输入emoji表情，如果不做一定处理，就会导致插入数据库异常。


## 修改
### 服务器端
修改数据库配置文件`/etc/my.cnf`，添加下面的配置，然后重启服务器：

```conf
[mysqld]
character-set-server=utf8mb4 
collation_server=utf8mb4_unicode_ci
init-connect="SET NAMES utf8mb4"

[mysql]
default-character-set=utf8mb4
```

> 重启之后，登录 mysql，可以通过`show variables like 'character%';`查看编码是否已经修改成功。修改成功应该类似如下：
> ```
mysql> show variables like '%char%';
+--------------------------+----------------------------------+
| Variable_name            | Value                            |
+--------------------------+----------------------------------+
| character_set_client     | utf8mb4                          |
| character_set_connection | utf8mb4                          |
| character_set_database   | utf8mb4                          |
| character_set_filesystem | binary                           |
| character_set_results    | utf8mb4                          |
| character_set_server     | utf8mb4                          |
| character_set_system     | utf8                             |
| character_sets_dir       | /usr/local/mysql/share/charsets/ |
+--------------------------+----------------------------------+
8 rows in set (0.00 sec)
```

然后再修改相应的数据库表的编码为 utf8mb4：

`ALTER TABLE tbl_name CONVERT TO CHARACTER SET utf8mb4;`

> 转换数据表编码的语句格式如下：
`ALTER TABLE tbl_name CONVERT TO CHARACTER SET charset_name;`

### 客户端
使用不同的编程语言的时候，可能会对 utf8mb4 不支持，不能在连接字符串中指定的，此时可以在获取连接之后，执行`set names utf8mb4`来解决这个问题。

比如，对于 ThinkPHP 框架，在配置文件中设置数据库连接字符集为 utf8mb4 之后(`'DB_CHARSET'=>'utf8mb4'`)，会出现错误，提示无法使用这个字符集。

此时，由于我们已经在 mysql 的配置文件中设置了`init-connect="SET NAMES utf8mb4"`，所以直接将 ThinkPHP 配置文件中的数据库字符集设置为空(`'DB_CHARSET'=>''`)，则连接数据库之后，服务器会自动设置连接字符集为 utf8mb4，此时写入和读取 emoji 表情就能正常了。


## 扩展
MySQL 默认情况下，无法存储中文字符，其实这和 MySQL 默认无法存储表情字符是同样的原因：数据库字符编码问题。

默认情况下，MySQL 使用的字符集是 Latin，所以无法存储中文或者其他的一些字符。

如果要存储中文，可以设置 MySQL 的字符集为支持中文的字符集，比如 GBK 或者 UTF-8。一般情况下，我们设置为 UTF-8，能有更好的兼容性。

1. 修改 MySQL 配置文件`/etc/my.cnf`，添加下面的配置，然后重启服务器：

    ```conf
    [mysqld]
    character-set-server=utf8 
    
    [mysql]
    default-character-set=utf8
    ```

2. 重启 mysqld 服务，使刚才的修改生效

3. 如果数据库中已经建有数据表了，那么还需要将这些数据表的字符集做更改。如果数据表有很多，可以导出数据库和数据，然后重新建库。

    ```sql
    ALTER TABLE tbl_name CONVERT TO CHARACTER SET utf8
    ```


