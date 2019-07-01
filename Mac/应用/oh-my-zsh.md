zsh 是另一个 shell，Mac 上默认使用的 shell 是 bash，而 zsh 相对来说更加好用，因为开源界提供了非常多的插件工具。而 oh-my-zsh 则是基于 zsh 的一个开源配置框架，包含了很多推荐的配置，而且能够使用更多的插件。

## 一、配置

### 1.1 安装与卸载

通过 curl：

```shell
sh -c "$(curl -fsSL https://raw.githubusercontent.com/robbyrussell/oh-my-zsh/master/tools/install.sh)"
```

通过 wget：

```shell
sh -c "$(wget https://raw.githubusercontent.com/robbyrussell/oh-my-zsh/master/tools/install.sh -O -)"
```

卸载时只需要执行卸载命令即可：

```shell
uninstall_oh_my_zsh
```

### 1.2 设置主题

默认的 oh-my-zsh 主题可能并不满意，可以修改 zsh 的配置文件`~/.zshrc`，设置喜欢的主题。主题文件在`~/.oh-my-zsh/themes`目录中。

```shell
ZSH_THEME="ys"
```

> 更多主题：[Themes](https://github.com/robbyrussell/oh-my-zsh/wiki/Themes)
> 
> 显示当前使用的主题：`echo $ZSH_THEME`。

## 二、使用

### 2.1 智能补全

在 oh-my-zsh 中进入一个路径的时候，可以使用简写的方式，然后按 Tab 键就可以自动补全了：

![](http://cnd.qiniu.lin07ux.cn/markdown/1561894700513.png)

这在进入多层路径的时候，这个功能非常有用。

当补全内容较多时，不用像 bash 一样持续提示需要继续输入，也不会像 cmd 永无止境的循环下去，连续敲击两次 TAB 键 zsh 给出一个补全目录，可以上下左右选择：

![](http://cnd.qiniu.lin07ux.cn/markdown/1561894808950.png)

### 2.2 快速跳转

在命令行工作中，经常需要在不同的路径间切来切去，可以使用`cd -`命令跳转到前一次所在的目录。

如果要跳转到更前面的访问过的路径，可以在输入`cd -`之后，按一下 Tab 键，从而得到当前会话中，之前的访问过的路径列表：

![](http://cnd.qiniu.lin07ux.cn/markdown/1561894985201.png)

接着根据下面的提示输入数字按回车就过去了，比如输入`cd -5`之后回车确认，就可以跳转到`~/software/libclang-python3`路径中了。

当然还可以不输入数字，而是再按一次 Tab 进入选择模式，上下键或者`ctrl+n/p`来选择，回车确认，`ctrl+g`返回。

## 三、插件

oh-my-zsh 有很多的第三方插件可以用来扩增其功能，一般都是通过命令的方式来进行增强。

### 3.1 指令高亮效果

指令高亮效果作用是当用户输入正确命令时指令会绿色高亮，错误时命令红色高亮。zsh 中可以通过[zsh-syntax-highlighting](https://github.com/zsh-users/zsh-syntax-highlighting)插件来实现。

在 oh-my-zsh 框架中，可以通过如下方式安装：

1. 拷贝 zsh-syntax-highlighting 插件仓库到 oh-my-zsh 的 plugins 目录中：
 
    ```shell
    git clone https://github.com/zsh-users/zsh-syntax-highlighting.git $ZSH_CUSTOM/plugins/zsh-syntax-highlighting
    ```

2. 在`~/.zshrc`中激活 zsh-syntax-highlighting 插件：

    ```
    plugins=( [plugins...] zsh-syntax-highlighting)
    ```

3. 重启 iTerm2 或者在当前 iTerm2 窗口中重新索引`~/.zshrc`：

    ```shell
    source ~/.zshrc
    ```
    
### 3.2 自动提示命令

可以使用[zsh-autosuggestions](https://github.com/zsh-users/zsh-autosuggestions)插件来实现当输入命令时，终端会自动提示你接下来可能要输入的命令，这时按`→`便可输出这些命令，非常方便。

> 这个效果是基于输入历史的，当在命令行中输入的命令越多的时候，提示就会越多。

安装过程如下：

1. 克隆插件到 oh-my-zsh 的 plugins 路径中：

    ```shell
    git clone git://github.com/zsh-users/zsh-autosuggestions $ZSH_CUSTOM/plugins/zsh-autosuggestions
    ```

2. 在`~/.zshrc`中激活 zsh-syntax-highlighting 插件：

    ```
    plugins=( [plugins...] zsh-autosuggestions)
    ```

3. 重启 iTerm2 或者在当前 iTerm2 窗口中重新索引`~/.zshrc`：

    ```shell
    source ~/.zshrc
    ```
   
### 3.3 autojump

autojump 是一个快速切换、跳转到不同路径的工具，通过简单的`j`命令和路径的部分名称，就可以快速的进入。

使用 oh-my-zsh 的 autojump 插件之前，需要先通过 Homebrew 安装`autojump`。具体安装步骤如下：

1. 使用如下命令安装 autojump：`brew install autojump`。
2. 安装好之后，会在终端上显示出类似如下的提示，根据提示将对应的语句加入到`~/.zshrc`文件中。
3. 将`autojump`加入到`~/.zshrc`的`plugins`中。
4. 重新加载配置：`source ~/.zshrc`。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1521623224522.png" width="915"/>

### 3.4 encode64

提供了两个命令`encode64`、`decode64`，用于对字符串进行 base64 加密和解密。实例如下：

```
$ encode64 some_string
=> c29tZV9zdHJpbmc=

$ decode64 c29tZV9zdHJpbmc=
=> some_string
```

### 3.5 urltools

提供了两个命令`urlencode`和`urldecode`，分别用于对 url 的编码和解码。

```
$ urlencode http://www.baidu.com\?\=lin
=> http%3A%2F%2Fwww.baidu.com%3F%3Dlin

$ urldecode http%3A%2F%2Fwww.baidu.com%3F%3Dlin
=> http://www.baidu.com?=lin
```



