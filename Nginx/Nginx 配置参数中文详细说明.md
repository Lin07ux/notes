### 基本配置

```conf
# 定义 Nginx 运行的用户和用户组
user www www;

# nginx 进程数，建议设置为等于 CPU 总核心数
worker_processes 8;

# 一个 nginx 进程打开的最多文件描述符数目，理论值应该是最多打开文件数（系统的值 ulimit -n）与 nginx 进程数相除,但是 nginx 分配请求并不均匀，所以建议与 ulimit -n 的值保持一致
worker_rlimit_nofile 65535;

# 全局错误日志定义类型[ debug | info | notice | warn | error | crit ]
error_log /var/log/nginx/error.log info;

# 进程文件
pid /var/run/nginx.pid;

# 工作模式与连接数上限
events
{

    # 参考事件模型[ kqueue | rtsig | epoll | /dev/poll | select | poll ];
    # epoll 模型是 Linux 2.6 以上版本内核中的高性能网络I/O模型，如果跑在 FreeBSD 上面就用 kqueue 模型
    use epoll;

    # 单个进程最大连接数
    # 并发总数是 worker_processes 和 worker_connections 的乘积
    # 即 max_clients = worker_processes * worker_connections
    # 在设置了反向代理的情况下，max_clients = worker_processes * worker_connections / 4，除以 4 是一个经验值
    # 另外，worker_connections 值的设置跟物理内存大小有关
    # 因为并发受IO约束，max_clients的值须小于系统可以打开的最大文件数
    # 而系统可以打开的最大文件数和内存大小成正比，一般1GB内存的机器上可以打开的文件数大约是10万左右
    # 我们来看看360M内存的VPS可以打开的文件句柄数是多少：
    # $ cat /proc/sys/fs/file-max  输出 34336
    # 所以，worker_connections 的值需根据 worker_processes 进程数目和系统可以打开的最大文件总数进行适当地进行设置
    # 使得并发总数小于操作系统可以打开的最大文件数目，其实质也就是根据主机的物理 CPU 和内存进行配置
    # 当然，理论上的并发总数可能会和实际有所偏差，因为主机还有其他的工作进程需要消耗系统资源。
    worker_connections 65535;
    
}

# 设定 http 服务器
http {

    # 设定 mime 类型，类型由 mime.type 文件定义
    include mime.types;
    
    # 定义一个日志格式 main
    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
                      '$status $body_bytes_sent "$http_referer" '
                      '"$http_user_agent" "$http_x_forwarded_for"';

    # 默认文件类型
    default_type application/octet-stream;

    # 默认编码
    charset utf-8;

    # 服务器名字的 hash 表大小
    server_names_hash_bucket_size 128;

    # 上传文件大小限制
    client_header_buffer_size 32k;

    # 设定请求缓存大小
    large_client_header_buffers 4 64k;

    # 设置客户端请求体的最大大小
    client_max_body_size 8m;

    # 开启目录列表访问，适合下载服务器，默认关闭
    autoindex on;

    # 显示文件大小。默认为 on，显示出文件的确切大小，单位是 bytes
    # 改为 off 后，显示出文件的大概大小，单位是 kB 或者 MB 或者 GB
    autoindex_exact_size on;

    # 显示文件时间 默认为off,显示的文件时间为GMT时间 改为on后,显示的文件时间为文件的服务器时间
    autoindex_localtime on;

    # 开启高效文件传输模式
    # sendfile 指令指定 nginx 是否调用 sendfile 函数(zero copy 方式)来输出文件
    # 对于普通应用设为 on，如果用来进行下载等应用磁盘 IO 重负载应用，可设置为off，以平衡磁盘与网络 I/O 处理速度，降低系统的负载
    # 注意：如果图片显示不正常把这个改成 off
    sendfile on;

    # 防止网络阻塞
    tcp_nopush on;

    # 防止网络阻塞
    tcp_nodelay on;

    # 设置客户端连接保持活动的超时时间(单位 s)
    # 在超过这个时间后服务器会关闭该链接
    keepalive_timeout 120;

    # FastCGI 相关参数是为了改善网站的性能：减少资源占用，提高访问速度
    fastcgi_connect_timeout 300;
    fastcgi_send_timeout 300;
    fastcgi_read_timeout 300;
    fastcgi_buffer_size 64k;
    fastcgi_buffers 4 64k;
    fastcgi_busy_buffers_size 128k;
    fastcgi_temp_file_write_size 128k;

    # 开启 gzip 压缩输出，但是在 IE 6- 浏览器中关闭 gzip
    gzip on;
    gzip_disable "MSIE [1-6].";
    
    # 允许压缩的页面的最小字节数
    # 页面字节数从 header 中的 content-length 中获取
    # 默认是 0：不管页面多大都进行压缩
    # 建议设置成大于 1k 的字节数，小于 1k 可能会越压越大
    gzip_min_length 1k;

    # 表示申请 4 个单位为 16k 的内存作为压缩结果流缓存
    # 默认值是申请与原始数据大小相同的内存空间来存储 gzip 压缩结果
    gzip_buffers 4 16k;

    # 压缩版本（默认1.1，目前大部分浏览器已经支持 gzip 解压。前端如果是 squid2.5 请使用1.0）
    gzip_http_version 1.1;

    # 压缩等级
    # 1 压缩比最小，处理速度快；
    # 9 压缩比最大，比较消耗 cpu 资源，处理速度最慢，但是因为压缩比最大，所以包最小，传输速度快
    gzip_comp_level 2;

    # 压缩类型。默认就已经包含 text/html，所以下面就不用再写了
    # 写上去也不会有问题，但是会有一个 warn
    gzip_types text/plain application/x-javascript text/css application/xml;

    # 让前端的缓存服务器缓存经过 gzip 压缩的页面
    # 例如：用 squid 缓存经过 nginx 压缩的数据
    gzip_vary on;

    # 开启限制 IP 连接数的时候需要使用
    limit_zone crawler $binary_remote_addr 10m;

    #### upstream 的负载均衡四种调度算法(后面主讲) ####

    # 虚拟主机的配置
    server {

        # 监听端口
        listen 80;

        # 域名可以有多个，用空格隔开
        server_name wangying.sinaapp.com;
        
        # 定义服务器的默认网站根目录位置
        root /data/www/;

        # 定义首页索引文件的名称
        index index.html index.htm index.php;
        
        # 设定本虚拟主机的访问日志(使用的是上面定义的 main 日志格式)
        access_log logs/nginx.access.logmain;

        # PHP 脚本请求全部转发到 FastCGI 处理。使用 FastCGI 默认配置
        location ~ .*\.(php|php5)?$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include fastcgi.conf;
        }
        
        # 支持 PathInfo
        # 需要创建一个 pathinfo.conf 文件，内容如下：
        #   fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        #   set $path_info $fastcgi_path_info;
        #   fastcgi_param PATH_INFO       $path_info;
        #   try_files $fastcgi_script_name =404;
        # 另外，需要加载的是 fastcgi.conf 文件，而不是 fastcgi_params 文件
        # 注：当 nginx 和 php-fpm 在一个服务器上时，用 unix sock 比直接使用 IP 协议传输更快
        location ~ .+\.php(/|$) {
            # fastcgi_pass  127.0.0.1:9000;
            fastcgi_pass  unix:/tmp/php-cgi.sock;
            fastcgi_index index.php;
            include fastcgi.conf;
            include pathinfo.conf;
        }
        
        # 定义错误提示页面
        error_page   500 502 503 504 /50x.html;
        location = /50x.html {
        }

        # 静态文件，nginx 自己可以处理
        # 图片缓存时间设置
        location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
            # 过期30天，静态文件不怎么更新，过期可以设大一点，
            # 如果频繁更新，则可以设置得小一点。
            expires 30d;

            # 静态文件一般不需要写入访问日志中
            access_log off;
        }

        # JS 和 CSS 缓存时间设置
        location ~ .*\.(js|css)?$ {
            expires 1h;
            access_log off;
        }

        # 设定查看 Nginx 状态的地址
        # StubStatus 模块能够获取 Nginx 自上次启动以来的工作状态
        # 此模块非核心模块，需要在 Nginx 编译安装时手工指定才能使用
        location /NginxStatus {
            stub_status on;
            access_log on;
            auth_basic "NginxStatus";
            # htpasswd 文件的内容可以用 apache 提供的 htpasswd 工具来产生
            auth_basic_user_file conf/htpasswd;
        }

        # 禁止访问文件名为 .ht 开头的文件
        location ~ /.ht {
            deny all;
        }
    }
}
```


### Nginx 多台服务器实现负载均衡

```conf
events {
    use epoll;
    worker_connections 65535;
}

http {

    ## upstream 的负载均衡，四种调度算法 ##

    # 调度算法1：轮询
    # 每个请求按时间顺序逐一分配到不同的后端服务器，如果后端某台服务器宕机，故障系统被自动剔除，使用户访问不受影响
    upstream webhost {
        server 192.168.0.5:6666 ;
        server 192.168.0.7:6666 ;
    }

    # 调度算法2：weight(权重)
    # 可以根据机器配置定义权重，权重越高被分配到的几率越大
    upstream webhost {
        server 192.168.0.5:6666 weight=2;
        server 192.168.0.7:6666 weight=3;
    }

    # 调度算法3：ip_hash
    # 每个请求按访问 IP 的 hash 结果分配
    # 这样来自同一个 IP 的访客固定访问一个后端服务器，有效解决了动态网页存在的 session 共享问题
    upstream webhost {
        ip_hash;
        server 192.168.0.5:6666 ;
        server 192.168.0.7:6666 ;
    }

    # 调度算法4：url_hash(需安装第三方插件)
    # 此方法按访问 url 的 hash 结果来分配请求，使每个 url 定向到同一个后端服务器
    # 可以进一步提高后端缓存服务器的效率
    # Nginx 本身是不支持 url_hash 的，如果需要使用这种调度算法，必须安装 Nginx 的 hash 软件包
    upstream webhost {
        server 192.168.0.5:6666 ;
        server 192.168.0.7:6666 ;
        hash $request_uri;
    }

    # 调度算法5：fair(需安装第三方插件)。这是比上面两个更加智能的负载均衡算法。
    # 此种算法可以依据页面大小和加载时间长短智能地进行负载均衡
    # 也就是根据后端服务器的响应时间来分配请求，响应时间短的优先分配
    # Nginx 本身是不支持 fair 的，如果需要使用这种调度算法，必须下载 Nginx 的 upstream_fair 模块


    # 虚拟主机的配置(采用调度算法3:ip_hash)
    server {
        listen  80;
        server_name  mongo.demo.com;

        # 对 "/" 启用反向代理
        location / {
            proxy_pass http://webhost;
            proxy_redirect off;
            proxy_set_header X-Real-IP $remote_addr;
            
            # 后端的Web服务器可以通过X-Forwarded-For获取用户真实IP
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

            # 以下是一些反向代理的配置,可选.

            proxy_set_header Host $host;

            # 允许客户端请求的最大单文件字节数
            client_max_body_size 10m; 

            # 缓冲区代理缓冲用户端请求的最大字节数
            client_body_buffer_size 128k;

            # nginx跟后端服务器连接超时时间(代理连接超时)
            proxy_connect_timeout 90; 

            # 后端服务器数据回传时间(代理发送超时)
            proxy_send_timeout 90;

            # 连接成功后,后端服务器响应时间(代理接收超时)
            proxy_read_timeout 90;

            # 设置代理服务器（nginx）保存用户头信息的缓冲区大小
            proxy_buffer_size 4k;

            # proxy_buffers缓冲区,网页平均在32k以下的设置
            proxy_buffers 4 32k;

            # 高负荷下缓冲大小（proxy_buffers * 2）
            proxy_busy_buffers_size 64k;

            # 设定缓存文件夹大小，大于这个值将从 upstream 服务器传
            proxy_temp_file_write_size 64k;

        }
    }
}
```

### 转摘
[Nginx配置参数中文说明](http://wangying.sinaapp.com/archives/931)

