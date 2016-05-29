Nginx 可以将 php 文件请求传给 php-fpm 处理，有两种方式传递：

- 通过 IP 传递，如`fastcgi_pass: 127.0.0.1:9000`。这样可以做分布式服务器；
- 通过 sock 传递，如`fastcgi_pass: unix:/var/run/php-fpm/php-cgi.sock`。

当 Nginx 和 php-fpm 在同一个服务器上的时候，使用 sock 协议传递更有效率，但是此时则需要注意 php-fpm 的 sock 文件的权限问题。

一般情况，需要将 php-fpm 的 sock 文件的用户及群组设置为与 Nginx 相同。

编辑`/etc/php-fpm.d/www.conf`文件，将其中的下面两行取消注释，并填写 Nginx 配置中的用户和群组名：

```conf
listen.owner = nobody
listen.group = nobody
```

比如，如果 Nginx 设置的运行用户和组是：www:www，那么，在 php-fpm 的配置文件中，就需要设置 listen 的用户和组也为 www：

```conf
listen.owner = www
listen.group = www
```

> What I ended up doing is adding the following lines to my PHP-FPM configuration file.
> 
> ```conf
> listen.owner = www-data
> listen.group = www-data
> ```
> Make sure that www-data is actually the user the nginx worker is running as. For debian it's www-data by default.

