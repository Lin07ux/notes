可以通过如下的方式修改 SSH 链接的端口号：

* 首先，修改`/etc/ssh/sshd_config`里的 Port 字段的值，比如将原先默认的`Port 22`改成` Port 2222`，那么就需要通过 2222 端口来进行 SSH 链接。

* 然后，重启 sshd 服务：`service sshd restart`。



