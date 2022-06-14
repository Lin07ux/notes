### 同步时间

CentOS 中，查看日期和时间的命令是`date`，设置命令是`date -set 'yyyy-mm-dd H:i:s'`。

如果经常遇到时间不准确的问题，可以尝试安装网络时间同步工具，如下：

```shell
# 安装服务
sudo yum -y install ntp ntpdate
# 同步时间
ntpdate cn.pool.ntp.org
```

### 修改 IP 地址

Centos 7 修改 IP 地址就是修改一个文件中的内容配置即可：

```shell
# 查看当前 IP 地址
> ip addr
...
inet 192.168.0.30/24 brd ...
...

# 更新 IP 地址配置
> vim /etc/sysconfig/network-scripts/ifcfg-ens192
IPADDR=192.168.0.200

# 重启网络服务
> systemctl network retart
Restarting network     [ OK ]

# 确认 IP 地址
> ip addr
...
inet 192.168.0.200/24 brd ...
...
```

> IP 地址配置文件的名称可能不是`ifcfg-ens192`，但是前缀一定是`ifcgf-ens`。

### Nginx 有权限但是无法访问文件

Nginx 配置的 root 目录有对应的权限，但是无法访问，提示 404 错误。日志中记载的是权限不足，无法访问。

此时一般是由于使用 selinux 服务导致的。关闭该服务即可恢复。

查看 selinux 状态：`getenforce`。

临时关闭：

```shell
setenforce 0 #设置SELinux 成为permissive模式
#setenforce 1 设置SELinux 成为enforcing模式
```

永久关闭(需重启)：

```shell
# 修改 /etc/selinux/config 文件
# 将 SELINUX=enforcing 改为 SELINUX=disabled
SELINUX=disabled
```

### 清理垃圾

```shell
find / -name *~ -exec rm -rf {} \; 
```

### minerd 挖矿处理
在服务器中，由于服务很卡，使用 top 命令看到里面有一个进程 minerd 占用了 90% 多的 CPU。查询资料，这个是一个挖矿程序，很占用 CPU，找到对应的程序，删掉之后之后会自动恢复。也就是无法彻底删干净。

为了能够停止该进程的运行，可以考虑将该进程删除之后，修改程序的权限，去掉执行权限。

1. 关闭访问挖矿服务器的访问

    ```shell
    iptables -A INPUT -s xmr.crypto-pool.fr -j DROP and iptables -A OUTPUT -d xmr.crypto-pool.fr -j DROP
    ```
    
2. 取消掉执行权限

    ```shell
    chmod -x minerd
    ```
    
3. 杀掉进程

    ```shell
    pkill minerd
    ```

4. 删除计划任务

    ```shell
    service stop crond # crontab -r
    ```





