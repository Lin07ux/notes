### 1. Connection closed by remote host

> 参考：[ssh_exchange_identification: Connection closed by remote host under Git bash [closed]](https://stackoverflow.com/questions/10127818/ssh-exchange-identification-connection-closed-by-remote-host-under-git-bash)

在使用 Git 命令连接远程仓库的时候，有如下的错误提示：

```text
ssh_exchange_identification: Connection closed by remote host
```

在国内出现这个问题主要出现在通过 VPN 访问 GitHub 的情况，此时使用默认的 22 端口和`github.com`主机名访问时会被禁掉，但是可以使用 HTTPS 来访问。

所以，可以通过在`~/.ssh/config`文件中对`github.com`主机做如下的配置来解决无法链接 GitHub 仓库的问题：

```conf
Host github.com
 Hostname ssh.github.com
 Port 443
```

