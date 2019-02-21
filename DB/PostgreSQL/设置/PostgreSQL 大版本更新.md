PostgreSQL 的次版本直接是不能直接升级的，属于不同的大版本。升级之后，旧有的数据是不能直接使用的，需要重新转换成新版本的数据。下面介绍下如何升级 PostgreSQL 大版本，并使用`pg_upgrade`工具转换数据。

> 参考：[PostgreSQL升级方案](https://segmentfault.com/a/1190000008897312)

### 1. 安装新版本

根据 [PostgreSQL 安装与配置](./PostgreSQL 安装与配置.md)或直接根据[官网安装说明](https://www.postgresql.org/download/linux/redhat/)安装需要的新版本。如安装 9.6 版本：

```shell
yum install https://download.postgresql.org/pub/repos/yum/9.6/redhat/rhel-7-x86_64/pgdg-centos96-9.6-3.noarch.rpm

yum install postgresql96
yum install postgresql96-server
/usr/pgsql-9.6/bin/postgresql96-setup initdb
systemctl enable postgresql-9.6
```

> 安装完成之后，暂时不需要开启新版本的 PostgreSQL。

### 2. 安装插件(可选)

如果老版本的 PostgreSQL 安装了插件，那么新版本的 PostgreSQL 也需要安装对应的插件，但仅仅需要编译安装好插件的可执行程序，不需要在新版本的 PostgreSQL 中进行创建。因为在转换数据的时候，老的数据中包含由在 PostgreSQL 中创建扩展的命令。如果提前先创建了，有可能会导致转换失败。

> 安装插件的时候，记得需要将系统环境变量`$PATH`中 PostgreSQL 的`bin`目录改成新安装的 PostgreSQL 的`bin`目录，否则插件将会继续安装到旧版本。

### 3. 检查和转换数据

一切准备好之后，就可以切换到 postgres 用户进行数据的检查和转换了：

```shell
# 切换用户
su - postgres

# 检查数据
pg_upgrade -c -b /usr/local/pgsql/bin -B /usr/pgsql-9.6/bin -d /home/data/ -D /home/pgsql-data/9.6/

# 关闭 PostgreSQL
pg_ctl -D /opt/PostgreSQL/9.5 stop
pg_ctl -D /opt/PostgreSQL/9.6 stop


# 进行转换
pg_upgrade -b /usr/local/pgsql/bin -B /usr/pgsql-9.6/bin -d /home/data/ -D /home/pgsql-data/9.6/
```

其中，检查数据和执行转换的命令基本相同，只是前者多了一个`-c`选项。各个选项的作用可以查看 [pg_upgrade 官方文档](https://www.postgresql.org/docs/9.6/pgupgrade.html)：

* `-c` 执行数据检查
* `-b` 老版本 PostgreSQL 的`bin`目录
* `-B` 新版本 PostgreSQL 的`bin`目录
* `-d` 老版本 PostgreSQL 的`data`目录
* `-D` 老版本 PostgreSQL 的`data`目录

当检查完成没有问题，就可以执行数据的转换了。

### 4. 注意事项

使用 pg_upgrade 工具进行数据升级转换时，老数据库是需要有断线的。正常情况下，数据转换的速度比较快，一般会在几分钟内完成。如果转换后，老数据不需要了。那么还可以添加`--link`选项，加速转换。具体可以查看 [pg_upgrade 官方文档](https://www.postgresql.org/docs/9.6/pgupgrade.html)。

在进行数据检查的时候，老版本的 PostgreSQL 是可以开启的，但是在进行数据转换的时候，则新旧版本的 PostgreSQL 都需要关闭。

当数据转换遇到问题的时候，可以根据提示找到相应的错误信息，根据信息进行调整即可。在改正之后，重新执行数据转换之前，需要先将新数据库中的数据都清空(包括用户创建的数据库、角色/用户等)，可以开启新版本的 PostgreSQL 登录进入用命令行清除，也可以重新初始化数据：

```shell
# 删除数据
rm -rf /var/lib/pgsql/9.6/data/*
# 初始化数据
/usr/pgsql-9.6/bin/postgresql96-setup initdb
```

> 如果`data`目录不是默认的，则还需要清空`data`目录后，将初始化的数据全部拷贝到`data`目录。


