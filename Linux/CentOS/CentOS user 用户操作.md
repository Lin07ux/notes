## useradd 添加用户

添加用户使用`useradd <username>`。

## usermod 修改用户信息


### 问题
在修改用户的信息的时候(如主目录)的时候，可能会出现*当前用户正在使用中*错误：

```shell
$ usermod -d /home/user502home user502
> usermod: user502 is currently used by process 4220
```

这是由于有其他应用是以该用户的身份和权限在运行的。可以通过`ps -fp <pid>`来查看是什么程序，然后关闭程序或者直接`kill <pid>`结束进程：

```shell
$ ps -fp 4220
> UID        PID  PPID  C STIME TTY          TIME CMD
> www        742   800  0 5月24 ?       00:00:03 php-fpm: pool www
```

这里显示是被 php-fpm 程序使用的，可以停止这个程序，然后重新修改用户的主目录即可。

## userdel 删除用户

删除用户使用如下的命令：

```shell
userdel <username>
userdel -f tmp_name  # 连同用户目录一并删除
```

> 注意：如果用户还在登陆的话，删除时会提示，用户正在登陆无法删除。此时可能需要先强制用户退出。

## passwd 修改密码

修改用户的登录密码使用`passwd <username>`命令，然后就会提示你输入用户的新密码并需要确认重输一遍。

## 强制退出登录

* 首先查看当前已经登录的用户：`w`。
* 然后退出指定用户：`pkill -kill -t [TTY]`。

> TTY 根据`w`的查看结果中得到。

