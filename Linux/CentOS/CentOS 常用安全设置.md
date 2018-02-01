### 设置密码最长有效期

将系统的密码最长使用时间设置为较短时间，建议为 1095 天。

```shell
vim /etc/login.defs
```

找到其中的`PAS_MAX_DAYS`项，设置为`PASS_MAX_DAYS   1095`。

### crontab 权限设置

设置可以创建 crontab 任务的白名单，而不是用黑名单机制：

```shell
rm -f /etc/cron.deny /etc/at.deny
touch /etc/cron.allow /etc/at.allow
chmod 0600 /etc/cron.allow /etc/at.allow
```

### 禁止转发 ICMP 重定向报文

先关闭重定向：

```shell
sysctl -w net.ipv4.conf.all.send_redirects=0
```

然后检查`/etc/sysctl.conf`文件中是否有如下的设置，没有的话就添加进去：

```conf
net.ipv4.conf.all.send_redirects=0
```


