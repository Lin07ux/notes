> 转摘：[HTML rel属性值释义大全](https://www.zhangxinxu.com/wordpress/2019/06/html-a-link-rel/)

HTML 中有一个名为`rel`的属性，是`relationship`这个单词的缩写，指明两个文档之间的关系，专门用来链接相关元素上，如`<a>`、`<area>`、`<form>`或`<link>`元素上，因此`rel`的属性值也是“链接类型”的代称。

`rel`支持非常多的属性值，包含的知识非常多，且有些属性值非常重要，整理如下：

### 1. rel="alternate"

单词`alternate`有交替、替换的意思，顾名思意就是链接有替换内容。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

主要有下面 3 大应用场景：

1. 用在`link`元素中，和`stylesheet`链接类型一起使用，配合`title`属性（必须），可以定义替换 CSS，可以以一种体验更好的方式实现类似换肤这种功能。具体可以参见 [link rel=alternate网站换肤功能最佳实现](https://www.zhangxinxu.com/wordpress/2019/02/link-rel-alternate-website-skin/)。

2. 如果网站包含 RSS 订阅，可以使用该值进行指明，需要设置`type`属性值为`application/rss+xml`或者`application/atom+xml`(ATOM 是一种订阅网志的格式，一种 Web feed，和 RSS 相类似)。例如：
    
    ```html
    <link rel="alternate" type="application/rss+xml" href="https://zhangxinxu.com/feed" />
    ```
    
    或者使用在指向 RSS 订阅地址的`<a>`元素上：
     
    ```html
    <a rel="alternate"t ype="application/rss+xml" href="https://zhangxinxu.com/feed">RSS订阅</a>
    ```

3. 还可以用来定义用来替换的页面。例如：

    media 响应处理，小于 640 像素时候，告诉用户或者设备（或搜索引擎），还有移动站页面：

    ```html
    <link rel="alternate" media="only screen and (max-width: 640px)" href="https://m.zhangxinxu.com/" />
    ```

    多语言时候告诉用户或者设备（或搜索引擎）还有其他语言的网站，需要`hreflang`属性同时设置，例如西班牙语：

    ```html
    <link rel="alternate" href="http://es.zhangxinxu.com/" hreflang="es" />
    <!-- 或者直接使用链接 -->
    <a href="http://es.zhangxinxu.com/" hreflang="es" rel="alternate">西班牙语</a>
    ```

    还可以用于指向另外的格式，例如 PDF 文件，需要`type`属性同时设置（值为对应文件的 MIME type 值）。

### 2. rel="archives"

archives 语义是归档。此属性值可以用来定义指向归档语义的超链接。例如，博客的每月索引页的`<a>`链接就可以使用这个值。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

例如：

```html
<a href="https://www.zhangxinxu.com/wordpress/2019/05/" rel="archives">2019年五月</a>
```

> 注意后面有`s`，直接`rel="archive"`是不正确的。

### 3. rel="author"

链接指向作者介绍页。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

例如：

```html
<a href="https://www.zhangxinxu.com/life/about/" rel="author">张鑫旭</a>
```

链接地址支持直接`href`属性值以`mailto:`开头，可以呼起邮件客户端发送邮件，但是在网页中其实不推荐这么做，因为容易被爬虫把邮箱爬过去。推荐作为是链接地址执行作者信息介绍页面，然后通过`rel="author"`增强这个链接的语义。

### 4. rel="bookmark"

bookmark 是指书签，表示链接可以作为书签收藏，通常用在指向文章内容的永久链接上。

* 允许使用的链接元素：`a`、`area`
* 不允许使用的链接元素：`link`、`form`

### 5. rel="canonical"

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

canonical 是规范的意思，只能作用在`<link>`元素上，此值的作用与搜索引擎有关，可以为类似网页或重复网页指定规范网页。

拿 Google 搜索举例，如果某一个网页可通过多个网址访问，或者不同网页包含类似内容（例如，某个网页既有移动版，又有桌面版），那么 Google 会将这些网页视为同一个网页的重复版本。Google 会选择一个网址作为规范版本并抓取该网址，而将所有其他网址视为重复网址并降低对这些网址的抓取频率。

如果未明确告知 Google 哪个网址是规范网址，Google 就会替您做出选择，或会将这两个版本视为同等重要，这可能会导致出现一些不当的行为。

那如何让 Google 知道哪个网址是最规范最优先的呢？其中一个方法就是`rel=canonical`的`<link>`标记。

例如 Wordpress 文章链接地址有多种 URL 表示，比如希望用户通过`ttps://www.zhangxinxu.com/wordpress/2019/05/html-a-rel/`（而非`https://www.zhangxinxu.com/wordpress/?p=8488`）访问这篇文章。则可以在`<head>`中新增如下所示的代码：

```html
<link rel="canonical" href="https://www.zhangxinxu.com/wordpress/2019/05/html-a-rel/" />
```

如果规范网页有移动版，还可以为其添加`rel="alternate"`链接，并使该链接指向此网页的移动版（本站没有专门移动页面，下面地址仅示意）：

```html
<link rel="alternate" media="only screen and (max-width: 640px)" href="http://m.zhangxinxu.com/wordpress/2019/05/html-a-rel/">
```

`rel=canonical`的`<link>`标记方法的优点是可以映射无限多个重复网页，不足是在大型网站或网址经常改变的网站上维护映射会比较复杂，且仅适用于 HTML 网页，不适用于 PDF 之类的文件（在这种情况下可以使用`rel=canonical` HTTP 标头）。

### 6. rel="dns-prefetch"

只能作用在`<link>`元素上，作用是 DNS 预读取。允许浏览器在用户单击链接之前进行DNS查找和协议握手，可以提高页面资源加载速度。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

例如：

```html
<link rel="dns-prefetch" href="http://www.zhangxinxu.com/">
```

此时域名`www.zhangxinxu.com`将被预先解析。此时如果页面中有链接地址的域名也是`www.zhangxinxu.com`，那么当用户点击这个链接的时候，新打开的页面就少了 DNS 向上查找这一步，因为之前浏览器已经DNS预读取了，页面呈现速度就会快一些，虽然快的并不是很多，但能快一点是快一点。

### 7. rel="external"

external 是外部的意思。当链接指向的是外部资源或外部链接的时候，可以使用该属性。主要作用是告诉搜索引擎，这是一个外部链接。常常和`nofollow`值一起使用。

> 和链接在新窗口打开没有任何关系。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

例如：

```html
<a href="http://example.com/" rel="external nofollow">Foobar</a>
```

### 8. rel="first"

表示指向一个序列页面资源的第一个资源。其它类似的 rel 链接资源类型有`last`、`prev`和`next`。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

### 9. rel="help"

* 允许使用的链接元素：`a`、`area`、`link`、`form`

表示帮助信息：

* 如果元素是`a`或`area`，则表示超链接指向一个资源，该资源对元素的父元素及其子元素提供了进一步的帮助。
如果元素是`link`，则表示超链接指向一个资源，该资源将对整个页面提供进一步的帮助。

### 10. rel="icon"

指定网站 favicon 图标。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

之前常常会在 icon 类型前面加上`shortcut` link 类型，但是现在这样已经不符合规范了，应该直接使用 icon 即可。例如：

```html
<!-- 过时写法 -->
<link rel="shortcut icon" href="favicon.ico" />
<!-- 规范写法 -->
<link rel="icon" href="favicon.ico" />
```

也可以使用 PNG 格式的图像作为 favicon。如果是多个 icon，还可以使用`type`或者`sizes`等属性指定不同的类型和尺寸。

```html
<link rel="icon" type="image/png" href="/path/to/icons/favicon-16x16.png" sizes="16x16">
<link rel="icon" type="image/png" href="/path/to/icons/favicon-32x32.png" sizes="32x32">
```

### 11. rel="import"

就是 HTML Imports，Web Components 开发重要组成部分之一。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

例如：

```html
<link rel="import" href="module.html">
```

### 12. rel="index"

表示超链接指向的页面资源是某个具有层级结构的一部分。如果还存在一个或多个`up`链接类型，则`up`链接类型的数目表示层次结构中当前页面的深度。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

> 这个属性在 HTML5 规范出来之后就已经被舍弃了。

### 13. rel="last"

表示指向序列页面的最后一个页面。其它类似的 rel 链接资源类型有`first`、`prev`和`next`。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

### 14. rel="license"

license 语义是许可证，也可以理解为版权说明。

* 允许使用的链接元素：`a`、`area`、`link`、`form`

例如一个指向 MIT license 说明或者 BSD license 说明的超链接，就可以使用此 rel 属性值。

```html
<a href="/somesite/bsd-license" rel="license">BSD license</a>
```

### 15. rel="manifest"

指定清单文件，用做 Web 应用程序清单部署。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

例如：

```html
<link rel="manifest" href="/manifest.webmanifest">
```

`.webmanifest`是约定俗成扩展名，返回文件内容类型需要是：`Content-Type: application/manifest+json`，也支持`.json`扩展名的清单文件。

`manifest`可以让 webapp 变得更加 native，离线开发时候也很有用。

### 16. rel="modulepreload"

modulepreload 可以用来预加载原生模块脚本。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

例如：

```html
<head>
  <link rel="modulepreload" href="super-critical-stuff.mjs">
</head>
[...]
<script type="module" src="super-critical-stuff.mjs">
```

为何不使用`<link rel="preload">`加载模块 JS 呢？因为`<script type="module">`没有 crossorigin 属性，和普通的`<script>`、`<link>`元素不一样。

### 17. rel="next"

表示指向序列页面的下一个页面。其它类似的rel链接资源类型有`first`、`last`和`prev`。

* 允许使用的链接元素：`a`、`area`、`link`、`form`

### 18.  rel="nofollow"

可让网站站长告诉搜索引擎“不要跟踪此网页上的链接”或“不要跟踪此特定链接”。

* 允许使用的链接元素：`a`、`area`、`form`
* 不允许使用的链接元素：`link`

例如，登录页面无需抓取，可以在登录页面中加上如下代码：

```html
<a href="signin.php" rel="nofollow">登录</a>
```

### 19. rel="noopener"

这是一个很重要的且常用的 rel 属性值，与安全相关。

如果链接元素没有设置`noopener`，则在新窗口打开这个链接的时候，则这个新窗口页面可以通过`window.opener`获取来源页面的窗体对象，于是可以改变原页面 URL 地址之类的事情。

而如果加上了`rel="noopener"`则可以避免。因此，如果网站上有外部的链接地址，一定要记得加上`noopener`。

* 允许使用的链接元素：`a`、`area`、`form`
* 不允许使用的链接元素：`link`

### 20. rel="noreferrer"

当导航到其他页面的时候，可以阻止浏览器向跳转页面发送页面地址以及其他值。

* 允许使用的链接元素：`a`、`area`、`form`
* 不允许使用的链接元素：`link`

> 作用存疑

### 21. rel="opener"

这是个还处于实验阶段的新特性。`opener`和`noopener`的语义完全是相反的，表示超链接出去的页面有`window.opener`。

* 允许使用的链接元素：`a`、`area`、`form`
* 不允许使用的链接元素：`link`

因为在新的规范中，设置了`target=_blank`的`<a>`元素默认就没有`opener`，这样更安全。如果开发者希望页面有`opener`，则可以给链接元素添加`rel="opener"`。

### 22. rel="pingback"

pingback 估计了解的开发人员不多，如果是 Wordpress 资深用户，应该会比较熟悉。在运管 WordPress 博客的时候，后台评论经常看到来自某一篇文章的 pingback，表示你这篇文章被引用了。pingback 可以让 Web 作者追踪什么人链接至他的文章。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

### 23. rel="preconnect"

作用是告知浏览器提前连接链接地址对应站点，不过只是连接，并不会公开任何私人信息或者下载任何内容。好处是打开链接内容的时候可以更快的获取（节约了 DNS 查询、重定向以及指向处理用户请求的最终服务器的若干往返）。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

实验阶段属性值。由于是渐进增强特性，因此，可以放心使用。

例如：

```html
<link rel="preconnect" href="https://www.zhangxinxu.com">
```

上面这段 HTML 作用是浏览器知道我们打算连接到`www.zhangxinxu.com`并从其中获取内容，然后浏览器会提前做好连接。

但是`preconnect`并不是没有成本的，不能滥用。`<link rel="preconnect">`会占用宝贵的 CPU 时间，如果用户没有在 10 秒内使用该连接，资源浪费的情况就会变得更严重，因为当浏览器关闭连接时，所有已完成的连接都将遭到浪费。

因此，尽可能使用`<link rel="preload">`，一些特殊场景下使用`<link rel="preconnect">`。

### 24. rel="prefetch"

prefetch 表示预获取，通常是一些静态资源。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

prefetch 最适合抢占用户下一步可能进行的操作并为其做好准备。例如搜索结果列表中首个产品的详情页面或搜索分页内容的下一页：

```html
<link rel="prefetch" href="page-2.html">
```

### 25. rel="preload"

`preload`表示预加载。告诉浏览器这些资源你先加载，之后要使用。注意，`preload`是强制浏览器执行的指令，不只是可选提示，与上面的`preconnect`和`prefetch`是不一样的。

因此，使用`preload`时一定要保证内容会被使用，如果提取的资源 3 秒内没有被当前页面使用，Chrome 开发者工具的控制台会触发警告！

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

例如：

```html
<link rel="preload" as="script" href="super-important.js">
<link rel="preload" as="style" href="critical.css">
```

### 26. rel="prerender"

`prerender`表示预渲染。告知浏览器在背后先默默渲染页面，当用户之后导航到这个页面时候会大大加快加载速度。

和`prefetch`区别在于，`prefetch`获取页面并不会加载页面中的 css 和 js 资源，更多是页面本身，但是`prerender`已经在背后默默做起渲染的事情，预处理要更进一步。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

例如：

```html
<link rel="prerender" href="about.html">
```

### 27. rel="prev"

表示指向序列页面的上一个页面。其它类似的rel链接资源类型有`first`、`last`和`next`。

* 允许使用的链接元素：`a`、`area`、`link`、`form`

### 28. rel="search"

表示链接地址是当前网站或资源对于的搜索文档接口页面，以及其他一些类似插件的作用。

* 允许使用的链接元素：`a`、`area`、`link`、`form`

例如设置`type`属性值为`application/opensearchdescription+xml`，则对应的资源（OpenSearch 描述文件）可以作为 Firefox 或者 Internet Explorer 的搜索插件。

例如在`<head>`标签里加上：

```html
<link rel="search" type="application/opensearchdescription+xml" href="provider.xml" title="关键词搜索" />
```

`provider.xml`的内容为：

```xml
<?xml version="1.0" encoding="utf-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
　<InputEncoding>UTF-8</InputEncoding>
　<ShortName>关键词搜索</ShortName>
　<Description>关键词搜索</Description>
　<Image height="16" width="16" type="image/vnd.microsoft.icon">/favicon.ico</Image>
　<Url type="text/html" template="/wordpress/?s={searchTerms}" />
</OpenSearchDescription>
```

在 Firefox 浏览器下效果类似下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1560155530223.png)

这样，网站所有页面，就算没有搜索框也可以搜索了。不过 Chrome 浏览器地址栏没搜索框无法支持，Firefox 和 IE 浏览器支持。

### 29. rel="shortlink"

网站当前页面对应的短链接，这样分享链接的时候要更容易（微博字数限制，微信或 QQ 中地址呈现等）。

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：不详

例如：

```html
<link rel='shortlink' href='https://www.zhangxinxu.com/wordpress/?p=8488' />
```

### 30. rel="sidebar"

sidebar 语义就是指向可以帮助浏览二级浏览上下文的资源，例如侧边栏。

曾经是 HTML 规范的一部分，但是最近已经从规范中删除掉了，Firefox 63之 后的版本已经不支持。

* 允许使用的链接元素：`a`、`area`、`form`
* 不允许使用的链接元素：`link`

### 31. rel="stylesheet"

指向样式表资源

* 允许使用的链接元素：`link`
* 不允许使用的链接元素：`a`、`area`、`form`

### 32.  rel="tag"

表示指向表述当前文档标签的链接。可以使 SEO 更加精准，质量更高。

* 允许使用的链接元素：`a`、`area`
* 不允许使用的链接元素：`link`、`form`

例如：

```html
<a href="https://www.zhangxinxu.com/wordpress/2019/06/html-a-link-rel/" rel="tag">rel</a>
```

### 33. rel="up"

表示当前页面是层次结构的一部分，并且超链接指向该结构的更高级别资源。`up`链接类型的数量表示当前页面和链接资源之间的深度差。

* 允许使用的链接元素：`a`、`area`、`link`
* 不允许使用的链接元素：`form`

