## usermod
修改用户信息

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

