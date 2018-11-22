在安装 Laravel Homestead 虚拟机的时候，由于一些原因，直接添加 box 实在太慢，中途失败的几率又太大。所以，在国内，非常推荐直接使用本地 box 的方式安装 Laravel Homestead。但是通过本地添加的 box 在使用的时候，可能会遇到版本问题。下面的方式可以解决这个问题。

### 1. 下载 box 文件

你可以使用迅雷或其他的方式下载 Homestead 的 box 文件，相关的下载地址如下：

```
https://app.vagrantup.com/laravel/boxes/homestead/versions/3.0.0/providers/virtualbox.box
```

这里提供的是 v3.0.0 版本的用于 virtualbox 的 box。如果需要下载其他的版本，可以查看这个页面，找到对应的版本和 providers，替换上面下载链接中的版本号和 providers 后面的值：

```
https://app.vagrantup.com/laravel/boxes/homestead
```

### 2. 添加本地的 box

在命令行中执行下面的命令：

```shell
vagrant box add laravel/homestead ~/Downloads/virtualbox.box
```

注意后面的路径`~/Downloads/virtualbox.box`是刚下载下来的 box 在电脑上存放的路径。如果是 Windows 系统，路径形式差不多是这样：`file:///c:/users/jellybool/downloads/virtualbox.box`。

执行之后，大概是这个样子：

![](http://cnd.qiniu.lin07ux.cn/markdown/1505796479115.png)

**注意：添加成功之后，一定不要急着执行`vagrant up`**

### 3. 修改版本号

Mac 用户需要来到`/.vagrant.d/boxes/laravel-VAGRANTSLASH-homestead`目录：

```shell
cd ~/.vagrant.d/boxes/laravel-VAGRANTSLASH-homestead
ls
```

> Windows 用户对应的目录大概是`c:/users/jellybool/.vagrant.d/boxes/laravel-VAGRANTSLASH-homestead`。

你大概会看到这个目录中有一个名称为`0`的目录。然后在这里需要做两步工作：

1. 添加一个名为`metadata_url`的文件，里面只写上这个 url：

    ```
    https://app.vagrantup.com/laravel/boxes/homestead
    ```

    > 需要注意的是，这个文件名称就是`metadata_url`，而且其内容除了这个网址之外，不能有任何其他的字符，包括空格、换行符。

2. 把这个目录中的那个`0`目录修改为你的 box 版本号，比如这里的就是第一步的`3.0.0`。

### 4. 启动 Vagrant

做好上面的三步之后，就可以进入到 Homestead 目录中执行`vagrant up`命令来启动 Homestead 虚拟机了。

### 转摘

[vagrant 添加本地 box 安装 laravel homestead](https://www.codecasts.com/blog/post/vagrant-add-homestead-locally)

