### Vue-cli proxyTable
vue-cli 的 config 文件里有一个参数叫 proxyTable，这个参数主要是一个地址映射表，可以通过设置将复杂的 url 简化，例如我们要请求的地址是`api.xxxxxxxx.com/list/1`，可以按照如下设置：

```json
proxyTable: {
  '/list': {
    target: 'http://api.xxxxxxxx.com',
    pathRewrite: {
      '^/list': '/list'
    }
  }
}
```

这样我们在写 url 的时候，只用写成`/list/1`就可以代表`api.xxxxxxxx.com/list/1`。
那么又是如何解决跨域问题的呢？其实在上面的'list'的参数里有一个 changeOrigin 参数，接收一个布尔值，如果设置为 true，那么本地会虚拟一个服务端接收你的请求并代你发送该请求，这样就不会有跨域问题了，当然这只适用于开发环境。增加的代码如下所示：

```json
proxyTable: {
  '/list': {
    target: 'http://api.xxxxxxxx.com',
    changeOrigin: true,
    pathRewrite: {
      '^/list': '/list'
    }
  }
}
```


> 这个设置来自于其使用的插件[`http-proxy-middleware`](https://github.com/chimurai/http-proxy-middleware)。
> 具体的配置文档查看：[API Proxying During Development](https://vuejs-templates.github.io/webpack/proxy.html)

> 转摘：[Vue-cli proxyTable 解决开发环境的跨域问题](http://www.jianshu.com/p/95b2caf7e0da)


### 后端设置跨域
如果不用代理，那么就可以在 web 服务器中设置一些跨域的 header：

```conf
Access-Control-Allow-Origin：<domain>;
Access-Control-Allow-Credentials: true;
```

前端也需要做一些设置，jquery 的设置如下：

```javascript
xhrFields: {
    withCredentials: true
},
crossDomain: true
```

