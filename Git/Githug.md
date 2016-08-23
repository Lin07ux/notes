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
### #1 init
![要求](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471921872296.png)

第一关是最基本的，初始化一个空的 git 仓库：

![init](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471921921742.png)

### #2 config
配置 git 账户的名称和邮箱。可以设置为全局的，或者当前仓库的。

![config](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471922086920.png)

### #3 add
将 README 文件添加到暂存区中。

![add](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471922144521.png)

### #4 commit
将 README 文件提交到当前分支中。每次提交的时候，使用`-m`参数，填写提交说明是个好习惯。

![commit](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471922292449.png)

### #5 clone
克隆一个远程的仓库到当前文件夹。

![clone](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471922437596.png)


### #6 clone_to_folder
克隆一个远程的仓库到指定的文件夹中。

![clone to folder](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471922582812.png)

### #7 ignore
忽略所有`.swp`后缀名的文件。这里使用 vim 编辑器打开`.gitignore`，这个文件记录了 git 忽略文件的规则, 不会 vim 的同学可以用自己熟悉的编辑器。

![ignore](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923064352.png)

需要使用正则式来匹配所有的`.swp`文件：

![.gitignore](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471922814472.png)

### #8 include
除了`lib.a`文件，其他所有的`.a`后缀名的文件都忽略。和上一关的操作一样，修改 .gitignore 文件。

![include](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923202477.png)

在 .gitignore 文件中，`#`开头的是注释，`*`开头的是匹配所有具有后面字符组成的文件，`!`开头的表示不要忽略对应的文件：

![.gitignore include](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923352775.png)

### #9 status
查看所有处于 untracked 状态的文件。使用`git status`查看当前仓库的状态，可以看到红色部分就是 untracked 状态的文件。

![status](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923462751.png)

### #10 number_of_files_committed
统计没有提交的文件数量。其实就是查看处于`staged`状态的文件，图中绿色部分就是，所以个数就是2。

![number_of_files_committed](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923570993.png)

### #11 rm
有一个文件从硬盘中删除了，但是并未从 git 仓库中删除，找到它并从 git 仓库中删除。删除也是修改的一种，提交这个修改就好了。

![rm](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923752717.png)

### #12 rm_cached
将一个新文件从`staging area`中删除。按照要求，不应该直接从硬盘上删除这个文件，只是从 Git 中删除而已。

加上`--cache`可以是文件只是从`staging area`中移除，不会真正的删除物理文件，如果要连这个物理文件也一起删除，请使用`-f`选项。

![rm_cached](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471923879253.png)

### #13 stash
临时提交某个文件。这个操作在需要临时保存修改，而又不想提交的时候特别好用！而且 git 中维护了一个栈来保存，所以支持提交多次。如果需要恢复某次提交，使用`git stash apply`即可。

![stash](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471924015574.png)

### #14 rename
重命名文件。首先这个文件需要是已经是已追踪状态，才可以使用`git mv`命令，操作完成后自动处于 staging 状态。

![rename](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471924128982.png)

### #15 restructure
新建一个名为 src 的文件夹，并移动所有 .html 文件到 src 文件夹。

`git mv`后面的第二个参数可以接受文件或目录。如果是目录，则文件会直接放入目录内，可以使用正则（glob 模式）匹配所有 .html 文件。

![restructure](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471924288502.png)

### #16 log
查找并回答出最近的一次提交的 hash 值。

使用`git log`查看历史提交记录，找到最新的 commit 的 hash 值，记录下来用户回答问题。这里是按照倒叙排列的，最新的在最前面，commit 关键字后面跟着的就是这个 commit 的 hash 值

![log](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471924377980.png)

### #17 tag
为最新的 commit 打 tag。不加额外参数就是为当前 commit 记录 tag，当然可以为特定的 commit 打 tag。

![tag](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471924544036.png)

### #18 push_tags
将所有本地 tag 都推送到远端。推送时，加上`--tags`参数代表将所有的 tags 都推送到远端。

![push_tags](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471924700142.png)

### #19 commit_amend
某个文件在上次提交中遗漏了，在那次提交中补上这个文件。

> 使用`git commit --amend`会进入编辑界面修改备注信息，可以直接`:wq`保存并退出。

![commit_amend](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471926587252.png)

### #20 commit_in_future
为提交指定一个未来的时间。`git commit`可以添加`--date`参数，来为本次提交设置提交时间。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471927608489.png)

使用`git commit --date`会进入编辑界面，添加备注信息。输入备注信息后，`:x`保存并退出。

![编辑信息](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471927676996.png)


