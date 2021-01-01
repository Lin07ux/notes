> 转摘：[实战多阶段构建 Laravel 镜像](https://yeasy.gitbook.io/docker_practice/image/multistage-builds/laravel)。
> Laravel 基于 8.x 版本。

## 一、准备

新建一个 Laravel 项目或在已有的 Laravel 项目根目录下新建`.dockerignore`、`laravel.conf`文件。

### 1.1 设置构建忽略文件

`.dockerignore`文件用来指定在构建阶段不打包发送到 Docker 服务器端的文件/文件夹列表，内容如下：

```
.idea/
.git/

vendor/

node_modules/

public/js/
public/css/
public/mix-manifest.json

yarn-error.log

bootstrap/cache/*
storage/

# 自行添加其他需要排除的文件，例如 .env.* 文件
```

### 1.2 设置 Laravel 的 Nginx 配置

在`laravel.conf`文件中写入 nginx 配置。这里需要注意的是：PHP 文件的解析转发到了`laravel:9000`路径，这里的名称`laravel`在后续的启动中是需要用到的。

```conf
server {
  listen 80 default_server;
  root /app/laravel/public;
  index index.php index.html;

  location / {
      try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ .*\.php(\/.*)*$ {
    fastcgi_pass   laravel:9000;
    include        fastcgi.conf;

    # fastcgi_connect_timeout 300;
    # fastcgi_send_timeout 300;
    # fastcgi_read_timeout 300;
  }
}
```

## 二、编写 Dockerfile

下面根据构建的步骤，分别展现前端资源编译、PHP 依赖安装、整合资源文件、构建 Nginx 服务四个阶段的 Dockerfile 配置，并最终构建镜像、启动运行。

### 2.1 前端资源编译

Laravel 的前端资源需要使用 Node 来进行编译得到最终的 js、css 等文件：

```yaml
FROM node:alpine as frontend

COPY package.json webpack.mix.js /app/
COPY resources/ /app/resources/

# 构建前端资源
RUN set -x ; cd /app \
      && npm install --registry=https://registry.npm.taobao.org \
      && touch artisan \
      && mkdir -p public \
      && npm run production
```

### 2.2 安装 PHP 依赖

Laravel 使用 Composer 管理依赖，可以在这一阶段把所需要的依赖单独下载下来：

```yaml
FROM composer as composer

COPY database/ /app/database/
COPY composer.json composer.lock /app/

RUN set -x ; cd /app \
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
    && composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist
```

### 2.3 整合资源

这一阶段将前面两步中产生的前端资源和 PHP 依赖与项目代码合并到一起：

```yaml
FROM php:7.4-fpm-alpine as laravel

ARG LARAVEL_PATH=/app/laravel

COPY --from=composer /app/vendor/ ${LARAVEL_PATH}/vendor/
COPY . ${LARAVEL_PATH}
COPY --from=frontend /app/public/js/ ${LARAVEL_PATH}/public/js/
COPY --from=frontend /app/public/css/ ${LARAVEL_PATH}/public/css/
COPY --from=frontend /app/public/mix-manifest.json ${LARAVEL_PATH}/public/mix-manifest.json

RUN set -x ; cd ${LARAVEL_PATH} \
      && mkdir -p storage \
      && mkdir -p storage/framework/cache \
      && mkdir -p storage/framework/sessions \
      && mkdir -p storage/framework/testing \
      && mkdir -p storage/framework/views \
      && mkdir -p storage/logs \
      && chmod -R 777 storage \
      && php artisan package:discover
```

### 2.4 设置 Nginx 镜像

这一阶段就需要配置 Laravel 项目对应的 Nginx 配置，并将前端资源文件暴露给 Nginx 服务器：

```yaml
FROM nginx:alpine as nginx

ARG LARAVEL_PATH=/app/laravel

# 设置 Nginx 站点配置文件
COPY laravel.conf /etc/nginx/conf.d/
# 设置前端资源文件路径
COPY --from=laravel ${LARAVEL_PATH}/public ${LARAVEL_PATH}/public
```

### 2.5 构建镜像和运行

使用`docker build`命令分别构建得到 Laravel 和 Nginx 的镜像：

```shell
docker build -t my/laravel --target=laravel .
docker build -t my/nginx --target=nginx .
```

为了能让 my/laravel 和 my/nginx 启动的容器连通，需要先创建 Docker 网络，并在启动的时候指定网络名称：

```shell
# 创建网络
docker network create laravel
# 先启动 Laravel 容器
docker run -dit --name=laravel --network=laravel my/laravel
# 再启动 Nginx 容器
docker run -dit --name=nginx --network=laravel -p 8080:80 my/nginx
```

> 这里创建的 Docker 网络的名称和前面准备工作中`laravel.conf`文件中设置的`fastcgi_pass laravel:9000;`的名称是一致的。

此时，在本机中访问`127.0.0.1:8080`就可以看到 Laravel 项目首页了。

> Laravel 项目如果依赖其他的外部服务，如 Redis、MySQL 等，需要自行启动这些服务之后再测试。

### 三、附录

完整的 Dockerfile 文件如下：

```yaml
# 构建前端资源
FROM node:alpine as frontend

COPY package.json webpack.mix.js /app/
COPY resources/ /app/resources/

RUN set -x ; cd /app \
      && npm install --registry=https://registry.npm.taobao.org \
      && touch artisan \
      && mkdir -p public \
      && npm run production

# 安装 PHP 依赖
FROM composer as composer

COPY database/ /app/database/
COPY composer.json /app/

RUN set -x ; cd /app \
      && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
      && composer install \
           --ignore-platform-reqs \
           --no-interaction \
           --no-plugins \
           --no-scripts \
           --prefer-dist

# 整合项目资源
FROM php:7.4-fpm-alpine as laravel

ARG LARAVEL_PATH=/app/laravel

COPY --from=composer /app/vendor/ ${LARAVEL_PATH}/vendor/
COPY . ${LARAVEL_PATH}
COPY --from=frontend /app/public/js/ ${LARAVEL_PATH}/public/js/
COPY --from=frontend /app/public/css/ ${LARAVEL_PATH}/public/css/
COPY --from=frontend /app/public/mix-manifest.json ${LARAVEL_PATH}/public/mix-manifest.json

RUN set -x ; cd ${LARAVEL_PATH} \
      && mkdir -p storage \
      && mkdir -p storage/framework/cache \
      && mkdir -p storage/framework/sessions \
      && mkdir -p storage/framework/testing \
      && mkdir -p storage/framework/views \
      && mkdir -p storage/logs \
      && chmod -R 777 storage \
      && php artisan package:discover

# 构建 Nginx 镜像
FROM nginx:alpine as nginx

ARG LARAVEL_PATH=/app/laravel

COPY laravel.conf /etc/nginx/conf.d/
COPY --from=laravel ${LARAVEL_PATH}/public ${LARAVEL_PATH}/public
```

