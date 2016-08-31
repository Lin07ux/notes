### 基本配置

```conf
# 定义 Nginx 运行的用户和用户组
user www www;

# nginx 进程数，建议设置为等于 CPU 总核心数
worker_processes 8;

# 全局错误日志定义类型,[ debug | info | notice | warn | error | crit ]
error_log /var/log/nginx/error.log info;


#进程文件
pid /var/run/nginx.pid;


# 一个 nginx 进程打开的最多文件描述符数目,理论值应该是最多打开文件数（系统的值 ulimit -n）与 nginx 进程数相除,但是 nginx 分配请求并不均匀,所以建议与 ulimit -n 的值保持一致.
worker_rlimit_nofile 65535;

# 工作模式与连接数上限
events
{

    # 参考事件模型,use [ kqueue | rtsig | epoll | /dev/poll | select | poll ]; epoll模型是Linux 2.6以上版本内核中的高性能网络I/O模型,如果跑在FreeBSD上面,就用kqueue模型.
    use epoll;

    # 单个进程最大连接数（最大连接数=连接数*进程数）
    worker_connections 65535;

}

# 设定http服务器
http {

    include mime.types; # 文件扩展名与文件类型映射表

    default_type application/octet-stream; # 默认文件类型

    charset utf-8; # 默认编码

    server_names_hash_bucket_size 128; # 服务器名字的 hash 表大小

    client_header_buffer_size 32k; # 上传文件大小限制

    large_client_header_buffers 4 64k; # 设定请求缓

    client_max_body_size 8m; # 设定请求缓


    # 开启目录列表访问,合适下载服务器,默认关闭.
    autoindex on;

    # 显示文件大小 默认为on,显示出文件的确切大小,单位是bytes 改为off后,显示出文件的大概大小,单位是kB或者MB或者GB
    autoindex_exact_size on;

    # 显示文件时间 默认为off,显示的文件时间为GMT时间 改为on后,显示的文件时间为文件的服务器时间
    autoindex_localtime on;

    #开启高效文件传输模式,sendfile指令指定nginx是否调用sendfile函数来输出文件,对于普通应用设为 on,如果用来进行下载等应用磁盘IO重负载应用,可设置为off,以平衡磁盘与网络I/O处理速度,降低系统的负载.注意：如果图片显示不正常把这个改成off.
    sendfile on;

    tcp_nopush on; # 防止网络阻塞

    tcp_nodelay on; # 防止网络阻塞

    # (单位s)设置客户端连接保持活动的超时时间,在超过这个时间后服务器会关闭该链接
    keepalive_timeout 120;

    # FastCGI相关参数是为了改善网站的性能：减少资源占用,提高访问速度
    fastcgi_connect_timeout 300;

    fastcgi_send_timeout 300;

    fastcgi_read_timeout 300;

    fastcgi_buffer_size 64k;

    fastcgi_buffers 4 64k;

    fastcgi_busy_buffers_size 128k;

    fastcgi_temp_file_write_size 128k;

    # gzip模块设置
    # 开启gzip压缩输出
    gzip on;
    
    # 允许压缩的页面的最小字节数,页面字节数从header偷得content-length中获取.默认是0,不管页面多大都进行压缩.建议设置成大于1k的字节数,小于1k可能会越压越大
    gzip_min_length 1k;

    # 表示申请4个单位为16k的内存作为压缩结果流缓存,默认值是申请与原始数据大小相同的内存空间来存储gzip压缩结果
    gzip_buffers 4 16k;

    # 压缩版本（默认1.1,目前大部分浏览器已经支持gzip解压.前端如果是squid2.5请使用1.0）
    gzip_http_version 1.1;

    # 压缩等级.1压缩比最小,处理速度快.9压缩比最大,比较消耗cpu资源,处理速度最慢,但是因为压缩比最大,所以包最小,传输速度快
    gzip_comp_level 2;

    # 压缩类型,默认就已经包含text/html,所以下面就不用再写了,写上去也不会有问题,但是会有一个warn.
    gzip_types text/plain application/x-javascript text/css application/xml;

    # 选项可以让前端的缓存服务器缓存经过gzip压缩的页面.例如:用squid缓存经过nginx压缩的数据
    gzip_vary on;

    # 开启限制IP连接数的时候需要使用
    limit_zone crawler $binary_remote_addr 10m;

    ##upstream的负载均衡,四种调度算法(下例主讲)##

    #虚拟主机的配置
    server {

        # 监听端口
        listen 80;

        # 域名可以有多个,用空格隔开
        server_name wangying.sinaapp.com;

        index index.html index.htm index.php;

        root /data/www/;

        location ~ .*\.(php|php5)?$ {

            fastcgi_pass 127.0.0.1:9000;

            fastcgi_index index.php;

            include fastcgi.conf;

        }

        # 图片缓存时间设置
        location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$ {
            expires 10d;
        }

        # JS 和 CSS 缓存时间设置
        location ~ .*\.(js|css)?$ {
            expires 1h;
        }
        

        #日志格式设定

        log_format access '$remote_addr - $remote_user [$time_local] "$request" '

        '$status $body_bytes_sent "$http_referer" '

        '"$http_user_agent" $http_x_forwarded_for';

        # 定义本虚拟主机的访问日志
        access_log /var/log/nginx/access.log access;

        # 设定查看 Nginx 状态的地址.StubStatus 模块能够获取 Nginx 自上次启动以来的工作状态，此模块非核心模块，需要在 Nginx 编译安装时手工指定才能使用
        location /NginxStatus {

            stub_status on;

            access_log on;

            auth_basic "NginxStatus";

            auth_basic_user_file conf/htpasswd;

            #htpasswd文件的内容可以用apache提供的htpasswd工具来产生.

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

    ##upstream的负载均衡,四种调度算法##

    # 调度算法1:轮询.每个请求按时间顺序逐一分配到不同的后端服务器,如果后端某台服务器宕机,故障系统被自动剔除,使用户访问不受影响
    upstream webhost {

        server 192.168.0.5:6666 ;

        server 192.168.0.7:6666 ;

    }

    # 调度算法2:weight(权重).可以根据机器配置定义权重.权重越高被分配到的几率越大
    upstream webhost {

        server 192.168.0.5:6666 weight=2;

        server 192.168.0.7:6666 weight=3;

    }

    # 调度算法3:ip_hash. 每个请求按访问IP的hash结果分配,这样来自同一个IP的访客固定访问一个后端服务器,有效解决了动态网页存在的session共享问题
    upstream webhost {

        ip_hash;

        server 192.168.0.5:6666 ;

        server 192.168.0.7:6666 ;

    }

    # 调度算法4:url_hash(需安装第三方插件).此方法按访问url的hash结果来分配请求,使每个url定向到同一个后端服务器,可以进一步提高后端缓存服务器的效率.Nginx本身是不支持url_hash的,如果需要使用这种调度算法,必须安装Nginx 的hash软件包
    upstream webhost {

        server 192.168.0.5:6666 ;

        server 192.168.0.7:6666 ;

        hash $request_uri;

    }

    # 调度算法5:fair(需安装第三方插件).这是比上面两个更加智能的负载均衡算法.此种算法可以依据页面大小和加载时间长短智能地进行负载均衡,也就是根据后端服务器的响应时间来分配请求,响应时间短的优先分配.Nginx本身是不支持fair的,如果需要使用这种调度算法,必须下载Nginx的upstream_fair模块


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

            client_max_body_size 10m; # 允许客户端请求的最大单文件字节数

            client_body_buffer_size 128k; # 缓冲区代理缓冲用户端请求的最大字节数,

            proxy_connect_timeout 90; # nginx跟后端服务器连接超时时间(代理连接超时)

            proxy_send_timeout 90; # 后端服务器数据回传时间(代理发送超时)

            proxy_read_timeout 90; # 连接成功后,后端服务器响应时间(代理接收超时)

            proxy_buffer_size 4k; # 设置代理服务器（nginx）保存用户头信息的缓冲区大小

            proxy_buffers 4 32k; # proxy_buffers缓冲区,网页平均在32k以下的设置

            proxy_busy_buffers_size 64k; # 高负荷下缓冲大小（proxy_buffers*2）

            proxy_temp_file_write_size 64k; # 设定缓存文件夹大小,大于这个值,将从upstream服务器传

        }
    }
}
```

### 转摘
[Nginx配置参数中文说明](http://wangying.sinaapp.com/archives/931)

