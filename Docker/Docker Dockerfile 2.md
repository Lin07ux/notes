### 10. VOLUME 匿名卷

容器运行时应该尽量保持容器存储层不发生写操作，需要保留的数据应该保存于卷(Volume)中。而为了防止用户忘记将动态文件所保存的目录挂载为卷，可以在 Dockerfile 中事先指定某些目录挂载为匿名卷。这样，在运行时如果用户不指定挂载，其应用也是可以正常运行，且不会向容器存储层中写入数据。

`VOLUME`指令的格式为：

* `VOLUME ["<路径 1>", "<路径 2>" ...]`
* `VOLUME <路径>`

比如：

```yaml
VOLUME /data
```

这里的`/data`目录就会在运行时自动挂载为匿名卷，向`/data`中写入的任何数据都不会被纪录进容器存储层，从而保证了容器存储层的无状态化。

当然，运行的时候可以覆盖这个挂载设置，如：

```yaml
docker run -d -v mydata:/data xxx
```

在这个命令中，就将`mydata`这个命名卷挂载到了`/data`位置，替代了 Dockerfile 中定义的匿名卷的挂载配置。

### 11. EXPOSE 暴露端口

`EXPOSE`指令声明运行时容器提供服务的端口，格式为：`EXPOSE <端口 1> [<端口 2> ...]`。

需要注意的是：这仅仅是一个声明，在运行时并不会因为这个声明应用就会开启这个端口的服务。

在 Dockerfile 中写入这样的声明的好处有两个：

* 帮助镜像使用者理解这个镜像服务的守护端口，方便配置映射；
* 在运行时使用随机端口映射(`docker run -P`)时，会自动随机映射到`EXPOSE`的端口上。

需要将`EXPOSE`指令和在运行时使用`-p <宿主端口>:<容器端口>`区分开：

* `-p` 选项是映射宿主度哪款和容器端口，也就说：将容器的对应端口服务公开给外界访问；
* `EXPOSE`仅仅是声明容器打算使用什么端口，并不会自动在宿主进行端口映射。

### 12. WORKDIR 指定工作目录

`WORKDIR`指令用来指定工作目录（或称为当前目录），之后各层的指令执行时的当前目录就被设置为这个目录。

指令格式为：`WORKDIR <工作目录路径>`。

如果指定的目录不存在，`WORKDIR`指令会帮助建立该目录。而且，如果`WORKDIR`指令使用的是相对路径，那么所切换的路径与之前的`WORKDIR`相关。

比如：

```yaml
WORKDIR /a
WORKDIR b
WORKDIR c

RUN pwd
```

这里构造时，`pwd`命令输出的结果为`/a/b/c`。

之所以需要使用`WORKDIR`指令来设置当前工作目录，是因为不同层的指令在执行的时候都会将工作目录切换成默认的工作目录，而不会相互影响。

比如：

```yaml
...

RUN cd /app
RUN echo "hello" > world.txt
```

这个 Dockerfile 文件构建之后运行时，可能会出现找不到`/app/world.txt`文件，或者文件内容不对。这是因为，每一个`RUN`指令都是启动一个新的容器，然后执行命令，所以第一个`RUN`指令虽然切换了工作目录，但是第二个`RUN`指令是在一个新的容器中执行文件内容的写入的，命令执行的时候已经重新切回到了默认的工作目录了。

要解决这个问题，就需要使用`WORKDIR`指令了：

```yaml
WORKDIR /app

RUN echo "hello" > world.txt
```

### 13. USER 指定当前用户

`USER`指令指定当前用户，并影响之后的层执行`RUN`、`CMD`以及`ENTRYPOINT`时的用户身份。

指令格式为：`USER <用户名>[:<用户组>]`。

需要注意的是：`USER`指令仅会切换当前用户，并不会创建不存在的用户，所以需要先确保要切换的用户是存在的，否则无法正常切换。

比如：

```yaml
RUN groupadd -r redis && useradd -r -g redis redis
USER redis
RUN [ "redis-server" ]
```

如果需要在脚本中改变用户，可以使用`gosu`命令来协助实现：

```yaml
# 下载 gosu
RUN wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/1.12/gosu-amd64" \
&& chmod +x /usr/local/bin/gosu \
# 建立 redis 用户
RUN groupadd -r redis && useradd -r -g redis redis
&& gosu nobody true
# 设置 CMD，并以 redis 用户执行
CMD [ "exec", "gosu", "redis", "redis-server" ]
```

### 14. HEALTHCHECK 健康检查

`HEALTHCHECK`指令告诉 Docker 应该如何判断容器的状态是否正常。

该指令有两种格式：

* `HEALTHCHECK [选项] CMD <命令>` 设置容器健康检查命令；
* `HEALTHCHECK NONE` 屏蔽基础镜像的健康检查指令。

`HEALTHCHECK`指令有如下三个选项：

* `--interval=<间隔>` 两次健康检查的间隔，默认为 30 秒；
* `--timeout=<时长>` 健康检查命令运行的超时时间，如果超过这个时间，本次健康检查就被视为失败，默认为 30 秒；
* `--retries=<次数>` 当健康检查连续失败次数达到这个选项的值时，将容器的状态视为`unhealthy`，默认为 3 次。

和`CMD`、`ENTRYPOINT`一样，`HEALTHCHECK`只可以出现一次，如果写了多个，只有最后一个生效。

在`HEALTHCHECK [选项] CMD`后面的命令，格式和`ENTRYPOINT`一样，分为 shell 格式，和 exec 格式。命令的返回值作为容器健康状态的判断依据：0 成功；1 失败；2 保留，不要使用这个值。

当指定了`HEALTHCHECK`指令后，容器启动时，其初始状态为`starting`，在`HEALTHCHECK`检查成功后变成`healthy`，连续失败一定的次数时变成`unhealthy`。

> 容器的状态可以通过`docker container ls`命令看到。

示例如下：

```yaml
FROM nginx
RUN apt-get update && apt-get install -y curl && rm -rf /var/lib/apt/lists/*
HEALTHCHECK --interval=5s --timeout=3s \
CMD curl -fs http://localhost/ || exit 1
```

这里为一个 Nginx Web 服务器容器添加了健康检查：5s 检查一次，如果检查命令执行超过 3s 则表示检查失败。

为了帮助排查，健康检查命令的输出（包括 stdout 和 stderr）都会被存储于健康状态里，可以使用`dcoker inspect`来查看：

```shell
$ docker inspect --format '{{json .State.Health}}' web | python -m json.tool
{
    "FailingStreak": 0,
    "Log": [
        {
        "End": "2016-11-25T14:35:37.940957051Z",
        "ExitCode": 0,
        "Output": "<!DOCTYPE html>\n<html>\n<head>\n<title>Welcome to nginx!</title>\n<style>\n body {\n width: 35em;\n margin: 0 auto;\n font-family: Tahoma, Verdana, Arial, sans-serif;\n }\n</style>\n</head>\n<body>\n<h1>Welcome to nginx!</h1>\n<p>If you see this page, the nginx web server is successfully installed and\nworking. Further configuration is required.</p>\n\n<p>For online documentation and support please refer to\n<a href=\"http://nginx.org/\">nginx.org</a>.<br/>\nCommercial support is available at\n<a href=\"http://nginx.com/\">nginx.com</a>.</p>\n\n<p><em>Thank you for using nginx.</em></p>\n</body>\n</html>\n",
        "Start": "2016-11-25T14:35:37.780192565Z"
        }
    ],
    "Status": "healthy"
}
```

### 15. ONBUILD 下一级构建指令

`ONBUILD`指令比较特殊：该指令后面跟随的指令（如`RUN`、`COPY`）在当前镜像构建时并不会被执行，只有当以当前镜像为基础镜像，去构建下一级镜像的时候才会被执行。

指令格式：`ONBUILD <指令>`。

合理使用该指令，可以使得其他以当前镜像为基础镜像的构建更加简单。

比如，有如下一个镜像：

```yaml
FROM node:slim
RUN mkdir /app
WORKDIR /app
COPY ./package.json /app
RUN [ "npm", "install" ]
COPY . /app/
CMD [ "npm", "start" ]
```

为了复用这个镜像，里面的`COPY`、`RUN`指令就不能写了，而是要移动到以这个镜像为基础的构建 Dockerfile 中。但是使用`ONBUILD`指令就可以将这几个指令继续保留在当前的构建文件，而让后续的构建更加的简洁：

```yaml
FROM node:slim
RUN mkdir /app
WORKDIR /app
ONBUILD COPY ./package.json /app
ONBUILD RUN [ "npm", "install" ]
ONBUILD COPY . /app/
CMD [ "npm", "start" ]
```

当需要以该镜像为基础进行构建时，只需要在对应的 Dockerfile 中写入如下的指令即可：

```yaml
FROM my-node
```

### 16. SHELL 指定命令行

`SHELL`指令可以指定`RUN`、`ENTRYPOINT`、`CMD`指令的 shell。

指令格式为：`SHELL ["executable", "parameters]`。

Linux 中默认的 shell 为`["/bin/sh", "-c"]`，可以通过`SHELL`指令来进行更改：

```yaml
SHELL ["/bin/sh", "-c"]

# /bin/sh -cex "nginx"
ENTRYPOINT nginx

SHELL ["/bin/sh", "-cex"]

# /bin/sh -cex "nginx"
CMD nginx
```


