### HTTPie
HTTPie 是一个更加人性化的 HTTP 命令行客户端。提供了一些简单常用的 HTTP 命令，可以用来测试服务器等。比 Curl 更加的人性化。

[HTTPie - Github](https://github.com/jkbrzt/httpie)

Mac OSX 安装：

`brew install httpie`

## Alfred
### NewFile
`nf + 空格+文件名.后缀`  在当前Finder新建文件，默认文件扩展名为‘.txt’
`nfo + 空格+文件名.后缀` 在当前Finder新建文件，并打开文件，默认文件扩展名为‘.txt’
多个文件之间用「/」分隔。如果当前没有打开的 Finder 窗口，则默认在桌面上新建你指定的文件。

### Terminalfinder
`ft`  可以当前 Finder 窗口路径作为默认路径打开 terminal
`tf`  打开当前 terminal 所在路径对应的文件夹

### Recent Documents
在 Alfred 窗口中输入`recent`，即可查看最近使用过的文件或文件夹。


## oh-my-zsh 插件
oh-my-zsh 是基于 Mac 里面的 zsh 终端开发的一套框架，能改好的改善 Mac 下终端的使用体验。
### 安装 oh-my-zsh
安装，执行下面的命令即可：

`sh -c "$(curl -fsSL https://raw.github.com/robbyrussell/oh-my-zsh/master/tools/install.sh)"`

也可以使用 Git 拷贝 oh-my-zsh 的库来自行安装。

### 更改主题
编辑`~/.zshrc`文件，将`ZSH_THEME="ys"`这里的值 ys 改成你想要的主题即可。
> 主题文件在`~/.oh-my-zsh/themes`目录中。

显示当前使用的主题：
`echo $ZSH_THEME`

### 自动补齐
Mac 下的终端默认情况下，自动补齐是要严格匹配大小写的，而使用 Oh-my-zsh 则不严格区分大小写。

如果我们在`cd`命令后面加一个空格，然后按下`Tab`键，那么就会自动将目录下的所有子目录和文件列出来。
在这个状态下，如果继续按一下`Tab`键，就可以在这个目录和文件列表中通过光标移动并选择。

### 插件
[](http://swiftcafe.io/2015/12/04/omz-plugin/)
[zsh-autosuggestions](https://github.com/zsh-users/zsh-autosuggestions) 命令行自动提示

### 其他
[哦！我的Zsh！](http://hczhcz.github.io/2014/03/27/oh-my-zsh.html)


