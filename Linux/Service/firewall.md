> 转摘：[Linux CentOS7 开通端口外网端口访问权限](https://www.cnblogs.com/gz666666/p/12710457.html)

Linux 的多个发行版本都默认安装了 firewall 服务作为防火墙，其可以控制 Liunx 系统的端口开放情况和网络数据连通等。

### 1. 服务状态

```shell
systemctl status firewall # 查看服务状态
systemctl start firewall # 开启服务
systemctl stop firewall # 停止服务
systemctl disable firewall # 禁止开机自启动
systemctl enable firewall # 启用开机自启动
```

### 2. 基本参数

* `--version` 查看版本；
* `--state` 查看状态；
* `--reload` 重新启动以更新防火墙规则；
* `--list-port` 查看打开的端口；
* `--zone` 指定访问区域，一般使用`--zone=public`来指定配置外网访问；
* `--get-active-zones` 查看区域信息；

### 3. 开启端口外网 TCP 访问

```shell
# 开启 TCP 80 端口的外网访问
firewall-cmd --zone=public --add-port=80/tcp --permanent

# 开启 TCP 443 端口的外网访问
firewall-cmd --zone=public --add-port=443/tcp --permanent

# 开放多个端口的 TCP 外网访问
firewall-cmd --zone=public --add-port=80-85/tcp --permanent
```

> `--permanent`表示永久生效，不指定此参数的话，重启 firewall 服务后该配置就会失效。

### 4. 其他常用命令

```shell
# 查看所有打开外网访问的端口
firewall-cmd --zone=public --list-ports

# 查看指定接口所属区域
firewall-cmd --get-zone-of-interface=eth0

# 拒绝所有包
firewall-cmd --panic-on

# 查看是否拒绝
firewall-cmd --query-panic
```

