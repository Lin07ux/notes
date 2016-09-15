目前由于前后端分离，后端 API 接口化，就会经常出现跨域访问的问题，下面提供几个 Nginx 允许跨域访问的配置：

```conf
upstream service {
    server 127.0.0.1:8080;
}

# 将需要跨域的域名或者IP解析出来，方便后面的配置处理。
map $http_origin $cors_header {
    default "";
    "~^https?://localhost(:[0-9]+)?$" "$http_origin";
}

server {
    listen 80;
    server_name 127.0.0.1;

    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    location = /favicon.ico {
        deny all;
        error_log off;
        access_log off;
        log_not_found off;
    }

    location /api/ {
        add_header 'Access-Control-Allow-Origin' '$cors_header' always;
        add_header 'Access-Control-Allow-Credentials' 'true' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
        # X-AUTH-USER, X-AUTH-TOKEN，是API中传递的自定义 HEADER
        add_header 'Access-Control-Allow-Headers' 'Origin,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Accept,Cookie,Set-Cookie, X-AUTH-USER, X-AUTH-TOKEN' always;
        
        if ($request_method = 'OPTIONS') {
            add_header 'Content-Length' 0 always;
            add_header 'Content-Type' 'text/plain charset=UTF-8' always;

            return 200;
        }

        uwsgi_pass service;
        include uwsgi_params;
    }
}
```



