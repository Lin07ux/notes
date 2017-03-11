## 安装
首先在[官网](https://nodejs.org/en/#download)下载相应的安装包。

然后运行下载的安装包，安装 Node 和 NPM。安装的目录默认是`/usr/local/bin/`。

安装好之后，确保`/usr/local/bin`目录在系统的`PATH`中，就可以在命令行中使用`node --version`和`npm --version`来查看相应的版本了。

## 配置

`.npmrc`做为 npm 的配置文件，可以配置例如`username`、`registry`、`email`等信息。比如：

```
save=true
save-exact=true
email=wfsovereign@outlook.com
username=wfsovereign
registry=https://registry.npm.taobao.org
```

这里配置了`save`和`save-exact`属性，作用是让我们`npm install`指令安装的依赖自动保存在`package.json`文件的`dependencies`中并且让版本号固定。

`.npmrc`文件可以定义在多个地方：

* `~/.npmrc`  用户根目录，根目录内所有的 npm 指令都会查询到该配置
* `/path/to/npm/npmrc`  npm 内建配置文件
* `./.npmrc`  当前项目根目录，用于配置特定于当前项目的配置

这几个位置的`.npmrc`文件，后面的配置会覆盖前面的配置。

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


## package.json

`package.json`是项目的配置管理文件，它定义了这个项目所需要的各个依赖模块以及项目的配置信息（名字，版本号，许可证等）。一个最基本的`package.json`必须有`name`和`version`，差不多长这样：

```json
{
	"name": "xxx",
	"version": 0.0.0
}
```

### 创建 package.json

我们可以通过`npm init`指令在当前文件夹中初始化一个`package.json`文件，其包含如下内容：

```json
{
  "name": "test",
  "version": "1.0.0",
  "description": "",
  "main": "index.js",
  "scripts": {
    "test": "echo \"Error: no test specified\" && exit 1"
  },
  "author": "wfsovereign",
  "license": "ISC",
  "devDependencies": {
  },
  "dependencies": {
  }
}
```

下面分别介绍这些常用的字段。

> 更详细的介绍，请查看 [官方文档](https://docs.npmjs.com/files/package.json)

### name、version、description

这三者分别表示项目的名称、当前版本、描述信息。这些信息是根据项目和开发情况进行调整的。一般是用于别人使用该项目的时候的可以得到基本的认知。

### keywords

`keywords`指定了在库中搜索时能够被哪些关键字搜索到，所以一般这个会多写一些项目相关的词在这里，这是一个字符串的数组。

### main

指定项目加载的入口文件，默认是根目录的`inde.js`。

### scripts

`scripts`定义了一些脚本指令的 npm 命令缩写，通过这些命令我们可以方便的启动项目、进行测试或者拿到一些钩子来做某些指令前预先做的事。

比如，定义了如下的三个命令：

```json
"scripts": {
	 "pretest": "echo \"this is pre test\" ", 
	 "test": "echo \"Error: no test specified\"",
	 "posttest": "echo \"this is post test\""
}
```

当我们执行`npm test`会得到如下输出：

```
this is pre test
test@1.0.0 test
echo “Error: no test specified”
Error: no test specified
test@1.0.0 posttest
echo “this is post test”
this is post test
```

通过如上实验，我们能够知道，`prexx`指令是一个预执行指令，`postxx`是一个后置指令，他俩都和`xx`指令强相关。

### file

`file`是一个字符串的数组，指定我们发布的包应该包含当前目录的哪些文件，这个在我们发布包的时候很有用，因为开发包里面的文件夹不是都需要发布出去的。当然一下文件是始终会被包含进去的，不论我们是否设置：

* package.json
* README
* CHANGES / CHANGELOG / HISTORY
* LICENSE / LICENCE
* NOTICE
* The file in the “main” field


## 其他

### 版本号

语义化的版本标识会经常用到一些特别的符号，来限定相应的版本，如下：

* `*` 任意版本
* `1.0.0` 安装指定的 1.0.0 版本
* `~1.0.0` 安装`>= 1.0.0 && < 1.1.0`的最新版本
* `^1.0.0` 安装`>= 1.0.0 && < 2.0.0`的最新版本



