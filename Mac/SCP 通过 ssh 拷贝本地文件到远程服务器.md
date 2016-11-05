在命令行中，可以通过 scp 命令将本地的文件或目录拷贝到远程服务器上，也可以将远程服务器上的文件或目录拷贝下载到本地。

### 命令格式
命令格式如下：

```shell
# 拷贝本地文件到远程
scp <local/file> <user>@<host-or-ip>:<remote/path>

# 拷贝远程文件到本地
scp <user>@<host-or-ip>:<remote/file> <local/path>

# 将本地目录拷贝到远程
scp -r <local/path> <user>@<host-or-ip>:<remote/path>

# 从远程将目录拷回本地
scp -r <user>@<host-or-ip>:<remote/file> <local/path>
```

在执行拷贝的时候，会提示输入登录远程服务器的用户的密码。

### 示例
比如，下面的命令将本地的`root`目录下的所有以`install.`开头的文件都拷贝到远程的`/usr/local/src`目录中：

```shell
scp /root/install.* root@192.168.1.12:/usr/local/src
```

下面的命令将远程`/usr/local/src`目录中的文件都下载到本地的`root`目录中。

```shell
scp root@192.168.1.12:/usr/local/src/*.log /root/
```


