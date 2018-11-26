pip 是 Python 包管理工具，该工具提供了对Python 包的查找、下载、安装、卸载的功能。

[pip 官网](https://pypi.org/project/pip/)

### 安装

如果未安装过 pip，则可以通过如下的命令来安装：

```shell
curl https://bootstrap.pypa.io/get-pip.py -o get-pip.py
sudo python get-pip.py
```

如果同时安装了 Python 2.x 和 Python 3.x，那么就会有两个 pip：pip2(或 pip) 和 pip3。一般情况下，pip 就表示 pip2。

> 不论是 pip2 还是 pip3，都只需要通过这种方式安装一次即可。

### 升级

可以通过如下命令来查看是否需要升级：

```shell
pip show pip
```

如果需要升级，会有类似如下的输出：

```shell
ou are using pip version 9.0.1, however version 18.1 is available.
You should consider upgrading via the 'pip install --upgrade pip' command.
```

如果需要进行更新，则可以通过如下方式进行升级：

```shell
pip install --upgrade pip
# 或
pip3 install --upgrade pip
```

> 如果同时安装了 Python 2.x 和 Python 3.x，那么只需要更新一个即可。


这种方式也可以用来安装和升级 Python 包：

```shell
pip install <package-name>
pip install --upgrade <package-name>
```

