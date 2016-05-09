## 安装
首先在[官网](https://nodejs.org/en/#download)下载相应的安装包。

然后运行下载的安装包，安装 Node 和 NPM。安装的目录默认是`/usr/local/bin/`。

安装好之后，确保`/usr/local/bin`目录在系统的`PATH`中，就可以在命令行中使用`node --version`和`npm --version`来查看相应的版本了。

## 配置
### Node 配置

### NPM 配置
安装好 Node 和 NPM 之后，可以通过`npm config ls -l`来查看所有的 NPM 默认配置。并且可以通过`npm config set <property> <value>`的方式来修改配置。

比如，由于官方的仓库加载模块太慢，我们可以通过下面的命令修改 npm 的默认仓库：

`npm config set registry https://registry.npm.taobao.org`

## NPM 操作
### 初始化
`npm init`命令初始化当前目录，并在当前目录建立一个`package.json`文件，包含项目的一些基本信息。

### 搜索模块
`npm search <module>`搜索指定的模块。

> 如果修改了 npm 的默认仓库，使用搜索命令的时候，可能会出错。

### 安装模块
`npm install <module> [-g] [--save | --save-dev]`命令安装指定的模块。

安装模块的时候，可以在模块名后面加上`@`和版本号来安装指定版本的模块，比如：

```shell
npm install vue@1.0.21   # 安装版本号为1.0.21的vue模块
npm install vue@^1.0.10  # 安装版本不低于1.0.10的vue模块
```

如果指定了 -g 选项则是全局安装。

**本地安装**

1. 将安装包放在 ./node_modules 下（运行 npm 命令时所在的目录），如果没有 node_modules 目录，会在当前执行 npm 命令的目录下生成 node_modules 目录。
2. 可以通过 require() 来引入本地安装的包。

**全局安装**

1. 将安装包放在 /usr/local 下。
2. 可以直接在命令行里使用。
3. 不能通过 require() 来引入全局安装的包。

> 注意：如果是全局安装，需要使用管理员权限才能正确安装，否则会提示权限不足。因为默认情况下，mac 下 npm 的全局路径是`/usr/local/bin`，需要管理员才能写入或修改。

> 可以使用`npm ls -g`来列出全局安装的所有模块；使用`npm ls`列出本地安装的所有模块。

`--save`和`--save-dev`选项会在安装模块的同时，写入到项目的 package.json 文件中的依赖中。

使用 install 命令还能够更新 npm：`sudo npm install npm -g`。


### 卸载模块
`npm uninstall <module>`删除指定的模块(本地或者全局模块)。

### 更新模块
`npm update <module>`更新指定的模块。


