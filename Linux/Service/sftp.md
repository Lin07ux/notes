SFTP(Secure File Transfer Protocol，安全文件传输协议)是一种基于可靠数据流(data stream)，提供文件存取和管理的网络传输协议，它在网络协议层的结构如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1558015612352.png" />

与 FTP 协议相比，SFTP 在客户端与服务器间提供了一种更为安全的文件传输方式。目前已经有很多 GUI 客户端支持 SFTP 协议，而他们都是基于命令行实现的。

### 连接 sftp

因为 SFTP 是基于 SSH 协议的，所以默认的身份认证方法与 SSH 协议保持一致。通常我们使用 SSH Key 来进行连接，如果你已经可以使用 SSH 连接到远程服务器上，那么可以使用以下命令来连接 SFTP：

```ssh
sftp user_name@remote_server_address[:path]
```

如果远程服务器自定义了连接的端口，可以使用 -P 参数：

```ssh
sftp -P remote_port user_name@remote_server_address[:path]
```

如果连接地址存在 path 并且 path 不是一个目录，那么 SFTP 会直接从服务器端取回这个文件。

连接成功后将进入一个 SFTP 的解释器，可以发现命令行提示符变成了 sftp> ，使用`quit`或`exit`命令可以退出连接。

### 远程常用命令

在 SFTP 解释器中可以使用`help`命令来查看帮助文档：

```shell
sftp> help
Available commands:
bye                                Quit sftp
cd path                            Change remote directory to 'path'
chgrp grp path                     Change group of file 'path' to 'grp'
chmod mode path                    Change permissions of file 'path' to 'mode'
chown own path                     Change owner of file 'path' to 'own'
df [-hi] [path]                    Display statistics for current directory or
                                   filesystem containing 'path'
exit                               Quit sftp
get [-afPpRr] remote [local]       Download file
reget [-fPpRr] remote [local]      Resume download file
reput [-fPpRr] [local] remote      Resume upload file
help                               Display this help text
lcd path                           Change local directory to 'path'
lls [ls-options [path]]            Display local directory listing
lmkdir path                        Create local directory
ln [-s] oldpath newpath            Link remote file (-s for symlink)
lpwd                               Print local working directory
ls [-1afhlnrSt] [path]             Display remote directory listing
lumask umask                       Set local umask to 'umask'
mkdir path                         Create remote directory
progress                           Toggle display of progress meter
put [-afPpRr] local [remote]       Upload file
pwd                                Display remote working directory
quit                               Quit sftp
rename oldpath newpath             Rename remote file
rm path                            Delete remote file
rmdir path                         Remove remote directory
symlink oldpath newpath            Symlink remote file
version                            Show SFTP version
!command                           Execute 'command' in local shell
!                                  Escape to local shell
?                                  Synonym for help
```

SFTP 解释器中预置了常用的命令，但是没有自带的 Bash 来得丰富。常用的有如下几个：

* `pwd` 显示当前的工作目录
* `ls` 查看当前目录的内容，使用`-la`参数可以以列表形式查看，并显示隐藏文件
* `cd` 切换目录
* `mkdir` 建立文件夹

### 本地命令

前面的命令都是用来操作远程服务器的，如果想要操作本地目录呢？只需要在每个命令前添加`l`即可，例如显示本地操作目录下的文件：

```shell
sftp> lpwd
Local working directory: /Users/Lin07ux/Git/copyright
sftp> lls
LICENSE.txt   README.md
```

### 运行 shell 命令

使用`!`可以直接运行 Shell 中的指令：

```shell
sftp> !df -h
Filesystem      Size   Used  Avail Capacity iused               ifree %iused  Mounted on
/dev/disk1s1   466Gi  360Gi  101Gi    79% 3642919 9223372036851132888    0%   /
devfs          336Ki  336Ki    0Bi   100%    1162                   0  100%   /dev
/dev/disk1s4   466Gi  4.0Gi  101Gi     4%       5 9223372036854775802    0%   /private/var/vm
map -hosts       0Bi    0Bi    0Bi   100%       0                   0  100%   /net
map auto_home    0Bi    0Bi    0Bi   100%       0                   0  100%   /home
```

### 传输文件

**1. 从远程服务器拉取文件**

使用`get`命令可以从远程服务器拉取文件到本地：

```shell
get remote_file_or_dir [newName] [-r]
```

如果不指定`newName`，将使用和远程服务器相同的文件名。使用`-r`参数可以拉取整个目录。

**2. 从本地上传文件到服务器**

使用`put`命令可以从本地上传文件到服务器：

```shell
put local_file_or_dir [-r]
```

同样的，可以使用`-r`参数来上传整个目录，但是有一点要注意，**如果服务器上不存在这个目录需要首先新建**。


