模板文件是使用 Compose 的核心，涉及到的指令关键字也比较多。这里面大部分指令跟`docker run`相关参数的含义都是类似的。

默认的模板文件名为`docker-compose.yml`，内容为 YAML 格式。如：

```yaml
version: "3"

services:
  webapp:
    image: examples/web
    ports:
      - "80:80"
    volumes:
      - "/data"
```

项目的每一个服务都对应着`services`下的一个配置块，而且必须通过`image`指令指定镜像或`build`指令（需要 Dockerfile）来自动构建生成镜像。如果使用`build`指令，在对应的`Dockerfile`中设置的选项（例如：`CMD`、`EXPOSE`、`VOLUME`、`ENV`等）将会自动被获取，无需在`docker-compose.yml`中重复设置。

### 1. 指令列表

* `build` 指定服务容器的 Dockerfile。
* `cap_add` 添加容器的内核能力(capacity)，如添加全部的能力：

    ```yaml
    cap_add:
      - ALL
    ```
* `cap_drop` 删除容器的内核能力(capacity)，如去掉 NET_ADMIN 能力：

    ```yaml
    cap_drop:
      - NET_ADMIN
    ```
* `command` 覆盖容器启动后默认执行的命令，如：`command: echo "Hello World"`。
* `configs` 仅用于`Swarm mode`。
* `cgroup_parent` 指定父`cgroup`组，意味着将继承该组的资源限制。如：`cgroup_parent: cgroups_1`。
* `container_name` 指定容器名称，默认会使用`项目名称_服务名称_序号`这样的格式。如：`container_name: docker-web-container`。

    > 注意：指定容器名称之后，该服务将无法进行扩展(Scale)，因为 Docker 不允许多个容器具有相同的名称。
* `deploy` 仅用于`Swarm mode`。
* `devices` 指定设备映射关系。如：
    
    ```yaml
    devices:
      - "/dev/ttyUSB1:/dev/ttyUSB0"
    ```
* `depends_on` 解决容器的依赖、启动顺序的问题。
* `dns` 自定义 DNS 服务器，可以是一个值也可以是一个列表。比如：
    
    ```yaml
    dns: 8.8.8.8
    
    dns:
      - 8.8.8.8
      - 114.114.114.114
    ```
* `dns_search` 配置 DNS 搜索域，可以是一个值，也可以是一个列表。比如：
    
    ```yaml
    dns_search: example.com
    
    dns_search:
      - domain1.example.com
      - domain2.example.com
    ```
* `tmpfs` 挂载一个 tmpfs 文件系统到容器，可以是一个值，也可以是一个列表。比如：
    
    ```yaml
    tmpfs: /run
    tmpfs:
      - /run
      - /tmp
    ```
* `env_file` 定义环境变量的文件，可以为单独的文件路径或列表。
* `environment` 设置环境变量。可以使用数组或字典两种格式。
* `expose` 暴露端口，但不映射到宿主机，只被连接到服务访问。仅可以指定内部端口为参数：

    ```yaml
    expose:
     - "3000"
     - "8000"
    ```
* `external_links` **不建议使用该指令**。链接到`docker-compose.yml`外部的容器，甚至并非 Compose 管理的外部容器。
    
    ```yaml
    external_links:
     - redis_1
     - project_db_1:mysql
     - project_db_1:postgresql
    ```
* `extra_hosts` 指定额外的 host 名称映射信息。
* `healthcheck` 通过命令检查容器是否健康运行：
    
    ```yaml
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost"]
      interval: 1m30s
      timeout: 10s
      retries: 3
    ```
* `image` 指定为镜像名称或镜像 ID。如果镜像在本地不存在，Compose 将会尝试拉取这个镜像。如：`image: orchardup/postgresql`。
    * `labels` 为容器添加 Docker 元数据(Metadata)信息。例如，可以为容器添加辅助说明信息：
    
    ```yaml
    labels:
      com.startupteam.description: "webapp for a startup team"
      com.startupteam.department: "devops department"
      com.startupteam.release: "rc3 for v1.0"
    ```
* `links` **不推荐使用该指令**。
* `logging` 配置日志选项。
* `network_mode` 设置网络模式，值和`docker run`命令中的`--network`参数一样：

    ```yaml
    network_mode: "bridge"
    network_mode: "host"
    network_mode: "none"
    network_mode: "service:[service name]"
    network_mode: "container:[container name/id]"
    ```
* `networks` 配置容器链接的网络：
    
    ```yaml
    version: "3"
    services:
    
      some-service:
        networks:
         - some-network
         - other-network
    
    networks:
      some-network:
      other-network:
    ```
* `pid` 跟主机系统共享进程命名空间。打开该选项的容器之间，以及容器和宿主机系统之间可以通过进程 ID 来相互访问和操作。如：`pid: "host"`。
* `ports` 暴露端口信息。
* `secrets` 存储敏感数据。
* `security_opt` 指定容器模板标签(label)机制的默认属性（用户、角色、类型、级别等）。例如，配置标签的用户名和角色名：
    
    ```yaml
    security_opt:
        - label:user:USER
        - label:role:ROLE
    ```
* `stop_signal` 设置另一个信号来停止容器，在默认情况下使用的是 SIGTERM 信号来停止容器。比如：`stop_signal: SIGUSR1`。
* `sysctls` 配置容器内核参数，比如：
    
    ```yaml
    sysctls:
      net.core.somaxconn: 1024
      net.ipv4.tcp_syncookies: 0
    
    sysctls:
      - net.core.somaxconn=1024
      - net.ipv4.tcp_syncookies=0
    ```
* `ulimits` 指定容器的 ulimits 限制值。例如，指定最大进程数为 65535，指定文件句柄数为 20000（软限制，应用可以随时修改，不能超过硬限制） 和 40000（系统硬限制，只能 root 用户提高）。
    
    ```yaml
      ulimits:
        nproc: 65535
        nofile:
          soft: 20000
          hard: 40000
    ```
* `volumes` 设置数据卷的挂载。

此外，还有包括`domainname, entrypoint, hostname, ipc, mac_address, privileged, read_only, shm_size, restart, stdin_open, tty, user, working_dir`等指令，基本与`docker run`中对应的参数的功能一致。

### 2. 读取变量

Compose 模板文件支持动态读取主机的系统环境变量和当前目录下的`.env`文件中的变量。

例如，下面的 Compose 文件将从运行它的环境中读取变量`${MONGO_VERSION}`的值，并写入执行的指令中。

```yaml
version: "3"

services:

  db:
    image: "mongo:${MONGO_VERSION}"
```

对此模板文件：

* 如果执行`MONGO_VERSION=3.2 docker-compose up`命令，则会启动一个`mongo:3.2`镜像的容器；
* 如果执行`MONGO_VERSION=2.8 docker-compose up`命令，则会启动一个`mongo:2.8`镜像的容器。

如果在当前目录存在`.env`文件，执行`docker-compose`命令时将从该文件中读取变量。比如，在当前目录新建`.env`文件，并写入如下内容：

```yaml
# 支持 # 号注释
MONGO_VERSION=3.6
```

执行`docker-compose up`则会启动一个`mongo:3.6`镜像的容器。

### 3. build

该指令用于指定当前服务对应容器的`Dockerfile`文件所在文件夹的路径，可以是绝对路径，也可以是相对`docker-compose.yml`文件的路径。

Compose 会使用指定的`Dockerfile`自动构建这个镜像，然后使用这个镜像生成容器。

```yaml
version: "3"
services:

  webapp:
    build: ./dir
```

也可以不为该指令直接指定路径，而是在其下级使用更多命令来定义`Dockerfile`：

* `context` 指定`Dockerfile`所在文件的路径；
* `dockerfile` 指定`Dockerfile`文件名称；
* `args` 指定构建镜像时的变量。

```yaml
version: "3"
services:

  webapp:
    build:
      context: ./dir
      dockerfile: Dockerfile-alternate
      args:
        buildno: 1
```

还可以使用`cache_from`指定构建镜像的缓存：

```yaml
version: "3"
services:

  webapp:
    build:
      context: .
      cache_from:
        - alpine:latest
        - corp/web_app:3.14
```

### 4. depends_on

项目中服务可能会依赖其他的服务，这就涉及到服务的启动顺序问题了。以下例子中会先启动 redis 和 db 服务容器，然后在启动 web 服务容器：

```yaml
version: '3'

services:
  web:
    build: .
    depends_on:
      - db
      - redis

  redis:
    image: redis

  db:
    image: postgres
```

> 注意：web 服务不会等待 redis 和 db 服务**完全启动**之后才启动。

### 5. env_file

设置获取环境变量的文件。

如果通过`docker-compose -f FILE`的方式指定 Compose 模板文件，则`env_file`中变量的路径会基于模板文件的路径。

如果有变量名称与`environment`指令冲突，则按照惯例，以位置靠后者为准。

```yaml
env_file: .env

env_file:
  - ./common.env
  - ./apps/web.env
  - /opt/secrets.env
```

环境变量文件中每一行必须符合格式，支持`#`开头的注释行：

```ini
# common.env: Set development environment
PROG_ENV=development
```

### 6. environment

这个和在`env_file`中设置环境变量类似，但是是使用 YAML 语法。

如果之给定环境变量名称，而未指定具体的值，则会自动获取运行 Compose 主机上的对应变量的值，可以用来防止泄露不必要的数据。

```yaml
environment:
  RACK_ENV: development
  SESSION_SECRET:

environment:
  - RACK_ENV=development
  - SESSION_SECRET
```

如果变量名称或者值中用到`true|false`、`yes|no`等表达布尔含义的词汇，最好放到引号中，避免 YAML 自动解析某些内容为对应的布尔语义。这些特定词汇包括如下：

```yaml
y|Y|yes|Yes|YES|n|N|no|No|NO|true|True|TRUE|false|False|FALSE|on|On|ON|off|Off|OFF
```

### 7. extra_hosts

类似 Docker 中的`--add-host`参数，可以添加额外的 DNS 映射：

```yaml
extra_hosts:
 - "googledns:8.8.8.8"
 - "dockerhub:52.1.157.61"
```

这样会在启动后的服务容器中的`/etc/hosts`文件中添加如下两条条目：

```
8.8.8.8 googledns
52.1.157.61 dockerhub
```

### 8. logging

日志选项支持`driver`和`options`：

```yaml
logging:
  driver: syslog
  options:
    syslog-address: "tcp://192.168.0.42:123"
```

目前支持三种日志驱动类型：

```yaml
driver: "json-file"
driver: "syslog"
driver: "none"
```

`options`配置日志驱动的相关参数：

```yaml
options:
  max-size: "200k"
  max-file: "10"
```

### 9. ports

保留端口信息，使用`HOST:CONTAINER`(宿主机:容器端口)格式，或者仅仅指定容器的端口（宿主机将会随机选择端口）都可以：

```yaml
ports:
 - "3000"
 - "8000:8000"
 - "49100:22"
 - "127.0.0.1:8001:8001"
```

> 注意：当使用`HOST:CONTAINER`格式来映射端口时，如果你使用的容器端口小于 60 并且没有放到引号中，可能会得到错误结果。因为 YAML 会自动解析`xx:yy`这种数字格式为 60 进制。为避免出现这种问题，建议数字串都采用引号包括起来的字符串格式。

### 10. secrets

存储敏感数据，例如 MySQL 服务密码：

```yaml
version: "3.1"
services:

mysql:
  image: mysql
  environment:
    MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
  secrets:
    - db_root_password
    - my_other_secret

secrets:
  my_secret:
    file: ./my_secret.txt
  my_other_secret:
    external: true
```

### 11. volumes

设置容器中数据卷的挂载。可以设置为`HOST:CONTAINER`(宿主机:容器)的路径方式，或者数据卷名称`VOLUME:CONTAINER`，还可以设置访问模式`HOST:CONTAINER:ro`.

该指令中的路径支持相对路径：

```yaml
volumes:
 - /var/lib/mysql
 - cache/:/tmp/cache
 - ~/configs:/etc/configs/:ro
```

如果路径为数据卷名称，必须在文件中配置数据卷。

```yaml
version: "3"

services:
  my_src:
    image: mysql:8.0
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

