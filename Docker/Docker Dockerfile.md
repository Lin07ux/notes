Docker 镜像就是一层一层的配置、文件的叠加组成，所以可以通过指定每一层的内容的方式来自定义所需的镜像，而 Dockerfile 就是一种指定每层内容的文本文件，而且简单高效。

> 虽然也可以使用`docker commit`命令构建镜像，但是这种方法构建的镜像会出现体积臃肿、变更无法查看和追溯等问题，所以不建议使用该命令构建镜像。

### 1. FROM 指定基础镜像

`FROM`指令是 Dockerfile 中的第一条指令，而且是必备指令，用来指定基础镜像。

[Docker Hub](https://hub.docker.com/search?q=&type=image&image_filter=official) 中可以找到非常多的高质量的官方镜像，比如各种红包给服务类镜像、开发/构建/运行时镜像、操作系统类镜像等。

Docker 还存在一个特殊的镜像，名为`scratch`，这个镜像是虚拟的概念，并不实际存在，表示一个空白的镜像。以该镜像为基础的话，意味着不以任何镜像为基础，后续的指令将作为镜像的第一层开始而存在：

```yaml
FROM scratch
...
```

> 不以任何系统为基础，直接将可执行文件复制进镜像的做法并不罕见，比如 swarm、etcd。对于 Linux 下静态编译的程序来说，并不需要有操作系统提供运行时支持，所需的一切库都已经在可执行文件里了，因此直接`FROM scratch`会让镜像体积更加小巧。使用 Go 语言 开发的应用很多会使用这种方式来制作镜像，这也是为什么有人认为 Go 是特别适合容器微服务架构的语言的原因之一。

### 2. RUN 执行命令

`RUN`指令是用来执行命令行命令的。由于命令行的强大能力，`RUN`指令是在定制镜像时最常用的指令之一。其格式有两种：

* Shell 格式：`RUN <命令>` 这种格式与直接在命令行中输入命令一样；
* Exec 格式：`RUN ["可执行文件", "参数 1"...]` 这种格式类似于函数调用。

> Exec 格式在解析时会被解析成 JSON 数组，因此一定要使用双引号`"`，不是单引号`'`。

Dockerfi 中的每一个指令都会建立一层，`RUN`命令的行为也一样：新建立一层，然后在其上执行命令，执行结束后`commit`这一层的提交，构建成新的镜像。

> 另外，Union FS 是有最大层数限制的，比如 AUFS，曾经是最大不得超过 42 层，现在是不得超过 127 层。

所以，为了保持镜像的简洁，减少不必要的文件、配置的引入，建议尽量合并实现相同功能的`RUN`指令，并在执行完成之前进行清理工作。例如：

```yaml
FROM debian:stretch

RUN set -x; buildDeps='gcc libc6-dev make wget' \
    && apt-get update \
    && apt-get install -y $buildDeps \
    && wget -O redis.tar.gz "http://download.redis.io/releases/redis-5.0.3.tar.gz" \
    && mkdir -p /usr/src/redis \
    && tar -xzf redis.tar.gz -C /usr/src/redis --strip-components=1 \
    && make -C /usr/src/redis \
    && make -C /usr/src/redis install \
    # 删除中间文件
    && rm -rf /var/lib/apt/lists/* \
    && rm redis.tar.gz \
    && rm -r /usr/src/redis \
    # 执行系统清理
    && apt-get purge -y --auto-remove $buildDeps
```

### 3. Content 构建上下文

使用 Dockerfile 构建镜像的时候，需要使用`docker build`命令，该命令需要提供一个上下文路径，一般直接就设置为了`.`，表示当前的目录。但是这个参数并非是用来指定 Dockerfile 的路径的，而是指构建时的上下文路径。

Docker 在运行时分为 Docker 引擎（也就是服务器端守护进程，虽然也存在于本地）和客户端工具。Docker 的引擎提供了一组 REST API，被称为 [Docker Remote API](https://docs.docker.com/develop/sdk/)，而如`docker`命令这样的客户端工具就是通过这组 API 与 Docker 引擎交互，从而完成各种功能。因此，虽然好像是在本机执行各种`docker`命令，但实际上，一切都是使用远程调用的形式在服务器端（Docker 引擎）完成的。

在进行镜像构建的时候，并非所有的镜像定制都只需要通过`RUN`指令完成，经常会需要将一些本地文件复制进镜像（如`COPY`、`ADD`指令等）。而实际的构建是发生在服务器端（也就是 Docker 引擎）。

为了能让 Docker 引擎在构建的时候正确的得到本地文件，就需要为`docker build`命令提供上下文路径，然后客户端会将该路径下的所有内容打包，上传给 Docker 引擎。这样 Docker 引擎在执行构建时就会得到构建镜像所需的一起文件。

比如：

```yaml
COPY ./package.json /app/
```

这并不是要复制执行`docker build`命令时所在的目录下的`oackage.json`文件，也不是复制 Dockerfile 文件所在的目录下的`package.json`文件，而是复制执行`docker build`执行的上下文路径参数目录下的`package.json`文件。

因此，`COPY`这类执行的源文件的路径都是相对路径，这也是为什么`COPY ../package.json /app`或`COPY /opt/xxxx /app`无法工作的原因，因为这些路径已经超出了上下文的范围，Docker 引擎无法获得这些位置的文件。

其实，这在`docker build`命令的输出中已经有所展示了：

```
$ docker build -t nginx:v3 .
Sending build context to Docker daemon 2.048 kB
...
```

构建时的上下文除了可以指定为本地的路径之外，还可以使用 URL(如 Git 仓库)、压缩包、标准输入作为上下文：

```shell
# 使用 Git 仓库
docker build -t hello-world https://github.com/docker-library/hello-world.git#master:amd64/hello-world

# 使用压缩包：Docker 引擎会下载这个包，并自动解压缩，以其作为上下文，开始构建
docker build http://server/context.tar.gz

# 读取标准输入中的 Dockerfile：没有上下文，不可使用 COPY、ADD 等指令
docker build - < Dockerfile
# 或
cat Dockerfile | docker build -

# 从标准输入中读取上下文压缩包进行构建：支持 gzip、bzip2 以及 xz 格式的压缩包
docker build - < content.tar.gz
```

### 4. COPY 复制文件

`COPY`指令将构建上下文目录中的`<源路径>`指定的文件/目录复制到新的一层的镜像内的`<目标路径>`位置。和`RUN`指令一样，`COPY`指令也有两种格式：

* Shell 格式：`COPY [--chown=<user>:<group>] <源路径>... <目标路径>`
* Exec 格式：`COPY [--chown=<user>:<group>] ["<源路径>",... "<目标路径>"`

其中：

* **源路径**：可以是一个或者多个，也可以是通配符（规则要满足 Go 中的 [filepath.Match](https://golang.org/pkg/path/filepath/#Match)）规则；
* **目标路径**：可以是容器内的绝对路径，也可以是相对于工作目录的相对路径（工作目录可以使用`WORKDIR`指令来指定）。在复制文件前自动创建缺失的目录。

使用`COPY`指令复制文件的时候，源文件的各种元数据都会保留，比如：读/写/执行权限、文件变更时间等。如果源路径为文件夹，复制的时候不是直接复制该文件夹，而是将文件夹中的内容复制到目标路径中。

例如：

```YAML
COPY hom* /mydir/
COPY hom?.txt /mydir/

COPY --chown=55:mygroup files* /mydir/
COPY --chown=bin files* /mydir/
COPY --chown=1 files* /mydir/
COPY --chown=10:11 files* /mydir/
```

### 5. ADD 高级复制

`ADD`和`COPY`指令的格式和形式基本一致，但是比`COPY`指令多了可以从 URL 下载、自动解压(gzip/bzip2/xz)等功能。

虽然`ADD`指令更加高级，但是它默认下载的文件会设置成`600`权限，而且并不能解压其他格式的压缩包，所以通常需要使用更多一层的`RUN`指令来协作。而且，`ADD`指令会另镜像构建缓存失效，从而可能会令镜像构建变的比较缓慢。

因此，通常建议使用`COPY`或`RUN`指令来完成`ADD`指令的功能，而`ADD`指令仅在需要自动解压的情况下考虑使用。

示例：

```yaml
ADD ubuntu-xenial-core-cloudimg-amd64-root.tar.gz /

ADD --chown=55:mygroup files* /mydir/
ADD --chown=bin files* /mydir/
ADD --chown=1 files* /mydir/
ADD --chown=10:11 files* /mydir/
```

### 6. CMD 容器启动命令

`CMD`指令用于指定默认的容器主进程的启动命令。因为 Docker 不是虚拟机，而容器就是进程。既然是进程，那么在启动容器的时候就需要指定其运行的程序及参数，这就是`CMD`指令的作用。

`CMD`指令也有两种格式：

* Shell 格式：`CMD <命令> <参数 1> ...`
* Exec 格式：`CMD ["可执行文件", "参数 1", "参数 2"...]`

一般建议使用 Exec 格式，如果使用 Shell 格式的话，实际的命令会被包装为`sh -c`的参数的形式被执行。比如：

```yaml
CMD echo $HOME
```

在实际执行中，会变成：

```yaml
CMD [ "sh", "-c", "echo $HOME" ]
```

这就是为什么可以使用环境变量的原因，因为这些环境变量会被 Shell 进行解析处理。

另外，在使用`docker run`命令运行容器的时候，可以指定新的命令来替代镜像设置中的这个默认命令。比如，`ubuntu`镜像默认的`CMD`是`/bin/bash`，如果在命令行中使用`docker run -it ubuntu`的话，就会进入到`bash`中；如果使用`docker run -it ubuntu cat /etc/os-release`启动 ubuntu 容器，那么就会输出系统版本信息，然后立即结束容器。

提到`CMD`就不得不提*容器中应用在前台执行和后台执行*的问题。这是常出现的一个混淆。

Docker 不是虚拟机，而容器内是没有后台服务的概念的，所以容器中的应用都应该以前台的方式执行。对于容器而言，其启动程序就是容器应用进程，容器就是为了主进程而存在的；主进程如果退出了，那么容器就失去了存在的意义，继而也会自动退出，其他的辅助进程不是容器需要关心的东西。这也是前面使用使用`docker run -it ubuntu cat /etc/os-release`启动的 ubuntu 容器，在输出系统版本信息后就立即退出的原因。

以常用的 nginx 容器为例，如果将启动命令写成如下形式：

```yaml
CMD service nginx start
```

在启动该容器的时候，会发现容器执行后就立即退出了，甚至在容器内使用`systemctl`命令结果发现根本执行不了：使用`service nginx start`命令是希望容器启动以后以后台收进程形式启动 Nginx 服务，而`CMD service nginx start`在执行时会被理解为`CMD [ "sh", "-c", "service nginx start"]`，因此主进程实际上是`sh`。那么当`service nginx start`命令结束后，`sh`也就结束了，而`sh`作为主进程退出了，自然就会让容器也退出了。

正确的做法是使用 Exec 格式直接执行`nginx`可执行文件，并且以前台形式运行：

```yaml
CMD ["nginx", "-g", "daemon off;"]
```

