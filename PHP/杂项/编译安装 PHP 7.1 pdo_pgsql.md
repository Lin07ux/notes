### 1. 下载源码

如果在编译安装 PHP 7.1 时没有开启 pdo_pgsql 扩展，那么需要先下载 pdo_pdo 扩展包源码：[pecl.php.net](http://pecl.php.net/package/PDO_PGSQL)。

```shell
wget http://pecl.php.net/get/PDO_PGSQL-1.0.2.tgz
tar -zxvf PDO_PGSQL-1.0.2.tgz
cd PDO_PGSQL-1.0.2
```

如果编译安装的 PHP 源码还存在，则不需要单独下载，直接使用 PHP 源码中的代码：

```shell
cd php-7.1.25/ext/pdo_pgsql/
```

### 2. 安装

进入源码目录之后，需要先对其进行相关处理，没问题即可安装：

```shell
/usr/local/php71/bin/phpize

./configure -with-php-config=/usr/local/php71/bin/php-config -with-pdo-pgsql=/usr/pgsql-9.6/

make && make install
```

这里有两个地方需要注意：

* 这里`-with-pdo-pgsql`的值为安装 PostgreSQL 的位置；
* 如果有多个版本的 PHP，`phpize`和`php-config`都需要使用相应 PHP 版本的程序。

### 3. 修改配置

编译安装完成之后，还需要在`php.ini`文件中引入该扩展，否则不会自动加载：

```ini
extension=pdo_pgsql.so
```

### 4. 问题

使用扩展包的源码安装时，可能会提示如下错误：

```
configure: error: Cannot find libpq-fe.h. Please specify correct PostgreSQL installation path
```

这是由于没有指定正确的 PostgreSQL 的位置导致的，需要确保 PostgreSQL 已经正确安装，并指定正确位置。可以通过查找`libpq-fe.h`文件的位置来确定：

```shell
find / -iname libpq-fe.h
```

如果位置正确，依旧出现这个错误，则需要考虑安装和 PostgreSQL 版本相对应的如下依赖：

```shell
yum install postgresql96-devel
```

如果依旧不能正常安装，建议使用 PHP 的源码中的扩展来安装，注意 PHP 源码需要和编译安装的 PHP 版本一致。

