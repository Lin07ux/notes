开发 Laravel 的本地环境当然是用官方提供的 homestead vagrant 镜像。但是鉴于国内特殊原因，homestead 镜像通过命令行在线安装几乎是不可能完成的任务(经常会出现中断的问题，而且不能断点续传)，只能选择先下载 homestead 镜像，再手动安装。

### 下载离线镜像
首先，下载好 Laravel/Homestead 的安装包，目前的地址如下：

```
https://atlas.hashicorp.com/laravel/boxes/homestead/versions/0.5.0/providers/virtualbox.box
```

其中`0.5.0`表示的是 homestead 镜像的版本，可以根据自己的需求来更换这个版本号，从而得到不同版本的镜像。

> 如果想找到当前最新的 box 的地址，可以在[官网](https://atlas.hashicorp.com)上搜索，也可以使用`vagrant box add laravel/homestead`命令来安装，安装的时候，就会在命令行中显示出下载地址。

### 添加离线镜像
下载好 box 文件(假设名称为`homestead.box`)之后，进入存放下载的 box 的文件的目录中，执行下面的命令就可以添加添加本地镜像了：

```shell
vagrant box add laravel/homestead hometead.box
```

添加好之后，使用`vagrant box list`查看安装的镜像，可以看到如下信息：

```
laravel/homestead     (virtualbox, 0)
```

这样虽然能够添加成，但是我们在后续启动 homestead 的步骤中会出现`laravel/homestead not found`一类的提示，而且会重新使用命令行进行下载，这说明我们本地安装的镜像有哪里不对。

因为我们默认安装本地的镜像之后，版本会是 0(就是上面的`(virtualbox, 0)`)，而 homestead 现在默认情况下需要版本在 0.4.0 以上的镜像了。所以要解决上面的问题，方法很简单：将本地安装的镜像的版本设置为 0.4.0 以上就行了。

首先，在我们下载得到的 box(`homestead.box`) 文件的目录中，新建一个 json 文档，名称随便(比如`homestead.json`)，然后写入如下的内容：

```json
{
    "name": "laravel/homestead",
    "versions": [{
        "version": "0.5.0",
        "providers": [{
            "name": "virtualbox",
            "url": "file://homestead.box"
        }]
    }]
}
```

然后我们删除前面添加的 laravel/homestead 镜像，再重新添加，命令如下：

```shell
vagrant box remove laravel/homestead
vagrant box add homestead.json
```

注意：第二个命令添加的是刚才新建的 json 文件，而不是 box 文件。

这时候，我们再查看已经安装的镜像列表：

```
laravel/homestead  (virtualbox, 0.5.0)
```

成功将本地安装的镜像设置了版本号了。之后再启动 Homestead 就不会出错了。

### 克隆官方仓库
镜像安装后就需要克隆 laravel 官方的 homestead git 仓库了：

```shell
git clone https://github.com/laravel/homestead.git Homestead
```

> vagrant，virtualbox，homestead 镜像，以及官方 homestead git 仓库的关系：
> * virtualbox 是虚拟机，它最终的任务就是运行 homestead 虚拟机；
> * homestead 镜像就是 laravel 官方为了方便开发者，将一系列的开发环境、软件打包成一个镜像供大家使用；
> * vagrant 可以看作是对 virtualbox 或 vmware 的一个高级封装，本质就是调用了一些 virtualbox 和 vmware 开放出来的 api。vagrant 官网介绍一开始就有说`Vagrant stands on the shoulders of giants`，所以他能站在高处通过完美的封装方便开发者使用虚拟机。想更多了解请移步[vagrant docs](https://docs.vagrantup.com/)；
> * homestead git 仓库则是 laravel 官方对于 homestead 虚拟机的一些配置文件，里面有一些方便的 linux 脚本，能够方便开发者配置虚拟机。

### 配置 homestead
克隆完成之后，进入克隆的目录`Homestead`中，执行`init.sh`脚本(Window 系统可以执行`init.bat`批处理)。这个脚本的作用就是在用户的 Home 目录下生成一个名为`.homestead`的文件夹，里面会添加三个文件，我们主要关注的是`Homestead.yaml`文件。

在`~/.homestead/Homestead.yaml`就是 Laravel 提供给开发者的一个配置 Homestead 虚拟机的文件，在这里的配置会影响到 homestead 中的相关配置。参数都是很简明易懂，有一点需要注意的是rsa 公钥私钥路径一定要对，不然无法启动。

我们一般会修改的配置有以下三个：

* `folders`  用来设置同步本机的文件夹到 homestead 虚拟机的映射关系。
* `sites`    用来配置 homestead 虚拟机中的 Nginx 网站设置。
* `databases` 用来自动在 homestead 虚拟机中的 MySQL 中生成对应的数据库。

### 启动 homestead
设置 yaml 配置文件后，我们就可以启动 homestead 了，进入到 homestead 目录(就是上一步中 git clone 的那个 Homestead 目录)，使用 Vagrant 命令即可：

```shell
vagrant up
```

由于 vagrant 进行了端口转发(保护本地常用端口)，所以 ssh 端口不再是熟悉的 22，而是被转发到了 2222，我们要想通过 putty 或者其他 ssh 工具连接的话地址应该填上`127.0.0.1:2222`。而如果是用命令行的话，就在该目录下，直接使用下面的命令即可：

```shell
vagrant ssh
```

然后方便调试我们还需要修改本机 hosts 文件，在 hosts 文件中添加如下的记录：

```hosts
192.168.10.10  homestead.app
```

这里的`192.168.10.10`和`homestead.app`都是在 homestead.yaml 配置文件中出现的，分别表示 homestead 虚拟机的地址，和配置的`sites`站点的域名。我们都可以根据自己的需求对这两个设置进行修改的。


### 问题
#### A VirtualBox machine with the name 'homestead-7' already exists
出现这个错误，一般是由于之前已经安装过 homestead，重新安装一遍的时候，原先的 homestead 虚拟机没有被删除导致的。

之所以不删除之前的 homestead 虚拟机会导致重名现象，是由于 homestead 的官方配置(`Homestead/scripts/homestead.rb`)中，设定了默认的虚拟机的名称为`homestead-7`。

```rb
# Configure A Few VirtualBox Settings
config.vm.provider "virtualbox" do |vb|
  vb.name = settings["name"] ||= "homestead-7"
  vb.customize ["modifyvm", :id, "--memory", settings["memory"] ||= "2048"]
  vb.customize ["modifyvm", :id, "--cpus", settings["cpus"] ||= "1"]
  vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
  vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  vb.customize ["modifyvm", :id, "--ostype", "Ubuntu_64"]
end
```

所以每次新建 homestead 虚拟机的时候，如果指定虚拟机的名称，那么就会被命名为`homestead-7`。所以，解决方法有两种：

* 打开 virtualbox 虚拟机软件，找到 homestead-7 虚拟机，将其删除，然后再重新启动新安装的 homestead。
* 不删除原先的虚拟机，而是在新安装的 homestead 虚拟机文件夹中，修改 Vagrantfile 文件，在其中添加如下的内容：

```rb
config.vm.provider "virtualbox" do |vb|
  vb.name = "your-specific-project-name"
end
```


