`add_header`指令用于设置用户自定义的响应头，比如，设置 HSTS 响应头：

```conf
add_header Strict-Transport-Security "max-age=63072000; preload";
add_header X-Frame-Options SAMEORIGIN;
add_header X-Content-Type-Options nosniff;
add_header X-XSS-Protection "1; mode=block";
```

### 继承性

> 转摘：[小心Nginx的add_header指令](https://www.tlanyan.me/be-careful-with-nginx-add_header-directive/)

`add_header`指令的继承性有点特殊，官网上关于`add_header`有如下的说明：

> There could be several add_header directives. **These directives are inherited from the previous level if and only if there are no add_header directives defined on the current level**.

这段话中，重要的就是**仅当当前层级中没有`add_header`指令才会继承父级设置**。

也就是说：如果`location`块中有`add_header`指令，那么`server`和更上层级的`http`块中的`add_header`都会失效。

这是 Nginx 的故意行为，说不上是 bug 或坑。但深入体会这句话，会发现更有意思的现象：仅最近一处的`add_header`起作用。`http`、`server`和`location`三处均可配置`add_header`，但起作用的是最接近的配置，往上的配置都会失效。

即便是同级的`location`之间进行的`rewrite`操作，也仅仅会在最后一次出现`add_header`处的`location`块中的有效，其他的都失效。

示例如下：

```conf
location /foo1 {
    add_header foo1 1;
    rewrite / /foo2;
}

location /foo2 {
    add_header foo2 1;
    return 200 "OK";
}
```

不管请求`/foo1`还是`/foo2`，最终`header`只有`foo2`。

