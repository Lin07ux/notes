### parameter inet_interfaces: no local interface found for ::1

启动 postfix 服务的时候，报错如下：

```
Job for postfix.service failed. See 'systemctl status postfix.service' and 'journalctl -xn' for details.
```

根据提示，使用`systemctl status postfix.service -l`查看错误原因，如下：

```
postfix/sendmail[1143]: fatal: parameter inet_interfaces: no local interface found for ::1
```

这是由于 postfix 默认会开启 IPv6 的支持，但是服务器没有提供 IPv6，所以需要修改 postfix 的配置文件，使其仅支持 IPv4。

打开配置文件：

```shell
vim /etc/postfix/main.cf
```

在其 116 行左右，可以看到如下配置：

```conf
inet_interfaces = localhost
inet_protocols = all
```

修改为如下内容并保存：

```conf
inet_interfaces = localhost # 只能接受来自本机的邮件
inet_protocols = ipv4 # 拒绝ipv6的本机地址::1
```

