> [Alpine安装php各种扩展](https://blog.csdn.net/liyyzz33/article/details/97265262)

### 0. 基础

Alpine 中使用 apk 命令来安装一般的软件。使用前，建议设置阿里云的镜像，并安装基本的编译依赖：

```shell
sed -i "s/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g" /etc/apk/repositories
apk add --no-cache autoconf g++ libtool make curl-dev libxml2-dev linux-headers
```

而安装 php 扩展的时候，可以使用专有的`docker-php-ext-install`来安装。如果需要 PHP 源码支持的话，可以使用`docker-php-source`命令来支持（完成后应删除源码，避免增加镜像体积）：

```shell
RUN docker-php-source extract \ # 解出源码
    # do important things \
    && docker-php-source delete # 删除源码

```

### 1. 常规扩展

下面的这些扩展，可以直接使用`docker-php-ext-install`命令进行扩展，基本没有其他的编译依赖：

```shell
echo "---------- Install pdo_mysql ----------"
docker-php-ext-install -j 2 pdo_mysql

echo "---------- Install zip ----------"
docker-php-ext-install -j 2 zip

echo "---------- Install pcntl ----------"
docker-php-ext-install -j 2 pcntl

echo "---------- Install mysqli ----------"
docker-php-ext-install -j 2 mysqli

echo "---------- Install mbstring ----------"
docker-php-ext-install -j 2 mbstring

echo "---------- Install exif ----------"
docker-php-ext-install -j 2 exif

echo "---------- Install calendar ----------"
docker-php-ext-install -j 2 calendar

echo "---------- Install sockets ----------"
docker-php-ext-install -j 2 sockets

echo "---------- Install shmop ----------"
docker-php-ext-install -j 2 shmop

echo "---------- Install curl ----------"
docker-php-ext-install -j 2 curl

echo "---------- Install mysql ----------"
docker-php-ext-install -j 2 mysql

echo "---------- Install wddx ----------"
docker-php-ext-install -j 2 wddx
```

### 2. 需其他编译依赖的扩展

下面的这些扩展需要各自特定的编译依赖，需要先安装依赖库之后，再使用`docker-php-ext-install`命令进行安装：

```shell
echo "---------- Install mcrypt ----------"
apk add --no-cache libmcrypt-dev
docker-php-ext-install mcrypt

echo "---------- Install gettext ----------"
apk add --no-cache gettext-dev
docker-php-ext-install -j 2 gettext

echo "---------- Install bz2 ----------"
apk add --no-cache bzip2-dev
docker-php-ext-install -j 2 bz2

echo "---------- Install xsl ----------"
apk add --no-cache libxslt-dev
docker-php-ext-install -j 2 xsl

echo "---------- Install wddx ----------"
apk add --no-cache libxslt-dev
ocker-php-ext-install -j 2 wddx

echo "---------- Install readline ----------"
apk add --no-cache readline-dev libedit-dev
docker-php-ext-install -j 2 readline

echo "---------- Install gmp ----------"
apk add --no-cache gmp-dev
docker-php-ext-install -j 2 gmp

echo "---------- Install ldap ----------"
apk add --no-cache ldb-dev openldap-dev
docker-php-ext-install -j 2 ldap
```

### 3. 有安装选项的扩展

有些扩展可以在安装的时候提供一些选项，以便得到更好的作用，此时可以使用`docker-php-ext-configure`命令进行配置。

```shell
echo "---------- Install gd ----------"
apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev \
&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
&& docker-php-ext-install -j 2 gd
```

### 4. 需 PHP 源码支持的扩展

下面这些扩展，在安装的时候会提示一些错误。比如：

```
Cannot find config.m4. 
Make sure that you run '/opt/local/bin/phpize' in the top level source directory of the module
```

这时候需要考虑使用 PHP 源码进行支持：

```shell
echo "---------- Install openssl ----------"
docker-php-source extract \
&& cp /usr/src/php/ext/openssl/config0.m4 /usr/src/php/ext/openssl/config.m4 \
&& docker-php-ext-install openssl

echo "---------- Install mhash ----------"
mkdir mhash \
&& tar -xf mhash-0.9.9.9.tar.gz -C mhash --strip-components=1 \
&& ( cd mhash  && ./configure && make && make install )\
&& docker-php-source extract \
&& ( cd /usr/src/php && ./configure --with-mcrypt --with-mhash=/usr/local/include && make && make install )\
&& docker-php-source delete
```

### 5. 使用 PECL 或源码安装的扩展

下面这些扩展不支持`docker-php-ext-install`命令进行安装，但是可以使用`PECL`命令安装，只是安装好之后还需要使用`docker-php-ext-enable`命令来启用扩展。

```shell
echo "---------- Install redis ----------"
pecl install http://pecl.php.net/get/redis-5.3.4.tgz
&& docker-php-ext-enable redis

# 或者可以使用源码安装
echo "---------- Install redis ----------"
mkdir redis \
&& tar -xf redis-4.1.1.tgz -C redis --strip-components=1 \
&& ( cd redis && phpize && ./configure && make && make install ) \
&& docker-php-ext-enable redis
```

