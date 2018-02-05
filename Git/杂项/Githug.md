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

### #21 reset

两个文件都被添加到了`staging area`，但是只想提交其中一个。使用`git reset`可以用仓库中的版本覆盖`staging area`的版本。

`git reset`使用不同的参数`--hard/--mixed/--soft`可以使用仓库中的版本覆盖`working directory`、`staging area`和当前分支的 HEAD 中的版本。默认情况下，参数是`--mixed`。

![reset](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471928055992.png)

### #22 reset_soft

撤销上一次提交。`git reset --soft`可以修改当前分支中的 HEAD 的指向，但是不更改`working directory`、`staging area`中的内容。

![reset_soft](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471928328127.png)

### #23 checkout_file

抛弃某一次的修改，使用上次提交的版本。`git checkout <file>`会用暂存区中的版本覆盖工作区中的这个文件，从而能够丢弃当前的一些修改。

![checkout_file](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471928510973.png)

### #24 remote

查看远端仓库。

![remote](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471928588846.png)

### #25 remote_url

查看远程仓库的 url 地址。`git remote`加上参数`-v`就能查看到当前仓库的远程仓库的名称和 url 了。

![remote_url](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471928719208.png)

### #26 pull

拉取远程仓库的版本。

拉取和推送的时候，都可以指定本地和远程的分支，格式分别如下：

* 拉取：`git pull origin [remote]:[local]`
* 推送：`git push origin [local]:[remote]`

可以这样记忆：拉取的时候，是从远程拉取，所以远程分支在前面；推送的时候，是从本地推送到远程，所有本地分支在前面。

![pull](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471928965941.png)

### #27 remote_add

添加一个远端仓库。

![remote_add](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471929047027.png)

### #28 push

先将本地的 master 分支和远程的 master 分支合并(rebase)，然后再推送本地修改到远端。

![push](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471929774702.png)

### #29 diff

查看 staging area 和 working directory 中文件的差异。

> 在命令行中可能无法看出具体的更改的行数，但是可以在编辑器中找到。

![diff](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930122471.png)

### #30 blame

`git blame`可以列出文件中每行的修改人是谁

![blame](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930264382.png)

### #31 branch

创建分支。

![branch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930374407.png)

### #32 checkout

创建并切换分支。使用`git checkout -b`能够创建一个分支，并立即切换到该分支。

![checkout branch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930456189.png)

### #33 checkout_tag

切换到一个标签。

![checkout_tag](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930562543.png)

### #34 checkout_tag_over_branch

切换到一个标签上，这个标签的名称和一个分支的名称相同。

![checkout_tag_over_branch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930648494.png)

### #35 branch_at

根据一个特定的提交创建新分支。

![branch_at](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930767528.png)

### #36 delete_branch

删除分支。

![delete_branch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471930839717.png)

### #37 push_branch

推送分支到远程仓库。默认情况下，推送分支到远程仓库的时候，会推送到远程仓库的 master 分支上。如果要推送到其他的分支，需要明确的指定远程仓库的分支名。

![push_branch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931031592.png)

### #38 merge

合并分支。合并之前，我们需要先切换到最终合并到的分支上。默认情况下，git 会优先使用 Fast-forward 方式合并。

![merge](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931116748.png)

### #39 fetch

获取远端的修改，但是并不合并到当前分支。其实，`git pull`就是`git fetch`和`git merge`组成的。

![fetch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931234315.png)

### #40 rebase

`git rebase`这个命令，大概意思是从某个提交分化出两个分支，然后其中一个分支需要将另一个分支的修改合并过来，但是又不想在提交记录上留下两个分支合并的痕迹，只留下一个分支以前后顺序记录两边的修改。

`git rebase`一个分支的所有修改在另一个分支上重新应用一遍，所以在提交记录上看，会发现一个分支的所有提交在另一个分支之前或者之后。然后删除另一个被合并的分支，保持分支简洁。

`git rebase master feature`表示将 feature 上的修改在 master 上重新应用一遍。

![rebase](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931849912.png)

`git log --graph -all`，`--graph`会用图形化将提交记录显示出来，而`--all`会显示所有分支的提交记录。

对于第一个`git log --graph --all`，显示如下：

![git log](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931740241.png)

对应第而二个`git log --graph -all`，可以发现只保留了一个分支，看起来简洁了很多。

![git log](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931817970.png)

在使用此命令的时候，需要非常注意的是，不要 rebase 哪些已经推送到公共库的更新，因为此操作是重新应用修改，所以公共库的更新可能已经被其他协作者所同步，如果再次 rebase 这些修改，将可能导致冲突。

### #41 repack

项目时间长了，git 仓库会慢慢变大，可以将版本库未打包的松散对象打包来优化。

![repack](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471931936758.png)

### #42 cherry-pick

应用另一个分支上的某一个提交的修改到当前分支上。一般这个会用在需要摘引另一个分支的部分有效提交的时候。

首先，找到我们想要的那个提交，记录下它的 hash 值，然后使用`git cherry-pick`将其摘到这个提交。

> 在查找对应提交的 hash 值的时候，除了可以使用`--all`之外，还可以指定分支，比如`git log new-feature -p README.md`。

![cherry-pick](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471932224353.png)

### #43 grep

`git grep`支持各种条件搜索及正则表达式，平时用的不多，但功能强大。

![grep](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471932398420.png)

### #44 rename_commit

修改提交信息。查看 log(`git log --oneline`)，可以看到中间的一个提交的信息出现了拼写错误：

![git log --oneline](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471933397350.png)

重命名提交。当涉及提交修改时，应该想到`git rebase -i`命令，它接受可以一个参数（提交的哈希值），它将罗列出此提交之后的所有提交，然后可以对各个提交做对应的操作。`-i`参数的意义就是`interactive`交互。

首先，使用`git rebase -i HEAD~2`，以便能修改倒数第二次的提交。此时，会出现编辑框。修改第一行中的`pick`为`reword`或者`r`，然后保存退出。

![reword](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471933973910.png)

上一步退出之后，会立即有打开另一个编辑框，此时修改其中的 commit message 信息，改成拼写粗错误：

![修改信息](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471934035350.png)

改正后，保存退出，此时就可以验证通过了：

![rename_commit](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471933835247.png)

### #45 squash

合并提交历史。当我们小步的提交太多的记录之后，以后的维护工作会增加。我们可以将一些相关性较高的提交合并在一起，从而简化提交历史。

首先，查看提交历史：

![git log](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471934212103.png)

可以看到，它提示我们将最后三个提交合并到第二个提交`Adding README`中，我们可以使用上一关的`git rebase -i`命令，将第一行保持不变，后面的三行将`pick`都改成`s`或`squash`，意思是使用这个 commit，但将它合并到前一个 commit 中去。：

![git rebase](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471934344309.png)

保存退出，会提示我们编辑 commit message，再次保存退出后，查看一下提交记录，可以看到一件合并了：

![squash](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471934469229.png)

### #46 merge_squash

在 merge 特性分支时，把所有的新提交合并成一个。

首先，我们可以查看下 master 和 long-feature-branch 两个分支的提交历史：

![git log](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471934723499.png)

然后使用`git merge [branch] --squash`命令来将 long-feature-branch 合并成当前分支的一次修改，最后提交到当前分支即可：

![merge_squash](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471934820770.png)

### #47 reorder

提交顺序错乱时，也可以使用`git rebase -i`进行调整。

先看看 Log，最后两个提交颠倒了位置，然后执行`git rebase -i HEAD~2`，将两行`pick xxx`代码交换位置即可。

![reorder](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935004216.png)

### #48 bisect

代码中不知道什么时候引入了 bug，不过没关系，我们有自动化测试。我们可以不断手工 checkout 到某个 commit，结合二分法查找快速定位到引入 bug 的那一个 commit。不过这种纯手工重复的事情，已经包含在 git 的命令中了，就是 bisect。

通过查看`githug hint`的提示信息，可以看到，他提示我们使用`git bisect`，先定义一个正常的起始点，一个不正常的提交点，然后在执行`git bisect run make test`即可。

![githug hint](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935360022.png)

通过`git help bisect`可以看到有这样的使用方法：

![git help bisect](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935432619.png)

我们知道 HEAD 的代码是有问题的，而第一个 commit 的代码是没问题的。通过`git log`获得第一个 commit 的 Hash，就可以执行 bisect 命令：

![git bisect](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935603938.png)

然后，就可以进行测试查找了：

![查找](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935696737.png)

这样，就能找到错误的地方，然后即可通过这一关：

![githug](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935737473.png)

### #49 stage_lines

开发了一个特性没提交，接着又开发了另一个特性。作为一个自律的程序员，应该是要分两次提交的，如果修改的是不同的文件，那可以轻松地通过 add 不同的文件进行两次提交。但这次好巧不巧的是居然修改了同一个文件，怎么办？看看提示：

![githug hint](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471935870120.png)

原来`git add`的最小粒度不是「文件」，而是`hunk`（代码块）。`git help add`然后查找 `hunk`：

![git help add](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471936520657.png)

执行如下命令：

![git add feature.rb -p](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471936745470.png)

Git 会让我们有机会选择对每一个 hunk 做什么样的操作。这里修改同一个位置，在一个 hunk 里，根据提示我们还要输入 e 手工编辑 hunk。

![编辑 hunk](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471936783958.png)

将第 5 行删除，保存退出，再看当前状态：

![对比状态](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471936828477.png)

这样就达到了目的了：

![githug](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471936894720.png)

### #50 find_old_branch

正在特性分支上开发一个功能，被叫去修了一个紧急的 bug，修完后发现：那个特性分支叫啥？忘记了！这种情况说明分支命名太没有规律，或者分支太多，不然可以通过 git branch 看一下，也能很快找到特性分支。

提示中说可以使用`git reflog`来查看曾经使用的命令。那么我们就可以使用这个命令来找到之前的分支。下面图中，根据`get reflog`的输出，可以看出其第二行就显示了我们之前工作的特性分支。

![find_old_branch](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471937176321.png)

### #51 revert

有时代码 push 到远程仓库后发现某一块代码有问题，我们可以通过 revert 命令将特定 commit 完全恢复。

首先我们要找到需要 revert 的 commit 的 hash，然后就可以 revert 到上面去：

![revert](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471937497970.png)

使用 revert 的时候，也会进入编辑提交信息的编辑器，输入提交信息，保存退出即可。
![编辑信息](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471937426139.png)

### #52 restore

刚刚把最新的一次提交给毫无保留的扔掉，马上就改了主意，怎么办？只要进行 git 版本控制的一切都能找得回来。

被我们抛弃的那个 commit 依旧存在，只是不被任何分支索引到。那么我们还能通过 git reflog 找到它的代号。找到它的 Hash 后就可以通过 cherry-pick 将它找回来。

![restore](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471937690263.png)

### #53 conflict

冲突合并。当合并的时候，可能会出现冲突。此时我们需要先解决冲突，然后重新添加冲突的文件并提交到当前分支，才能完成合并。

![conflict](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471937977010.png)

处理冲突的时候，可以用任何编辑器将 git 提示冲突的地方处理下即可。
![处理冲突后的内容](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471937944986.png)

### #54 submodule

submodule 是 Git 组织大型项目的一种方式，通常可把第三方依赖作为 submodule 包含进来，这个 submodule 本身也是一个独立的 Git 项目。

![submodule](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471938646041.png)

### #55 contribute

最后这一关并非测试使用 GitHub 的能力，而是期望大家贡献代码，包括增加更多关卡，修复 Bug 或者完善文档。


## 转摘
1. [闯过这 54 关，点亮你的 Git 技能树](https://segmentfault.com/a/1190000004222489)

2. [闯过这 54 关，点亮你的 Git 技能树（一）](https://segmentfault.com/a/1190000004234194)

3. [闯过这 54 关，点亮你的 Git 技能树（二）](https://segmentfault.com/a/1190000005123830)

4. [闯过这 54 关，点亮你的 Git 技能树（三）](https://segmentfault.com/a/1190000005160940)

5. [闯过这 54 关，点亮你的 Git 技能树（四）](https://segmentfault.com/a/1190000005342274)

6. [闯过这 54 关，点亮你的 Git 技能树（五）](https://segmentfault.com/a/1190000006214703)

