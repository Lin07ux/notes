> 转摘：
>   1. [「Nginx」- 配置调试（打印查看、配置验证、Debug）](https://blog.k4nz.com/ede1cb3a6ee3609298844c335e05d197/)
>   2. [「Nginx」- 自定义日志格式](https://blog.k4nz.com/9af91ce37c3fc5b5cff55536fdeff5cb/)

Nginx 文档的部分内容没有详细的说明，只能通过调试的方式来查看相关变量的值。

调试查看 Nginx 的配置可以使用如下的一些方法：

### 1. 通过 add_header 指令

`add_header`指令可以为响应添加指定的响应头，并通过变量等方式凭借头信息值，而且不会影响请求的实际执行。

比如：

```conf
location / {
    add_header debug-geoip-city "$getoip_city_continent_code, $geoip_city_country_name, $geoip_city" always;
}
```

这样，在响应头信息部分，就能看到`debug-geoip-city`头信息了：

```shell
> curl --head https://domain.com
HTTP/1.1 404 Not Found
Server: nginx/1.14.0 (Ubuntu)
Date: Mon, 07 Jun 2021 03:44:25 GMT
Content-Type: text/html
Content-Length: 178
Connection: keep-alive
debug-geoip-city: AS, China, Yuzhong Chengguanzhen
```

### 2. 通过 return 指令

`return`指令同样也能用于调试，和`add_header`指令类似，可以在返回的信息中自定义拼接数据。

与`add_header`指令不同的是，`return`指令作为一个返回指令，会中断请求的继续执行，而是直接返回`return`指定的内容。

比如：

```conf
location / {
    return 200 debug-geoip-city "$getoip_city_continent_code, $geoip_city_country_name, $geoip_city" always;
}
```

此时，请求的时候就会直接得到 getoip 相关的数据了：

```shell
> curl https://domain.com
AS, China, Yuzhong Chengguanzhen
```

### 3. 通过自定义日志格式

在 Nginx 记录日志时，可以配置自定义的 Log Format 来记录相关变量的值，从而实现调试目的。

比如，在 Nginx 中自定义一个`compression`日志格式，并应用改日志格式：

```conf
http {
    log_format compression '$remote_addr - $remote_user [$time_local] '
                           '"$request" $status $body_bytes_sent '
                           '"$http_referer" "$http_user_agent" "$gzip_ratio"';

    server {
        gzip on;
        access_log /spool/logs/nginx-access.log compression;
        ...
    }
}
```

然后在访问日志中就能记录下所关注的变量的数据，从而实现调试目的。

当然，还可以通过为`access_log`指令添加`if=condition`条件，以在特定的时候才记录日志。这样能够使得日志更加聚焦于关注的情况。

比如：

```conf
map $status $loggable {
  ~^[23]  0;
  default 1;
}

access_log /spool/logs/nginx-access.log compression if=$loggable;
```

### 4. 通过 Debug 功能

Nginx 安装的 时候默认没有开启 debug 功能，所以使用该方式进行调试前，需要重新编译安装 Nginx，并通过`--with-debug`选项来开启调试，所以该方式一般不做使用。

首先，确认已开启调试功能：

```shell
> nginx -V 2>&1 | grep -- '--with-debug'
```

然后，指定错误日志的级别为 debug：

```conf
error_log /var/log/nginx/error.log debug;
```

之后，有请求的时候，Nginx 就会记录所有的变量值日志：

```
...
2021/03/06 15:14:48 [debug] 2550#2550: *1 fastcgi param: "SCRIPT_FILENAME: /usr/lib/cgit/cgit.cgi/something.git/cgit.cgi"
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script copy: "QUERY_STRING"
2021/03/06 15:14:48 [debug] 2550#2550: *1 fastcgi param: "QUERY_STRING: "
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script copy: "REQUEST_METHOD"
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script var: "GET"
2021/03/06 15:14:48 [debug] 2550#2550: *1 fastcgi param: "REQUEST_METHOD: GET"
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script copy: "CONTENT_TYPE"
2021/03/06 15:14:48 [debug] 2550#2550: *1 fastcgi param: "CONTENT_TYPE: "
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script copy: "CONTENT_LENGTH"
2021/03/06 15:14:48 [debug] 2550#2550: *1 fastcgi param: "CONTENT_LENGTH: "
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script copy: "SCRIPT_NAME"
2021/03/06 15:14:48 [debug] 2550#2550: *1 http script var: "/cgit/cgit.cgi/something.git/cgit.cgi"
2021/03/06 15:14:48 [debug] 2550#2550: *1 fastcgi param: "SCRIPT_NAME: /cgit/cgit.cgi/something.git/cgit.cgi"
...
```

