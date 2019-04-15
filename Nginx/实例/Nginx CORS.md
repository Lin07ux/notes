### CORS 简介

CORS 是一个 W3C 标准，全称是"跨域资源共享"(Cross-origin resource sharing)。它允许浏览器向跨源服务器，发出 XMLHttpRequest 请求，从而克服了 AJAX 只能同源使用的限制。

> 跨域资源共享(CORS)标准新增了一组 HTTP 首部字段，允许服务器声明哪些源站有权限访问哪些资源。另外，规范要求，对那些可能对服务器数据产生副作用的 HTTP 请求方法（特别是 GET 以外的 HTTP 请求，或者搭配某些 MIME 类型的 POST 请求），浏览器必须首先使用 OPTIONS 方法发起一个预检请求(preflight request)，从而获知服务端是否允许该跨域请求。服务器确认允许之后，才发起实际的 HTTP 请求。在预检请求的返回中，服务器端也可以通知客户端，是否需要携带身份凭证（包括 Cookies 和 HTTP 认证相关数据）。

CORS 规定，`Content-Type`不属于以下 MIME 类型的，都需要发送预检请求：

```
application/x-www-form-urlencoded
multipart/form-data
text/plain
```

### CORS 响应头

CORS 需要浏览器和服务器同时支持：浏览器会在检测到需要跨域访问的时候，会自动添加一些附加的头信息，有时还会多出一次附加的预检请求，但用户不会有感觉；而服务器端则需要在跨域的请求的响应中设置特定的响应头，让浏览器可以根据这些响应头判断是否允许跨域访问。

服务器端对跨域控制的响应头主要有如下几个：

* `Access-Control-Allow-Origin`
    
    该字段是必须的。它的值要么是请求时 Origin字 段的值，要么是一个`*`，表示接受任意域名的请求。

* `Access-Control-Allow-Methods`

    该字段是必须的，用来列出浏览器的 CORS 请求可以用到哪些 HTTP 方法，一般会设置为常用的 HTTP 方法：`GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS`。
    
    发送"预检请求"时，需要用到方法`OPTIONS`，所以服务器需要允许该方法。而且可以对该方法直接返回 204 响应即可。

* `Access-Control-Allow-Credentials`

    该字段可选。它的值是一个布尔值，表示是否允许发送 Cookie。默认情况下，Cookie 不包括在 CORS 请求之中。设为 true，即表示服务器明确许可，Cookie 可以包含在请求中，一起发给服务器。这个值也只能设为 true，如果服务器不要浏览器发送 Cookie，删除该字段即可。

* `Access-Control-Allow-Headers`

    该字段是一个逗号分隔的字符串，表明服务器支持的所有头信息字段，不限于浏览器在"预检"中请求的字段。一般要将常用的 header 加入到其中，如`Content-Type`。

* `Access-Control-Expose-Headers`

    该字段可选。CORS 请求时，XMLHttpRequest 对象的`getResponseHeader()`方法只能拿到 6 个基本字段：`Cache-Control`、`Content-Language`、`Content-Type`、`Expires`、`Last-Modified`、`Pragma`。如果想拿到其他字段，就必须在`Access-Control-Expose-Headers`里面指定。

* `Access-Control-Max-Age`

    该字段可选，用来指定预检请求的有效期，单位为秒。比如，设置值为 1728000，表示允许缓存该条回应 1728000 秒，即有效期是 20 天，在此期间，不用发出另一条预检请求。

### 跨域配置

一个简单的 Nginx CORS 响应头可以类似如下配置：

```conf
location / {
    add_header 'Access-Control-Allow-Origin' '$http_origin';
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, HEAD, OPTIONS';
    # X-AUTH-USER, X-AUTH-TOKEN 是 API 中传递的自定义 HEADER
    add_header 'Access-Control-Allow-Headers' 'DNT,Origin,User-Agent,Keep-Alive,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Accept,Cookie,Set-Cookie,X-AUTH-USER,X-AUTH-TOKEN';
    add_header 'Access-Control-Expose-Headers' 'Content-Length,Content-Range';
    add_header 'Access-Control-Max-Age' 1728000;
    
    if ($request_method = 'OPTIONS') {
       return 200;
    }
}
```

### 参考

* [CORS on Nginx](https://enable-cors.org/server_nginx.html)
* [跨域资源共享 CORS 详解 - 阮一峰](http://www.ruanyifeng.com/blog/2016/04/cors.html)
* [Nginx配置跨域请求 Access-Control-Allow-Origin *](https://segmentfault.com/a/1190000012550346)



