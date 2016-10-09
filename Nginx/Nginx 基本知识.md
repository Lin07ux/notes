Nginx 这个轻量级、高性能的 web server 主要可以做两件事：

- 直接作为 http server(代替 Apache，对 PHP 网站需要传递请求给 FastCGI 处理)；
- 作为反向代理服务器实现负载均衡。

Nginx 的优势在于处理并发请求上。虽然 Apache 的`mod_proxy`和`mod_cache`结合使用也可以实现对多台 app server 的反向代理和负载均衡，但是在并发处理方面 Apache还是没有 Nginx 擅长。


## 安装和运行
### 安装
在 Windows 下，下载好了相应的压缩包之后，解压即可使用。

在类 CentOS 中，可以通过添加相应的仓库之后，使用`yum -y install nginx`来安装。

### 运行命令
Nginx 提供了几个命令来控制 Nginx 的启停：

- 启动：`start nginx`
- 快速停止：`nginx -s stop`
- 优雅重载：`nginx -s reload`。这个命令一般用在修改了 Nginx 配置文件之后，无中断的重启 Nginx 服务。
- 退出：`nginx -s quit`。这个命令会彻底退出 Nginx 服务。
- 重新打开日志文件：`nginx -s reopen`。

> 在 Windows 系统中，上述命令需要在 Nginx 的安装目录下运行。
如果添加 Nginx 安装目录到系统路径下了，就可以在任意地方运行。

### 查看和杀除进程
找到 Nginx pid 的两个方法：
- nginx.pid 文件，在 /usr/local/nginx/logs or /var/run
- ps -ax | grep nginx

杀死进程：
- kill -s QUIT 1628

## 配置
### 指令
- `root` 声明根目录的路径。可以放到 http、server、location 的任一层级里。子层级重新定义的 root 会覆盖父层级的 root。
- `index` 定义 index 文件的名称。默认是 index.html，也可以指定为其他名称，可以指定多个名字，用空格分隔，Nginx 会按定义顺序查找，返回第一个可访问的文件。可以用在 server、location 中。当一个请求以斜杠结尾，nginx 认为它想访问一个目录然后会去寻找目录中的 index 文件。
- `audoindex` 开启或关闭列出该目录下的文件功能。如果 index 文件不存在会报404，但是如果想列出该目录下的文件，则使用`audoindex on;`指令即可。
- `try_files` 检测指定的文件或者目录是否存在，如果不存在进行内部跳转或者返回一个指定的状态码。可以指定多个备选的跳转地址。

```nginx
server {
    root /www/data;

    location /images/ {
        # 如果uri所请求的文件不存在
        # 就会内部重定向到 /www/data/images/default.gif。
        try_files $uri /images/default.gif;
    }
    
    location / {
        # 第二个参数也可以是一个指定的状态码。
        try_files $uri $uri/ $uri.html =404;
    }
}
```
- `proxy_pass` 指定代理的地址，地址可以包含端口号。在 location 中使用。如果代理的地址包含一个 uri(如下面例子中的`/link/`)，它将会替换请求的 uri 中匹配 location 参数的那部分。如果代理的地址没有 uri，那么将整个 uri 传递过来。还可以将请求转到非 http 服务：
    * `fastcgi_pass`   转到 FastCGI 服务器
    * `uwsgi_pass`     转到 uwsgi 服务器
    * `scgi_pass`      转到 scgi 服务器
    * `memcached_pass` 转到 memcached 服务器

```nginx
location /some/path/ {
    # 如果请求是 /some/path/page.html，
    # uri则会被代理成：http://www.example.com/link/page.html。
    proxy_pass http://www.example.com/link/;
}

location ~ \.php {
    proxy_pass http://127.0.0.1:8000;
}
```

- `proxy_set_header` 传递请求头。
    * 默认的，nginx 重新定义两个 header："Host"和"Connection"，并且排除掉值为空的 header。"Host"的值是`$proxy_host`变量的值，"Connection"设置成 close。
    * 这个指令可以放到 location 或者更高的层级，可以在特定的 server context 或者 http 块中指定。
    * 如果不想让某个 header 传给被代理的服务器，将其置空。
- `proxy_bind` 指定出口 IP。
    * 代理服务器可能有多个 ip 地址，对于一个特定的被代理的 server 可以选择一个特定的 ip 地址。
    * ip 地址也可以使用变量，如`$server_addr`，指定的是接受请求的那个 ip。
- `upstream` 设置一组代理服务器。用在 http context 里。
    * 可以选择一种负载均衡方法，默认的是权重轮询算法，nginx 会按照各个 server的权重比来分发请求。默认每个服务器的权重均为 1。
    * 每个代理服务器可以指定一个权重，用`weight`来指定。权重高的会更有可能被转发到。

```nginx
http {
    upstream backend {
        server backend1.example.com weight=5;
        server backend2.example.com;
        server 192.0.0.1 backup;
    }
    
    server {
        # 使用proxy_pass指令把请求打到被代理的服务
        location / {
            proxy_pass http://backend;
        }
    }
}
```

### server
- 每个 server 对应一个 web 站点
- 一个 http 可以包含多个 server
- server 里面`listen`表明这个 server 的端口号
- `server_name`就是 host，一个 server Context 可以配置多个 server_name。如：`server_name localhost localhost.org;`
- server_name 可以使用通配符、正则表达式
- 如果 host header 不能够匹配任何一个 server_name，可以设置`default_server`来处理它

### location
location 指令有两种不同的参数：
- 匹配规则
- context 内容

**匹配规则**
匹配规则支持多种方式，而且支持添加修饰符来调整匹配规则。

- 前缀字符串(pathnames)：那些以该前缀字符串开头的的URIs都会匹配到。如：

```nginx
# 这个会匹配/some/path/test 但是不会匹配 /test/some/path
location /some/path {
    ...
}
```
- 正则表达式：和一般的正则表达式基本一样。只是其修饰符有些不一样
    * 大小写敏感的前面加`~`修饰符
    * 大小写不敏感的加`~*`修饰符
    * 不匹配正则表达式加`^~`修饰符

**context**
- `root`指令来声明访问静态文件的路径，如 uri 是 /images/example.png，如果匹配到的 location 的 root 指令的值是 /data，那么就会把 /data/images/example.png 作为响应。
- `proxy_pass`指令，将请求转到配置的 url 上，然后将被代理的 server 的响应转发到客户端。

### 变量
在配置 nginx 的时候可以使用变量，*变量以`$`开头*，变量在运行时得到对应的值，然后作为参数传给指令。有很多预定义的变量，如 core HTTP varabiles，可以用 set map 和 geo 指令来自定义变量。大多数的变量都是在运行时得到值并且包含与特定请求相关的信息。注意是特定请求不是 context。

### 返回特定的状态码
最简单的方式是使用`return`指令返回特定的状态码。可以设置一到两个参数，第一个参数是状态码，第二个参数是重定向的 url 或者返回体中的文字（只限于301、302、303、307）。

```nginx
location /wrong/url {
    return 404;
}

location /permanently/moved/url {
    return 301 http://www.example.com/moved/here;
}
```

### url 重写
- 通过`rewrite`指令进行 url 重写，在 server 和 location 里面都可以使用 rewrite 指令。
- 一共有三个参数，第一个是匹配 url 的正则表达式，第二个参数是重写的 url，第三个参数是一个标记，是可选参数。
- uri 可以多次更改，uri 会依次匹配每个 rewrite 指令。
- 第三个参数，last 和 break 的区别：
    * 两个值在 server context 中表现一致，跳过剩下的 rewrite 指令，进行location 匹配。
    * 在 location context 中，last 会跳出当前 location 寻找新的 location 匹配，而 break 不会再去匹配其他的 location，在当前 location请求对应的 uri。
- 小心循环重定向，尽量在 server context 里面进行 url 重写。

```nginx
# 多个rewrite指令，如果都不匹配返回403
server {
    ...
    rewrite ^(/download/.*)/media/(.*)\..*$ $1/mp3/$2.mp3 last;
    rewrite ^(/download/.*)/audio/(.*)\..*$ $1/mp3/$2.ra  last;
    return  403;
    ...
}

# 循环重定向
location = error.html {
    rewrite /error.html /error.html last;
    fastcgi_pass 127.0.0.1:9000;
}

# break
location /users/ {
    rewrite ^/users/(.*)$ /show?user=$1 break;
}
```

### 错误处理
通过 error_page 指令，来处理错误。

- 当发生404错误时候，返回一个自定义的页面：`error_page 404 /404.html;`
- 当发生404的时候，重定向到一个新的 url：

```nginx
location /old/path.html {
    error_page 404 =301 http:/example.com/new/path.html;
}
```
- 内部重定向

```nginx
server {
    ...
    location /images/ {
        root /data/www;

        # Disable logging of errors related to file existence
        # 用于组织文件找不到时的报错信息，这里就不需要打开了，
        # 因为文件找不到时候已经被处理掉了。
        open_file_cache_errors off;

        # Make an internal redirect if the file is not found
        # 将找不到的地址重定向到 /fetch$uri 上进行处理
        error_page 404 = /fetch$uri;
    }

    location /fetch/ {
        proxy_pass http://backend/;
    }
}
```

