
fastcgi 模块自带了一个`fastcgi_split_path_info`指令，
这个指令根据给定的正则表达式来分割 URL，从而提取出脚本名和 path info 信息。

另外，`try_files`指令可以用来判断请求的文件是否存在于服务器上。

配置如下：

```conf
server {
	...
	location / {
		index index.php index.html index.htm;

		# 如果文件不存在则尝试使用 ThinkPHP 的方式进行解析
		try_files  $uri  /index.php$uri;
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

