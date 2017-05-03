
1、编辑 nginx.conf 文件，在 http 模块添加`server_tokens`指令，并配置为 off。

```shell
vim /etc/nginx/nginx.conf

http {
	...
    server_tokens off;
	...
}
```

2、编辑 nginx 的 php-fpm 配置文件，如 fastcgi_params.conf 文件，
修改`SERVER_SOFTWARE`指令的值为 nginx 即可。

```conf
# fastcgi_param SERVER_SOFTWARE nginx/$nginx_version;
fastcgi_param SERVER_SOFTWARE nginx;
```

3、重新 reload nginx 之后即可生效。
