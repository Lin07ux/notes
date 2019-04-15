### 1、分流代理

将不同的访问方式分配给不同的服务。如下例所示，输入`http://192.168.0.101`和`http://localhost`会看到不同的结果：

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
        root   /usr/local/web/public;
        index  home.html;
    }
}
```

### 2、静态文件拦截器

将以`images/js/img/css...`开头的地址映射到网站目录，由 ngnix 直接提供服务：

```conf
http {
    ...
    server {
        ...
        location ~ ^/(images/|img/|javascript/|js/|css/|stylesheets/|flash/|media/|static/|robots.txt|humans.txt|favicon.ico) {
          root /usr/local/web/public;
          access_log off;
          expires max;
        }
        ...
    }
}
```

### 3、设置缓存

```conf
http {
    ...
    proxy_cache_path  /var/cache/nginx levels=1:2 keys_zone=one:8m max_size=3000m inactive=600m;
    proxy_temp_path   /var/tmp;
    ...
}
```

### 4、设置 Gzip 压缩

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

### 5、返回状态码

可以在一些情况下，直接返回状态码，从而表示立即结束请求，并返回指定状态码的响应。

比如，对某个文件或者文件夹禁止访问，可以使用`deny all;`返回 403 禁止访问状态码，也可以使用`return 404;`返回 404 文件不存在状态码。

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

### 9. 将无 www 的访问跳转到 www 子域名

当需要将无子域名的访问都跳转到 www 子域名上时，可以在 server 块中使用如下的配置：

```conf
if ($host = 'domain.com') {
   return 301 http://www.$host$request_uri;
}
```

### 10. 按天生成日志文件

可以使用`map`定义一个时间结构，并且在`access_log`的配置名中加上这个结构，类似下面这样：

```conf
# nginx.conf
map $time_iso8601 $logdate {
    '~^(?\d{4}-\d{2}-\d{2})' $ymd; default 'nodate'; 
}

accesslog '/var/log/nginx/access${logdate}.log'
```



