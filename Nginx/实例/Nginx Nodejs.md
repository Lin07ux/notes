使用 Nginx 反向代理 NodeJS 而不是直接使用 NodeJS 服务器，可以降低对静态文件的处理负担，并实现多机负载均衡。

配置参考如下：

```conf
location @nodejs {
    proxy_http_version 1.1;
    proxy_set_header Host $host; # 为反向设置原请求头
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Upgrade $http_upgrade; # 设置 WebSocket Upgrade
    proxy_set_header Connection "upgrade";
    proxy_pass http://localhost:3000;
}

location / {
    try_files $uri $uri/ @nodejs;
}

location ~ \.(gif|png|jpg|css|js)$ {
    root /srv/http/www; # 静态文件的位置，例如 express 中的 public 目录
    try_files $uri @nodejs;
    expires 7d; # 设置静态文件 7 天过期
}
```

这里需要注意：静态文件的`location`作用域中也使用了`try_files $uri @nodejs;`指令。因为如果使用了 Socket.IO 之类的 Node 库，那么它的 js/css 文件就需要从 Node 中获取，所以要判断如果找不到静态文件时就尝试去 Node 中获取。

另外，对于`location /`区块，由于`try_files`中使用了`$uri/`，所以会先在目录中查找对应的默认文件(由`index`指令设置，一般是`index.html`一类的)，如果找不到会再到代理中查找。但是当使用 Socket.IO 时，由于默认情况 Socket.IO 会使用`POST`来请求一个目录，如果刚好这个目录有默认的静态文件(如`index.html`)，那么 Nginx 会因 POST 请求静态文件而产生 405 错误，此时就不可用在`try_files`指令中使用`$uri/`了。

