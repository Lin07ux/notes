## 一、生产环境配置

### 1.1 composer

首先使用`composer install --optimize-autoloader`安装依赖。

### 1.2 设置目录权限

配置项目目录中的`storage/`和`bootstrap/cache/`两个目录可以被 PHP-FPM 读写：

```shell
chown -R apache:apache storeage
chown -R apache:apache bootstrap/cache
```

> 如果 PHP-FPM 的用户和属组不是 apache，则需要改成正确的用户和属组。

### 1.3 设置密钥

拷贝`.env.example`为一个新的文件`.env`，并根据实际情况进行配置。配置好之后，还需要生成应用密钥，用于保护加密信息：

```shell
php artisan key:generate
```

> 项目运行之后，应将`.env`做一个备份，避免加密密钥发生变化，否则可能会造成一些数据无法解析。


## 二、优化

### 2.1 配置优化

将应用部署到生产环境时，记得在部署过程中运行 Artisan 命令`config:cache`：

```shell
php artisan config:cache
```

这个命令可以将所有 Laravel 的配置文件合并到单个文件中缓存，此举能大大减少框架在加载配置值时必须执行的系统文件的数量。

### 2.2 路由优化

通过下面的命令，可以将所有路由注册减少为缓存文件中的单个方法调用，以达到当应用程序在注册数百条路由时，提高路由注册的性能：

```shell
php artisan route:cache
```

