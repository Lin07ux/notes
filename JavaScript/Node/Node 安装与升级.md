### 1. 使用 n 工具

使用 npm 安装 Node 工具包`n`，然后使用`n`工具即可将 Node 升级到最新版本。

```shell
sudo npm i -g n
sudo n stable
```

`n`是一个 Node 工具包，提供了一下几个升级 Node 的命令参数：

* `n` 显示已安装的 Node 的版本
* `n latest` 安装最新版本的 Node
* `n stable` 安装最新稳定版本的 Node
* `n lts` 安装最新长期维护版(lts)的 Node

### 2. 使用 homebrew

首先，卸载之前安装的 node，如果是使用 Node 官网提供的安装包安装的，使用如下的命令进行卸载：

```shell
sudo rm -rf /usr/local/{bin/{node,npm},lib/node_modules/npm,lib/node,share/man/*/node.*}
```

然后使用 brew 进行安装：

```shell
brew install node
```

安装之后，可能在终端中直接使用 node 命令会提示找不到命令，可以执行如下的命令将安装的 node 可执行程序链接到系统 PATH 中：

```shell
brew link node
```

执行 link 命令的时候，可能会提示文件已存在的冲突，类似如下：

```shell
Linking /usr/local/Cellar/node/11.2.0...
Error: Could not symlink include/node/common.gypi
Target /usr/local/include/node/common.gypi
already exists. You may want to remove it:
  rm '/usr/local/include/node/common.gypi'

To force the link and overwrite all conflicting files:
  brew link --overwrite node

To list all files that would be deleted:
  brew link --overwrite --dry-run node
localhost:wkdir meng$ brew link --overwrite node
Linking /usr/local/Cellar/node/11.2.0...
Error: Could not symlink include/node/common.gypi
/usr/local/include/node is not writable.
```

这是由于之前安装的 node 还有些残余的文件没有删除干净，可以使用如下的命令来查看有哪些文件并进行删除：

```shell
brew link --overwrite --dry-run node
```

将这个命令输出的文件和文件夹都删除掉之后，再运行如下的命令：

```shell
brew link --overwrite node
```

> 如果提示某个文件夹没有写入的权限，建议更改其权限，或者直接将其删除。


