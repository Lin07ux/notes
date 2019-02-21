> 官方常见安装错误问题解答：[Common installation problems](https://gitlab.com/gitlab-org/omnibus-gitlab/tree/master/doc/common_installation_problems)。

### 使用外部 PostgreSQL 时提示缺少扩展

执行`sudo gitlab-ctl reconfigure`时，有类似如下错误输出：

```
ActiveRecord::StatementInvalid: PG::UndefinedFile: ERROR:  could not open extension control file "/usr/pgsql-9.6/share/extension/pg_trgm.control": No such file or directory
```

这是由于未安装`postgresql-contrib`扩展导致的。安装该扩展，并在 PostgreSQL 中创建扩展即可：

```shell
# 安装相应版本的 contrib
yum -y install postgresql96-contrib

# 创建扩展(-d 选项后面是 gitlab 使用的数据库名)
sudo -u postgres psql -d gitlabhq_production
CREATE EXTENSION pg_trgm;
\q
```

> 参考：[Installation from source guide missing instructions for pg_trgm extension creation](https://gitlab.com/gitlab-org/gitlab-ce/issues/17191)

### 502 错误

如果安装后，打开页面出现 502 错误，有多种原因，可以使用`gitlab-ctl tail`查看信息，找到里面的错误提示。

一般会有如下原因：

* `unicorn`端口被占用。默认情况下，`unicorn`使用的是 8080 端口，容易被占用，可以修改端口配置为`unicorn['port'] = 9090`，然后重新构建。

### OpenSSL::Cipher::CipherError

这类错误是由于不能正常加解密造成的。

Gitlab 默认情况下，在进行配置重构的时候，会自动生成一个用于加解密的 token，当 token 有问题的时候，一般就会造成这个错误，这就需要重新配置该 token 了。

当使用非内建服务器的时候，如果重装过了 Gitlab，而数据库没有清理，那么就会造成这个错误。

可以通过删除数据库中的 token 的值来完成：

```sql
delete from ci_variables where gl_project_id = XXX
```

如果数据库中没有什么重要数据，可以考虑清理数据库后重新编译 gitlab 的配置。

> 参考：[Runners exhibit 500 internal server error when new build is posted](https://gitlab.com/gitlab-org/gitlab-ce/issues/13272)


### parameter inet_interfaces: no local interface found for ::1

启动 postfix 服务的时候，报错如下：

```
Job for postfix.service failed. See 'systemctl status postfix.service' and 'journalctl -xn' for details.
```

根据提示，使用`systemctl status postfix.service -l`查看错误原因，如下：

```
postfix/sendmail[1143]: fatal: parameter inet_interfaces: no local interface found for ::1
```

这是由于 postfix 默认会开启 IPv6 的支持，但是服务器没有提供 IPv6，所以需要修改 postfix 的配置文件，使其仅支持 IPv4。

打开配置文件：

```shell
vim /etc/postfix/main.cf
```

在其 116 行左右，可以看到如下配置：

```conf
inet_interfaces = localhost
inet_protocols = all
```

修改为如下内容并保存：

```conf
inet_interfaces = localhost # 只能接受来自本机的邮件
inet_protocols = ipv4 # 拒绝ipv6的本机地址::1
```

