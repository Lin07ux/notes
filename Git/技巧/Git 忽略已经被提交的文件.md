
### 问题

一个文件已经提交到仓库和远程仓库中，现在不想在继续追踪这个文件的变动，而且并不想从仓库中删除这个文件。

### 解决方法

如果某些文件已经被跟踪了，再放入到`.gitinore`可能并不会取消对该文件的追踪，可以用以下命令来临时忽略：
 ```shell
git update-index --assume-unchanged filename [filename....]
```

撤销用： 
```shell
git update-index --no-assume-unchanged filename [filename....]
```

这个办法可以在你本地中不在显示这些文件的变化，但是如果其他开发人员改动了这些文件，而且推送到了远程仓库，并且你从远程仓库拉取更新了，那么就会失效。

这只是一种告诉 Git 假装看不到本地这些文件的变化的方法。

`git update-index`的定义是：

> Register file contents in the working tree to the index（把工作区下的文件内容注册到索引区）

这句话暗含的意思是：`update-index`针对的是 Git 数据库里被记录的文件，而不是那些需要忽略的文件。

应用了该标识之后，Git 停止查看工作区文件可能发生的改变，所以你必须 手动 重置该标识以便 Git 知道你想要恢复对文件改变的追踪。当你工作在一个大型项目中，这在文件系统的`lstat`系统调用非常迟钝的时候会很有用。

`git update-index --assume-unchanged`的真正用法是这样的：

1. 你正在修改一个巨大的文件，你先对其`git update-index --assume-unchanged`，这样 Git 暂时不会理睬你对文件做的修改；

2. 当你的工作告一段落决定可以提交的时候，重置改标识：`git update-index --no-assume-unchanged`，于是 Git 只需要做一次更新，这是完全可以接受的了；

3. 提交＋推送。

> 参考：[git忽略已经被提交的文件](https://segmentfault.com/q/1010000000430426)

### 补充

当使用了`git add`或者`commit`一个文件/目录之后，再将这个文件/目录写入到`.gitignore`文件中，并不会阻止 Git 追踪这个文件/文件夹的变化。

此时，可以使用下面三步来解决问题：

1. 使 Git 停止对这个文件/文件夹的追踪

```shell
git rm --cached logs/xx.log
```

2. 添加该文件/文件夹到`.gitignore`文件中

3. 将上面操作引起的变化`commit`到仓库

经过上面三步之后，Git 就不会再继续追踪该文件/文件夹的变化了。而且本地仓库中还会存在这个文件，但是提交更新到远程仓库后，远程仓库将会删除该文件了。


