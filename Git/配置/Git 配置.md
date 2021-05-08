## 一、基础

### 1.1 Git config 配置文件

git config 文件可以位于三个地方，这三个地方的配置，后一个会覆盖前一个的设置，比如`~/.gitconfig`会覆盖`/etc/config`的配置：

- `/etc/gitconfig`：包含了适用于系统所有用户和所有库的值。如果传递参数选项`--system`给`git config`，它将明确的读和写这个文件。

- `~/.gitconfig`：这个是对当前登陆用户起作用的配置文件，可以通过传递`--global`选项使 Git 读写这个特定的文件。

- git 安装目录中的 config 文件。

> 在 Windows 系统中，`/etc/gitconfig`会被设置为相对 Git 的安装根目录进行查找。

其他相关介绍：[Git config 配置文件](http://www.cnblogs.com/wanqieddy/archive/2012/08/03/2621027.html)

### 1.2 Git Alias

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

## 二、问题相关

### 2.1 Git 不能显示中文(或其他非英文)

当使用`git status`或者其他的 git 指令的时候，如果文件或目录中包含 utf-8 字符(如中文)，那么就会显示形如`\347\273\223`的乱码。

这种情况下，可以将`core.quotepath`设置为 false，就不会对`0x80`以上的字符进行 quote 了：

```shell
git config --global core.quotepath false
```

### 2.2 换行符自动转换

Git 默认情况下会将全部的换行符都转换为类 Unix 系统中的`LF`，比如，在 Windows 系统下一直使用`CRLF`作为换行符，当使用 Git 将文件提交时，会自动将`CRLF`转换为`LF`，在将仓库的数据拉回本地的时候，则自动将`LF`替换为`CRLF`。但是这种转换是有 bug 的，可能就会造成无法正确的将`CRLF`替换为`LF`，从而引起一些问题。

为了避免换行符自动被替换，可以设置`autocrlf`项为 false。

在`[core]`区段找到`autocrlf`，将它的值改为`false`。如果没找到，就在`[core]`区段中新增一行：

```conf
[core]
autocrlf = false
```

关于换行符，`[core]`区段还有一个`safecrlf`选项用来检查文件是否混用了不同风格的换行符，可以有如下几个值：

* `false` - 不做任何检查   
* `warn` - 在提交时检查并警告
* `true` - 在提交时检查，如果发现混用则拒绝提交

### 2.2 git pull 时采用不使用 merge 方式合并

在拉取远程分支的更新时，如果本地也有了新的提交，默认情况下，会生成一个 merge 提交来合并远程和本地的提交。

如果想保持分支的线性，可以使用 rebase 方式来拉取。只需要执行如下命令进行配置即可：

```shell
# 本项目配置和全局配置
git config pull.rebase true
git config --global pull.rebase true
```

![git pull rebase](http://cnd.qiniu.lin07ux.cn/markdown/git-pull-rebase-e81df88539221.png)

