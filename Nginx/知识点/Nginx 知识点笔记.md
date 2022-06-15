### 关闭错误日志

`error_log off`并不能关闭日志记录功能，而是将日志文件写入一个文件名为`off`的文件中。

如果想关闭错误日志记录功能，应使用以下配置： 

```conf
error_log /dev/null crit; 
```

### 开启 http2

Nginx 目前已经支持 http2，但是要开启 http2 必须要使用 https，也就是要先设置证书。

对于已经开启 https 的情况下，可以通过如下配置来开启 http2：

```conf
server {
   listen       443 ssl http2;
   server_name  localhost;

   ssl_certificate      server.crt;
   ssl_certificate_key  server.key;
}
```

### 域名的匹配顺序

Nginx 中的`server_name`指令主要用于配置基于名称虚拟主机。同一个 Nginx 虚拟主机中，可以绑定多个server_name，各个域名用空格隔开即可。如下：

```conf
server
{
    listen       80;
    server_name  test.com www.test.com;
    ...
}
```

> 如果`server_name`有多个，那么通过代码(如`$_SERVER["SERVER_NAME"]`)获取的始终将是 Nginx `server_name`配置中的第一个域名，第一个域名就相当于 Apache 虚拟主机配置中的 ServerName，后面的域名就相当于 Apache 的 ServerAlias。

如果 Nginx 中有多个 server 块，那么请求匹配`server_name`的顺序如下：

1. 首先匹配准确的`server_name`，如：`server_name  test.com www.test.com;`。
2. 然后匹配以`*`通配符开始的`server_name`，如：`server_name  *.test.com;`。
3. 然后匹配以`*`通配符结束的`server_name`，如：`server_name  www.test.*;`。
4. 最后匹配正则表达式形式的`server_name`，如：`server_name ~^(?<www>.+)\.test\.com$;`。

以上只要有一项匹配到以后就会停止搜索。

### fastcgi_index 和 index 的却别

常规的`index`指令用于指定默认的文件名。当访问的 URI 没有指定特定的文件名的时候，会默认使用该路径下的由`index`指令的参数指定的文件作为响应。

`fastcgi_index`指令可以使用在`http`、`server`、`location`配置块中，如果请求的 URI 以斜线(`/`)结尾，文件名将追加到 URI 后面，这个值将存储在变量`$fastcgi_script_name`中。

比如，对于如下的配置：

```conf
fastcgi_index  index.php;
fastcgi_param  SCRIPT_FILENAME  /home/www/scripts/php$fastcgi_script_name;
```

请求`/page.php`的时候，`SCRIPT_FILENAME`将被设置为`/home/www/scripts/php/page.php`，但是请求`/`时，其值则为`/home/www/scripts/php/index.php`。


