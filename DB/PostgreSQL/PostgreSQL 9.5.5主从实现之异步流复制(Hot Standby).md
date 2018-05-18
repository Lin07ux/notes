> 转摘：[PostgreSQL 9.5.5主从实现之异步流复制（Hot Standby）](https://blog.csdn.net/wlwlwlwl015/article/details/53287855)

PostgreSQL 主从的实现方式之一：基于 Standby 的异步流复制，这是 PostgreSQL 9.x 版本（2010.9）之后提供的一个功能。下面详细记录一下在 pg 9.5 中实现 Hot Standby 异步流复制的完整配置过程和注意事项。

## 一、Standby 数据库原理

做主从同步的目的就是实现数据库服务的高可用性，通常是一台主数据库提供读写，然后把数据同步到另一台从库，然后从库不断应用从主库接收到的数据，从库不提供写服务，只提供读服务。

在 PostgreSQL 中提供读写全功能的服务器称为`Primary Database`或`Master Database`，在接收主库同步数据的同时又能提供读服务的从库服务器称为`Hot Standby Server`。

PostgreSQL 在数据目录下的`pg_xlog`子目录中维护了一个`WAL`日志文件，该文件用于记录数据库文件的每次改变，这种日志文件机制提供了一种数据库热备份的方案，即：在把数据库使用文件系统的方式备份出来的同时也把相应的`WAL`日志进行备份，即使备份出来的数据块不一致，也可以重放`WAL`日志把备份的内容推到一致状态。这也就是基于时间点的备份(Point-in-Time Recovery)，简称 PITR。而把 WAL 日志传送到另一台服务器有两种方式，分别是：

* WAL 日志归档(base-file)
* 流复制(streaming replication)

第一种是写完一个 WAL 日志后，才把 WAL 日志文件拷贝到 standby 数据库中，简言之就是通过`cp`命令实现远程备份，这样通常备库会落后主库一个 WAL 日志文件。而第二种流复制是 PostgreSQL 9.x 之后才提供的新的传递 WAL 日志的方法，它的好处是只要 master 库一产生日志，就会马上传递到 standby 库，同第一种相比有更低的同步延迟，所以我们肯定也会选择流复制的方式。

在实际操作之前还有一点需要说明就是 standby 的搭建中最关键的一步：在 standby 中生成 master 的基础备份。PostgreSQL 9.1 之后提供了一个很方便的工具`pg_basebackup`，关于它的详细介绍和参数说明可以在官网中查看([pg_basebackup tool](https://www.postgresql.org/docs/current/static/app-pgbasebackup.html))，下面在搭建过程中再做相关具体说明，关于一些基础概念和原理先介绍到这里。

## 二、详细配置

下面开始实战，首先准备两台服务器，开了 2 个虚机做测试，分别是：

* 主库(master) centos-release-7-2.1511 192.168.111.101 postgresql 9.5.5
* 从库(standby) centos-release-7-2.1511 192.168.111.102 postgresql 9.5.5

先从主库配置开始。

### 1、主库配置

**注意此处的操作都是在主库(192.168.111.101)上进行的。**

#### 1.1 修改配置文件`postgresql.conf`

首先打开数据目录下的`postgresql.conf`文件然后做以下修改：

```conf
listen_address = '*'    # 默认 localhost
wal_level = hot_standby # 默认是 minimal
max_wal_senders = 2     # 默认是 0
wal_keep_segments = 64  # 默认是 0
```

* `wal_level`表示启动搭建 Hot Standby；
* `max_wal_senders`则需要设置为一个大于 0 的数，它表示主库最多可以有多少个并发的 standby 数据库；
* `wal_keep_segments`也应当设置为一个尽量大的值，以防止主库生成 WAL 日志太快，日志还没有来得及传送到 standby 就被覆盖，但是需要考虑磁盘空间允许，一个 WAL 日志文件的大小是 16M。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1526546039977.png)

如上图，一个 WAL 日志文件是 16M，如果`wal_keep_segments`设置为 64，也就是说将为 standby 库保留 64 个 WAL 日志文件，那么就会占用 16 * 64 = 1GB 的磁盘空间，所以需要综合考虑，在磁盘空间允许的情况下设置大一些，就会减少 standby 重新搭建的风险。

#### 1.2 创建复制用户

接下来还需要在主库创建一个用户来专门负责让 standby 连接去拖 WAL 日志。这个用户一般会设置全局的读取权限，但是不给写权限。

```sql
CREATE ROLE name REPLICATION LOGIN PASSWORD 'password'; 
```

> 当然，也可以不设置密码，这样的话，会有一定的安全性，应该设置只有从特定地址访问的连接可以登录。

#### 1.3 修改`pg_hba.conf`配置

将上面创建的复制用户加入到访问控制中。打开数据目录中的`pg_hba.conf`文件，加入类似如下的行：

```conf
host    replication   repl   192.168.111.0/24  md5
```

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1526547045499.png)

如上图，这行配置的意思是允许用户`repl`从`192.168.111.0/24`网络上发起到本数据库的流复制连接，简言之即允许从库服务器连接主库去拖 WAL 日志数据。

> 如果复制用户没有设置密码，而且限定仅从特定的 IP 中来拖 WAL 日志，那么可以将最后的`md5`改成`trust`。但是不建议这样做。

主库配置很简单，到此就算结束了，接下来启动主库，然后继续配置从库。

### 2、从库配置

从此处开始配置从库(192.168.111.102)。

#### 2.1 生成寄出备份

首先要通过`pg_basebackup`命令行工具在从库上生成基础备份，命令如下：

```shell
pg_basebackup -h 192.168.111.101 -U repl -F p -x -P -R -D /usr/local/postgresql/data/ -l replbackup20180517
```

> `pg_basebackup`是安装 PostgreSQL 时安装的一个工具，如果提示找不到该命令，则检查 PostgreSQL 安装目录是否加入到了系统 PATH 中，或者直接引用该目录的完整路径。

下面简单做一下参数说明(也可以通过`pg_basebackup --help`进行查看：

* `-h` 指定连接的数据库的主机名或 IP 地址，这里就是主库的 IP。
* `-U` 指定连接的用户名，此处是我们刚才创建的专门负责流复制的`repl`用户。
* `-F` 指定了输出的格式，支持`p`（原样输出）或者`t`（tar 格式输出）。
* `-x` 表示备份开始后，启动另一个流复制连接从主库接收 WAL 日志。
* `-P` 表示允许在备份过程中实时的打印备份的进度。
* `-R` 表示会在备份结束后自动生成`recovery.conf`文件，这样也就避免了手动创建。
* `-D` 指定把备份写到哪个目录，这里尤其要注意一点就是做基础备份之前从库的数据目录(`/usr/local/postgresql/data`)目录需要手动清空。
* `-l` 表示指定一个备份的标识。

运行命令后看到如下进度提示就说明生成基础备份成功：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1526547442860.png)

如上图，由于我们在`pg_hba.conf`中指定的 md5 认证方式，所以需要输入密码。

#### 2.2 修改`postgresql.conf`配置

然后还需要修改一下从库数据目录下的`postgresql.conf`文件，启用 hot_standby：

```conf
hot_standby=on   # 启用 hot_standby
```

更改之后就可以启动从库了。

> 启动从库时，如提示`data`目录的权限有问题，则根据提示将`data`目录的权限设置成`0700`即可：`chmod -R 0700 /usr/local/postgresql/data/`。
> 
> 在从库运行`pg_ctl start -l /usr/local/postgresql/log/pg_server.log`可以查看启动日志。

#### 2.3 验证

在主库和从库服务器上，分别通过`ps -ef|grep postgres`查看一下进程：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1526547813185.png)

两个服务器上，都有类似图中圈出的进程和数值，则说明已经同步成功了。此时，登陆从库，可以查看数据库、数据表和数据都已经同步过来了。同时，如果尝试在从库中删除或修改数据，则会报错：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1526547919450.png)

如上图，standby 的数据无法删除，正如之前说的，standby 只提供只读服务，而只有 master 才能进行读写操作，所以 master 才有权限删除数据，master 删除的同时 standby 中的数据也将同步删除，关于异步流复制的内容到这里就已经全部介绍完毕了。

