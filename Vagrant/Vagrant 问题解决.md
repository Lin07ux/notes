
Vagrant 的安装见文件《使用 Vagrant 打造跨平台开发环境.pdf》

### Virtualbox 无法开启虚拟机
**问题**
使用 vagrant up 命令无法启动虚拟机，而且使用 Virtualbox GUI 界面也无法启动，提示启动遇到异常，代码为：1073741819 (0xc0000005)

**原因**
在 Windows 系统中安装了 MacType 之后，会导致 Virtualbox 虚拟机在启动的时候因 MacType 服务的干扰而出现异常，所以服务启动。

**解决方法**
可以将 Virtualbox 的相关服务都在 MacType 的进程管理中添加例外，不允许其干涉 Virtualbox 虚拟机。
如果这个方法不行，需要将 MacType 服务彻底退出。最好也将安全软件退出。


### Vagrant 无法开启虚拟机
**问题**
使用 Virtualbox GUI 程序能够开启虚拟机，但是使用 vagrant up 命令无法开启虚拟机。
错误信息如下：
	The guest machine entered an invalid state while waiting for it to boot. Valid states are 'starting, running'. The machine is in the 'aborted' state.

**原因**
一般这个是由于项目中 Vagrantfile 文件中的配置不当引起的。
因为在 GUI 中可以看到设置了较小的显存，不支持 3D 加速。而在使用 vagrant up 命令启动时，其配置没有设置好。

**解决方法**
参考：[vagrant issue 2720](https://github.com/mitchellh/vagrant/issues/2720)

打开项目中的 Vagrantfile 文件，更改下面的代码：

```shell
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #  vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #  vb.memory = "1024"
  # end
```

更改为：

```shell
config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
    vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
    vb.memory = "1024"
end
```


### vagrant 启动报错
**问题**
启动时，提示“The following SSH command responded with a non-zero exit status”。

**原因**
问题就处在在持久网络设备udev规则（persistent network device udev rules）是被原VM设置好的，再用box生成新VM时，这些rules需要被更新。而这和Vagrantfile里对新VM设置private network的指令发生冲突。删除就好了。

**解决方法**
虽然vagrant up启动报错，但是vagrant ssh还是能登陆虚拟机的，进入虚拟机后，执行如下命令
`sudo rm -f /etc/udev/rules.d/70-persistent-net.rules`




