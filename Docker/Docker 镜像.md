镜像是 Docker 容器的模板，相关的主要命令如下：

* `docker image pull` 拉取镜像
* `docker run` 运行镜像
* `docker image rm` 删除镜像
* `docker image ls` 展示本地已下载的镜像列表
* `docker image prune` 清理虚悬镜像
* `docker system df` 查看镜像、容器、数据卷所占用的空间

## 一、获取镜像

[Docker Hub](https://hub.docker.com/search?q=&type=image) 上有大量的高质量的镜像可以用，可以使用`docker pull`从其中获取镜像。命令如下：

```shell
docker pull [选项] [Docker Registry 地址[:端口号]]仓库名[:标签]
```

具体的选项可以通过`docker pull --help`命令看到。镜像名称的格式如下：

* Docker 镜像仓库地址：格式一般是`<域名/IP>[:端口号]`。默认地址是 Docker Hub(docker.io)。
* 仓库名：如之前所说，这里的仓库名是两段式名称，即`<用户名>/<软件名>`。对于 Docker Hub，如果不给出用户名，则默认为 library，也就是官方镜像。

比如，下面的命令是用来获取 Docker 官方镜像仓库中的 unbuntu 仓库中的 18.04 镜像：

```shell
docker pull ubuntu:18.04
```

## 二、运行镜像

拉取下来镜像之后，就可以使用`docker run`命令来运行镜像容器，并在容器启动完成之后进入到容器的 bash 中并且进行交互式操作：

```shell
docker run -it --rm ubuntu:18.04 bash
```

简要的说明一下上面用到的参数：

* `-it` 这是两个参数，一个是`-i`表示交互式操作，一个是`-t`表示终端。这里是进入 bash 执行一些命令，所以需要交互式终端。
* `--rm` 这个参数是说容器退出之后随之将其删除。默认情况下是不应该退出容器后就立即删除的，这里只是演示，不需要保留，所以使用这个参数可以避免浪费空间。
* `ubuntu:18.04` 这个参数用来指定容器启动的镜像。
* `bash` 放在镜像名后面的是**命令**，这里表示希望进入交互式的 Shell。

运行成功之后，可以使用`exit`命令退出这个容器。

## 三、删除镜像

可以使用`docker image rm`命令来删除本地的镜像，格式如下：

```shell
$ docker image rm [选项] <镜像1> [<镜像2> ...]
```

其中，可以使用镜像 ID、镜像名(repository:tag)、镜像摘要(repository@digests)来指定镜像。

删除的时候，会分为 Untagged 和 Deleted 两种行为：前者表示只删除标签，后者表示删除镜像。而且在执行 Deleted 行为的时候，也并非一定会删除全部所依赖的中间层镜像，除非这些中间层镜像不被其他镜像依赖了。

删除镜像时，还需要查看是否有容器对镜像存在依赖，如果存在这样的依赖（即使容器没有运行），镜像也是不可被删除的。

删除时，可以配合使用查看镜像的命令：

```shell
# 删除所有仓库名为 redis 的镜像
docker image rm $(docker image ls -q redis)

# 删除所有在 mongo:3.2 之前的镜像
docker image rm $(docker image ls -q -f before=mongo:3.2)
```

## 四、镜像列表

`docker image ls`命令可以获取当前本地已下载的镜像列表，命令格式如下：

```
docker image ls [-a|-f|-q] [repository[:tag]]
```

默认只展示顶层镜像，添加`-a`选项会展示包括中间层镜像在内的所有镜像，同时还可以使用过滤方式来只展示所需的镜像列表。

列表中包含：仓库名、标签、镜像 ID、创建时间 以及占用的空间。类似如下：

```
$ docker image ls
REPOSITORY     TAG           IMAGE ID            CREATED        SIZE
redis          latest      5f515359c7f8        5 days ago       183 MB
nginx          latest      05a60462f8ba        5 days ago       181 MB
mongo          3.2         fe9198c04d62        5 days ago       342 MB
<none>         <none>      00285df0df87        5 days ago       342 MB
ubuntu         18.04       f753707788c5        4 weeks ago      127 MB
ubuntu         latest      f753707788c5        4 weeks ago      127 MB
```

### 4.1 镜像仓库、镜像标签、镜像 ID

镜像仓库表示的是镜像的名称，一般是两段式名称，即`<用户名>/<软件名>`。对于 Docker Hub，如果不给出用户名，则默认为 library，也就是官方镜像。

镜像标签是镜像的一个版本名称，类似于 Git 中的标签概念。镜像如果有 bug 需要修复，或者版本更新，都会在更新后添加新的标签或者标记为已有的标签进行发布，这样可以确保任何使用这个标签的用户可以获得更安全、更稳定的镜像。

镜像 ID 是镜像的唯一标识，一个镜像可以对应多个标签，当多个标签指向相同的镜像版本时，它们对应的镜像 ID 就是一样的了，这类似于 Git 中的 Commit Hash。

### 4.2 镜像体积

镜像体积分为压缩后和展开后的大小：前者对应的是网络传输镜像时耗费的流量大小，后者对应的是镜像在本地磁盘中的大小。

镜像列表中展示的全部镜像占用空间的总和并非实际占用的大小。由于 Docker 使用 Union FS，相同的层只需要保存一份即可，因此实际镜像硬盘占用空间很可能要比这个列表镜像大小的总和要小的多。

### 4.3 虚悬镜像

上面的镜像列表中，还可以看到一个特殊的镜像，这个镜像既没有仓库名，也没有标签，均为`<none>`。

这种现象会在执行`docker pull`、`docker build`命令的时候出现：由于新旧镜像同名，旧镜像的名称被取消，从而出现仓库名、标签均为`<none>`的镜像。这类无标签镜像也被称为虚悬镜像(Dangling Image)。

### 4.4 中间层镜像

为了加速镜像构建、重复利用资源，Docker 会利用中间层镜像。

这些中间层镜像也是没有仓库名、标签的镜像，但是其与虚悬镜像是不同的：这些无标签的镜像是其他镜像所依赖的镜像，不应该被删除，否则会导致上层镜像因为依赖丢失而出错。而在删除依赖这些中间层镜像的镜像后，它们也会被删除掉。



