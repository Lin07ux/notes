### Interactive shell

使用 docker-compose 启动一个 php-fpm 容器的时候提示：

```
Interactive shell
```

可以通过为 docker-compose 文件添加`stdin_open`和`tty`来避免这个错误：

```yaml
php:
    image: php:7.4.9-fpm-alpine3.12
    container_name: php74
    ports:
        - "9000:9000"
    volumes:
        - /wwwroot/site/wordpress:/var/www/html
    stdin_open: true
    tty: true
```

这两个配置的意思为：

* `stdin_open` 相当于在 docker 运行的时候指定`-i`参数。
* `tty` 相当于在 docker 运行的时候指定`-t`参数。

### host not found in upstream

当在 docker-compose 中同时使用 php-fpm 和 nginx 对外提供服务的时候，nginx 配置文件中，对 php 的代理配置如下：

```conf
location ~ \.php$ {
   fastcgi_split_path_info ^(.+\.php)(/.+)$;
   fastcgi_pass php:9000;
   fastcgi_index index.php;
   include fastcgi_params;
}
```

启动的时候会提示：`host not found in upstream php`。这一般是由两个原因引起的：

1. nginx 应该依赖于 php 容器，在 php 容器启动之后再启动，否则 nginx 在启动的时候将找不到 php 容器。
2. nginx 容器和 php 容器应该使用同一个网络，否则也将无法通过网络正确连接到 php 容器中。

对应的修改主要为：

1. 在 nginx 容器的配置中增加`depends_on`配置；
2. 在 nginx、php 容器和 docker-compose 中都配置相同的网络。

例如：

```yaml
version: "3.3"

services:
  web:
    image: nginx:alpine
    container_name: nginx
    restart: always
    ports:
      - "8080:80"
    volumes:
      - ./vhosts:/etc/nginx/conf.d
      - ~/code/jzg-admin:/wwwroot/jzg-admin
    depends_on:
      - jzg-admin
    networks:
      phpnet:

  jzg-admin:
    build: .
    container_name: jzg-admin
    restart: always
    stdin_open: true
    tty: true
    volumes:
      - ~/code/jzg-admin:/wwwroot/jzg-admin
    networks:
      phpnet:

networks:
  phpnet:
```

### nginx 转发给 php 后代码未执行

由于 nginx 自身无法执行 php 脚本，所以需要 nginx 将对 php 脚本的请求转发到 php 容器中执行。但有时候会遇到 nginx 能正常将脚本转发过去却未能正常执行的问题，或者报脚本不存在。

这一般是由于 nginx 和 php 容器中，代码的文件路径不一致导致的，将代码在 nginx 和 php 容器中挂载到相同的路径即可。

### PHPStorm XDebug

> [Docker, PhpStorm & Xdebug: Can't find source position error](https://stackoverflow.com/questions/48977304/docker-phpstorm-xdebug-cant-find-source-position-error)

当在 docker 中安装了 xdebug 时，为了使 PHPStorm 能够配合调试，需要对 PHPStorm 进行一些 xdebug 方面的配置。

配置完成之后，如果依旧出现“Can't find source position error”，那么考虑给 docker 添加一个环境变量：

```yaml
php:
    image: php:7.2.33-fpm-alpine3.12
    environment:
      - PHP_IDE_CONFIG=serverName=0.0.0.0:5092
```

这里的`serverName`的值与 PHPStorm 中的`Settings -> Languages & Framework -> PHP -> Servers`中服务器的名称要一致。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/ZZDDadfgdfg2edd-5.png)

