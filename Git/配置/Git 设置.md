
### Git config 配置文件

git config 文件可以位于三个地方，这三个地方的配置，后一个会覆盖前一个的设置，比如 ~/.gitconfig 会覆盖 /etc/config 的配置：

- /etc/gitconfig 文件：包含了适用于系统所有用户和所有库的值。如果你传递参数选项’--system’ 给 git config，它将明确的读和写这个文件。

- ~/.gitconfig 文件：这个是对当前登陆用户起作用的配置文件，可以通过传递 --global 选项使 Git 读写这个特定的文件。

- git 安装目录中的 config 文件。

> 在 Windows 系统中，/etc/gitconfig 会被设置为相对 Git 的安装根目录进行查找。

其他相关介绍：[Git config 配置文件](http://www.cnblogs.com/wanqieddy/archive/2012/08/03/2621027.html)

### Git 不能显示中文(或其他非英文)

当使用`git status`或者其他的 git 指令的时候，如果文件或目录中包含 utf-8 字符(如中文)，那么就会显示形如 \347\273\223.png 的乱码。

这种情况下，可以将 core.quotepath 设置为 false，就不会对 0x80 以上的字符进行 quote 了：
	`$ git config --global core.quotepath false`

### Git Alias

可以在 Git 的配置文件中设置`alias`来为一个或多个命令定义一个新的名称，这些命令通常会包含一些特定的选项或标识。使用别名一般是为了高效的调用一些罕见的、重复的、比较复杂的命令。

比如，可以通过`git config --global --add alias.st status`在全局配置中设置了`status`的别名为`st`，这样就可以使用`git st`表示`git status`了。

也可以直接编辑配置文件，在其中的`[alias]`配置段中添加别名设置，比如：

```ini
[alias]
st = status
```

在 Git 的配置中，并不是只能定义 Git 的子命令别名，还可以定义和运行其他的 shell 命令，这样就可以完成更加复杂的任务。比如，在 Git 的配置文件中的`[alias]`配置段中添加如下的配置：

```ini
[alias]
upstream-merge = !"git fetch origin -v && git fetch upstream -v && git merge upstream/master && git push"
```

这样就定义了一个`upstream-merge`命令，通过这个命令可以完成一系列的 shell 命令。

注意：定义中的`!`是用于告知 Git 来通过 shell 运行这个命令。当然，这样的定义并非只能写 git 操作，还有设置更多其他的 shell 命令。


