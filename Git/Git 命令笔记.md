
## 基本命令
### 状态切换
可以使用以下命令使文件在三种状态间切换：

![切换状态](7xkt52.com1.z0.glb.clouddn.com/markdown/1464932592834.png)

也可以跳过中间状态切换：

![跳过中间状态的切换](7xkt52.com1.z0.glb.clouddn.com/markdown/1464932635964.png")

### 追加 Commit
如果想对刚才做的 commit 做适当修改，可以紧接着写修改的操作，然后执行命令`git commit --amend`即可将暂存区中的内容补充到最近的一次 commit 中。

如果刚才提交完没有作任何改动，直接运行此命令的话，相当于有机会重新编辑提交说明，但将要提交的文件快照和之前的一样。

如果刚才提交时忘了暂存某些修改，可以先补上暂存操作，然后再运行`--amend`提交：

```git
# 做一次提交
git commit -m 'initial commit' 

# 将漏掉的文件添加到暂存区
git add forgotten_file
# 将这个漏掉的文件追加到刚才的提交中
git commit --amend
```
上面的这三条命令最终只是产生一个提交，第二个提交命令修正了第一个的提交内容。

### 删除文件
如果只是简单地从工作目录中手工删除文件，运行`git status`时就会有`Changes not staged for commit`的提示。

要从 Git 中移除某个文件，就必须要从已跟踪文件清单中移除，然后提交。可以用以下命令完成此项工作：

`git rm <file>`

如果删除之前修改过并且已经放到暂存区域的话，则必须要用强制删除选项`-f`：

`git rm -f <file>`

如果把文件从暂存区域移除，但仍然希望保留在当前工作目录中，换句话说，仅是从跟踪清单中删除，使用`--cached`选项然后将文件添加到 .gitignore 文件中即可：

`git rm --cached <file>`


### 推送本地分支到远程
如果在本地新建了一个分支，然后推送到远程，可以使用如下的方式：

`git push origin local_branch:remote_branch`

这个操作中，`local_branch`必须为你本地存在的分支，`remote_branch`为远程分支，如果`remote_branch`不存在则会自动创建分支。

### 删除远程分支
与推送本地分支到远程类似，只需要在命令中将本地分支名称留空，即可删除远程分支。

`git push origin :remote_branch`

在这里，`local_branch`留空，则是删除远程`remote_branch`分支。

