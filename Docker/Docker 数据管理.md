在容器中管理数据主要有两种方式：

* 数据卷(Volumes)
* 挂载主机目录(Bind Mounts)

## 一、数据卷

**数据卷是被设计用来持久化数据**的，它的生命周期独立于容器，Docker 不会在容器被删除后自动删除数据卷，并且也不存在垃圾回收这样的机制来处理没有任何容器引用的数据卷。

> 如果需要在删除容器的同时移除数据卷，可以在删除容器的时候使用`docker rm -v`命令。

数据卷是一个可供一个或多个容器使用的特殊目录，它绕过了 UFS，可以提供很多有用的特性：

* 数据卷可以在容器之间共享和重用；
* 对数据卷的修改会立马生效；
* 对数据卷的更新不会影响镜像；
* 数据卷默认会一直存在，即使容器被删除。

> 注意：数据卷的使用类似于 Linux 下对目录或文件进行 mount，容器中的被指定为挂载点的目录中的文件会复制到数据卷中（仅数据卷为空时会复制）。

### 1.1 数据卷基本命令

* `docker volume ls` 查看所有的数据卷
* `docker volume create <数据卷名称>` 创建一个数据卷
* `docker volume inspect <数据卷名称>` 查看数据卷的信息
* `docker volume rm <数据卷名称>` 删除一个数据卷
* `docker volume prune` 清理无主的数据卷

查看数据卷的信息如下：

```shell
$ docker volume inspect my-vol
[
    {
        "Driver": "local",
        "Labels": {},
        "Mountpoint": "/var/lib/docker/volumes/my-vol/_data",
        "Name": "my-vol",
        "Options": {},
        "Scope": "local"
    }
]
```

### 1.2 使用数据卷

在用`docker run`命令的时候，可以使用`--mount`标记来讲数据卷挂载到容器里，而且可以一次挂载多个数据卷。

下面创建一个名为 web 的容器，并加载一个数据卷到容器的`/usr/share/nginx/html`目录：

```shell
$ docker run -d -P --name web \
    # -v my-vol:/usr/share/nginx/html \
    --mount source=my-vol,target=/usr/share/nginx/html \
    nginx:alpine
```

然后就可以在宿主机中使用以下命令来查看 web 容器的信息：

```shell
$ docker inspect web
```

输出的数据中，`Mounts`键对应的数据为数据卷信息：

```JSON
"Mounts": [
    {
        "Type": "volume",
        "Name": "my-vol",
        "Source": "/var/lib/docker/volumes/my-vol/_data",
        "Destination": "/usr/share/nginx/html",
        "Driver": "local",
        "Mode": "",
        "RW": true,
        "Propagation": ""
    }
],
```

## 二、挂载主机目录

将主机的目录挂载到容器中，也可以实现容器与主机的数据同步，不过挂载主机目录主要是为了方便的在主机控制容器的文件，方便做测试、开发等工作。

### 2.1 挂载主机目录作为数据卷

`docker run`命令使用`--mount`选项可以指定挂载一个本地主机的目录到容器中去：

```shell
$ docker run -d -P --name web \
    # -v /src/webapp:/usr/share/nginx/html \
    --mount type=bind,source=/src/webapp,target=/usr/share/nginx/html \
    nginx:alpine
```

上面的命令会加载主机的`/src/webapp`目录到容器的`/usr/share/nginx/html`目录。

需要注意的是：

* 主机目录的路径必须是绝对路径；
* 使用`-v`选项时如果主机目录不存在，Docker 会自动在主机中创建该文件夹，使用`--mount`选项时如果主机目录不存在 Docker 会报错。

Docker 挂载主机目录的默认权限是`读写`，也建议通过增加`readonly`指定为`只读`：

```shell
$ docker run -d -P --name web \
    # -v /src/webapp:/usr/share/nginx/html:ro \
    --mount type=bind,source=/src/webapp,target=/usr/share/nginx/html,readonly \
    nginx:alpine
```

这样，在容器中的`/usr/share/nginx/html`目录新建文件、文件夹时，会显示如下的错误：

```shell
/usr/share/nginx/html # touch new.txt
touch: new.txt: Read-only file system
```

### 2.2 挂载主机文件作为数据卷

`--mount`标记也可以从主机挂载单个文件到容器中：

```shell
$ docker run --rm -it \
   # -v $HOME/.bash_history:/root/.bash_history \
   --mount type=bind,source=$HOME/.bash_history,target=/root/.bash_history \
   ubuntu:18.04 \
   bash

root@2affd44b4667:/# history
1  ls
2  diskutil list
```

把主机的`.bash_history`挂载到容器中，就可以记录到容器中输入过的命令了。

### 2.3 查看挂载文件/目录的信息

在主机中使用`docker inspect web`命令查看容器中的挂载的数据卷的信息，输出中的`Mounts`键的值类似如下：

```JSON
"Mounts": [
    {
        "Type": "bind",
        "Source": "/src/webapp",
        "Destination": "/usr/share/nginx/html",
        "Mode": "",
        "RW": true,
        "Propagation": "rprivate"
    }
],
```

