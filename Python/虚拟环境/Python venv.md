Python 开发中，不同的项目可能对 Python 或依赖的库的版本的需求不一致，如果直接修改宿主机上的版本，那么可能会造成其他项目出现 bug 或无法正常运行。

Python 社区为了解决这个问题，提出了虚拟环境的概念。也就是人为的创建一个虚拟环境供项目运行，而该项目需要什么库文件，则自行在这个虚拟环境中下载安装，而在虚拟环境中下载安装的库文件，是不会影响到系统环境中的库文件的。有了虚拟环境的概念， Python 的项目管理也就不那么麻烦了。

Python 有多种工具可以实现虚拟环境，如：[virtualenv](https://github.com/pypa/virtualenv)、[pyenv](https://github.com/pyenv/pyenv)、[pipenv](https://github.com/pypa/pipenv)、[anaconda](https://www.anaconda.com/) 以及 Python3 自带的`venv`。

下面以`venv`为例进行虚拟环境的创建。

### 基本语法

通过如下命令可以查看 venv 使用的说明：

```shell
python -m venv -h
```

可以看到，venv 基本的语法为：

```
venv [-h] [--system-site-packages] [--symlinks | --copies] [--clear]
    [--upgrade] [--without-pip]
    ENV_DIR [ENV_DIR ...]
```

各个配置项的解释如下：

* `-h` 获取帮助信息。
* `--system-site-packages` 让虚拟环境可以使用宿主系统中已经安装的库包。
* `--symlinks` 通过连接的方式引用宿主系统的一些库包。
* `--copies` 使用拷贝的方式使用宿主系统的库包。
* `--clear` 如果指定的虚拟环境路径已经存在，则先删除已存在的环境，然后重新用新的配置项创建。
* `--upgrade` 使用当前版本的 Python 更新虚拟环境。
* `--without-pip` 在虚拟环境中不安装 pip。从 Python 3.4 开始，默认会在虚拟环境中安装 pip。
* `ENV_DIR` 虚拟环境的安装位置，如果指定了多个路径，则会在每个路径中都按照相同的配置项创建虚拟环境。

### 创建虚拟环境

首先创建一个项目目录，然后进入该目录之后，创建虚拟环境：

```shell
mkdir python-venv
cd python-venv
python3 -m venv venv
```

这样就在项目目录中创建了一个`.env`文件夹，里面包含有基本的 Python3 和 pip、setuptools 库。

### 激活虚拟环境

创建好项目的虚拟环境之后，还需要激活该虚拟环境才能真正的从宿主环境中脱离：

  Platform |   Shell    | Command to activate virtual environment
 ----------|------------|----------------------------------------
   Posix   | bash/zsh   | `$ source <venv>/bin/activate`
           | fish       | `$ . <venv>/bin/activate.fish`
           | csh/tcsh   | `$ source <venv>/bin/activate.csh`
  Windows  | cmd.exe    | `C:\> <venv>\Scripts\activate.bat`
           | PowerShell | `PS C:\> <venv>\Scripts\Activate.ps1`

其中，`<venv>`需要替换为虚拟环境创建时设置的路径名，比如，对于上面创建的虚拟环境，在 MacOS 中则需要使用如下的方式激活：

```shell
source venv/bin/activate
```

这样就进入到虚拟环境中了，此时使用 pip 安装和升级库就只会在当前项目中起作用，而不会影响宿主环境。

### 退出虚拟环境

退出虚拟环境可以通过直接`exit`或`logout`方式退出终端的方式，可以通过下面的命令来实现：

```shell
deactivate
```

### 资料

* [venv — Creation of virtual environments](https://docs.python.org/3/library/venv.html)
* [12. Virtual Environments and Packages](https://docs.python.org/3/tutorial/venv.html)

