> 原文链接：[深入 Github 主页](https://www.awesomes.cn/source/10)

Github 提供给每个用户的一个用于运行静态页面的地址。包括一个二级域名(`http://[Github 用户名].github.io/`)和存放文件的版本库。按照库的类型可以分为个人主页和项目主页。

下面我们就来讲讲如何创建这两种主页以及注意事项。

## 个人（组织）主页
一般是用来作为你的个人（或组织）信息展示的主页。

注意：

* 1、创建的版本库必须命名为`[Github 用户名].github.io`
* 2、必须将要展示的内容放到`master`分支下
* 3、访问地址为`http://<Github 用户名>.github.io`

创建的步骤如下：

> 假设用户名为：`Lin07ux`。

1. 在本地创建一个名为`Lin07ux.github.io`的文件夹：`mkdir Lin07ux.github.io`；
2. 创建自己的主页的项目，这里我们简单地只创建一个 index.html 页面：`echo "My Page" > index.html`；
3. 在 Github 上创建名为`Lin07ux.github.io`的个人项目；
4. 将本地项目推送到刚才创建的远程项目中：

```shell
git init
git add .
git commit -m 'init project'
git remote add origin git@github.lin07ux.com:Lin07ux/Lin07ux.github.io
git push -u remote master
```

推送到 Github 仓库中之后，就可以通过`http://Lin07ux.github.io`来访问这个个人主页了。

> 可能会有一段时间的延迟。


## 项目主页
主要用来为每个项目创建说明文档主页。一般我们是在一个项目自身完成后才去创建项目主页，所以在我们创建项目主页之前，该项目已经存在了。下面我们就给`Lin07ux/emoji`这个项目添加一个项目主页。

注意：

* 1、必须将要展示的内容放到`gh-pages`分支下
* 2、访问地址为`http://<Github 用户名>.github.io/<项目名>`

创建步骤：

1. 首先我们给项目创建一个`gh-pages`分支：

```shell
git checkout --orphan gh-pages
```

上面的指令是创建一个 gh-pages 分支，并切换到该分支。`--orphan`表示该分支是全新的，不继承原分支的提交历史（默认`git branch gh-pages`创建的分支会继承 master 分支的提交历史，所以就不纯净了）。不过需要注意的是，这里`git branch`是显示不出`gh-pages`分支的，需要做一次提交才行。

2. 把新分支中的文件删掉：

```shell
git rm .
rm .gitignore
```

3. 然后创建我们需要的静态页面文件，这里象征性地创建一个 index.html 并写入内容：

```shell
echo "My Page" > index.html
```

4. 然后开始做提交：

```shell
git add index.html
git commit -a -m "First pages commit"
git push origin gh-pages
```

现在通过`git branch`就可以看到 gh-page 分支了。然后访问`http://Lin07ux.github.io/emoji`就可以看到我们的项目主页了。

**注意事项**

1、如果你的项目名开始或结尾包含破折号（如`emoji-`或`-emoji`），或者包含连续破折号（如 `web--emoji`），那么 Linux 用户访问的时候会报错，你需要移除项目名中的非字母数字的字符。

2、 大家也许注意到了，个人主页的地址是`http://Lin07ux.github.io/`，而项目主页的地址是类似`http://Lin07ux.github.io/emoji`，事实上就是一个相对于个人主页项目的二级访问目录。那么问题来了：如果个人主页项目里面有一个`emoji`文件夹，里面也有相应的静态页面。访问`http://Lin07ux.github.io/emoji`时究竟访问的是哪个项目呢？经过测试，实际上访问的是`Lin07ux/emoji`的项目主页，也就是说个人主页的`emoji`目录是访问不到的。所以大家要注意不要造成这两者的命名冲突。


## 页面自动生成器
这是 Github 为我们提供的可视化生成主页的工具，具体操作如下：

1. 在此之前，我们将上面手动生成的`gh-pages`分支删掉(需要切换到非`gh-pages`的分支)：

```shell
# 删除本地分支
git checkout master
git branch -D gh-pages

# 再删除远程分支
git push origin --delete gh-pages
```

2. 在 Github 上切换到你的项目主页，点击`Settings`设置按钮

![Github settings](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471843387641.png)

3. 点击`Launch automatic page generator`，启动页面自动生成器：

![Launch automatic page generator](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471843430749.png)

4. 进入主页设计页面（这里使用的是 Markdown 编辑器），并填写相关的信息，还可以添加 Google 的统计插件。注意，这里我们一般可以通过加载`README.md`文件来直接从中读取内容，否则你就得自己手动编写。

![主页设计](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471843487926.png)

5. 点击`Continue To Layouts`按钮，进入模板选择页面。这里你可以选择自己喜欢的模板样式，是不是很漂亮？而且很多模板相信大家都在一些项目的主页中看到过。

![Layouts](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1471843537716.png)

6. 选择好模板后，可以点击左上角的`Publish page`发布页面，然后就可以看到成功提示了。

7. 最后，我们可以将`http://awesomes-cn.github.io/emoji/`设置成项目主页

整个过程完成后，你可以看到在你的项目中自动生成了一个新的分支。没错，就是 gh-pages，可以看到里面有一些样式和脚本文件。如果你对其中的某些部分不满意，或者想添加新的页面，那么可以将该分支 pull 到本地进行修改。

* 首先将分支拉到本地：`git fetch origin`
* 然后切换到 gh-pages 分支：`git checkout gh-pages`
* 接下来就可以做自己的修改了。

> 注意：如果你是在个人主页中使用页面自动生成器，那么最终的文件会生成到 master 分支下。因为上面我们说过个人主页是 master 分支，项目主页是 gh-pages 分支。所以可以直接`git pull origin master`


## 第三方工具
当然，除了上面两种方式，还有很多第三方的工具来帮助我们构建主页，如[jekyll](https://help.github.com/articles/using-jekyll-with-pages/)，这里我就不详细讲了，感兴趣的同学可以了解一下。


## 域名绑定
域名绑定
有的同学不喜欢 Github 给的二级域名，想设置自己的域名来访问，很简单，我们这里以我的*个人主页项目*来介绍。

1. 来到项目主页，切换到 master 分支下（注意：如果是项目主页则切换到 gh-pages 分支），新建一个文件。
2. 将文件命名为`CNAME`，然后添加你要解析的域名`home.Lin07ux.cn`(注意这里不是`http://home.Lin07ux.cn`，且只能填写一个域名)，这里用的是一个二级域名。然后填写提交信息并直接提交合并。
3. 域名解析。这里用的是万网的域名，需要做下面的解析操作：
    * 记录类型：`CNAME`
    * 主机记录：`home`
    * 记录值：`Lin07ux.github.io`

接下来你就可以直接访问`http://home.Lin07ux.cn/`来替代`http://Lin07ux.github.io`了。

> 注意：这里是将`home.Lin07ux.cn`解析到了个人主页项目上，访问`http://home.Lin07ux.cn/emoji`仍然是可以访问到 emoji 项目主页的。但是如果把这个 CNAME 文件创建到 emoji 项目的`gh-pages`分支下（即直接解析 emoji 项目主页），那么访问`http://home.Lin07ux.cn`就相当于访问`http://Lin07ux.github.io/emoji`了，而个人主页项目也就访问不到了。


## 官方文档
[Github Help](https://help.github.com/categories/github-pages-basics/)


