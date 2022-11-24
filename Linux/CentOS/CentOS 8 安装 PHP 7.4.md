> 转摘：[How to Install PHP 7.4 on CentOS 8](https://www.tecmint.com/install-php-on-centos-8/)

### 1. 添加仓库

在 CentOS 8 中安装 PHP 7.4 需要先添加对应的 EPEL & Remi 仓库。

首先，安装 EPEL 仓库：

```shell
dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
```

安装好之后，可以通过如下的命令来确认：

```shell
> rpm -qa | grep epel
# epel-release-8-7.el8.noarch
```

然后，安装 Remi 仓库：

```shell
dnf install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
```

安装好之后，可以通过如下的命令来确认：

```shell
> rpm -qa | grep remi
# remi-release-8.0-4.el8.noarch
```

### 2. 安装 PHP 7.4

```shell
# 查看当前可用的 PHP
dnf module list php

# 开启 Remi 仓库的 PHP 7.4 模块
dnf module enable php:remi-7.4

# 安装 PHP 相关组件
dnf install php php-cli php-common

# 查看已安装的 PHP 版本
php -v
```

