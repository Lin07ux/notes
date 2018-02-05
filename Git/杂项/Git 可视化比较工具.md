## P4Merge

这是一个 Git 的 GUI 比较工具，免费，支持中文。界面如下：

![P4Merge](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468385877584.png)

### 下载安装

打开这个链接: [perforce-visual-merge-and-diff-tools](https://www.perforce.com/product/components/perforce-visual-merge-and-diff-tools)，点左上角的`FREE DOWNLOAD P4Merge`，找到`Perforce Clients`中的`P4Merge: Visual Merge Tool`项，在右边的第二行选择`Mac OS 10.6+ (64bit）` ，点下面橙色的`Accept and Download`，选择跳过`Skip Registration`，就会开始下载了。

下载到`P4V.dmg`文件后，双击打开，拖动`P4Merge`到`Application`文件夹上就可以完成安装了。 

### 配置

先配置 Git difftool 工具：

```shell
git config --global diff.tool p4merge
git config --global difftool.p4merge.cmd /Applications/p4merge.app/Contents/MacOS/p4merge
git config --global difftool.p4merge.cmd "/Applications/p4merge.app/Contents/Resources/launchp4merge \$LOCAL \$REMOTE"
```

> 如果直接复制官网介绍上的代码，由于短横线有问题，运行上面的代码时会提示：`error: key does not contain a section: -global`。

再配置下工具路径，否则会出现找不到命令的错误：

```shell
git config --global difftool.p4merge.path "/Applications/p4merge.app/Contents/Resources/launchp4merge"
```

配置好之后，比较Git中的代码时，敲`git difftool filepath`即可。

### 更多

P4Merge 除了有比较功能，还有合并功能。更多配置参考下面的链接：

1. [《Mac os x下git merge工具P4Merge的安装与配置》](http://blog.csdn.net/ccf0703/article/details/7661789)
2. [《Git 用户信息》](http://nieyong.github.io/wiki_ny/git%E5%9F%BA%E6%9C%AC%E9%85%8D%E7%BD%AE%E8%AE%B0%E5%BD%95.html)
3. [《Setup p4merge as a visual diff and merge tool for git》](https://gist.github.com/tony4d/3454372)

