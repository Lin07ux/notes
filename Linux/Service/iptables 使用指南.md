## 简介

Linux 系统在内核中提供了对报文数据包过滤和修改的官方项目，名为 Netfilter，它指的是 Linux 内核中的一个框架，可以用于在不同阶段将某些钩子函数（hook）作用于网络协议栈。Netfilter 本身并不对数据包进行过滤，它只是允许可以过滤数据包或修改数据包的函数挂接到内核网络协议栈中的适当位置。这些函数是可以自定义的。

iptables 是用户层的工具，它提供命令行接口，能够向 Netfilter 中添加规则策略，从而实现报文过滤，修改等功能。Linux 系统中并不止有 iptables 能够生成防火墙规则，其他的工具如 firewalld 等也能实现类似的功能。

> iptables 的配置文件路径在：`/etc/sysconfig/iptables`

## 安装

下面以 CentOS 系统为例，安装 iptables。CentOS7 默认的防火墙不是 iptables，而是 firewalle。

```shell
# 先检查是否安装了iptables
service iptables status
# 没有的话就安装 iptables
yum install -y iptables
# 有的话就升级 iptables
yum update iptables 
# 安装 iptables-services
yum install iptables-services

# 另外，停止 firewalld 服务
systemctl stop firewalld
# 禁用 firewalld 服务
systemctl mask firewalld
```

安装之后就可以启用 iptables 了：

```shell
# 注册 iptables 服务
# 相当于以前的 chkconfig iptables on
systemctl enable iptables.service
# 开启服务
systemctl start iptables.service
# 查看状态
systemctl status iptables.service
```

## iptables 规则组成

iptables 具有 Filter、NAT、Mangle、Raw 四种内建表，每种表有不同的链,我们可以在链中添加不同的规则。

> Mangle 表和 Raw 一般不会用到，不再介绍。

### Filter 表

Filter 表是 iptables 的默认表，也是最常操作的表，它具有三种链:

* INPUT 链 – 处理来自外部的数据。
* OUTPUT 链 – 处理向外发送的数据。
* FORWARD 链 – 将数据转发到本机的其他网卡设备上。

### NAT 表

* PREROUTING 链 – 处理刚到达本机并在路由转发前的数据包。它会转换数据包中的目标 IP 地址（destination ip address），通常用于 DNAT(destination NAT)。

* POSTROUTING 链 – 处理即将离开本机的数据包。它会转换数据包中的源 IP 地址（source ip address），通常用于SNAT（source NAT）。

* OUTPUT 链 – 处理本机产生的数据包。

## 基础语法

### 常用命令

```shell
# 查看 iptables 现有规则
iptables -L -n
# 先允许所有，不然有可能会杯具
iptables -P INPUT ACCEPT
# 清空所有默认规则
iptables -F
# 清空所有自定义规则
iptables -X
# 所有计数器归 0
iptables -Z
# 允许来自于 lo 接口的数据包(本地访问)
iptables -A INPUT -i lo -j ACCEPT
# 开放 22 端口
iptables -A INPUT -p tcp --dport 22 -j ACCEPT
# 开放 21 端口(FTP)
iptables -A INPUT -p tcp --dport 21 -j ACCEPT
# 开放 80 端口(HTTP)
iptables -A INPUT -p tcp --dport 80 -j ACCEPT
# 开放 443 端口(HTTPS)
iptables -A INPUT -p tcp --dport 443 -j ACCEPT
# 允许 ping
iptables -A INPUT -p icmp --icmp-type 8 -j ACCEPT
# 允许接受本机请求之后的返回数据 RELATED，是为 FTP 设置的
iptables -A INPUT -m state --state  RELATED,ESTABLISHED -j ACCEPT
# 其他入站一律丢弃
iptables -P INPUT DROP
# 所有出站一律绿灯
iptables -P OUTPUT ACCEPT
# 所有转发一律丢弃
iptables -P FORWARD DROP
# 保存上述规则
service iptables save
# 重启 iptables 服务
systemctl restart iptables.service
```

### 其他设定

```shell
# 如果要添加内网 ip 信任（接受其所有 TCP 请求）
iptables -A INPUT -p tcp -s 45.96.174.68 -j ACCEPT
# 过滤所有非以上规则的请求
iptables -P INPUT DROP
# 要封停一个 IP，使用下面这条命令：
iptables -I INPUT -s ***.***.***.*** -j DROP
# 要解封一个 IP，使用下面这条命令:
iptables -D INPUT -s ***.***.***.*** -j DROP
```

## 问题

### vsftpd 在 iptables 开启后，无法使用被动模式

1. 首先在`/etc/sysconfig/iptables-config`中修改或者添加以下内容：

    ```conf
    # 添加以下内容，注意顺序不能调换
    IPTABLES_MODULES="ip_conntrack_ftp"
    IPTABLES_MODULES="ip_nat_ftp"
    ```

2. 重新设置 iptables 设置：

    ```shell
    iptables -A INPUT -m state --state  RELATED,ESTABLISHED -j ACCEPT
    ```

## 参考

1. [CentOS7 安装 iptables 防火墙](http://www.cnblogs.com/kreo/p/4368811.html)

