### 更改文件默认打开程序

点击文件 - 右键 - 显示简介 - 打开方式 - 选择合适的程序之后 - 全部更改。

### Mac OS 10.12 安全域隐私没有允许任何来源的app选项

Mac OS 10.12 系统中，在“系统偏好” --> “安全&隐私”中默认去除了允许安装任意来源App的选项，很多不是从 AppStore 中下载的 App 都不能安装了。

官方对这个问题给出的解决办法是：按住键盘上的 Control 键，然后点击要打开的 App 安装文件即可。

但是如果经常需要安装非 AppStore 中的 App 时，还是不方便。查看官方资料，这个选项之所以消失是因为 10.12 系统默认开启了 Gatekeeper 服务，所以只要关闭这个服务即可找到这个选项了。

关闭命令如下(在终端中输入)：

```shell
sudo spctl --master-disable
```

关闭后就能在“安全&隐私”中找到允许安装任意来源App的选项了。但是如果你再次选择了其他的选择，那么就会再次打开 Gatekeeper 服务，从而再次隐藏该选项。如果要重新开启，就再次执行上面的命令即可。

### Mac OS X 升级之后，在终端执行命令出现错误

从 App Store 升级或更新 Mac OS X 之后，在终端中执行某些命令的时候，会出现如下的错误：

```
xcrun: error: invalid active developer path (/Library/Developer/CommandLineTools), missing xcrun at: /Library/Developer/CommandLineTools/usr/bin/xcrun
```

这时候可以在终端中执行如下的命令即可解决：

```shell
xcode-select --install
```

参考：[xcrun: error](http://tips.tutorialhorizon.com/2015/10/01/xcrun-error-invalid-active-developer-path-library-developer-commandline-tools-missing-xcrun/)


