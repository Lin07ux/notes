`git rebase`可以合并多个 commit 为一个 commit。可以把它理解成是“重新设置基线”，将你的当前分支重新设置开始点，这个时候才能知道你当前分支与你需要比较的分支之间的差异。

### 1. 合并分支

相对于使用`git merge`合并分支来说，用`git rebase`来合并分支的好处在于：不会产生新的`commit log`，而且提交日志也会很干净，是一条直线。但是变基之后，不会保留合并分支的原始提交记录。

首先，当前的分支情况如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1478523792810.png)

develop 分支的 commit log 如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1478523844876.png)

develop_fixbug_imageprint 分支的 commit log 如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1478523873396.png)

可以看到，develop_fixbug_imageprint 的 commit log 是和 devleop 的 commit log 一模一样。我们现在切换到 develop_fixbug_imageprint 进行一些操作。

添加一个 1.txt 文件，然后`git add .`，`git commit –m 'add 1.txt'`。
再添加一个 2.txt 文件，然后`git add .`，`git commit –m 'add 2.txt'`。

![](http://cnd.qiniu.lin07ux.cn/markdown/1478523996294.png)

现在我们要合并代码到 develop 分支。但是这两个提交是为了解决一个问题的，所以我们想将他们合并成一个提交。此时就需要用到`git rebase`命令了。

为了合并到 develop 中，在 develop_fixbug_imageprint 分支上使用`git rebase develop`命令就立即可以知道 develop 与 develop_fixbug_imageprint 之间的差异了。

如果添加了”-i“参数，则是指交互模式。就是说你可以干预 rebase 这个事务的过程，包括设置 commit message，暂停 commit 等等。

![](http://cnd.qiniu.lin07ux.cn/markdown/1478525584244.png)

我们设置第二个”pick 657a291 add 2.txt” 为” s 657a291 add 2.txt”这里的 s 就是 squash 命令的简写。此时跳出来了一个临时文件，最上面是两行之前的 commit message。

![](http://cnd.qiniu.lin07ux.cn/markdown/1478526030231.png)

我们修改下这个 commit message，设置新的信息：

![](http://cnd.qiniu.lin07ux.cn/markdown/1478526082581.png)

操作完成之后，再在 develop_fixbug_imageprint 分支上查看 commit log，如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1478526211414.png)

可以看到已经没有之前的那两个提交的历史了。之后合并该分支到 develop 上就不会出现杂乱的 commit log 了。

> 那两个提交并没有被删除掉，运行`git gc`之后才会被删除掉。

rebase 需要基于一个分支来设置你当前的分支的基线，这基线就是当前分支的开始时间轴向后移动到最新的跟踪分支的最后面，这样你的当前分支就是最新的跟踪分支。这里的操作是基于文件事务处理的，所以你不用怕中间失败会影响文件的一致性。在中间的过程中你可以随时用`git rebase –abort`取消 rebase 事务。

### 2. 合并同一分支中的 commit

为了不时的提交下开发进度，在完成一个任务的过程中，可能会提交多个 commit，而这些全部的 commmit 合并起来才是任务的完成开发，所以我们可能就需要合并这些 commit，使得提交历史更干净。

与合并分支的时候类似，我们也需要提供一个基准点作为`git rebase`合并 commit 的基础位置。这时候的基准点可以使用`HEAD^`这种方式表示，也可以直接使用某个 commit 的 hash 值。

> 需要注意的是，选择的基准点是不参与合并的，也就是说从这个基准点之后的 commit 会被进行合并处理。

首先假设我们有 3 个 commit：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494409536000.png)

我们需要将`2dfbc7e8`和`c4e858b5`合并成一个 commit，那么我们输入如下命令：

```shell
git rebase -i f1f92b
```

之后就会进入到默认的编辑器进行合并设置的操作。如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494409679865.png)

可以看到其中分为两个部分，上方未注释的部分是填写要执行的指令，而下方注释的部分则是指令的提示说明。指令部分中由前方的命令名称、`commit hash`和`commit message`组成。

常用的指令一般只有`pick`和`squash`这两个命令：

* `pick`的意思是要会执行这个 commit
* `squash`的意思是这个 commit 会被合并到前一个 commit

合并过程中，如果有冲突产生，就会临时暂停合并，并提示你处理冲突。可以使用`git status`查看到具体是哪些文件产生了冲突。

处理完成之后，使用`git add`命令添加修改，然后使用`git rebase --continue`命令继续进行合并。如果中途想要放弃合并，可以使用`git rebase --abort`命令。

如果没有冲突，或者冲突已经解决，则会出现如下的`commit message`编辑窗口：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494409947082.png)

其中，非注释部分就是两次的`commit message`，你要做的就是将这两个修改成新的`commit message`。修改完成后，输入`wq`保存并退出，再次输入`git log`查看 commit 历史信息，就会发现这两个 commit 已经合并了。

### 3. rebase 时如何保留 merge commit

如果 dev 分支上有 merge commit（合并了 test 分支），而 master 分支上有一个新的提交 e，如下所示：

```
* 31ef4ec (HEAD -> master) e
| *   b554f2d (dev) Merge branch 'test' into dev
| |\
| | * 853aaf6 (test) c
| * | 1af86fa d
| |/
| * c1b49a5 b
|/
* 763a350 a
```

现在 dev 分支想同步到 master 分支上，使用 rebase 操作后，log tree 如下所示：

```
* 8924fda (HEAD -> dev) c
* 06201b2 d
* e0c6b78 b
* 31ef4ec (master) e
| * 853aaf6 (test) c
| * c1b49a5 b
|/
* 763a350 a
```

可以看到，原本 dev 分支上与 test 分支合并的 commit 被抛弃了，同时 test 分支上的提交 d 和 c 都被 patch 到了 dev 分支上，相当于 dev 分支先对 test 分支进行了 rebase，然后再对 master 分支进行了 rebase。

这个效果可能与预期的不太一样：预期的效果是继续保留 merge commit。rebase 命令有一个`--rebase-merges`参数可以来达到这种预期：

```shell
git rebase --rebase-merges master
```

执行之后，log tree 如下：

```
*   88e6cf7 (HEAD -> dev) Merge branch 'test' into dev
|\
| * e2fae10 c
* | 65869a2 d
|/
* e61592d b
* 31ef4ec (master) e
| * 853aaf6 (test) c
| * c1b49a5 b
|/
* 763a350 a
```

可以看到，merge commit 被保留了，但 test 分支也被变基了，而且 test 分支上的 commit 还是被复制了一份。

所以，建议 merge 和 rebase 尽量使用一种，不要在一个分支中混用 merge 和 rebase 命令。

### 转摘

1. [聊下 git rebase -i](http://www.cnblogs.com/wangiqngpei557/p/5989292.html)
2. [rebase - Git Community Book 中文版](http://gitbook.liuhui998.com/4_2.html)
3. [「Git」合并多个 Commit](http://www.jianshu.com/p/964de879904a)
4. [Git在rebase时如何保留merge commit](https://mp.weixin.qq.com/s/vApS6ajWs4UcOd0U8j9c5Q)


