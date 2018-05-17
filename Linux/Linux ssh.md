### 修改 SSH 端口号

可以通过如下的方式修改 SSH 链接的端口号：

* 首先，修改`/etc/ssh/sshd_config`里的 Port 字段的值，比如将原先默认的`Port 22`改成` Port 2222`，那么就需要通过 2222 端口来进行 SSH 链接。

* 然后，重启 sshd 服务：`service sshd restart`。

> 如果有 iptables 或其他防火墙，还需要设置防火墙规则。

### 禁用密码登录

如果设置了通过 SSH Key 登录，那么就建议禁用掉密码登录，可以增加服务器的安全性。

禁用方式如下：

1. 打开`/etc/ssh/sshd_config`文件
2. 设置其中的`PasswordAuthentication`的值为`no`，如果没有这一项，则添加一行`PasswordAuthentication no`。
3. 重启 ssh：`systemctl restart sshd`



