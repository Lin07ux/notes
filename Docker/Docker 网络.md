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


