### 负载均衡方法
1. 默认的是权重轮询算法，nginx 会按照各个 server 的权重比来分发请求。
2. 最少连接法：每次都选择连接数最少的 server，如果有多个 server 的连接数相同，再根据这几个 server 的权重分发请求。

```nginx
upstream backend {
    least_conn;

    server backend1.example.com;
    server backend2.example.com;
}
```

3. ip 哈希：使用 IPv4 的前三个八字节位或者 IPv6 的全部来计算哈希值，所以相同地址的请求始终会打到相同的 server 上，除非这个 server 不可用。

```nginx
upstream backend {
    ip_hash;

    server backend1.example.com;
    server backend2.example.com;
    server backend3.example.com down;
}
```

4. 哈希：使用自定义的key去进行哈希计算。通过 consistent 参数使用 ketama 一致哈希算法来减少增减服务器对客户端的影响。

```nginx
# 通过 uri 进行哈希计算
upstream backend {
    hash $request_uri consistent;

    server backend1.example.com;
    server backend2.example.com;
}
```

5. 最小响应时间：对于每个请求，nginx 选择平均等待时间最小并且连接数最少的。通过 least_time 的参数来确定平均等待时间的计算方式。
    * header：收到服务器返回的第一字节时间
    * last_byte：收到全部返回内容的时间

```nginx
upstream backend {
    least_time header;

    server backend1.example.com;
    server backend2.example.com;
}
```

### 服务器权重
- 默认的权重是 1，通过 weight 参数来指定权重值。
- 通过 backup 来标记备用服务器，如果其他的都宕机了，请求会打到备用的服务器是上。

```nginx
upstream backend {
    server backend1.example.com weight=5;
    server backend2.example.com;
    server 192.0.0.1 backup;
}
```

### 服务器慢启动
对于刚刚恢复的服务器，如果一下子被请求淹没可能会再次宕机。可以通过 server 指令的 slow_start 参数来让其权重从 0 缓慢的恢复到正常值。

```nginx
upstream backend {
    server backend1.example.com slow_start=30s;
    server backend2.example.com;
    server 192.0.0.1 backup;
}
```

> 注意：如果一个组里面只有一个 server，那么 max_fails，fail_timeout 和slow_start 参数都会被忽略。并且这个服务器永远都不会被认为是不可用的。

### session 持久性
Session 持久性是指 nginx 识别客户端的 session 然后把同一个 session 的请求路由到同一个 server 上。

nginx 支持三种 session 持久的方法，通过`sticky`指令去设置：

- **`sticky cookie` 方法** 通过这种方法，当 server 第一个响应的时候，nginx 添加一个 cookie 来标识是哪个 server 响应的，当它下次请求的时候，会带着 cookie，nginx 会把请求路由到同一台 server。

```nginx
upstream backend {
    server backend1.example.com;
    server backend2.example.com;

    sticky cookie srv_id expires=1h domain=.example.com path=/;
}
```

- **`sticky route`方法** 使用这种方法，nginx 第一个收到请求的时候会给客户端下发一个路由。后续的请求都会带着路由参数，nginx 再来判断请求该打到哪个 server 上。路由信息可以通过 cookie 或者 uri 获取到。

```nginx
upstream backend {
    server backend1.example.com route=a;
    server backend2.example.com route=b;

    sticky route $route_cookie $route_uri;
}
```

- **`cookie learn` 方法** 通过这种方法，nginx 通过检测请求和响应来寻找 session 标记。通常，这些标记通过 cookie 传递。如果一个请求的包含的 session 标记已经 learned，nginx 将会把请求打到正确的 server 上。
    * 在下面的例子中，server 通过在响应中设置一个"EXAMPLECOOKIE"来标记一个 session。
    * create 的参数指定一个变量来指示一个 session 的创建，在这个例子中，server 发送"EXAMPLECOOKIE"表示新的 session 建立。
    * lookup 的参数指定如何寻找一个已经存在的 session，在这个例子中，通过查找客户端的"EXAMPLECOOKIE"来搜索现有的 session。
    * zone 指定一块共享的内存来保存 sticky sessions 的信息。上个例子中，这块空间叫做 client_sessions，大小为 1M。

```nginx
upstream backend {
   server backend1.example.com;
   server backend2.example.com;

   sticky learn 
       create=$upstream_cookie_examplecookie
       lookup=$cookie_examplecookie
       zone=client_sessions:1m
       timeout=1h;
}
```
    
### 限制连接数量
可以通过`max_conns`参数来限制连接数，如果达到了最大连接数，请求就可以放到通过`queue`指令生命的队列中来等待后续的处理 ，queue 指令设置了可以放到队列中得最大的请求数。

```nginx
upstream backend {
    server backend1.example.com  max_conns=3;
    server backend2.example.com;

    queue 100 timeout=70;
}
```

如果队列排满或者在可选参数 timeout 设置的时间内无法选择上游服务器，客户端将接到一个错误。

> 注意：如果闲置的 keepalive 连接在另一个 worker processes 中打开了，max_conns 限制会被忽略。导致的结果是，在多个工作进程共享内存的配置中，连接的总数可能会超过 max_conns 的值。

### 被动的健康监测
当 nginx 认为一个 server 不可用，它会暂时停止向这个 server 转发请求直至 nginx 再次认为它是可用的。

nginx 通过两个参数来控制 nginx 的判断：

- `fail_timeout` 当在 fail_timeout 时间段内，失败次数达到一定数量则认为该 server 不可用。并在接下来的 fail_timeout 时间内不会再将请求打到这个 server 上。默认是 10s。
- `max_fails` 这就是上面说的失败的一定数量。默认是 1 次。

```nginx
upstream backend {                
    server backend1.example.com;
    server backend2.example.com max_fails=3 fail_timeout=30s;
    server backend3.example.com max_fails=2;
}
```

### 主动的心跳检测
使用`health_check`指令来检测 server 是否可用，除此之外还要使用`zone`指令。

```nginx
http {
    upstream backend {
        zone backend 64k;

        server backend1.example.com;
        server backend2.example.com;
        server backend3.example.com;
        server backend4.example.com;
    }

    server {
        location / {
            proxy_pass http://backend;
            health_check;
        }
    }
}
```

> 在上个例子中，nginx 每五秒请求一次 backend 组里的每台机器，请求地址是"/"，如果失败或者超时（或者返回的状态码不是 2xx 或者 3xx），nginx 就认为这个 server 宕机了。nginx 将停止向它转发请求，直至其再次通过心跳检测。

- 可以通过参数控制 health_check 指令的行为。

```nginx
location / {
    proxy_pass http://backend;
    # 每十秒检测一次，如果失败了3次就认为宕机了，如果成功2次就认为通过了检测。
    health_check interval=10 fails=3 passes=2;
}

location / {
    proxy_pass http://backend;
    # 也可以检测特定的 uri
    health_check uri=/some/path;
}
```

- 可以通过 match 块自定义成功的状态。match 块中可以包含一个 status 的状态，一个 body 的状态和多个 header 的状态。status、body、header 中可以使用 ! 来表示不匹配给定的条件。

```nginx
http {
    ...

    # 要求 status 在 200 到 399 之间，并且 body 满足提供的正则表达式
    match server_ok {
        status 200-399;
        body !~ "maintenance mode";
    }
    
    # 表示 status 不是 301、302、303 或者 307，
    # 并且 header 中不包含 Refresh。
    match not_redirect {
        status ! 301-303 307;
        header ! Refresh;
    }

    server {
        ...

        location / {
            proxy_pass http://backend;
            health_check match=server_ok;
        }
    }
}
```


