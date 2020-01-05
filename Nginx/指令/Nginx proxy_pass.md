### proxy_pass 的代理路径

> 转摘：[nginx配置之proxy_pass代理路径](https://blog.csdn.net/zero_0_one_1/article/details/83540146)

Nginx 中的 proxy_pass 指令一般用于反向代理配置中。语法如下：

```conf
proxy_pass uri;
```

其中，`uri`是代理的资源路径，其值可以分为两种情况：

1. 仅包括代理主机地址(包括`http://`和端口`:port`)，此时会将请求路径不做处理的传递到代理服务器中。
2. 包含代理主机地址和路径(此路径可仅为一个`/`)，此时会将请求的路径去除掉当前 location 指令中匹配的路径字符串之后，拼接到代理路径中传递给代理服务器。

比如：

**示例 1：**

```conf
location /proxypath/ {
   proxy_pass http://hostname[:port];
}
```

这里的`proxy_pass`指令的值仅包含代理主机地址(和端口)，那么会将当前请求的路径直接传递给代理服务器。

也就是说，当访问`http://domain/proxypath/page.html`时，将由地址`http://hostname/proxypath/page.html`页面代理响应。

**示例 2：**

```conf
location /proxypath/ {
   proxy_pass http://hostname[:port]/;
}
```

这里的`proxy_pass`指令的值除了包含代理主机地址，还包括了一个`/`代理路径，则会将当前的请求路径去除掉 location 中匹配的`proxypath`字符串之后，拼接在代理路径`/`之后传递给代理服务器。

也就是说，当访问`http://domain/proxypath/page.html`时，将由地址`http://hostname/page.html`页面代理响应，可以看到代理的路径中不包括`/proxypath/`字符串了。

**示例 3：**

```conf
location /proxypath/ {
	proxy_pass http://hostname[:port]/resourcepath;
}
```

这里的`proxy_pass`指令的值除了包含代理主机地址，还包括了一个`/resourcepath`代理路径，则会将当前的请求路径去除掉 location 中匹配的`proxypath`字符串之后，拼接在代理路径`/resourcepath`之后传递给代理服务器。

也就是说，当访问`http://domain/proxypath/page.html`时，将由地址`http://hostname/resourcepathpage.html`页面代理响应。可以看到代理路径是将当前请求的路径去除掉 location 中匹配的`/proxypath/`字符串后，直接拼接到代理路径`/resourcepath`之后形成的。

**示例 4：**

```conf
location /proxypath/ {
	proxy_pass http://hostname[:port]/resourcepath/;
}
```

这里的`proxy_pass`指令的值除了包含代理主机地址，还包括了一个`/resourcepath/`代理路径，则会将当前的请求路径去除掉 location 中匹配的`proxypath`字符串之后，拼接在代理路径`/resourcepath/`之后传递给代理服务器。

也就是说，当访问`http://domain/proxypath/page.html`时，将由地址`http://hostname/resourcepath/page.html`页面代理响应。可以看到代理路径是将当前请求的路径去除掉 location 中匹配的`/proxypath/`字符串后，直接拼接到代理路径`/resourcepath/`之后形成的。

