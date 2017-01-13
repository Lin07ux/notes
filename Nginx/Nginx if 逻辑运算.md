Nginx 的配置中不支持 if 条件的逻辑与 &&、逻辑或 || 等逻辑运算符，而且不支持 if 的嵌套语法，否则会报错。

为了实现多重条件的判定，可以使用中间变量的方式进行中转。

比如，为了实现非 www 子域名访问，而且是访问后台(`admin.php`为标志)的则重定向到 www 子域名：

```conf
set $flag 0;
if ($host = 'domain.com') {
   set $flag '${flag}1';
}

if ($uri ~* /admin.php(/|$)) {
   set $flag '${flag}1';
}

if ($flag = '011') {
   return 301 http://www.$host$request_uri;
}
```

转摘：[nginx: if逻辑运算 （与或非） 实现](https://www.iteblog.com/archives/1243.html)


