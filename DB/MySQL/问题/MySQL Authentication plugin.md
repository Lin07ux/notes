> 转摘：[解决MAC+ MySQL 8 错误：Authentication plugin 'caching_sha2_password' cannot be loaded](https://www.jianshu.com/p/9a645c473676)

### 1. 问题描述

在 Mac 中的 Navicat 中连接 MySQL 8.0 的时候，出现如下连接错误提示：

```
Authentication plugin 'caching_sha2_password' cannot be loaded: dlopen(/usr/local/mysql/lib/plugin/caching_sha2_password.so, 2): image not found
```

### 2. 问题原因

这不是客户端 Navicat 的原因，而是 MySQL 的版本兼容问题：

* MySQL 8.0 默认的认证方式是`caching_sha2_password`；
* MySQL 5.* 默认的认证方式是`mysql_native_password`。

### 3. 解决方案

解决这个问题只需要修改数据库的认证方式即可，有如下多种方式：

* 用终端连接 MySQL，修改如下指令进行修改相关用户的登录密码：
    
    ```sql
    ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'yourpassword';
    ```

* 修改`my.cnf`文件中的认证方式，然后重启 MySQL

    ```ini
    [mysqld]
    default_authentication_plugin=mysql_native_password
    ```

* Docker 中，修改 MySQL 镜像命令的启动配置项：

    ```yaml
    db:
        image: mysql:8.0.18
        command: --default-authentication-plugin=mysql_native_password
    ```

    > [mysql_native_password - Docker Hub](https://hub.docker.com/_/mysql)

