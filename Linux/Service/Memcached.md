## 配置

Memcached 的配置文件位于`/etc/sysconfig/memcached`，修改之后，重启 Memcached 服务即可。默认的配置文件的内容如下：

```conf
PORT="11211"
USER="memcached"
MAXCONN="1024"
CACHESIZE="64"
OPTIONS=""
```

> 可以通过`ps -ef | grep memcached`查看启动参数。

为了安全，一般建议修改 Memcached 的端口，避免被利用。另外，还有如下的方式来进行加固：

### 监听在本机

Memcached 默认情况下，如果不指定`-l`参数，则会监听在`0.0.0.0`地址上，这样会让外部也能访问，一般可以将其固定监听在某个内网 IP 上，增加其安全性，或者可以直接设置成`127.0.0.1`来监听本机即可。

> 这个配置可以写在 Memcached 配置文件中的`OPTIONS`中。

### 关闭 UDP

Memcached 在 1.5.6 版本之后默认关闭 UDP，但是之前的版本需要手动关闭 UDP。因为目前 Memcached 的 UDP 开启时，如果没有做好防护措施，会被用来进行反射攻击，消耗服务器的出网带宽和 CPU 资源。

关闭 UDP 很简单，就是增加一个参数即可：`-U 0`。

> 这个配置可以写在 Memcached 配置文件中的`OPTIONS`中。



