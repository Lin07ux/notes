> 转摘：[Git分支的前世今生](http://www.linuxprobe.com/git-branch-history-today.html)

## 一、Git 分支

Git 分支模型在众多版本控制系统中脱颖而出，因为其非常的轻量，从而使分支的相关操作都非常迅速。

### 1.1 Git 保存数据的方式

为了真正理解 Git 处理分支的方式，需要回顾一下 Git 是如何保存数据的：Git 保存的不是文件的变化或者差异，而是一系列的不同时刻的文件快照。

在进行提交操作时，Git 会保存一个提交对象(Commit Object)，该提交对象会包含一个指向暂存内容快照的指针，还包含了提交者的名称、邮箱、提交时输入的信息以及它的父对象的指针。首次提交产生的提交对象没有福对象，普通提交操作产生的提交对象有一个父对象，而由多个分支合并产生的提交对象有多个父对象。

为了说的更加形象，假设现在有一个工作目录，里面包含了三个将要被暂存和提交的文件。暂存操作会为每个文件计算校验和(使用 SHA-1 哈希算法)，然后会把当前版本的文件快照保存到 Git 仓库中(Git 使用 blob 对象保存它们)，最终将校验和加入到暂存区域等待提交：

```shell
git add README test.rb LICENSE
git commit -m 'The initial commit of my project'
```

当使用`git commit`进行提交操作时，Git 会先计算每一个字，目录(本例中只有项目根目录)的校验和，然后再 Git 仓库中将这些校验和保存为树对象。随后，Git 便会创建一个提交对象，它除了包含上面提到的那些信息外，还包含指向这个树对象(项目根目录)的指针。如此一来，Git 就可以在需要的时候重现此次保存的快照。

现在，Git 参考过中有五个对象：三个 blob 对象(保存着文件快照)、一个树对象(记录着目录结构和 blob 对象索引)以及一个提交对象(包含着指向前述树对象的指针和所有提交信息)。如下图所示：

![首次提交对象及其树结构](http://cnd.qiniu.lin07ux.cn/markdown/1572077943463.png)

做些修改后再次提交，此时产生的提交对象就会包含一个指向上次提交对象(父对象)的指针。如下图所示：

![提交对象及其父对象](http://cnd.qiniu.lin07ux.cn/markdown/1572078016910.png)

### 1.2 Git 分支的本质

Git 分支本质上仅仅是一个指向提交对象的可变指针，每次进行提交操作时，相应的分支指针就会自动向前移动，指向最后一个提交对象。

Git 中的分支地位都一样，没有区别。Git 默认的分支名称是 master，之所以每一个 Git 仓库都有 master 分支，是因为在执行`git init`命令时，默认创建的了一个分支，并命名为 master，而且大多数人都不会去改动它。

![分支及提交对象的关系](http://cnd.qiniu.lin07ux.cn/markdown/1572078246091.png)

## 二、分支操作

Git 中的分支仅仅是一个可变的指针，其实质是包含指向对象校验和(长度为 40 的 SHA-1 值字符串)的文件，所以在对分支进行操作的时候，只需要很少量的花销，异常高效。

下面通过对分支的创建、合并、删除等操作来加深对 Git 分支的理解。

### 2.1 分支创建

Git 是怎么创建新分支的呢？很简单，只需要创建一个可以移动的新的指针即可。

比如，创建一个 testing 分支，只需要使用`git branch`命令即可：

```shell
git branch testing
```

此时，就在当前所在的提交对象上创建了一个新的指针，并命名为`testing`：

![分支创建和指向](http://cnd.qiniu.lin07ux.cn/markdown/1572078455746.png)

虽然分支很简单的就创建了，但是 Git 是怎么知道当前在哪一个分支上呢？也很简单，Git 中还有一个名为 HEAD 的特殊指针。这个指针总是指向当前所在的本地分支，或者可以将其考虑为当前分支的别名。

在上面创建分支之后，由于没有切换分支，所以还是留在 master 分支上，那么 HEAD 指针就指向的是 master 分支指针了。如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1572078619668.png)

可以使用`git log`命令来查看各个分支当前所指向的提交对象，如下：

```shell
> git log --oneline --decorate
f30ab (HEAD, master, testing) add feature #32 - ability to add new
34ac2 fixed bug #1328 - stack overflow under certain conditions
98ca9 initial commit of my project
```

可见，当前三个指针实际指向的都是校验和以`f30ab`开头的提交对象。

### 2.2 分支切换

要切换到一个已存在的分支，需要使用`git checkout`命令，如下，切换到新建的 testing 分支：

```shell
git checkout testing
```

执行完这个命令后，当前就在 testing 分支了，自然的，HEAD 指针也就指向了 testing 分支了。如下图所示：

![切换分支后 HEAD 的指向](http://cnd.qiniu.lin07ux.cn/markdown/1572078835833.png)

此时修改文件之后，再做一次提交，就会将 testing 指针指向了新的提交对象了，同时 HEAD 指针也跟随移动了。如下图所示：

![新增提交后 testing 分支的指向](http://cnd.qiniu.lin07ux.cn/markdown/1572078927432.png)

这里可以看到，虽然 testing 分支向前移动了，但是 master 分支并没有变化，依然指向之前的提交对象。此时切回 master 分支时，各个分支和 HEAD 指针的指向如下图所示：

![分支移动](http://cnd.qiniu.lin07ux.cn/markdown/1572182887162.png)

切换分支时，Git 做了两件事：一是使 HEAD 指向 master 分支，二是将工作目录恢复成 master 分支所指向的快照内容。这就可以将当前的工作区域恢复成之前的状态，在 testing 分支中进行的修改被忽略了。如果 Git 不能干净利落的完成工作目录的恢复，就会禁止切换分支。

### 2.3 分支合并

假设现在有两个分支：master 和 iss53，分别指向不同的提交对象，如下图所示：

![分支基础状态](http://cnd.qiniu.lin07ux.cn/markdown/1572184138426.png)

#### 2.3.1 Fast-forward 合并

此时需要紧急修复一个 bug，从 master 分支中新建一个 hotfix 分支，进行相关的修改操作：

```shell
> git checkout -b hotfix
Switched to a new branch 'hotfix'
> vim index.html
> git commit -a -m 'fixed the broken email address'
[hotfix 1fb7853] fixed the broken email address
 1 file changed, 2 insertions(+)
```

此时分支指向如下图所示：

![hotfix 分支](http://cnd.qiniu.lin07ux.cn/markdown/1572186329515.png)

确保修改正确之后，就可以将 hostfix 分支合并到 master 分支中。此时就可以使用`git merge`来实现：

```shell
> git checkout master
> git merge hotfix
Updating f42c576..3a0874c
Fast-forward
 index.html | 2 ++
 1 file changed, 2 insertions(+)
```

可以看到合并的提示信息中有 Fast-forward(快进)，由于当前 master 分支所指向的提交是 hotfix 分支所指向的提交的直接上游，所以 Git 只是简单的将 master 分支指针向前移动。换句话说，当试图合并两个分支时，如果顺着一个分支走下去能够到达另一个分支，那么 Git 在合并两者时，只会简单地将指针向前移动，因为这种情况下的合并操作不会存在需要解决的分支。这就叫做 Fast-forward 快进合并。

此时各个分支的指向就如下图所示了：

![Fast-forward 合并结果](http://cnd.qiniu.lin07ux.cn/markdown/1572186785403.png)

合并完成之后，不再需要 hotfix 分支了，就可以将其删除了：

```shell
> git branch -d hotfix
Deleted branch hotfix (3a0874c)
```

#### 2.3.2 三方合并

切回到 iss53 分支继续进行开发之后，也需要将其合并到 master 分支：

```shell
> git checkout master
Switched to branch 'master'
> git merge iss53
Merge made by the 'recursive' strategy.
index.html |    1 +
1 file changed, 1 insertion(+)
```

这次合并和之前合并 hotfix 分支时有一点不同。在这种情况下，要合并的分支是从一个更早的地方开始分叉出来的(diverged)，master 分支所在的提交并不是 iss53 分支所在的提交的直接祖先，Git 需要做一些额外的工作才能将两者合并到一起。此时，Git 会使用两个分支的末端所指的快照(C4 和 C5)以及两个分支的祖先提交(C2)做一个简单的三方合并。三方的标注如下所示：

![一次典型合并中所用到的三个快照](http://cnd.qiniu.lin07ux.cn/markdown/1572183854426.png)

和之前的快速推进不同的是，Git 将此次三方合并的结果做了一个新的快照，并自动创建一个新的提交指向它。这个被称作一次合并提交，它的特别之处在于它不止有一个父提交，如下图所示：

![三方合并提交](http://cnd.qiniu.lin07ux.cn/markdown/1572187367050.png)

需要指出的是，Git 会自行决定选取哪一个提交作为最优的共同祖先，并以此作为合并的基础；这和更加古老的 CVS 系统或者 Subversion （1.5 版本之前）不同，在这些古老的版本管理系统中，用户需要自己选择最佳的合并基础。 Git 的这个优势使其在合并操作上比其他系统要简单很多。

#### 2.3.3 合并冲突

有时候合并操作不会如此顺利，如果在两个不同分支中，对同一个文件的同一个部分进行了不同的修改，Git 就不能自动的将其合并，这时就需要开发者手动处理冲突之后，才能顺利完成分支合并。

假设前面在 iss53 分支和 hotfix 分支中都涉及到同一个文件的同一处，在合并的时候就会产生合并冲突：

```shell
> git merge iss53
Auto-merging index.html
CONFLICT (content): Merge conflict in index.html
Automatic merge failed; fix conflicts and then commit the result.
```

此时 Git 做了合并，但是没有自动的创建一个新的和并提交。Git 会暂停下来，等待开发者解决合并产生的冲突。可以在合并冲突的任意时刻使用`git status`命令来查看那些因包含合并冲突而处于未合并(unmerged)状态的文件：

```shell
> git status
On branch master
You have unmerged paths.
  (fix conflicts and run "git commit")

Unmerged paths:
  (use "git add <file>..." to mark resolution)

    both modified:      index.html

no changes added to commit (use "git add" and/or "git commit -a")
```

任何因包含合并冲突而有待解决的文件都会以为合并状态标识出来。Git 会在有冲高图的文件中加入标准的冲突解决标记，这样就可以打开这些饱含冲突的文件，快速找到冲突位置，从而解决冲突。出现冲突的文件会包含一些特殊区段，看起来像下面这样：

```
<<<<<<< HEAD:index.html
<div id="footer">contact : email.support@github.com</div>
=======
<div id="footer">
 please contact us at support@github.com
</div>
>>>>>>> iss53:index.html
```

这表示 HEAD 所指示的版本（也就是 master 分支所在的位置，因为在运行 merge 命令的时候已经检出到了这个分支）在这个区段的上半部分(`=======`的上半部分)，而 iss53 分支所指示的版本在`=======`的下半部分。为了解决冲突，必须选择使用由`=======`分割的两部分中的一个，或者也可以自行合并这些内容。例如，可以通过把这段内容换成下面的样子来解决冲突：

```
<div id="footer">
please contact us at email.support@github.com</div>
```

上述的冲突解决方案仅保留了其中一个分支的修改，并且`<<<<<<<`、`=======`和`>>>>>>>`这些行被完全删除了。在解决了所有文件里的冲突之后，对每个文件使用`git add`命令来将其标记为冲突已解决。 一旦暂存这些原本有冲突的文件，Git 就会将它们标记为冲突已解决。

解决完全部的冲突之后，就可以输入`git commit`来完成合并提交了。

## 三、分支管理

`git branch`命令不只是可以创建与删除分支。如果不加任何参数运行它，会得到当前所有分支的一个列表：

```shell
> git branch
  iss53
* master
  testing
```

注意 master 分支前的`*`字符：它代表现在检出的那一个分支（也就是说，当前 HEAD 指针所指向的分支）。 这意味着如果在这时候提交，master 分支将会随着新的工作向前移动。

如果需要查看每一个分支的最后一次提交，可以运行`git branch -v`命令：

```shell
> git branch -v
  iss53   93b412c fix javascript issue
* master  7a98805 Merge branch 'iss53'
  testing 782fd34 add scott to the author list in the readmes
```

`--merged`与`--no-merged`这两个有用的选项可以过滤这个列表中已经合并或尚未合并到当前分支的分支。如果要查看哪些分支已经合并到当前分支，可以运行`git branch --merged`：

```shell
> git branch --merged
  iss53
* master
```

因为之前已经合并了 iss53 分支，所以现在看到它在列表中。在这个列表中分支名字前没有`*`号的分支通常可以使用`git branch -d`删除掉。

查看所有包含未合并工作的分支，可以运行`git branch --no-merged`：

```shell
> git branch --no-merged
  testing
```

这里显示了其他分支。 因为它包含了还未合并的工作，尝试使用`git branch -d`命令删除它时会失败：

```shell
> git branch -d testing
error: The branch 'testing' is not fully merged.
If you are sure you want to delete it, run 'git branch -D testing'.
```

如果真的想要删除分支并丢掉那些工作，如同帮助信息里所指出的，可以使用`-D`选项强制删除它。


