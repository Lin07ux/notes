通过在服务器上部署公钥，在本地上做相关的设置即可实现 ssh 的免密登录。

## 生成密钥
首先需要在本地生成一个密钥对，使用`ssh-keygen`命令即可。

```shell
cd ~/.ssh/
ssh-keygen -t rsa
```

在命令执行过程中，会提示设置密钥对名称和密码。为了避免其他影响，建议设置一个单独的名称，而不是直接使用默认的名称(id_rsa)。至于密码则一般不需要设置。

这样，就会在`~/.ssh/`目录下生成指定名称的密钥对了。

## 部署公钥到服务器
接下来，需要使用`ssh-copy-id`命令将刚才生成的公钥部署到服务器上。

> 注意：可能系统中没有安装`ssh-copy-id`这个工具，可能需要先安装。在 Mac 上可以用如下的命令安装：`brew install ssh-copy-id`。

`ssh-copy-id -i ~/.ssh/<name>.pub <user>@<host>`

其中，`name`就是刚才生成密钥的时候输入的名称；`user`就是登陆服务器的那个用户名；`host`则是服务器的 IP 或者域名。

这样就把本机的公钥追到服务器上 user 的 ~/.ssh/authorized_keys 文件中了。

> 如果 ssh 的端口不是 22，可用下面命令：`ssh-copy-id -i ~/.ssh/<name>.pub "-p <port> <user>@<host>"`，port 就是 ssh 的端口号。

## 配置 ssh config
接下来，在本机上配置一下 ssh 的配置文件。

SSH 连接建立之前，会在系统中寻找它的配置，一般有两个位置：
    * `/etc/ssh/ssh_config` 这里是对所有用户适用的全局配置
    * `~/.ssh/config`或者 `$HOME/.ssh/config` 这是用户的个人配置，这些配置会覆盖全局配置
    
我们就直接修改`~/.ssh/config`文件了。在其中添加如下的内容：

```conf
# 配置示例
Host <serv-name>
  HostName <host>
  User <user>
  Port <port>
  IdentityFile <~/.ssh/name>
```

这里，`serv-name`我们可以任意定义，只要方便我们记忆即可，这个是在我们登录服务器的时候会用到，是给服务器取的一个别名；`host`、`user`、`port`和上一节的意义相同，当然如果 ssh 的端口是默认的 22 也可以不设置；最后的`IdentityFile`需要我们指定对应的私钥的路径，也就是我们刚才生成的私钥的路径。


## 登录服务器
上面设置好之后，即可免密登录了：

`ssh <serv-name>`

其中，`serv-name`就是我们配置文件中设置的`Host`字段的值。

执行命令，即可直接登录服务器了，而不会提示你输入密码了。

> 当然，也可以不做上面那么多的配置。因为 ssh 默认的认证文件是`~/.ssh/id_dsa`，所以我们如果生成密钥对的时候，使用默认的名称，然后登录服务器的时候，通过`ssh user@host`的方式也是可以免密登录的。但这显然没有上面配置之后方便。

## ssh 配置文件说明
在 ssh 的配置文件中，都是一行行的键值对。其中，键名不区分大小写，但是值是区分大小写的。

一般常用的键名并不是很多，主要有如下一些：

- `Host`          SSH 连接名
- `HostName`      如上所示，可以是通配符，可以是 IP，也可以是域名等
- `User`          登录服务器的用户名
- `IdentifyFile`  version 1 协议下默认是`~/.ssh/identify`，version 2 协议下，默认是依次匹配：`~/.ssh/id_dsa`， `~/.ssh/id_ecdsa`，`~/.ssh/id_rsa`，还有 version 2 兼容模式。
- `LocalForward`  端口的内部跳转
- `Port`          端口设置，默认 SSH 的端口是 22
- `Protocal`      协议版本号，1 或者 2

其中，`LocalForward`的配置是在服务器中设置了端口的跳转的时候，可以方便的配置。如数据库的 3306 端口对外不开放，可以开放另一个接口，然后内部跳转到 3306，我们可以如下配置：

`LocalForward 8999 127.0.0.1:3306`

其对应的命令行如下：

`ssh -f -N -L 8999:127.0.0.1:3306 test@database.server.com`

当有多台远程终端的时候，config 文件的优势就更加明显了，可以给每个服务器做相应的设置，即可方便的登录对应的服务器，而不需要输入一大串的命令。


