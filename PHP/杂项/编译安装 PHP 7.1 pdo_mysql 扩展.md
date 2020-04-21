> 参考：[php7 编译安装pdo_mysql扩展](https://my.oschina.net/u/2399303/blog/1512320)

### 1. 下载源码

如果在编译安装 PHP 7.1 时没有开启 pdo mysql 扩展，那么需要先下载 pdo_mysql 扩展包源码：[pecl.php.net](http://pecl.php.net/package/PDO_MYSQL)。

```shell
wget http://pecl.php.net/get/PDO_MYSQL-1.0.2.tgz
tar -zxvf PDO_MYSQL-1.0.2.tgz
cd PDO_MYSQL-1.0.2
```

如果编译安装的 PHP 源码还存在，则不需要单独下载，直接使用 PHP 源码中的代码：

```shell
cd php-7.1.25/ext/pdo_mysql/
```

### 2. 安装

进入源码目录之后，需要先对其进行相关处理，没问题即可安装：

```shell
/usr/local/php71/bin/phpize

./configure --with-php-config=/usr/local/php71/bin/php-config --with-pdo-mysql=mysqlnd

make && make install
```

这里有两个地方需要注意：

* 这里使用`mysqlnd`而不是`/usr/local/mysql`，因为 PHP7 正式移除了 mysql 扩展；
* 如果有多个版本的 PHP，`phpize`和`php-config`都需要使用相应 PHP 版本的程序。

### 3. 修改配置

编译安装完成之后，还需要在`php.ini`文件中引入该扩展，否则不会自动加载：

```ini
extension=pdo_mysql.so
```

### 4. 问题

使用 pdo_mysql 扩展包的源码安装时，可能会提示`can not find mysql under the "mysqlnd" that you specified`，即便已经安装了`mysqlnd`，依旧不行。这需要安装 MySQL 的开发包：

```shell
yum install libmysqlclient-dev
```

如果依旧不能正常安装，建议使用 PHP 的源码中的扩展来安装，注意 PHP 源码需要和编译安装的 PHP 版本一致。


