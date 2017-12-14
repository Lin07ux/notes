
Vagrant 的安装见文件《使用 Vagrant 打造跨平台开发环境.pdf》

### 在 host 文件中设置的域名无法通过浏览器访问到虚拟机

**问题**

虚拟机正常启动后，发现在 host 文件中设置的自定义域名无法访问了，通过 IP 可以正常访问，而且在命令行中使用 curl 也能访问。

**原因**

在 host 中设置的自定义域名的顶级域名没有被注册，所以浏览器不会发送请求到该域名。此时，只要修改为正确的顶级域名，再配置虚拟机中的网站即可。

比如，原先设置的域名`lin07ux.dev`无法在浏览器中访问，而改成`lin07ux.io`就可以正常了。

> 该问题之前未出现过，在我将系统升级到 Mac 10.13.2 之后就出现了，但由于也同时升级了 Chrome 浏览器，所以不知道是不是因为系统版本的问题，还是浏览器版本的问题。暂时这样是可以解决的。

### box 存放位置

add 一个新的 box 之后，Vagrant 会将 box 默认存放在`~/.vagrant.d/boxes`中。

可以通过下面的方式更改默认的存放位置：

```shell
# 拷贝原先默认的 boxes 文件夹到新的目标位置
cp ~/.vagrant.d /path/to/vagrant_home
# 设置环境变量，添加 Vagrant 的主目录变量
export VAGRANT_HOME='/path/to/vagrant_home'
```

### 启动被中断

**问题**

使用 Virtualbox GUI 程序能够开启虚拟机，但是使用 vagrant up 命令无法开启虚拟机。
错误信息如下：

```
The guest machine entered an invalid state while waiting for it to boot. Valid states are 'starting, running'. The machine is in the 'aborted' state.
```

**原因**

一般这个是由于项目中 Vagrantfile 文件中的配置不当引起的。因为在 GUI 中可以看到设置了较小的显存，不支持 3D 加速。而在使用`vagrant up`命令启动时，其配置没有设置好。

**解决方法**

参考：[vagrant issue 2720](https://github.com/mitchellh/vagrant/issues/2720)

打开项目中的 Vagrantfile 文件，将下面的代码前的注释去掉，结果如下：

```shell
config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
    vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
    vb.memory = "1024"
end
```

### 非零错误码退出

**问题**

启动时，提示`The following SSH command responded with a non-zero exit status`。

**原因**

问题出在持久网络设备 udev 规则（persistent network device udev rules）是被原 VM 设置好的，在用 box 生成新 VM 时，这些 rules 需要被更新。而这和 Vagrantfile 里对新 VM 设置 private network 的指令发生冲突。删除就好了。

**解决方法**

虽然`vagrant up`启动报错，但是`vagrant ssh`还是能登陆虚拟机的，进入虚拟机后，执行如下命令：`sudo rm -f /etc/udev/rules.d/70-persistent-net.rules`。

### 启动卡在 SSH auth method: private key

**问题**

在使用 Laravel Homestead 的时候，配置好相关的操作后，启动`vagrant up`之后，进度会卡在`homestead-7: SSH auth method: private key`这里不再进行。

**原因**

这一般是由于 Vagrant 和 VirtualBox 的版本较低，不匹配 Homestead 的相关要求造成的。

**解决办法**

更新 Vagrant 和 VirtualBox。更新完成后，将创建的 Homestead 虚拟机销毁后重建后即可：

```shell
vagrant destroy
vagrant up
```

参考：

1. [Homestead hangs at SSH auth method: private key](https://laracasts.com/discuss/channels/laravel/homestead-hangs-at-ssh-auth-method-private-key)
2. [Vagrant Stopping at homestead-7: SSH auth method: private key](https://laracasts.com/discuss/channels/servers/vagrant-stopping-at-homestead-7-ssh-auth-method-private-key)

### 虚拟机名称已经存在

**问题**

启动时报错，错误提示如下：

```
A VirtualBox machine with the name 'homestead' already exists.
Please use another name or delete the machine with the existing
name, and try again.
```

**原因**

配置中指定了虚拟机的名称，多次使用相同的配置创建了虚拟机，而且没有通过`vagrant destroy`命令删除之前的虚拟机造成创建的虚拟机的名称相同的情况，从而会报错。

**解决办法**

首先可以打开 VirtualBox 来删除一些不需要的虚拟机，然后再重新启动。

需要注意的是，删除不需要的虚拟机的时候，推荐使用`vagrant destroy`命令进行销毁。

参考：[解决安装laravel/homestead vagrant环境报"A VirtualBox machine with the name 'homestead' already exists."的错误](http://www.cnblogs.com/huangye-dream/p/4604973.html)

### Windows 启动异常 0xc0000005

**问题**

使用`vagrant up`命令无法启动虚拟机，而且使用 Virtualbox GUI 界面也无法启动，提示启动遇到异常，代码为：1073741819 (0xc0000005)

**原因**

在 Windows 系统中安装了 MacType 之后，会导致 Virtualbox 虚拟机在启动的时候因 MacType 服务的干扰而出现异常，所以服务启动。

**解决方法**

可以将 Virtualbox 的相关服务都在 MacType 的进程管理中添加例外，不允许其干涉 Virtualbox 虚拟机。如果这个方法不行，需要将 MacType 服务彻底退出。最好也将安全软件退出。


### The guest additions on this VM do not match the installed version of VirtualBox!


**问题**

启动的时候，会检查宿主机器和虚拟机中的 guest additions 的版本信息。两者的版本不匹配可能不会引起问题，但是如果遇到文件夹同步等方面的问题时，可以考虑先更新 guest additions 版本。

**解决方法**

在宿主机中，执行如下的命令：

```shell
sudo vagrant plugin install vagrant-vbguest
```

该命令会安装一个插件，在启动虚拟机的时候，该插件会自动更新 guest additions，使宿主机和虚拟机中的版本一致。

> 参考：[Virtualbox doesn't match with guest additions version](https://askubuntu.com/questions/649734/virtualbox-doesnt-match-with-guest-additions-version)


