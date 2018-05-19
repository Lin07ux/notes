当使用 Nginx 配置 PHP 服务器的时候，需要使用到 FastCGI，此时就需要对 FastCGI 进行相关的配置。

在 FastCGI 的配置中，有一个`fastcgi_index`参数，该参数和常规的`index`参数有什么关系呢？

常规的`index`参数就是表示当访问的 URI 没有指定特定的文件名的时候，会默认使用该路径下的由`index`参数指定的文件作为响应。

对于`fastcgi_index`，可以使用在`http`、`server`、`location`配置块中，如果请求的 URI 以斜线(`/`)结尾，文件名将追加到 URI 后面，这个值将存储在变量`$fastcgi_script_name`中。

比如，对于如下的配置：

```conf
fastcgi_index  index.php;
fastcgi_param  SCRIPT_FILENAME  /home/www/scripts/php$fastcgi_script_name;
```

请求`/page.php`的时候，`SCRIPT_FILENAME`将被设置为`/home/www/scripts/php/page.php`，但是请求`/`时，其值则为`/home/www/scripts/php/index.php`。



