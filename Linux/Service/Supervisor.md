## 一、常用命令

* 启动守护进程：`supervisord -c /etc/supervisor/supervisord.conf`
* 重载配置：`supervisorctl reload`
* 关闭：`supervisorctl shutdown`
* 重新读入配置：`supervisorctl reread`
* 重新载入配置：`supervisorctl update`(更新子进程组的配置)
* 查看状态：`supervisorctl status`
* 启动程序：`supervisorctl start <program>`
* 关闭程序：`supervisorctl stop <program>`
* 重启程序：`supervisorctl restart <program>`
* 清空日志：`supervisorctl clear <program>`

> `start`、`stop`、`restart`后跟随`all`表示表示启动、关闭、重启所有程序。

## 二、常用配置

### 1. 使 Supervisor 的日志定位到标准输出中而不是文件中

```ini
[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0
```

`nodaemon=true`配置能够使 Supervisor 的日志输出到标准输出中，`logfile_maxbytes=0`则可以避免日志超限后不再记录。

因为 Supervisor 不会区别特殊文件和普通文件，所以这个日志大小限制是必填的，否则会按照默认的 50MB 的限制记录。

> 转摘：[supervisord disable log files or use logfile=/dev/stdout](https://stackoverflow.com/questions/45645758/supervisord-disable-log-files-or-use-logfile-dev-stdout)

## 三、常见报错

使用 Supervisor 遇到错误时，基本思路如下：

1. 首先要明确问题出在 Supervisor 上还是启动的程序上，可以用`ps -ef | grep supervisord`查看是否启动，在用`ps`查看设定的命令的进程有没有启动；

2. 确认下启动的 Supervisor 配置文件是哪个，有的是`/etc/supervisor/supervisord.conf`，有的是`/etc/supervisord.conf`，以实际情况为准，不要弄混；

3. 推荐使用 apt-get 安装，其次是 pip，最后才是 yum。另外，尽量用 Supervisord 3.x 以上的版本，2.x 版本出问题概率大；

4. Supervisord 的日志在`/var/log/supervisor/supervisord.log`，启动失败一般能再这里找到有用的信息。


### 1. No such file or directory: file

启动或执行其他 Supervisor 命令时，提示如下错误信息：

```
error: <class 'socket.error'>, [Errno 2] No such file or directory: file: <string> line: 1
```

一般可能是因为 Supervisor 配置的 sock 文件不存在或权限不足导致的。执行`supervisorctl`命令一般可以看到具体的信息：

```shell
> supervisorctl
unix:///var/run/supervisor/supervisor.sock no such file
```

这就需要创建该 sock 文件并进行设置相应权限，再重启就正常了：

```shell
sudo touch /var/run/supervisor/supervisor.sock
sudo chmod 777 /var/run/supervisor/supervisor.sock

# 重启服务
supervisord -c /etc/supervisord.conf
```

### 2. ini file does not include supervisorctl section

报错信息如下：

```
Error: .ini file does not include supervisorctl section
```

这是由于配置文件中的问题，缺少指定的配置段，补上即可。

### 3. Exited too quickly

报错信息如下：

```shell
Exited too quickly (process log may have details)
```

这是配置的要执行的命令的问题，而不是 Supervisor 的问题了。

解决办法： 

1. 先确认`[program:XXX]`中自己的程序的`command=<启动命令>`和`directory=<运行命令的路径>`没有问题，Python 不是用的自己要的环境的 Python(比如虚拟环境的)，log 文件的文件夹是不是已经创建（没创建的话 Supervisor 没权限生成 log 文件），以及该 log 文件是不是授权给所有用户了。
2. 确保用上面的配置中的`command`在指定路径可以直接运行不会报错，这时候一般就不会有什么问题了。这时候查看 log 文件一般就能看到报错的信息，照着解决后重启 Supervisor 就好了。 
3. 如果上面的命令确保可以跑，但还是没法正常运行，也看不到自己程序的报错，尝试把`[program:XXX]`中的名字换成了一个跟启动命令不一样的另一个名字（不要太短），重启 Supervisor 之后也许就可以了。

## 转摘

1. [Supervisor 常见报错](https://blog.csdn.net/kkevinyang/article/details/80539940)

