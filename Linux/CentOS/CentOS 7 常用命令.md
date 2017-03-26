## 系统服务

* 使某服务自动启动
    `chkconfig --level 3 httpd  on` --> `systemctl enable httpd.service`

* 使某服务不自动启动
    `chkconfig --level 3 httpd off` --> `systemctl disable httpd.service`

* 检查服务状态
    `service httpd status` --> `systemctl status httpd.service`(服务详细信息) 或 `systemctl is-active httpd.service`(仅显示是否 Active)

* 显示所有已启动的服务
    `chkconfig --list` --> `systemctl list-units --type=service`

* 启动某服务
    `service httpd start` --> `systemctl start httpd.service`

* 停止某服务
    `service httpd stop` --> `systemctl stop httpd.service`

* 重启某服务
    `service httpd restart` --> `systemctl restart httpd.service`

## 系统信息

* 修改系统字符集
    `vim /etc/locale.conf` 将其中的`LANG`的值修改为目标字符集，如`LANG="en_US.UTF-8"`，重启后就修改成功了。

* 获取系统版本
    可以使用`uname -a`来查看系统的 Linux 版本信息，使用`cat /etc/redheat-release`查看 CentOS 的版本。

## Yum 仓库

[Webtatic Yum Repository](https://webtatic.com/projects/yum-repository/)

该仓库是一个为 CentOS/RHEL 提供 Web 开发相关软件的仓库。主要目标是解决官方软件仓库的软件版本太老旧的问题。

> The Webtatic Yum repository is a CentOS/RHEL repository containing updated web-related packages.
> 
> Its main goals are:
>  ▪	to provide CentOS/RHEL administrators with the latest stable minor releases of web development/hosting software, which are not provided in CentOS/RHEL distribution minor releases.
>  ▪	to serve as an additional installation option for some of Webtatic’s projects.
> 
> All packages are signed using GnuPG, and are verified using the [Webtatic EL 6](https://mirror.webtatic.com/yum/RPM-GPG-KEY-webtatic-andy) and [Webtatic EL 7](https://mirror.webtatic.com/yum/RPM-GPG-KEY-webtatic-el7) GPG keys:

使用方式：

```shell
# CentOS/RHEL 7.x
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

# CentOS/RHEL 6.x
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
```


