`switch`命令是 Git 2.23.0 中引入的新命令，用于承担`checkout`命令的分支切换操作。

### 1. 原有分支操作

Git 中原本有两个命令用来操作分支：

* `branch` 管理分支，如分支的增删改查管理；
* `checkout` 切换分支，也可以新建分支。

> 由于 Git 中分支仅仅是一个 commit id 的别名，所以`checkout`命令也可以切换到一个 commit id 上。

```shell
$ git branch         # 查看当前所在分支
$ git branch aaa     # 新建分支 aaa
$ git branch -d aaa  # 删除分支 aaa

$ git checkout aaa       # 切换到 aaa 分支
$ git checkout -b aaa    # 创建 aaa，然后切换到 aaa 分支
$ git checkout commitid  # 切换到某个 commit id
```

### 2. 新版分支操作

`switch`命令虽然是用来接替`checkout`的功能，但是`switch`只能切换到分支，不能切换到 commit id。

```shell
$ git switch aaa    # 切换到 aaa 分支
$ git switch -c aaa # 创建 aaa 分支，然后切换到 aaa 分支
```

对比如下：

 操作            |  2.23-            | 2.23+
-----------------|-------------------|--------------
 管理分支         | `git branch`      | `git branch`
 切换分支         | `git checkout`    | `git switch`
 新建+切换分支     | `git checkout -b` | `git switch -c`
 切换到 commit id | `git checkout`    | `git checkout`

### 3. 转摘

[Git 新命令 switch 和 restore](https://mp.weixin.qq.com/s/xhr-rkrd-kRQvG3vYruTTA)



