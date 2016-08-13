转载：[git的reset和checkout的区别](https://segmentfault.com/a/1190000006185954)

在讲 git 的`reset`和`checkout`的区别之前，不得不说说 HEAD、Index、Working Directory 三个区域。


## HEAD、Index、Working Directory
Git 里有三个区域很重要，Git 的所有操作就是对这三个区域的状态（或内容）的操作。

1. `HEAD`  指向最近一次 commit 里的所有 snapshot
2. `Index` 缓存区域，只有 Index 区域里的东西才可以被 commit
3. `Working Directory`  用户操作区域

下图解释了这三个区域的状态的变化过程：

![Git 三个区域的变化](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471078032695.png)

下面对每个状态的转换做一个解释。理解了这些变化就能够对`git reset`和`git checkout`命令的效果有更直观的认识了。

### 初始状态
当你 checkout 分支的时候，git 做了这么三件事情

* 将 HEAD 指向那个分支的最后一次 commit
* 将 HEAD 指向的 commit 里所有文件的 snapshot 替换掉 Index 区域里原来的内容
* 将 Index 区域里的内容填充到 Working Directory 里

所以你可以发现，HEAD、Index、Working Directory 这个时候里的内容都是一模一样的。

> 注意：一般会误解为，Index 中的内容是空的，只有`git add`后才会有东西。实际上不是，Index 里一直是有东西的。

### Changed
如果你在 Working Directory 里修改了文件，git 会发现 Working Directory 里的内容和 Index 区域里的内容不一致了。这个时候`git status`的结果是：

```
# Changes not staged for commit:
```

### Staged
一个文件仅仅 changed 是不能被 commit 的，Git 要求只能提交 Index 里的东西。所以需要先`git add`变化的文件。这个命令的意思是，把 Changed 的文件的内容同步到 Index 区域里。这样 Working Directory 和 Index 区域的内容就一致了。这个过程被称之为`stage`。

这个时候`git status`的结果是：

```
# Changes to be committed:
```

### Committed
最后，你就可以提交了：`git commit`。这样，就把 HEAD 的状态和 Index 以及 Working Directory 形成一致了。


## reset
reset 是用来修改提交历史的。想象这种情况，如果你在 2 天前提交了一个东西，突然发现这次提交是有问题的。这个时候你有两个选择，要么使用`git revert`（推荐），要么使用`git reset`。

![reset 和 revert 的区别](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471078479277.png)

上图可以看到`git reset`是会修改版本历史的，他会丢弃掉一些版本历史。而`git revert`是根据那个 commit 逆向生成一个新的 commit，版本历史是不会被破坏的，只会讲那次 commit 的工作逆向去掉。

由于`git reset`会丢失版本历史，所有如果 commit 已经被 push 到远程仓库上了，也就意味着其他开发人员就可能基于这个 commit 形成了新的 commit，这时你去 reset，就会造成其他开发人员的提交历史莫名其妙的丢失，或者其他灾难性的后果。因此，**一旦 commit 已经被 push 到远程仓库，那么是坚决不允许去 reset 它的**。

下面来看看 reset 的操作效果。

### 不带文件参数的 reset
在不带文件参数的时候，reset 会修改当前分之的 HEAD 指向。

reset 实际上有 3 个步骤，根据不同的参数可以决定执行到哪个步骤(`--soft`，`--mixed`，`--hard`)：

1. 改变 HEAD 所指向的 commit(`--soft`)
2. 然后将 Index 区域更新为 HEAD 所指向的 commit 里包含的内容(`--mixed`)
3. 再将 Working Directory 区域更新为 HEAD 所指向的 commit 里包含的内容(`--hard`)

> 注意：`--mixed`是默认参数，也就是说执行 reset 的时候不给就认为是`--mixed`。

下图说明了三种形式的`git reset`所产生的不同效果。target 代表想要将 git 指向到哪个 commit。

![git reset 效果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471081172998.png)

### 带文件参数的reset
如果带上文件参数，那么会有如下效果：

* HEAD 不会动，这是最重要的一点。
* 将那个 commit 的 snapshot 里的那个文件放到 Index 区域中

需要注意的是带文件参数的`git reset`没有`--hard`、`--soft`这两个参数。只有`--mixed`参数。也就是带有文件参数的时候，`git reset`就会将 Index 区域中的内容和指定的 commit 中该文件的内容同步。


### unstage
如果带文件参数的 reset 命令，指定的恢复版本就是当前的 HEAD，那么其效果就是 stage 的反向操作：unstage。

下面的两个命令的效果是相同的，都是将`file.txt`文件恢复到最近一次的 commit 版本，也即是 HEAD 版本：

```git
git reset file.txt
git reset --mixed HEAD file.txt
```

### 恢复到历史版本
如果将一个文件先恢复到某个版本，然后就直接 commit，那么就相当于把该文件恢复到了指定的历史版本。

下面这个例子的意思在于：把某个文件的历史版本恢复到 Index 区域里，然后直接 commit，这样就等于把当前 HEAD 中这个文件恢复到历史版本了，这样你都不需要去改动 Working Directory 了。

```git
git reset eb43bf file.txt
git commit -m '恢复 file.txt 到历史版本'
```


## checkout
前面讲到 checkout 是会修改 HEAD 的指向，变更 Index 区域里的内容，修改 Working Directory 里的内容。这看上去很像`git reset --hard`，但和`git reset --hard`相比有两个重要的差别：

1. reset 会把 working directory 里的所有内容都更新掉，而 checkout 不会去修改你在Working Directory 里修改过的文件
2. reset 把 branch 的最新的 commit 移动到指定的地方，而 checkout 则把 HEAD 移动到另一个分支。

第二个区别可能有点难以理解，举例来说：假设你有两个分支 master 和 develop，这两个分支指向不一样的 commit，我们现在在 develop 分支上（HEAD 指向的地方）。此时：如果我们`git reset master`，那么 develop 就会指向 master 所指向的那个 commit；如果我们`git checkout master`，那么 develop 不会动，只有 HEAD 会移动。HEAD 会指向 master。看图：

![reset 和 checkout 的区别](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471082287874.png)

当执行`git checkout [branch] file`时，checkout 做了这些事情：

* 更新了 Index 区域里 file 文件的内容
* 更新了 working directory 里 file 文件的内容

总结下 checkout 和 reset 的区别：

![checkout 和 reset 的区别](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471082517793.png)



