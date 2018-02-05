### 需求

有两个分支 A、B，需要将 B 分支中的部分文件合并到 A 分支中。

### 实现

使用`git merge`命令进行分支合并是通用的做法，但是`git merge`合并的时候会将两个分支的内容完全合并，如果想合并一部分肯定是不行的。

这时候`git checkout`是个合适的工具：`git checkout source_branch <path/to/file>...`。

示例如下：

```shell
$ git branch
  * A  
    B
    
$ git checkout B message.html message.css message.js other.js

$ git status
# On branch A
# Changes to be committed:
#   (use "git reset HEAD <file>..." to unstage)
#
#    new file:   message.css
#    new file:   message.html
#    new file:   message.js
#    modified:   other.js
#
```

这样就合并完成了。

**注意：在使用`git checkout`某文件到当前分支时，会将当前分支的对应文件强行覆盖
**

如果 A 分支上需要合并的文件有过更新，直接这样合并的话就会被覆盖丢失了。此时可以考虑先从 A 分支上新建一个分支 C，然后将 B 分支和 C 分支通过`git merge`合并，然后再将 A 分支和 C 分支使用这种方式来合并就行了。

> 转摘：[git小技巧--如何从其他分支merge个别文件或文件夹](https://segmentfault.com/a/1190000008360855)


