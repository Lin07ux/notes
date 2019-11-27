### 多个域名的配置

Nginx 中的`server_name`指令主要用于配置基于名称虚拟主机。同一个 Nginx 虚拟主机中，可以绑定多个server_name，各个域名用空格隔开即可。如下：

```conf
server
{
    listen       80;
    server_name  test.com www.test.com;
    ...
}
```

> 如果`server_name`有多个，那么通过代码(如`$_SERVER["SERVER_NAME"]`)获取的始终将是 Nginx `server_name`配置中的第一个域名，第一个域名就相当于 Apache 虚拟主机配置中的 ServerName，后面的域名就相当于 Apache 的 ServerAlias。

### 域名的匹配顺序

如果 Nginx 中有多个 server 块，那么请求匹配`server_name`的顺序如下：

1. 首先匹配准确的`server_name`，如：`server_name  test.com www.test.com;`。
2. 然后匹配以`*`通配符开始的`server_name`，如：`server_name  *.test.com;`。
3. 然后匹配以`*`通配符结束的`server_name`，如：`server_name  www.test.*;`。
4. 最后匹配正则表达式形式的`server_name`，如：`server_name ~^(?<www>.+)\.test\.com$;`。

以上只要有一项匹配到以后就会停止搜索。


