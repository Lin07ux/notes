> 转摘：[Centos7 安装字体库&中文字体](http://www.mamicode.com/info-detail-2190315.html)

### 1. 查看当前已安装字体

使用`fc-list`可以查看当前系统中已经安装了的字体。如果提示没有该命令，则需要先安装该工具：

```shell
yum -y install fontconfig
```

### 2. 添加中文字体文件

在安装字体之前，需要先将中文字体文件拷贝到系统中：

```shell
# 创建一个专门存放中文字体的文件夹，当然，放在其他文件夹中也是可以的
mkdir /usr/share/fonts/chinese
# 移动字体文件到该文件夹中
mv simhei.ttf /usr/share/fonts/chinese/
# 修改文件夹权限，确保该文件夹可被查看和执行
chmod -R 755 /usr/share/fonts/chinese/
```

### 3. 安装及配置

做好准备工作后，就可以使用 ttmkfdir 工具来搜索目录中所有的字体信息，并汇总生成`fonts.scale`文件。配置好之后该字体就会被安装到系统中了。

```shell
# 如果没有安装 ttmkfdir 则需要先进行安装
yum -y install ttmkfdir
# 执行 ttmkfdir 命令
ttmkfdir -e /usr/share/X11/fonts/encodings/encodings.dir
# 修改字体配置文件，确保存放改字体文件的路径在配置文件中
vim /etc/fonts/fonts.conf
```

在`fonts.conf`文件中，可以看到已经列出了一些字体文件的目录。如果在前面第 2 步中新创建的字体文件存放路径不在这里，则需要将其添加进入。如下图中的倒数第二行：

![](http://cnd.qiniu.lin07ux.cn/markdown/1584939037218.png)

### 4. 刷新字体缓存

执行完上面的操作后，不需要重启系统，只需要刷新系统的字体缓存即可：

```shell
# 刷新缓存
fc-cache
# 查看当前系统的字体列表
fc-list
```

这样在系统中就可以使用该字体了。需要注意的是，使用字体的时候，其名称与字体文件的名称并不一定是相同的。


