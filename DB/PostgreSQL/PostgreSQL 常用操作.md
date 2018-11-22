## 安装

PostgreSQL 官方提供了针对多种系统的安装方式，具体可以查看 [PostgreSQL DOWNLOADS](https://www.postgresql.org/download/)。

如对于在 CentOS 7 上安装 PostgreSQL 9.5，可以找到如下的安装命令：

```shell
# https://www.postgresql.org/download/linux/redhat/
# Install the repository RPM
yum install https://download.postgresql.org/pub/repos/yum/9.5/redhat/rhel-7-x86_64/pgdg-centos95-9.5-3.noarch.rpm

# Install the client packages
yum install postgresql95

# Optionally install the server packages
yum install postgresql95-server

# Optionally initialize the database and enable automatic start
/usr/pgsql-9.5/bin/postgresql95-setup initdb
systemctl enable postgresql-9.5
systemctl start postgresql-9.5
```

## 命令行

### 登陆

默认情况下，PostgreSQL 有一个用户 postgres，可以直接登陆，不需要密码，但是只能在数据库服务器本机中登陆。 还可以登陆时指定数据库。

```shell
psql -U postgres -d postgres
```

### 常用命令

```sql
-- 列出所有的数据库
\l
\list

-- 切换数据库
\c dbname

-- 列出当前数据库下的数据表
\d

-- 列出当前数据库中的全部 schema
\dnS

-- 列出指定表的全部字段
\d tablename

-- 查看指定表的基本情况
\d+ tablename

-- 退出登陆
\q
```

## 数据库/表

### 添加/删除主键
```sql
alter table server add primary key (id);
alter table server drop constraint server_pkey;
```

> 参考：[为PostgreSQL数据库中没有主键的表增加主键](http://www.sijitao.net/2026.html)

### 删除数据库

```sql
DROP DATABASE [ IF EXISTS ] name
```

### 删除 SCHEMA

```sql
DROP SCHEMA schema_name [ CASCADE ];
```

如果 schema 中还有表、函数等其他定义的时候，是没有办法直接删除的，此时如果确定要删除，可以带上`CASCADE`参数来使用级联删除。

## 用户

> 参考：
> 1. [PostgreSQL 批量权限 管理方法](https://yq.aliyun.com/articles/41512)
### 授权

```sql
-- 将用户对某个 schema 中的全部表的都设置权限
GRANT SELECT, UPDATE, INSERT ON ALL TABLES IN SCHEMA schema_name TO role_name;
```

### 取消授权

```sql
-- 将用户对某个 schema 中的全部表都取消执行权限
REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA schema_name FROM role_name;
```

### 删除用户

删除用户不需要登陆到 PostgreSQL 中，直接在 shell 中执行`dropuser`命令即可：

```shell
dropuser [connection-option...] [option...] [username]
```

也可以登陆数据库之后，使用如下 sql 命令删除：

```sql
DROP USER user_name;
```

## 插件

### 查看插件

登陆到 psql 命令行之后，使用下面的命令即可看到目前已经安装的插件：

```shell
\dFp
# 或者
\dx
```

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1526542496048.png" width="352"/>

### 安装插件

PostgreSQL 插件需要先在操作系统中安装，得到对应的可执行文件，然后再在 psql 命令行中载入。

在操作系统中，可以通过系统安装命令(如`yum`)安装，也可以通过源码编译安装。安装好之后，就可以登录 psql 命令行载入插件了。

```sql
CREATE EXTENSION ext_name;
```

执行成功后会提示`CREATE EXTENSION`，然后再使用`\dx`命令查看。

### 卸载插件

```sql
DROP EXTENSION ext_name;
```

如果有别的插件依赖要卸载的插件，则上述命令不能成功执行，会有相关提示。如果我们也不需要依赖该插件的插件，则可以加上`CASCADE`来将相关插件都删除：

```sql
DROP EXTENSION ext_name CASCADE;
```


