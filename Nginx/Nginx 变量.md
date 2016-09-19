* `$arg_PARAMETER`  如果在请求中设置了查询字符串，那么这个变量包含在查询字符串是 GET 请求 PARAMETER 中的值。

* `$args`  该变量的值是 GET 请求在请求行中的参数。

* `$binary_remote_addr`  二进制格式的客户端地址

* `$body_bytes_sent`  响应体的大小，即使发生了中断或者是放弃，也是一样的准确。

* `$content_length`  该变量的值等于请求头中的 Content-length 字段的值

* `$cookie_COOKIE`  该变量的值是 cookie COOKIE 的值

* `$document_root`  该变量的值为当前请求的 location(http，server，location，location 中的 if)中 root 指令中指定的值。

* `$document_uri`  同$uri

* `$host`  该变量的值等于请求头中 Host 的值。如果 Host 无效时，那么就是处理该请求的 server 的名称。在下列情况中，`$host`变量的取值不同于`$http_host`变量。
    * 当请求头中的 Host 字段未指定（使用默认值）或者为空值，那么`$host`等于 server_name 指令指定的值。
    * 当 Host 字段包含端口时，`$host`并不包含端口号。另外，从 0.8.17 之后的 nginx 中，`$host`的值总是小写。

* `$hostname`  由 gethostname 返回值设置机器名。

* `$http_HEADER`  该变量的值为 HTTP 请求头 HEADER，具体使用时会转换为小写，并且将“-”（破折号）转换为"_"(下划线)。

* `$is_args`  如果设置了`$args`，那么值为“?”，否则为“”

* `$limit_rate`  该变量允许限制连接速率。

* `$nginx_version`  当前运行的 nginx 的版本号

* `$query_string`  同`$args`

* `$remote_addr`  客户端的 IP 地址

* `$remote_user`  该变量等于用户的名字，基本身份验证模块使用。

* `$remote_port`  客户端连接端口

* `$request_filename`  该变量等于当前请求文件的路径，由指令 root 或者 alias 和 URI 构成。

* `$request_body`  该变量包含了请求体的主要信息。该变量与 proxy_pass 或者 fastcgi_pass 相关。

* `$request_body_file`  客户端请求体的临时文件。

* `$request_completion`  如果请求成功完成，那么显示“OK”。如果请求没有完成或者请求不是该请求系列的最后一部分，那么它的值为空。

* `$request_method`  该变量的值通常是 GET 或者 POST。

* `$request_uri`  该变量的值等于原始的 URI 请求，就是说从客户端收到的参数包括了原始请求的 URI，该值是不可以被修改的，不包含主机名，例如“/foo/bar.php?arg=baz”。

* `$scheme`  该变量表示 HTTP scheme（例如 HTTP，HTTPS），根据实际使用情况来决定，
   例如：`rewrite  ^ $scheme://example.com$uri redirect;`。

* `$server_addr`  该变量的值等于服务器的地址。通常来说，在完成一次系统调用之后就会获取变量的值，为了避开系统钓鱼，那么必须在 listen 指令中使用 bind 参数。

* `$server_name`  该变量为 server 的名字。

* `$server_port`  该变量等于接收请求的端口。

* `$server_protocol`  该变量的值为请求协议的值，通常是`HTTP/1.0`或者`HTTP/1.1`

* `$uri`  该变量的值等于当前请求中的 URI（没有参数，不包括`$args`）的值。它的值不同于`$request_uri`，由浏览器客户端发送的 request_uri 的值。例如，可能会被内部重定向或者使用 index。另外需要注意：`$uri`不包含主机名，例如 "/foo/bar.html"
 
* 当前 URL = `$scheme://$server_name/$url`

转载：![nginx变量](http://blog.sina.com.cn/s/blog_594c47d00101dfyh.html)

