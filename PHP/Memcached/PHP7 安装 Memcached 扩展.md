Memcached 是一个高性能的内存缓存系统，能够提高站点系统的性能，但是 PHP7 中的 Memcached 扩展的安装并不能简单的通过一两个命令就安装成功。下面是安装中的过程和问题笔记。

> 安装的是 Memcached 扩展，PHP 还有一个 Memcache 扩展，这两者是不同，需要特别注意。

## 从软件源中安装

对于某些 PHP 版本或者 Linux 系统来说，可能会存在一些软件源包含了打包好的 Memcached 扩展，此时就可以直接通过系统命令来安装，如：

```shell
yum install memcached -y
```

> 不过这种安装的，并不一定能够使用，或者被 PHP 识别为扩展。

## 编译安装

编译安装，首先需要从 GitHub 仓库下载源码，然后进行编译。在安装过程中，可能会出现一些依赖问题，解决好依赖问题之后，才能顺利安装成功。

### 安装步骤

下面是安装步骤：

```shell
cd ~
git clone https://github.com/php-memcached-dev/php-memcached.git
cd php-memcached/
git checkout php7

phpize # 或者使用 /usr/local/php7/bin/phpize

./configure

make && make install

# 修改php.ini
vim /usr/local/php7/lib/php.ini
# 在 END 前加上
extension=memcached.so;
# 重启 php-fpm
service php-fpm restart
```

各步骤的作用如下：

1. 首先从 GitHub 中拉取源码，并切换到 php7 分支；
2. 使用`phpize`命令来生成 PECL 扩展的 configure 文件，如果该命令不可用，可以试试从已经安装的 PHP 路径中的命令；
3. 执行`configure`脚本来完成相关的配置；
4. 进行编译和安装;
5. 之后修改 PHP 配置文件，加入相关扩展，并重启 php-fpm。

#### 禁止 UDP

### 问题

编译安装中，需要如下的依赖：

* [libmemcached](http://libmemcached.org/libMemcached.html)
* [memcached](http://memcached.org/)
* [zlib](http://www.zlib.net/)
* [igbinary](https://github.com/igbinary/igbinary)

这些依赖如果没有安装好的话，前面的编译安装就不会提供，而且会有相关的提示信息。根据提示信息，依次解决即可。下面是常遇到的一些问题和解决方法：

#### 1、没有安装 Memcached

由于 PHP 中的 Memcached 扩展也是需要依赖 Memcached，所以需要先安装 Memcached 服务：

```shell
# 安装
yum install -y memcached
# 启动
/usr/bin/memcached -d -l 127.0.0.1 -p 11211 -m 150 -u root
```

* `-d` 守护进程。当从终端窗口退出的时候 memcached 还会继续运行。
* `-l` 指定 ip 地址，这里我们指定本地 ip。
* `-p` 指定端口号，端口号为 11211。
* `-m` 分配内存，这里我分配了 150M 内存。
* `-u` 使用哪个用户运行 memcached。

#### 2、没有安装 libmemcached

首先在 [libmemcached 官网](https://launchpad.net/libmemcached/+download) 中找到合适的版本(一般最新)的下载链接，将其下载下来之后，进行安装：

```shell
# 下载
wget https://launchpad.net/libmemcached/1.0/1.0.18/+download/libmemcached-1.0.18.tar.gz
# 解压
tar -zxvf libmemcached-1.0.8.tar.gz
# 编译(指定编译到 /usr/lib/libmemcached 目录下)
./configure --prefix=/usr/lib/libmemcached
# 安装
make && make install
```

#### 3、没有安装 zlib

Memcached 模块使用了函数 zlib 来支持数据压缩，因此安装此模块需要安装 Zlib 模块：

```shell
yum install -y zlib-devel
```

#### 4、PHP Warning: Module 'memcache' already loaded in Unknown on line 0

如果安装成功之后，经常有这个提示，可以删除 PHP 配置文件`php.ini`中的`extension=memcached.so;`，然后重启 php-fpm。

### 参考

1. [CenOS7环境安装PHP7扩展(持续更新)](https://hanxv.cn/archives/25.html#memcached)
2. [GitHub - php-memcached-dev/php-memcached](https://github.com/php-memcached-dev/php-memcached/tree/php7)
3. [CentOS下安装memcached](http://blog.csdn.net/sinat_21125451/article/details/50983343)
4. [PHP Warning: Module 'memcache' already loaded in Unknown on line 0](http://forums.nzedb.com/index.php?topic=643.0)

