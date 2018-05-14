### 问题

如何查看安装的 Nginx 是否支持 HTTP 2.0？如果支持，如何使网站使用 http 2.0？

### 解决

**1. 查看是否支持**

使用`nginx -V`命令，查看 Nginx 的详细配置信息。在显示的信息中，查看是否有`--with-http_v2_module`，如果有则说明是支持的。

**2. 启用 http 2.0**

由于 HTTP 2.0 需要使用 HTTPS 协议，所以要先确保服务器已经配置了 SSL 证书。而且 HTTP 2.0 在客户端不支持的时候，会自动降级到 HTTP 1.1，所以只需要在服务器中进行配置即可。

参考配置如下：

```conf
server {
    # listen 80;
    listen  443 ssl http2;
    #....
}
```

配置好之后，重启 Nginx 即可。

然后在谷歌浏览器上打开你使用 http2 的站点，打开调试工具(F12)，进入 Network，刷新页面，点击第一个请求，在`Headers --> Response Headers`中查看。如果 Response Headers 右边有一个`view source`，点进去就能看到 http 版本，如果没有，说明是 http2 ，因为 http2 是采用二进制传输。

