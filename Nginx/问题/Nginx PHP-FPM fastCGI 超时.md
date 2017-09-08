### 问题

Nginx 通过 PHP-FPM 这个 fastCGI 接口来转发执行 PHP 程序。但是如果 FastCGI 接口长时间没有返回的时候，就会造成超时，导致 Nginx 直接返回 404 错误。

### 原因

这是由于 Nginx 对 FastCGI 接口有默认的等待时间(60s)的，当 Nginx 将请求转发给 PHP-FPM 执行的时候，会等待一定的时间，如果在这个时间内，PHP 有返回数据，那么就一切正常，否则就会出现 Nginx 等待超时，然后就关闭与 PHP-FPM 的链接，直接返回 404 了。

### 解决

在 Nginx 的转发到 FastCGI 的配置中，增加`fastcgi_read_timeout`指令，设置一个较长的时间来解决该问题。如下：

```conf
location ~ .*\.(php|php5)?$ {
  fastcgi_pass 127.0.0.1:9000;
  fastcgi_read_timeout 700;
  fastcgi_index index.php;
  include fastcgi.conf;
}
```


