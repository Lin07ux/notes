Git 的`cherry-pick`用于将制定的 commit “拷贝”到当前分支中。可以一次操作一个或多个 commit。

> “拷贝”是指将制定 commit 拷贝到当前分支，而且不在原属分支中将其去除。

一个常见的场景是：有两个常见的维护版本 v2.0 和 v3.0，当在 v3.0 分支中增加了一个新的功能时，也想在 v2.0 分支添加该功能，不能直接把两个分支合并，这样会导致稳定版本混乱，这时就可以使用 cherry-pick 对已经存在的 commit 进行再次提交到 v2.0 分支中。

### 1. 基本用法

最简单的使用就是将一个或多个 commit 直接拷贝到当前分支中，语法如下：

```shell
# 拷贝一个 commit
git cherry-pick <commit_id>
# 拷贝多个 commit
git cherry-pick <commit_id> <commit_id> <commit_id>
```

当执行完 cherry-pick 之后，将会自动生成新的 commit 进行提交，也就是会有新的 commit ID。

### 2. 拷贝连续的多个 commit

如果要将多个连续的 commit 进行拷贝，可以使用类似如下的语法：

```shell
git cherry_pick <start-commit-id>…<end-commit-id>
```

这样操作的范围就是`start-commit-id`到`end-commit-id`之间所有的 commit，但是它这是一个`(左开，右闭]`的区间，也就是说，它将不会包含`start-commit-id`的 commit。

如果想要包含起始 commit，可以使用`^`符号来将起始 commit 标记成起始 commit 前的一个 commit，如下所示：

```shell
git cherry-pick <start-commit-id>^...<end-commit-id>
```

这样就变成`[左闭，右闭]`的区间。

### 3. 保留原提交信息

如果想要将原 commit 的提交信息一并拷贝过来，可以使用`-x`选项，这样拷贝后这个 commit 的提交信息(如提交者、提交时间等)和之前是一样，语法如下：

```shell
git cherry-pick -x <commit_id>
```

### 4. 冲突解决

在进行 cherry-pick 的时候，如果遇到冲突，按照正常的方式先处理冲突，然后使用如下方式继续执行即可：

```shell
git cherry-pick --continue
```

如果要终止，则可以使用如下命令：

```shell
git cherry-pick --abort
```


