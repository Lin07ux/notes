
## 预备知识
### 更新 yum
首先需要更新 yum 源。

升级所有包，改变软件设置和系统设置,系统版本内核都升级
	yum -y update

升级所有包，不改变软件设置和系统设置，系统版本升级，内核不改变
	yum -y upgrade

### 查看已经安装的程序
CentOS 上已经预安装了很多软件，可以通过如下的方式查看是否安装有某种软件 soft_name：
	rpm -qa | grep [soft_name]
如，查看是否安装有 mysql：
	rpm -qa | grep mysql
如果有输出，则表示安装的有相应的程序。

### 删除已安装的程序
由于 CentOS 上安装的程序版本可能比较低，所以我们需要先删除掉相应的程序，然后再安装合适的版本。
	rpm -e [soft_name]
如，删除 mysql：
	rpm -e mysql

如果删除程序时，提示有依赖的其他文件，可以用下面的强力删除命令：
	rpm -e --nodeps mysql

### 查看可以安装的程序的
安装程序之前，可能我们需要先查看下是否可以安装需要的版本的程序，可以用如下的命令：
	yum list | grep [soft_name]


-------------------------------------------------------------------------------
-------------------------------------------------------------------------------


## 安装 FTP 服务
由于会经常需要将文件上传到服务器中，所以我们需要在服务器上安装一个 vsftpd 服务。

1. 安装 vsftpd
	yum -y install vsftpd

2. 设置 vsftpd 服务开机自启动
	chkconfig vsftpd on

3. 相关操作指令
	service vsftpd start		开启 vsftpd 服务
	service vsftpd restart		重启 vsftpd 服务
	service vsftpd stop			关闭 vsftpd 服务
	service vsftpd status		查看 vsftpd 服务的状态

4. 修改配置文件
	vi /etc/vsftpd/vsftpd.conf

	# 禁止匿名用户anonymous登录
	anonymous_enable=NO
	# 允许本地用户登录
	local_enable=YES
	# 让登录的用户有写权限(上传，删除)
	write_enable=YES
	# 默认umask
	local_umask=022
	# 把传输记录的日志保存到/var/log/vsftpd.log
	xferlog_enable=YES
	xferlog_file=/var/log/vsftpd.log
	xferlog_std_format=NO
	# 允许ASCII模式上传
	ascii_upload_enable=YES 
	# 允许ASCII模式下载
	ascii_download_enable=YES
	# 使用20号端口传输数据
	connect_from_port_20=YES

	# **接下来的三条配置很重要**
	# `chroot_local_user` 设置为 YES，那么所有的用户默认将被 chroot，
	# 也就用户目录被限制在了自己的 home 下，无法向上改变目录。
	# ★超重要：如果`chroot_local_user`设置为 YES，
	# 那么`chroot_list_file` 设置的文件里的用户，是不会被 chroot 的(即，可以向上改变目录)
	# ★超重要：如果`chroot_local_user`设置为 NO，
	# 那么`chroot_list_file` 设置的文件里的用户，是会被 chroot 的(即，无法向上改变目录)
	# `chroot_list_enable` 设置为 YES，即让 chroot 用户列表有效。
	chroot_local_user=YES
	chroot_list_enable=YES
	chroot_list_file=/etc/vsftpd/chroot_list
	# 新建这个 chroot_list 文件
	# touch /etc/vsftpd/chroot_list

	use_localtime=YES
	# 以standalone模式在ipv4上运行
	listen=YES
	# PAM认证服务名，这里默认是vsftpd，在安装vsftpd的时候已经创建了这个pam文件，
	# 在/etc/pam.d/vsftpd，根据这个pam文件里的设置，/etc/vsftpd/ftpusers
	# 文件里的用户将禁止登录ftp服务器，比如root这样敏感的用户，所以你要禁止别的用户
	# 登录的时候，也可以把该用户追加到/etc/vsftpd/ftpusers里。
	pam_service_name=vsftpd

	# 重启 vsftpd
	service vsftpd restart

5. 添加 ftp 用户
	设置好 vsftpd 服务之后，需要设置一个 ftp 账户用来登录 ftp。
	同时，可以给这个 ftp  账户设定相应的主目录 home，避免改动其他目录。
	当然，也可以创建多个 ftp 账户，分别管理不同的目录。

	# 在创建好相应的主目录之后，添加 ftp 账户
	# 创建一个名为 ftpuser 的账户，主目录为 /home/wwwroot/magento，组为 ftp，
	# 同时指定其不能用于登录系统
	useradd -d /home/wwwroot/magento -g ftp -s /sbin/nologin ftpuser
	# 设置密码，之后会提示输入密码并确认重输入
	passwd ftpuser
	# 如果是先建立的文件夹，然后添加的文件，还需要更改路径权限
	chown -R ftpuser /home/wwwroot/magneto 

-------------------------------------------------------------------------------
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
-------------------------------------------------------------------------------


## 安装 PHP
参考：
	[Magento PHP](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/php-centos.html)

CentOS 上的源很久没有更新了，需要更新源之后才能安装 PHP 5.3 以后的版本。

1. 追加CentOS 6.5的 epel 及 remi 源：
	rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm

> 注意，不能安装错了源，否则会提示错误，无法安装其他程序了。

2. 查看可安装的 PHP 包
	使用 yum list 命令查看可安装的包(Packege)。也可以不看，直接安装。
	`yum list --enablerepo=remi --enablerepo=remi-php56 | grep php`
	这一步可能会执行的比较久

3. 安装 PHP 5.6
	安装好源之后，就可以用下面的命令安装 PHP 5.6，当然也可以指定其他的版本。
	`yum install -y php56w php56w-opcache php56w-xml php56w-mcrypt php56w-gd php56w-devel php56w-mysql php56w-intl php56w-mbstring php56w-bcmath`

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
    # 设置内存限制，Magento2 推荐 768M
    memory_limit = 768M
    # 取消自动获取内容
    always_populate_raw_post_data = -1
    # 关闭 asp 风格
    asp_tags = Off

6. PHP 扩展库位置
	/usr/lib64/php/modules/

7. 查看默认加载的 php.ini 的位置
	php -i | grep php.ini

	输出类似如下：
		Configuration File (php.ini) Path => /etc
		Loaded Configuration File => /etc/php.ini 


-------------------------------------------------------------------------------
-------------------------------------------------------------------------------


## 安装 MySQL
参考：
	[Magento MySQL](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/mysql.html)

1. 更新 yum 源
	yum -y update

2. 添加 MySQL 源
	wget http://repo.mysql.com/mysql-community-release-el6-5.noarch.rpm && rpm -ivh mysql-community-release-el6-5.noarch.rpm

4. 安装 MySQL
	# 下面这个命令会自动安装 MySQL-server 和 MySQL-client 等
	yum -y install mysql-server

5. 开启 MySQL 服务并添加自动启动
	# 第一次启动会有一些相关信息自动完成
	service mysql start
	chkconfig mysqld on

6. 设置 root 密码和其他安全选项
	# 执行下面的命令，然后根据提示进行
	mysql_secure_installation
	(1). 提示输入密码，由于是第一次安装，没有密码，直接 Enter
	(2). 提示是否设置密码，输入 Y，然后 Enter
	(3). 设置密码，并确认密码
	(4). 提示是否删除匿名用户，输入 Y，然后 Enter
	(5). 提示是否禁止 root 用户远程登陆，输入 Y，然后 Enter
	(6). 提示是否删除 test 数据库，输入 Y，然后 Enter
	(7). 提示是否立即重新加载权限表，输入 Y，然后 Enter

7. 增加新用户，并设置权限
    # 创建新用户并设置密码
    # % 表示外部任何地址都能访问
    mysql> create user 'magento'@'%' identified by '123456';
    # 给新用户授权，使其能从外部登陆和本地登陆
    # 并能管理数据库 magento 数据库的所有表
    mysql> grant all privileges on magento.* to 'magento'@'localhost' identified by '123456';
    mysql> grant all privileges on magento.* to 'magento'@'%' identified by '123456';

8. 检查 MySQL 数据库的引擎
    mysql> show variables like 'storage_engine';
    # 如果显示的结果不是 InnoDB 而是 MyISAM
    # 则需要修改引擎为 InnoDB
    mysql> exit;
    service mysqld stop
    # 编辑 my.cnf 文件
    vi /etc/my.cnf
    # [mysqld] 后加入
    default-storage-engine=InnoDB

9. 配置 MySQL
    在 /etc/my.cnf 文件中至少加入下面的一段
    否则在安装时，会无法连接数据库

    # 打开配置文件
    vi /etc/my.cnf
    # 加入 [client] 段
    [client]
    socket = /var/lib/mysql/mysql.sock

10. MySQL 其他
	# 日志文件
	/var/log/mysqld.log
	# 显示 MySQL 的配置
	mysqld --print-defaults

-------------------------------------------------------------------------------
-------------------------------------------------------------------------------

## 安装 Magento
参考：
	步骤1  ：[Composer 中文网](http://www.phpcomposer.com)
	步骤2/3：[Apache User](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/apache-user.html)

1. 安装 composer
	composer 是一个用来管理 PHP 依赖关系的工具。类似 Nodejs 的 npm。

	# 全局安装
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar /usr/local/bin/composer

2. 创建一个用户，并设置密码
	useradd wwwuser
	passwd wwwuser

3. 将新建的用户加入到 web 服务器组
	# 查看 web 服务器组，一般是 apache
	egrep -i '^user|^group' /etc/httpd/conf/httpd.conf
	# 将用户 wwwuser 添加到 apache 组
	usermod -g apache wwwuser
	# 确认 wwwuser 用户的组
	groups wwwuser
	# 重启 apache 服务器
	service httpd restart

4. 在 /var/www/html 中新建文件夹，并更改所有者
	# 新建 magneto2 文件夹
	cd /var/www/html
	mkdir magento2
	# 更改所有者和组
	chown wwwuser magento2
	chown -R :apache magento2

5. 上传压缩包到服务器，并解压到 /var/www/html/magento2 文件夹
	# 上传后拷贝
	cp /home/ftp/Magento-2.0.0-tar.bz2 magento2
	# 修改权限
	chown wwwuser magento2/Magento-2.0.0-tar.bz2

6. 切换到 wwwuser 用户进行操作
	# 切换用户，或者重开会话用这个用户登录
	su - wwwuser
	# 解压
	tar -jxvf Magento-2.0.0-tar.bz2

7. 设置文件夹权限
	find . -type d -exec chmod 770 {} \; && find . -type f -exec chmod 660 {} \; && chmod u+x bin/magento

8. 浏览器中安装
	最后一步，安装时，如果提示类似：无法打开 /var/lib/php/session/ 文件的错误，
	一般是由于 apache 组对 /var/lib/php/session/ 这个文件夹没有写权限造成的，
	更改下 apache 组的权限，然后重启 apache 即可。
	chmod 775 /var/lib/php/session
	service httpd restart

	其他文件，参见：
		[Magento Troubleshooting](http://devdocs.magento.com/guides/v2.0/install-gde/trouble/tshoot.html)


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




