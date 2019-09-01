Nginx 通过`limit_conn_zone`和`limit_req_zone`对同一个 IP 地址进行限速限流，可防止 DDOS/CC 和 flood 攻击：

- `limit_conn_zone`是限制同一个 IP 的连接数，而一旦连接建立以后，客户端会通过这连接发送多次请求。
- `limit_req_zone`就是对请求的频率和速度进行限制。

另外，还可以设置带宽限制、黑白名单限制等。

### 1. 限制连接数

首先在 Nginx 的 http/server 段定义一个限制连接设置，配置如下：

```conf
limit_conn_zone $binary_remote_address zone=addr:10m;
```

然后在 Nginx 的 server/location 段应用上面设置的限制连接设置，配置如下：

```conf
limit_conn addr 2;
```

这里两行虽然不是在一起配置，它们之间通过`addr`这个变量名联系在一起。

定义限连设置时，各个配置的含义如下：

* `$binary_remote_address` 表示保存客户端 IP 地址的二进制形式。
* `zone=addr:10m` 表示定义一个限连标识符，名称是`addr`，而且这个配置可用内存空间为 10m，也就是可以存储的二进制 IP 地址的空间为 10m，大约可以存储 16 万个 IP 地址。

可以对某个目录或指定后缀比如`.html`或`.jpg`进行并发连接限制，因为不同资源连接数是不同的，对于主要的`.html`文件并发数是两个就够了，但是一个`html`页面上有多个`jpg/gif`资源，那么并发两个肯定是不够，需要加大连接数，但是也不能太大。

### 2. 限制频率

有了连接数限制，相当于限制了客户端浏览器和 Nginx 之间的管道个数，那么浏览器通过这个管道运输请求，如同向自来水管中放水，水的流速和压力对于管道另外一端是有影响的。为了防止不信任的客户端通过这个管道疯狂发送请求，对耗 CPU 的资源 URL 不断发出狂轰滥炸，必须对请求的速度进行限制，如同对水流速度限制一样。

首先，需要在 Nginx 的 http/server 段定义限制频率的设置，配置如下：

```conf
limit_req_zone $binary_remote_addr zone=one:10m rate=5r/s;
```

然后在 Nginx 的 server/location 段应用定义的限频设置，配置如下：

```conf
limit_req zone=one burst=10 delay;
```

在定义限频设置时，各个字段和限流设置的含义相同，只是多了如下一个配置：

* `rate=5r/s` 定义了最大请求速率，这里表示客户端(单一 IP)每秒最大不能超过 5 个请求。

在应用限流定义时，除了指明限流定义的名称，还多了如下两个配置：

* `burst=10` 使用 burst 漏桶原理限流，当请求速率超过`rate`设置的限定后，超过的请求会被排队等待。这里表示允许超过频率 rate 限制的请求数不多于 10 个，也就是最大排队数量为 10。
* `delay` 表示超过限流设定的请求数量之后，排队的请求会被延迟处理。不明确写`delay`或者`nodelay`时都表示`delay`。

对于这里的使用漏桶限流的具体设定，可以按照如下思路理解：

* 当每秒请求不超过 5 个时，会被正常处理；
* 当每秒请求超过 5 个，不超过 15 个(10 + 5)时，超过的这些请求会被放到延时队列中等待处理。
* 当每秒请求超过 15 个时，超过的请求会被直接返回 HTTP 503 响应，不会继续排队了。

对于一个页面如果有很多资源需要加载，使用延时也就是队列的方式对服务器冲击小，而且可以防止攻击者对同一个资源发出很多请求。

如果使用`nodelay`配置就没有了延时排队功能，请求会被尽快完成。比如：

```conf
limit_req zone=one burst=10 nodelay;
```

这表示，如果每秒请求在 0-15 个之间会尽快完成，也就是以每秒 15 个速率完成。而每秒超过 15 个的请求则会直接被返回 503 响应。这依然会对服务器造成较大的冲击。因此，**对于中小型网站，推荐使用 delay 方案，而不要写明 nodelay**。

> 在 Twitter、Facebook、LinkedIn 这类大型网站中，由于访问量巨大，通常会在 http 服务器后面放置一个消息队列，比如 Apache Kafka，用来排队大量请求。

### 3. 带宽限制

```nginx
limit_rate 50k; 
limit_rate_after 500k;
```

上面的设置表示：当下载的大小超过 500k 以后，以每秒 50K 速率限制。

### 4. 黑白名单

Nginx 中可以根据客户端的 IP 地址来设置黑白名单，在黑名单中的请求可以进行限流，或者禁止，而白名单中的请求则不限制。

* 限流黑名单

    下面的配置会将 122.16.11.0/24 段的 IP 添加到白名单中，而不在白名单中的请求会被限流到每秒 1 个请求。
    
    ```conf
    server {
        ...
        
        geo $limit {
            122.16.11.0/24 0;
        }
        
        map $limit $limit_key {
            1 $binaruy_remote_addr;
            0 "";
        }
    
        limit_req_zone $limit_key zone=mylimit:10m rate=1r/s;
        
        location / {
            limit_req zone=mylimit burst=5 delay;
            proxy_pass http://serviceCluster;
        }
    }
    ```

* 禁止黑名单

    使用`deny`可以禁止特定的 IP 访问，相当于黑名单；使用`allow`可以允许特定的 IP 访问，相当于白名单。
    
    设置白名单后，使用`deny all`，则表示禁止全部白名单之外的 IP 访问。
    
    ```conf
    # 黑名单
    location / {
        deny 10.52.119.21;
        deny 122.12.1.0/24;
    }
    
    # 白名单
    location {
        allow 10.1.1.0/16;
        allow 1001:0db8::/32;
        deny all;
    }
    ```

### 5. 其他

上面总结了几种常见限速限流设置方式，还有一种能够防止特定类别的请求的攻击。

比如黑客通过发出大量 POST 请求对网站各种 URL 进行试探攻击，可以通过下面方式防止：

```nginx
http {
    ...

    # 如果请求类型是 POST 将 ip 地址映射到 $limit 变量
    map $request_method $limit {
        default "";
        POST $binary_remote_addr;
    }
    
    # 创造 10mb 内存存储二进制 ip
    limit_req_zone $limit zone=my_zone:10m rate=1r/s;
}
```

### 6. 参考

* [Nginx 对同一IP限流](http://www.jdon.com/performance/nginx-dos-protection.html)
* [除了负载均衡，Nginx还可以做很多，限流、缓存、黑白名单等](https://mp.weixin.qq.com/s/u_UQz8Nmpp8YRgAX7OE6zQ)


