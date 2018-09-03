> 转摘：[Innodb中MySQL如何快速删除2T的大表](http://database.51cto.com/art/201808/582324.htm)

MySQL 删除表可以直接使用`drop table table_name`命令，这个时候所有的 MySQL 的相关进程都会停止，直到删除结束才会恢复执行。出现这个情况的原因是因为，在`drop table`的时候，Innodb 维护了一个全局锁，drop 完毕锁就释放了。

一般情况下是可以很快就删除完成的，但是如果要删除一个有很多的表，就会造成 MySQL 长时间卡死，而影响正常使用。

为了尽量不影响数据库的使用，可以通过如下的方法来实现。

### 前提

这里说的删除方法有一个前提：MySQL 开启了独立表空间。在 MySQL 5.6.7 之后这是默认开启。

也就是说，在`my.cnf`中，有这么一条配置：`innodb_file_per_table = 1`，而且在数据库中通过`show variables like '%per_table';`命令看到的`innodb_file_per_table`的值是`ON`。

这个配置是用于数据库使用共享表空间还是独立表空间。当值为`ON`时使用的是独立表空间。而这里介绍的方法就是需要使用独立表空间。

* 共享表空间：某一个数据库的所有的表数据，索引文件全部放在一个文件中，默认这个共享表空间的文件路径在数据库的`datadir`目录中。默认的文件名为`ibdata1`(此文件可以扩展成多个)。注意，在这种方式下，所有数据都在一个文件里，要对单表维护，十分不方便。另外，在做`delete`操作的时候，文件内会留下很多间隙，`ibdata1`文件不会自动收缩。换句话说，使用共享表空间来存储数据，会遭遇`drop table`之后空间无法释放的问题。

* 独立表空间：每一个表都以独立方式来部署，每个表都有一个`.frm`表描述文件和一个`.ibd`文件，前者保存了该表的元数据，包括表结构的定义等(该文件与数据库引擎无关)，后者保存了该表的数据和索引。这种方式下就没有了上面共享表空间提到的弊端。

### 原因

假设数据库的数据路径为`/data/mysql/`，而有一个数据库`mytest`，数据路径下的文件类似如下：

```shell
> system ls -l /data/mysql/mytest/
-rw-r----- 1 mysql mysql          9023  8 18 05:21 erp.frm  
-rw-r----- 1 mysql mysql 2356792000512  8 18 05:21 erp.ibd
```

当执行`drop table`的时候，就需要删除这个表对应的`.frm`和`.ibd`文件，而由于`.ibd`文件较大，在删除时系统磁盘 IO 较大，然后就卡住了。

### 解决

要解决这个问题，其实就是解决 Linux 删除大文件时候的磁盘 IO 卡住问题，需要通过硬链接来解决。

Linux 中文件的链接有两种：软链接和硬链接。软链接类似于 Windows 系统中的快捷方式，而硬链接则可以理解为两个文件名指向同一个文件，相当于 C 语言中地址引用。

Linux 系统中，一切皆文件，而对于真正存储的文件来说，有一个 Inode Index 指向存储文件，然后有一个文件名指向 这个Inode Index。软链接就是一个文件名指向了另一个文件名，而硬链接则是两个或多个文件名指向同一个 Innode Index。

删除文件时，Linux 系统会先删除文件名，然后查看该文件名所指向的 Innode Index 是否有别的引用。如果没有的话，则删除 Innode Index 指向的存储数据，如果有的话，则不做其他处理。

所以，当为一个文件创建了硬链接，然后再删除这个文件名时，操作会很快，因为只涉及到文件名的删除，而真实的数据并没有被清理。

这就是用于解决 MySQL 删除大数据量表避免卡住的思路：

1. 为表对应的文件创建一个硬链接

```shell
system ln /data/mysql/mytest/erp.ibd /data/mysql/mytest/erp.ibd.hdlk
```

2. 执行`drop table`来删除表

```sql
drop table erp
```

### 后续

通过上述操作，虽然已经在数据库删除了表，但磁盘空间并没有释放。如果直接删除文件，虽然不会让 MySQL 服务卡住，但是磁盘 IO 依旧会卡住，从而影响服务。这时就需要使用`truncate`命令来进行删除。

> truncate 可能需要单独安装。

truncate 命令对磁盘 IO、CPU 负载几乎无影响。删除脚本如下：

```shell
TRUNCATE=/usr/local/bin/truncate  
for i in `seq 2194 -10 10 `;   
do   
  sleep 2  
  $TRUNCATE -s ${i}G /data/mysql/mytest/erp.ibd.hdlk   
done  
rm -rf /data/mysql/mytest/erp.ibd.hdlk ;
```

从 2194G 开始，每次缩减 10G，停 2 秒，继续，直到文件只剩 10G，最后使用 rm 命令删除剩余的部分。


