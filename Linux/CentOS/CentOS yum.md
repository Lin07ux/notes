
## 使用

### 更改安装位置

```shell
yum -c /etc/yum.conf --installroot=/opt/all_venv/ --releasever=/  install nginx
```

其中：

* `-c /etc/yum.conf` 表示指定yum配置文件地址
* `--installroot=/usr/local` 表示指定自定义的安装目录

## 问题

### 提示源重复出现

使用`yum`相关命令的时候，会提示类似`Repository updates-debuginfo is listed more than once in the configuration`一类的信息。

这是由于在`yum`的源中，有多个类似的源文件出现了，此时删除对应的文件即可。

通过`ls -l /etc/yum.repos.d/`命令可以看到具体的源文件，根据`yum`命令中的相关提示删除不需要的源文件即可。

> 删除之前做好备份保存工作。

> 参考：[updates is listed more than once in the configuration 的解决](http://blog.csdn.net/pknming/article/details/52574321)

### yum 更新后无法正常执行 systemctl: Error getting authority

在进行 yum 相关的操作后，使用 sysmtemctl 执行系统命令(如`systemctl start sshd`)时，出现类似如下的错误：

```
"Error getting authority: Error initializing authority: Could not connect: Connection refused (g-io-error-quark, 1)"
```

一般是由于`/var`目录被错误隔离导致的，可以使用如下的操作：

```shell
mv -f /var/run /var/run.runmove~
ln -sfn /run /var/run
mv -f /var/lock /var/lock.lockmove~
ln -sfn /run/lock /var/lock
```

然后重新启动即可。

> 参考：["Error getting authority: Error initializing authority: Could not connect: Connection refused (g-io-error-quark, 39)" after yum update.](https://access.redhat.com/solutions/3522441)

### 提示 repomd.xml 找不到

当执行 Yum 相关命令时，有类似如下提示：

```
http://mirror.centos.org/centos/$releasever/os/x86_64/repodata/repomd.xml: [Errno 14] HTTP Error 404 - Not Found Trying other mirror.
```

这是由于未能正常解析出网址中的`$releasever`变量导致的，可以使用如下方式解决：

```shell
# 如文件夹不存在则先创建
mkdir -p /etc/yum/vars
# 设置变量值
echo 7 > /etc/yum/vars/releasever
```

> 参考：[CentOS 7.2: Yum repo configuration fails](https://superuser.com/questions/1091450/centos-7-2-yum-repo-configuration-fails)


