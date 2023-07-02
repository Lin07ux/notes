git revert 命令可以用来对指定的提交进行回退。

### revert merge commit

> 转摘：[当你决定去 revert 一个merge commit](https://juejin.cn/post/6844903590545506312)

一些情况下，因为不能`fast-forward`合并而导致合并点有两个父级，如下图所示：

![](https://cnd.qiniu.lin07ux.cn/markdown/da629ba58369f611f4164a9f300b191b.jpg)

此时要对这个合并进行 revert，以取消 feature 分支的提交。如果直接执行 revert 命令，会得到如下的错误：

```shell
$ git revert cae5381
error: commit cae5381823aad7c285d017e5cf7e8bc4b7b12240 is a merge but no -m option was given.
fatal: revert failed
```

提交错误提示，查看 revert 命令的`-m`选项的[官方文档](https://git-scm.com/docs/git-revert#git-revert--mparent-number)，可知：

> 通常情况下，无法 revert 一个 Merge，因为 Git 并不知道 Merge 的哪一条线应该被视为主线。而选项`-m`就是用来指定主线的 parent 的代号（从 1 开始），并允许以相对于指定的 parent 进行 revert。

可以使用`git show`命令来查看当前的 commit 的祖先有哪些：

```shell
$ git show cae5381
commit cae5381823aad7c285d017e5cf7e8bc4b7b12240
Merge: edf99ca 125cfdd
Author: ULIVZ <472590061@qq.com>
Date:   Thu Apr 12 18:27:21 2018 +0800

    Merge tag 'thumbup-feature'
```

输出中的`Merge`行就指明了当前的 parent 有哪些。parent 的顺序是有一定规则的：当前在 B 分支上，要把 A 分之合并到 B 分支，则 B 即为 parent1，A 则为 parent2。

因此，要 revert 掉合并进来的 feature 分支，就要使用`-m`来指定主线分支：

```shell
git revert cae5381 -m 1
```