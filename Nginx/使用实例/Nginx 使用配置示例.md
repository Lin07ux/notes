### 1、分流代理
效果：
	将不同的访问方式分配给不同的服务。
	输入http://192.168.0.101, http://localhost会看到不同的结果

配置：

```conf
server {
    listen       80;
    server_name  localhost;

    location / {
        proxy_pass http://node_app;
    }
}

# static server
server {
    listen       80;
    server_name  192.168.0.101;

    location / {
        root   D:\GitHub\areu\web;
        index  home.html;
    }
}
```

### 2、静态文件拦截器
效果：静态文件拦截器，将以`images/js/img/css...`开头的地址映射到网站目录，由 ngnix 直接提供服务。

配置：

```conf
http {
    ...
    server {
        ...
        location ~ ^/(images/|img/|javascript/|js/|css/|stylesheets/|flash/|media/|static/|robots.txt|humans.txt|favicon.ico) {
          root /usr/local/silly_face_society/node/public;
          access_log off;
          expires max;
        }
        ...
    }
}
```

### 3、设置缓存
配置：

```conf
http {
    ...
    proxy_cache_path  /var/cache/nginx levels=1:2 keys_zone=one:8m max_size=3000m inactive=600m;
    proxy_temp_path   /var/tmp;
    ...
}
```

### 4、设置Gzip压缩
配置：

```conf
http {
    ...
    gzip on;
    gzip_comp_level 6;
    gzip_vary on;
    gzip_min_length  1000;
    gzip_proxied any;
    gzip_types text/plain text/html text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript;
    gzip_buffers 16 8k;
    ...
}
```

### 5、返回 404
如果对某个文件或者文件夹禁止访问，可以使用`deny all;`返回 403 禁止访问状态码，也可以使用`return 404;`返回 404 文件不存在状态码。

> 更好的建议是，将这个文件或者文件夹放在网站目录之外。

### 6、代理转发
下面的配置可以将`domain.com/app`的访问转发到`http://192.168.10.38:3000/app`进行处理。

```conf
server
{
    listen 80;
    server_name domain.com;
    
    location /app {
        proxy_redirect off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_pass http://192.168.10.38:3000/app;
    }
    
    access_log logs/domain.com.access.log;
}
```

### 7、部署 ThinkPHP
fastcgi 模块自带了一个`fastcgi_split_path_info`指令，这个指令根据给定的正则表达式来分割 URL，从而提取出脚本名和 path info 信息。

另外，`try_files`指令可以用来判断请求的文件是否存在于服务器上。在`try_files`指令中使用的是`$request_uri`而不是`$uri`，是因为前者可以将请求的 uri 中`?`后的查询字段也都传递过去，后者则不行。

配置如下：

```conf
server {
	...
	location / {
		index index.php index.html index.htm;

		# 如果文件不存在则尝试使用 ThinkPHP 的方式进行解析
		try_files  $request_uri  /index.php$request_uri;
	}

	location ~ .+\.php($|/) {
		root           /www/html/website;
		fastcgi_pass   127.0.0.1:9000;
		fastcgi_index  index.php;

		# 设置PATH_INFO
        # 注意 fastcgi_split_path_info 已经自动改写了 fastcgi_script_name 变量
        # 后面不需要再改写 SCRIPT_FILENAME，SCRIPT_NAME 环境变量
        # 所以必须在加载 fastcgi.conf 之前设置
        fastcgi_split_path_info  ^(.+\.php)(/.*)$;
        fastcgi_param  PATH_INFO  $fastcgi_path_info;

        # 加载 Nginx 默认的“服务器环境变量”配置文件
        include  fastcgi.conf;
	}
}
```

### 8、rewrite 重写访问
比如，网站的 Api 放在根目录下，但是需要将对 Api 的访问转向到统一的入口文件上进行处理。

```conf
location ^~ /api/ {
   rewrite ^\/api\/(.*)$ /api.php/$1 last;

   # 如果要有 api 目录，并且其下有可以直接访问的文件，可以如下配置
   # if (!-e $request_filename){
   #   rewrite ^\/api\/(.*)$ /api.php/$1 last;
   # }
}
```

### 9、跨域配置

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

参考：[CORS on Nginx](https://enable-cors.org/server_nginx.html)

### 10. 将无 www 的访问跳转到 www 子域名
当需要将无子域名的访问都跳转到 www 子域名上时，可以在 server 块中使用如下的配置：

```conf
if ($host = 'domain.com') {
   return 301 http://www.$host$request_uri;
}
```


