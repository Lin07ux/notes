Git Hook 是 Git 提供的一个钩子，能被特定的事件触发后调用。也就是说，当设置了特定的 Git Hook 后，只要远程仓库收到一次相应的事件(如 push、merge 等)之后，Git Hook 就能执行一次设定的 bash 脚本。类似于一个事件订阅操作。

下面展示使用 Git Hook 实现提交之后自动部署新代码的功能。

### 1. 初始化远程仓库

首先需要在服务器上初始化一个远程仓库，并且需要初始化为裸仓库，如下：

```shell
cd ~
mkdir testRepo
cd testRepo
git --bare init
```

### 2. 初始化部署目录仓库

为了能够实现自动部署，还需要在服务器上建立一个本地仓库，来拉取远程仓库的最新代码，作为部署仓库：

```shell
cd ~
mkdir testDeploy
cd testDeploy
git clone ~/testRepo # 从远程仓库 clone 源代码
```

### 3. 为远程仓库设置 Hook

配置好远程仓库和部署目录仓库之后，就可以设置远程仓库的 Hook，实现自动在部署目录仓库拉取最新代码的功能：

```shell
cd ~/testRepo/hooks
vim post-receove
```

其中，`post-receive`文件中保存的就是在有代码更新时自动执行的 shell 脚本，内如如下：

```shell
#!/bin/sh
unset GIT_DIR
DeployPath=/home/user/testDeploy # 这就是前面创建的部署仓库地址
WwwPath=/home/wwwroot/testDeploy

# 获取仓库的最新代码
cd $DeployPath
git add . -A && git stash
git pull origin master

# 下面这 2 步都是按照实际添加的 bash 脚本
fis release -Dompd $WwwPath # 使用 FIS 对前端代码进行编译
qrsync /home/user/qiniutools/config.json # 使用七牛同步工具进行同步
```

保存之后，还需为`post-receive`文件添加可执行权限：

```shell
chmod +x post-receive
```

### 4. 为本地仓库添加 remote 源

之后就可以在自己本地电脑上进行开发和推送到远程仓库了。在原有 Git 项目里面添加一条新的 remote 源，以后往这个 remote 源里面 push 代码就会自动触发上面那 bash 脚本了。

```shell
git remote add deploy user@server.ip:/home/user/testRepo
git push deploy master
```


