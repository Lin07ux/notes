> 转摘：[SSH 端口转发教程](https://www.tuicool.com/articles/nQ7ZjyR)

## 一、配置

### 1.1 修改 SSH 端口号

可以通过如下的方式修改 SSH 链接的端口号：

* 首先，修改`/etc/ssh/sshd_config`里的 Port 字段的值，比如将原先默认的`Port 22`改成` Port 2222`，那么就需要通过 2222 端口来进行 SSH 链接。

* 然后，重启 sshd 服务：`service sshd restart`。

> 如果有 iptables 或其他防火墙，还需要设置防火墙规则。

### 1.2 禁用密码登录

如果设置了通过 SSH Key 登录，那么就建议禁用掉密码登录，可以增加服务器的安全性。

禁用方式如下：

1. 打开`/etc/ssh/sshd_config`文件
2. 设置其中的`PasswordAuthentication`的值为`no`，如果没有这一项，则添加一行`PasswordAuthentication no`。
3. 重启 ssh：`systemctl restart sshd`

## 二、端口转发

SSH 一般是作为常用的登录服务器的工具，除此之外，还可以用作端口转发(Port Forwarding)，又称作 SSH 隧道(Tunnel)。

SSH 作为数据通信的加密跳板，有多种使用场景，主要支持如下三种用法：

### 2.1 动态转发

第一种场景是，访问所有外部网站，都要通过 ssh 中介，这时就要把本地端口绑定到 ssh 跳板机。至于 ssh 跳板机要去访问哪一个网站，完全是动态的，所以叫做动态转发。

```shell
ssh -D <local-port> <tunnel-host> -N
```

上面命令中：

* `-D` 表示动态转发
* `-N` 表示只进行端口转发，不登录远程 Shell
* `local-port` 是本地端口
* `tunnel-host` 是 ssh 跳板机

举例来说，如果本地端口是 2121，跳板机 IP 为 10.10.10.1，那么实际命令就是下面这样：

```shell
ssh -D 2121 10.10.10.1 -N
```

这种转发**采用了 SOCKS5 协议**。访问外部网站时，需要把 HTTP 请求转成 SOCKS5 协议，才能把本地端口的请求转发出去。

下面是 ssh 隧道建立后的一个使用实例：

```shell
curl -x socks5://localhost:2121 http://www.example.com
```

上面命令中，curl 的`-x`参数指定代理服务器，即通过 SOCKS5 协议的本地 2121 端口，访问`http://www.example.com`。

### 2.2 本地转发

第二种场景是，只针对特定网站打开 ssh 隧道，这叫做“本地转发”。

```shell
ssh -L <local-port>:<target-host>:<target-port> <tunnel-host> -N
```

上面命令中：

* `-L` 参数表示本地转发
* `-N` 表示只进行端口转发，不登录远程 Shell
* `local-port` 是本地端口
* `target-host` 是想要访问的目标服务器
* `target-port` 是目标服务器的端口
* `tunnel-host` 是 ssh 跳板机

举例来说，在本地 2121 端口建立 ssh 隧道，访问`www.example.com`，可以写成下面这样：

```shell
ssh -L 8080:www.example.com:80 10.10.10.1 -N
```

此时，访问本机的 2121 端口，就是访问`www.example.com`：

```shell
curl http://localhost:2121
```

**本地端口转发采用 HTTP 协议，不用转成 SOCKS5 协议。**

### 2.3 远程转发

第三种场景比较特殊，主要针对内网的情况。

本地计算机在外网，ssh 跳板机和目标服务器都在内网，而且本地计算机无法访问内网之中的跳板机，但是跳板机可以访问本机计算机。由于本机无法访问跳板机，就无法从外网发起隧道，必须反过来，从跳板机发起隧道，这时就会用到远程端口转发。

```shell
ssh -R local-port:target-host:target-port local
```

命令中：

* `-R` 参数表示远程端口转发
* `-N` 表示只进行端口转发，不登录远程 Shell
* `local-port` 是本地端口
* `target-host` 是想要访问的目标服务器
* `target-port` 是目标服务器的端口

上面的命令，首先需要注意，**不是在本机(外网客户机)执行的，而是在 ssh 跳板机执行的**，从跳板机去连接本地(外网客户机)计算机。

比如，跳板机执行下面的命令，绑定本地计算机的 2121 端口，去访问`www.example.com:80`：

```shell
ssh -R 2121:www.example.com:80 local -N
```

执行上面的命令以后，跳板机到本地计算机的隧道已经建立了。然后，就可以从本机访问目标服务器了，即在本机执行下面的命令：

```shell
curl http://localhost:2121
```

这种端口转发会远程绑定另一台机器的端口，所以叫做“远程端口转发”，也是采用 HTTP 协议。

### 2.4 实例

#### 2.4.1 Email 加密下载

公共场合的 WiFi，如果使用非加密的通信，是非常不安全的。假定在咖啡馆里面，需要从邮件服务器明文下载邮件，怎么办？一种解决方法就是，采用本地端口转发，在本地电脑与邮件服务器之间，建立 ssh 隧道：

```shell
ssh -L 2121:mail-server:143 tunnel-host -N
```

上面命令指定本地 2121 端口绑定 ssh 跳板机 tunnel-host ，跳板机连向邮件服务器的 143端口。这样下载邮件，本地到 ssh 跳板机这一段，是完全加密的。当然，跳板机到邮件服务器的这一段，依然是明文的。

#### 2.4.2 简易 VPN

VPN 用来在外网与内网之间建立一条加密通道。内网的服务器不能从外网直接访问，必须通过一个跳板机。如果本机可以访问跳板机，就可以使用 ssh 本地转发，简单实现一个 VPN：

```shell
ssh -L 2080:corp-server:80 -L 2443:corp-server:443 tunnel-host -N
```

上面命令通过 ssh 跳板机，将本机的 2080 端口绑定内网服务器的 80 端口，本机的 2443 端口绑定内网服务器的 443 端口。

#### 2.4.3 两级跳板

端口转发可以有多级，比如新建两个 ssh 隧道，第一个隧道转发给第二个隧道，第二个隧道才能访问目标服务器：

```shell
# 创建第一级隧道
ssh -L 7999:localhost:2999 tunnel1-host
# 创建第二级隧道
ssh -L 2999:target-host:7999 tunnel2-host -N
```

上面命令：

1. 首先在本地 7999 端口与 tunnel1-host 之间建立一条隧道，隧道的出口是 tunnel1-host 的 localhost:2999，也就是 tunnel1-host 收到本地的请求以后，转发给其自身的 2999 端口。
2. 将第一台跳板机 tunnel1-host 的 2999 端口，通过第二台跳板机 tunnel2-host，连接到目标服务器 target-host 的 7999 端口。

最终效果就是，访问本机的 2999 端口，就会转发到 target-host 的 2999 端口。

