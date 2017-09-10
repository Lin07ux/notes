## 简介

Linux 系统在内核中提供了对报文数据包过滤和修改的官方项目，名为 Netfilter，它指的是 Linux 内核中的一个框架，可以用于在不同阶段将某些钩子函数（hook）作用于网络协议栈。Netfilter 本身并不对数据包进行过滤，它只是允许可以过滤数据包或修改数据包的函数挂接到内核网络协议栈中的适当位置。这些函数是可以自定义的。

iptables 是用户层的工具，它提供命令行接口，能够向 Netfilter 中添加规则策略，从而实现报文过滤，修改等功能。Linux 系统中并不止有 iptables 能够生成防火墙规则，其他的工具如 firewalld 等也能实现类似的功能。

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




