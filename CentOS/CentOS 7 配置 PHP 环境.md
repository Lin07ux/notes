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
### 安装 Nginx
默认情况下，CentOS 系统上的源里面的 Nginx 软件包不是很新，可以添加 Nginx 的软件源来找到新的 Nginx 包：

```shell
# CentOS7 Nginx 源
sudo rpm -Uvh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm

# 安装 Nginx
yum install -y nginx

# 开机自启动
systemctl enable nginx.service

# 启动 Nginx
systemctl start nginx
```

### 安装 PHP 5.6


```shell
# 添加源
rpm -Uvh https://mirror.webtatic.com/yum/el7/epel-release.rpm
# 或
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

# 安装 PHP 5.6 及相关依赖和插件
yum install -y php56w php56w-fpm php56w-opcache php56w-xml php56w-mcrypt php56w-gd php56w-devel php56w-mysql php56w-intl php56w-mbstring php56w-bcmath php56w-cli.x86_64 php56w-common.x86_64 php56w-ldap.x86_64 php56w-pdo.x86_64
```

安装好之后，就需要对 PHP 做一些基本的设置：


```shell
# 编辑 php.ini 文件
vi /etc/php.ini
# 设置时区
date.timezone = Asia/Chongqing
# 取消自动获取内容
always_populate_raw_post_data = -1
# 关闭 asp 风格
asp_tags = Off

# 设置 php-fpm
vim /etc/php-fpm.d/www.conf
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

> 注意：设置监听方式为 sock 后，需要确保运行 php-fpm 的用户或用户组(`apache:apache`)有读写及运行 sock 文件的权限，否则无法正常启动 php-fpm。
> 注意：设置监听方式为 sock 后，需要确保`listen.user`和`listen.group`中有一个是和 web server(Nginx 或 Apache) 的运行用户或组是相同的，否则会出现`connect() to unix:/var/run/php-fpm/php-cgi.sock failed (13: Permission denied) `的错误。web 页面会显示 500 错误。
> 注意：php-fpm 的运行用户或用户组需要有 session 的存储目录的读写权限，否则会出现无法正常使用 session 的问题。

### PHP 与 Nginx 的权限说明
在 LAMP 组合中，Nginx 要将对 php 文件的访问转发给 php-fpm 来进行处理，而在这个过程中，涉及到 Nginx、php-fpm 和网站目录之间的访问权限的问题。

* `Nginx --> php-fpm`  Nginx 需要能够将访问请求转发给 php-fpm 的 sock 文件，就需要对该 sock 文件有访问权限，而这个可以通过在 php-fpm 的配置文件 www.conf 中进行配置，就是`listen.user`和`listen.group`，设置其为 Nginx 的运行用户或属组即可。

* `php-fpm --> sock`  虽然 sock 文件是需要 php-fpm 来创建，但是这需要 php-fpm 对创建 sock 的目录有读写权限，也就是说，存放 sock 文件的目录的用户或属组需要设置为 php-fpm 的运行用户或属组(也就是 www.conf 文件中的`user`和`group`)，或者这个目录需要对任何用户都有读写权限。

* `php-fpm --> web root`  由于需要 php-fpm 来执行 php 文件，所以网站的根目录需要提供给 php-fpm 的运行用户或属组有读写和执行权限(写是因为需要 php 来设置缓存文件、上传文件等功能)。

* `Nginx --> web root`  Nginx 作为一个 web server，自然需要对网站目录有访问权限。不过可以仅仅赋予其读和执行的权限，而不必进行写。另外，目前 php 项目大都使用 MVC 框架，而框架中的文件夹并不需要被用户访问，只需要提供给用户一个公共目录即可。所以我们将网站的根目录(一般是 Public 目录)设置为能让 Nginx 读和执行，而项目中的其他目录和文件则禁止 Nginx 访问。


### 安装 MySQL


```shell
# 安装源
rpm -ivh http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm

# 安装
yum -y install mysql-server

# 开启自启动
systemctl enable mysql.service

# 开启服务
systemctl start mysql.service

# 初始化
mysql_secure_installation
```

