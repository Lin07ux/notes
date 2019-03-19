Yarn 是用来代替 npm 客户端的命令行工具，它更快，更可靠，更安全。用 Yarn 后可以继续使用你所有的包。

Yarn 有如下的特点：

* **性能**：没人喜欢等着依赖包安装的过程，特别当你只需要更新一两样小东西时。 Yarn 重度缓存了依赖包，并且优化安装过程，这样安装时间比以往大大加快。
* **安全**：当你依赖的是从网上安装的包，你会希望下载下来的代码是你想要的代码。你不希望有人拦截你的请求然后更改里面的内容。用 Yarn，你可以存一个包的 checksum 来保证代码在每次安装时都是一样的。
* **离线**：Yarn 允许你从离线镜像里安装包。这可以用于命令行环境，阻止它向互联网提出请求（对安全来说也很重要）。

[中文官网](https://yarnpkg.com/zh-Hans/)

### 安装
Mac 上可以使用 Homebrew 进行安装：

```
brew update
brew install yarn
```

### 配置
**1. 镜像**

在用 Yarn 时你可以继续用 npm registry 或者任何 npm 镜像。下面就是使用淘宝的镜像：

```shell
yarn config set registry https://registry.npm.taobao.org

# 或
yarn install --registry https://registry.npm.taobao.org
```

### 常用命令
开始新项目：

```shell
yarn init
```

添加依赖包:

```shell
yarn add [package]
yarn add [package]@[version]
yarn add [package]@[tag]
```

移除依赖包：

```shell
yarn remove [package]
```

安装项目的全部依赖：

```shell
yarn

# 或
yarn install
```



