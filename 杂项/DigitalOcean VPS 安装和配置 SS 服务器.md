## 一、VPS 基本配置

### 1、服务器系统

DO 提供的服务器系统有多个，这里选择的是 Ubuntu 18 x64 系统。

选择好系统和其他配置之后，就可以启动并登录服务器了。

登录之后，首先需要更新系统：

```shell
apt-get update
```

### 2、修改 root 密码

初始化的 root 账户的密码是很长的一个随机字符串，而且如果用密码登录会提示你进行密码重置。虽然可以使用 ssh 方式登录，但是还是建议重置下密码：

```shell
passwd root
```

之后根据提示重置密码即可。

## 二、SS 服务器

### 1、安装 python 和 pip

首先检查系统中是否已经有 Python 或者 Python3 了，如果有了就可以直接安装 python-pip，没有的话，需要先安装 Python。

```shell
python --version
# python3 --version

sudo apt install python-pip
# sudo apt install python3-pip
```

> 如果提示`Unable to locate package python-pip`错误，则需要先更新系统，`apt update`。

### 2、安装 ss 服务

安装完成 pip 之后就可以安装 ss 服务了：

```shell
pip install git+https://github.com/shadowsocks/shadowsocks.git@master
# pip3 install git+https://github.com/shadowsocks/shadowsocks.git@master
```

### 3、配置 ss 服务

创建一个文件`/etc/shadowsocks.json`，里面的内容类似如下：

```json
{
    "server":"my_server_ip",
    "local_address": "127.0.0.1",
    "local_port":1080,
    "port_password": {
        "8000": "password1",
        "8001": "password2",
        "8002": "password3"
    },
    "timeout":300,
    "method":"aes-256-cfb",
    "fast_open": false
}
```

> 如果仅单用户使用，可以将上面的`port_password`改成如下配置：
> ```shell
> "server_port":8388,
> "password":"mypassword",
> ```

### 4、启停 ss 服务

ss 服务可以作为后台服务一直运行：

```shell
ssserver -c /etc/shadowsocks.json -d start
ssserver -c /etc/shadowsocks.json -d stop
ssserver -c /etc/shadowsocks.json -d restart
```

## 三、参考

1. [Shadowsocks readme](https://github.com/shadowsocks/shadowsocks/tree/master)
2. [Configuration via Config File](https://github.com/shadowsocks/shadowsocks/wiki/Configuration-via-Config-File)
3. [Shadowsocks多用户服务端搭建](https://go2think.com/ss-manyuser/)

