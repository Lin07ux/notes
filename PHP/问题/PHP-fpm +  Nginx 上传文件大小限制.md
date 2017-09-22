1. 首先修改`/etc/php.ini`文件

* `post_max_size = 50M`　　　　PHP可接受的最大POST数据
* `upload_max_filesize = 50M` 文件上传允许的最大值
* `max_execution_time = 300`　每个脚本的最大执行时间，秒钟（0则不限制，不建议设0）

可能还需要修改下面的几个参数：

* `max_input_time = 600`  每个PHP页面接收数据所需的最大时间，默认60秒，修改大一些，避免因网速问题无法正常上传完成
* `memory_limit = 128M`     每个PHP页面所吃掉的最大内存，默认8M，可以适当增大

2. 修改 Nginx 配置文件`/etc/nginx/nginx.conf`

* `client_max_body_size 50m;` 设置客户端提交的信息体的最大值为50M(默认2M)，或者适当增大一些，避免客户端上传时除了文件还要附带其他的信息。这个参数可以设置在 location、server 和 http 块中。
 

