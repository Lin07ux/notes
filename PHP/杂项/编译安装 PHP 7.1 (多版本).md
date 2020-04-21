当需要在一个服务器上安装多个不同版本的 PHP 的时候，就需要手动进行编译安装了，这样才可以在安装的时候指定安装路径，避免冲突。

### 1. 下载源码

首先需要从[PHP 官网](http://php.net/downloads.php)中找到需要安装的 PHP 版本的源码，获取到相应的下载链接之后，就可以下载源码了：

```shell
cd ~

# 下载
wget http://cn2.php.net/distributions/php-7.1.25.tar.gz

# 解压
tar -zxvf php-7.0.2.tar.gz
```

### 2. 编译安装

解压得到源码之后，就可以用源码进行配置安装了。在安装的时候，需要特别注意的是：安装地址(使用`--prefix`指定)需要和已经安装的其他版本的 PHP 有所区别。

> 这些配置并不一定都要安装，可以根据自己的需要有选择的使用。而且不同版本的 PHP 编译时配置参数会有所更改，需要根据实际情况进行确认。
> 
> 编译过程中，如果遇到错误请参考后面的**编译错误**部分。

```shell
cd ~/php-7.1.25

# 编译
./configure --prefix=/usr/local/php71 --with-config-file-path=/usr/local/php71/etc --with-mhash --with-openssl --with-mysqli=mysqlnd --with-pdo-mysql=mysqlnd --enable-pdo --with-iconv --with-zlib --enable-inline-optimization --enable-intl --disable-rpath --enable-shared --enable-xml --enable-bcmath --enable-shmop --enable-sysvsem --enable-sysvshm --enable-mbregex --enable-mbstring  --enable-pcntl --enable-sockets --with-xmlrpc --enable-phar  --enable-posix --enable-soap --disable-ftp --without-pear --with-gettext --enable-session --with-curl --enable-opcache --enable-fpm --with-fpm-user=nginx --with-fpm-group=nginx --without-gdbm --enable-fast-install --with-bz2 --with-zip --enable-gd --with-jpeg --with-webp

# 安装
make clean && make && make install
```

### 3. PHP 配置

安装完成之后，还需要初始化生成相应的配置文件：

```shell
cd ~/php-7.1.25

# PHP 配置文件(使用开发配置或发布配置)
cp php.ini-development /usr/local/php71/etc/php.ini
cp php.ini-production /usr/local/php71/etc/php.ini

# 修改扩展路径配置
vim /usr/local/php71/etc/php.ini
# 找到 extension_dir 修改扩展目录位置如下:
extension_dir = "../lib/php/extensions/no-debug-non-zts-20160303/"
# 找到 timezone 修改时区如下:
date.timezone = PRC
```

### 4. PHP_FPM

完成 PHP 安装之后，PHP-FPM 也已经安装好了，但是还需要添加 php-fpm 管理相关的配置文件到系统配置目录`/etc/init.d`，并初始化 php-fpm 相关的配置：

```shell
cd ~/php-7.1.25

# 将 php 源码编译目录下的`sapi/fpm/init.d.php-fpm`文件拷贝到系统配置`/etc/init.d/`目录下
# 注意需要对其进行重命名，避免和其他版本的 php-fpm 有冲突
cp sapi/fpm/init.d.php-fpm /etc/init.d/php71-fpm

# 设置 php71-fpm 服务可执行权限
chmod +x /etc/init.d/php71-fpm

# 初始化 php-fpm 配置文件
cp /usr/local/php71/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf

# 初始化 www.conf 配置文件
cp /usr/local/php71/etc/php-fpm.d/www.conf.default /usr/local/php/etc/php-fpm.d/www.conf

# 修改 www.conf 里面的用户、属组、监听地址
user = nginx group = nginx
listen = 127.0.0.1:9001
# 或
listen = /var/run/php-fpm/php71-fpm.sock
```

需要注意，如果安装了多个版本的 PHP，那么需要将`www.conf`里的`listen`监听的端口或 sock 文件设置为不同的，否则会造成冲突。

### 5. 启动 php-fpm 服务

完成上述配置之后，就可以启动 php-fpm 服务，并设置为自动启动：

```shell
systemctl is-enabled php71-fpm.service # 查询服务是否开机启动 systemctl enable php71-fpm.service # 开机运行服务 systemctl disable php71-fpm.service # 取消开机运行 systemctl start php71-fpm.service # 启动服务 systemctl stop php71-fpm.service # 停止服务 systemctl restart php71-fpm.service # 重启服务 systemctl status php71-fpm.service # 查询服务运行状态 systemctl —failed # 显示启动失败的服务
```

### 6. 添加到系统路径

安装完成之后，默认安装后的可执行程序不在系统路径中，无法直接执行，可以将编译后的`bin`路径添加到系统路径中：

```shell
vim /etc/profile
# 在文件末尾添加如下语句
export PATH=$PATH:/usr/local/php71/bin
```

如果已经有其他版本的 PHP，那么这样可能无法正常执行新安装的 PHP，可以考虑将其做软连接到系统路径中：

```shell
ln -s /usr/local/php71/bin/php /usr/bin/php71
ln -s /usr/local/php71/bin/php-cgi /usr/bin/php71-cgi
ln -s /usr/local/php71/bin/php-config /usr/bin/php71-config
ln -s /usr/local/php71/bin/phpize /usr/bin/phpize71
```

### 7. 编译错误

如果编译过程有报错，提示缺失依赖，则需要安装相应的依赖。

#### 7.1 常见错误

常见错误对应要安装的依赖如下：

```
报错 Cannot find OpenSSL's <evp.h>
执行 yum -y install openssl openssl-devel

报错 configure: error: cURL version 7.15.5 or later is required to compile php with cURL support
报错 Please reinstall the libcurl distribution
执行 yum -y install curl-devel

报错 configure: error: libxml2 not found. Please check your libxml2 installation.
执行 yum install -y libxml2-devel

错误 configure: error: jpeglib.h not found.
执行 yum -y install libjpeg-devel

报错 configure: error: png.h not found.
执行 yum install -y libpng-devel

报错 configure: error: freetype-config not found.
执行 yum install -y freetype-devel

报错 configure: error: Please reinstall the libzip distribution
执行 yum install -y libzip-devel

错误 checking for BZip2 in default path... not found configure: error: Please reinstall the BZip2 distribution
执行 yum -y install bzip2-devel

错误 configure: error: xpm.h not found.
执行 yum -y install libXpm-devel

错误 Unable to locate gmp.h
执行 yum -y install gmp-devel

错误 Unable to detect ICU prefix or /usr//bin/icu-config failed. Please verify ICU install prefix and make sure icu-config works
执行 yum install -y icu libicu libicu-devel

错误 mcrypt.h not found. Please reinstall libmcrypt.
执行 yum -y install php-mcrypt libmcrypt libmcrypt-devel

错误 configure: error: Cannot find libpq-fe.h. Please specify correct PostgreSQL installation path
执行 yum -y install postgresql-devel

错误 configure: error: xslt-config not found. Please reinstall the libxslt >= 1.1.0 distribution
执行 yum -y install libxslt-devel

# php 7.4 处理正则表达式的依赖
错误 configure: error: Package requirements (oniguruma) were not met: No package 'oniguruma' found
执行 yum install -y oniguruma-devel
```

#### 7.2 libzip 版本过低

当 libzip 的版本过低时，可能会有类似如下的错误：

```
checking for libzip... configure: error: system libzip must be upgraded to version >= 0.11
```

此时需要将原先安装的低版本的 libzip 卸载掉，然后再下载新版本的 libzip 源码进行编译安装：

```shell
yum remove -y libzip

# 由于 libzip 需要使用 cmake 3.0 以上的版本来编译，先确认已安装 cmake3
yum install -y cmake3

# 下载最新版本的 libzip
wget https://libzip.org/download/libzip-1.6.1.tar.gz
tar -zxvf libzip-1.6.1.tar.gz
cd libzip-1.6.1
mkdir build && cd build

# 编译和安装
cmake3 ..
make && make test && makte install
```

已经安装过 libzip 后，重新配置 PHP 时，如果依旧提示 libzip 不存在，可以通过如下两种方式来解决：

```shell
# 这里配置的路径为 libzip 的 libzip.pc 文件路径，可以查看 libzip 的安装信息来确认实际安装位置

# 直接设置系统的 PKG_CONFIG_PATH
export PKG_CONFIG_PATH="/usr/local/lib64/pkgconfig"

# 在 PHP 配置时设置 PKG_CONFIG_PATH
./configure .. PKG_CONFIG_PATH=/usr/local/lib64/pkgconfig
```

在后续的 PHP make 过程中，可能会出现找不到`libzip.so.5`的错误，建议先参考下后面的`7.3 off_t 未定义`部分，确认 ldconfig 中已有 libzip.so 的配置。

> [libzip - github](https://github.com/nih-at/libzip/blob/master/INSTALL.md)
> [CentOS7.3编译安装PHP7.4.1](https://wujie.me/centos7-3-compile-and-install-php-7-4-1/)

#### 7.3 off_t 未定义

报错如下：

```
configure: error: off_t undefined; check your library configuration
```

`off_t`类型是在头文件`unistd.h`中定义的，在 32 位系统编译成`long int`，64 位系统则编译成`long long int`。在进行编译的时候是默认查找 64 位的动态链接库，但是默认情况下 CentOS 的动态链接库配置文件`/etc/ld.so.conf`里并没有加入搜索路径，这个时候需要将`/usr/local/lib64`、`/usr/lib64`这些针对 64 位的库文件路径加进去。

```shell
#添加搜索路径到配置文件
echo '/usr/local/lib64
/usr/local/lib
/usr/lib
/usr/lib64'>>/etc/ld.so.conf

#然后 更新配置
ldconfig -v
```

