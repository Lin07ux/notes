`resotre`命令是 Git 2.23.0 中引入的新命令，用于承担`checkout`命令的文件恢复操作。

### 1. 旧版文件恢复

Git 中文件恢复原本涉及到两个命令：`checkout`和`reset`。

```shell
$ git checkout -- aaa      # 从 staged 中恢复 aaa 文件到 worktree
$ git reset -- aaa         # 从 repo 中恢复 aaa 文件到 staged
$ git checkout -- HEAD aaa # 从 repo 中恢复 aaa 文件到 staged 和 worktree
$ git reset --hard -- aaa  # 同上
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1611033767-git-file-restore.png)

### 2. 新版文件恢复

`restore`命令专门用来恢复 staged 和 worktree 的文件：

```shell
$ git restore [--worktree] aaa        # 从 staged 中恢复 aaa 文件到 worktree
$ git restore --staged aaa            # 从 repo 中恢复 aaa 文件到 staged
$ git restore --staged --worktree aaa # 从 repo 中恢复 aaa 文件到 staged 和 worktree
$ git restore --source dev aaa        # 从指定 commit 中恢复 aaa 文件到 worktree
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1611033939-git-file-restore-new.png)

### 3. 转摘

[Git 新命令 switch 和 restore](https://mp.weixin.qq.com/s/xhr-rkrd-kRQvG3vYruTTA)

