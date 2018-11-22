### 把 Github 项目变成前端网站
GitHub Pages 大家可能都知道，常用的做法，是建立一个`gh-pages`的分支，通过`setting`里的设置的 GitHub Pages 模块可以自动创建该项目的网站。

这里经常遇到的痛点是，master 遇到变更，经常需要去 sync 到`gh-pages`，特别是纯 web 前端项目，这样的痛点是非常地痛。Github 官方可能嗅觉到了该痛点，出了个 master 当作网站是选项，太有用了。

![](http://cnd.qiniu.lin07ux.cn/markdown/1482554602364.png)

选择完 master branch 之后，master 自动变成了网站。master 所有的提交会自动更新到网站。

### 精准分享关键代码
**单行代码**

比如你有一个文件里的某一行代码写得非常酷炫或者关键，想分享一下。可以在 url 后面加上`#L行号`。

比如，点击下面这个 url：[https://github.com/AlloyTeam/AlloyTouch/blob/master/alloy_touch.js#L240](https://github.com/AlloyTeam/AlloyTouch/blob/master/alloy_touch.js#L240)，你便会跳到`alloy_touch.js`的第 240 行。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1482554933733.png" width="972"/>

**一段代码**

如果我是一段代码，即多行代码，则在 url 后面加上`#L开始行号-L结束行号`。

比如，AlloyTouch 的运动缓动和逆向缓动函数如下面代码段所示：[https://github.com/AlloyTeam/AlloyTouch/blob/master/alloy_touch.js#L39-L45](https://github.com/AlloyTeam/AlloyTouch/blob/master/alloy_touch.js#L39-L45)。

**生成代码分享链接**

其实也不用记忆你直接在网址后面操作，github 自动会帮你生成 url。比如你点击 39 行，url 就会变成`url#L39`的格式。再按住`shift`点击 45 行，url 变成了`url#L39-L45`的格式。然后直接复制浏览器上的 url 即可。

![](http://cnd.qiniu.lin07ux.cn/markdown/1482555164569.png)

### 通过提交的 msg 自动关闭 issues
比如有人提交了个 issues：`https://github.com/AlloyTeam/AlloyTouch/issues/6`。然后你去主干上改代码，改完之后提交填 msg 的时候，填入：

```
fix  https://github.com/AlloyTeam/AlloyTouch/issues/6
```

这个 issues 会自动被关闭。当然不仅仅是`fix`这个关键字。下面这些关键字也可以：

* close
* closes
* closed
* fixes
* fixed
* resolve
* resolves
* resolved

### 通过 HTML 方式嵌入 Github
如下面所示，`user`和`repo`改成你想要展示的便可以：

```html
<iframe src="//ghbtns.com/github-btn.html?user=alloyteam&repo=alloytouch&type=watch&count=true" allowtransparency="true" frameborder="0" scrolling="0" width="110" height="20"></iframe>
```

插入之后你便可以看到这样的展示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1482555402075.png)

更多的使用方式，可以查看 [GitHub Buttons](http://ghbtns.com/)。

### 设置项目语言
Github 会根据相关文件代码的数量来自动识别你这个项目的主要使用变成语言。如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1482555562771.png)

但是有时候可能项目中代码数量最多的并非我们真实的项目语言，此时可以在项目的根目录中加入一个`.gitattributes`文件，并在其中指定将某类型的文件识别为指定的类型即可。比如：

```
*.html linguist-language=JavaScript
```

意思就是：把所有 html 文件后缀的代码识别成 js 文件。

### 查看自己项目的访问数据
在自己的项目下，点击`Graphs`，然后再点击`Traffic`如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1482555759610.png)

里面有`Referring sites`和`Popular content`的详细数据和排名，分别表示从什么网站来到你的项目的，以及经常看你项目的哪些文件。

如：`Referring sites`显示如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1482555803686.png)

### trending 排行榜
下面可以看看怎么查看某类型语言的每日排行榜。比如 js、html、css 每日排行榜：

* [https://github.com/trending/javascript?since=daily](https://github.com/trending/javascript?since=daily)
* [https://github.com/trending/javascript?since=daily](https://github.com/trending/html?since=daily)
* [https://github.com/trending/javascript?since=daily](https://github.com/trending/css?since=daily)

### 其他
* issue 中输入冒号`:`添加表情
* 任意界面，`shift + ?`显示快捷键
* issue 中选中文字，`R`键快速引

