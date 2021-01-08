Docker 允许通过外部访问容器或容器之间互联的方式来提供网络。

## 一、外部访问容器

如果需要在容器外部访问到容器内部的网络服务，需要指定端口映射，将容器的端口暴露出来。

### 1.1 端口映射

在启动容器的时候，可以使用`-p`和`-P`选项来指定主机与容器的端口映射：

* `-p` 将容器内部指定的网络端口映射到主机中(Publish a container's port(s) to the host)；
* `-P` 将容器内部所有开放的网络端口分别随机映射到主机的某个端口上(Publish all exposed ports to random ports)。

使用`-p`指定端口映射时，支持的格式有：

* `hostIp:hostPort:containerPort` 将容器中指定的端口映射到主机特定 IP 上的特定端口；
* `hostIp::containerPort` 将容器中指定的端口映射到主机特定 IP 上的任意端口；
* `hostPort:containerPort` 容器的端口映射到主机所有的 IP 地址上的指定端口。

> `-p`选项可以使用多次，而且`-p`选项的每种格式都支持在后面加上`/udp`来指定`udp`端口。

示例如下：

```shell
# 映射到主机的 127.0.0.1 IP 上的 80 端口
$ docker run -d -p 127.0.0.1:80:80 nginx:alpine

# 映射到主机的 127.0.0.1 IP 上的任意端口，本地主机会自动分配一个端口
$ docker run -d -p 127.0.0.1::80 nginx:alpine

# 将主机所有 IP 的 80 端口映射到容器的 80 端口
$ docker run -d -p 80:80 nginx:alpine

# 映射多个端口
$ docker run -d -p 80:80 -p 443:443 nginx:alpine

# 使用 udp 端口
$ docker run -d -p 127.0.0.1:80:80/udp nginx:alpine
```

### 1.2 查看端口映射配置

可以使用`docker container ls`命令来查看容器的端口映射，映射配置在输出的 PORTS 列中展示：

```shell
# 开启随机映射
$ docker run -d -P nginx:alpine

# 在输出的 PORTS 列可以看到容器的端口映射
$ docker container ls -l
CONTAINER ID   IMAGE           ...    PORTS                   NAMES
fae320d08268   nginx:alpine    ...    0.0.0.0:32768->80/tcp   bold_mcnulty
```

也可以使用`docker port <容器名称或 ID> [port]`命令来查看当前映射的端口配置。比如，查看容器的 80 端口的映射：

```shell
$ docker port fa 80
0.0.0.0:32768
```

## 二、容器互联

容器之间进行网络互联，需要为容器指定自定义的 Docker 网络。

> 以前的`--link`选项已被废弃，不建议使用。多个容器之间需要相互连接，推荐使用 Docker Compose。

### 2.1 新建网络

新建网络的命令为：

```shell
docker network create [-d bridge|overlay] <net-name>
```

其中，`-d`选项用来指定 Docker 网络类型，值可以为`bridge`和`overlay`，一般使用`bridge`即可。

比如，下面新建一个名字叫做`my-net`的 Docker 网络：

```shell
$ docker network create -d bridge my-net
```

### 2.2 连接容器

在启动容器的时候，可以使用`--network`选项来指定容器连接到的网络上。

比如，下面在两个终端中分别启动一个容器，并设定连接到新建的`my-net`网络：

```shell
# 终端 1：
$ docker run -it --rm --name busybox1 --network my-net busybox sh

# 终端 2：
$ docker run -it --rm --name busybox2 --network my-net busybox sh
```

启动完成之后，可以分别在两个容器中 ping 对方，来证明这两个容器建立了互联关系：

```shell
# 在容器 1 中（即：终端 1）
/ # ping busybox2
PING busybox2 (172.19.0.3): 56 data bytes
64 bytes from 172.19.0.3: seq=0 ttl=64 time=0.072 ms
64 bytes from 172.19.0.3: seq=1 ttl=64 time=0.118 ms

# 在容器 2 中（即：终端 2）
/ # ping busybox1
PING busybox1 (172.19.0.2): 56 data bytes
64 bytes from 172.19.0.2: seq=0 ttl=64 time=0.064 ms
64 bytes from 172.19.0.2: seq=1 ttl=64 time=0.143 ms
```

可以看到，这两个容器是可以互相 ping 通的，而且分别有了同一个网段下的 IP 地址：`172.19.0.2`和`172.19.0.3`。

## 三、高级网络

### 3.1 Docker 网络基本原理

![Docker 网络](http://cnd.qiniu.lin07ux.cn/markdown/1610117527874.png)

当 Docker 启动时，会自动在主机上创建一个`docker0`虚拟网桥，实际上是 Linux 的一个 bridge，可以理解为一个软件交换机，它会挂载到它的网口之间进行转发。

同时，Docker 会随机分配一个本地未占用的私有网段中的一个 IP 地址给`docker0`接口(比如`172.17.42.1`)。此后启动的容器内的网口也会自动分配一个同一网段(`172.17.0.0/16`)的 IP 地址。

当创建一个 Docker 容器的时候，会同时创建一对`veth pair`接口：这对接口一端在容器内(即`eth0`)，另一端在本地并被挂载到`docker0`网桥，名称以`veth`开头(如`vethAQI2QT`)。当数据包发送到一个接口时，另外一个接口也可以接收到相同的数据包。通过这种方式，主机可以跟容器通信，容器之间也可以相互通信。

如此，Docker 就在在主机和所有容器之间创建了一个虚拟共享网络。

### 3.2 容器访问控制

容器的访问控制主要是通过 Linux 上的 iptables 防火墙来进行管理和实现的。

**容器访问外部网络**

容器想要访问外部网络，需要本地系统的转发支持。如果在启动 Docker 服务的时候设定`--ip-forward=true`，Docker 就会自动设定系统的`ip_forward`参数为 1：

```shell
# Linux 中查看 ip-forward 的设置
$ sysctl net.ipv4.ip_forward
net.ipv4.ip_forward = 1

# 开启 ip-forward
$ sysctl -w net.ipv4.ip_forward=1
```

**容器之间访问**

容器之间相互访问，需要两方面的支持：

* 容器的网络拓扑是否已经互联。默认情况下，所有的容器都会被连接到`docker0`网桥上。
* 本地系统的防火墙软件(iptables)是否允许连接。

启动 Docker 服务的时候，默认会添加一条转发策略到本地主机 iptables 的 FORWARD 链上。策略为通过(`ACCEPT`)还是禁止(`DROP`)取决于 Docker 服务的配置`--icc=true`（缺省值）还是`--icc=false`。

所以，默认情况下，不同容器之间是允许网络互通的。

> 如果为了安全考虑。可以在`/etc/docker/daemon.json`文件中的配置`{"icc": false}`来禁止它。

### 3.3 端口映射的实现

默认情况下，容器可以主动连接到外部网络，但是外部网络无法访问到容器内。

**容器访问外部实现**

容器所有到外部网络的连接，源地址都会被 NAT 成本地系统的 IP 地址。这是使用`iptables`的源地址伪装操作实现的。

查看主机的 NAT 规则：

```shell
$ sudo iptables -t nat -nL
...
Chain POSTROUTING (policy ACCEPT)
target     prot opt source               destination
MASQUERADE  all  --  172.17.0.0/16       !172.17.0.0/16
...
```

其中，上述规则将所有源地址在`172.17.0.0/16`网段、目标地址在其他网段（外部网络）的流量动态伪装成从系统网卡发出。MASQUERADE 跟传统 SNAT 的好处是它能动态从网卡获取地址。

**外部访问容器实现**

容器允许外部访问，可以在`docker run`的时候通过`-p`或`-P`参数来启动，其实也是在本地的 iptables 的 nat 表中添加相应的规则。

比如，使用`-P`时：

```shell
$ iptables -t nat -nL
...
Chain DOCKER (2 references)
target     prot opt source               destination
DNAT       tcp  --  0.0.0.0/0            0.0.0.0/0            tcp dpt:49153 to:172.17.0.2:80
```

使用`-p 80:80`时：

```shell
$ iptables -t nat -nL
Chain DOCKER (2 references)
target     prot opt source               destination
DNAT       tcp  --  0.0.0.0/0            0.0.0.0/0            tcp dpt:80 to:172.17.0.2:80
```

当然，也可以使用`-p IP:host_port:container_port`或`-p IP::port`来指定允许访问容器的主机上的 IP、接口等。

