Docker Compose 的命令格式如下所示：

```shell
docker-compose [-f=<arg>...] [options] [COMMAND] [ARGS...]
```

对于 Compose 来说，大部分命令的对象既可以是项目本身，也可以指定为项目中的服务或容器。如果没有特别的指定，那么 Compose 命令的对象将是项目，这意味着项目中所有的服务都会受到命令影响。

执行`docker-compose [COMMAND] --help`或者`docker-compose help [COMMAND]`可以查看具体某个命令的使用格式。

### 1. 命令选项

`docker-compose`命令具有如下一些选项：

* `-f, --file FILE` 指定使用的 Compose 模板的文件，默认为`docker-compose.yml`，而且可以多次指定。
* `-p, --project-name NAME` 指定项目的名称，默认将使用所在目录的名称作为项目名。
* `--verbose` 输出更多的调试信息。
* `-v, --version` 打印版本并退出。

### 2. 命令列表

* `build` 构建（重新构建）项目中的服务容器。可以随时在项目目录下运行`docker-compose build`来重新构建服务。服务容器一旦构建后，将会带上一个标记名。例如，对于 Web 项目中的一个 DB 容器，可能是 web_db。该命令的选项包括：
    - `--force-rm` 删除构建过程中的临时容器。
    - `--no-cache` 构建镜像过程中不使用 cache（这将加长构建过程）。
    - `--pull` 始终尝试通过 pull 来获取更新版本的镜像。
* `config` 验证 Compose 文件格式是否正确。若正确则显示配置，若格式错误显示错误原因。
* `down` 此命令将会停止`up`命令所启动的容器，并移除网络。
* `exec` 进入指定的容器。
* `help` 获得一个命令的帮助信息。
* `images` 列出 Compose 文件中包含的镜像。
* `kill` 通过发送`SIGKILL`信号来强制停止服务容器。支持通过`-s`参数来指定发送的信号。如，发送`SIGINT`信号：`docker-compose kill -s SIGINT`。
* `logs` 查看服务容器的输出，默认情况下，Docker Compose 将对不同的服务输出使用不同的颜色来区分。可以通过`--no-color`来关闭颜色。
* `pause` 暂停一个服务容器。
* `port` 打印某个容器端口所映射的公共端口。选项如下：
    - `--protocol=proto` 指定端口协议，默认为 tcp，也可以指定为 udp。
    - `--index=index` 如果统一服务存在多个容器，指定命令对象容器的序号（默认为 1）。
* `ps` 列出项目中目前的所有容器，可以使用`-q`选项只打印容器的 ID 信息。
* `pull` 拉取服务依赖的镜像，可以使用`--ignore-pull-failure`选项忽略拉取镜像过程中的错误。
* `push` 推送服务依赖的镜像到 Docker 镜像仓库。
* `restart` 重启项目中的服务，可以使用`-t, timeout TIMEOUT`选项指定重启前停止容器的超时（默认为 10 秒）。
* `rm` 删除所有（停止状态的）服务容器。推荐先执行`docker-compose stop`命令来停止容器。选项如下：
    - `-f, --force` 强制删除容器，包括非停止状态的容器。一般尽量不要使用。
    - `-v` 删除容器所挂载的数据卷。
* `run` 在指定的服务商指定一个命令。
* `scale` 设置指定服务运行的容器格式。
* `start` 启动已经存在的服务容器。
* `stop` 停止已经处于运行状态的容器，但不删除它。通过`docker-compose start`可以在此启动这些容器。可以使用`-t, --timeout TIMNEOUT`选项设置停止容器时候的超时（默认为 10 秒）。
* `top` 查看各个服务容器内运行的进程。
* `unpause` 恢复处于暂停状态中的服务。
* `up` 该命令将尝试自动完成包括构建镜像、（重新）创建服务、启动服务、关联服务相关容器的一系列操作。
* `version` 打印 Docker Compose 的版本信息。

### 3. run

命令格式为：`docker-compose run [options] [-p PORT...] [-e KEY=VAL...] SERVICE [COMMAND] [ARGS...]`。

命令选项如下：

* `-d` 后台运行容器。
* `--name NAME` 为容器指定一个名字。
* `--entrypoint CMD` 覆盖默认的容器启动指令。
* `-e KEY=VAL` 设置环境变量值，可多次使用选项来设置多个环境变量。
* `-u, --user=""` 指定运行容器的用户名或者 uid。
* `--no-deps` 不自动启动关联的服务容器。
* `--rm` 运行命令后自动删除容器，d 模式下将忽略。
* `-p, --publish=[]` 映射容器端口到本地主机。
* `--service-ports` 配置服务端口并映射到本地主机。
* `-T 不分配伪 tty`，意味着依赖 tty 的指令将无法运行。

该命令会启动指定的服务容器，然后执行指定的命令。默认情况下，如果存在关联，则所有关联的服务将会被自动启动，除非这些服务已经在运行中。

比如，下面的命令将启动一个 ubuntu 服务容器，并执行`ping docker.com`命令：

```shell
docker-compose run ubuntu ping docker.com
```

该命令类似启动容器后运行指定的命令，相关卷、链接等都会按照配置自动的创建。但是有两点不同：

* 给定命令将会覆盖原有的自动运行命令；
* 不会自动创建端口，以避免冲突。

如果不希望自动启动关联的容器，可以使用`--no-deps`选项，例如：

```shell
docker-compose run --no-deps web python manage.py shell
```

这将不会启动 web 容器所关联的其它容器。

### 4. scale

命令格式为：`docker-compose scale [options] [SERVICE=NUM...]`。

该命令可以用过`SERVICE=NUM`格式来指定服务容器的个数。例如，下面的命令将会启动 3 个容器运行 web 服务，启动 2 个容器运行 db 服务：

```shell
docker-compose scale web=3 db=2
```

一般的：当指定数目多于该服务当前实际运行的容器数量，将新创建并启动容器；反之，将会停止容器。

还可以使用`-t, --timeout TIMNEOUT`设置停止容器时候的超时（默认为 10 秒）。

### 5. up

命令格式为：`docker-compose up [options] [SERVICE...]`。

该命令十分强大，它将尝试自动完成包括构建镜像，（重新）创建服务，启动服务，并关联服务相关容器的一系列操作。链接的服务都将会被自动启动，除非已经处于运行状态。

可以说，大部分时候都可以直接通过该命令来启动一个项目。

该命令的选项如下：

* `-d` 在后台运行服务容器。
* `--no-color` 不使用颜色来区分不同的服务的控制台输出。
* `--no-deps` 不启动服务所链接的容器。
* `--force-recreate` 强制重新创建容器，不能与`--no-recreate`同时使用。
* `--no-recreate` 如果容器已经存证了，则不重新创建，不能与`--force-recreate`同时使用。
* `--no-build` 不主动构建缺失的服务镜像。
* `-t, --timeout TIMEOUT` 停止容器的超时时间（默认为 10 秒）。

默认情况下，`docker-compose up`启动的容器都在前台运行，控制台将会同时打印所有容器的输出信息，可以方便进行调试。当通过`Ctrl+C`停止时，所有的容器都将会停止。

如果使用`docker-compose up -d`命令启动项目，所有的服务容器都将在后台启动并运行。一般推荐生产环境下使用该选项。

默认情况下。如果服务容器已经存在，`docker-compose up`将会尝试停止容器，然后重新创建（保持使用`volumes=from`挂载的卷），以保证新启动的服务匹配`docker-compose.yml`文件的最新内容。如果不希望容器被停止并被重新创建，可以使用`docker-compose up --no-recreate`命令来启动，这样将只启动处于停止状态的容器，而忽略已经运行的服务。

如果只想重新部署某个服务，可以使用`docker-compsoe up --no-deps -d <SERVICE_NAME>`来重新创建服务并后台停止就服务、启动新服务。这样并不会影响到其所依赖的服务。

