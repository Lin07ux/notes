> 转载：
> 
>   1. [Nginx变量使用方法详解](https://www.jianshu.com/p/44680c081ea0)
>   2. [nginx变量](http://blog.sina.com.cn/s/blog_594c47d00101dfyh.html)
>   3. [顺风详解Nginx系列—Ngx中的变量](https://cloud.tencent.com/developer/news/207263)

## 一、使用

在 Nginx 配置中，变量只能存放一种类型的值，就是字符串。而且可以将一个或多个变量、或者变量与字符串进行拼接组合，作为其他指令的参数值。

Nginx 中的变量是通过`$`符号加上变量名称来引用的，和 PHP 变量类似。而且变量的名称可以是英文字符、数字、下划线等。

### 1.1 拼接

变量之间可以进行拼接而组成其他的字符串，这对于修改配置值非常有效。拼接的方式就是将多个变量名依次写在一起即可。

变量最常见的用途就是用于作为其他指令的参数。比如，在配置 PHP FastCGI 中，常见有如下配置：

```conf
location ~ .+\.php($|/) {
   fastcgi_pass   api:9000;
   fastcgi_index  index.php;
   fastcgi_split_path_info         ^(.+\.php)(.*)$;
   fastcgi_param  PATH_INFO        $fastcgi_path_info;
   fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
   fastcgi_param  PATH_TRANSLATED  $document_root$fastcgi_path_info;
   include        fastcgi_params;
}
```

可以看到，这里使用了`$fastcgi_path_info`、`$document_root`、`$fastcgi_script_name`这三个变量（它们都是内置变量），而且进行了简单的变量拼接。

变量还能直接和非变量名的字符串进行拼接，而拼接的结果就是和字符串组装相同。比如，下面的的配置中，通过变量拼接字符串得到了响应结果：

```conf
location /document-root {
    return 200 "document root: $document";
}
```

### 1.2 输出`$`符号

由于 Nginx 是通过`$`符号来识别变量的，所以要想在字符串中直接输出`$`就不行了：它会被当做变量名称的起始标识符来解析。结果要么是使用变量值来替换`$`符号和变量名，要么是因为无法找到变量而报错。

而且，Nginx 中也并没有针对`$`符号转义的配置。

所以，要正确的输出`$`字符，就要通过不支持变量插值的模块配置指令专门构造出值为`$`的 Nginx 变量，然后再使用这个变量来输出`$`符号。

比如，下面通过标准模块 ngx_geo 提供的 geo 指令定义了变量`$dollar`，并将其值设置为`$`，然后就能通过`$dollar`来输出`$`符号了：

```conf
http {
    ...
    geo $dollar {
        default "$";
    }
    
    server {
        ...
        location /test-dollar {
          return 200 "This is a dollar sign: $dollar";
        }
    }
}
```

访问`/test-dollar`：

```shell
> curl localhost/test-dollar
This is a dollar sign: $
```

### 1.3 使用大括号插值

在变量插值上下文中，还有一种特殊情况，即：当引用的变量名之后紧跟着可作为变量名称的构成字符（如字母、数字、下划线）时，就需要使用大括号插值方式来消除歧义，避免 Nginx 将正确的变量名和其后的其他字符整体作为一个变量名来解析。

比如：

```conf
server {
    ...
    location /test-brace {
        set $first "hello ";
        return 200 "${first}world";
    }
}
```

这里使用自定义的变量`$first`的时候，其后紧跟着`world`。而`world`是一个合法的 Nginx 变量名的构成部分，如果直接写成`$firstworld`，那么 Nginx 的变量插值计算引擎会将之识别为引用了变量`$firstworld`。

为了避免这种歧义，Nginx 的字符串支持使用花括号把`$`后的变量名围起来。此时就能得到正确的输出了：

```shell
> curl localhost/test-brace
hello world
```

## 二、特性

### 2.1 可变性

Nginx 中的变量也存在可变和不可变之分，但是并没有显著的修饰符，所以从表面上是看不出来该变量是否可变的。

Nginx 在启动过程中有一个自检机制，当在配置文件中试图修改一个不可变变量时，Nginx 是不会顺利启动的。通过这种机制可以间接的判断某个变量是否可变。

比如，当试图修改`$host`变量时：

```conf
location /a {
    set $host "I am host";
    return 200 "$host";
}
```

在启动的时候，会得到如下错误：

```
[emerg] the duplicate "host" variable in /path/conf/nginx.conf:49
```

Nginx 中每个变量在被定义时都会被打上一个是否可被改变的标记，然后放到一个容器中。当后续试图再次定义同一个变量的时候，Nginx 会首先从这个容器中查找这个变量中。如果找到相同的变量则继续判断该变量是否存在可以改变的标记。如果有可修改标记，则会用新定义的变量值覆盖容器中的变量值；如果没有可修改标记，则会返回错误并终止 Nginx 的启动。

**ngx_http_core 模块中**的内置变量是先于`set`、`geo`等指令定义的变量的，而且其中**几乎所有的内置变量都是不可改变的，只有`$args`和`$limit_rate`这两个内置变量可以被改变**。

由于动态内置变量并不会被放入到容器中，所以它看起来是可以被改变的。但其实是因为**动态变量被重新定义后，就变成了一个普通的自定义变量**，而非动态变量了。因为动态变量的“定义”发生在所有内置变量和自定义变量之后。在 Nginx 中，一旦某个变量被认为是自定义变量或内置变量，后续就不会再被赋予动态变量的特性了。

比如：

```conf
location /a {
    set $arg_a "I am a";
    return 200 "$arg_a";
}
```

验证如下：

```shell
> curl localhost/a?a=b
I am a
```

### 2.2 可缓存性

Nginx 中所有的变量在定义的时候都会被关联上一个`get_handler()`方法。所有变量在第一次获取值的时候，都是通过这个 hander 方法获取的。后续再次获取变量值的时候，是否仍然调用该 handler 方法则取决于该变量是否可以被缓存。

不可缓存的变量在获取值的时候都是实时计算的。比如`$arg_`开头的动态变量，每次获取值的时候都会从查询参数中重新解析对应的值。

可缓存的变量并不会每次都调用 handler 方法。在它的整个生命周期中，如果这个变量没有被刷新过，那么自始至终只会调用一次。

Nginx 中用`set`指令定义的变量都是可缓存的，但`set`指令不会改变已有变量的缓存特性（比如内置变量）。

比如：

```conf
location /a {
    set $a "$arg_name";
    return 200 "$a = $arg_name";
}

location /b {
    set $a "$arg_name";
    set $args "name=lisi";
    return 200 "$a = $arg_name";
}

location /c {
    set $arg_name "$arg_name";
    set $a "$arg_name";
    set $args "name=lisi";
    return 200 "$a = $arg_name";
}
```

访问`/a`的结果和预期相同，如下：

```shell
> curl localhost/a?name=zhangsan
zhangsan = zhangsan
```

访问`/b`时因为`$arg_name`动态变量的不可缓存特性，导致其每次访问都要从`$args`中重新解析，所以结果如下：

```shell
> curl localhost/b?name=zhangsan
zhangsan = lisi
```

访问`/c`时，因为`$arg_name`被重新定义，就不再是一个动态变量了，是可缓存的。所以修改`$args`的值并不会影响最终的输出：

```shell
> curl localhost/c?name=zhangsan
zhangsan = zhangsan
```

### 2.3 可见性

**Nginx 变量一旦创建，其变量名的可见范围就是整个 Nginx 配置，甚至可以跨越不同虚拟主机的 server 配置块。**

Nginx 中的每一个变量都是全局可见的，但是又不是全局变量。所谓全局可见，是指不管变量定义在配置文件的哪个地方，它在整个配置文件中都是可见的，但这并不表示它是全局变量。

比如：

```conf
server {
    listen 8080;
  
    location /foo {
        return 200 "foo = [$foo]";
    }
    
    location /bar {
        set $foo 32;
        return "foo = [$foo]";
    }
}
```

此时，访问`/foo`和`/bar`时都能正常找到`$foo`变量：

```shell
> curl 'http://localhost/foo'
foo = []

> curl 'http://localhost/bar'
foo = [32]

> curl 'http://localhost/foo'
foo = []
```

这是因为，虽然`$foo`变量是在`/bar`这个 location 中通过`set`赋值（和声明）的，但是它在整个配置文件中都可见的。因此可以在`/foo`这个 location 中直接引用这个变量而不用担心 Nginx 会报错。

而又因为只在`/bar` location 中调用了`set`指令对`$foo`变量进行赋值，所以赋值操作只会在访问`/bar`的请求中执行。而请求`/foo`时得到的就总是空的字符串值，因为在`/foo`中只使用了`$foo`而未对其进行赋值。

Nginx 的变量之所以是全局可见的，但又不是全局变量，是因为变量的创建和赋值操作发生在全然不同的时间阶段：

* Nginx 变量的创建只能发生在 Nginx 配置加载的时候，或者说是 Nginx 启动的时候；
* Nginx 变量的赋值操作则只会发生在请求实际处理的时候。

所以，只要声明了变量，那么这个变量就会在 Nginx 启动后的整个配置中可见了；如果没有访问到变量赋值的地方，那么变量就只有空值。

这也意味着：不创建而直接使用变量会导致启动失败，而且无法在请求处理时动态的创建新的 Nginx 变量。

比如，下面的配置中使用了未创建的变量`$foo`：

```conf
location /bad {
    return 200 $foo;
}
```

此时 Nginx 服务器会拒绝加载配置：

```
[emerg] unknown "foo" variable
```

### 2.4 隔离性

虽然 Nginx 变量名的可见范围虽然是整个配置，但是**每个请求都有所有变量的独立副本**，彼此之间互不干扰。

比如在前面的示例中，请求了`/bar`时，虽然`$foo`被赋值为 32 了，但是丝毫不会影响后续对`/foo`请求时`$foo`的值（它仍然是空的）。

一个请求在处理过程中，**发生内部跳转前后**，都属于同一个请求，它们的**变量的值是继续保持的**。因为内部跳转只是当前 location 发生了变化，还是原来的那一套 Nginx 变量的副本。

**会触发内部跳转的指令有** ngx_http_core_module 模块的**`try_files`指令**和 ngx_rewrite 模块的**`rewrite`指令**。

> 所谓“内部跳转”就是在处理请求的过程中，于服务器内部，从一个 location 跳转到另一个 location 的过程。这不同于通过 HTTP 状态码 301/302 所进行的“外部跳转”，这是需求由 HTTP 客户端配合进行的。
> 
> 内部跳转类似于 C 语言中的 goto 语句。

比如：

```conf
server {
    listen 8080;
    
    location /foo {
        set $a "hello";
        rewrite ^ /bar;
    }
    
    location /bar {
        return 200 "a = [$a]";
    }
}
```

当访问`/foo`时会发生内部跳转，走到`/bar` location 上。所以当访问`/foo`时能够输出非空的`$a`变量值，但是直接访问`/bar`则得到的空值：

```shell
> curl localhost/foo
a = [hello]

> curl localhost/bar
a = []
```

由此可知，Nginx 变量值的声明周期是与当前正在处理的请求绑定的，而与 location 的变化无关。


## 二、内建变量

Nginx 内建变量最常见的用途就是获取关于请求或响应的各种信息。

## 2.1 uri 变量

`$uri`、`$document_uri`和`$request_uri`都是由 ngx_http_core_module 提供的，用于获取请求的 URI，都不包含主机名。

两者的区别在于：

* `$uri` 用来获取当前请求经过解码的 URI，并且不包含请求参数（`$query_string`）。而且，`$uri`会由于内部跳转或者使用 index 而发生变化；
* `$document_uri` 同`$uri`；
* `$request_uri` 则表示由 HTTP 客户端发送的 request uri 的未解码原值，并且包含请求参数（`$query_string`）。而且，`$request_uri`不会因内部跳转而发生改变。

比如，对于如下配置：

```conf
location /test-uri {
    return 200 "uri = $uri\r\nrequest_uri = $request_uri\r\n";
}

location /foo {
    rewrite ^ /test-uri;
}
```

对于不同的访问 uri 输出如下：

```shell
> http -b "localhost/test-uri"
uri = /test-uri
request_uri = /test-uri

> http -b "localhost/test-uri?a=1&c=abc"
uri = /test-uri
request_uri = /test-uri?a=1&c=abc

> http -b "localhost/test-uri/hello%20world?a=3&b=4"
uri = /test-uri/hello world
request_uri = /test-uri/hello%20world?a=3&b=4

> http -b "localhost/foo"
uri = /test-uri
request_uri = /foo

> http -b "localhost/foo?a=1&c=abc"
uri = /test-uri
request_uri = /foo?a=1&c=abc

> http -b "localhost/foo/hello%20world?a=3&b=4"
uri = /test-uri
request_uri = /foo/hello%20world?a=3&b=4
```

### 2.2 args 变量

针对每个请求带的查询参数，可以通过 args 相关变量来获取到。

* `$args` 表示是 GET 请求在请求行中的参数；
* `$query_string` 同`$args`；
* `$is_args` 当`$args`不为空时，其值值为`?`，否则为空字符串`""`；
* `$arg_xxx` 针对请求中的参数名称生成的以`arg_`开头的变量名，而且其值是未解码的原始形式的值。

需要特别注意的是`$arg_xxx`类型的变量，这是在 Nginx 核心中经过特别处理的，第三方 Nginx 模块是不能提供这样充满魔法的内建变量的。这类变量的名称是不区分大小写的，也就是说，参数名称无论是大写还是小写，Nginx 在匹配参数名之前，都会把原始请求中的参数名调整为全部小写的形式。

也就是说，对于请求中包含一个名为`name`的参数，或者名称为`Name`、`NAME`名称，都可以通过`$arg_name`命令来获取`name`参数的值。

比如，对于如下配置：

```conf
> curl "localhost/test-args"
args =
is_args =
name:
class:

> curl "localhost/test-args?name=Tom&class=3"
args = name=Tom&class=3
is_args = ?
name: Tom
class: 3

> curl "localhost/test-args?name=hello%20world&class=9"
args = name=hello%20world&class=9
is_args = ?
name: hello%20world
class: 9

> curl "localhost/test-args?NAME=Marry"
args = NAME=Marry
is_args = ?
name: Marry
class:

> curl "localhost/test-args?Name=Jimmy"
args = Name=Jimmy
is_args = ?
name: Jimmy
class:
```

如果相对 args 的值中的`%xx`这样的编码序列进行解码，可以用到第三方的 ngx_set_misc 模块提供的`set_unescape_uri`指令。该指令和`set`类似，可以声明和赋值一个变量。例如：

```conf
location /test-unescape-uri {
    set_unescape_uri $name $arg_name;
    set_unescape_uri $class $arg_class;
    retur 200 "name: $name\r\nclass: $class\r\n";
}
```

效果如下：

```shell
> curl "localhost/test-arg?name=hello%20world&class=9"
name: hello world
class: 9
```

### 2.3 请求资源路径变量

`$request_filename`表示当前请求资源的物理路径，计算表达式为：

```
$request_filename = $document_root$uri
```

这个变量是在`$request_uri`被解析处理得到了最终的`$uri`后，才结合`$document_root`生成的。

比如：

```conf
server {
    root /home/wwwroot/site/public;
}
```

当请求`/index.php/new/list?p=1&ps=10`时:

* `$request_uri = /index.php/news/list?p=1&ps=10`
* `$uri = /index.php/new/list`
* `$document_root = /home/wwwroot/site/public`
* `$request_filename = /home/wwwroot/site/public/index.php/news/list`

### 2.4 fastcgi 变量

fastcgi 相关的变量有很多，但大都较为简单明了，而`$fastcgi_script_name`和`$fastcgi_path_info`这两个变量较为复杂一些。

默认情况下，`$fastcgi_script_name = $uri`。但是为了美观，大部分情况下 url 都采用了 pathinfo 风格。所以如果直接把类似`index.php/news/index`传递给 PHP 等 CGI 客户端，那么对饮的`$_SERVER['SCRIPT_NAME']`的值就变成了`index.php/news/index`，这并不是一个有效的可执行文件名。

此时，可以使用`fastcgi_split_path_info`指令通过正则对请求的`$uri`进行拆解，对拆出两部分内容：

1. 将`$1`赋值给`$fastcgi_script_name`
2. 将`$2`赋值给`$fastcgi_path_info`

这样就能够在 CGI 客户端中得到真正要执行的脚本名称和脚本后携带的路径了。

这也是在 Nginx + PHP 服务器中常见的配置：

```conf
fastcgi_split_path_info ^(.+?\.php)(/.+)$;
fastcgi_param SCRIPT_NAME $fastcgi_script_name;
fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
fastcgi_param PATH_INFO $fastcgi_path_info;
fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
```

在对于这种配置下，请求`/news/index?p=1&ps=10`时：

1. 初始化时

    * `$request_uri = /news/index?p=1&ps=10`
    * `$uri = /news/index?p=1&ps=10`

2. 经过 location try_files 重写后：

    * `$uri = /index.php/news/index`
    * `$request_filename = /home/wwwroot/site/public/index.php/news/index`
    * `$fastcig_script_name = $uri`

3. 经过 pathinfo 解析后

    * `$fastcgi_script_name = /index.php`
    * `$fastcgi_path_info = /news/index`

### 2.5 其他预定义变量

* `$remote_addr` 数字格式的客户端的 IP 地址；
* `$remote_port` 客户端连接端口；
* `$remote_user` 请求的用户的名字，基本身份验证模块使用；
* `$binary_remote_addr` 二进制格式的客户端地址；

* `$content_length` 该变量的值等于请求头中的 Content-length 字段的值；
* `$content_type` 请求头中的 Content-Type 字段的值；

* `$host` 该变量的值等于请求头中 Host 的值。如果 Host 无效时，那么就是处理该请求的 server 的名称。在下列情况中，`$host`变量的取值不同于`$http_host`变量。
    
    * 当请求头中的 Host 字段未指定（使用默认值）或者为空值，那么`$host`等于 server_name 指令指定的值。
    * 当 Host 字段包含端口时，`$host`并不包含端口号。另外，从 0.8.17 之后的 nginx 中，`$host`的值总是小写。

* `$hostname` 为`gethostname`的返回值；
* `$http_cookie` 客户端 cookie 信息；
* `$http_user_agent` 客户端 agent 信息；
* `$http_xxx` 类似于`$arg_xxx`变量群，用来获取请求头的值，使用时会转换为小写，并且将`-`(破折号)转换为`_`(下划线)；

* `$request_body` 包含请求体的主要信息，与`proxy_pass`或者`fastcgi_pass`相关；
* `$request_body_file` 客户端请求主体信息的临时文件名；
* `$request_completion` 如果请求成功完成，那么值为`OK`。如果请求没有完成或者请求不是该请求系列的最后一部分，那么它的值为空。
* `$request_method`  该变量的值通常是 GET 或者 POST。

* `$scheme`  该变量表示 HTTP scheme（例如 HTTP，HTTPS），根据实际使用情况来决定，
   例如：`rewrite  ^ $scheme://example.com$uri redirect;`。

* `$server_name` 为 server 的名字。
* `$server_port` 为接收请求的端口。
* `$server_addr` 为于服务器的地址。为了避开系统钓鱼，必须在`listen`指令中使用`bind`参数。
* `$server_protocol` 为请求协议的值，通常是`HTTP/1.0`或者`HTTP/1.1`
 
* `$body_bytes_sent` 响应体的大小，即使发生了中断或者是放弃，也是一样的准确；

* `cookie_xxx` 类似于`$arg_xxx`变量群，用来获取 Cookie 中的值；

* `$document_root` 该变量的值为当前请求的中 root 指令中指定的值。

* `$sent_http_xxx` 类似于`$arg_xxx`变量群，用来获取响应头的值。

* `$limit_rate` 该变量可以限制连接速率。

* `$nginx_version`  当前运行的 nginx 的版本号

## 三、自定义变量

### 3.1 变量定义

**Nginx 中可以通过`set`、`map`、`geo`等指令来定义变量**。这些指令原本是用于赋值功能，但是有创建自定义变量的副作用，即：当作为赋值对象的变量尚不存在时，会自动创建该变量。

变量的名称可以由大写英文字母、小写英文字母、数字和下划线四种字符组成。如果变量名称包含一些非法的字符，在启动 Nginx 的时候就会报错：

```
[emerg] invalid variable name in nginx.conf:42
```

### 3.2 正则匹配捕获值

> 转摘：[Grab value from nginx location path](https://serverfault.com/questions/876659/grab-value-from-nginx-location-path)

在 Nginx 中的，正则表达式中，任何处于圆括号中的常规表达式都会被捕获为变量值，变量的名称为`$n`格式，其中`n`为匹配的序号。比如，第一个圆括号中捕获的值会存放在`$1`变量中，其次类推。

使用这种方法，也可以在 location 的匹配规则中捕获想要的值，比如：

```conf
location ~ ^/(users|admins)/v2/(.+)$ {
    proxy_pass http://app.domain.com/api/$1/v2/$2;
}
```

这里，`$1`的值即为`users`或`admins`，`$2`的值即为`/users/v2/`之后的所有字符。

需要注意的是：**每次使用正则表达式进行匹配的时候，就会重置`$n`的值。**

所以在使用`$1`、`$2`等值的时候，需要注意对应的作用域。比如：

```conf
location ~ ^/(users)/v2/(.+)$ {
    if ($http_user_agent ~ 'Googlebot') {
        # Does not work: $1 and $2 are undefined in this scope.
        proxy_pass http://app.domain.com/api/$1/v2/$2;
    }
}
```

这个配置中，`if`块中`$1`和`$2`是未定义状态，因为`if`语句中重新使用了正则表达式，而这个表达式并没有对应的圆括号。

为了使用 location 的正则表达式中的`$1`和`$2`的值，可以使用自定义变量来暂存：

```conf
location ~ ^/(users)/v2/(.+)$ {
    set $base $1;
    set $resource $2;
    if ($http_user_agent ~ 'Googlebot') {
        proxy_pass http://app.domain.com/api/$base/v2/$resource;
    }
}
```


