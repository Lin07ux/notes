转自：[nginx配置：location配置原理及实例详解](http://www.cnblogs.com/sunkeydev/p/5225051.html)

### location 匹配的是 nginx 的哪个变量？
`$request_uri`


### location 的匹配种类有哪些?
一般格式为：

```nginx
location [ 空格 | = | ~ | ~* | !~ | !~* ] /uri/ {}
```
主要分为以下三类：

* 精确匹配：`=`
* 字符串匹配：`空格`、`^~`(匹配开头)
* 正则匹配：`~`(区分大小写正则匹配)、`~*`(不区分大小写正则匹配)、`!~`(区分大小写正则不匹配)、`!~*`(不区分大小写正则不匹配)


### location 搜索优先级
location 规则的搜索的顺序为：

```
精确匹配 >> 一般匹配( 长 >> 短 [注: ^~ 匹配则停止匹配]) >> 正则匹配(上 >> 下)
```

也即是：

- 首先搜索精确匹配。精确匹配只能命中一个。命中则停止搜索。
- 然后搜索字符串匹配，但是此时只是记录下最长的匹配，而不会停止搜索。不过，如果最长匹配是`^~`格式的，则会停止搜索，直接匹配这个结果。
- 再搜索正则匹配。正则匹配是按照定义的先后顺序来匹配的。一旦匹配就会停止搜索。
- 如果正则匹配没有找到匹配的，则使用最长的字符串匹配。
- 如果都没有匹配，则返回 404 错误。


### 示例
下面使用 [echo-nginx-module](https://github.com/openresty/echo-nginx-module) 模块，方便做输出测试。

1. 精确匹配

```nginx
location = /images/test.png {
    echo 'config1';
}

location  /images/test.png {
    echo 'config2';
}

location \/images\/test\.png$ {
    echo 'config3';
}
```

此时访问`http://127.0.0.1/images/test.png`就会输出`config1`，因为精确搜索优先级最高，直接匹配了精确搜索。

2. 精确搜索特殊情况

```nginx
location = / {
    index index.html;
}

location / {
    echo 'config2';
}
```

此时访问`http://127.0.0.1`输出的是`config2`。
为什么呢？精确搜索怎么不能匹配了？
其实，此时精确搜索还是匹配了的，只是由于匹配的是一个目录，所以 Nginx 在内部将请求转换成了`http://127.0.0.1/index.html`，此时，精确搜索就不匹配了，而是匹配到了字符串搜索，所以输出了`config2`。

所以：**所以精确匹配不要用来匹配`/`(或其他定义了 index 的目录)**

3. 字符串搜索和正则搜索

```nginx
location /images/test.png {
    echo 'config1';
}

location ^~ /images/ {
    echo 'config2';
}

location ~ \/images\/test\.png$ {
    echo 'config3';
}

location ~ \/images\/ {
    echo 'config4';
}
```

此时，访问`http://127.0.0.1/images/test.png`，会输出`config3`。
因为，先搜索了字符串匹配，找到了最长的字符串匹配项`/images/test.png`；然后继续搜索正则匹配，找到了对应的正则匹配`\/images\/test\.png$`，所以就使用了正则匹配，而不是最长的字符串匹配。

4. 字符串匹配优先级的提升(^~)

```nginx
location /images/ {
    echo 'config1';
}

location ^~ /images/test.png {
    echo 'config2';
}

location ~ /images/test\.png$ {
    echo 'config3';
}

location ~ \/images\/ {
    echo 'config4';
}
```

此时，再访问`http://127.0.0.1/images/test.png`，就会输出`config2`了。
因为，此时也是先搜索字符串匹配，而且找到了最长的字符串匹配`/images/test.png`，并且这个字符串匹配是`^~`格式的，于是就停止后面的正则匹配，直接命中这个最长字符串匹配了。所以就输出了`config2`。

所以：**`^~`符号比较特殊，就是为了提升字符串匹配的优先级，优先于正则匹配**。


