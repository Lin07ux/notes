Githug 是一个命令行工具，被设计来练习你的 Git 技能，它把平常可能遇到的一些场景都实例化，变成一个一个的关卡，一共有 55 个关卡，所以将他形象的形容为 *Git 游戏*。

其 Github 仓库地址是：[Githug](https://github.com/Gazler/githug)

## 安装
首先我们需要来安装这个游戏，Githug 是用 Ruby 编写的，可通过如下命令安装：

`gem install githug`

如果遇到权限问题，请加上 sudo ：

`sudo gem install githug`

安装成功后，在 Terminal 里进入你常用的目录，输入`githug`，会提示游戏目录不存在，是否要创建一个，输入`y`然后回车，就会创建一个`git_hug`的文件夹。然后我们就可以进入这个文件夹进行*游戏*了。

![Githug 初始化](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471915632399.png)


## 基本命令
Githug 提供了几个基本的命令来方便我们进行游戏操作：

* `githug help [TASK]`  # Describe available tasks or one specific task
* `githug hint`         # Get a hint for the current level. 显示当前关卡过关提示
* `githug levels`       # List all of the levels. 显示所有的关卡列表
* `githug play`         # Initialize the game. 开始本关游戏
* `githug reset`        # Reset the current level. 重启本关，或指定的关卡
* `githug test`         # Test a level from a file path

以第一关为例，演示一下这些命令的使用：

第一关的名称是：`init` ，提示是：「一个新目录 git_hug 被创建了，请把它初始化为一个空仓库」。

![第一关](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471915963038.png)

假设现在我不知道该怎么过关，我可以查看过关提示：

![githug hint](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471916020501.png)

指示是：「你可以输入 git 命令来查看 git 命令列表」。

![git](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471916077677.png)

看最后一行，原来用`git init`就可以初始化一个空仓库，初始化之后，接着输入`githug`进行过关检测：

![githug 检测过关](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471916153841.png)

此时已经在第二关了，如果想要回到第一关，就可以使用`reset`命令。需要注意的是，`githug reset`会重置当前关卡，但是如果指定了关卡的名称，则可以回到指定的关卡。比如，我们回到第一关，需要这样操作：

![githug reset](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471916601785.png)

如果不知道某一关的名称，我们就可以使用`githug levels`来查看所有关卡的名称了。

基本流程就是这样。

## 开始游戏


