[lua-nginx-module](https://github.com/openresty/lua-nginx-module) 是 Nginx 中的一个模块，使得 Nginx 可以利用 Lua 实现一些编程访问和控制的能力。

下面是一些真实的使用场景的配置：

> 转摘：[nginx+lua在我司的实践](https://www.cnblogs.com/chopper-poet/p/10744214.html)

### 1. 入口层的灰度识别

入口层流量的灰度识别，简单来说就是 A 用户的请求打到线上环境，B 用户的请求打到灰度环境，目的就是做新功能的验证，实现逻辑很简单，大体流程如下：

1. 测试同学在灰度控制台配置灰度规则，规则里会约束哪些 url 下哪些商户的请求进入灰度环境；
2. 灰度控制台推送规则给入口层 Nginx，Nginx 会将规则存储到本地内存中；
3. 请求进入的时候（通过`rewrite_by_lua_file`触发）获取本地内存中的规则进行比对，如果命中规则就将请求转发到灰度环境，对 Nginx 来说就是切换到不同的 upstream，比如线上是 prod_serverA，灰度是 gray_serverA。

代码片段如下：

```nginx
upstream  gray_serverA {
    server 192.68.1.1:8080;
}
 
upstream prod_serverA {
    server 192.68.1.2:8080;
}
 
server {
    listen 80;
    server_name graytest.demo.com;
    charset utf-8;
     
    location ~ \.do$ {
        set $backend 'prod_serverA';   # 默认的 upstream 为线上服务
        
        # rewrite_by_lua_file: https://github.com/openresty/lua-nginx-module/#rewrite_by_lua_file
        # 可以简单的理解为一个过滤器，Nginx 在 rewrite 阶段会执行指定的脚本文件
        # 在这个文件中会判断请求是否为灰度请求如果是灰度请求就将 backend 改为 gray_serverA
        rewrite_by_lua_file "conf/lua-gray/rewriter.lua";
        
        proxy_pass http://$backend;
    }  
}
```

### 2. 入口层记录错误日志

可以针对特定的错误码进行日志记录。比如，对于 415 错误，可以利用 [log_by_lua_block](https://github.com/openresty/lua-nginx-module/#log_by_lua_block) 指令进行错误日志记录：

```nginx
location  ~ \.do$ {
    proxy_pass http://$backend;
    # 判断如果 http 响应状态码为 415 就输出请求头到文件中
    log_by_lua_block {
     if tonumber(ngx.var.status) == 415 then
           ngx.log(ngx.ERR, "upstream reponse status is 415, please notice it, here are request headers:")
           local h, err = ngx.req.get_headers(100, true)
 
           if err == "truncated" then
               ngx.log(ngx.ERR, "request headers beyond 100, will not return")
           else
              local cjson = require("cjson")
              ngx.log(ngx.ERR, cjson.encode(h))
           end
       end
    }
}
```

### 3. 将 Nginx 信息注册到监控平台

当 Nginx 启动的时候，将自身信息上报到 Redis 中，上报内容包括自身的 IP、代理的域名等，而且只会还会定时上传这些信息，起到心跳检测效果。然后有个监控平台会定期从 Redis 中读取这些信息做展示，方便运维管理。

可以在启动或者 reload Nginx 的时候使用`init_worker_by_lua_file file_name.lua`加载如下的脚本：

```lua
local workerId = ngx.worker.id()
 
if(workerId == 0) then
    ngx.log(ngx.INFO, 'workerId is 0 will startup task')
         
     
    local ok, err = ngx.timer.every(4, function()
        # 1. get local ip and domins
        #2. write to redis
    end)
else
    ngx.log(ngx.INFO, 'workerId is not 0, just ignore it')
end
```

其中，`ngx.worker.id()`这个方法会返回 Nginx Worker 进程的编号，从 0 开始。如果 Nginx 有 4 个 worker，那么返回值的范围就是 0 ~ 3。通过 worker 编号的判断，可以防止每个进程都进行上报。

`ngx.timer.every`方法是用来做定时任务的，保证每过一段时间，就会自动上报一次信息。

### 4. 将入口层流量同时转发到多个后端服务

Nginx 默认只会将每个进程转发到一个后端服务中进行处理。利用 Lua 脚本可以实现类似消息队列的发布订阅一样，将一个请求在 Nginx 层就同时发送到多个地址终。

代码片段类似如下：

```nginx
location  ~ /capture_test$ {
    content_by_lua_block {
        ngx.req.read_body()
      
        res1, res3 = ngx.location.capture_multi{
            {"/capture_test1", { method = ngx.HTTP_POST, always_forward_body=true }},
            {"/capture_test2", { method = ngx.HTTP_POST, always_forward_body=true }},
        }
    
        ngx.say(res3.body)
        ngx.exit(res3.status)
    }
}
```


