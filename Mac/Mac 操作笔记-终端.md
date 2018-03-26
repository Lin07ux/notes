## 终端命令
### 命令行快捷键

- `Command + L` 清除上一部分内容
- `Command + K` 清除当前终端所有的内容
- `clear`  清屏     
- `Ctrl + R`  在命令行中搜索已经使用过的命令，如果没有找到，可以再按一次到下一个匹配的命令。

### 粘贴板

命令行中粘贴板可以使用`pbcopy`来调用，比如将文件内容拷贝到粘贴板中：`pbcopy > ~/.ssh/id_rsa.pub`

### 切换终端 shell

使用 chsh 命令可以更改默认的终端，比如切换到 zsh 终端：`chsh -s /bin/zsh`

### 查看当前使用的是哪种 shell

使用`ps -p $$`或者`ps $$`即可看到。

### 命令行打开应用

* `open /path/to/some.app`   打开指定路径中的指定应用
* `open "path/to/file.ext"`  使用默认的应用打开指定的文件
* `open /path/`              在 Finder 中打开指定路径
* `open -a /path/to/some.app "/path/to/file.ext"`   使用指定应用打开指定文件
* `open -e "/path/to/file.ext"`  使用 TextEdit 打开指定文件
* `open http://www.apple.com/`   使用默认浏览器打开网址

在命令行下打开 Sublime Text 可以如下的设置：

* 如果是在默认的 Shell 下，可以创建软连接到系统环境路径中：`sudo ln -s "/Applications/Sublime\ Text.app/Contents/SharedSupport/bin/subl" /usr/bin/st`

* 如果是使用的 zsh 则可以在`~/.zshrc`文件中添加如下的命令即可：`alias st="'/Applications/Sublime Text.app/Contents/SharedSupport/bin/subl'"`

### pushd

`pushd <path>`

pushd 命令可以在 cd 进入到一个路径的同时，将路径保存起来。

之后可以使用`dirs -v`来查看所保存的路径。(当前进入的目录总是保存在最顶部)
在切换的时候，可以使用`pushd +<编号>`的方式来快速进入。(+号是不可少的，比如：`pushd +1`就是返回上一个路径。)

### cd

`cd <path>`可以进入一个路径，而`cd -`可以返回上一个路径。

### 查看进程数

`ps aux | grep -c php-fpm`  这样可以查看 php-fpm 的进程数。


## 终端设置
### 设置 host

、Mac 系统的 host 文件位于：`/etc/hosts`。

### 修改 hostname($HOST)

在终端中，一般会显示当前电脑的电脑名或者 $HOST。

修改 $HOST 的方法为：`sudo scutil --set hostname [ newname | newname.local ]`。

> 如果出现使用 newname 方式重命名之后，不能访问网络，可以设置为 newname.local 方式试试。

另外，还可以在`设置 -> 共享`中修改电脑名称即可。需要注意的是，电脑名称中的空格在终端中显示的时候会自动替换成短横线`-`。

### 自定义终端提示符

默认情况下，Mac 终端中的提示符显示的效果这种格式：`\h:\w \u$`。也即是`电脑名称:当前路径 当前用户名$`。

可以在`~/.bash_profile`文件中修改显示样式，添加如下的一条语句即可：

`export PS1="\u@\h:\W $ "`

其他一些配置指令如下：

```
PS1内容详情 
\a    ASCII 响铃字符（也可以键入 \007） 
\d    "Wed Sep 06" 格式的日期 
\e    ASCII 转义字符（也可以键入 \033） 
\h    主机名的第一部分（如 "mybox"） 
\H    主机的全称（如 "mybox.mydomain.com"） 
\j    在此 shell 中通过按 ^Z 挂起的进程数 
\l    此 shell 的终端设备名（如 "ttyp4"） 
\n    换行符 
\r    回车符 
\s    shell 的名称（如 "bash"） 
\t    24 小时制时间（如 "23:01:01"） 
\T    12 小时制时间（如 "11:01:01"） 
\@    带有 am/pm 的 12 小时制时间 
\u    用户名 
\v    bash 的版本（如 2.04） 
\V    Bash 版本（包括补丁级别） ?/td> 
\w    当前工作目录（如 "/home/drobbins"） 
\W    当前工作目录的“基名 (basename)”（如 "drobbins"） 
\!    当前命令在历史缓冲区中的位置 
\#    命令编号（只要您键入内容，它就会在每次提示时累加） 
\$    如果您不是超级用户 (root)，则插入一个 "$"；如果您是超级用户，则显示一个 "#" 
\xxx    插入一个用三位数 xxx（用零代替未使用的数字，如 "\007"）表示的 ASCII 字符 
\\    反斜杠 
\[    这个序列应该出现在不移动光标的字符序列（如颜色转义序列）之前。它使 bash 能够正确计算自动换行。 
\]    这个序列应该出现在非打印字符序列之后。 
```

### 彩色化 ls 命令的输出结果

默认 ls 命令的输出中都是同一种颜色，可以通过安装 GNU Coreutils 来替换 Mac 的 ls 命令的输出样式。

首先需要安装 coreutils 软件：

```shell
brew install coreutils
gdircolors --print-database > ~/.dir_colors
```

> 这里 gdircolor 的作用就是设置 ls 命令使用的环境变量 LS_COLORS。我们可以修改`~/.dir_colors`自定义文件的颜色。

然后编辑 ~/.bash_profile 文件：

```shell
vim ~/.bash_profile

if brew list | grep coreutils > /dev/null ; then
  PATH="$(brew --prefix coreutils)/libexec/gnubin:$PATH"
  alias ls='ls -F --show-control-chars --color=auto'
  eval `gdircolors -b $HOME/.dir_colors`
fi
```

### grep 高亮显示关键字

这个很简单，加上`--color`参数就可以了，为了方便使用，可以在`~/.bash_profile`配置文件中加上 alias 定义：

```shell
alias grep='grep --color'
alias egrep='egrep --color'
alias fgrep='fgrep --color'
```






