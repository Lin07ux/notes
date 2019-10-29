当在一个分支中进行开发时，经常需要查看别的分支的文件内容，如果要切换分支，就需要先整理当前的工作目录，否则无法正常的切换分支，比较麻烦。

一个简单的方式是使用`git show`命令，并指定分支和文件名。比如，下面的命令用来查看 master 分支中的`README`文件的内容：

```shell
git show master:README
```

同样的，对于`git diff`命令，也可以指定分支和文件名，实现比对其他分支的文件的功能。比如，下面的命令用来将当前分支上的`README`文件和 master 分支上的`README`文件进行比对：

```shell
git diff master:README
```

