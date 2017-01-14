## 效率软件
### Near Lock
这款软件是 Mac + iPhone 结合使用，利用蓝牙进行距离感应，能够对 Mac 电脑进行自动锁屏/开锁。当手机远离 Mac 的时候，会自动将 Mac 锁屏，当靠近 Mac 的时候，会自动将 Mac 解锁。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1480985999380.png)

[Near Lock](http://nearlock.me/)

### cheatsheet
快捷键提示。只需要长按 cmd，可弹出当前 active 的软件的快捷键。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1480990487746.png)

[cheatsheet](https://www.mediaatelier.com/CheatSheet/)


### Alfred
#### NewFile
* `nf + 空格+文件名.后缀`  在当前Finder新建文件，默认文件扩展名为‘.txt’
* `nfo + 空格+文件名.后缀` 在当前Finder新建文件，并打开文件，默认文件扩展名为‘.txt’。多个文件之间用「/」分隔。如果当前没有打开的 Finder 窗口，则默认在桌面上新建你指定的文件。

#### Terminalfinder
`ft`  可以当前 Finder 窗口路径作为默认路径打开 terminal
`tf`  打开当前 terminal 所在路径对应的文件夹

#### Recent Documents
在 Alfred 窗口中输入`recent`，即可查看最近使用过的文件或文件夹。

### oh-my-zsh
oh-my-zsh 是基于 Mac 里面的 zsh 终端开发的一套框架，能改好的改善 Mac 下终端的使用体验。

#### 安装
安装，执行下面的命令即可：

`sh -c "$(curl -fsSL https://raw.github.com/robbyrussell/oh-my-zsh/master/tools/install.sh)"`

也可以使用 Git 拷贝 oh-my-zsh 的库来自行安装。

#### 更改主题
编辑`~/.zshrc`文件，将`ZSH_THEME="ys"`这里的值 ys 改成你想要的主题即可。

> 主题文件在`~/.oh-my-zsh/themes`目录中。

显示当前使用的主题：`echo $ZSH_THEME`

#### 自动补齐
Mac 下的终端默认情况下，自动补齐是要严格匹配大小写的，而使用 Oh-my-zsh 则不严格区分大小写。

如果我们在`cd`命令后面加一个空格，然后按下`Tab`键，那么就会自动将目录下的所有子目录和文件列出来。

在这个状态下，如果继续按一下`Tab`键，就可以在这个目录和文件列表中通过光标移动并选择。

#### 插件
[](http://swiftcafe.io/2015/12/04/omz-plugin/)

[zsh-autosuggestions](https://github.com/zsh-users/zsh-autosuggestions) 命令行自动提示

#### 其他
[哦！我的Zsh！](http://hczhcz.github.io/2014/03/27/oh-my-zsh.html)


## 系统设置
### Foldery
更改文件夹的颜色。在 AppStore 中可以搜索到。

### Homebrew Cask
对 Homebrew 的增强，能够更方便的使用命令行安装各种所需的应用。

安装：`brew tap caskroom/cask`

[官网](https://caskroom.github.io/)
[Github](https://github.com/caskroom/homebrew-cask)

### Amphetamine
防休眠。可以使用 Alfred workflow 来完成快速设置自定义时长的操作。

[iTunes](https://itunes.apple.com/app/amphetamine/id937984704?mt=12)
[Workflow 下载地址](https://link.zhihu.com/?target=http%3A//www.packal.org/workflow/amphetamine-control)

### quick-look-plugins
快速预览插件。是通过 Homebrew Cask 来安装的一系列的增强 Mac OSX 预览功能的插件。

[Github](https://github.com/sindresorhus/quick-look-plugins)

安装语法：`brew cask install <package>`

可用的插件：

```
qlcolorcode
qlstephen
qlmarkdown
quicklook-json
qlprettypatch
quicklook-csv
betterzipql
qlimagesize
webpquicklook
suspicious-package
```

* QLColorCode  预览代码时有语法高亮
![QLColorCode](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468473598144.png)

* QLStephen  预览没有扩展名的文件内容
![QLStephen](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468473682574.png)

* QLMarkdown  预览 Markdown 文档
![QLMarkdown](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468473740091.png)

* QuickLookJSON  预览 JSON
![QuickLookJSON](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468473790781.png)


## 实用软件
### GIMP 图片编辑器
GIMP，PS该有的，它基本都有。

软件主页以及下载地址：[GIMP](http://www.gimp.org/)

### XMind 脑图工具
主攻脑图（思维导图），流程图也支持，另外还有日程安排计划等额外的功能。

软件主页以及下载地址[XMind 官网](http://www.xmind.net/)

### Gliffy Diagrams
并不是一个独立安装的APP，而是作为Chrome的插件，可以去Chrome的App Store下载安装，很轻量，运行速度快。

软件主页：[Gliffy Diagrams 主页](https://www.gliffy.com/)

### IINA
为 macOS 设计的现代视频播放器，比 QuickTime 支持更多的格式。

[Github](https://github.com/lhc70000/iina)


## 编程开发
### Dash 文档 API 查看
写代码的时候是不是有些API记不住，比如画椭圆该用哪个类？计算开平方用什么函数？怎么连接远程的mysql服务器检索数据？这个时候一般怎么办？问度娘？问谷歌？直接查看在线编程文档？

在国内问谷歌需要翻墙，那么涉及到另外工具的使用。查看在线文档，如果记不住入口网址怎么办？放收藏里啊，如果入口改变了呢？还是需要问搜索引擎啊！那么问题来了，度娘乱贴小广告咋办？用Dash吧，一个APP搜罗了这个世界上几乎所有的编程语言文档，而且更新速度快。

它还可以和其他的软件整合，比如，安装好 Dash 之后，可以点击"Intengrtion"选项卡中的 Alfred，即可自动安装 Alfred 工作流，然后在 Alfred 搜索框中就可以输入 dasha + 关键字 进行搜索了。

对于 Sublime Text 来说，可以安装 DashDocs 插件，然后就可以对处于选中状态，或者处于鼠标指针下的词使用`Ctrl + H`进行搜索。

软件主页以及下载地址：[Dash](https://kapeli.com/dash)

### Wireshark 网络抓包
老牌网络抓包利器，各种平台都可以玩耍。

软件主页以及下载地址：[Wireshark](https://www.wireshark.org/)

### HTTPie
HTTPie 是一个更加人性化的 HTTP 命令行客户端。提供了一些简单常用的 HTTP 命令，可以用来测试服务器等。比 Curl 更加的人性化。

[HTTPie - Github](https://github.com/jkbrzt/httpie)

Mac OSX 安装：`brew install httpie`


