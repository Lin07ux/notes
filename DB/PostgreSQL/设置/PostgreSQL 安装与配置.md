PostgreSQL [官方网站](https://www.postgresql.org/download/)中，对不同操作系统都提供了详细的安装说明，确定自己的系统版本之后，即可看到对应的安装命令。比如，对于 Redhat 类系统，可以在[Linux downloads (Red Hat family)](https://www.postgresql.org/download/linux/redhat/)网页中，设置对应的 PostgreSQL 版本和系统类别及版本即可。

## 一、安装

1. 选择 PostgreSQL 版本为 9.6；
2. 选项则操作系统为 CentOS 7；
3. 选择系统类别为 x86_64；
4. 安装仓库 RPM：`yum install https://download.postgresql.org/pub/repos/yum/9.6/redhat/rhel-7-x86_64/pgdg-centos96-9.6-3.noarch.rpm`。
5. 安装 PostgreSQL 客户端：`yum install postgresql96`；
6. 安装 PostgreSQL 服务器端：`yum install postgresql96-server`；
7. 设置开机自启动：

```shell
/usr/pgsql-9.6/bin/postgresql96-setup initdb
systemctl enable postgresql-9.6
systemctl start postgresql-9.6
```

## 二、配置文件

PostgreSQL 的配置文件的名称为`pg_hba.conf`和`postgresql.conf`，通过 Yum 安装时，路径默认为`/var/lib/pgsql/9.x/data`。如果找不到配置文件的位置了，可以使用如下命令进行查找：

```shell
find / -iname pg_hba.conf
```

找到配置文件之后，更改该文件中的配置即可。

### 2.1 允许远程访问

> 参考：[如何设置PostgreSQL允许被远程访问](http://lazybios.com/2016/11/how-to-make-postgreSQL-can-be-accessed-from-remote-client/)

**修改 postgresql.conf**

```shell
# 打开文件
vim /etc/postgresql/9.6/data/postgresql.conf

# 将其中的 listen_addresses 修改为星号
listen_addresses = '*'
```

**修改 pg_hba.conf**

`pg_hba.conf`，位置与`postgresql.conf`相同，虽然上面配置允许任意地址连接 PostgreSQL，但是这在 pg 中还不够，还需在`pg_hba.conf`中配置服务端允许的认证方式：

```shell
# 打开文件
vim /etc/postgresql/9.6/data/pg_hba.conf

# 编辑或添加类似如下的行
# TYPE  DATABASE  USER  CIDR-ADDRESS  METHOD
host  all  all 0.0.0.0/0 md5
```

默认 pg 只允许本机通过密码认证登录，修改为上面内容后即可以对任意 IP 访问进行密码验证。

然后重启 PostgreSQL 即可：`sudo systemctl restart postgresql-9.6`。

