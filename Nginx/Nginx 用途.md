
Nginx 这个轻量级、高性能的 web server 主要可以做两件事：

- 直接作为 http server(代替 Apache，对 PHP 网站需要传递请求给 FastCGI 处理)；

- 作为反向代理服务器实现负载均衡。

Nginx 的优势在于处理并发请求上。虽然 Apache 的`mod_proxy`和`mod_cache`结合使用也可以实现对多台 app server 的反向代理和负载均衡，但是在并发处理方面 Apache还是没有 Nginx 擅长。


### 反向代理
- 1. **环境搭建**

a. 我们本地是 Windows 系统，然后使用 VirutalBox 安装一个虚拟的 Linux 系统。
在本地的 Windows 系统上分别安装 nginx(侦听80端口) 和 apache(侦听80端口)。在虚拟的 Linux 系统上安装 apache(侦听80端口)。
这样我们相当于拥有了 1 台 nginx 在前端作为反向代理服务器；后面有 2 台 apache 作为应用程序服务器(可以看作是小型的 server cluster。；

b. nginx 用来作为反向代理服务器，放置到两台 apache 之前，作为用户访问的入口；
nginx 仅仅处理静态页面，动态的页面(php请求)统统都交付给后台的两台 apache 来处理。
也就是说，可以把我们网站的静态页面或者文件放置到 nginx 的目录下；动态的页面和数据库访问都保留到后台的 apache 服务器上。

c. 我们假设前端 nginx(为127.0.0.1:80) 仅仅包含一个静态页面 index.html；
后台的两个 apache 服务器(分别为 localhost:80 和 158.37.70.143:80)，一台根目录放置 phpMyAdmin 文件夹和 test.php(里面测试代码为print “server1“;)，另一台根目录仅仅放置一个 test.php(里面测试代码为 print “server2“;)。


- 2. **三种实现 server cluster 的负载均衡的方法**

a. 在最简单地构建反向代理的时候 (nginx 仅仅处理静态不处理动态内容，动态内容交给后台的 apache server 来处理)，我们具体的设置为：

在nginx.conf中修改配置，代码如下:

```
location ~ \.php$ {
	proxy_pass 158.37.70.143:80 ;
}
```

这样当客户端访问 127.0.0.1:80/index.html的时候，前端的nginx会自动进行响应；

当用户访问 127.0.0.1:80/test.php 的时候(这个时候 nginx 目录下根本就没有该文件)，通过上面的设置`location ~ \.php$`(表示正则表达式匹配以.php结尾的文件，详情参看 [location 是如何定义和匹配的](http://wiki.nginx.org/NginxHttpCoreModule))，nginx服务器会自动 pass 给  158.37.70.143 上的 apache 服务器了。该服务器下的 test.php 就会被自动解析，然后将 html 的结果页面返回给 nginx，然后 nginx 进行显示(如果 nginx 使用 memcached 模块或者 squid，还可以支持缓存)，输出结果为打印 server2。

b. 反向代理和负载均衡(不同页面)

现在对上面的例子做扩展，使其支持上述环境中配置的两台服务器。

设置 nginx.conf 的 server 模块部分，将对应部分修改为下面的代码：

```
location ^~ /phpMyAdmin/ {
　　proxy_pass localhost:80;
}
location ~ \.php$ {
　　proxy_pass 158.37.70.143:80 ;
}
```

上面第一个部分`location ^~ /phpMyAdmin/`，表示不使用正则表达式匹配(`^~`)，而是直接匹配，也就是如果客户端访问的 URL 是以 http://127.0.0.1:80/phpMyAdmin/ 开头的话(本地的 nginx 目录下根本没有 phpMyAdmin 目录)，nginx 会自动 pass 到 localhost:80  的 Apache 服务器，该服务器对 phpMyAdmin 目录下的页面进行解析，然后将结果发送给 nginx，后者进行显示；

如果客户端访问 URL 是 http://127.0.0.1/test.php 的话，则会被 pass 到 158.37.70.143:80 的 apache 服务器进行处理。

因此综上，我们实现了针对不同请求的负载均衡：

(1) 如果用户访问静态页面 index.html，最前端的 nginx 直接进行响应；
(2) 如果用户访问 test.php 页面的话，158.37.70.143:80 的 Apache 进行响应；
(3) 如果用户访问目录 phpMyAdmin 下的页面的话，localhost:80 的 Apache 进行响应；


c. 反向代理和负载均衡(相同页面)

也就是，当用户访问 127.0.0.1:80/test.php 页面时，需要实现两台服务器的负载均衡。
在实际情况中，这两个服务器上的数据要求同步一致，在这里我们分别定义了打印 server1 和 server2 是为了进行辨认区别。

首先，在 Nginx 的配置文件 nginx.conf 的 http 模块中添加服务器集群 server cluster，代码如下：

```
upstream myCluster {
	server localhost:80;
	server 158.37.70.143:80;
}
```
这段代码表示，这个服务器集群 server cluster 包含两台服务器，并分别指定了集群中每个服务器的地址和端口。

然后，在 server 模块中定义负载均衡，代码如下：

```
location ~ \.php$ {
	proxy_pass http://myCluster;   # 这里的名字和上面定义的 server cluster 的名字相同
	proxy_redirect off;
	proxy_set_header Host $host;
	proxy_set_header X-Real-IP $remote_addr;
	proxy_set_header X-Forwarded-For $proxy_add_x_forwaded_for;
}
```
这样的话，如果访问 http://127.0.0.1/test.php 的话，Nginx 目录下根本没有该文件，但是会自动将该请求 pass 到 myCluster 定义的服务器集群中，分别由 localhost:80 或者 158.37.70.143:80 两个服务器来处理。

因为上面在定义 upstream 的时候每个 server 之后没有定义权重，表示两者均衡获得的流量。
如果希望某个服务器获得更多的响应的话，可以为其定义个权重，如下代码所示：

```
upstream myCluster {
	server localhost:80 weight=5;
	server 158.37.70.143:80;
}
```
这样就表示 5/6 的几率会访问 localhost:80 这个服务器，而 1/6 的机会访问另一个服务器。

另外还可以定义`max_fails`和`fail_timeout`等参数。


### 总结
综上，我们使用 Nginx 的反向代理功能，将访问量分配到多台 Apache server 的前端。
Nginx 仅仅用来处理静态文件的响应，和动态请求的代理 pass，后台的 Apache server 作为 app server 来对前台 pass 过来的动态页面进行处理并返回给 Nginx。

通过以上的架构，我们可以实现 Nginx server 和多台 Apache server 构成的机群 cluster 的负载均衡。

两种均衡方式：

　　1) 可以在 Nginx 中定义访问不同的内容，代理到不同的后台 server；如上例子中的访问 phpMyAdmin 目录代理到第一台 server上；访问 test.php 代理到第二台 server 上；

　　2) 可以在 Nginx中定义访问同一页面，均衡(当然如果服务器性能不同可以定义权重来均衡)地代理到不同的后台 server 上。如上的例子访问 test.php 页面，会均衡地代理到 server1 或者 server2 上。
　　实际应用中，server1 和 server2 上保留相同的 app 程序和数据，需要考虑两者的数据同步。














