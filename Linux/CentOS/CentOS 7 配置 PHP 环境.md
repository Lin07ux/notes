## 准备
### 配置防火墙
CentOS 7.0 默认使用的是 firewall 作为防火墙，这里改为 iptables 防火墙。

1. 关闭 firewall：

    ```shell
    systemctl stop firewalld.service    # 停止firewall
    systemctl disable firewalld.service # 禁止firewall开机启动
    ```

2. 安装 iptables 防火墙

    ```shell
    yum install iptables-services  # 安装
    vi /etc/sysconfig/iptables     # 编辑防火墙配置文件
    ```
    
    > 主要是添加 80 端口，必要的换，更换 ssh 端口为别的。`-A INPUT -m state --state NEW -m tcp -p tcp --dport 80 -j ACCEPT`。

### 关闭 SELINUX

```shell
vi /etc/selinux/config  # 编辑 selinux
setenforce 0            # 使配置立即生效
```

设置如下：

* `SELINUX=enforcing` 注释掉
* `SELINUXTYPE=targeted` 注释掉
* `SELINUX=disabled` 增加


## 安装服务

### 配置仓库源

安装服务之前需要先更新下 CentOS 的仓库源，因为系统自带的仓库中，包含的程序并非最新的。推荐源为 [Webtatic Yum 仓库](https://webtatic.com/projects/yum-repository/)。

这个仓库对 CentOS 7.x 和 CentOS 6.x 有两种不同的安装方式。

对于 7.x 系统，使用如下的方式：

```shell
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
```

对于 6.x 系统，使用如下方式：

```shell
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
```

另外，对于 CentOS 7.x 系统来说，可能这个仓库中没有 Nginx、MySQL。此时可以通过安装如下的仓库来安装：

```shell
# CentOS 7 Nginx 源
sudo rpm -Uvh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm

# CentOS 7 MySQL 源
rpm -ivh http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm
```

### 安装 Nginx

```shell
yum install -y nginx   # 安装 Nginx
systemctl enable nginx # 开机自启动
systemctl start nginx  # 启动 Nginx
```

### 安装 PHP 5.6

```shell
# 安装 PHP 5.6 及相关依赖和插件，如果要安装其他版本修改 56 为对应版本数字即可
# 比如，需要安装 7.1 版本就把 56 改成 71 即可
yum install -y php56w php56w-fpm php56w-opcache php56w-xml php56w-mcrypt php56w-gd php56w-devel php56w-mysql php56w-intl php56w-mbstring php56w-bcmath php56w-cli.x86_64 php56w-common.x86_64 php56w-ldap.x86_64 php56w-pdo.x86_64
```

安装好之后，就需要对 PHP 做一些基本的设置：

* 修改 PHP 的配置文件夹`/etc/php.ini`，设置时区`date.timezone = Asia/Chongqing`；
* 修改 php-fpm 的配置文件`/etc/php-fpm.d/www.conf`，如下：

    ```conf
    # 设置监听方式为 sock
    listen = /var/run/php-fpm/php-cgi.sock
    # 设置 php-fpm 的运行用户
    user = apache
    group = apache
    # 设置 sock 的连接权限为 web server 的用户
    listen.owner = nginx
    listen.group = nginx
    # 设置 session 的存储方式和路劲
    php_value[session.save_handler] = files
    php_value[session.save_path]    = /var/lib/php/session
    ```

> 注意：php-fpm 的运行用户或用户组需要有 session 的存储目录的读写权限，否则会出现无法正常使用 session 的问题。

### 安装 MySQL

```shell
yum -y install mysql-server # 安装 MySQL
systemctl enable mysqld     # 开启自启动
systemctl start mysqld      # 开启 MySQL 服务
mysql_secure_installation   # 初始化
```

### 部署项目

* 首先建立项目目录：

```sh
mkdir /usr/share/nginx/html/web/
```

* 然后设置 Git 来拉取仓库中的源代码：

```sh
cd web/
git init
git remote add origin git@<ip-or-host>:user/project
git pull -u origin master
```

* 再设置项目目录的访问权限：

```sh
#! /bin/sh

# 执行 git pull 之后，修改网站目录的访问权限
cd /usr/share/nginx/html/

echo "修改web的访问权限"
chmod -R 770 web/
chmod 775 web/

echo "修改web/的用户和属组"
chown -R root:apache web/

cd web/
echo "修改Public的访问权限"
chmod -R 775 Public/
```

> 设置项目源码目录(`web/`)和网站(`web/Public`)的根目录的访问权限为 775，源码目录中的其他文件和目录的权限设置为 770。且设置源码目录及其子目录的用户和属组为 root 和 php-fpm 的运行属组(`apache`)。
> 
> 这样设置是为了保证 Nginx 能访问网站根目录的内容，而不能访问其他的目录资源，避免源码外露。而保证 php-fpm 能对整个源码目录都能访问，从而能够进行源码的读取和文件的写入。

* 最后，在更新代码的时候，使用 git 来拉取新的源码，并强制覆盖；覆盖之后还要修改入口文件，关闭调试状态，并删除缓存：

```sh
#!/bin/sh

# 进入到项目目录中
cd /usr/share/nginx/html/htymmedia.cn/

# 下载远程仓库的最新的内容
git fetch --all

# 不做任何的合并 git reset 把HEAD指向刚刚下载的最新的版本
git reset --hard origin/master

# 删除缓存
rm -rf ./Public/*Runtime/*

# 提示修改入口文件
echo '请进入项目根目录，并修改入口文件的 APP_STATUS 为 false'
echo 
echo '  cd /usr/share/nginx/html/htymmedia.cn/Public/'
echo '  vim admin.php'
echo '  vim index.php'
echo '  vim wechat.php'
echo
echo '  (如果还有其他的入口，请都进行修改)'
```


