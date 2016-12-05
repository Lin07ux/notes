Nginx 这个轻量级、高性能的 web server 主要可以做两件事：

- 直接作为 http server(代替 Apache，对 PHP 网站需要传递请求给 FastCGI 处理)；
- 作为反向代理服务器实现负载均衡。

Nginx 的优势在于处理并发请求上。虽然 Apache 的`mod_proxy`和`mod_cache`结合使用也可以实现对多台 app server 的反向代理和负载均衡，但是在并发处理方面 Apache 还是没有 Nginx 擅长。


## 安装和运行
### 安装
在 Windows 下，下载好了相应的压缩包之后，解压即可使用。

在类 CentOS 中，可以通过添加相应的仓库之后，使用`yum -y install nginx`来安装。

### 控制命令
Nginx 提供了几个命令来控制 Nginx 的启停：

- `start nginx` 启动 Nginx 服务
- `nginx -s stop` 快速停止 Nginx
- `nginx -s reload` 优雅重载。这个命令一般用在修改了 Nginx 配置文件之后，无中断的重启 Nginx 服务。
- `nginx -s quit` 退出。这个命令会彻底退出 Nginx 服务。
- `nginx -t` 检查配置文件。在修改了 Nginx 的配置文件之后，重启之前，使用这个命令来检查是否有错误的配置。
- `nginx -s reopen` 重新打开日志文件。

> 在 Windows 系统中，上述命令需要在 Nginx 的安装目录下运行。
如果添加 Nginx 安装目录到系统路径下了，就可以在任意地方运行。

### 查看和杀除进程
找到 Nginx pid 的两个方法：

- nginx.pid 文件，在`/usr/local/nginx/logs`或`/var/run`
- `ps -ax | grep nginx`

杀死进程：`kill -s QUIT 1628`

## 配置
下面是一份`nginx.conf`配置文件的骨架：

```conf
# main 全局设置空间

events {  
  ....
}

http {  
  upstream myproject_svr {
    .....
  }
  
  server  {
    location {
        ....
    }
  }
  
  server  {
    location {
        ....
    }
  }
  
  ....
}
```

nginx 配置文件主要分为六个区域：

* `main` 全局设置。主要设置一些全局的属性，比如用户组、进程数、错误日志等。
* `events` 工作模式。用于指定 nginx 的工作模式和连接数上限
* `http` http 设置。是最核心的模块。它负责 HTTP 服务器相关属性的配置，它里面的 server 和 upstream 子模块至关重要。
* `upstream` 负载均衡服务器设置。通过简单的调度算法来实现客户端 IP 到后端服务器的负载均衡。
* `server` 主机设置。http 的子模块，它用来设定一个虚拟主机。里面有 location 模块。
* `location` URL 匹配。是 nginx 中用的最多的，也是最重要的模块。用来定位 URL，解析 URL。负载均衡、反向代理、虚拟域名都与它相关。


### upstream
设置一组代理服务器。用在 http context 里。

可以选择一种负载均衡方法，默认的是权重轮询算法，nginx 会按照各个 server 的权重比来分发请求。默认每个服务器的权重均为 1。每个代理服务器可以指定一个权重，用`weight`来指定。权重高的会更有可能被转发到。

```conf
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
#### listen
server 配置块使用`listen`命令监听本机 IP 和端口号（包括 Unix domain socket and path），支持 IPv4、IPv6，IPv6 地址需要用方括号括起来。

如果不写端口号，默认使用 80 端口，如果不写 IP ，则监听本机所有 IP。

```conf
server {
    listen 127.0.0.1:8080;  # IPv4地址，8080端口
    # listen [2001:3CA1:10F:1A:121B:0:0:10]:80;   # IPv6地址，80端口
    # listen [::]:80;  # 听本机的所有IPv4与IPv6地址，80端口
    # listen 80;       # 监听本机所有 IP 的 80 端口
}
```

#### server_name
如果多个 server 的 listen IP 和端口号一模一样， Nginx 通过请求头中的 Host 与`server_name`定义的主机名进行比较，来选择合适的虚拟服务器处理请求：

```conf
server {
    listen      80;
    server_name lufficc.com  www.lufficc.com;
    ...
}
```

server_name 的参数可以为：

1.	完整的主机名，如：`api.lufficc.com`。
2.	含有通配符（含有`*`），如：`*.lufficc.com 或 api.*`。
3.	正则表达式，以`~`开头。

需要注意的是：**通配符只能在开头或结尾，而且只能与一个`.`相邻**。`www.*.example.org`和`w*.example.org`均无效。但是，可以使用正则表达式匹配这些名称，例如`~^www\..+\.example\.org$`和`~^w.*\.example\.org$`。**而且`*`可以匹配多个部分**。名称`*.example.org`不仅匹配`www.example.org，还匹配`www.sub.example.org`。

对于正则表达式：Nginx 使用的正则表达式与 Perl 编程语言（PCRE）使用的正则表达式兼容。**要使用正则表达式，必须以 ~ 开头**。

命名的正则表达式可以捕获变量，然后使用：

```conf
server {
    server_name   ~^(www\.)?(?<domain>.+)$;

    location / {
        root   /sites/$domain;
    }
}
```

小括号`()`之间匹配的内容，也可以在后面通过`$1`来引用，`$2`表示的是前面第二个`()`里的内容。因此上述内容也可写为：

```conf
server {
    server_name   ~^(www\.)?(.+)$;

    location / {
        root   /sites/$2;
    }
}
```

如果有多个 server_name 值匹配 Host 头部， Nginx 采用下列顺序选择：

1.	完整的主机名，如`api.lufficc.com`。
2.	最长的，且以`*`开头的通配名，如：`*.lufficc.com`。
3.	最长的，且以`*`结尾的通配名，如：`api.*`。
4.	第一个匹配的正则表达式。（按照配置文件中的顺序）

> 即优先级：`api.lufficc.com > *.lufficc.com > api.* > 正则`。

如果 Host 头部不匹配任何一个 server_name，Nginx 将请求路由到默认虚拟服务器。**默认虚拟服务器是指：nginx.conf 文件中第一个 server ，或者显式用`default_server`声明**：

```conf
server {
    listen  80 default_server;
    ...
}
```

#### root
`root`声明根目录的路径。可以放到 http、server、location 的任一层级里。子层级重新定义的 root 会覆盖父层级的 root。

匹配到的 location 中，请求最终对应的文件路径会是 root 指定的路径后面跟上 URI。

如，有如下设置：

```conf
server {
    location /images/ {
        root /data;
    }
}
```

如果请求`/images/example.png`，则拼接后返回的本地服务器文件是`/data/images/example.png`。

#### index
定义 index 文件的名称。默认是 index.html，也可以指定为其他名称，可以指定多个名字，用空格分隔，Nginx 会按定义顺序查找，返回第一个可访问的文件。

`index`可以用在 server、location 中。

当一个请求以斜杠结尾，nginx 认为它想访问一个目录然后会去寻找目录中的 index 指定的文件。

### location
当选择好 server 之后，Nginx 会根据 URI s 选择合适的 location 来决定代理请求或者返回文件。

location 指令接受两种类型的参数：

1.	前缀字符串（路径名称）
2.	正则表达式

对于前缀字符串参数， URIs 必须严格的以它开头。例如对于`/some/path/`参数，可以匹配`/some/path/document.html`，但是不匹配`/my-site/some/path`，因为它不以`/some/path/`开头。

对于正则表达式，以`~`开头表示大小写敏感，以`~*`开头表示大小写不敏感。注意路径中的`.`要写成`\.`。例如一个匹配以`.html`或者`.htm`结尾的 URI 的 location：

```conf
location ~ \.html? {
    ...
}
```

location 的搜索匹配流程如下：

1.	将 URI 与所有的前缀字符串进行比较。
2.	`=`修饰符表明 URI 必须与前缀字符串相等（不是开始，而是相等），如果找到，则搜索停止。
3.	如果找到的最长前缀匹配字符串以`^~`开头，则不再搜索正则表达式是否匹配。
4.	存储匹配的最长前缀字符串。
5.	测试对比 URI 与正则表达式。
6.	找到第一个匹配的正则表达式后停止。
7.	如果没有正则表达式匹配，使用 4 存储的前缀字符串对应的 location 。

`=`修饰符拥有最高的优先级。如果网站首页访问频繁，我们可以专门定义一个 location 来减少搜索匹配次数（因为搜索到`=`修饰的匹配的 location 将停止搜索），提高速度：

```conf
location = / {
    ...
}
```

#### try_files
检测指定的文件或者目录是否存在。如果不存在进行内部跳转或者返回一个指定的状态码。可以指定多个备选的跳转地址。

```conf
server {
    root /www/data;

    location /images/ {
        # 如果 uri 所请求的文件不存在
        # 就会内部重定向到 /www/data/images/default.gif 文件。
        try_files $uri /images/default.gif;
    }
    
    location / {
        # 第二个参数也可以是一个指定的状态码。
        try_files $uri $uri/ $uri.html =404;
    }
}
```

#### audoindex
开启或关闭列出该目录下的文件功能。

当`autoindex`关闭的时候，找不到指定的 index 文件会报 404。但是如果开启`autoindex on;`，则列出该目录下的文件列表。

#### proxy_pass
指定代理的地址，地址可以包含端口号。

* 如果代理的地址包含一个 uri(如下面例子中的`/link/`)，它将会替换请求的 uri 中匹配 location 参数的那部分。
* 如果代理的地址没有 uri，那么将整个 uri 传递过来。

```conf
location /some/path/ {
    # 如果请求是 /some/path/page.html，
    # uri则会被代理成：http://www.example.com/link/page.html。
    proxy_pass http://www.example.com/link/;
}

location ~ \.php {
    proxy_pass http://127.0.0.1:8000;
}
```

还可以将请求转到非 http 服务：

* `fastcgi_pass`   转到 FastCGI 服务器
* `uwsgi_pass`     转到 uwsgi 服务器
* `scgi_pass`      转到 scgi 服务器
* `memcached_pass` 转到 memcached 服务器

#### proxy_set_header
向目标服务器传递请求头。这个指令可以放到 location 或者更高的层级，可以在特定的 server context 或者 http 块中指定。

默认的，nginx 重新定义两个 header：`Host`和`Connection`，并且排除掉值为空的 header。`Host`的值是`$proxy_host`变量的值，`Connection`设置成`close`。

如果不想让某个 header 传给被代理的服务器，将其置空。

#### proxy_bind
指定出口 IP。代理服务器可能有多个 ip 地址，对于一个特定的被代理的 server 可以选择一个特定的 ip 地址。

ip 地址也可以使用变量，如`$server_addr`，指定的是接受请求的那个 ip。

#### return
返回特定状态码。return 指令可以包含在 location 和 server 上下文中。

return 的第一个参数是响应代码。可选的第二个参数可以是重定向（对应于代码301，302，303和307）的 URL 或在响应正文中返回的文本。 

比如，如果你的网站上的一些资源永久移除了，最快最简洁的方法就是使用 return 指令直接返回：

```conf
location /permanently/moved/url {
    return 301 http://www.example.com/moved/here;
}
```

#### error_page
`error_page`命令可以配置特定错误码的错误页面，或者重定向到其他的页面。可以包含在 location 和 server 上下文中。

下面的示例将在 404 错误发生时返回`/404.html`页面。

```conf
server {
    error_page 404 /404.html;
}
```

`error_page`命令定义了如何处理错误，因此不会直接返回，而`return`却会立即返回。当代理服务器或者 Nginx 处理时产生相应的错误的代码，均会返回相应的错误页面。

或者还可以直接内部重定向：

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


#### rewrite
`rewrite`指令可以多次修改请求的 URI。在 server 和 location 里面都可以使用。

`rewrite`共有三个参数：

- 第一个参数是 URI 需要匹配的正则表达式；
- 第二个参数是将要替换的 URI；
- 第三个参数是可选参数，指示是否继续可以重写或者返回重定向代码（301或302）。

比如：

```conf
location /users/ {
    rewrite ^/users/(.*)$ /show?user=$1 break;
}
```

可以在 server 和 location 上下文中包括多个`rewrite`指令。Nginx 按照它们发生的顺序一个一个地执行指令。当选择 server 时， server 中的`rewrite`指令将执行一次。

在 Nginx 处理一组`rewrite`指令之后，它根据新的 URI 选择 location。如果所选 location 仍旧包含 rewrite 指令，它们将依次执行。如果 URI 匹配所有的重写指令规则，则在处理完所有定义的`rewrite`指令后，搜索新的 location。

比如下面的几个示例：

```conf
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

第三个参数，`last`和`break`的区别如下：

* 两个值在 server context 中表现一致，跳过剩下的`rewrite`指令，进行 location 匹配。
* 在 location context 中，last 会跳出当前 location 寻找新的 location 匹配，而 break 不会再去匹配其他的 location，在当前 location 请求对应的 uri。

> 小心循环重定向，尽量在 server context 里面进行 url 重写。




### 变量
在配置 nginx 的时候可以使用变量，**变量以`$`开头**，变量在运行时得到对应的值，然后作为参数传给指令。有很多预定义的变量，如 core HTTP varabiles，可以用 set map 和 geo 指令来自定义变量。大多数的变量都是在运行时得到值并且包含与特定请求相关的信息。注意是特定请求不是 context。

一个简单的应用就是从 http 重定向到 https 时带上路径信息：

```conf
server {
    ...
    return   301 https://lufficc.com$request_uri;
    ...
}
```

在下面的示例中，当 Nginx 找不到页面时，它将使用代码 404 替换代码 301，并将客户端重定向到`http://example.com/new/path.html`。 此配置很有用，比如当客户端仍尝试用旧的 URI 访问页面时，301 代码通知浏览器页面已永久移除，并且需要自动替换为返回的新地址。

```conf
location /old/path.html {
    error_page 404 =301 http:/example.com/new/path.html;
}
```



