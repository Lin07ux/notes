转摘：[git checkout 命令详解](http://www.uml.org.cn/pzgl/2016082505.asp)

`git checkout`一般是用来创建/切换分支、重置文件版本等作用。之所以可以做到这样，和 Git 中的 HEAD 有关。

### HEAD 的指向

当我们在 master 分支上的时候，可以查看下 HEAD 的指向内容：

```shell
cat .git/HEAD
# 输出：
# ref: refs/heads/master
```

可以看到，HEAD 指向的是当前分支名 master，而 master 又对应了当前的最新的一次提交 ID。当我们新提交了一次修改之后，HEAD 仍旧指向 master，而 master 则指向了最新的一次 commit ID。

当我们使用`git checkout`切换了分支之后，HEAD 就指向了对应的分支。但是，使用 checkout 命令的时候，并不是每次都会改变 HEAD 指针的指向。

### HEAD 动不动，看你怎么用

checkout 的命令如下：

1. `git checkout [-q] [<commit>] [--] <paths> ...`
2. `git checkout <branch>`
3. `git checkout [-m] [ [-b | -- orphan ] <new_branch>] [start_point]`

用法 2 比用法 1 的区别在于，用法 1 包含了路径。为了避免路径和引用（或提交 ID）同名而发生冲突，可以在`<paths>`前用两个连续的连字符作为分隔。

第 1 种用法（包含`<paths>`的用法）不会改变 HEAD 头指针，主要使用于指定版本的文件覆盖工作区中对应的文件。如果省略`<commit>`，则会用暂存区的文件覆盖工作区中的文件，否则用指定提交中的文件覆盖暂存区和工作区中的对应文件。

用法 2，切换分支时会改变 HEAD 的指向，那么如果我们是检出某个 commit，同检出分支一样，会用该 commit 下的内容覆盖当前分支工作区和暂存区的内容。

对于第 2 种用法，不是检出某个具体文件的的时候，即不指定`<paths>`的时候，单纯的检出某个 commit 或分支，是会改变 HEAD 头指针的。而且只有当 HEAD 切换到某个分支的时候才可以对提交进行跟踪，否则就会进入“分离头指针”的状态。如果省略用法 2 后面的`<branch>`，则默认对工作区进行状态检查。

### checkout 更多用法

1. `git checkout -b <new_branch> [<start point>]`
    基于当前分支的 start_point 提交历史创建一个新的分支。
    
2. `git checkout --detach <branch>` 
    切换到分支的游离状态，默认为该分支下的最后一次提交 ID。
    比如当前分支为 a，然后使用`git checkout --detach master`，那么 HEAD 就会切换为 master 分支的最后一次 commit 的值，而不再是指向 master。

3. `git checkout -B <branch>`
    强制创建并切换到新的分支。
    如果当前仓库中，已经存在一个跟你新建分支同名的分支，那么使用普通的`git checkout -b <branch>`这个命令，是会报错的，且同名分支无法创建。如果使用 -B 参数，那么就可以强制创建新的分支，并会覆盖掉原来的分支。

4. `git checkout --orphan <branch>`
    基于当前所在分支新建一个干净的分支，没有任何的提交历史，但是当前分支的内容一一俱全。
    新建的分支，严格意义上说，还不是一个分支，因为 HEAD 指向的引用中没有 commit 值，只有在进行一次提交后，它才算得上真正的分支。

5. `git checkout --merge <branch>`
    这个命令适用于在切换分支的时候，将当前分支修改的内容一起打包带走，同步到切换的分支下。
    这个命令要慎用，它有两个需要注意的问题：
    * 第一，如果当前分支和切换分支间的内容不同的话，容易造成冲突。
    * 第二，切换到新分支后，当前分支修改过的内容就丢失了。

6. `git checkout -p <branch>`
    这个命令可以用来打补丁。这个命令主要用来比较两个分支间的差异内容，并提供交互式的界面来选择进一步的操作。这个命令不仅可以比较两个分支间的差异，还可以比较单个文件的差异！

