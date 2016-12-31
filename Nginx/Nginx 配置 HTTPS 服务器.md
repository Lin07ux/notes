### 基本配置
要配置 HTTPS NGINX 服务器，必须在配置文件`server`块中的监听指令`listen`后启用`ssl`参数，并且指定服务器证书`ssl_certificate`和私钥`ssl_certificate_key`的位置：

```conf
server {
    listen              443 ssl;
    server_name         www.example.com;
    ssl_certificate     www.example.com.crt;
    ssl_certificate_key www.example.com.key;
    ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers         HIGH:!aNULL:!MD5;
    ...
}
```

> 服务器证书是一个公共实体，它被发送给连接到服务器的每一个客户机。私钥是一个安全实体，应该存储在具有受限访问的文件中，但它必须可被 nginx 主进程读取。私钥也可以存储在与服务器证书相同的文件中。

指令`ssl_protocols`和`ssl_ciphers`可用于限制仅包括强版本和密码的 SSL/TLS 连接。默认情况下，Nginx 使用`ssl_protocols TLSv1 TLSv1.1 TLSv1.2`版本和`ssl_ciphers HIGH:!aNULL:!MD5`密码，因此通常不需要显式地配置它们。

> 注意，这些指令的默认值已经变更好几次了。


### HTTPS 服务器优化
SSL 操作会消耗额外的 CPU 资源。最耗 CPU 的操作是 SSL 握手。

有两种方法来最小化每个客户端执行这些操作的次数：第一是通过启用`keepalive_timeout`参数来让这些连接在一个连接中发送多个请求，第二是重用 SSL 会话参数，以避免并行和后续连接的 SSL 握手。这些会话存储在 NGINX 工作程序的共享 SSL 会话缓存中，并由`ssl_session_cache`指令配置。

1M 的高速缓存包含大约 4000 个会话。默认的缓存超时时间为 5 分钟。它可以通过使用`ssl_session_timeout`指令增大。下面是针对具有 10M 共享缓存的多核心系统的优化示例配置：

```xml
worker_processes auto;
http {
    ssl_session_cache   shared:SSL:10m;
    ssl_session_timeout 10m;

    server {
        listen              443 ssl;
        server_name         www.example.com;
        keepalive_timeout   70;

        ssl_certificate     www.example.com.crt;
        ssl_certificate_key www.example.com.key;
        ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers         HIGH:!aNULL:!MD5;
        ...
    }
}
```


### SSL 证书链
有些浏览器可能警示由知名证书颁发机构签名的证书，而其他浏览器却能无问题的接受这些证书。这是因为这些证书颁发机构使用了中间证书来签署服务器证书，所签署的证书不存在于特定浏览器发行时内置的可信证书颁发机构颁发的证书库中。在这种情况下，颁发机构提供一组与颁发的服务器证书(根证书)串接的捆绑证书，并让服务器证书(根证书)出现在合并后文件(证书链）的捆绑证书之前：

```shell
$ cat www.example.com.crt bundle.crt www.example.com.chained.crt
```

生成的证书链文件应该放在`ssl_certificate`指令之后：

```conf
server {
    listen              443 ssl;
    server_name         www.example.com;
    ssl_certificate     www.example.com.chained.crt;
    ssl_certificate_key www.example.com.key;
    ...
}
```

如果根证书和捆绑证书使用了错误的链接顺序，Nginx 将会启动失败并显示如下错误信息：

```
SSL_CTX_use_PrivateKey_file(" ... /www.example.com.key") failed
   (SSL: error:0B080074:x509 certificate routines:
    X509_check_private_key:key values mismatch)
```

因为 Nginx 尝试去使用私钥与捆绑后证书的第一个证书验证而不是它本该去验证的服务器证书。


### 基于名称的 HTTPS 服务器
当配置两个或多个 HTTPS 虚拟主机服务器侦听单个 IP 地址时会出现常见问题：

```conf
server {
    listen          443 ssl;
    server_name     www.example.com;
    ssl_certificate www.example.com.crt;
    ...
}

server {
    listen          443 ssl;
    server_name     www.example.org;
    ssl_certificate www.example.org.crt;
    ...
}
```

使用这种配置，浏览器接收默认服务器的证书，即"www.example.com" 而不管请求的实际服务器名称，这是由SSL协议行为造成的。

SSL 连接建立在浏览器发送 HTTP 请求之前，这时候 Nginx 还不知道请求的服务器名称。因此，它只能提供默认的服务器证书。

解决此问题最古老和最可靠的方法是为每个 HTTPS 虚拟主机服务器指定一个单独的 IP 地址：

```conf
server {
    listen          192.168.1.1:443 ssl;
    server_name     www.example.com;
    ssl_certificate www.example.com.crt;
    ...
}

server {
    listen          192.168.1.2:443 ssl;
    server_name     www.example.org;
    ssl_certificate www.example.org.crt;
    ...
}
```

还有其他方法允许在几个 HTTPS 虚拟主机服务器之间共享单个 IP 地址。然而，他们都有自己的缺点。

其中一种方法是在证书的`SubjectAltName`字段中使用多个名称，例如`www.example.com`和`www.example.org`。但是，`SubjectAltName`字段长度有限。

另一种方法是使用带有通配符名称的证书，例如`*.example.org`。通配符证书能保护指定域的所有子域，但只限一个级别。此证书与`www.example.org`匹配，但不匹配`example.org`和`www.sub.example.org`。

这两种方法也可以结合。证书可以在`SubjectAltName`字段中包含完全匹配和通配符名称，例如`example.org`和`*.example.org`。

最好在配置文件的`http`区块中放置具有多个名称的证书文件及其私钥文件，以在所有其下的虚拟主机服务器中继承其单个内存副本：

```conf
ssl_certificate     common.crt;
ssl_certificate_key common.key;

server {
    listen          443 ssl;
    server_name     www.example.com;
    ...
}

server {
    listen          443 ssl;
    server_name     www.example.org;
    ...
}
```

### 转摘
[Nginx：配置HTTPS 服务器](http://www.zcfy.cc/article/nginx-configuring-https-servers-2278.html)

