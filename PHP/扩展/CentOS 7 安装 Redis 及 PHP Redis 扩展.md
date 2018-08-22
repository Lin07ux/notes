CentOS 的 yum 仓库中提供了 Redis 安装包，但是版本一般比较低。如果需要使用较新版本的 Redis，则需要下载源码进行安装。

### 一、安装和配置文件

在[Redis 官网](https://redis.io/download)中找到相应版本的下载链接，下载源码，然后编译安装：

```shell
wget http://download.redis.io/releases/redis-4.0.11.tar.gz
tar zxf redis-4.0.11.tar.gz
cd redis-4.0.11
make
make install
```

成功执行上述命令之后，就会在编译安装文件夹中的`./src`目录中生成 redis-server、redis-cli 等相关的程序，可以直接运行。另外，在`/usr/local/bin/`路径中，也存在相同的程序。

在启动 Redis 服务之前，一般先设置一下配置文件。默认会在编译路径下生成一个`redis.conf`文件，这就是 Redis 的配置文件，打开该文件，修改相关配置：

```conf
# 修改daemonize为yes，即默认以后台程序方式运行。 daemonize no 
# 可修改默认监听端口 port 6379
 # 修改生成默认日志文件位置 logfile "/usr/local/redis/logs/redis.log" 
# 配置持久化文件存放位置 dir /usr/local/redis/data/redisData
```

设置好之后，就可以在编辑文件夹中使用如下的命令来启动：

```shell
./src/redis-server redis.conf
```

### 二、开机自启动

一般 Redis 需要跟随系统自动启动，这就需要进行一些配置。推荐在生产环境中使用启动脚本方式启动 redis 服务。启动脚本`redis_init_script`位于 Redis 编译目录的`./utils/`目录下，打开该文件，大致有如下的内容：

```sh
# redis服务器监听的端口 REDISPORT=6379  # 服务端所处位置，在 make install 后默认存放与`/usr/local/bin/redis-server`
# 如果未 make install 则需要修改该路径，下同。 EXEC=/usr/local/redis/bin/redis-server  # 客户端位置 CLIEXEC=/usr/local/redis/bin/redis-cli  # Redis的PID文件位置 PIDFILE=/var/run/redis_${REDISPORT}.pid
 # 配置文件位置，默认用监听的端口名作为配置文件等命名 CONF="/etc/redis/${REDISPORT}.conf"
```


1. 根据启动脚本要求，将修改好的配置文件以端口为名复制一份到制定目录，需要使用 root 的用户：

    ```shell
    mkdir /etc/redis
    co redis.conf /etc/redis/6379.conf
    ```

2. 将启动脚本复制到`/etc/init.d`目录下，本例将启动脚本命名为`redisd`(通常都以 d 结尾表示后台自启动服务)：

    ```shell
    cp utils/redis_init_script /etc/init.d/redisd
    ```

3. 设置为开机自启动：

    ```shell
    chkconfig redisd on
    ```
    
    此处不可使用`systemctl enable redisd`。
    
    如果有报错`service redisd does not support chkconfig`，那么可以在启动脚本的首行下面添加如下的注释，以修改其运行级别，然后再设置即可：
    
    
    ```shell
    #!/bin/sh
    #
    # chkconfig:   2345 90 10     # description:  Redis is a persistent key-value database     #
    ```

设置成功之后，就可以使用如下的命令来管理 redis 服务：

```shell
systemctl start redisd
systemctl stop redisd
systemctl restart redisd
```

### 三、安装 PHP 扩展

#### 3.1 安装 igbinary

igbinary 扩展能够提供比 PHP 提供更好的序列化和反序列化性能，而且 Redis 的 PHP 扩展也会需要使用到，所以需要先安装该扩展。

```shell
# 查看 PHP 相关的 igbinary 扩展
yum list | grep igbinary
# 安装
yum install php71w-pecl-igbinary
```

#### 3.2 安装 PHP Redis 扩展

PHP 的 Redis 扩展虽然在 yum 仓库中也有安装包，但是版本一般比较低，可以使用 pecl 工具来安装最新版本的 Redis：

```shell
pecl install redis
```

安装过程中，会询问是否使用 igbinary 压缩，输入 y 或者 yes 即可启用。

安装好之后，需要在 PHP 的配置文件中引入 redis 扩展。对于 PHP 7 来说，不能直接在`/etc/php.ini`文件中通过`extension=redis.so`来引入，这样会提示找不到`igbinary`的相关函数，而需要在`/etc/php.d/`路径中，新建一个`redis.ini`文件，然后在其中引入 redis 扩展并重启 php-fpm：

```ini
extension=redis.io
```

### 四、参考

1. [Redis 如何在系统启动时设置为开机自启](https://blog.csdn.net/wanggangabc111/article/details/78133170)
2. [phpredis/INSTALL.markdown](https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown)


