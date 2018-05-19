## 一、介绍

Nginx 本身就提供了 Web 服务，对于静态文件，能够快速高效的返回给用户。也就是说，一般情况下，Nginx 就支持文件下载的。那么为什么还需要用到 X-Accel 来提供文件下载呢？

### 1、遇到的问题

很多时候网站需要提供下载文件的功能，如果文件是可以通过一个固定链接公开获取的，那么只需将文件存放到 webroot 下的目录里就好。但大多数情况下需要做权限控制，例如下载私人文件、付费内容等。这时通常会借助于脚本代码来控制权限，再提供下载，如下面的代码所示：

```php
<?php
  // 用户身份认证，若验证失败跳转
  authenticate();
  // 获取需要下载的文件，若文件不存在跳转
  $file = determine_file();
  // 读取文件内容
  $content=file_get_contents($file);
  // 发送合适的 HTTP 头
  header("Content-type: application/octet-stream");
  header('Content-Disposition: attachment; filename="' . basename($file) . '"');
  header("Content-Length: ". filesize($file));
  echo $content; // 或者 readfile($file);
```

这样无疑会增加服务器的负担。如果遇到一些访客下载速度巨慢，就会造成大量资源被长期占用得不到释放，很快后端程序就会因为没有资源可用而无法正常提供服务。

对于动态程序来说，提供文件下载是其一个弱点，而对于 Nginx 来说，则是它的强项。在这种形式下能不能让 Nginx 来完成静态资源的下载呢？答案是可以的。而这是通过`X-Sendfile`功能实现的。

### 2、什么是`X-Sendfile`

`X-Sendfile`是一种将文件下载请求由后端应用转交给前端 web 服务器处理的机制，它可以消除后端程序既要读文件又要处理发送的压力，从而显著提高服务器效率，特别是处理大文件下载的情形下。

`X-Sendfile`通过一个特定的 HTTP header 来实现：在`X-Sendfile`头中指定一个文件的地址来通告前端 web 服务器。当 web 服务器检测到后端发送的这个 header 后，它将忽略后端的其他输出，而使用自身的组件（包括缓存头、断点重连等优化）机制将文件发送给用户。

**使用`X-Sendfile`将允许下载非 web 目录中的文件（例如`/root/`）。**

不过，在使用`X-Sendfile`之前，我们必须明白这并不是一个标准特性，在默认情况下它是被大多数 web 服务器禁用的。而不同的 web 服务器的实现也不一样，包括规定了不同的`X-Sendfile`头格式。如果配置失当，用户可能下载到 0 字节的文件。

不同的 web 服务器实现了不同的 HTTP 头：

|    Sendfile Header   |             Web Server            |
| -------------------- | --------------------------------- |
| X-Sendfile           | Apache / Lighttpd v1.5 / Cherokee |
| X-LIGHTTPD-send-file | Lighttpd v1.4                     |
| X-Accel-Redirect     | Nginx / Cherokee                  |

> 使用`X-SendFile`的缺点是你失去了对文件传输机制的控制。例如如果你希望在完成文件下载后执行某些操作，比如只允许用户下载文件一次，这个`X-Sendfile`是没法做到的，因为后台脚本并不知道下载是否成功。

那么，在 Nginx 上实现前面的需求，就是需要使用`X-Accel`模块和`X-Accel-Redirect`头了。

## 二、X-Accel

使用 X-Accel 模块实现上述需求的时候，具体的处理流程如下：

```
                                                          +---------------+
        GET /file/name             proxy request          |      check    |
Client ----------------> Nginx ----------------------> Backend <----------+
  ∧                      |   ∧                               |
  |   download           |   |  X-Accel-Redirect: /path/name |
  +----------------------+   +-------------------------------+
```

Nginx 默认支持 X-Accel 模块，不需要额外加载，只是需要在 Nginx 中做一些特别的配置，而且后端代码需要返回的 HTTP 头为`X-Accel-Redirect`。

### 1 后端返回

以 PHP 为例，在后端 PHP 中做完相关验证之后，需要发送一个`X-Accel-Redirect`头，该头的值为提供给用户下载的文件的路径，如下所示：

```php
<?php
  $filePath = '/protected/iso.img';
  header('Content-type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . basename($file) . '"');
  //让Xsendfile发送文件
  header('X-Accel-Redirect: '.$filePath);
```

### 2 Nginx 配置

在 Nginx 中的配置需要注意以下几点：

1. `location`**必须**被定义为`internal`，使其只能在 Nginx 内部访问，不能用浏览器直接访问，防止未授权的下载；
2. 如果在`location`中使用`alias` 一定要注意目录结尾的`/`；
3. 要注意`location`匹配时尽量只用目录名。

下面是一些示例：

```conf
# 用户下载的文件在服务器上的位置为：/some/path/protected/iso.img
location /protected/ {
    internal;
    root /some/path;
}

# 用户下载的文件在服务器上的位置为：/other/path/iso.img
location /protected/ {
    internal;
    alias /other/path/;  # 注意最后的斜杠
}

# 也可以代理到其他的服务器中
location /protected/ {
    internal;
    proxy_pass http://127.0.0.2;
}
```

## 三、参考

1. [Nginx与X-Sendfile](http://www.cnblogs.com/duanxz/p/4254945.html)
2. [在Nginx中使用X-Sendfile头提升PHP文件下载的性能（针对大文件下载）](http://www.jb51.net/article/51854.htm)
3. [nginx启用sendfile之高级篇](https://www.oschina.net/question/54100_33185)

