## 一、安装

在 Gitlab 官网的[安装说明](https://about.gitlab.com/install/)页面中，可以找到多种系统的安装步骤和命令。但是官网上使用安装源，在国内访问会很慢，而且其安装的是 Gitlab 企业版。对于一般情况下，安装 Gitlab 社区版来说，可以使用另外的源来安装。

下面介绍使用清华源来安装 Gitlab 社区版。

> gitlab-ce 镜像仅支持 x86-64 架构。
> 
> 参考：[Gitlab Community Edition 镜像使用帮助](https://mirror.tuna.tsinghua.edu.cn/help/gitlab-ce/)

### 1.1 创建清华源

新建`/etc/yum.repos.d/gitlab-ce.repo`文件，内容为：

```
[gitlab-ce]
name=Gitlab CE Repository
baseurl=https://mirrors.tuna.tsinghua.edu.cn/gitlab-ce/yum/el$releasever/
gpgcheck=0
enabled=1
```

### 1.2 安装

执行下面的命令，即可安装最新版 gitlab-ce 了：

```shell
sudo yum makecache
sudo yum install gitlab-ce
```

### 1.3 gitlab 常用命令

安装完成之后，系统中就多了`gitlab-ctl`及其他相关命令。常用命令如下：

* `gitlab-ctl start` 启动 gitlab
* `gitlab-ctl stop` 关闭 gitlab
* `gitlab-ctl restart` 重启 gitlab
* `gitlab-ctl reconfigure` 重新编辑配置
* `gitlab-ctl tail` 查看运行日志

## 二、配置

安装完成之后，一般需要先根据情况，修改配置文件中的内容，然后重新编译配置，即可启动使用了。在之后的使用过程中，如果需要变更配置，也需要先停止 gitlab-ce，重新编辑配置，再启动。

gitlab-ce 的配置文件的路径为：`/etc/gitlab/gitlab.rb`。

### 2.1 邮件

使用邮件时，需要先确认服务器有安装邮件服务，比如可以安装 Postfix：

```shell
# 安装 postfix
sudo yum install postfix
sudo systemctl enable postfix
sudo systemctl start postfix
# 设置防火墙
sudo firewall-cmd --permanent --add-service=http
sudo systemctl reload firewalld
```

然后可以在 gitlab-ce 的配置文件中修改 smtp 相关的配置，根据使用的邮箱的不同，需要设置不同的配置，可以参考 [Gitlab - SMTP settings](https://docs.gitlab.com/omnibus/settings/smtp.html)。比如，对于 QQ 企业邮箱来说，使用类似如下的配置：

```rb
gitlab_rails['smtp_enable'] = true
gitlab_rails['smtp_address'] = "smtp.exmail.qq.com" # 值不变
gitlab_rails['smtp_port'] = 465                     # 值不变
gitlab_rails['smtp_domain'] = "exmail.qq.com"       # 值不变
gitlab_rails['smtp_user_name'] = "xxxx@xx.com"      # 填写邮箱地址
gitlab_rails['smtp_password'] = "password"          # 填写密码
gitlab_rails['smtp_authentication'] = "login"       # 值不变
gitlab_rails['smtp_enable_starttls_auto'] = true
gitlab_rails['smtp_tls'] = true

# If your SMTP server does not like the default 'From: gitlab@localhost' you
# can change the 'From' with this setting.
gitlab_rails['gitlab_email_from'] = 'xxxx@xx.com'
gitlab_rails['gitlab_email_reply_to'] = 'noreply@xx.com'
```

在 gitlab-ce 启动之后，可以通过如下方式验证邮件是否可以发送成功：

```shell
# 启动并进入 Rails console
gitlab-rails console

# 在 Rails console 中执行如下命令，其中邮箱地址填写个人邮箱即可
Notify.test_email('email@address.com', 'Message Subject', 'Message Body').deliver_now
```

如果可以正常接收到邮件，则说明邮箱配置正常。如果不能成功发送或者无法接收到邮件，则检查下邮箱配置是否完整。一般会是配置错误或遗漏、邮件服务不能正常工作造成的。

### 2.2 不使用内建的 Nginx

gitlab-ce 默认包含有内建的 Nginx 服务器，可以直接使用。如果要使用已经安装在服务器上 Nginx，则需要有一些特别的配置，可以查看 [Gitlab - Using a non bundled web-server](https://docs.gitlab.com/omnibus/settings/nginx.html#using-a-non-bundled-web-server)。

> 也支持使用 Apache 等服务器，可以在 [Gitlab - Nginx setting](https://docs.gitlab.com/omnibus/settings/nginx.html) 中找到相关说明。

```rb
# 首先关闭内建 Nginx
nginx['enable'] = false

# 设置已有 Nginx 的运行用户
web_server['external_users'] = ['www-data']

# 设置 gitlab-ce 可信任的代理，如果 gitlab-ce 和 Nginx 在同一台服务器，可以直接留空
gitlab_rails['trusted_proxies'] = []
```

配置完成后，使用`sudo gitlab-ctl reconfigure`命令重新构建配置，一切正常的话，还要再配置 Nginx，可以从 [GitLab recipes repository](https://gitlab.com/gitlab-org/gitlab-recipes/tree/master/web-server) 找到 Nginx  的参考配置。

### 2.3 不使用内建的 PostgreSQL

如果使用服务器中已经安装好的 PostgreSQL，更改 PostgreSQL 相关的一些配置即可。可以查看 [Gitlab - Database](https://docs.gitlab.com/omnibus/settings/database.html#using-a-non-packaged-postgresql-database-management-server) 文档进行参考。

> 如果要使用 MySQL 数据库，也可以查看 [官方文档](https://docs.gitlab.com/omnibus/settings/database.html)。

```rb
# Disable the built-in Postgres
postgresql['enable'] = false

# Fill in the connection details for database.yml
gitlab_rails['db_adapter'] = 'postgresql'
gitlab_rails['db_encoding'] = 'utf8'
gitlab_rails['db_host'] = '127.0.0.1'
gitlab_rails['db_port'] = 5432
gitlab_rails['db_username'] = 'USERNAME'
gitlab_rails['db_password'] = 'PASSWORD'
```

然后重新编辑配置即可。

### 2.4 不使用内建的 Redis

如果要使用系统中已经安装好的 Redis 服务，根据 [Gitlab - Redis Setting](https://docs.gitlab.com/omnibus/settings/redis.html#using-a-non-packaged-redis-instance) 的说明，可以使用如下配置：

```rb
# 禁用内建 Redis
redis['enable'] = false

# 通过 TCP 连接使用 Redis
gitlab_rails['redis_host'] = 'redis.example.com'
gitlab_rails['redis_port'] = 6380

# 通过 Unix domain sockets 使用 Redis
gitlab_rails['redis_socket'] = '/tmp/redis.sock' # defaults to /var/opt/gitlab/redis/redis.socket
```

然后重新构建配置即可。

### 2.5 设置数据存放位置

> [官方文档](https://docs.gitlab.com/omnibus/settings/configuration.html#storing-git-data-in-an-alternative-directory)

如果要将 Gitlab 的数据(如仓库代码)存放到别的位置，可以修改`/etc/gitlab/gitlab.rb`配置文件中的`git_data_dirs`配置项：

```rb
# 配置单独的位置
git_data_dirs({ "default" => { "path" => "/mnt/nas/git-data" } })

# 或者配置多个
git_data_dirs({
  "default" => { "path" => "/var/opt/gitlab/git-data" },
  "alternative" => { "path" => "/mnt/nas/git-data" }
})
```

## 三、卸载

> 转摘：[完全卸载删除gitlab](https://blog.whsir.com/post-1469.html)

```shell
# 1. 停止 Gitlab
gitlab-ctl stop

# 2. 卸载
yum remove gitlab-ce

# 3. 结束 gitlab runsvdir 服务
systemctl stop gitlab-runsvdir.service
# 或查找进程，并杀掉 runsvdir 进程
ps aux | grep gitlab
kill -9 <pid>

# 4. 删除 gitlab 的文件
find / -name gitlab | xargs rm -rf
find / -name "*gitlab*" | xargs rm -rf
```


