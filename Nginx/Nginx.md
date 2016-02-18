
## 安装和运行
### 安装
在 Windows 下，下载好了相应的压缩包之后，解压即可使用。

## 运行命令
Nginx 提供了几个命令来控制 Nginx 的启停：

- 启动：`start nginx`

- 停止：`nginx -s stop`

- 重载：`nginx -s reload`。这个命令一般用在修改了 Nginx 配置文件之后，无中断的重启 Nginx 服务。

- 退出：`nginx -s quit`。这个命令会彻底退出 Nginx 服务。

> 在 Windows 系统中，上述命令需要在 Nginx 的安装目录下运行。
如果添加 Nginx 安装目录到系统路径下了，就可以在任意地方运行。


## 配置示例
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
效果：
	静态文件拦截器，将以images/js/img/css…开头的地址映射到网站目录，由ngnix直接提供服务

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


