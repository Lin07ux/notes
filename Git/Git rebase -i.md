`git rebase`可以合并多个 commit 为一个 commit。可以把它理解成是“重新设置基线”，将你的当前分支重新设置开始点，这个时候才能知道你当前分支与你需要比较的分支之间的差异。

下面通过一个示例来进行说明。

首先，当前的分支情况如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478523792810.png />

develop 分支的 commit log 如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478523844876.png />

develop_fixbug_imageprint 分支的 commit log 如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478523873396.png />

可以看到，develop_fixbug_imageprint 的 commit log 是和 devleop 的 commit log 一模一样。我们现在切换到 develop_fixbug_imageprint 进行一些操作。

添加一个 1.txt 文件，然后`git add .`，`git commit –m 'add 1.txt'`。
再添加一个 2.txt 文件，然后`git add .`，`git commit –m 'add 2.txt'`。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478523996294.png />

现在我们要合并代码到 develop 分支。但是这两个提交是为了解决一个问题的，所以我们想将他们合并成一个提交。此时就需要用到`git rebase`命令了。

为了合并到 develop 中，在 develop_fixbug_imageprint 分支上使用`git rebase develop`命令就立即可以知道 develop 与 develop_fixbug_imageprint 之间的差异了。

如果添加了”-i“参数，则是指交互模式。就是说你可以干预 rebase 这个事务的过程，包括设置 commit message，暂停 commit 等等。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478525584244.png />

我们设置第二个”pick 657a291 add 2.txt” 为” s 657a291 add 2.txt”这里的 s 就是 squash 命令的简写。此时跳出来了一个临时文件，最上面是两行之前的 commit message。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478526030231.png />

我们修改下这个 commit message，设置新的信息：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478526082581.png />

操作完成之后，再在 develop_fixbug_imageprint 分支上查看 commit log，如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1478526211414.png />

可以看到已经没有之前的那两个提交的历史了。之后合并该分支到 develop 上就不会出现杂乱的 commit log 了。

> 那两个提交并没有被删除掉，运行`git gc`之后才会被删除掉。

rebase 需要基于一个分支来设置你当前的分支的基线，这基线就是当前分支的开始时间轴向后移动到最新的跟踪分支的最后面，这样你的当前分支就是最新的跟踪分支。这里的操作是基于文件事务处理的，所以你不用怕中间失败会影响文件的一致性。在中间的过程中你可以随时用`git rebase –abort`取消 rebase 事务。

### 转摘
1. [聊下 git rebase -i](http://www.cnblogs.com/wangiqngpei557/p/5989292.html)
2. [rebase - Git Community Book 中文版](http://gitbook.liuhui998.com/4_2.html)


