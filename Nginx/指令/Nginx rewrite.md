Nginx 中`rewrite`指令主要用于重写请求 URI，实现路径重写、重定向等功能。配合`if`、`set`等指令可以实现比较复杂的处理功能。

> 转摘：[Nginx Rewrite详解](https://www.starduster.me/2016/05/26/deep-into-nginx-rewrite/)

## 一、基础

### 1.1 语法

Nginx rewrite 可以用在 server、location、if 区块中，而且同一个区块中可以有多条 rewrite 指令。

语法如下：

```conf
rewrite <正则> <替换语句> [标识位];
```

其中：

* `正则` 部分用于对请求 uri 进行匹配
* `替换语句` 用来生成重写后的 uri
* `标识位` 用来控制本次重写后的后续处理

### 1.2 正则匹配

Nginx rewrite 指令中的正则部分是会对请求的相对路径`$uri`，不包含 hostname 和查询参数(URL 中`?`及其后面的部分)。所以如果要按照正则来匹配请求之后再进行重写，则需要确保正则能够在请求路径中得到匹配，否则不会执行该重写。

比如下面的 301 重定向示例：

```conf
rewrite ~* cafeneko\.info http://newdomain.com/ permanent;
```

当请求`http://blog.cafeneko.info/2010/10/neokoseseiki_in_new_home/?utm_source=rss`这个 URL 时，并不能完成重定向，因为正则中的`cafeneko.info`出现在了 hostname 中，而正则真正要匹配的`/2010/10/neokoseseiki_in_new_home/`字段内容中并没有出现该字符串，所以并不能匹配到这条重写规则。

### 1.3 替换语句

替换语句就是用来指定重写后的 uri，在这里可以使用如`$1`、`$2`等的方式引用正则中的匹配结果。

当然，由于 rewrite 的正则是不匹配`$query_string`的，所以默认情况下，`$query_string`会自动追加到 rewrite 后的地址上。

如果不想自动追加`$query_string`则需要在 rewrite 地址的末尾添加`?`符号。

如下所示：

```conf
rewrite  ^/users/(.*)$  /show?user=$1?  last;
```

对`$query_string`的不同处理方式是`rewrite`和`try_files`的重要区别：`try_files`指令会丢弃`$query_string`。这也是为什么使用`try_files`重写时，通常都会加上`$query_string`的原因：

```conf
location / {
  # 非 pathinfo 重写
  # 适用于使用 $_SERVER['request_uri'] 中的路径做路由解析的框架，如 Laravel/Yii2
  # 因为重写不会改变 $request_uri，所以在重写的时候不传递 $uri 也没关系
  # 但是使用 try_files 时必须带上 $query_string，否则就会获取到不查询参数
  try_files $uri $uri/ /index.php$is_args$query_string;
  
  # pathinfo 重写
  # 适用于使用 $_SERVER['path_info'] 做路由解析的框架，如 ThinkPHP
  # 此时需要在重写的时候将 $uri 显示的传递过去，否则就会造成 path_info 解析异常
  # 因为规范的 pathinfo 要求参数也路径化在 $uri 中，所以可以不加 $query_string
  try_files $uri $uri/ /index.php$uri$is_args$query_string
}
```

### 1.4 标识位

Nginx rewrite 的标识位有四种：

* `break` 停止 rewrite 检测。也就是说，当含有 break 标识位的 rewrite 语句被执行时，该语句就是 rewrite 的最终结果。
* `last` 停止当前语句块内的 rewrite 检测。带有 last 标识位的 rewrite 语句被执行时，生成的并非是最终的结果，仅仅是结束当前语句块内的本轮 rewrite 检测。
* `redirect` 返回 302 临时重定向，一般用于重定向到完整的 URL(包含`http:`部分)。
* `permanent` 返回 301 永久重定向，一般用于重定向到完整的 URL(包含`http:`部分)。

> Nginx `return`指令也可以用来返回 301 和 302 跳转，而且性能更好一些，因为`rewrite`指令有很多写法和规则，执行完所有正则匹配后，Nginx 才会知道这是一个重定向跳转。
> 
> Nginx `return`指令重定向的语句如下所示：`return 301 https://www.example.com$request_uri;`

## 二、提升

### 2.1 rewrite retry

Nginx rewrite 有个特性：rewrite 后的 url 可以再次进行 rewrite 检查，而且最多重试 10 次，10 次之后如果还没有终止的话，就会返回 HTTP 500 响应。

Nginx rewrite 的查找执行流程如下：

1. Sever 区块中如果有包含 rewrite 规则，则会最先执行，而且只会执行一次。
2. 然后再判断命中哪个 Location 的配置，并执行该 Location 中的 rewrite。
3. 当该 Location 中的 rewrite 执行完毕时，rewrite 查找并不会停止，而是根据 rewrite 处理后的 URL 再次判断 Location 并执行其中的配置。

这里就存在一个问题：如果 rewrite 写的不正确的话，是会在 Location 区块间造成无限循环的。所以 Nginx 才会加一个最多重试 10 次的上限。

比如下面这个例子：

```conf
location /download/ {
    rewrite  ^(/download/.*)/media/(.*)\..*$  $1/mp3/$2.mp3;
}
```

此时如果请求为`/download/eva/media/op1.mp3`则请求被 rewrite 到`/download/eva/mp3/op1.mp3`。而重写后的 URL 重新命中了`location /download/`。虽然这次命中该 location 后并没有命中其 rewrite 的正则表达式，但因为缺少终止 rewrite 的标志位，其仍会不停的重试该 location 的 rewrite 规则，直到达到 10 次上限之后返回 HTTP 500 响应。

### 2.2 last 和 break 的区别

rewrite 的`last`和`break`标识位虽然都是用来结束 rewrite 检测，但是两者的区别也比较明显：

* `break`是终止当前的 location 的 rewrite 检测，而且不再进行其他的 location 匹配，表示最终的重写结果。
* `last`是终止当前 location 的 rewrite 检测，但是会继续重试 location 匹配，并处理匹配块中的 rewrite 规则。

> 如果用编程语言来类比的话，`break`类似于一般编程语言中的`break`语句，表示跳出当前循环，并不再继续后续的循环；而`last`则表示`continue`语句，表示跳过当前循环的后续处理，但是会继续进入到下一次循环。

比如，对于下面的这个配置

```conf
location /download/ {
    rewrite  ^(/download/.*)/media/(.*)\..*$  $1/mp3/$2.mp3;
    rewrite  ^(/download/.*)/movie/(.*)\..*$  $1/avi/$2.mp3;
    rewrite  ^(/download/.*)/avvvv/(.*)\..*$  $1/rmvb/$2.mp3;
}
```

上面的各个 rewrite 语句中没有设置标识位。当访问`/download/acg/moive/UBW.avi`路径时：

* 如果为 rewrite 添加`last`标识位，则此时会在第二个 rewrite 处终止，并重试`location /download`区块，进入死循环。
* 如果为 rewrite 添加`break`标识位，则此时会在第二行的 rewrite 处终止，其结果为最终的的 rewrite 地址，不再继续匹配。

所以，对于 rewrite 来说：全局性的 rewrite 规则最好放在 server 区块中，并减少不必要的 location 区块；而 location 区块中的 rewrite 要想清楚是用 last 还是 break 标识位。

但是使用 break 标识位并非万无一失，因为使用 break 之后，Nginx 就不会再继续进行 location 的匹配了，此时可能会造成 Nginx 直接返回脚本文件的内容(如 PHP 脚本)。

### 2.3 实例

下面是 WordPress 的 Permalink + Supercache rewrite 实现：

> 该配置是针对访客的访问做缓存处理，让访客直接访问静态文件，从而实现访问加速功能。

```conf
server {
    listen   80;
    server_name  cafeneko.info www.cafeneko.info;
 
    access_log  ***;
    error_log   *** ;
 
    root   ***;
    index  index.php;
 
    # 如果浏览器支持 gzip 则在压缩前先寻找是否存在压缩好的同名 gz 文件避免再次压缩浪费资源
    gzip_static on;
 
    # 如果是直接请求某个真实存在的文件则用 break 语句停止 rewrite 检查
    if (-f $request_filename) {
        break;
    }  
	
    # 用 $request_uri 初始化变量 $supercache_uri.
    set $supercache_file '';
    set $supercache_uri $request_uri;
 
    # 如果请求方式为 POST 则不使用 supercache，这里用清空 $supercache_uri 的方法来跳过检测
    if ($request_method = POST) {
        set $supercache_uri '';
    }  

    # 因为使用了 rewrite 的原因，正常情况下不应该有 query_string(一般只有后台才会出现 query string)
    # 有的话则不使用supercache
    if ($query_string) {
        set $supercache_uri '';
    }  
	
    # 默认情况下 supercache 是仅对 unknown user 使用的，其他诸如登录用户或者评论过的用户则不使用
    # comment_author 是测试评论用户的 cookie，wordpress_logged 是测试登录用户的 cookie
    if ($http_cookie ~* "comment_author_|wordpress_logged_|wp-postpass_" ) {
        set $supercache_uri '';
    }

    # 如果变量 $supercache_uri 不为空，则设置 cache file 的路径
    if ($supercache_uri ~ ^(.+)$) {
        set $supercache_file /wp-content/cache/supercache/$http_host/$1index.html;
    }  

    # 检查 cache 文件是否存在，存在的话则执行 rewrite
    # 这里因为是 rewrite 到 html 静态文件，所以可以直接用 break 终止掉
    if (-f $document_root$supercache_file) {
        rewrite ^(.*)$ $supercache_file break;
    }  

    # 执行到此则说明不使用 suercache，进行 wordpress 的 permalink rewrite
    if (!-e $request_filename) {
        rewrite . /index.php last;
    }  

    location ~ \.php$ {  
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  ***$fastcgi_script_name;
        include        fastcgi_params;
    }  

    location ~ /\.ht {
        deny  all;
    }
}
```



