### 1. 基础

Nginx 中的`if`指令是通过`ngx_http_rewrite_mode`模块实现的。该指令可以实现条件判断，主要用来判断一些在`rewrite`语句中无法直接匹配的条件，比如检测文件是否存在、检测特定的 http header、cookie 等。

语法如下：

```conf
if (condition) { ... }
```

该指令具有如下特点：

* 不支持逻辑与`&&`、逻辑或`||`等逻辑运算符；
* 不支持`else`语法；
* 不支持嵌套语法。

### 2. 条件模式

`if`的条件 condition 中，可以是如下的任何内容：

* 变量名：如果这个变量是空字符串或者为 0 开始的字符串，则其等效于 false；
* 相等判断：使用`=`、`!=`比较的一个变量和字符串；
* 正则匹配：使用`~`、`~*`与正则表达式匹配的变量（如果正则表达式中包含`{}`，则整个正则表达式需要用`"`或`'`包裹起来）；
* 文件判断：使用`-f`、`!-f`检查一个文件是否存在；
* 目录判断：使用`-d`、`!-d`检查一个目录是否存在；
* 存在判断：使用`-e`、`!-e`检查一个文件、目录、符号链接是否存在；
* 可执行判断：使用`-x`、`!-x`检查一个文件是否可执行。

比如：

```conf
if ($http_user_agent ~ MSIE) {
    rewrute ^(.*)$ /msie/$1 break;
}

if ($http_cookie ~* "id=([^;]+)(?:;|$)") {
    set $id 1;
}

if ($request_method = POST) {
    return 405;
}

if ($slow) {
    limit_rate 10k;
}

if ($invalid_referer) {
    return 403;
}
```

### 3. 多重判断

> 转摘：[nginx: if逻辑运算 （与或非） 实现](https://www.iteblog.com/archives/1243.html)

`if`指令不支持嵌套语法，为了实现多重条件的判定，可以使用中间变量的方式进行中转。

比如，为了实现非 www 子域名访问，而且是访问后台(`admin.php`为标志)的则重定向到 www 子域名：

```conf
set $flag 0;
if ($host = 'domain.com') {
   set $flag '${flag}1';
}

if ($uri ~* /admin.php(/|$)) {
   set $flag '${flag}1';
}

if ($flag = '011') {
   return 301 http://www.$host$request_uri;
}
```

### 4. 使用注意

一般来说，**应该尽量避免使用`if`指令**，但`if`指令的行为并不是反复无常的。两个条件完全一致的请求，Nginx 并不会出现一个正常工作而另一个请求失败的随机情况，在良好的测试和清晰的理解下`if`指令是可用的。但是还是建议使用其他指令。

当在`location`区块中使用`if`指令的时候会有一些问题，在某些情况下并不能按照预期运行，甚至会出现段错误。在`location`区块中，`if`指令下唯一 100%安全的指令只有：

```conf
return ...;
rewrite ... last;
```

