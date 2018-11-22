map 指令用来创建变量，但是仅在变量被接受的时候执行视图映射操作，对于处理没有引用变量的请求时，这个模块并没有性能上的缺失。

> map 指令是`ngx_http_map_module`模块提供的。默认情况下，nginx 默认有加载这个模块，除非人为的`--without-http_map_module`。`ngx_http_map_module`模块可以创建变量，这些变量的值与另外的变量值相关联。允许分类或者同时映射多个值到多个不同值并储存到一个变量中。

### map 指令使用说明
* 语法: `map $var1 $var2 { ... }`
* 默认值：无
* 配置段：http
* 作用：按照 map 快中的映射关系，获取与`$var1`匹配到的模式对应的值，赋值给`$var2`。

语法中，大括号内是为变量设置的映射表。映射表由两列组成，匹配模式和对应的值。

匹配模式可以是一个简单的字符串或者正则表达式，使用正则表达式要用('~')。

> 一个正则表达式如果以`~`开头，表示这个正则表达式对大小写敏感。以`~*`开头，表示这个正则表达式对大小写不敏感。

示例：

```conf
map $http_user_agent $agent {
    default "";         # 默认值
    ~curl curl;         # 大小写敏感匹配curl
    ~*apachebench" ab;  # 大小写不敏感匹配
}
```

正则表达式里可以包含命名捕获和位置捕获，这些变量可以跟结果变量一起被其它指令使用。但是**不能在 map 块里面引用命名捕获或位置捕获变量**。如果源变量值包含特殊字符如`~`，则要以`\`来转义。而结果变量可以是一个字符串也可以是另外一个变量。

```conf
map $uri $value {
    # 下面的写法是正确的
    /ttlsa_com                   /index.php;
    ~^/ttlsa_com/(?<suffix>.*)$  /boy/;
    ~/fz(/.*)                    /index.php?;
    
    # 下面的写法是错误的
    ~^/ttlsa_com/(.*)             /boy/$1;
    
    # 转义特殊字符
    \~Mozilla                     222;                       
}
```

map 指令块中有三个关键字：

* `default`： 指定如果没有匹配结果将使用的默认值。当没有设置`default`时，将会用一个空的字符串作为默认的结果。
* `hostnames`： 允许用前缀或者后缀掩码指定域名作为源变量值。这个参数必须写在值映射列表的最前面。
* `include`： 包含一个或多个含有映射值的文件。

### 匹配顺序
如果匹配到多个特定的变量，如掩码和正则同时匹配，那么会按照下面的顺序进行选择：

1. 没有掩码的字符串
2. 最长的带前缀的字符串，例如: `*.example.com`
3. 最长的带后缀的字符串，例如：`mail.*`
4. 按顺序第一个先匹配的正则表达式 （在配置文件中体现的顺序）
5. 默认值

### 其他相关设置
**map_hash_bucket_size**

语法：`map_hash_bucket_size size;`
默认值：`map_hash_bucket_size 32|64|128;`
配置段：http
作用：指定一个映射表中的变量在哈希表中的最大值，这个值取决于处理器的缓存。

**map_hash_max_size**

语法：`map_hash_max_size size;`
默认值：`map_hash_max_size 2048;`
配置段：http
作用：设置映射表对应的哈希表的最大值。

### 实例
#### 简单示例
下面配置中，使用 map 指令将`$http_user_agent`(用户类型)按照一定规则分析出结果并赋值给`$agent`变量，之后就可以使用 Nginx 中的 echo 模块进行输出：

```conf
http {
    map $http_user_agent $agent {
        ~curl curl;
        ~*chrome chrome;
    }
    
    server {
        listen       8080;
        server_name  test.ttlsa.com;
 
        location /hello {
            default_type text/plain;
            echo http_user_agent: $http_user_agent;
            echo agent: agent:$agent;
        }
    }
}
```

我们在命令行中使用 curl 来访问，可以得到如下的输出：

```
> curl 127.0.0.1:8080/hello

http_user_agent: curl/7.15.5 (x86_64-redhat-linux-gnu) libcurl/7.15.5 OpenSSL/0.9.8b zlib/1.2.3 libidn/0.6.5
agent: curl
```

当用 Chrome 浏览器访问的时候，可以看到类似如下的结果：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1473948258181.png" width="433"/>


当用其他浏览器，如 IE 浏览器访问的时候，会有类似如下的输出：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1473948310822.png" width="323"/>

#### 进阶示例
下面的示例演示了如何使用 map 指令块中正则表达式中的位置捕获：在 map 指令块中，正则匹配中，使用了一个小括号，从而生成了一个位置捕获`$1`。这个位置捕获虽然不能再 map 指令块中的值中使用，但是可以在其他的位置使用。

```conf
http {
    map $uri $match {
        ~^/hello/(.*) http://www.ttlsa.com/;
    }
    
    server {
        listen       8080;
        server_name  test.ttlsa.com;
 
        location /hello {
            default_type text/plain;
            echo uri: $uri;
            echo match: $match;
            echo capture: $1;
            echo new: $match$1;
        }
    }
}
```

当我们访问的 URI 类似于`/hello/aaa/bbb`的时候，会有类似如下的输出：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1473948546062.png" width="241"/>


### 转载
[nginx map使用方法](http://www.ttlsa.com/html/3206.html)


