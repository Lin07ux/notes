容器是独立运行的一个或一组应用，以及它们的运行态环境。容器是非常的轻量级的，很多时候都是可以随时删除和新建容器的。

> 对应的，虚拟机可以理解为模拟运行的一整套操作系统（提供了运行态环境和其他系统环境）和跑在上面的应用。

## 一、启停容器

### 1.1 启动容器

容器有两种启动方式：一种是基于镜像新建一个容器并立即启动，另外一种是将处于终止状态(stopped)的容器重新启动。

#### 1.1.1 新建并启动

新建并启动一个容器的时候，需要指定对应的镜像，使用的命令是：`docker run`。

```shell
# 新建一个容器，执行完成之后就终止容器
docker run --name ubuntu ubuntu:18.04 /bin/echo 'Hello world'

# 交互式运行一个容器
docker run -i -t --name ubuntu ubuntu:18.04 /bin/bash
```

使用`docker run`命令启动容器的时候，可以附带一些参数，方便对容器进行管理和操作：

* `-i` 选项让容器的标准输入打开；
* `-t` 选项让 Docker 分配一个伪终端(pseudo-tty)并绑定到容器的标准输入上；
* `-d` 让 Docker 容器在后台运行，而不是直接把容器执行命令的结果输出到当前宿主机中。

使用`-i`和`-t`选项可以让容器进入交互式模式，用户可以通过所创建的终端来输入命令，操作容器。而使用`-d`选项则可以避免在宿主机中看到容器运行时输出的内容（如果需要查看输出，可以使用`docker logs`命令）。使用`-d`选项启动容器之后，会返回一个唯一的容器 ID，该 ID 可以用于`docker container`相关的命令的参数。

> 容器是否会长久运行，是与`docker run`指定的命令或 Dockerfile 中设定的`CMD`指令有关，和`-d`参数无关。

使用`docker run`命令创建容器时，Docker 在后台运行的标准操作包括：

1. 检查本地是否存在指定的镜像，不存在就从公有仓库中下载；
2. 利用镜像创建并启动一个容器；
3. 分配一个文件系统，并在只读的镜像层外面挂载一层可读写层；
4. 从宿主主机配置的网桥接口中桥接一个虚拟接口到容器中去；
5. 从地址池配置一个 IP 地址给容器；
6. 执行用户指定的应用程序；
7. 执行完毕后容器被终止。

#### 1.1.2 启动已终止的容器

前面使用`docker run`启动的容器终止之后，可以使用`docker container start`命令将其重新启动，而不需要再次从镜像新建一个容器。

```shell
docker container start <容器名称>
```

容器的核心为所执行的应用程序，所需要的资源都是应用程序运行所必需的。除此之外并没有其他的资源。可以在伪终端中使用`ps`或`top`来查看进程信息。

```
root@ba267838cc1b:/# ps
  PID TTY          TIME CMD
    1 ?        00:00:00 bash
   11 ?        00:00:00 ps
```

可见，容器中仅运行了指定的 bash 应用。这种特点使得 Docker 对资源的利用率极高，是真正的轻量级虚拟化。

### 1.2 终止容器

当容器中指定的应用终结时，容器会自动终止。当然，也可以主动的来终止一个运行中的容器：

```shell
docker container stop <container_id>|<container_name>
```

使用该命令的时候，可以使用容器的 ID 或者容器的名称来指定要终止的容器。

### 1.3 重启容器

重启容器时，会先将容器停止，然后再启动容器：

```shell
docker container restart <container_id>|<container_name>
```

### 1.4 查看容器

可以使用`docker container ls`命令来查看容器列表，该命令和`docker image ls`命令很像：

```shell
docker contaner ls [container_id|container_name] [options]
```

参数：

* `-a` 该参数可以显示所有符合条件的容器，包括处于终止状态的容器。

## 二、进入容器

使用`-d`参数时，容器启动后会进入到后台。如果需要进入容器进行操作，可以使用`docker attach`命令或`docker exec`命令，推荐使用`docker exec`命令。

### 2.1 `docker attach`

使用`docker attach`命令进入到容器之后，如果从中退出(`exit`)，会导致容器的停止：

```shell
$ docker run -dit ubuntu
243c32535da7d142fb0e6df616a3c3ada0b8ab417937c853a9e1c251f499f550

$ docker container ls
CONTAINER ID   IMAGE          COMMAND       CREATED          STATUS          PORTS    NAMES
243c32535da7   ubuntu:latest  "/bin/bash"   18 seconds ago   Up 17 seconds            nostalgic_hypatia

$ docker attach 243c
root@243c32535da7:/#
```

### 2.2 `docker exec`

`docker exec`命令可以跟随多个参数，主要的有如下两个参数：

* `-i` 进入交互模式。如果只用`-i`参数，由于没有分配伪终端，所以不会有 Linux 命令提示符，但命令执行结果仍然可以正常返回；
* `-t` 使用该参数可以分配到一个伪终端，配合`-i`参数就能与在 Linux 终端中执行命令一样了。

使用`docker exec`命令进入容器后，退出(`exit`)时不会导致容器的停止。这也就是为什么建议使用该命令进入容器的原因。

```shell
$ docker run -dit ubuntu
69d137adef7a8a689cbcb059e94da5489d3cddd240ff675c640c8d96e84fe1f6

$ docker container ls
CONTAINER ID        IMAGE               COMMAND             CREATED             STATUS              PORTS               NAMES
69d137adef7a        ubuntu:latest       "/bin/bash"         18 seconds ago      Up 17 seconds                           zealous_swirles

# 仅使用 -i 参数则没有 Linux 命令提示符
$ docker exec -i 69d1 bash
ls
bin
boot
dev
...

# 使用 -it 参数就会进入到正常的 Linux 终端
$ docker exec -it 69d1 bash
root@69d137adef7a:/#
```

## 三、导入导出

### 3.1 导出容器

如果要导出本地某个容器，可以使用`docker export`命令，导出的内容是归档格式：

```shell
$ docker container ls -a
CONTAINER ID   IMAGE          COMMAND       CREATED         STATUS                    PORTS    NAMES
7691a814370e   ubuntu:18.04   "/bin/bash"   36 hours ago    Exited (0) 21 hours ago            test

$ docker export 7691a814370e > ubuntu.tar
```

### 3.2 导入容器

导出的容器快照可以使用`docker import`命令将其导入为镜像：

```shell
$ cat ubuntu.tar | docker import - test/ubuntu:v1.0

$ docker image ls
REPOSITORY    TAG      IMAGE ID        CREATED              VIRTUAL SIZE
test/ubuntu   v1.0     9d37a6082e97    About a minute ago   171.3 MB
```

另外，该命令也可以从 URL 或者某个目录来导入：

```shell
$ docker import http://example.com/exampleimage.tgz example/imagerepo
```

可以使用`docker load`命令来导入*镜像存储文件*到本地镜像库，也可以使用`docker import`命令导入一个*容器快照*到本地镜像库，这两者的区别在于：

* *镜像存储文件*会保留完整的记录，体积比较大，导入时可以重新指定标签等元数据信息；
* *容器快照*会丢弃所有的历史记录和元数据信息（即仅保存容器当时的快照状态）。

## 四、删除容器

### 4.1 删除指定容器

可以使用`docker container rm`命令来删除一个处于终止状态的容器：

```shell
docker container rm <container_id>|<container_name>
```

如果要删除一个运行中的容器，可以添加`-f`参数。此时 Docker 会发送`SIGKILL`信号给容器，然后再完成删除操作。

### 4.2 清理素有处于终止状态的容器

用 docker container ls -a 命令可以查看所有已经创建的包括终止状态的容器，如果数量太多，一个个删除会比较麻烦，可以使用下面的命令来清理掉所有处于终止状态的容器：

```shell
docker container prune
```

