
摘自：[《Git 教程 -- 廖雪峰》](http://www.liaoxuefeng.com/wiki/0013739516305929606dd18361248578c67b8067c8c017b000)

## 准备工作
首先需要安装 Git，并加入到系统环境变量中。这样就能方便我们使用`git`命令。

> 需要明确：所有的版本控制系统，只能跟踪文本文件的改动，如 txt 文件、网页、所有的程序代码文件等，Git 也不例外。版本控制系统可以告诉你每次的改动，比如在某一行增加了一个什么单词，在某一行删除了什么字符等等。而图片、视频、微软 Word 文档等这些二进制文件，虽然也能由版本控制系统管理，但是没有办法跟踪文件的变化，只能把二进制文件每次改动串起来，不知道到底改动了什么。

安装完成之后，需要配置一下 Git：

```shell
git config --global user.name "Your Name"
git config --global user.email "email@example.com"
```

这里的"Your Name"和"email@example.com"需要替换成你自己的名称和 email 地址。

另外，可能还需要重新生成 SSH Keys 秘钥对。在 git bash 程序中，执行下面的命令，会先生成一个 SSH Key，然后拷贝公钥到剪贴板中：

```shell
ssh-keygen -t rsa -b 4096 -C "linshengli.linux7@qq.com"
clip < ~/.ssh/id_rsa.pub
```

然后在 GitHub 中个人账户的 setting 中新建一个秘钥，并将剪贴板中的内容粘贴到相应位置，即可使用 GitHub 上的远程仓库。


## 创建版本库(repository)
版本库又名仓库，repository，可以理解成一个目录，这个目录里面的所有文件都可以被 Git 管理起来，每个文件的修改、删除，Git 都能跟踪，以便任何时刻都可以追踪历史，或者在将来某个时刻可以“还原”。

所以创建一个版本库，首先创建一个文件夹，然后用 Git 初始化这个文件夹即可：

```shell
mkdir git-learn
cd git-learn
git init
```

这样就创建好了一个本地空仓库了。在这个目录下，有一个 .git 文件夹(Windows 下是隐藏的)。
这个 .git 目录是 Git 用来跟踪管理版本库的，不要手动修改这个目录里面的文件，否则容易破坏 Git 仓库。

> 当然，也可以在含有内容的目录中创建 Git 仓库。

## 添加文件到版本库
在这个文件夹中创建一个文件之后，就可以将这个文件添加到版本库中，使其受 Git 的管理。

比如，我们创建了一个 readme.md 文件，然后可以将其添加并提交到 Git 版本库中：

```shell
git add readme.md
git commit -m "wrote a readme file"
```

这两个命令，先将文件添加到版本仓库中，然后使用 commit 命令将其提交到仓库里面，使仓库记录这个文件及其内容的变动。

`git add`命令可以一次添加多个文件，每个文件名之间使用空格分隔；也可以反复多次使用，添加不同的文件。

`git commit`命令中，选项 -m 后面输入的是本次提交的说明，可以输入任意内容。虽然可以不添加这个选项，但是强烈建议添加一个有意义的说明。

每一次的变动，都需要先用 add 将变动的文件添加到 Git 版本库中，然后用 commit 提交到版本库中。因为 Git 有一个暂存区的概念。

Git 中包含三个分区：工作区、暂存区、分支区。

- 工作区就是我们项目目录，我们对文件的增删改都是在工作区进行的；
- 当我们修改了工作区的文件之后，就需要先提交到暂存区，此时文件的更改尚未反应到版本库中；
- 当做了一阶段的任务之后，就可以统一一次性将暂存区的内容同步到当前的分支上，从而反映到版本库中了。

add 就是将文件变动添加到暂存区，commit 就是将暂存区的所有内容提交到当前分支中。
在这里，将文件添加到版本库，之所以分成 add 和 commit 两步，是因为：add 能够多次使用，添加不同的文件，而 commit 可以一次提交很多文件。

commit 的时候，可以关联对应的 issue 和关闭 issue：

* `git commit -m '#133'`  在任意位置带上`#`符号加上 issue 号码即表示关联 issue
* `git commit -m 'fix #133` 使用`fix #`加上 issue 号码表示关闭对应的 issue


## 版本状态
当我们上面建立好版本库之后，Git 就开始不断的记录我们版本库中文件及文件内容的变动。每一次变动，都会影响版本库的状态(status)。

可以使用 status 命令来查看当前版本库的状态：

`git status`

在我们上面建立一个文件，并 add 和 commit 到版本库之后，版本库的状态会显示'nothing to commit, working directory clean'。而如果我们新增一个文件、或者改动了当前版本库中文件的内容，或者删除了一个文件的时候，版本库的状态中就会显示出相应的变化。

也可以使用 diff 命令查看文件具体的修改地方：

`git diff readme.md`

这样就能查看 readme.md 文件中具体的修改，指明那里增加了行，修改了内容，删除了东西等。
如果没有指定文件名，则会显示从上次 commit 之后，到现在的所有的变动。

> 需要用`q`来退出 diff 命令的显示。

还可以使用 log 命令显示出提交的日志：

`git log`

这个命令会显示从最近到最远的提交日志，包括提交的 hash 值、提交者、提交日期，以及提交的备注。如果添加了`--pretty=oneline`选项，可以只显示 hash 值、提交备注，每次的提交信息都放在一行中。

`git log --pretty=oneline`

## 版本回退和恢复
在 Git 中，用 HEAD 表示当前版本，也就是最新的提交版本。那上一个版本就是 HEAD^，上上个版本就是 HEAD^^，可以一直添加 ^ 表示更上一级。当然，如果返回的版本太多，也可以使用 ~10 这样的方式表示，比如前面的 5 个版本就是 HEAD~5。另外，每个版本都有一个唯一的 commit id 值作为标识。

用 reset 命令和版本标识能够回退或恢复到任意的版本上去：

`git reset [--hard|--mixed|--soft] commit-id`

这里的 commit-id 可以是当前版本前面的某个版本的标识，也可以是当前版本之后的某个版本的标识，这样就分别实现了回退和恢复功能。

- --hard 将指定的历史版本的内容同步到当前分支的 HEAD、暂存区、工作区中。
- --mixed 默认值。将指定的历史版本的内容同步到当前分支的 HEAD、暂存区中，而不改变工作区的内容。
- --soft 仅仅改变当前分支的 HEAD 到指定的历史版本上。

另外，还可以指定将某个文件恢复到历史版本，而不是整个分支回退：

`git reset [--hard|--mixed] filename commit-id`

这里的 --hard 和 --mixed 和上面的作用类似。

### 回退
回退版本，可以使用要回退的版本的 commit id，也可以使用相对标识，比如 HEAD^。

要回退到前面的版本，使用 reset 命令：

`git reset --hard HEAD^`

这里，HEAD^ 就表示上一个版本，你可以根据自己的需要来回到指定的版本。回退版本之后，被回退掉的提交信息就不会显示在 log 命令的输出中了。

### 恢复
如果之后想重新恢复到被回退前的某个版本，就必须找到你想恢复的版本的 commit id。

有两种方法找到这个 id：

- 1. 如果使用 reset 命令之后，命令行窗口没有关闭，此时可以直接翻回命令行的记录，查看相关的 commit id。
- 2. 使用 reflog 命令来查找。

`git reflog`

这个命令记录了每一次的 git 命令。用这个命令可以列出之前操作时的相关 commit id 值。

找到相应版本的 commit id 之后，就可以使用 reset 命令重新回到这个版本了：

`git reset --hard 953f6cc`

注意，这里的 953f6cc 应该替换成你需要回退到的版本的 commit id 值。而且不必写完全所有的 id 值，只需要写出前面的一部分即可。


## 撤销修改
在修改后，内容变动所处的状态不同时，需要使用不同的撤销方法。

- 1. 修改尚未 add 到缓存区

此时，可以直接删除修改的内容，恢复文件之前的内容即可。

如果你修改后未 add，执行 status 命令后，会发现 git 提示你，可以使用 checkout 命令丢弃工作区的修改：

`git checkout -- <file>`

这里的 file 是指你需要撤销修改的文件的名称。
执行这个命令之后会让文件回到最近一次 git commit 或 git add 时的状态。

> 注意，这里的两个短横线 -- 不能去掉，没有这两个短横线，就变成了“切换到另一个分支”的命令。

- 2. 修改已经 add 到了暂存区

如果你已经将修改添加到了暂存区中，此时，执行 status 命令，会发现 git 提示你可以使用 reset 命令来撤销暂存区的修改：

`git reset HEAD <file>`

使用 reset HEAD 命令能够将提交到暂存区中的最近一次的修改撤销掉，重新放回到工作区，相当于只是清理了暂存区。此时，工作区中的内容还包含着最后一次的修改，这就需要使用第一种情况中的方法将工作区的文件的修改撤销。

- 3. 修改已经 commit 到了本地分支中

此时就需要使用前面提到的版本回退方法。

`git reset --hard commit-id`


## 删除文件
在 Git 中，删除也是一个修改操作，也会被 Git 记录到。在 Git 中删除文件，建议使用 git rm 命令删除，而不是用系统的 rm 命令删除：

`git rm <file>`

这个命令和 git add 是相反的操作，表示从暂存区中删除指定的文件。所以执行这个命令之后，还需要 commit，将删除操作提交到分支中。可以一次性删除多个文件，每个文件名之间使用空格分隔。

对于已经 add 到了缓存区，但是没有 commit 到分支区中的文件，是不能直接使用 git rm 来删除的，需要增加下面的两个选项中的一个：

- --cached ：保留这个文件
- --f      ：强制删除这个文件，会将暂存区中的这个文件也删除掉，删除之后的状态是待 commit。

如果直接删除了文件(比如用命令`rm <file>`删除文件)，那么 Git 知道你删除了文件，因此工作区和版本库就不一致了。这时，有两个选择：

- 确定要从版本库中删除该文件，就用命令 git rm 删掉这个文件，然后 commit 这个操作；
- 如果是删错了，就使用前面介绍的撤销操作的 checkout 命令恢复文件到最新版本：`git checkout -- <file>`

如果使用 git rm 命令删除之后，尚未 commit 到版本库，需要重新恢复，则要先恢复当前文件到暂存区，然后从暂存区恢复到工作区：

```shell
git reset HEAD -- <file>
git checkout -- <file>
```

需要注意的是：这样恢复出来的文件，是不包含最后一次 commit 之后修改的内容的。

如果删除了文件，而且也 commit 到了版本库中，需要重新恢复，则只能版本回退了。此时，随删除文件操作一起 commit 的操作都没有了，需要重新来过。

> 如果是想要移动文件(重命名)，可以使用 mv 命令：`git mv file_from file_to`。


## 远程仓库
可以自己搭建一个远程仓库。这里就使用 GitHub 来作为练习。

### 准备工作
首先，注册一个 GitHub 账号。

然后在本地用 Shell(Windows 下使用 Git Bash)，用自己的邮箱创建一对 SSH Key：

`ssh-keygen -t rsa -C "email@example.com"`

一路回车使用默认值，无需设置密码，之后就可以在用户主目录里找到 .ssh 目录，这里面有 id_rsa 和 id_rsa.pub 两个文件，分别是 SSH Key 秘钥对中的私钥和公钥。私钥不要泄露，公钥可以告诉别人。

之后，登陆 GitHub，打开 Account settings，在 SSK Keys 中，增加一个 SSH Key，填上任意 Title，在 Key 文本框中粘贴 id_rsa.pub 文件的内容(这是一个文本文件，可以用文本编辑器直接打开)。

### 添加远程仓库
在 GitHub 中创建一个新的仓库，之后就会显示相关的提示信息。根据这些提示信息操作，就可以将本地仓库和远程仓库关联起来。

使用 git remote add 命令可以将我们在本地创建的 git-learn 仓库和 GitHub 上的 git-learn 仓库联系起来：

`git remote add origin git@github.com:Lin07ux/git-learn.git`

这个命令将位于 git@github.com 服务器上的 Lin07ux/git-learn.git 仓库关联到当前目录所对应的本地目录上。添加后，远程仓库的名字就是 origin，这是 Git 的默认叫法，当然，也可以改成别的。

可以使用 remote 命令查看远程库的信息：

`git remote`

也可以加一个 -v 参数来查看详细的远程库信息：

`git remote -v`

添加远程仓库关联之后，就可以将本地的版本库推送到远程仓库中了：

`git push -u origin master`

其中，origin 就是我们添加的远程仓库的名称，matser 是我们本地的版本库分支，默认是 master 分支，也可以改成其他的分支。

如果远程仓库一开始是空的，我们第一次推送 master 分支的时候，加上 -u 参数，Git 不但会把本地的 master 分支内容推送到远程仓库中新的 master 分支，还会把本地的 master 分支和远程的 master 分支关联起来。在以后的推送或者拉取时就可以简化命令了：

`git push origin master`

### 添加多个远程仓库
有时候可能有需求，同一个项目同时要推送到多个仓库(可能不在同一个服务器上)。

实现这个需求的方法有多个，比如在项目下添加多个远程仓库，也就是执行多次`git remote add [origin-name] [url]`，对每个远程仓库分别命名，然后每一次修改之后，分别 push 到着多个仓库中。

还有另一个简单的方法：git 的一个远程库可以对应多个地址，即我能让远程库 origin 拥有多个 url 地址。

假如我们需要添加三个远程仓库的地址，可以如下操作：

```shell
# 首先先添加初始远程仓库地址
git remote add origin git@github.com:Lin07ux/gitskills.git
# 然后添加第二个、第三个仓库地址
git remote set-url --add origin git@coding.com:Lin07ux/gitskills.git
git remote set-url --add origin git@oschain.net:Lin07ux/gitskills.git
```

这样就能给仓库的 origin 添加了三个远程仓库。使用`git remote -v`就可以看到远程仓库的详情：

![添加多个远程仓库的结果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471764048297.png)

以后只要使用`git push origin master`就可以一次性 push 到 3 各库里面了(使用`git push`也可)。

其实，`git remote set-url --add origin`就是往当前 git 项目的 config 文件(路径为`.git/config`)里增加一行记录，每执行一次就会增加一行。所以说，你直接在 config 里面直接添加 url 来修改也是可以的，不必去执行 git 命令。

![config 内容](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471764092521.png)

> 虽然能够使用 git push 能同时推送到多个仓库，但是拉取的时候，只能拉取 origin 里的一个 url 地址(即`fetch-url`，如上图)，这个 fetch-url 默认为你添加的到 origin 的第一个地址。如果你想更改，只需要更改 config 文件里，那三个 url 的顺序即可，fetch-url 会自动对应排行第一的那个 url 连接。

> set-url 的用法：
> * `git remote set-url [--push] <name> <newurl> [<oldurl>]` 修改远程仓库地址
> * `git remote set-url --add <name> <newurl>` 添加远程仓库地址
> * `git remote set-url --delete <name> <url>` 删除远程仓库地址

> 参考：[git 给远程库添加多个 url 地址](http://my.oschina.net/shede333/blog/299032)

### 从远程仓库克隆
如果我们是从零开发，最好是先创建远程仓库，然后从远程仓库克隆。

假如 GitHub 仓库中存在一个 gitskills 仓库，可以使用 clone 命令克隆到本地仓库中：

`git clone git@github.com:Lin07ux/gitskills.git`

这样就在本地当前目录中创建了一个 gitskills 目录，并克隆了远程仓库中的所有内容，而且已经关联了远程仓库。


### 从远程仓库拉取
使用 pull 命令即可从远程仓库拉取内容：

`git pull [origin-name] [branch-name]`

这个命令会拉取和当前所在分支相关联的远程分支上最新的修改，然后在本地合并。

如果 git pull 的时候，提示"no tracking information"，则说明本地分支和远程分支的链接关系没有创建，用下面的命令创建链接：

`git branch --set-upstream <branch-name> <origin/branch-name>`

一般这个命令用于解决推送冲突。比如，当推送一个分支的更改到远程仓库中时，遇到了冲突，就会无法提交，此时需要使用 pull 命令将远程仓库上的相关分支上的修改拉取下来，手动解决冲突之后，再重新推送上去。

* `git pull origin master` 获取远程仓库中的 master 分支
* `git pull --all` 获取远程所有内容，包括 tag
* `git pull origin next:master` 取回 origin 仓库中的 next 分支，与本地的 master 分支合并
* `git pull origin next` 获取 origin 仓库中的 next 分支和当前分支合并

如果远程主机删除了某个分支，默认情况下，git pull 在拉取远程分支的时候，不会删除对应的本地分支。这是为了防止，由于其他人操作了远程主机，导致 git pull 不知不觉删除了本地分支。但是，你可以改变这个行为，加上参数 -p 就会在本地删除远程已经删除的分支。

```shell
git pull -p
# 等同于下面的命令
git fetch --prune origin 
git fetch -p
```

## 分支
默认情况下，Git 在初始化时就创建了一个名为 master 的主分支，我们一般会将最终的项目放在主分支上发布出去。

而如果我们需要开发一个项目的新功能，为了不影响现有的稳定版本的项目，可以在主分支之外建立其他的分支，将开发改动放在其他分支上。在开发完成之后，再合并其他分支到主分支上。

由于 Git 中的分支都是使用类似指针的东西进行标识，所以新建分支、切换分支、删除分支、合并分支都是很快速的事情。

当切换分支时，HEAD 就会移动到切换的分支上去。之后在本地对文件的修改，都是 commit 到这个分支上了，而不会影响其他分支。在某个分支上修改文件并 commit 了之后，切换回另外的分支上，这个修改并不会显示了。

### 查看所有分支
可以使用 git branch 来查看本地仓库中所有的分支，而且当前分支的名称前面会有一个星号 * 作为标记：

`git branch`

### 创建分支
创建分支使用 branch 命令：

`git branch <branch-name>`

新建一个 branch-name 分支，并且这个分支和 HEAD 指向同一个地方。也就是新建分支的起始位置就是当前分支上的当前位置。

创建之后即可切换分支：

`git checkout <branch-name>`

创建并切换分支，还有一个快捷的指令：

`git checkout -b <branch-name>`

这个命令会创建一个分支，并切换到这个新建的分支上去(即 HEAD 指向这个新建的分支)。新建的分支的名称由 <branch-name> 指定。

### 删除分支
删除分支也是使用 branch 命令，不过要加一个 -d 选项：

`git branch -d <branch-name>`

如果这个分支的内容已经合并到某个分支上了，此时就能顺利的删除这个分支了。

而如果要删除的分支的内容并没有完全 merge 到其他分支，确认要删除这个分支，就需要使用 -D 参数来删除分支：

`git branch -D <branch-name>`

如果要删除远程仓库中的分支，可以 push 上去一个空分支，或者使用 --delete 参数：

`git push origin :<branch-name>`

`git push origin --delete <branch-name>`

### 合并分支
当在分支上开发完成，确定没有问题之后，就可以使用 merge 命令将指定分支上的修改合并到当前分支上去了：

`git merge <branch-name>`

这里的 branch-name 需要指定为不是当前分支的名称。

通常，合并分支时，如果可能，Git 会用 Fast-forward 模式，这样合并会很快，但是删除分支之后，会丢掉分支的信息。
如果要强制禁用 Fast-forward 模式，需要使用 --no-ff 选项，这样 Git 就会在 merge 时生产一个新的 commit，从分支历史上就可以看出分支信息。

`git merge --no-ff -m 'merge-note' <branch-name>`

由于这样的合并需要创建一个新的 commit，所有加入 -m 参数，把 commit 描述写进去。

### 合并冲突
当我们在不同的分支中，对同一个文件的同一行中进行了修改，如果修改后的内容不同，那么在合并这两个分支的时候，就会出现冲突，此时在这个发生冲突的文件中，Git 会用 <<<<<<<、=======、>>>>>>> 三种符号标记出不同分支的内容。

此时，我们需要修改这部分内容，然后在在当前分支上重新 add 和 commit 这个文件。

### 远程分支
如果要把本地的一个分支推送到远程分支，就直接使用 push 命令即可：

`git push origin <branch-name>`

这和推送主分支的命令是一样的，只是 branch-name 需要换成你要推送到远程服务器的分支的名称。

当在另一个电脑上 clone 这个远程仓库，clone 完成之后，是看不到除 master 分支之外的其他分支的。
如果需要使用远程仓库上的其他分支，需要在本地创建分支，并和远程的相应分支关联起来：

`git checkout -b <branch-name> <origin/branch-name>`

或：

`git branch <branch-name> <origin/branch-name>`

这里，branch-name 是在新电脑上本地仓库中创建的分支的名称，origin/branch-name 是远程仓库 origin 中的分支的名称。然后在本地对 dev 分支上的修改就可以 push 到远程仓库中的 dev 分支上了。
如，在本地创建一个 dev 分支，关联远程仓库 origin 的 dev 分支：

`git checkout -b dev origin/dev`

## 保存和恢复现场
如果在开发的时候，临时有个紧急任务需要完成，但是当前分支上的任务由于没有完成，没有办法 commit 到分支中，没有办法直接新建并切换分支去解决紧急任务。

Git 提供了一个 stash 功能，可以把当前工作现场储藏起来，等以后再来恢复现场继续工作：

`git stash`

执行了这个命令之后，再使用 status 命令查看，会发现当前的工作区是干净的。此时就能放心的新建和切换分支了。

保存现场后，可以使用 stash list 命令查看当前仓库所有分支上被保存的现场：

`git stash list`

每个现场的名称都是形如 stash@{n} 的格式，其中 n 是一个整数。最近的一个现场的名称是 stash@{0}，依次类推。

在某个分支上保存的分支可以在任何的分支上进行恢复，但是建议在原本的分支上恢复，否则会扰乱其他分支的文件状态。
恢复现场有两种方式：

- 使用 stash apply 恢复，但是这样恢复之后，stash 的内容并没有被删除，需要使用 stash drop 来删除。

`git stash apply <stash@{n}>`

`git stash drop <stash@{n}>`

这两个命令分别是恢复指定的 stash@{n} 的现场的内容，删除指定的 stash@{n} 的现场内容。其中，n 是一个整数。
如果没有指定参数 <stash@{n}>，那么两者的作用是：恢复最近保存的现场，删除最近保存的现场。

- 另一种方式是使用 stash pop 命令，可以在恢复最近保存的一个现场的同时，删除这个现场：
`git stash pop`


## 标签
发布一个版本的时候，通常现在版本库中打一个便签，这样就唯一确定了打标签时刻的版本。将来无论什么时候，取某个标签的版本就是把那个打标签的时刻的历史版本取出来。所以标签也是版本库的一个快照。

Git 的标签虽然是版本库的快照，但其实它就是指向某个 commit 的指针，所以创建和删除标签都是瞬间完成的。

### 创建标签
首先，切换到要打标签的分支上，然后用命令 tag 即可打一个标签：

`git tag <tag-name> [<commit-id>] [-m 'tag-description']`

默认情况下，标签是打在当前分支的当前位置上的(最新的一次 commit)，标签的名称由 tag-name 指定。
如果指定了 commit id，则会将标签打在指定的 commit 位置处。可以就可以为忘记打标签的地方打上标签。
如果指定了 -m 参数，则可以为这个标签添加一段说明文字。

另外，还可以添加 -a 参数指定标签名，添加 -s 参数进行 PGP 签名等。

### 查看标签
可以用 tag 命令查看所有分支上的所有的标签：

`git tag`

注意：标签不是按时间顺序列出的，而是按字母排序的。

可以用 show 命令来查看标签的具体信息：

`git show <tag-name>`

这个命令会显示出指定标签的 commit id、提交者、提交日期等。

### 删除标签
如果标签打错了，也可以使用 tag 命令加上 -d 参数来删除指定标签：

`git tag -d <tag-name>`

这个命令将会删除 tag-name 指定的标签。

如果标签已经推送到了远程仓库，要删除远程标签会麻烦一些，需要先从本地删除标签，然后再从远程删除：

`git tag -d <tag-name>`

`git push origin :refs/tags/<tag-name>`

或者也可以 push 一个指定的标签的时候加上 --delete 参数：

`git push origin --delete tag <tag-name>`

### 推送标签到远程仓库
如果要推送某个标签到远程，可以使用 push 命令来进行：

`git push origin <tag-name>`

如果要一次性推送全部尚未推送到远程的本地标签，可以使用 --tags 参数：

`git push origin --tags`


## 其他操作
### 忽略特定文件
项目中的有些文件是不能或者不必放到版本库中的，此时可以在项目工作区的根目录中创建一个特殊的文件，名为 .gitignore。然后把要忽略的文件和文件夹名称填进去，Git 就会自动忽略这些文件。然后将这个 gitignore 文件添加到版本库中即可。

> 如果在创建 .gitignore 文件之前，或者已经将某个文件提交到版本库之后，再在 .gitignore 文件中添加这个文件名，那么之后这文件改动的时候，还是会检测到的。

GitHub 为我们准备了各种配置文件，我们只需要组合一下即可使用了，可以查看这里：[所有配置文件](https://github.com/github/gitignore)

### 配置别名
可以使用 Git 的 alias 命令为命令配置别名：

`git config [--global] alias.<short-name> <cli-name>`

这里的 --global 参数表明是对所有的 git 仓库生效。如果没有这个参数，则只对当前仓库有效。

比如，将 chk 配置为 checkout 的别名，将 cmt 配置为 commit 的别名，将 br 配置为 branch 的别名：

`git config --golbal alias.chk checkout`

`git config --global alias.cmt commit`

`git config --global alias.br branch`

还可以用别名代替一个命令中的多个单词，比如，用 unstage 配置为 reset HEAD 的别名：

`git config --global alias.unstage 'reset HEAD'`

这样，输入`git unstage test.py`的时候，就等于输入`git reset HEADE test.py`

甚至，还能将 lg 配置成下面的别名：

`git config --global alias.lg "log --color --graph --pretty=format:'%Cred%h%Creset -%C(yellow)%d%Creset %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit"`

### 配置文件
每个仓库中都有一个配置文件，就是 .git/config 文件。这个文件中包含着当前仓库的配置信息，包括远程仓库、分支信息、别名等。

可以直接修改这个文件中的内容，就可以对这个仓库产生影响。

### 其他命令
git diff：是查看working tree与index file的差别的。

git diff --cached：是查看index file与commit的差别的。

git diff HEAD：是查看working tree和commit的差别的。（你一定没有忘记，HEAD代表的是最近的一次commit的信息）

