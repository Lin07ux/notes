由于 Git 自身的原理特性，每一次提交的改变都会以新文件的形式存储在本地项目根目录下的`.git`中，会在`.git/objects`下面形成一个 Blob（一段二进制数据）文件记录。这意味着，即使你只改动了某个文件的一行内容，Git 也会生成一个全新的对象来存储新的文件内容。所以 Git 仓库随着时间变化会自增长，我们往往忽视了这种潜在的危险。

有两种方法能够进行瘦身：

**方法一** 压缩 Git 仓库

这种方法治标不治本，但是使用比较简单，执行这条命令就可以了：`git gc --prune=now`。

执行这条命令后，当你再次使用`du -hs`的时候会发现仓库大小有一定的变小。其实 Git 自身在可承受范围内会自动用 gc 帮你压缩打包，所以除非真的遇到 pull、push 都困难的时候，可以不用手动执行。

这个方法明显的缺点在于压缩的效果有限，且大文件还一直在之后的每次提交中，为以后埋下隐患。

**方法二** 删除 Git 提交记录中大文件，再 gc 压缩

使用这种方式稍微麻烦点，首先要查找大文件，命令如下：

```shell
git rev-list --objects --all | grep "$(git verify-pack -v .git/objects/pack/*.idx | sort -k 3 -n | tail -5 | awk '{print$1}')"
```

比如最终找到了`nspatientList1.txt`文件比较大，需要删除：

```shell
git filter-branch --force --index-filter 'git rm -rf --cached --ignore-unmatch bin/nspatientList1.txt' --prune-empty --tag-name-filter cat -- --s
```

删除之后会有大量输出，显示已经从各自历史 log 中剔除掉关于这个大文件的信息，之后可以使用 gc 命令再次压缩：

```shell
git gc --prune= now
```

然后再进行提交即可。



