### 问题
远程 master 分支下代码被不小心提交了很多垃圾代码或项目删掉，想要回滚到以前的某一版本并删除 commit log。怎么办？

> 情景举例：老板上传了个文件，我把他删掉了。但是这个不该删除。我们可以把文件再 push 下，但是这样会留下 commit log，发现了会被 fire 的 :(。

上面场景的代码如下：

```shell
vim A.txt
git add .
git commit -a -m "add A.txt"
git push
rm A.txt
git commit -a -m "我删除了老板的东西"
git push
```

### 解决方案
push 到远程的提交默认是不能修改的，但是一定要修改不是不行：
`git push -f`

简单来说，要解决上述场景的问题，首先是需要回退版本到删除之前的状态，然后再将删除前的版本强制提交到远程仓库，这样就完成了撤销删除和消除 commit log 的目的。

实际操作如下：

```shell
# 查看 git 的提交日志，找到要回滚的版本 
git log
# 重置到删除前的版本，--soft 将之前的修改退回到暂存区
# hard 参数：修改记录都没了。soft 参数：则会保留修改记录
git reset --soft ${commit-id}
# 暂存，为了保险
git stash
# 将本地 master push 到远程版本库中， -f 强制覆盖 
git push -f
```

