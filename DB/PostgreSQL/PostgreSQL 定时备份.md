通过 Linux 系统的 Crontab 服务可以定时对 PostgreSQL 数据进行备份，而备份使用的命令是`pg_dumpall`。该命令会将全部的数据库和数据都备份下来。备份下来之后，还可以对其进行压缩，以便节约存储空间。

1. 首先，创建自动备份脚本

    创建脚本`backup.sh`，内容如下：
    
    ```shell
    #!/bin/bash
    
    cur_date=$(date '+%Y-%m-%d')
    before_date=$(date -d -10days '+%Y-%m-%d')
    su - postgres <<!
    echo "Starting Backup PostgreSQL-9.5 ..."
    cd /data/backup/pgsql/9.5
    rm -rf $before_date.tar.gz
    pg_dumpall > "$cur_date.dmp"
    
    echo "Compressing..."
    tar zcvf "./$cur_date.tar.gz" *.dmp
    chmod 0600 $cur_date.tar.gz
    
    echo "Remove temp file ..."
    rm -rf *.dmp
    echo "Finish Backup ..."
    exit
    !
    ```

2. 设置 crontab 计划
    
    运行`crontab -e`命令，来将上面创建的脚本定时执行：
    
    ```shell
    0 2 * * * /var/lib/pgsql/backup.sh
    ```

> 参考：[PostgreSQL定时自动备份](https://blog.csdn.net/sunbocong/article/details/77936601)

