zsh 是另一个 shell，Mac 上默认使用的 shell 是 bash，而 zsh 相对来说更加好用，因为开源界提供了非常多的插件工具。而 oh-my-zsh 则是基于 zsh 的一个开源配置框架，包含了很多推荐的配置，而且能够使用更多的插件。

### 安装与卸载

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

### 设置主题

默认的 oh-my-zsh 主题可能并不满意，可以修改 zsh 的配置文件`~/.zshrc`，设置喜欢的主题。主题文件在`~/.oh-my-zsh/themes`目录中。

```shell
ZSH_THEME="ys"
```

> 更多主题：[Themes](https://github.com/robbyrussell/oh-my-zsh/wiki/Themes)
> 
> 显示当前使用的主题：`echo $ZSH_THEME`。

### 指令高亮效果

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
    
### 自动提示命令

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
   
### autojump

autojump 是一个快速切换、跳转到不同路径的工具，通过简单的`j`命令和路径的部分名称，就可以快速的进入。

使用 oh-my-zsh 的 autojump 插件之前，需要先通过 Homebrew 安装`autojump`。具体安装步骤如下：

1. 使用如下命令安装 autojump：`brew install autojump`。
2. 安装好之后，会在终端上显示出类似如下的提示，根据提示将对应的语句加入到`~/.zshrc`文件中。
3. 将`autojump`加入到`~/.zshrc`的`plugins`中。
4. 重新加载配置：`source ~/.zshrc`。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1521623224522.png" width="915"/>

### encode64

提供了两个命令`encode64`、`decode64`，用于对字符串进行 base64 加密和解密。实例如下：

```
$ encode64 some_string
=> c29tZV9zdHJpbmc=

$ decode64 c29tZV9zdHJpbmc=
=> some_string
```

### urltools

提供了两个命令`urlencode`和`urldecode`，分别用于对 url 的编码和解码。

```
$ urlencode http://www.baidu.com\?\=lin
=> http%3A%2F%2Fwww.baidu.com%3F%3Dlin

$ urldecode http%3A%2F%2Fwww.baidu.com%3F%3Dlin
=> http://www.baidu.com?=lin
```



