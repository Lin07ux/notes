> 转摘：[Linux: PHP-FPM, Docker, STDOUT and STDERR – no an application’s error logs](https://devpress.csdn.net/linux/62eba63f648466712833a89c.html)

## 一、前置说明

### 1.1 Supervisor

在一个容器镜像中安装了 PHP Swoole 应用(基于 ThinkPHP)，并用 Supervisor 来保持 Swoole 服务的一直运行，相关的 Supervisor 配置如下：

```ini
[supervisord]
logfile=/var/log/supervisord.log ; (main log file;default $CWD/supervisord.log)
pidfile=/var/run/supervisord.pid ; (supervisord pidfile;default supervisord.pid)
childlogdir=/var/log/            ; ('AUTO' child log dir, default $TEMP)

[program:swoole]
command = php think swoole restart
stdout_logfile=/proc/self/fd/1
stdout_logfile_maxbytes=0
stderr_logfile=/proc/self/fd/1
stderr_logfile_maxbytes=0
```

在使用中能够保持 Swoole 的正常执行，但是 Swoole 启动过程中的相关信息输出却没有记录下来：未出现在屏幕中（Docker Logs），也未出现在 Supervisor 的日志文件中。

### 1.2 stdin/stdout/stderr

在 Linux 系统中，默认为每个进程提供了三个特殊的文件：标准输入(stdin)、标准输出(stdout)和标准错误(stderr)。这三个命令分别对应着`/dev/stdin`、`/dev/stdout`和`/dev/stderr`。

```shell
$ ls -l /dev/stdin
lrwxrwxrwx    1 root     root            15 Mar 10 18:23 /dev/stdin -> /proc/self/fd/0

$ ls -l /dev/stdout
lrwxrwxrwx    1 root     root            15 Mar 10 18:23 /dev/stdout -> /proc/self/fd/1

$ ls -l /dev/stderr
lrwxrwxrwx    1 root     root            15 Mar 10 18:23 /dev/stderr -> /proc/self/fd/2
```

可以看到，这三个文件对应的是`/proc/self/fd`路径下的文件 0/1/2 三个文件。其实这个路径是别名，在处理的时候会自动将`self`替换为当前进程的 ID，也就是`/proc/self/fd/1`等同于`/proc/{pid}/fd/1`。而最终，这三个文件其实都指向了同一处：

```shell
$ ls -l /proc/self/fd/0
lrwx------    1 root     root            64 Mar 10 19:43 /proc/self/fd/0 -> /dev/pts/2

$ ls -l /proc/self/fd/1
lrwx------    1 root     root            64 Mar 10 19:43 /proc/self/fd/1 -> /dev/pts/2

$ ls -l /proc/self/fd/2
lrwx------    1 root     root            64 Mar 10 19:43 /proc/self/fd/2 -> /dev/pts/2

$ tty
/dev/pts/2
```

这里的`/dev/pts/2`也是一种特殊文件，表示当前使用的命令操作终端，也就是表示当前的屏幕。

### 1.3 Docker Logs

在 Docker 的文档中，有如下的描述：

> By default, `docker logs` or `docker service logs` show the command's output just as it would appear if you ran the command interactively in a terminal.
> 
> ...
> 
> The Offical `nginx` image creates a symbolic link from `/var/log/nginx/access.log` to `/dev/stdout`, and creates another symbolic link from `/var/log/nginx/error.log` to `/dev/stderr`, overwritting the log files and causing logs to be sent to the relevant special device instead.

就是说，Docker Logs 默认情况下就是容器主进程（1 号进程）的输出，和在终端中直接执行这个命令看到的结果是一样的。

查看下 Docker 容器主进程（1 号进程）的 stdin/stdout/stderr，可以看到它们其实都指向了`/dev/pts/0`：

```shell
$ ls -l /proc/1/fd/0
lrwx------    1 root     root            64 Mar 10 19:43 /proc/self/fd/0 -> /dev/pts/0

$ ls -l /proc/1/fd/1
lrwx------    1 root     root            64 Mar 10 19:43 /proc/self/fd/1 -> /dev/pts/0

$ ls -l /proc/1/fd/2
lrwx------    1 root     root            64 Mar 10 19:43 /proc/self/fd/2 -> /dev/pts/0
```

## 二、探究问题

### 2.1 Swoole 进程的标准输出

在命令行中直接启动 Swoole 的过程中，标准输出中会显示 Swoole 的一些相关信息，但是在 Docker 中由 Supervisor 维护的 Swoole 却没有在标准输出中展示任何信息。

先检查一下 Swoole 的标准输出指向哪里了：

```shell
$ ls -l /proc/20/fd/1
l-wx------    1 root     root            64 Mar 13 12:45 /proc/20/fd/1 -> pipe:[140511]
```

其中`20`是 Swoole 启动后众多进程中的一个，而且这些 Swoole 进程的标准输出都是指向同一个目标的：`pipe:[140511]`。

### 2.2 Supervisor 的标准输出

那么`pipe:[140511]`是什么呢？通过下面的命令可以找到和`pipe:[140511]`相关的进程：

```shell
$ lsof | grep 140511
9      /usr/bin/python3.8      pipe:[140511]
20     /usr/local/bin/php      pipe:[140511]
20     /usr/local/bin/php      pipe:[140511]
...
```

可以看到，由很多进程都指向这个地方，而且其中有一个 Python3.8 的进程。查看该进程：

```shell
$ ps aux | grep 9
    9 root      0:11 {supervisord} /usr/bin/python3 /usr/bin/supervisord -c /etc/supervisord.conf
   29 root      0:00 /bin/sh
  668 root      0:00 grep 9
```

可以看到，9 号 Python3.8 的进程其实就是 Supervisor 的进程。

这也就说明，采用前面的那个配置时，通过 Supervisor 启动的 Swoole 进程会自动和 Supervisor 使用相同的标准输出（标准输入和标准输出也是相同的）。

那么 Supervisor 的标准输出指向哪里呢？

```shell
$ ls -l /proc/9/fd/0
lr-x------    1 root     root            64 Mar 13 13:16 /proc/9/fd/0 -> /dev/null

$ ls -l /proc/9/fd/1
l-wx------    1 root     root            64 Mar 10 18:23 /proc/9/fd/1 -> /dev/null

$ ls -l /proc/9/fd/2
l-wx------    1 root     root            64 Mar 13 13:16 /proc/9/fd/2 -> /dev/null
```

这是为什么呢？

### 2.3 Supervisor 配置

查看 Supervisor 的配置，可以看到其日志文件配置了一个路径文件：

```ini
[supervisord]
logfile=/var/log/supervisord.log
```

也就是说，Supervisor 会将其日志都记录到文件中，此时就会默认忽略其标准输出和标准错误的内容。

如果将 Supervisor 的日志配置改为如下方式，就能将 Supervisor 的标准输出定位到 Docker Logs 中：

```ini
[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0
```

重启服务之后，就可以看到 Supervisor 的标准输出也指向了`/dev/pts/0`了：

```shell
$ ls -l /proc/9/fd/1
lrwx------    1 root     root            64 Mar 10 17:09 /proc/9/fd/1 -> /dev/pts/0
```

此时就能将 Supervisor 和 Swoole 的标准输出和标准错误都展示在 Docker Logs 中了：

```shell
# Supervisor
$ echo eeee > /proc/9/fd/1

# Swoole
$ echo ssss > /proc/20/fd/1
```

## 三、其他

### 3.1 kubectl logs

在 k8s 编排中，容器的日志也是能被捕获到的，并且可以通过`kubectl logs`进行查看。同样，也能基于 Docker Logs 日志做容器监控。

所以，将一些基础的信息输出到 Docker Logs 也是一种有效的监控方式。

### 3.2 PHP-FPM

在 Docker 中启动 PHP-FPM 的时候，即便其他的配置都正常，也有可能会出现无法再 Docker Logs 中查看到 PHP-FPM 的错误信息。而且查看 PHP-FPM 的标准错误的时候，可以看到其指向了`/dev/null`设备：

```shell
$ ls -l /proc/101/fd/1
lrwx------    1 nobody   nobody          64 Feb 17 13:55 /proc/101/fd/1 -> /dev/null

$ ls -l /proc/101/fd/2
lrwx------    1 nobody   nobody          64 Feb 17 13:55 /proc/101/fd/2 -> /dev/null
```

这一般是由于 PHP-FPM 的配置中缺少`catch_workers_output`的配置，加上之后即可：

```ini
[www]
listen = 9000
...
catch_workers_output = yes
```