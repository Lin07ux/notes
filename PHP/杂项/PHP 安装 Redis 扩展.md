PECL 是一个 PHP 扩展，提供一个目录的所有已知的扩展和托管设备下载 PHP 扩展，PHP 很多扩展都可以在这里面找到。

> 很多 PHP 扩展虽然也能使用操作系统的安装工具进行安装，但是其版本一般较低，尽量使用 PECL 进行安装。

## 一、单版本 PHP 安装 Redis 扩展

### 1.1 检查 igbinary 扩展

igbinary 扩展能够提供比 PHP 提供更好的序列化和反序列化性能，而且 Redis 的 PHP 扩展也会需要使用到，所以需要先安装该扩展。

```shell
# 查看是否安装了 igbinary
php -i | grep igbinary

# 使用 pecl 安装
pecl install igbinary
```

### 2.2 PECL 安装

```shell
pecl install redis
```

安装过程中，会询问是否使用 igbinary 压缩，输入 y 或者 yes 即可启用。

安装好之后，会自动将生成的 so 文件放在 PHP 的扩展目录中。

### 2.3 编译安装

编译安装也很简单，下载 PHP Redis 的源码之后，按照步骤编译安装即可：

```shell
# 下载
wget http://pecl.php.net/get/redis -o redis.tgz
# 解压
tar zxf redis.tgz 
cd redis

# 预处理
phpize

# 配置：选择合适的选项，一般 igbinary、lzf 都可以开启
./configure [--enable-redis-igbinary] [--enable-redis-msgpack] [--enable-redis-lzf [--with-liblzf[=DIR]]]

# 编译安装
make && make install
```

### 2.4 引入扩展

安装完成之后，需要在 PHP 的配置文件中引入 redis 扩展。

对于使用系统工具安装的 PHP 来说，不能直接在`/etc/php.ini`文件中通过`extension=redis.so`来引入，这样可能会提示找不到`igbinary`的相关函数，而需要在`/etc/php.d/`路径中，新建一个`redis.ini`文件，然后在其中引入 redis 扩展并重启 php-fpm：

```ini
extension=redis.so
```

对于编译安装的 PHP 来说，可以直接在其配置文件(如`/usr/local/php72/etc/php.ini`)中扩展相关的配置列表的最后添加上如下配置即可：

```ini
extension=redis.so
```

配置好之后，可以通过如下命令来检测是否正常加载了 Redis 扩展，如果下面的命令的输出中有结果则说明是正常的：

```shell
php -i | grep redis
```

对于 PHP-FPM 则需要重启之后才能正常启用 Redis 扩展：

```shell
sudo systemctl restart php-fpm.service
```

## 二、多版本 PHP 安装 Redis 扩展

在服务器上安装了多个版本的 PHP 的时候，如果需要分别为这些版本的 PHP 安装 Redis 扩展，则主要的是要引用好 phpize 和 php-config 工具，否则会因为版本不对而造成安装的版本不对。

> 如果其他版本的 PHP 已经安装过 Redis 扩展，则需要先将编译生成的`redis.so`文件进行备份，都是可能会被后续的安装给覆盖或删除。

### 2.1 PECL 安装

使用 PECL 安装时，需要先设定默认的 php、phpize、php-config 的路径。

比如，当前是 PHP 默认是 7.1 版本，如果要为 PHP 7.2 版本安装 Redis，则需要先做如下更改：

```shell
# 更改扩展目录
sudo pecl config-set ext_dir /usr/lib/php/20170718
# 更改 phpize 和 php-config 的路径
sudo pecl config-set php_dir /usr/local/php72
# 指定 php 路径
sudo pecl config-set php_bin /usr/bin/php72
```

然后使用 PECL 进行安装：

```shell
sudo pecl install redis
```

如果提示 redis 扩展已安装，则可以添加`-f`选项，强制安装，如下：

```shell
sudo pecl install -f redis
```

### 2.2 编译安装

编译安装也一样需要先得到 Redis 扩展的源码，然后指定相应版本的 phpize 和 php-config 的路径即可：

```shell
# 预处理
/usr/local/php72/bin/phpize

# 配置
./configure --prefix=/usr/local/php72 --with-php-config=/usr/local/php72/bin/php-config

# 编译安装
make && make install
```

### 2.3 引入扩展

同样的，安装完成之后，还需要将 redis 扩展在 PHP 的配置中引入。引入方式和前面单版本中的引入方式相同。

在 Homestead 环境中需要注意的是，除了要为 cli 配置引入 redis 扩展，还要为 fpm 配置引入，否则可能导致网站代码无法正常使用 Redis 扩展。

## 三、问题

### 3.1 Unable to load dynamic library redis.so

在启动 PHP FPM 的时候，出现如下错误：

```
PHP Warning: PHP Startup: Unable to load dynamic library '/usr/lib64/php/modules/redis.so' - /usr/lib64/php/modules/redis.so: undefined symbol: php_json_decode_ex in Unknown on line 0
```

这是因为 json 扩展加载的顺序产生了冲突导致的。

解决方法如下：

不要在`php.in`中引入`redis.so`扩展，而是在`php.d`文件夹中创建新文件`redis.ini`，然后在该文件中加入`extension=redis.so`，然后重启 PHP FPM 后，即可看到加载了 redis 扩展了。

## 四、参考

1. [phpredis/INSTALL.markdown](https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown)
2. [Linux下PHP安装Redis扩展（二）](https://segmentfault.com/a/1190000008420258)
3. [pecl 更换对应php版本](https://www.jianshu.com/p/fee58d93e8b1)
4. [linux上安装redis扩展 ，报错了：Unable to load dynamic library redis.so](https://segmentfault.com/q/1010000019735774)


