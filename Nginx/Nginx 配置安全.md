### $uri 导致的 CRLF 注入漏洞

Nginx 中很常见的一个配置是使访问从`http`转到`https`，或者将访问转到`www.domain.com`域名。在实现跳转的过程中，我们需要保证用户访问的页面不变，所以需要从 Nginx 获取用户请求的文件路径。查看 Nginx 文档，可以发现有三个表示 uri 的变量：

* `$uri`
* `$document_uri`
* `$request_uri`

1 和 2 表示的是解码以后的请求路径，不带参数；3 表示的是完整的 URI（没有解码）。那么，如果运维配置了下列的代码：

```conf
location / {
    return 302 https://$host$uri;
}
```

因为`$uri`是解码以后的请求路径，所以可能就会包含换行符，也就造成了一个 CRLF 注入漏洞。（关于 CRLF 注入漏洞，可以参考[这篇文章](https://www.leavesongs.com/PENETRATION/Sina-CRLF-Injection.html)。）

而修复这个 CRLF 漏洞就只需要将`$uri`改成`$request_uri`即可：

```conf
location / {
    return 302 https://$host$request_uri;
}
```

### alias 路径漏洞

Nginx 做反向代理时，动态的部分被`proxy_pass`传递给后端端口，而静态文件需要 Nginx 来处理。

假设静态文件存储在`/home/`目录下，而该目录在 url 中名字为`files`，那么就需要用`alias`设置目录的别名：

```conf
location /files {
    alias /home/;
}
```

此时，访问`http://example.com/files/readme.txt`，就可以获取`/home/readme.txt`文件。

但我们注意到，url 上`/files`没有加后缀`/`，而 alias 设置的`/home/`是有后缀`/`的，这个`/`就导致我们可以从`/home/`目录穿越到他的上层目录：当访问的是`http://example.com/files../`的时候，就会得到`/home/../`这个目录的信息，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1497578867340.png)

解决这个漏洞只需要保证`location`和`alias`的值都有或都没有后缀`/`。

### 转摘

[三个案例看Nginx配置安全](https://www.leavesongs.com/PENETRATION/nginx-insecure-configuration.html)

