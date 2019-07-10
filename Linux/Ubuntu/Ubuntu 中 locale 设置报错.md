### 1. 问题

当执行`apt-get install ...`或`perl -e`时，会出现类似以下错误：

```
perl: warning: Setting locale failed.
perl: warning: Please check that your locale settings:
        LANGUAGE = (unset),
        LC_ALL = (unset),
        LC_PAPER = "en_US.UTF-8",
        LC_ADDRESS = "en_US.UTF-8",
        LC_MONETARY = "en_US.UTF-8",
        LC_NUMERIC = "en_US.UTF-8",
        LC_TELEPHONE = "en_US.UTF-8",
        LC_IDENTIFICATION = "en_US.UTF-8",
        LC_MEASUREMENT = "en_US.UTF-8",
        LC_TIME = "en_US.UTF-8",
        LC_NAME = "en_US.UTF-8",
        LANG = "en_US.UTF-8"
    are supported and installed on your system.
```

### 2. 原因

通过错误提示，可以知道是系统中的 locale 设置有问题，其中`LANGUAGE`与`LC_ALL`变量均未设置。

### 3. 解决

首先，应确认系统中是否安装了想使用的 locale。在 Terminal 中执行以下命令：

```shell
locale -a
```

这会列出系统中当前已经安装的 locale 信息。若此时系统已安装需要的 locale(假设为`en_US.UTF-8`)，可以使用以下命令将未设置的 locale 变量的值写入 locale 的默认设置中：

```shell
echo 'LANGUAGE="en_US.UTF-8"' >> /etc/default/locale
echo 'LC_ALL="en_US.UTF-8"' >> /etc/default/locale
```

设置之后，需要重启才能生效：

```shell
reboot
```

重启过后可以使用`perl -e`再检查一下设置是否生效。若 Terminal 返回以下信息就说明设置问题已经解决：

```
No code specified for -e.
```

### 4. 转摘

[解决 Ubuntu 中 locale 设置报错问题](https://zhuanlan.zhihu.com/p/39576063)

