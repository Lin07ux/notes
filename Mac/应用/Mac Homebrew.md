Homebrew 是 MacOS 中一个常用的软件安装工具。

### 一、安装 Homebrew

首先需要确保已经安装了 Homebrew。没有安装的话，可以通过下面的方式安装：

```shell
/usr/bin/ruby -e "$(curl -fsSL https://raw.github.com/mxcl/homebrew/go)"
# 或
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
```

Homebrew 安装目录为`/usr/local/etc/`，放置软件包源码的路径为`/Library/Caches/Homebrew/`。

删除 Homebrew 可以使用如下的命令：

```shell
/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/uninstall)"
```

### 二、常用命令

```shell
brew install {app}     # 安装指定 app
brew uninstall {app}   # 卸载指定 app
brew list              # 列出已安装的 app
brew update            # 更新 homebrew
brew upgrade [{app}]   # 更新所有或指定的 app
brew search {app}      # 根据指定的 app 查找相应的应用
brew info {app}        # 查看指定 app 的信息
```

### 三、增强插件 Brew Cask

原生的 brew 命令安装应用时会下载源码，然后编译生成应用，再安装应用。而 brew cask 是基于 Homebrew 的另一种安装应用的方式，其安装的是已经编译打包好的程序，下载之后就能直接安装了。

另外，cask 还能管理系统中已经安装过的应用。

```shell
brew tap caskroom/homebrew-cask
# 或  
brew install brew-cask
```

> brew tap 是安装非 Homebrew 官方源中的应用。

### 问题

1. Error: /usr/local must be writable!

    这个可能是由于系统更新之后，`/usr/local`目录的权限改变了，可以使用如下的命令来把自己的权限添加进去：`sudo chown -R $USER:admin /usr/local`。其中，`$USER`需要替换成你自己的用户名。
    
    > 参考：[Brew doctor says: “Warning: /usr/local/include isn't writable.”](http://stackoverflow.com/questions/14527521/brew-doctor-says-warning-usr-local-include-isnt-writable)



