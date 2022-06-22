> 转摘：[cgi.fix_pathinfo 漏洞](https://segmentfault.com/a/1190000022499679)

### 1. 漏洞说明

PHP 在配置了`cgi.fix_pathino = 1`的时候，存在一个潜在的漏洞：当请求的路径不是一个可执行脚本时，cgi 会尝试修正可执行脚本路径，从而会导致在一些特殊情况下执行到了非服务器代码的问题。

假设 Nginx 对 PHP 脚本的配置如下：

```conf
location ~ [^/]\.php(/|$) {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    include fastcgi.conf;
    include pathinfo.conf;
}
```

此时，用户上传了一张把`index.php`改名为`index.jpg`的虚假图片，假设存储位置为`/uploads/index.jpg`。

然后用户访问`/uploads/index.jpg/hack.php/your/site`的时候，因为`hack.php`的存在，使其能命中上面给出的 location 规则。

此时，Nginx 通过`fastcgi_split_path_info ^(.+?\.php)(/.+)$;`解析得到的 script_name 和 pathinfo 分别是：

```
SCRIPT_NAME = /uploads/index.jpg/hack.php
PATH_INFO = /your/site
```

fastcgi 将这个`SCRIPT_NAME`传递给 PHP 后，PHP 发现其并非有效的脚本文件。由于`cgi.fix_pathinfo = 1`的配置，PHP 会尝试自动修正 pathinfo，尝试过程如下：

1. 将`/uploads/index.jpg/hack.php`分割成三段：`/uploads`、`/index.jpg`、`/hack.php`；
2. 由于`/uploads`是个目录，不是文件，更不是脚本，跳过；
3. `/uploads/index.jpg`是个问题，而且由于其本质是一个 PHP 脚本文件，是可执行的脚本文件，那么后面的就都是 pathinfo 了；
4. 然后 PHP 就会开始执行`/uploads/index.jpg`脚本，从而执行了用户传入的恶意代码。

### 2. 修复漏洞

除了做好上传文件的检测和限制外，这种路径寻址在 Nginx 层可以很方便的拦截掉。

由于`$request_filename`最终会指向一个确定的资源路径，可以把其中的 PHP 脚本文件名提取出来，并判断其是否存在，不存在就直接返回即可：

```conf
location ~ [^/].php(/|$) {
    if ($request_filename ~* (.*\.php)) {
        set $php_script_name $1;
        if (!-e $php_script_name) {
            return 404;
        }
    }
    ...
}
```

或者可以定义专门的 location 规则来保护上传目录：

```conf
location ~* /uploads/(.*\.php)([/|$]) {
    return 404;
}
```


