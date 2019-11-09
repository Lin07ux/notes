使用 Nginx 反向代理 NodeJS 而不是直接使用 NodeJS 服务器，可以降低对静态文件的处理负担，并实现多机负载均衡。

配置参考如下：

```conf
location @nodejs {
    proxy_http_version 1.1;
    proxy_set_header Host $host; # 为反向设置原请求头
    proxy_set_header X-Read-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Upgrade $http_upgrade; # 设置 WebSocket Upgrade
    proxy_set_header Connection "upgrade";
    proxy_pass http://localhost:3000;
}

location / {
    try_files $uri @nodejs;
}

location ~ \.(gif|png|jpg|css|js)$ {
    root /srv/http/www; # 静态文件的位置，例如 express 中的 public 目录
    try_files $uri @nodejs;
    expires 7d; # 设置静态文件 7 天过期
}
```

这里需要注意的地方有两点：

* `try_files`指令中没有`$uri/`
    
    因为如果加上`$uri/`之后，会显示 Nginx 的 404，而如要使用 NodeJS 中的 404 页面就不可添加`$uri/`。

* 静态文件的 location 作用域中也使用了`try_files $uri @nodejs;`指令

    因为如果使用了 Socket.IO 之类的 Node 库，那么它的 js/css 文件就需要从 Node 中获取，所以要判断如果找不到静态文件时就尝试去 Node 中获取。

