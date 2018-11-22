## 错误
在执行类似下面的 sql 导出部分数据到文件中的时候，会发生错误：

```sql
select * from course into outfile 'course.bak';
```

错误提示为：

```
ERROR 1290 (HY000): The MySQL server is running with the --secure-file-priv option so it cannot execute this statement
```

## 原因
这是由于 MySQL 设置了安全文件夹选项的缘故。开启该选项后，导出数据到文件的时候，仅能将导出文件的路径设置为安全文件夹中。

## 解决
首先执行如下的命令，查看安全文件夹的设置：

```sql
show variables like '%secure%';
```

会看到类似如下的设置：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1488964364779.png" width="367"/>

其中`secure_file_priv`对应的值即为安全路径。在导出的时候，将导出文件的路劲前缀改成这个即可。

比如：

```sql
select * from course into outfile '/var/lib/mysql-files/course.bak';
```


