本文主要是根据我个人使用 Hexo 搭建 Github Pages 博客的过程撰写的，并有我个人对一些操作的理解和解释，如果不正确的地方，欢迎指出，我将尽快更改。

**建议**：创建了属于个人的博客之后，请尽可能的去使用，多写一些有价值的文章。如果你没有这个打算，或者没有时间写，那就先不要在 GitHub Pages 上建立博客！

## 0x00 基本概念

### 1. GitHub Pages

GitHub pages 原本是用于让用户能够建立一个简单的页面，用来介绍托管在 GitHub 上的项目。GitHub Pages 为每个账号提供了 300M 的免费空间。这些空间，足够我们存放一些文本文件(如静态网页)，而且 GitHub Pages 足够稳定，并完全受控于我们自己，所以用 Github Pages 来存放我们的静态博客，是非常理想的。

其实，还有很多其他的空间，也能允许我们存放静态博客，只是当前来说，GitHub 是使用最广泛的一个代码托管中心，所以我也就选择了 GitHub Pages 来托管我的博客。同时还能够顺便熟悉一些 Git 命令。

### 2. 静态博客

前面一直提及到的是静态博客，这里的**静态**，是指能被他人访问的博客文章页面其实都是基本的 HTML 页面(包含 CSS 和 JavaScript)，存放在服务器(也就是 GitHub Pages 提供的空间)中的都是已经生成好的 HTML 页面，别人访问就是直接访问这个页面，而不是在有访问的时候，服务器再实时组装解释出这个 HTML 页面。而且，如果要修改这个博客文章的内容的话，我们需要在后台编辑这个博客的 MarkDown 文件，然后再调用 Hexo 来重新生成 HTML 页面，生成好之后，再发布到我们的服务器(GitHub Pages 提供的空间)中，此时，别人再查看的时候，才能看到变化。

举个可能不太相符的例子：就如同淘宝中宝贝详情页面，店铺所有者建立一个详情页，并发布这个宝贝之后，其他人能够看到的就是淘宝后台生成好的宝贝详情了；如果店铺所有者不去修改这个页面的内容，那么这个宝贝详情页就不会变化；如果要改变宝贝详情，就必须店铺所有者在后台中进行修改，然后再发布出来。

这样说，可能会有人觉得，这样也太麻烦了，做一个小的改动，都一定要重新生成，然后重新发布一遍。确实，改动或者发布新的内容，就是这么麻烦。但是也不是没有好处：这么麻烦，就是为了能够让服务器(GitHub Pages 空间)能够更简洁，更稳定，更普适。

### 3. Markdown

Markdown 是一种简洁易用的标记性语言。其语法很简单，并且能够兼容 HTML 语法。使用Markdown 编写的文件，易修改，而且也适合直接阅读，甚至可以直接用文本文件进行阅读。在撰写文件时，使用Markdown 进行编写，就不需要像使用 HTML 那样需要写很多标签对，繁琐而且会影响阅读。编写好的 Markdown 文档，也能够转化成 HTML 文件。

我们在部署静态博客的时候，使用到的 Hexo 主要的功能就是为了将 Markdown 文件按照一些特定的模板转化成 HTML 文件。其实，其他的一些部署静态博客的工具的功能也都是这样，而各自的差别就在于其 theme 和插件的多少。

Markdown 简单易学，使用到的标记也不太多。下面推荐一个 Markdown 的基础语法教程和一个在线的 Markdown 编辑器：

Markdown 语法说明：[http://wowubuntu.com/markdown/](http://wowubuntu.com/markdown/)

Markdown 在线编辑器：[mahua.jser.me/](http://mahua.jser.me/)

另外，Markdown 编辑器推荐使用[Atom](https://atom.io/)。

## 0x01 配置开发环境

通过上面的介绍，我们知道，真正上传到 GitHub Pages 中的内容是静态的 HTML 文件(及相关的CSS、JS 等)。而要生成这些静态文件，就需要我们在本地(个人电脑)中先生成源文件，然后再使用 Hexo 工具将源文件按照一定的模板生成我们需要的静态文件。所以我们首先需要在本地搭建一个开发环境。

### 0. 所需软件

- Node.js && NPM
- Hexo
- Git
- Sublime Text / Atom

### 1. 安装 Node.js && NPM

Node.js 是目前非常流行的一个服务器端 JavaScript 运行环境，结合 Node.js 人们做出了很多有用的插件(也就是功能)，而 NPM 就是给 Node.js 安装各种插件需要用到的一个工具。关于 Node.js 我们可以不需要了解太多，如果没办法理解他的作用，那就把 Node.js 当做一个微型系统，我们可以在这个微型系统上通过 NPM 这个工具安装各种软件(就是插件)，而通过这些安装的“软件”我们就能够完成各种不同的任务。

首先，我们在 Node.js 的[官网](https://nodejs.org/download/)上下载适合我们电脑系统 Node.js 版本，并安装。

在Windows上，建议下载和安装 msi 版本，这样就能同时安装 Node.js 和 NPM 了。安装过程很简单，默认一直下一步就行，也能够更换安装的目录。安装完成之后，我们可以通过下面的步骤来判断是否安装正确：

1. 打开命令行工具；
2. 输入`node -v`。

如果输出是类似 v0.12.7 的结果，那么就是安装正常了。

### 2. 安装 Hexo

安装好 nodejs 这个“系统”之后，接下来就可以安装我们所需要的 Hexo “软件”了。

安装很简单，只需要在命令行或者终端中输入下面的命令就可以了：

```shell
npm install -g hexo-cli
```

这里使用的是全局安装，所以安装完成之后，我们就能在任何目录下直接执行 hexo 命令了。

> 在 Windows 下，如果你不能直接执行`hexo`命令，那么请先检查下，你的 NPM 全局安装路径是否在系统的 PATH 中。如果不在 PATH 中，请先添加进去。
> 
> 可以通过下面的命令来查看 NPM 的全局安装路径：
> 
> ```shell
> npm config ls
> ```
> 
> 修改全局安装路径可以使用下面的命令：
> 
> ```shell
> npm config set prefix "path"
> ```

### 3. 安装 Git

前面两步其实已经安装好了本地开发静态博客的环境了，只是我们的最终目的是将静态博客发布到 GitHub Pages 上去，所以我们还需要安装 Git 这个工具。 Git 能够将我们的文件发布到一个Git服务器的仓库中，当然也能够发布到 GitHub Pages 中去。

#### 3.1 下载并安装 Git

在[Git官网](http://git-scm.com/download/)中下载并安装 Git 。

安装完成之后，还需要进行配置，以便能够连接到我们的 GitHub 仓库。

> Windows 用户则可以直接下载并安装[GitHub for Windows](https://windows.github.com/)，安装过程中勾选上`Git Shell`，这样安装完成之后，在客户端上登录你的 GitHub 账号，就不需要再使用命令行去配置 Git 了。

#### 3.2 注册 GitHub 账号

访问 GitHub [官网](https://github.com/)，根据提示，一步步完成账号的注册。

注册 GitHub 账号需要你有一个能够正常使用的邮箱地址，因为 GitHub 的很多消息都是通过邮件进行通知的，所以一定要保证邮箱的可访问性。

整个注册过程还是比较简单的，并不需要多说。

#### 3.3 配置 Git 的 SSH keys

对于 Windows 用户，如果安装的是 GitHub for windows ，则不需要再进行这一步的操作了。

其他情况下，则可以参照 GitHub 的[官方说明](https://help.github.com/articles/generating-ssh-keys/)进行操作。另外，我之后也会尽量将这一块写下来，补充上。

### 4. 安装 Sublime Text / Atom

其实这一步就是需要准备一个自己习惯使用的编辑器而已，个人推荐的是 Sublime Text 和 Atom 。这两者都是编辑文件的利器，而且 Atom 还能预览 MarkDown 文件，对我们之后撰写 MarkDown 文件很有帮助。

当然，如果你用自己喜欢的编辑器也没问题。

## 0x02 配置 Hexo

在做好前面的准备工作之后，我们就可以开始进行真正的配置我们的博客系统了，也就是配置属于我们自己的 Hexo 工具。

这方面的操作，需要在命令行或者终端中进行命令的执行。不懂每步的命令其实也没有太多的关系的，只需要执行列出的命令就行。

### 1. 初始化 Hexo

在执行这一步之前，需要确认你已经正常安装好了 Hexo 这个 Node.js 工具包。

在初始化 Hexo 前，先在硬盘上(可以为任何位置)创建一个文件夹，可以随便命名，我这里就叫做 Hexo。创建后进入到这个文件夹中，然后再执行下面的命令，进行初始化这个文件夹为一个 Hexo 博客系统：

```shell
mkdir Hexo  # 创建文件夹
cd Hexo     # 进入文件夹

hexo init   # 初始化 Hexo
npm install # 安装一些依赖代码
```
   
> 注意，在这里如果提示不存在“hexo”命令，那么很可能你前面全局安装 Hexo 工具的目录并没有加入到系统的 Path 中。

初始化完成之后，Hexo 文件夹中的结构如下：

```
.
├── node_modules
├── public
├── scaffolds
├── scripts
├── source
|   ├── _drafts
|   └── _posts
└── themes
├── _config.yml
├── package.json
```

各个文件或文件夹的用处如下：

* `_config.yml`：网站的配置信息，我们对博客系统的很多设置都需要在这里进行。
* `package.json`：这里是关于这个博客系统的一些信息，主要有这个博客系统依赖的 Node.js 插件。上面执行的 npm install 命令就是在安装这里写明的依赖的插件。
* `public`：这里存放的是执行`hexo g`之后生成的静态文件，也就是我们最终可以部署到 GitHub Pages 空间中展现出来的文件。
* `scaffolds`：模板文件夹。当我们创建新的文章的时候，Hexo 就会使用这里的模板建立文件。
* `scripts`：脚本文件夹。脚本是扩展 Hexo 最简易的方式，在此文件夹内的 JavaScript 文件会被自动执行。
* `source`：资源文件夹。资源文件夹是存放用户资源的地方。除`_posts`文件夹之外，开头名为`_ `(下划线)的文件/文件夹和隐藏文件将会被忽略。Markdown 和 HTML 文件会被解析并存放到`public`文件夹中，而其他文件则会被直接拷贝过去。
* `theme`：主题文件夹。Hexo 会根据我们指定的主题来生成博客的静态页面。默认情况下，Hexo 使用的是`landscape`主题，我们也可以安装或修改主题。

> 更详细的内容，可以查看 Hexo 提供的[官方文档](https://hexo.io/zh-cn/docs/setup.html)。

执行上的这个初始化命令之后，其实我们就能够先启动这个本地的 Hexo 博客了。先执行下面的命令，然后打开浏览器，输入`localhost:4000`进行查看这个最基本的博客：

```shell
hexo g
hexo s
```

### 2. 基本配置

通过上面一步，我们就已经将博客系统设置成默认状态了。虽然可以正常使用，但是里面的很多东西都不是我们想要的，这就需要我们对博客进行基础信息的配置了。

使用 Sublime Text 打开 Hexo 目录下的`_config.yml`文件，将下面的属性改成你想要的内容即可：
   
**Site**

```yml
title: Lin07ux 	# 站点名，会显示在站点左上角
subtitle: 开始我的路，朝向我的梦想！  # 副标题，显示在站点左上角
description: 我的个人博客，记录我的学习。  # 给搜索引擎看的，对站点的描述，可以自定义
author: Lin07ux     # 在站点左下角可以看到
language: zh-CN     # 注明我们博客使用的语言，默认为英文，中文的话，就使用 zh-CN 
timezone: Asia/Shanghai     # 网站时区。Hexo 预设使用您电脑的时区。[时区列表](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)
```

**URL**

```yml
url: http://Lin07ux.github.io   # 这里写你的 GitHub Pages 的网址，后面会介绍到，这里你可以将 Lin07ux 替换成你自己的Github的账号名
root: /
permalink: :year/:month/:day/:title/    # 博文的链接地址
permalink_defaults:
```

暂时修改这些内容即可，其他的属性，可以参考 Hexo [官方文档](https://hexo.io/zh-cn/docs/configuration.html)进行修改。

### 3. 安装 Hexo 主题

在上面初始化之后，如果你已经启动过本地博客进行查看，你会发现其实整个博客系统已经能够正常工作了，而且还有一个很不错的展现样式(`landscape`主题)。

而如果你对这个主题不是很满意，也可以在 Hexo 官网[主题页面](https://hexo.io/themes/)上找到你喜欢的主题，然后下载下来使用。

在这里，我安装的是`pacman`主题。安装过程很简单，直接执行下面的命令就行，根据网速的不同，可能会需要花费一点时间：

```shell
git clone https://github.com/A-limon/pacman.git themes/pacman
```

下载完主题之后，我们的博客并不会立即更改了样式，还需要修改 Hexo 目录下的`_config.yml`文件，之后再重新编译即可使用这个主题了。

> 需要注意，`theme`文件夹内的每个主题的文件夹中，也会包含一个`_config.yml`文件，而我们这里需要修改的则是 Hexo 目录下的`_config.yml`文件，不能搞错了。

首先，使用 Sublime Text 打开这个`_config.yml`文件，找到里面的`theme`属性，将其修改为`pacman`:

```yml
theme: pacman
```

然后，在命令行中重新编译，再开启本地服务器即可查看到博客已经使用了我们新下载的 pacman 主题了：

```shell
hexo g
hexo s
```
   
如过你没有找到你喜欢的主题，或者主题中有某些部分是你不喜欢要的，那么也可以自行进行修改。在下一章，我就会讲述一些关于修改自定义出一个属于我们个人的主题。

### 4. 本地调试 Hexo

安装并初始化完成本地的 Hexo 文件夹之后，我们就可以在本地进行预览查看了。

首先，先生成本地静态文件：

```shell
hexo g  #完整命令是: hexo generate
```

生成静态文件之后，就可以启动本地服务器：

```shell
hexo s  #完整命令是: hexo server
```

之后我们就可以在电脑上，打开浏览器，输入"localhost:4000"进行查看了。

可能有时候，你做的改动并没有展现出来，这时候就需要再执行`hexo g`之前，先执行清除指令：

```shell
hexo clean
```

### 5. 部署 Hexo 到 Github

本地预览调试觉得没有问题之后，我就可以把 Hexo 博客文件提交到 GitHub Pages 空间中了。

#### 5.1 创建仓库

首先，打开 GitHub [官网](https://github.com)，登录进去你自己的账号。

然后，在页面的右上角，点击十字标记，选择"New respository"，创建一个新的仓库。

![创建新仓库](http://cnd.qiniu.lin07ux.cn/2015-08-01%20Github-create-a-new-resposition.png "创建一个新的GitHub仓库")

在打开的创建新仓库页面，填写入仓库的名称，以及可选填的描述，然后设置仓库为"Public"：

![创建新仓库](http://cnd.qiniu.lin07ux.cn/2015-08-01%20Github-create-a-new-resposition-2.png "创建一个新的GitHub仓库")

**请注意**：仓库的名称是特定的，格式为**你的Github账号名.github.com**，比如，对于我的，名称就是"Lin07ux.github.com"。

> 除了仓库的名称有特别要求和仓库需要设置“public”外，其他的并没有特定要求：描述是选填的，是否生产“readme.md”文件也是可选的。

#### 5.2 修改`_config.yml`

部署之前，需要先将要部署到的仓库信息更新到 Hexo 目录下的`_config.yml`文件中。

打开`_config.yml`文件，找到`deploy`属性(如果没有，那就自己创建)，然后将其内容修改为如下：

```yml
deploy:
  type: git
  repository: git@github.com:Lin07ux/Lin07ux.github.io.git
  branch: master
  message: push the hexo to my GitHub Pages
```

**type**：在这里，我使用的 Hexo 版本是 3.1 的，所以"type"项的值就设置成`git`。(之前的版本需要设置为`github`)。

**respository**：这就是我们要部署静态博客文件到的仓库位置，可以直接将我这里的两个"Lin07ux"都更换为你自己的 github 账户名称就行了。

**branch**：设置为 master。

**message**：这个是可选项，就是提交到仓库时的备注信息，可以写你对这次提交的说明。

> 注意，由于 Hexo 也可以部署到其他类型的空间中，所以这里需要设置好对应的"type"属性的值。
> 
> 将 Hexo 部署到其他空间，可以参考 Hexo [官方文档](http://hexo.io/docs/deployment.html)。

#### 5.3 部署到 GitHub Pages

设置好`_config.yml`之后，就可以使用下面的命令将 Hexo 博客内容提交到我们的 GitHub Pages 中了：

```shell
hexo g
hexo d  #完整命令是：hexo deploy
```

或者，也可以使用下面的简写方式：

```shell
hexo d -g
```

提交完成之后，由于 GitHub Pages 有一些延迟，所以需要过几分钟才能看到我们的博客。查看的网址就是我们在前面“基础设置”中，设置的“url”地址，也就是"你的Github账户名.github.io"。

至此，我们其实已经将属于自己的 GitHub Pages 博客创建好了，以后就能够在本地(使用 Markdown 或 HTML)写好博文，使用 Hexo 编译并推送到这个博客上，供别人查看了。

## 0x03 写作

建立好了博客之后，就可以开始写博客了。在 Hexo 中，写博客的命令如下：

```shell
hexo new [layout] <title>   # new 可以简写为 n
```

其中`layout`是可选参数，表示博文使用的布局模板，默认为`post`模板；`title`表示这篇博客的标题。

> 其实也可以直接在`Hexo/source/_drafts`或者`Hexo/source/_posts`中直接新建 MarkDown 文件。

新建好新的博文文件之后，就可以使用 Atom 编辑器打开这个 Markdown 文件，写入我们的所思所得，记录我们的知识了。

### 1. Layout

在新建文章的时候，Hexo 会根据`scaffolds`文件夹内对应的模板文件来建立文件。

> 执行下面的命令的时候，Hexo 会尝试在`scaffolds`文件夹中寻找`photo.md`文件，并根据这个文件的内容建立文章：

```shell
hexo new photo "My Gallery"
```

在模板文件中，可以使用的变量如下：

| 变量    | 描述        |
| ------ | -----------|
| layout | 布局        |
| title  | 标题        |
| date   | 文件建立日期 |

Hexo 默认提供了三种布局模板：`post`、`page`、`draft`。使用他们生成的博文分别对应不同的路径，而我们自定义的其他布局和`post`相同，都将存储在`source/_posts`文件夹中。

如果要修改默认情况下，使用的布局模板，可以通过修改 Hexo 目录下的`_config.yml`文件中的`default_layout`参数的值来实现。

### 2. Front-matter

使用默认模板创建文章的时候，我们打开新创建的文件，会发现其中已经生成了如下的内容：

```shell
title: 标题(就是我们使用 new 命令的时候填写的 title)
date: 时间(文件生成的时间，类似于：2015-07-27 15:04:34)
---
```
   
这里的的三个短横线(`---`)上方的区域就是 Front-matter。

Front-matter 中的变量，是用于指定该文件的一些属性的，比如`title`就是指定文件的标题。 

Front-matter 默认提供的所有变量如下表中所示：

| 参数        | 描述            | 默认值      |
| ---------- | -------------- | ---------- |
| layout     | 布局            |            |
| title      | 标题            |            |
| date       | 建立日期         | 文件建立日期 |
| update     | 更新日期         | 文件更新日期 |
| commernts  | 开启文章的评论功能 | true       |
| tegs       | 标签(不适用于分页) |            |
| categories | 分类(不适用于分页) |            |
| permalink  | 覆盖文章网址      |            |

> 当然，我们也可以根据自己的需要，添加新的变量(如`mathjax`)，而在编译时寻找这个值的方式就是使用`page.mathjax`。

#### 2.1 分类和标签

只有文章支持分类和标签。

在其他系统中，分类和标签可能是很接近的意义，但是在 Hexo 中，两者有明显的差别：分类具有顺序性和层次性，也就是说`Foo, Bar`不等于`Bar, Foo`；而标签没有顺序和层次。

分类和标签的定义使用如下方式:

```
categories:
- ctg1
- ctg2
tags:
- tag1
- tag2
```

> 分类和标签的值，也可以直接使用一个英文中括号括起来，并用英文逗号分隔：`categories: [tag1, tag2]`。

#### 2.2 评论功能

评论功能需要依靠有评论插件集成到 Hexo 中。因为静态博客，是不支持动态评论的，否则就不会被称为静态博客了。

如果需要评论功能，那么可以添加相应的插件即可，如国内的[多说](http://duoshuo.com/)。

添加了评论功能之后，默认情况下，是对所有的文章都提供评论的，而这里的`comments`变量的存在，就是为了可以使某些特定的文章不开启评论功能。

#### 2.3 permalink

`permalink`变量能够让我们为文章指定一个特定的链接地址。

默认情况下，如果不设置这个变量，那么 Hexo 将会使用文章的标题为每个文章生成链接。

设置了这个变量的值之后，这个变量的值就会取代文章的标题称为该文章的链接地址。

比如，当我写了一篇文章，标题为*测试*。如果我不设置这个文章的`permalink`变量，那么 Hexo 默认生成的链接就是类似下面这种：`https://lin07ux.github.io/date/测试/`。

而如果我设置这个文章的`permalink`变量的值为"Test/test"，那么生成的链接就是：`https://lin07ux.github.io/date/Test/test/`。

#### 2.4 自定义变量

Hexo 默认提供的变量就只有那些，而如果我们需要得到这个文章的其他属性，这些默认属性可能就起不了作用了，此时就需要添加自定义的变量。

自定义变量的名称可以是任意的数字/字母组合，值也能自定义。

如，我需要判断我的文章中是否有使用数学公式，以便决定是否需要添加`mathjax.js`这个文件，我就可以在文章中加入`mathjax`属性。当文章中使用了数学公式的时候，就设置`mathjax`的值为`true`，没有使用的时候，设置值为`false`(也可以去除这个属性)。

```
title: mathjax公式介绍
date: 2015-07-31 12:00:01
mathjax: true
---
```

#### 2.5 自定义布局模板中的默认 Front-matter

默认情况下，`scaffolds`文件夹中的布局模板中，只包含了`title`和`date`变量，而我们一般需要给每篇文章都添加一些`tags`和`categories`。如果对每篇新建的文章都手动的输入这两个变量，也是有点麻烦的。为了偷懒，我们可以直接修改布局模板中的 Front-matter 变量。

如，我将`post`的模板(就是`scaffolds/post.md`文件)修改成了如下：

```
title: {{ title }}
date: {{ date }}
tags:
categories:
description:
permalink:
---
```

这样，我新建文章之后，就不需要手动输入这些变量，只需要定义他们的值即可。

> 可以看到，我这里默认自定义了一个`description`变量。这个变量是为了定义在博客的文章列表中展示的文章摘要/说明。

### 3. 草稿

在上面的 Layout 中提到的特殊布局`draft`，这种布局在建立的时候，会被保存到`source/_drafts`文件夹中，表示这个博文还是草稿。

草稿默认不会显示在博客页面中。

当你完成草稿之后，可以通过`publish`命令将这个草稿移动到`source/_posts`文件夹(该命令和`new`的使用方式十分类似):

```shell
hexo publish [layout] <title>   # 这里的title就是草稿文件的标题
```

## 0x04 进阶篇

前面，我们已经安装并配置好博客系统了，而且也可以应用不同的主题。但是，如果我们想要对页面某些部分不太满意，那么就需要自行进行修改了。

在下面要进行的操作中，可能需要你对 JavaScript、CSS 或者编程有一定了解才能得到符合你自己的结果。

> 下面所有的操作都是在你所使用的主题文件夹中。因为页面展现的全部逻辑都在每个主题中控制。
> 
> 对于我来说，用的是`pacman`主题，那么我就需要在`Hexo\themes\pacman\`中进行修改。

```
.
├── languages       # 多语言
|   ├── default.yml # 默认语言
|   └── zh-CN.yml   # 中文语言
├── layout          # 布局，根目录下的*.ejs文件是对主页，分页，存档等的控制
|   ├── _partial    # 局部的布局，此目录下的*.ejs是对头尾等局部的控制
|   └── _widget     # 小挂件的布局，页面下方小挂件的控制
├── source          # 源码
|   ├── css         # css源码
|   |   ├── _base   # *.styl基础css
|   |   ├── _partial   # *.styl局部css
|   |   ├── fonts   # 字体
|   |   ├── images  # 图片
|   |   └── style.styl   #*.styl引入需要的css源码
|   ├── fancybox    # fancybox效果源码
|   └── js          # javascript源代码
├── _config.yml     # 主题配置文件
└── README.md       # 说明文件
```

### 1. 修改布局

#### 1.1 修改顶部导航

默认情况下，博客的顶部导航只有`HEOM`、`ARCHIVE`。其实还能够添加`category`、`tag`导航，甚至还能够添加自定义的导航，也能够都修改成中文。

打开`Hexo/themes/pacman/_config.yml`文件，修改其中的`menu`属性。下面是我的设置：
   
```yml
menu:
  主页: /
  归档: /archives
  标签: /tags
  分类: /categories
  关于: /about/index.html
```

这里需要注意的是，各个导航后面的值就是这个导航将要转向的链接。

#### 1.2 TOC

`Table of contents`：这个功能能够给文章增加一个目录。这个目录是根据文章的分级标题自动生成的。

如果需要开启这个功能的话，需要在主题的`_config.yml`文件中配置`toc`的值：

```yml
toc:
  article: true   # show contents in article.
  aside: true     # show contents in aside.
```

如果需要在某个特定的文章中去除`toc`，可以在这个文章的 Front-matter 中添加`toc: false`即可。

`toc`相关的 CSS 样式可以在`Hexo\themes\pacman\source\css\_partial\hepler.styl`文件中找到(搜索`#toc`即可)。

#### 1.3 添加评论功能

Hexo 3.1.0 的 pacman 主题默认情况已经添加了评论功能，但是需要我们自己去开启。默认情况下，添加的是多说的评论系统。

首先，需要我们现在[多说](http://www.duoshuo.com)中注册一个账号，注册的时候，会让你填写一个唯一的用户名。

然后，打开`Hexo/themes/pacman/_config.yml`文件，设置`duoshuo`的相关属性：
   
```yml
duoshuo:
  enable: true         # duoshuo.com
  short_name: lin07ux  # duoshuo short name.
```

这里的`short_name`需要设置为你自己的多说账户名。

#### 1.4 代码高亮

Hexo 3.1.0 的`pacman`主题也已经默认添加了代码高亮功能，只是需要我们去开启。

打开`Hexo/themes/pacman/_config.yml`文件，设置`highlight`的相关属性：

```yml
highlight:
  enable: true
  line_number: true
  auto_detect: true
  tab_replace:
```

另外，关于代码方面的样式，是在`Hexo/themes/pacman/source/css/_base/code.styl`文件中定义的。我的博客上，给代码块添加一个圆角样式：

```
$code-block
  background highlight-background
  margin 0.5em 0
  padding 1em 2%
  overflow auto
  color highlight-foreground
  line-height line-height
  font-size font-size
  border-radius .35em       # 这个是我添加的
```

#### 1.5 博客底部头像

可能是一个 bug，在`panman`主题中，底部的那个圆形的图片无法显示出来，这个只需要修改一下这个主题文件夹中的`_config.yml`的`author_img`属性为如下：

```yml
author_img: /img/author.jpg
```

#### 1.6 其他样式

如果对 JavaScript 和 CSS 有一定的了解，能够修改代码，那么可以继续按照自己的方式进行修改布局和样式。虽然布局和样式代码并不是纯 JavaScript 和 CSS 代码，但是其实逻辑都是一样的，只需要略微看下就能明白的。

布局文件的位置在`Hexo/themes/pacman/layout/`文件夹；

样式文件的位置在`Hexo/themes/pacman/source/css`文件夹中。

### 2. 添加插件

#### 2.1 添加 sitemap

使用插件`hexo-generator-sitemap`能够生成站点地图，方便搜索引擎对我们的博客的抓取博客，以便能更好的展现博客的内容。

安装过程如下：

```shell
npm install hexo-generator-sitemap --save
```
   
> `--save`参数是将这个插件保存到 Hexo 目录下的`Package.json`文件中，这样以后就能方便的移植这个博客。

然后在 Hexo 根目录下的`_config.yml`里做如下配置：

```yml
plugins:
- hexo-generator-sitemap    # 启用这个插件

sitemap:
  path: sitemap.xml
```

对于国内用户来说，我们一般更关注的是百度对我们博客的抓取，所以我们可以安装`hexo-generator-baidu-sitemap`这个为百度量身打造的：

```shell
npm install hexo-generator-baidu-sitemap --save
```

然后在 Hexo 根目录下的 _config.yml 里添加如下的内容：

```yml
baidusitemap:
  path: baidusitemap.xml
```

> 更多的关于搜索引方面的内容，可以查看下[ Hexo优化与定制(二) ](http://lukang.me/2015/optimization-of-hexo-2.html)这篇文章。

#### 2.2 添加百度统计

首先，你需要注册一个[百度统计](http://tongji.baidu.com/)网站的账号。

然后进入到“网站中心”，点击“添加网站”，输入你的站点地址之后，就会给出异步加载百度统计的代码。复制这些代码。

打开`Hexo\themes\pacman\layout\_partial\head.ejs`文件，将刚复制的百度统计代码添加在这个文件的`</head>`标签之前就行了。

下面是我的百度统计代码，你需要做的就是得到你的统计代码：

```html
<script>
    // 百度统计
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "//hm.baidu.com/hm.js?a679c4c5b34afb670c4e4720bce980c2";
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(hm, s);
    })();
</script>
```

#### 2.3 修改文章页面导航

在博客中阅读文章时，在文章底部会有一个*上一篇*、*下一篇*的导航，但是默认为英文的`PREVIOUS`和`NEXT`。

打开`Hexo\theme\pacman\layout\_partial\post\pagination.ejs`文件，将其中的`PREVIOUS`和`NEXT`修改为对应的中文即可：

```ejs
<a href="<%- config.root %><%- page.prev.path %>" title="<%= page.prev.title %>">
  <strong>上一篇:</strong><br/>
  <span>
  <% if (page.prev.title){ %><%= page.prev.title %><% } else { %>(no title)<% } %></span>
</a>

<a href="<%- config.root %><%- page.next.path %>"  title="<%= page.next.title %>">
 <strong>下一篇:</strong><br/>
 <span><% if (page.next.title){ %><%= page.next.title %><% } else { %>(no title)<% } %>
</span>
</a>
```

至于想要修改样式，可以修改`Hexo\theme\pacman\source\css\_partial\hepler.styl`中`article-nav`相关的样式。这个需要对 CSS 有一些了解。

#### 2.4 InstantClick.js

加上 InstantClick.js 后, 通过预加载可以达到网页秒开的效果(并没有实际提速，只是将页面加载提前了一点)，具体介绍可以看[博客启用InstantClick](http://zhiqiang.org/blog/it/install-instantclick.html)。

使用 nstantclick.js 也很简单, 进入[InstantClick](http://instantclick.io/download)下载`instantclick.min.js`至使用的主题目录下`source/js`文件夹里, 然后在`/layout/_partial/after-footer.ejs`文件中加上如下代码即可：

```html
<script src="/js/instantclick.min.js" data-no-instant></script>
<script data-no-instant>InstantClick.init();</script>
```

需要注意的是：使用 InstantClick 可能导致 Google Analyitcs, 百度统计, Mathjax 和 Adsense 等不兼容。

##### 2.4.1 兼容Google Analyitcs

官方提供的兼容 Google Analyitcs 的方式如下：

```html
<script src="instantclick.min.js" data-no-instant></script>
<script data-no-instant>
/* Google Analytics code here, without ga('send', 'pageview') */

InstantClick.on('change', function() {
  ga('send', 'pageview', location.pathname + location.search);
});

InstantClick.init();
</script>
```

就是，在执行将 Google Analyitcs 代码中的`ga('send', 'pageview')`语句放到 InstantClick 的 change 事件的回调函数中执行。

##### 2.4.2 兼容其他

文章[让 InstantClick 兼容 MathJax 、百度统计等](http://zhiqiang.org/blog/it/instantclick-support-mathjax-baidu-stat.html)中，给出了如下的解决办法：

```html
<script src="instantclick.min.js" data-no-instant></script>
<script data-no-instant>
InstantClick.on('change', function(isInitialLoad) {
  if (isInitialLoad === false) {
    if (typeof MathJax !== 'undefined') // support MathJax
      MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    if (typeof prettyPrint !== 'undefined') // support google code prettify
      prettyPrint();
    if (typeof _hmt !== 'undefined')  // support 百度统计
      _hmt.push(['_trackPageview', location.pathname + location.search]);
    if (typeof ga !== 'undefined')  // support google analytics
        ga('send', 'pageview', location.pathname + location.search);
  }
});
InstantClick.init();
</script>
```

这段代码的含义是每次页面重载时，通过直接的函数调用来实现 MathJax、百度统计、Google Code Prettify、Google Analytics 的重新运行。和上面的方式官方解决 Google Analyitcs 方式是类似的。

#### 2.5 RSS

使用插件`hexo-generator-feed`能生成 Atom 1.0 或者 RSS 2.0 feed 。

```shell
npm install hexo-generator-feed --save
```

然后在 Hexo 根目录下的`_config.yml`里增加如下的属性：

```yml
plugins:
- hexo-generator-feed   # 启用插件

feed:
  type: atom        # 指定 RSS 类型是 atom 还是 rss2 
  path: atom.xml    # 表示 RSS 的路径
  limit: 20         # 指定多少篇最近的文章
```

然后修改使用的主题文件夹下的`_config.yml`中的 RSS 属性：

```yml
rss: /atom.xml
```

#### 2.6 fancybox

这个插件可以实现照片轮播的效果，也就是说，可以在文章的顶部增加一个照片轮播切换的效果，有点类似幻灯片。具体效果可以查看[这里](http://ibruce.info/reading/)。

Hexo 3.1.0 的`pacman`主题中已经默认添加了这个插件，但是需要在主题的`_config.yml`中需要开启这个功能。

首先，打开`Hexo/themes/pacman/_config.yml`，然后设置`fancybox`的属性值为`true`。
   
```yml
fancybox: true

## if you use gallery post or want use fancybox please set the value to true.
## if you want use fancybox in ANY post please copy the file  fancybox.js .
## in theme folder  /pacman/scripts  to your hexo blog folder  ../scritps .
```

然后，对于需要添加这个功能的文章，在其 Front-matter 区域中，加入 photos 变量：

```
title: 我的阅历
date: 2085-01-16 07:33:44
tags: [hexo]
photos:
- http://bruce.u.qiniudn.com/2013/11/27/reading/photos-0.jpg
- http://bruce.u.qiniudn.com/2013/11/27/reading/photos-1.jpg
---
```
   
为了避免每次都需要输入这个变量的麻烦，我们可以直接为这种文章创建一个布局模板，如创建`hexo/scaffolds/photo.md`文件：

```
layout: { { layout } }
title: { { title } }
date: { { date } }
tags:
photos:
-
---
```
   
之后，需要使用这个功能的就使用如下的命令创建文章：

```shell
hexo n photo 新文章
```

#### 2.7 其他的可用插件

**mathjax.js**：这个js插件能够在浏览器中生成数学公式。具体可以参见[这里](http://mathjax-chinese-doc.readthedocs.org/en/latest/configuration.html)。由于这个文件比较大，所以我们可以选择性加载：在文章的 Front-matter 中增加自定义变量，然后再 js 代码中判断是否为真，为真则添加这个 js 文件。

#### 2.8 其他事项

1. Hexo 中所有文件的编码格式均是 UTF-8。
2. Hexo 默认会处理全部 Markdown 和 HTML 文件，如果不想让 Hexo 处理你的文件，可以在文章的 Front-matter 中加入`layout: false`。
3. 建议使用图床，如[七牛图床](https://portal.qiniu.com/signup?code=3lgiixo6hfi36)。因为 GitHub Pages 只有 300M 空间，所以不适合放大量图片，而使用图床则能很好的解决这个问题。
4. 如果你修改布局或样式之后，发布到 GitHub Pages，但是发现并没有变化，此时可以先使用命令清除缓存，然后再生成文件，进行发布：

```shell
hexo clean
hexo d -g
```

## 0x05 问题

### 1. 文章的修改时间不是真正的修改时间

> 转摘：[从 Git 提交历史中「恢复」文件修改时间](https://yq.aliyun.com/articles/31770)

Hexo 没有依赖文件系统的特性去保存创建时间，而是直接把时间作为文章的元数据，放在文章开头的 YAML 区域里头了。

与此同时，Hexo 也提供了获取文章修改时间的 API，由于 POSIX 保证了能够问系统要到文件的最后修改时间，Hexo 就直接把这个功能交给系统代理，文件的最后修改时间就认为是文章的修改时间。

在博客的自动部署流程中，需要把博客源码从 Github 上 clone 到 Travis-CI 的虚拟机里，然后使用 Hexo 编译出静态页面。显然这些 clone 出来的文件，它们的最后修改时间是这些文件在 Travis-CI 的虚拟机里的创建时间，而不是当初修改并保存的时间。至于为什么 Git 不保存文件的修改时间，原因在[这里](https://git.wiki.kernel.org/index.php/Git_FAQ#Why_isn.27t_Git_preserving_modification_time_on_files.3F)。

那么有没有什么办法恢复文件的修改时间呢？精确的恢复是不可能的，毕竟信息已经丢失了，丢失得很彻底。但是作为一个博客系统，对时间精确度的要求没那么高，近似一下，使用文件的 commit 时间作为修改时间，也是可以接受的。

Google 一下，神通广大的外国朋友已经给出了[解决方案](http://www.commandlinefu.com/commands/view/14335/reset-the-last-modified-time-for-each-file-in-a-git-repo-to-its-last-commit-time)，精简参数后如下：

```shell
git ls-files | while read file; do touch -d $(git log -1 --format="@%ct" "$file") "$file"; done
```

这个操作就是把当前 Git 仓库里正在跟踪的文件给列出来，然后依次「篡改」文件的最后修改时间。根据`git log`命令的[文档](https://git-scm.com/docs/git-log)，`%ct`是 committer date, UNIX timestamp 的占位符，代表提交时的时间戳。

把上面这行代码添加到`.travis.yml`中，在生成静态页面之前恢复一下文件的修改时间就可以修复文章的修改时间不正确的问题了。

> 上面那段代码中为什么要在时间戳前面用`@`符号呢？从 Coreutils 5.3.0 开始，实用工具中只要是涉及时间的参数，都可以用`@+unix timestamp`的形式来替代，比如`touch -d`本来要带的参数是一个「人类可读」的时间描述，可以是`Sun, 29 Feb 2004 16:21:42 -0800`或者`2004-02-29 16:21:42`，甚至`next Thursday`也行，但还是可以任性地用`@1078042902`作为时间输入。
> 参考：[GNU Coreutils 的文档](https://www.gnu.org/software/coreutils/manual/html_node/Seconds-since-the-Epoch.html#Seconds-since-the-Epoch)

