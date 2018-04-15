Git stash 命令可以将当前工作区的内容暂存起来，然后重置工作区的内容为 Index 区的内容。通过该命令可以更好的进行一些开发支持。

默认情况下，直接使用`git stash`命令就可以进行暂存了，不过该命令还有更多其他相关的命令。

### 1、 git stash save

这个命令和`git stash`很像，但是这个命令有更多其他的选项：

1. 添加暂存信息：`git stash save "some message of the stash"`
2. 存储未跟踪的文件：`git stash save -u` 或者 `git stash save --include-untracked`

### 2. git stash list

每一次使用`git stash`或`git stash save`时，Git 实际上会创建一个 Git commit 对象，然后将它保存在代码库中。所以，就可以随时查看创建的暂存列表：

```shell
git stash list
```

列表中，最近暂存的排在最前面，而且每个暂存的名称都是按照`stash@{n}`顺序排列，其中`n`表示指定暂存在列表中的次序。

### 3. git stash apply

这个命令会将在暂存堆栈中最顶层(也就是最近一次暂存的) stash 拿出来应用到当前的工作区中。如果要获取一些其他的 stash，可以指定对应的 stash ID，如下：

```shell
# 应用第二个 stash
git stash apply stash@{1}
```

### 4. git stash pop

这个命令和上面的`git stash apply`很相似，不同之处在于，`git stash pop`从堆栈中取出最顶层的 stash 之后，会将其从 stash 堆栈中删除掉，而`git stash apply`则不会进行删除操作。

同样的，如果要使用指定的的 stash，可以使用其 stash ID：

```shell
# 应用第二个 stash，并从堆栈中删除
git stash pop stash@{1}
```

### 5. git stash show

这个命令显示了 stash 与当前仓库中的差异概要。如果要查看完整的差异信息，可以使用`-p`选项：

```shell
git stash show -p
```

也可以指定 stash ID 来获取特定 stash 的差异的概要：

```shell
git stash show stash@{1}
```

### 6. git stash branch <name>

这个命令使用最顶层的 stash 创建一个新的分支，然后删除该 stash（类似于`git stash pop`）。当然，也可以指定特定的 stash ID：

```shell
git stash branch new_branch_name stash@{1}
```

这在当将 stash 应用到最新版本的分支时遇到冲突的情况下，非常有用。

### 7. git stash clear

这个命令会将仓库中的所有 stash 都删除，不能恢复。

### 8. git stash drop

该命令从堆栈中删除最新的一个 stash，直接丢弃，不应用到工作区。删除后也不能恢复。

也可以指定特定的 stash ID：

```shell
git stash drop stash@{1}
```

### 转摘

[你可能不知道的关于 git-stash 的有用小技巧](https://www.tuicool.com/articles/yeiuu2V)


