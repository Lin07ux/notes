MySQL 的时区默认是其所在服务器的时区，所以一般直接设置服务器的时区和时间即可。

可以通过以下命令查看 MySQL 的时区：

```sql
mysql> show variables like '%time_zone%';   
+------------------+--------+   
| Variable_name    | Value  |   
+------------------+--------+   
| system_time_zone | CST    |    
| time_zone        | SYSTEM |    
+------------------+--------+   
```

如果要修改，可以通过修改`my.cnf`配置文件来实现：

- 方法一：

在`[mysqld]`之下加`default-time-zone=timezone`来修改时区。

如：`default-time-zone = '+8:00'`

> 改了记得重启 msyql。
> 注意：一定要在`[mysqld]`之下加 ，否则会出现`unknown variable 'default-time-zone=+8:00'`。

- 方法二：

另外也可以通过命令`set time_zone = timezone`在 mysql 中直接修改。

比如北京时间（GMT+0800）`set time_zone = '+8:00';`

这个和 php 的时区设置格式又有点差别，比如北京时间在 php 中是`date_default_timezone_set('Etc/GMT-8');`

