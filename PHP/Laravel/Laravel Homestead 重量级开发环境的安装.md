## 简介

Laravel 为开发者提供了一套完善的重量级本地开发环境 —— Laravel Homestead。

Laravel Homestead 实际是一个打包好各种 Laravel 开发所需软件和工具的 [Vagrant](https://www.vagrantup.com/) 盒子（关于 Vagrant 盒子的释义请参考 [Vagrant 官方文档](https://www.vagrantup.com/docs/boxes.html) ），该盒子为我们提供了一个优秀的开发环境，有了它，我们不再需要在本地环境安装 PHP、Composer、Nginx、MySQL、Memcached、Redis、Node 等其它工具软件，我们也完全不用再担心误操作搞乱操作系统 —— 因为 Vagrant 盒子是一次性的，如果出现错误，可以在数分钟内销毁并重新创建该 Vagrant 盒子！

Homestead 可以运行在 Windows、Mac 以及 Linux 等主流操作系统上，预装的软件和工具列表如下：

* Ubuntu 16.04
* Git
* PHP 7.1
* Nginx
* MySQL
* MariaDB
* SQLite 3
* Postgres
* Composer
* Node（With Yarn, Bower, Grunt, and Gulp）
* Redis
* Memcached
* Beanstalkd
* Mailhog
* ngrok

> 注：如果你使用的是 Windows，需要开启系统的硬件虚拟化（VT-x），这通常可以通过 BIOS 来开启。如果你是在 UEFI 系统上使用 Hyper-V，则需要关闭 Hyper-V 以便可以访问 VT-x。

## 首次安装

> 注：如果之前已经安装了 Homestead，可以直接跳转到升级部分，然后回过头看下新版本新增的特性。

在使用 Homestead 之前，需要先安装 [Virtual Box 5.1](https://www.virtualbox.org/wiki/Downloads) 、 [VMWare](https://www.vmware.com/) 或 [Parallels](http://www.parallels.com/products/desktop/) （三选一，我们通常选择 VirtualBox，因为只有它是免费的）以及 [Vagrant](https://www.vagrantup.com/downloads.html) ，所有这些软件包都为常用操作系统提供了一个便于使用的可视化安装器，通过安装界面引导就可以完成安装。

要使用 VMware 的话，需要购买 VMware Fusion（Mac） / Workstation（Windows） 以及 [VMware Vagrant 插件](https://www.vagrantup.com/vmware) ，尽管不便宜，一套下来要 1000 多块人民币，但是 VMware 可以提供更好的性能和体验（废话，不然谁用）。

要使用 Parallels 的话，需要安装 Parallels Vagrant 插件，这是免费的（仅仅是插件免费哈）。

### 安装 Homestead Vagrant 盒子

Vagrant 和 VirtualBox/VMWare/Parallels 安装好了之后，在终端中使用如下命令将 Homestead Vagrant 盒子`laravel/homesterad`添加到 Vagrant 中。下载该盒子将会花费一些时间，具体时间长短主要取决于你的网络连接速度：

```shell
vagrant box add laravel/homestead
```

> 注：如果上述命令执行失败，需要确认 Vagrant 是否是最新版本。

运行命令会列出一个选择列表，选择你安装的 VirtualBox/VMWare/Parallels 对应选项即可。

> 一般情况下，在国内即便使用了代理，依旧会很慢，经常会断开，建议先下载 box 文件，然后在本地添加。具体操作可以查看 [Vagrant 添加本地 box 安装 Laravel Homestead](https://github.com/Lin07ux/notes/blob/master/Vagrant/Vagrant%20%E6%B7%BB%E5%8A%A0%E6%9C%AC%E5%9C%B0%20box%20%E5%AE%89%E8%A3%85%20Laravel%20Homestead.md)。

### 安装 Homestead

接下来就需要安装 Homestead 了。可以通过克隆仓库代码来实现 Homestead 安装。将仓库克隆到指定目录，如`Homestead`目录，这样 Homestead 盒子就可以作为所有其他 Laravel 项目的主机：

```shell
cd ~
git clone https://github.com/laravel/homestead.git Homestead
```

克隆完成后，你需要检查 Homestead 的版本标签，因为`master`分支不会总是稳定版本，你可以在 [GitHub Release Page](https://github.com/laravel/homestead/releases) 查找到最新稳定版本然后在本地将其检出：

```shell
cd Homestead
# Checkout the desired release...
git checkout v6.1.0
```

接下来，在`Homestead`目录下运行`bash init.sh`命令来创建`Homestead.yaml`配置文件，生成的`Homestead.yaml`配置文件文件位于当前`Homestead`目录：

```
// Mac/Linux...
bash init.sh

// Windows...
init.bat
```

## 配置

### 设置 Provider

`Homestead.yaml`文件中的`provider`键表示使用哪个 Vagrant 提供者：`virtualbox`、`vmware_fushion`、`vmware_workstation`或`parallels`，你可以将其设置为自己喜欢的提供者，当然对大部分人来说也没得选：

```yaml
provider: virtualbox
```

### 配置共享文件夹

`Homestead.yaml`文件中的`folders`属性列出了所有主机和 Homestead 虚拟机共享的文件夹，一旦这些目录中的文件有了修改，将会在本地和 Homestead 虚拟机之间保持同步，如果有需要的话，你可以配置多个共享文件夹：

```yaml
folders:
    - map: ~/Development
      to: /home/vagrant/Code
```

如果要开启 [NFS](https://www.vagrantup.com/docs/synced-folders/nfs.html) ，只需简单添加一个标识到同步文件夹配置：

```yaml
folders:
    - map: ~/Development
      to: /home/vagrant/Code
      type: "nfs"
```

> 注：使用 NFS 的话，需要考虑安装 [vagrant-bindfs](https://github.com/gael-ian/vagrant-bindfs) 插件。该插件可用于在 Homestead 盒子中为文件和目录维护正确的用户/组权限。

你还可以通过`options`传递其他 Vagrant 支持的 [同步文件夹](https://www.vagrantup.com/docs/synced-folders/basic_usage.html) 选项：

```yaml
folders:
    - map: ~/Development
      to: /home/vagrant/Code
      type: "rsync"
      options:
          rsync__args: ["--verbose", "--archive", "--delete", "-zz"]
          rsync__exclude: ["node_modules"]
```

### 配置 Nginx 站点

通过`sites`属性可以方便地将“域名”映射到 Homestead 虚拟机的指定目录，`Homestead.yaml`中默认已经配置了一个示例站点。和共享文件夹一样，可以配置多个站点：

```yaml
sites:
    - map: homestead.app
      to: /home/vagrant/Code/Laravel/public
```

> 如果是在 Homestead 盒子启动之后进行了上述修改，需要运行`vagrant reload --provision`更新虚拟机上的 Nginx 配置。

### 设置 Hosts 文件

不要忘记把 Nginx 站点配置中的域名添加到本地机器上的`hosts`文件中，该文件会将对本地域名的请求重定向到 Homestead 虚拟机，在 Mac 或 Linux 上，该文件位于`/etc/hosts`，在 Windows 上，位于`C:\Windows\System32\drivers\etc\hosts`，添加方式如下：

```yaml
192.168.10.10 homestead.app
```

确保 IP 地址和`Homestead.yaml`文件中列出的一致，一旦将域名添加到`hosts`文件中，就可以在浏览器中通过该域名访问站点了：

```
http://homestead.app
```

> 注：在真正可以访问之前之前还需要通过 Vagrant 启动虚拟机上的 Homestead 盒子。

## 启动 Vagrant Box

配置好`Homestead.yaml`文件后，在`Homestead`目录下运行`vagrant up`命令，Vagrant 将会启动虚拟机并自动配置共享文件夹以及 Nginx 站点，初次启动需要花费一点时间进行初始化：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505829921509.png)

启动之后，就可以在浏览器中通过`http://homestead.app`访问 Laravel 应用了（前提是 Web 目录下已经部署 Laravel 应用代码）：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505829948144.png)

- 要登录到该虚拟机，使用``vagrant ssh`命令；
- 关闭该虚拟机，可以使用`vagrant halt`命令；
- 销毁该虚拟机，可以使用`vagrant destroy --force`命令。

如果启动中遇到问题，可以查看 [Vagrant 问题解决](https://github.com/Lin07ux/notes/blob/master/Vagrant/Vagrant%20%E9%97%AE%E9%A2%98%E8%A7%A3%E5%86%B3.md)。

## 可选操作

### 为指定项目安装 Homestead

全局安装 Homestead 将会使每个项目共享同一个 Homestead 盒子，你还可以为每个项目单独安装 Homestead，这样就会在该项目下创建`Vagrantfile`，允许其他人在该项目中执行`vagrant up`命令，在指定项目根目录下使用 Composer 执行安装命令如下：

```shell
composer require laravel/homestead --dev
```

这样就在项目中安装了 Homestead。安装完成后，使用`make`命令生成`Vagrantfile`和`Homestead.yaml`文件，`make`命令将会自动配置`Homestead.yaml`中的`sites``folders`属性。该命令执行方式如下：

Mac/Linux：

```shell
php vendor/bin/homestead make
```

Windows:

```
vendor\bin\homestead make
```

接下来，在终端中运行`vagrant up`命令然后在浏览器中通过`http://homestead.app`访问站点。不要忘记在`/etc/hosts`文件中添加域名`homestead.app`（已配置的话忽略）。



### 安装 MariaDB

如果希望使用 MariaDB 来替代 MySQL，可以添加`mariadb`配置项到`Homestead.yaml`文件，该选项会移除 MySQL 并安装 MariaDB，MariaDB 是 MySQL 的替代品，完全兼容 MySQL，所以在应用数据库配置中你仍然可以使用`mysql`驱动：

```yaml
box: laravel/homestead
ip: "192.168.20.20"
memory: 2048
cpus: 4
provider: virtualbox
mariadb: true
```

## 日常使用

### 全局访问 Homestead

要想在文件系统的任意路径都能够运行`vagrant up`启动 Homestead 虚拟机，在 Mac/Linux 系统中，可以添加`Bash`函数到`~/.bash_profile`；在 Windows 系统上，需要添加“批处理”文件到`PATH`。这些脚本允许你在系统的任意位置运行 Vagrant 命令，并且把命令执行位置指向 Homestead 的安装路径。

**Max/Linux**

将下面的函数中的`~/Homestead`路径调整为指向实际的`Homestead`安装路径，这样就可以在系统的任意位置运行`homestead up`或`homestead ssh`来启动/登录虚拟机：

```shell
function homestead() {
    ( cd ~/Homestead && vagrant $* )
}
```

> 补充知识点：`/etc/profile`和`~/.bash_profile`都可以用来设置系统`PATH`，不同之处在于前者是给系统超级用户使用，后者是给普通登录用户使用的，此外要让`~/.bash_profile`修改后生效，有两种方法，一种是退出系统重新登录，一种是使用`source ~/.bash_profile`命令。

**Windows**


在系统的任意位置创建一个批处理文件`homestead.bat`，内容如下：

```bat
@echo off

set cwd=%cd%
set homesteadVagrant=C:\Homestead

cd /d %homesteadVagrant% && vagrant %*
cd /d %cwd%

set cwd=
set homesteadVagrant=
```

需要将脚本中实例路径`C:\Homestead`调整为 Homestead 实际安装路径。创建文件之后，添加文件路径到`PATH`，这样就可以在系统的任意位置运行`homestead up`或`homestead ssh`命令了。

### 通过 SSH 连接

可以在`Homestead`目录下通过运行`vagrant ssh`以 SSH 方式连接到虚拟机。如果设置了全局访问 Homestead，也可以在任意路径下通过`homestead ssh`登录到虚拟机。

如果需要以更简捷的方式连接到 Homestead，可以为主机添加一个别名来快速连接到 Homestead 盒子，创建完别名后，可以使用`vm`命令从任何地方以 SSH 方式连接到 Homestead 虚拟机：

```shell
alias vm="ssh vagrant@127.0.0.1 -p 2222"
```

### 连接到数据库

Homestead 默认已经在虚拟机中为 MySQL 和 Postgres 数据库做好了配置，更方便的是，这些配置值与 Laravel 的`.env`中默认提供的配置一致。

想要通过本地的 Navicat 或 Sequel Pro 连接到 Homestead 上的 MySQL 或 Postgres 数据库，可以通过新建连接来实现，主机 IP 都是 127.0.0.1，对于 MySQL 而言，端口号是`33060`，对 Postgres 而言，端口号是`54320`，用户名/密码是`homestead/secret`。

> 注：只有从本地连接 Homestead 的数据库时才能使用这些非标准的端口，在 Homestead 虚拟机中还是应该使用默认的`3306`和`5432`端口进行数据库连接配置。

### 添加更多站点

Homestead 虚拟机在运行时，可能需要添加多个 Laravel 应用到 Nginx 站点。如果是在单个 Homestead 环境中运行多个 Laravel 应用，添加站点很简单，只需将站点添加到`Homestead.yaml`文件：

```yaml
sites:
    - map: homestead.app
      to: /home/vagrant/Code/Laravel/public
    - map: another.app
      to: /home/vagrant/Code/another/public</pre>
```

如果 Vagrant 不是自动管理“hosts”文件，仍然需要添加站点域名到本地`hosts`文件：

```
192.168.10.10  homestead.app
192.168.10.10  another.app
```

添加完站点后，在`Homestead`目录下运行`vagrant reload --provision`命令重启虚拟机。

### 站点类型

Homestead 支持多种框架，所以即使你没有使用 Laravel 的话，也可以使用 Homestead，例如，我们可以通过 `symfony2` 站点类型轻松添加一个 Symfony 应用：

<pre>sites:
    - map: symfony2.app
      to: /home/vagrant/Code/Symfony/web
      type: symfony2</pre>

目前支持的站点类型包括 `apache` 、 `laravel` 、 `proxy` 、 `silverstripe` 、 `statamic` 、 `symfony2` 和 `symfony4` 。

### 站点参数

也可以通过站点指令`params`添加额外的 Nginx `fastcgi_param`值。例如我们可以添加一个`FOO`参数，对应参数值是`BAR`：

```yaml
sites:
    - map: homestead.app
      to: /home/vagrant/Code/Laravel/public
      params:
          - key: FOO
            value: BAR
```

### 配置 Cron 调度任务

Laravel 提供了很方便的方式来 [调度 Cron 任务](https://laravel.com/docs/5.5/scheduling)：只需每分钟调度运行一次 Artisan 命令`schedule:run`即可。`schedule:run`会检查定义在`App\Console\Kernel`类中定义的调度任务并判断运行哪些任务。

如果想要为某个 Homestead 站点运行`schedule:run`命令，需要在定义站点时设置`schedule`为`true`：

```yaml
sites:
    - map: homestead.app
      to: /home/vagrant/Code/Laravel/public
      schedule: true
```

该站点的 Cron 任务会被定义在虚拟机的`/etc/cron.d`目录下：

### 端口转发配置

默认情况下，Homestead 端口转发配置如下：

* SSH: 2222 → Forwards To 22
* HTTP: 8000 → Forwards To 80
* HTTPS: 44300 → Forwards To 443
* MySQL: 33060 → Forwards To 3306
* Postgres: 54320 → Forwards To 5432

### 转发更多端口

如果想要为 Vagrant 盒子添加更多端口转发，做如下转发协议设置即可：

```yaml
ports:
    - send: 93000
      to: 9300
    - send: 7777
      to: 777
      protocol: udp
```

### 分享的环境

有时候可能希望和同事或客户分享自己当前的工作进度或成果，Vagrant 本身支持通过`vagrant share`来支持这个功能；不过，如果在`Homestead.yaml`文件中配置了多个站点的话就不行了。

为了解决这个问题，Homestead 内置了自己的`share`命令，该功能实现的原理是通过 [Ngrok](https://ngrok.com/) 将本地服务分享到互联网上进行公开访问，关于该软件的细节我们这里不讨论，大家可以自行百度，我们主要关注在 Homestead 中如何使用这一功能。首先通过`vagrant ssh`登录到 Homestead 虚拟机然后运行`share homestead.app`命令，这样就可以分享`homestead.app`站点了，其他站点分享以此类推：

```shell
share homestead.app
```

运行完该命令之后，就可以看到一个 Ngrok 界面出现，其中包含活动日志和分享站点所需的公开访问 URL：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1505832122347.png)

### 网络接口

`Homestead.yaml`的`networks`属性用于配置 Homestead 的网络接口，可以想配多少就配多少：

```yaml
networks:
    - type: "private_network"
      ip: "192.168.10.20"
```

要开启 [`bridged`](https://www.vagrantup.com/docs/networking/public_network.html) （桥接模式）接口，需要配置`bridge`设置并修改网络类型为`public_network`：

```yaml
networks:
    - type: "public_network"
      ip: "192.168.10.20"
      bridge: "en1: Wi-Fi (AirPort)"
```

要开启 [DHCP](https://www.vagrantup.com/docs/networking/public_network.html) （动态主机配置协议），只需要从配置中移除`ip`选项即可：

```yaml
networks:
    - type: "public_network"
      bridge: "en1: Wi-Fi (AirPort)"
```

### 更新 Homestead

更新 Homestead 只需两步即可，首先，使用`vagrant box update`命令更新 Vagrant 盒子：

```yaml
vagrant box update
```

接下来，需要更新 Homestead 源码，如果是通过 Github 仓库安装的，只需在克隆仓库的地方运行`git pull origin master`即可。

如果是通过项目的`composer.json`文件安装的 Homestead，需要确保`composer.json`文件包含`"laravel/homestead": "^6"`并更新的依赖：

```shell
composer update
```

### 使用老版本

注：如果只是需要使用老版本的 PHP，在考虑使用老版本的 Homestead 之前查看下多版本 PHP 这部分文档，如果新版本支持所需要的 PHP 版本，那就使用新版本的 Homestead 吧。

可以通过添加如下这行配置到`Homestead.yaml`文件来覆盖 Homestead 使用的老版本的盒子：

```yaml
version: 0.6.0
```

例如：

```yaml
box: laravel/homestead
version: 0.6.0
ip: "192.168.20.20"
memory: 2048
cpus: 4
provider: virtualbox
```

如果使用的是更老版本的 Homestead 盒子，需要为之匹配一个与之兼容的 Homestead 源码。下面的表格列出了支持的盒子版本，使用的 Homestead 源码的版本，以及对应的 PHP 版本：

| PHP | Homestead Version | Box Version |
| --- | --- | --- |
| 7.0 | 3.1.0 | 0.6.0 |
| 7.1 | 4.0.0 | 1.0.0 |
| 7.1 | 5.0.0 | 2.6.0 |
| 7.1 | 6.0.0 | 3.0.0 |


## 转摘

[[ Laravel 5.5 文档 ] 快速入门 —— 重量级开发环境：Homestead 安装使用详细教程](http://laravelacademy.org/post/7658.html)

