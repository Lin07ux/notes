Git 中的`remote`命令用于配置远程仓库的相关信息，如：添加远程仓库、设置远程仓库地址、设置名称、设置分支等。

### 1. 查看远程仓库信息

```shell
git remote [-v | --verbose]
```

这个命令会展示当前仓库已经配置的所有的远程仓库的基本信息，每个仓库会有两条信息，分别表示从远程仓库拉取和推送到远程仓库的 url。

比如，可能会显示如下信息：

```
origin	git@github.com:Lin07ux/notes.git (fetch)
origin	git@github.com:Lin07ux/notes.git (push)
```

这表示，当前仓库添加了一个名称叫做`origin`的远程仓库，而且这个远程仓库的拉取和推送地址是一样的。

### 2. 添加远程仓库

```shell
git remote add [-t <branch>] [-m <master>] [-f] [--[no-]tags] [--mirror=<fetch|push>] <name> <url>
```

这个命令的基本功能是为当前仓库添加一个名称为`name`、地址为`url`的远程仓库。除此之外，还可以通过一些选项来调整或增加一些功能：

- `-t <branch>` 该选项指定当前仓库和该远程仓库要建立追踪关系的分支。默认情况下，会建立远程分支中全部的`refs/remotes/<name>/`中的分支，而指定该选项后，只会追踪`branch`分支。该选项可以设置多次，从而可以设定多个分支的追踪。
- `-m <master>` 这个选项是用来将`refs/remotes/<name>/HEAD`引用指向改远程分支的`master`分支，和`git remote set-head`命令相似。
- `-f` 该选项指明，在完成远程仓库的添加之后，立即从该远程仓库中拉取最新代码，也就是会执行`git fetch <name>`。
- `--[no-]tags` 表示执行从该远程仓库拉取代码时，是否要导入远程仓库中的标签`tag`：`--tags`表示导入全部标签，`--no-tags`表示不导入任何标签。默认情况下，只会导入拉取分支中的标签。
- `--mirror=<fetch|push>` 当使用了`--mirror=fetch`选项时，会将远程仓库中的全部`refs/`中的内容直接镜像到当前仓库中，一般在裸仓库操作中会使用，因为这样设置后进行拉取时会将本地的提交都给覆盖掉。而使用`--mirror=push`时，则表示在后续推送提交到该远程仓库时，都会按照`git push --mirror`的行为进行推送。

### 3. 删除远程仓库

```shell
git remote remove <name>
# 或者
git remote rm <name>
```

这将删除当前仓库中名称为`name`的远程仓库。比如，如果要将当前仓库的`origin`远程仓库删除，可以使用如下命令：

```shell
git remote remove origin
```

删除之后，当前仓库就没有`origin`远程仓库的配置了。

### 4. 重命名远程仓库

```
git remote rename <old> <new>
```

这个命令可以将当前仓库中名称为`old`的远程仓库改名为`new`。比如，可以通过如下命令将当前仓库中的`origin`远程仓库改成`github`名称：

```shell
git remote rename origin github
```

### 5. 设置远程仓库 HEAD 指向

```shell
git remote set-head <name> (-a | --auto | -d | --delete | <branch>)
```

这个命令用于设置或删除指定远程仓库的 HEAD 的指向，也是就远程仓库在本地的默认分支。默认情况下，`refs/remotes/<name>/HEAD`应该指向的是远程仓库`name`的`origin/master`分支。

> 可以参考`git remote add`命令的`-m`选项。

该命令有如下选项：

* `-d | --delete` 表示删除`refs/remotes/<name>/HEAD`
* `-a | --auto` 表示自动设定该远程仓库的 HEAD。使用这个选项时，Git 会判断该远程仓库 HEAD 的指向，然后在设置`refs/remotes/<name>/HEAD`为相同的分支。比如，远程仓库的 HEAD 指向`next`分支，则`git remote set-head origin -a`会将`refs/remotes/origin/HEAD`设置为`refs/remotes/origin/next`。

需要注意的是，设置分支时，需要相应的分支已经存在于本地，否则需要先拉取该分支。比如，当设置`refs/remotes/origin/HEAD`为`refs/remotes/origin/master`时，如果该`master`不存在，则需要先使用`git fetch`拉取下来。

### 6. 设置追踪分支

```shell
git remote set-branches [--add] <name> <branch>...
```

这个命令用于调整或增加追踪分支。

> 可以参考`git remote add`命令的`-t`选项。

当不使用`--add`选项时，设置当前仓库和远程仓库仅有`branch`分支被追踪，如果原先已经设置了，则会被替换掉。使用`--add`选项则表示在原先追踪的基础上，再多增加一个追踪分支。

### 7. 获取远程仓库的链接

```shell
git remote get-url [--push] [--all] <name>
```

该命令用于查看远程分支的链接地址。

* `--push` 表示只查看推送 url
* `--all` 表示查看全部 url，包括推送和拉取的 url

### 8. 管理远程仓库 url

```shell
git remote set-url [--push] <name> <newurl> [<oldurl>]
git remote set-url --add [--push] <name> <newurl>
git remote set-url --delete [--push] <name> <url>
```

该命令用于管理远程仓库的推送、拉取链接。

* `--push` 设定仅操作推送链接。默认情况下，会同时修改推送和拉取链接。
* `--add` 通过新增的方式操作远程仓库链接，而不是默认的替换方式。
* `--delete` 删除远程仓库中所有匹配`url`正则的链接。

### 9. 查看远程仓库信息

```shell
git remote [-v | --verbose] show [-n] <name>...
```

该命令用于查看指定远程仓库的详细信息。

当使用了`-n`选项时，远程仓库的 heads 信息不会先通过使用`git ls-remote <name>`查询后展示，而是使用缓存的信息。

### 10. 更新远程仓库信息

```shell
git remote [-v | --verbose] update [-p | --prune] [(<group> | <remote>)...]
```

更新远程仓库的信息，如果指定了`group`或者`remote`则更新指定的远程仓库信息，否则更新默认远程仓库的信息。如果没有设置默认远程仓库，则更新全部的远程仓库的信息。

比如，在远程版本库创建了一个分支后，在本地可以使用该命令在本地创建远程追踪分支。

> Fetch updates for a named set of remotes in the repository as defined by remotes.<group>. If a named group is not specified on the command line, the configuration parameter remotes.default will be used; if remotes.default is not defined, all remotes which do not have the configuration parameter remote.<name>.skipDefaultUpdate set to true will be updated. (See git-config(1)).
>
> With --prune option, run pruning against all the remotes that are updated.

### 11. 清理远程分支中不再存在的分支

```shell
git remote prune [-n | --dry-run] <name>...
```

删除本地版本库上那些失效的远程追踪分支。如果添加了`--dry-run`分支，则仅展示哪些分支需要清理而不会执行实际的清理操作。


