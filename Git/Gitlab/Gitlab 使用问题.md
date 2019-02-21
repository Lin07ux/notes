### 无法强制提交覆盖更新

当我们在本地做了一些错误的提交之后，想要强制覆盖服务器端的版本，此时会提示错误，类似如下信息：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1486720099693.png" width="755"/>

查看其中的提示，有`pre-receive hook declined`的提示，这说明是 Gitlab 中的配置阻止了本次强制覆盖。

查看 Gitlab 的文档，可以看到是由于一个被叫做“Protected Branches”的功能在起作用。默认情况下，分支保护功能会阻止开发者对 master 分支进行强制覆盖。

那么，对应的我们就可以临时取消这个保护功能，然后进行覆盖提交。

1. 首先我们需要在网页中打开该项目的设置页面中的“Protected branches”：

    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1486720314700.png" width="280"/>

2. 然后取消 master 分支的保护：

    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1486720347099.png" width="939"/>

3. 覆盖提交之后，建议再重新对 master 设置分支保护：

    <img src="http://cnd.qiniu.lin07ux.cn/markdown/1486720393164.png" width="929"/>

### 屏蔽掉注册功能

对于 Gitlab 11.7 版本，设置路径为：

```
Admin Area --> Setting --> General --> Sign-up restrictions --> 取消勾选 Sign-up enabled
```

### 关闭 Auto DevOps

对于 Gitlab 11.7 版本，设置路径为：

```
# 影响全部仓库的默认配置
Admin area --> Settings --> CI/CD --> Continuous Integration and Deployment --> 取消勾选 Default to Auto DevOps pipeline for all projects

# 影响单个仓库的配置
project’s Settings --> CI/CD --> Auto DevOps --> 取消勾选 Default to Auto DevOps pipeline
```

这里的`Default to Auto DevOps pipeline`，如果选中，就表示允许执行 Auto DevOps，取消则表示禁止。

> 参考：[Auto DevOps](https://docs.gitlab.com/ce/topics/autodevops/#enablingdisabling-auto-devops-at-the-instance-level-administrators-only)


