MySQL 可以使用安装时自带的 mysqldump 工具来导出数据库、表结构、表数据到一个 sql 文件中。

> 导出一部分指定的数据，可以直接使用下面的这个 sql 语句：`select * from tbl_name into outfile 'file_name';`

### 常用方式
mysqldump 工具有如下几种使用方式：

- `mysqldump [OPTIONS] database [tables]`
- `mysqldump [OPTIONS] --databases [OPTIONS] DB1 [DB2 DB3...]`
- `mysqldump [OPTIONS] --all-databases [OPTIONS]`

比如：

1. 导出整个数据库(包括数据库中的数据）：`mysqldump -u username -p dbname > dbname.sql`
2. 导出数据库结构（不含数据）：`mysqldump -u username -p -d dbname > dbname.sql`
3. 导出数据库中的某张数据表（包含数据）：`mysqldump -u username -p dbname tablename > tablename.sql`
4. 导出数据库中的某张数据表的表结构（不含数据）：`mysqldump -u username -p -d dbname tablename > tablename.sql`

> `dbname`表示数据库名称；`tablename`表示数据库中的表名；


### 定时备份
1. 结合 Linux 的 cron 命令可以实现定时备份

比如需要在每天凌晨 1:30 备份某个主机上的所有数据库并压缩 dump 文件为 gz 格式，那么可在`/etc/crontab`配置文件中加入下面代码行：

```
30 1 * * * root mysqldump -u root -pPASSWORD --all-databases | gzip > /mnt/disk2/database_`date '+%m-%d-%Y'`.sql.gz
```

> `date '+%m-%d-%Y'`得到当前日期的`MM-DD-YYYY`格式，也可以自行修改格式。

2. Shell 脚本备份

首先边写备份任务脚本：

```sh
#vi /backup/backup.sh

#!bin/bash
cd /backup
echo "You are in backup dir"
mv backup* /oldbackup
echo "Old dbs are moved to oldbackup folder"
File = backup-$Now.sql
mysqldump -u user -p password database-name > $File
echo "Your database backup successfully completed"
```

上面脚本文件保存为`backup.sh`，并且系统中已经创建两个目录`/olcbackup`和`/backup`。每次执行`backup.sh`时都会先将`/backup`目录下所有名称为`backup`开头的文件移到`/oldbackup`目录。

为上述脚本制定执行计划如下：

```
#crontab -e
30 1 * * * /backup.sh
```


### 常用参数
- `--all-databases`, `-A` 导出全部数据库。`mysqldump -uroot -p --all-databases`

- `--all-tablespaces`, 
    - `-Y` 导出全部表空间：`mysqldump -uroot -p --all-databases --all-tablespaces--no-tablespaces`。
    - `-y`不导出任何表空间信息：`mysqldump -uroot -p --all-databases --no-tablespaces`

- `--add-drop-database` 每个数据库创建之前添加`drop`数据库语句。
    - `mysqldump -uroot -p --all-databases --add-drop-database`

- `--add-drop-table` 每个数据表创建之前添加`drop`数据表语句。
    - 默认为打开状态，使用`--skip-add-drop-table`取消该选项。
    - 默认添加 drop 语句：`mysqldump -uroot -p --all-databases`
    - 取消 drop 语句：`mysqldump -uroot -p --all-databases --skip-add-drop-table`

- `--add-locks` 在每个表导出之前增加`LOCK TABLES`并且之后`UNLOCK TABLE`。
    - 默认为打开状态，使用`--skip-add-locks`取消选项。
    - 默认添加 LOCK 语句：`mysqldump -uroot -p --all-databases`
    - 取消 LOCK 语句：`mysqldump -uroot -p --all-databases --skip-add-locks`

- `--comments` 附加注释信息。默认为打开，可以用`--skip-comments`取消。
    - 默认记录注释：`mysqldump -uroot -p --all-databases`
    - 取消注释：`mysqldump -uroot -p --all-databases --skip-comments`

- `--compact` 导出更少的输出信息(用于调试)。去掉注释和头尾等结构。
    - 可以使用选项：`--skip-add-drop-table`，`--skip-add-locks`，`--skip-comments`，`--skip-disable-keys`
    - `mysqldump -uroot -p --all-databases --compact`

- `--complete-insert`, `-c` 使用完整的`insert`语句(包含列名称)。
    - 这么做能提高插入效率，但是可能会受到`max_allowed_packet`参数的影响而导致插入失败。
    - `mysqldump -uroot -p --all-databases --complete-insert`

- `--compress`, `-C` 在客户端和服务器之间启用压缩传递所有信息。
    - `mysqldump -uroot -p --all-databases --compress`

- `--databases`, `-B` 导出几个数据库。参数后面所有名字参量都被看作数据库名。
    - `mysqldump -uroot -p --databases test mysql`

- `--debug` 输出 debug 信息，用于调试。默认值为：`d:t:o,/tmp/mysqldump.trace`。
    - `mysqldump -uroot -p --all-databases --debug`
    - `mysqldump -uroot -p --all-databases --debug=” d:t:o,/tmp/debug.trace”`

- `--debug-info` 输出调试信息并退出。
    - `mysqldump -uroot -p --all-databases --debug-info`

- `--default-character-set` 设置默认字符集，默认值为`utf8`。
    - `mysqldump -uroot -p --all-databases --default-character-set=latin1`

- `--delayed-insert` 采用延时插入方式（`INSERT DELAYED`）导出数据。`
    - `mysqldump -uroot -p --all-databases --delayed-insert`

- `--events`, `-E` 导出事件。
    - `mysqldump -uroot -p --all-databases --events`

- `--flush-logs` 开始导出之前刷新日志。
    - 请注意：假如一次导出多个数据库(使用选项`--databases`或者`--all-databases`)，将会逐个数据库刷新日志。
    - 除使用`--lock-all-tables`或者`--master-data`外。
    - 在这种情况下，日志将会被刷新一次，相应的所以表同时被锁定。
    - 因此，如果打算同时导出和刷新日志应该使用`--lock-all-tables`或者`--master-data`和`--flush-logs`。
    - `mysqldump -uroot -p --all-databases --flush-logs`

- `--flush-privileges` 在导出 mysql 数据库之后，发出一条`FLUSH PRIVILEGES `语句。
    - 为了正确恢复，该选项应该用于导出 mysql 数据库和依赖 mysql 数据库数据的任何时候。
    - `mysqldump -uroot -p --all-databases --flush-privileges`

- `--force` 在导出过程中忽略出现的 SQL 错误。
    - `mysqldump -uroot -p --all-databases --force`

- `--host`, `-h` 需要导出的主机信息。
    - `mysqldump -uroot -p --host=localhost --all-databases`

- `--ignore-table` 不导出指定表。指定忽略多个表时，需要重复多次，每次一个表。每个表必须同时指定数据库和表名。
    - 例如：`--ignore-table=database.table1 --ignore-table=database.table2 ……`
    - `mysqldump -uroot -p --host=localhost --all-databases --ignore-table=mysql.user`

- `--lock-all-tables`, `-x` 提交请求锁定所有数据库中的所有表，以保证数据的一致性。
    - 这是一个全局读锁，并且自动关闭`--single-transaction`和`--lock-tables`选项。
    - `mysqldump -uroot -p --host=localhost --all-databases --lock-all-tables`

- `--lock-tables`, `-l` 开始导出前，锁定所有表。
    - 用`READ LOCAL`锁定表以允许 MyISAM 表并行插入。
    - 对于支持事务的表例如 InnoDB 和 BDB，`--single-transaction`是一个更好的选择，因为它根本不需要锁定表。
    - 请注意当导出多个数据库时，`--lock-tables`分别为每个数据库锁定表。因此，该选项不能保证导出文件中的表在数据库之间的逻辑一致性。不同数据库表的导出状态可以完全不同。
    - `mysqldump -uroot -p --host=localhost --all-databases --lock-tables`

- `--no-create-db`, `-n` 只导出数据，而不添加`CREATE DATABASE`语句。
    - `mysqldump -uroot -p --host=localhost --all-databases --no-create-db`

- `--no-create-info`, `-t`只导出数据，而不添加`CREATE TABLE`语句。
    - `mysqldump -uroot -p --host=localhost --all-databases --no-create-info`

- `--no-data`, `-d` 不导出任何数据，只导出数据库表结构。
    - `mysqldump -uroot -p --host=localhost --all-databases --no-data`

- `--password`, `-p`连接数据库密码

- `--port`, `-P` 连接数据库端口号

- `--user`, `-u` 指定连接的用户名。


