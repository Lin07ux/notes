root 和 alias 都可以定义在 location 模块中，用来指定请求资源的真实路径。不同点在于：

1.	`alias`指令只能作用在 location 中，而`root`指令可以存在 server、http 和 location 中。
2.	`alias`指令的参数后面必须要用`/`结束，否则会找不到文件，而`root`指令的参数则对`/`可有可无。

`root`指令，对应的请求资源的**真实的路径是 root 指定的值加上 location 指定的值**。比如：

```conf
location /i/ {
    root /data/w3;
}
```

请求`http://foofish.net/i/top.gif`这个地址时，在服务器里面对应的真正的资源是`/data/w3/i/top.gif`文件。。

![](http://cnd.qiniu.lin07ux.cn/markdown/1476061025524.png)

`alias`指令指定的路径是 location 的别名，不管 location 的值怎么写，资源的**真实路径都是 alias 指定的路径替换 location 的路径后得到的结果**。比如：

```nginx
location /i/ {
    alias /data/w3/;
}
```

同样请求`http://foofish.net/i/top.gif`时，在服务器查找的资源路径是：`/data/w3/top.gif`。

![](http://cnd.qiniu.lin07ux.cn/markdown/1476061099215.png)





