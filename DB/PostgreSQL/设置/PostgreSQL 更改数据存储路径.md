数据路径迁移方法有两种：

* 重新初始化 PostgreSQL 数据库，初始化时指定新的数据路径`--PGDATA`，然后在新的环境下将原有的数据库备份恢复一下；
* 直接将现有的数据库文件全部拷贝到新的数据库路径下，然后重启数据库服务。

## 直接迁移

直接迁移方式相对简单，但是前提是 PostgreSQL 已经作为服务添加到了 systemctl 服务中。

1. PostgreSQL 安装后，默认的数据库路径是`/var/lib/pgsql/9.x/data`；

2. 新建一个路径作为新的数据库数据路径，假如是`/home/data`

    ```shell
    sudo mkdir /home/data
    sudo chown -R postgres:postgres data
    sudo chmod 700 data
    ```

    最后这个赋权命令是必须的，不然数据库启动会有问题的

3. 拷贝之前的数据文件

    ```shell
    # 首先要停止 PostgreSQL 服务
    sudo systemctl stop postgresql
    sudo su - postgres
    cp -rf /var/lib/pgsql/9.x/data/* /home/data
    ```

4. 修改 service 文件

    ```shell
    vim /usr/lib/systemd/system/postgresql*.service
    
    # 修改这个文件中的
    Environment=PGDATA=/var/lib/pgsql/9.4/data/
    # 将其修改为自己的新的数据路径：
    Environment=PGDATA=/home/data/
    ```

5. 重新加载 service，并重启 PostgreSQL。

    ```shell
    # 更改 service 文件之后需要重新加载
    systemctl daemon-reload
    # 重启 PostgreSQL 服务
    systemctl restart postgresql-9.5
    ```



