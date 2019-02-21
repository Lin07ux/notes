zhparser 是一个对中文进行分词的插件，在 PostgreSQL 中安装该插件的时候，需要先安装好 SCWS 依赖。下面介绍如何安装 zhparser 插件。

## 一、安装 SCWS

### 1.1 下载 SCWS

到 [SCWS 官网的下载页面](http://www.xunsearch.com/scws/download.php)中下载最新版本源码，如 1.2.3，并下载：

```shell
wget http://www.xunsearch.com/scws/down/scws-1.2.3.tar.bz2
```

### 1.2 编译安装 SCWS

```shell
# 解压
tar xvf scws-1.2.3.tar.bz2
cd scws-1.2.3

# 配置
./configure

# 安装
make && make install
```

> 如果安装时报 gcc 的错误，需要先在服务器上安装 gcc(`yum -y install gcc`)。

### 1.3 确认是否安装成功

使用如下命令，查看是否有正常输出：

```shell
ls /usr/local/scws/include/scws/
# 安装成功时，会输出类似下面的信息
# charset.h crc32.h darray.h darray.h pool.h rule.h scws.h version.h xbd.h xdict.h xtree.h
```

## 二、安装 zhparser

### 2.1 下载 zhparser

```shell
wget https://github.com/amutu/zhparser/archive/master.zip
```

### 2.2 编译安装

```shell
# 解压
unzip master.zip
cd zhparser-master.zip

# 安装
SCWS_HOME=/usr/local make && make install
```

当出现类似如下的输出时，表示安装成功了：

```
/usr/bin/mkdir -p '/usr/pgsql-9.6/lib'
/usr/bin/mkdir -p '/usr/pgsql-9.6/share/extension'
/usr/bin/mkdir -p '/usr/pgsql-9.6/share/extension'
/usr/bin/mkdir -p '/usr/pgsql-9.6/share/tsearch_data'
/usr/bin/install -c -m 755  zhparser.so '/usr/pgsql-9.6/lib/zhparser.so'
/usr/bin/install -c -m 644 .//zhparser.control '/usr/pgsql-9.6/share/extension/'
/usr/bin/install -c -m 644 .//zhparser--1.0.sql .//zhparser--unpackaged--1.0.sql  '/usr/pgsql-9.6/share/extension/'
/usr/bin/install -c -m 644 .//dict.utf8.xdb .//rules.utf8.ini '/usr/pgsql-9.6/share/tsearch_data/'
```

### 三、创建扩展

完成上述步骤之后，只是安装成功了 zhparser 的程序，但是 PostgreSQL 中还没有相应的扩展，此时就需要登录 PostgreSQL 创建扩展。

```shell
# 切换到 postgres 用户
su - postgres

# 进入 PostgreSQL 命令行
psql

# 创建扩展，会输出：CREATE EXTENSION
create extension zhparser;

# 查看 PostgreSQL 中的扩展，输出中就会包含有一个 zhparser 扩展了
\dx

# 创建使用 zhparser 作为解析器的全文搜索的配置，输出：CREATE TEXT SEARCH CONFIGURATION
CREATE TEXT SEARCH CONFIGURATION testzhcfg (PARSER = zhparser);

# 往全文搜索配置中增加 token 映射，会输出：ALTER TEXT SEARCH CONFIGURATION
CREATE TEXT SEARCH CONFIGURATION testzhcfg (PARSER = zhparser);
```

创建完成之后，就可以查看中文分词效果了：

```psql
postgres=# select to_tsvector('testzhcfg','南京市长江大桥');
       to_tsvector 
-------------------------
 '南京市':1 '长江大桥':2
(1 row)
```


