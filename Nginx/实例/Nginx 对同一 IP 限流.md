Nginx 通过`limit_conn_zone`和`limit_req_zone`对同一个 IP 地址进行限速限流，可防止 DDOS/CC 和 flood 攻击：

- `limit_conn_zone`是限制同一个 IP 的连接数，而一旦连接建立以后，客户端会通过这连接发送多次请求。
- `limit_req_zone`就是对请求的频率和速度进行限制。


## 限制连接数
在 Nginx 的`http`配置如下：

```limit_conn_zone $binary_remote_address zone=addr:10m;```

然后在 Nginx 的`server`段配置如下：

```limit_conn addr 2;```

这里两行虽然不是在一起配置，它们之间通过`addr`这个变量名联系在一起。你可以对某个目录或指定后缀比如`.html`或`.jpg`进行并发连接限制，因为不同资源连接数是不同的，对于主要的`.html`文件并发数是两个就够了，但是一个`html`页面上有多个`jpg/gif`资源，那么并发两个肯定是不够，需要加大连接数，但是也不能太大。

## 限制频率
有了连接数限制，相当于限制了客户端浏览器和 Nginx 之间 的管道个数，那么浏览器通过这个管道运输请求，如同向自来水管中放水，水的流速和压力对于管道另外一端是有影响的。为了防止不信任的客户端通过这个管道疯狂发送请求，对我们的耗 CPU 的资源 URL 不断发出狂轰滥炸，必须对请求的速度进行限制，如同对水流速度限制一样。

在 Nginx 的`http`段配置：

```limit_req_zone $binary_remote_addr zone=one:10m rate=5r/s;```

在 Nginx 的`server`段配置

```limit_req zone=one burst=10;```

这里引入**burst 漏桶原理**，结合`rate`速率每秒 5 个请求(rate=5r/s)解释如下：

* rate=5r/s：从单一 IP 地址每秒 5 个请求是允许的，
* burst=10：允许超过频率 rate 限制的请求数不多于 10 个
* 当每秒请求超过 5 个，但是在 10 个以下，也就是每秒请求的数量在 5 到 10 之间的请求将被延时 delay，虽然这里没有明写 delay，默认是延时，因为漏洞其实类似队列 Queue 或消息系统，当每秒请求数量超过最低速率每秒 5 个时，多余的请求将会进入这个队列排队等待。如同机场安检，一次放入 5 个，多于 5 个，小于 10 个的排队等待，注意：这个队列或漏洞是以每秒为单位的。
* 如果每秒请求数超过 10 个，也就是 burst 的限制，那么也不排队了直接回绝，返回 503 http。也就是说排队长度不能超过 10 个。

上述使用默认延时也就是队列的方式对于一个页面如果有很多资源需要加载，那么通过排队延时加载无疑对服务器冲击小，而且防止攻击者对同一个资源发出很多请求。

如果我们使用 nodelay：

```limit_req zone=one burst=10 nodelay;```

这表示，如果每秒请求在 5-10 个之间会尽快完成，也就是以每秒 10 个速率完成，超过每秒 10+5 也就是 15 个就立即返回 503，因此 nodelay 实际没有了延时，也就取消了队列等候过渡。

在 Twitter、Facebook、LinkedIn 这类大型网站中，由于访问量巨大，通常会在 http 服务器后面放置一个消息队列，比如 Apache Kafka，用来排队大量请求，因此，对于中小型网站，推荐使用 delay 方案，而不要写明 nodelay，但是网络上其他各种文章几乎都是推荐 nodelay。

## 带宽限制

```nginx
limit_rate 50k; 
limit_rate_after 500k;
```

上面的设置表示：当下载的大小超过 500k 以后，以每秒 50K 速率限制。

## 其他
上面总结了三个限速限流设置方式，还有一种能够防止 POST 攻击，黑客通过发出大量 POST 请求对网站各种 URL 进行试探攻击，可以通过下面方式防止：

```nginx
http {
    ... #nginx.conf 配置

    #如果请求类型是POST 将ip地址映射到 $limit 值
    map $request_method $limit {
        default "";
        POST $binary_remote_addr;
    }
    
    #创造 10mb zone 内存存储二进制 ip
    limit_req_zone $limit zone=my_zone:10m rate=1r/s;
}
```

## 参考
[Nginx 对同一IP限流](http://www.jdon.com/performance/nginx-dos-protection.html)


