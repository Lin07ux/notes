> 转摘：[后端实践：如何进行 Nginx 日志配置？（超详细）](https://mp.weixin.qq.com/s/gdZ1d_SLE4Y-4ux1ZXaZUw)

Nginx 日志主要分为两种：access_log(访问日志)和 error_log(错误日志)。

通过访问日志可以得到用户的 IP 地址、浏览器信息、请求处理时间等信息；错误日志记录了访问出错的相关信息，可以帮助定位错误位置和原因。

下面针对 Nginx 中的日志配置功能介绍`access_log`、`error_log`、`log_format`、`open_log_file_cache`指令的使用。

### 1. access_log

`access_log`用于配置 Nginx 访问日志是否开启、保存在何处、是否需要压缩等。

可以应用`access_log`指令的作用域分别有 http、server、location、limit_except，在这些作用域之外使用该指令时 Nginx 会报错。

语法：

```conf
# 设置访问日志的记录保存情况
access_log path [format [buffer=size] [gizp[=level]] [flush=time] [if=condition]];
# 关闭访问日志
access_log off;
```

根据`access_log`的语法可知，如果将其配置为`off`，那么当前作用域下的所有的请求日志都不会记录了。

当开启访问日志时，除了`path`项为必须配置的之外，其他配置项都是可选的。各个配置项的说明如下：

* `path` 指定日志的存放位置
* `format` 指定日志的格式，默认使用预定于的`combined`。
* `buffer` 用来指定日志写入时的缓存大小，默认是 64k。
* `gzip` 日志写入前先进行压缩。压缩率可以指定，从 1 到 9 数值越大压缩比例越高，同时压缩的速度也越慢。默认是 1。
* `flush` 设置就缓存的有效时间。如果超过 flush 指定的时间，缓存中的内容将被清空。
* `if` 写入日志的条件。如果指定的条件计算的值为 0 或空字符串，那么该请求的信息将不会被写入访问日志。

常见配置示例如下：

```conf
# 最简单设置，仅设置写入路径
access_log /var/logs/nginx-access.log;

# 设置缓存大小和刷新时间，以及压缩比率
access_log /var/logs/nginx-access.log 
```

### 2. error_log

错入日志在 Nginx 中是通过`error_log`指令实现的，该指令记录服务器和请求处理过程中的错误信息。

可以配置在`main`、`http`、`mail`、`stream`、`server`、`location`作用域。

语法：

```
error_log file [level]
```

该指令配置项只有两个：

* `file` 指定日志的写入位置。
* `level` 指定记录的日志的级别，可以是`debug`、`info`、`warn`、`error`、`alert`、`emerg`，默认是`error`。只有错误的级别等于或高于 level 指定的值才会写入错误日志中。

### 3. log_format

客户端向 Nginx 服务器发起的每一次请求都会被记录在访问日志中，一般包括：客户端 IP、浏览器信息、referer、请求处理时间、请求 URL 等。当然，也可以通过`log_format`配置来决定记录哪些信息。

语法：

```conf
log_format name [escape=default|json] string ...;
```

`log_format`的语法较为简单，各项配置的说明如下：

* `name` 格式名称，在`access_log`中会用到。
* `escaped` 设置变量中的字符编码方式是 json 还是 default，默认是 default。
* `string` 要定义的日志格式内容，该参数可以有多个，而且参数中可以使用 Nginx 变量。

下面是`log_format`指令中常用的一些变量：

    变量                  |    含义
-------------------------|---------------------------------
 `$bytes_sent`           | 发送给客户端的总字节数
 `$body_bytes_sent`      | 发送给客户端的字节数，不包括响应头的大小
 `$connection`           | 连接序列号
 `$connection_requests`  | 当前通过连接发出的请求数量
 `$http_referer`         | 请求的 referer 地址
 `$http_user_agent`      | 客户端浏览器信息
 `$http_x_forwarded_for` | 当前端有代理服务器时，设置 Web 节点记录客户端地址的配置，此参数生效的前提是代理服务器也要进行相关的`x_forwarded_for`配置
 `$msec`                 | 日志写入时间，单位为秒，精度是毫秒
 `$pipe`                 | 如果请求是通过 http 流水线发送，则其值为`p`，否则为`.`
 `$remote_addr`          | 客户端 IP
 `$remote_user`          | 客户端用户名称，针对启用了用户认证的请求
 `$request`              | 完整的原始请求行，如`GET / HTTP/1.1`
 `$request_length`       | 请求长度，包括请求行、请求头和请求体
 `$request_uri`          | 完整的请求地址，如`https://daojia.com/`
 `$request_time`         | 请求处理时长，单位为秒，精度为毫秒，从读入客户端的第一个字节开始，直到把最后一个字符发送到客户端进行日志写入为止
 `$status`               | 响应状态码
 `$time_iso8601`         | 标准格式的本地时间，形如`2019-01-01T18:31:27+08:00`
 `$time_local`           | 通用日志格式下的本地时间，形如`24/May/2019 18:31:27 + 08:00`

Nginx 中预定义了名为`combined`的日志格式，如果在`access_log`中没有明确指定日志格式，那么使用的就是该格式：

```conf
log_format combined '$remote_addr - $remote_user [$time_local]'
            '"$request" $status $body_bytes_sent'
            '"$http_referer" "$http_user_agent"';
```

如果不想使用 Nginx 预定义的格式，可以自定义格式，并在`access_log`的配置中指定自定义的格式。比如下面是一个自定义的日志格式：

```conf
log_format main '$remote_addr - $remote_user [$time_local] "$request"'
            '$status $body_bytes_send "$http_referer"'
            '"$http_user_agent" "$http_x_forwarded_for"';

# 使用该自定义日志格式
access_log /var/logs/nginx-access.log main;
```

此时，假如客户端有发起请求`https://suyunfe.com/`，请求的日志记录类似如下：

```
112.195.209.90 - - [20/Feb/2018:12:12:14 +0800] 
"GET / HTTP/1.1" 200 190 "-" "Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) 
AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Mobile Safari/537.36" "-"
```

可以看到最终的日志记录中`$remote_user`、`$http_referer`、`$http_x_forwarded_for`都对应了一个`-`，这是因为这几个变量为空。

### 4. open_log_file_cache

每一条日志记录的写入都是先打开文件再写入记录，然后关闭日志文件。

如果日志文件路径中使用了变量，如`access_log /var/logs/$host/nginx-access.log`，为提高性能，可以使用`open_log_file_cache`指令设置日志文件描述符的缓存。

`open_log_file_cache`指令可以配置在：`http`、`server`、`location`作用域中。

语法：

```conf
open_log_file_cache max=N [inactive=time] [min_uses=N] [valid=time];
```

各个配置项的说明如下：

* `max` 设置缓存中最多容纳的文件描述符数量，如果被占满，采用 LRU 算法将描述符关闭。
* `inactive` 设置缓存存活时间，默认值是 10s。
* `min_uses` 在`inactive`时间段内，日志文件最少使用几次时该日志描述符记入缓存中，默认是 1 次。
* `valid` 设置多久对日志文件名进行一次检查，看是否发生变化，默认是 60s。
* `off` 不使用缓存，默认为`off`。

配置示例如下：

```conf
open_log_file_cache max=1000 inactive=20s min_uses=2 valid=1m;
```

这里设置缓存最多缓存 1000 个日志文件描述符，20s 内如果缓存中的日志文件描述符至少被被访问 2 次，才不会被缓存关闭。每隔 1 分钟检查缓存中的文件描述符的文件名是否还存在。

