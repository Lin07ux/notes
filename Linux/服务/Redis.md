Redis 是一个内存缓存数据库。在 CentOS 和其他 Linux 系统中，可以通过系统自带方式安装，不过版本可能比较旧，如果要安装新版本，则需要通过源码编译方式安装。下面介绍在 CentOS 7 中使用源码编译安装 Redis。

### 下载源码

直接下载最新稳定版源码，并解压：

```shell
wget http://download.redis.io/redis-stable.tar.gz
tar -zxf redis-stable.tar.gz
```

### 2. 编译

进入解压后的文件夹，进行编译：

```shell
cd redis-stable

# 如果没有安装 gcc 等编译工具，则需要安装，否则直接跳过即可
yum -y install gcc gcc-c++ kernel-devel

# 编译
make
```

### 3. 安装

编译完成之后，就可以进行安装了。

> 在编译完成之后，会提示执行`make test`，可以执行，也可以不执行。

```shell
# 如果不指定 PREFIX 则默认安装在 /usr/local/ 目录中
make PREFIX=/usr/local/redis install

# 拷贝配置文件
mkdir /usr/local/redis/etc/
cp redis.conf /usr/local/redis/etc/

# 将可执行文件复制到 redis/bin/ 目录，也可以不复制。
cd /usr/local/redis/bin/
cp redis-benchmark redis-cli redis-server /usr/bin/
```

> 安装和安装之后，有调整路径的行为，如果不按照这个执行，则后面的设置开机自启动的地方需要进行适当的路径修改。

### 4. 更改配置

默认配置文件中，有些不符合我们的需求，可以适当修改：

```shell
vim /usr/local/redis/etc/redis.conf

# 修改一下配置
# redis以守护进程的方式运行
# no表示不以守护进程的方式运行(会占用一个终端)  
daemonize yes

# 客户端闲置多长时间后断开连接，默认为0关闭此功能                                      
timeout 300

# 设置redis日志级别，默认级别：notice                    
loglevel verbose

# 设置日志文件的输出方式,如果以守护进程的方式运行redis 默认:"" 
# 并且日志输出设置为stdout,那么日志信息就输出到/dev/null里面去了 
logfile stdout
# 设置密码授权，也可以不设置
requirepass <设置密码>
# 监听ip
bind 127.0.0.1 
```

### 5. 配置环境变量

> 如果安装的时候，默认安装在`/usr/local/`目录，则不需要执行此步。

```shell
vim /etc/profile
export PATH="$PATH:/usr/local/redis/bin"
# 保存退出

# 让环境变量立即生效
source /etc/profile
```

### 6. 配置启动脚本

新建脚本`/usr/local/redis/etc/redis`，输入如下内容：

```shell
#!/bin/sh
#
# Simple Redis init.d script conceived to work on Linux systems
# as it does use of the /proc filesystem.

### BEGIN INIT INFO
# Provides:     redis_6379
# Default-Start:        2 3 4 5
# Default-Stop:         0 1 6
# Short-Description:    Redis data structure server
# Description:          Redis data structure server. See https://redis.io
### END INIT INFO

REDISPORT=6379
EXEC=/usr/local/bin/redis-server
CLIEXEC=/usr/local/bin/redis-cli

PIDFILE=/var/run/redis_${REDISPORT}.pid
CONF="/usr/local/redis/redis.conf"

case "$1" in
    start)
        if [ -f $PIDFILE ]
        then
                echo "$PIDFILE exists, process is already running or crashed"
        else
                echo "Starting Redis server..."
                $EXEC $CONF
        fi
        ;;
    stop)
        if [ ! -f $PIDFILE ]
        then
                echo "$PIDFILE does not exist, process is not running"
        else
                PID=$(cat $PIDFILE)
                echo "Stopping ..."
                $CLIEXEC -p $REDISPORT shutdown
                while [ -x /proc/${PID} ]
                do
                    echo "Waiting for Redis to shutdown ..."
                    sleep 1
                done
                echo "Redis stopped"
        fi
        ;;
    *)
        echo "Please use start or stop as first argument"
        ;;
esac
```

> 注意：如果前面安装和调整路径有所不同，则这里的脚本需要修改其中对应的路径。

### 7. 设置开机自启动

```shell
# 复制脚本文件到init.d目录下
cp /usr/local/redis/etc/redis /etc/init.d/

# 给脚本增加运行权限
chmod +x /etc/init.d/redis

# 添加服务
chkconfig --add redis

# 配置启动级别
chkconfig --level 2345 redis on

# 查看服务列表
chkconfig --list
```

### 8. 启动与停止

```shell
systemctl start redis   # 或者 /etc/init.d/redis start  
systemctl stop redis   # 或者 /etc/init.d/redis stop

# 查看redis进程
ps -el|grep redis

# 端口查看
netstat -an|grep 6379
```

### 9. 问题

如果启动失败，则可以通过`systemctl status redis`查看相关信息。根据错误提示进行调整即可。

#### 9.1 取消认证密码

默认情况下，Redis 是只允许本地登录，且不需要使用密码进行认证的。如果开启了有认证密码的 Redis 服务，则关闭的时候也需要提供认证密码，否则无法正常关闭。

为了在前面配置的系统服务配置文件中，可以自动关闭，可以将`stop`命令改成类似如下的操作：

```shell
stop)
    if [ ! -f $PIDFILE ]
    then
        echo "$PIDFILE does not exist, process is not running"
    else
        PID=$(cat $PIDFILE)
        echo "Stopping ..."
        
        PASSWORD=$(cat $CONF | grep '^\s*requirepass'|awk '{print $2}'|sed 's/"//g')
        
        if [ -z $PASSWORD ]
        then 
            $CLIEXEC -p $REDISPORT shutdown
        else
            $CLIEXEC -a $PASSWORD -p $REDISPORT shutdown
        fi

        while [ -x /proc/${PID} ]
        do
            echo "Waiting for Redis to shutdown ..."
            sleep 1
        done
        echo "Redis stopped"
    fi
    ;;
```

> 参考：[Redis设置密码之后，关闭服务的问题](https://blog.csdn.net/u010309394/article/details/81807597)

### 10. 参考

1. [CentOS 7 源码编译安装 Redis](https://www.cnblogs.com/stulzq/p/9288401.html)
2. [CENTOS7下安装REDIS](https://www.cnblogs.com/zuidongfeng/p/8032505.html)


