
## 预备知识
### yum 与 rpm 的区别
rpm 是由红帽公司开发的软件包管理方式，使用 rpm 我们可以方便的进行软件的安装、查询、卸载、升级等工作。但是 rpm 软件包之间的依赖性问题往往会很繁琐,尤其是软件由多个 rpm 包组成时。

Yum（全称为 Yellow dog Updater, Modified）是一个在 Fedora 和 RedHat 以及 SUSE 中的 Shell 前端软件包管理器。基於 RPM 包管理，能够从指定的服务器自动下载 RPM 包并且安装，可以自动处理依赖性关系，并且一次安装所有依赖的软体包，无须繁琐地一次次下载、安装。

Yum 的关键之处是要有可靠的 repository，顾名思义，这是软件的仓库，它可以是http 或 ftp 站点，也可以是本地软件池，但必须包含 rpm 的 header，header 包括了 rpm 包的各种信息，包括描述，功能，提供的文件，依赖性等。正是收集了这些 header 并加以分析，才能自动化地完成余下的任务。

也就是说，Yum 需要先配置好一个或多个 repository 源，才能找到并安装你所需要的 rpm 包。

### rpm 包的安装和管理
CentOS 上已经预安装了 rpm 包，但由于 CentOS 上安装的源都比较旧，所以我们也需要自己安装一些需要的 rpm 包源。

* 安装一个包：`rpm -ivh < rpm package name>`
* 升级一个包：`rpm -Uvh < rpm package name>`
* 移走一个包：`rpm -e [soft_name]`
* 有依赖时强力删除：``rpm -e --nodeps [soft_name]`
* 查询一个包是否被安装：`rpm -q < rpm package name>`
* 得到被安装的包的信息：`rpm -qi < rpm package name>`
* 列出该包中有哪些文件：`rpm -ql < rpm package name>`
* 列出服务器上的一个文件属于哪一个 RPM 包：`rpm -qf`
* 列出所有被安装的 rpm package：`rpm -qa`
* 查看是否安装有指定的软件：`rpm -qa | grep [soft_name]`
* 列出一个未被安装进系统的 RPM 包文件中包含有哪些文件：`rpm -qilp < rpm package name>`
* 可综合好几个参数一起用：`rpm -qil < rpm package name>`

> 安装参数：
    --force  即使覆盖属于其它包的文件也强迫安装 
    --nodeps 如果该RPM包的安装依赖其它包，即使其它包没装，也强迫安装。 

### Yum 管理和使用
* 升级所有包，改变软件设置和系统设置,系统版本内核都升级：`yum -y update`
* 升级所有包，不改变软件设置和系统设置，系统版本升级，内核不改变：`yum -y upgrade`
* 查看可以安装的程序的：`yum list | grep [soft_name]`
* 安装程序：`yum install [soft_name]`
* 安装程序并自动安装依赖：`yum install -y [soft_name]`
* 卸载软件包：`yum remove -y [soft_name]`

### 开启 EPEL 仓库
参考：[CentOS 开启 EPEL 仓库](http://www.tecmint.com/how-to-enable-epel-repository-for-rhel-centos-6-5/)

```shell
# RHEL/CentOS 7 64 Bit
wget http://dl.fedoraproject.org/pub/epel/7/x86_64/e/epel-release-7-8.noarch.rpm
rpm -ivh epel-release-7-8.noarch.rpm

# RHEL/CentOS 6 32-Bit
wget http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
rpm -ivh epel-release-6-8.noarch.rpm

# RHEL/CentOS 6 64-Bit
wget http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
rpm -ivh epel-release-6-8.noarch.rpm
```

> 除 EPEL 仓库之外，安装软件的时候，可能需要使用其他的仓库来获取较新的版本。

### 创建 web 服务账户和组
一般会将 web 服务都用一个不能登录的账户来运行，web 目录也都设置为该账户和组所有，避免权限问题。

一般会将 Nginx 和 PHP/PHP-FPM 的用户均设置为 nobody 用户和组，并将网站根目录的用户和属组设置为 nobody，当然，也可以新建一个不能登录系统的用户。

> 需要用到 ftp 向网站根目录上传文档的时候，可以添加 ftp 用户的 uid 和 web 服务的用户相同。

```shell
groupadd www
useradd  www -s /sbin/nologin -d /var/www/ -g www
```

添加了 www 账户之后，可以查看其 uid，在后面添加 ftp 用户的时候会用到：
` `


-------------------------------------------------------------------------------

## 安装 ftp 服务器
参考：[CentOS vsftpd 安装和配置](https://github.com/Lin07ux/notes/blob/master/CentOS/CentOS%20vsftpd%20%E5%AE%89%E8%A3%85%E5%92%8C%E9%85%8D%E7%BD%AE.md)

-------------------------------------------------------------------------------

## 安装 Nginx
默认情况下，CentOS 系统上的源里面的 Nginx 软件包不是很新，可以添加 Nginx 的软件源来找到新的 Nginx 包：

```shell
# CentOS7 Nginx 源
sudo rpm -Uvh http://nginx.org/packages/centos/7/noarch/RPMS/nginx-release-centos-7-0.el7.ngx.noarch.rpm

# 安装 Nginx
yum install -y nginx

# 开机自启动
chkconfig nginx on
```

然后配置 nginx 的运行账户和组及其他设置：

```shell
vim /etc/nginx/nginx.conf
user www www;
```

-------------------------------------------------------------------------------

## 安装 Apache
参考：
	[Magento Apache](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/apache.html)

默认情况下，CentOS 6.5 上已经安装好了 httpd 2.2.17 服务。

1. 如果没有安装，直接使用下面的命令安装即可：
	yum -y install httpd

2. 相关命令：
	service httpd start
	service httpd stop
	service httpd restart
	service httpd status

3. 开启 httpd 服务之后，就能通过 IP 访问到服务器了，可以看到关于 Apache 的页面。
	service httpd start
	chkconfig httpd on

4. 配置
	vi /etc/httpd/conf/httpd.conf

	KeepAlive off
	更改为
	KeepAlive on

	MaxKeepAliveRequests 100
	更改为
	MaxKeepAliveRequests 500 //为了增进效率则可以改大一点；

	# 允许重写和 .htaccess
	# 找到 <Directory /var/www/html>
	<Directory /var/www/html>
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		Allow from all
	<Directory>

5. 两个执行模块，默认使用 prefork 模块，如果想使用 worker 模块，使用如下操作：
	vi /etc/sysconfig/httpd

	找下如下内容：
	#HTTPD=/usr/sbin/httpd.worker
	去掉前面的 # 号即可：
	HTTPD=/usr/sbin/httpd.worker

	然后重启一下apache服务：
	service httpd restart

6. 进阶配置：禁止恶意域名指向你的服务器 IP
	解决办法一：
		新建一个虚拟主机：
		<VirtualHost *:80>
		ServerName 171.111.158.91 //更改为您自己服务器的IP地址；
		<Location />
		Order Allow,Deny
		Deny from all
		</Location>
		</VirtualHost>
	 
	解决办法二：
		新建第一个虚拟主机（默认没有定义的域名访问到的是第一个虚拟主机的内容）：
		<VirtualHost *:80>
		DirectoryIndex index.html index.htm index.php
		DocumentRoot /var/www/html/test //定义一个空目录，或者在该目录下放一个显示您需要注明的网站页面；
		<Directory /var/www/html/test>
		ServerName 171.111.158.91 //更改为您自己服务器的IP地址；
		//目录同上；
		Order allow,deny
		allow from all
		</Directory>
		</VirtualHost>

7. 日志文件
	日志文件位于 /etc/httpd/logs 文件夹中
	access_log   访问日志
	error_log    错误日志


-------------------------------------------------------------------------------


## 安装 PHP
参考：
	[Magento PHP](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-centos.html)

CentOS 上的源很久没有更新了，需要更新源之后才能安装 PHP 5.3 以后的版本。

1. 追加对应版本的源：

```shell
# CentOs 5.x
rpm -Uvh http://mirror.webtatic.com/yum/el5/latest.rpm
  
# CentOs 6.x
rpm -Uvh http://mirror.webtatic.com/yum/el6/latest.rpm
  
# CentOs 7.X
rpm -Uvh https://mirror.webtatic.com/yum/el7/epel-release.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
```

> 注意，不能安装错了源，否则会提示错误，无法安装其他程序了。

2. 查看可安装的 PHP 包
	使用 yum list 命令查看可安装的包(Packege)。也可以不看，直接安装。
	`yum list --enablerepo=remi --enablerepo=remi-php56 | grep php`
	这一步可能会执行的比较久

3. 安装 PHP 5.6
	安装好源之后，就可以用下面的命令安装 PHP 5.6，当然也可以指定其他的版本。
	`yum install -y php56w php56w-fpm php56w-opcache php56w-xml php56w-mcrypt php56w-gd php56w-devel php56w-mysql php56w-intl php56w-mbstring php56w-bcmath php56w-cli.x86_64 php56w-common.x86_64 php56w-ldap.x86_64 php56w-pdo.x86_64`

4. 查看 PHP 版本
	安装好 PHP 之后，就可以使用如下命令查看 PHP 的版本：
	`php -v`

5. 配置 PHP
    安装好之后，PHP 的配置文件 php.ini 的位置是 /etc/php.ini
    使用 vi 编辑是，可以在命令行下输入 / 和要查找的内容，然后按 Enter 即可查找。
    按 n 移动到下一个，按 N 移动到上一个。

    如，查找 intl，在命令行下输入下面的内容：
    /intl

    vi /etc/php.ini
    # 设置时区
    date.timezone = Asia/Chongqing
    # 取消自动获取内容
    always_populate_raw_post_data = -1
    # 关闭 asp 风格
    asp_tags = Off

    # 设置 php-fpm 设置
    # 这里也可以不设置，建议web服务设置一个统一的账户
    vim /etc/php-fpm.d/www.conf
    # 设置其中的 user 和 group
    user = www
    group = www
    # 设置监听程序的用户和组
    listen.owner = www
	listen.group = www

6. PHP 扩展库位置
	/usr/lib64/php/modules/

7. 查看默认加载的 php.ini 的位置
	php -i | grep php.ini

	输出类似如下：
		Configuration File (php.ini) Path => /etc
		Loaded Configuration File => /etc/php.ini 


-------------------------------------------------------------------------------


## 安装 MySQL
参考：
	[Magento MySQL](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/mysql.html)
	[CentOS7 MySQL](https://www.linode.com/docs/databases/mysql/how-to-install-mysql-on-centos-7#install-mysql)

1. 更新 yum 源

`yum -y update`

2. 添加 MySQL 源

`wget http://repo.mysql.com/mysql-community-release-el6-5.noarch.rpm && rpm -ivh mysql-community-release-el6-5.noarch.rpm`

> CentOS7 的源：http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm 

4. 安装 MySQL

```shell
# 下面这个命令会自动安装 MySQL-server 和 MySQL-client 等
yum -y install mysql-server
```

5. 开启 MySQL 服务并添加自动启动

```shell
# 第一次启动会有一些相关信息自动完成
service mysqld start
chkconfig mysqld on
```

6. 设置 root 密码和其他安全选项

```shell
# 执行下面的命令，然后根据提示进行
mysql_secure_installation
# (1). 提示输入密码，由于是第一次安装，没有密码，直接 Enter
# (2). 提示是否设置密码，输入 Y，然后 Enter
# (3). 设置密码，并确认密码
# (4). 提示是否删除匿名用户，输入 Y，然后 Enter
# (5). 提示是否禁止 root 用户远程登陆，输入 Y，然后 Enter
# (6). 提示是否删除 test 数据库，输入 Y，然后 Enter
# (7). 提示是否立即重新加载权限表，输入 Y，然后 Enter
```

7. 增加新用户，并设置权限

```shell
# 创建新用户并设置密码
# % 表示外部任何地址都能访问
mysql> create user 'magento'@'%' identified by '123456';
# 给新用户授权，使其能从外部登陆和本地登陆
# 并能管理数据库 magento 数据库 magento.* to 'magento'@'localhost' identified by '123456';
mysql> grant all privileges on magento.* to 'magento'@'%' identified by '123456';
# 刷新权限
mysql> flush privileges;
```

8. 检查 MySQL 数据库的引擎

```shell
mysql> show variables like 'storage_engine';
# 如果显示的结果不是 InnoDB 而是 MyISAM
# 则需要修改引擎为 InnoDB
mysql> exit;
service mysqld stop
# 编辑 my.cnf 文件
vim /etc/my.cnf
# [mysqld] 后加入
# default-storage-engine=InnoDB
```

9. 配置 MySQL

在 /etc/my.cnf 文件中至少加入下面的一段。否则在安装时，会无法连接数据库

```shell
# 打开配置文件
vi /etc/my.cnf
# 加入 [client] 段
[client]
socket = /var/lib/mysql/mysql.sock
```

10. MySQL 其他

日志文件：/var/log/mysqld.log

显示 MySQL 的配置：mysqld --print-defaults

-------------------------------------------------------------------------------
-------------------------------------------------------------------------------

## 其他
### 安装 phpMyAdmin
可以通过 phpMyAdmin 来管理数据库，更方便。
	# 下载相关的源
	# 如不能下载就先本地下载，然后传到服务器
	wget http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-8.noarch.rpm
	# 安装源
	rpm -ivh epel-release-6-8.noarch.rpm
	# 安装 phpMyAdmin
	yum -y install phpmyadmin
	# 配置
	vi /etc/httpd/conf.d/phpMyAdmin.conf
	# 找到下面的指令
	<Directory"/usr/share/phpmyadmin">
		Order Deny,Allow
		# 注释掉 Deny From all
		# Deny from all
		# Allow from 设置为 0.0.0.0
		Allow from 0.0.0.0
	</Directory>

###  创建定时任务
参考：
	[Magento Cron](http://devdocs.magento.com/guides/v2.0/config-guide/cli/config-cli-subcommands-cron.html)

Magento 2 的运行需要在服务器上执行一些定时任务，完成特定的任务。
	# 确定 php.ini 文件的位置
	php -i | grep php.ini
	# 用 root 用户创建定时任务
	crontab -u wwwuser -e
	# 此时会打开一个文本进行编辑
	# magento cron:run  执行 reindexes indexers, send automated e-mails, generates the sitemap, and so on. 
	*/1 * * * * php -c /etc/php.ini /var/www/html/magento2s/bin/magento cron:run 
	*/1 * * * * php -c /etc/php.ini /var/www/html/magento2s/update/cron.php 
	*/1 * * * * php -c /etc/php.ini /var/www/html/magento2s/bin/magento setup:cron:run
	# 切换到 wwwuser 用户
	# 在 /var/www/html/magento2/bin/ 目录下开启定时任务
	cd /var/www/html/magento2/bin/
	./magento cron:run

### 安装时出现 mysql 链接错误
这一般是由于瞬间导入太多数据而导致 mysqld 服务死锁。

解决办法有种，按顺序来尝试：
	1. 查看 mysqld 服务状态，并重启
	    service mysqld status
	    service mysqld restart
	2. 如果重启服务器不成功重启机器
		shutdown -r now
	3. 其他方法
		# 备份文件
		cp /var/lock/subsys/mysqld /root/mysqld
		rm /var/lock/subsys/mysqld
		# 关闭所有依赖于 mysql 的服务
		service httpd stop
		service otrs stop
		# 重启服务
		service mysqld restart
		service httpd restart
		service otrs restart


### 频繁出现 mysqld 服务死锁
在日志文件中，一般能看到这种信息：
	InnoDB: The log sequence numbers 133808751880 and 133808751880 in ibdata files do not match the log sequence number 133810301875 in the ib_logfiles!
	InnoDB: Database was not shutdown normally!
有时候能够自动恢复，但是经常是无法恢复而死锁。

一般是由于内存较小导致的。

有两种解决方法：
	1、增加交换空间
		一般阿里云服务器安装好 CentOS 之后，并不会添加交换区。
		此时可以先增加交换区之后，查看 mysqld 服务的情况。
	2、需要增加内存
		如果增加交换区之后没有效果，那么可以考虑增加内存。
		另外，还有可能是磁盘空间不足。


### 频繁出现 mysqld 服务被重启
在 /var/log/mysqld.log 中经常出现下面的信息：
	mysqld_safe Number of processes running now: 0
	mysqld_safe mysqld restarted
而网站也经常出现错误。

此时一般是由于 mysqld 占用的内存太多了，导致出现 OOM(out of memory)，而被内核 kill 了。

解决办法：
	重新检查 mysql 的配置文件，将一些值调的更小一些。

参考：
	[64MB VPS 上优化 MySQL](http://www.vpsee.com/2009/06/64mb-vps-optimize-mysql/)

```config
	[mysqld]
	port            = 3306
	socket          = /var/run/mysqld/mysqld.sock
	skip-locking

	key_buffer = 16K
	query_cache_limit = 256K
	query_cache_size = 4M
	max_allowed_packet = 1M
	table_cache = 8

	max_connections = 16
	thread_concurrency = 2

	sort_buffer_size = 64K
	read_buffer_size = 256K
	read_rnd_buffer_size = 256K
	net_buffer_length = 2K
	thread_stack = 64K

	[mysqldump]
	quick
	max_allowed_packet = 16M

	[mysql]
	no-auto-rehash
	#safe-updates

	[isamchk]
	key_buffer = 8M
	sort_buffer_size = 8M

	[myisamchk]
	key_buffer = 8M
	sort_buffer_size = 8M

	[mysqlhotcopy]
	interactive-timeout
```




