> 转摘：[如何通过 Tampermonkey 快速查找 JavaScript 加密入口](https://mp.weixin.qq.com/s/r3MUVEPos2Rm5uKno8HysQ)

Tampermonkey 是一个功能非常强大的插件，中文也叫作「油猴」。利用它可以在指定的目标页面中执行任何 JavaScript 代码，可以实现任何可以用 JavaScript 实现的功能，如自动抢票、自动刷单、自动爬虫等功能。

## 一、基础

### 1.1 安装

在 Chrome 浏览器中，可以直接在 [Chrome 应用商店](https://chrome.google.com/webstore/detail/tampermonkey/dhdgffkkebhmkfjojejmpbldmpobfkfo) 或 [Tampermonkey 的官网](https://www.tampermonkey.net/) 下载安装。

安装完成之后，在 Chrome 浏览器的右上角会出现 Tampermonkey 的图标，这就代表安装成功了。

![](http://cnd.qiniu.lin07ux.cn/markdown/1577363826293.png)

### 1.2 脚本管理

Tampermonkey 运行的是 JavaScript 脚本，可以为不同的网站设置不同的脚本，从而实现特定的目的。这些脚本可以自定义，也可以使用别人已经写好的脚本。

可以在 [Greasy Fork](https://greasyfork.org/zh-CN/scripts) 网站中搜索自己需要的脚本，然后点击安装即可。

可以通过 Tampermonkey 的管理面板来管理插件，点击 Tampermonkey 插件的图标，点击「管理面板」按钮，就可以打开脚本管理页面。

![](http://cnd.qiniu.lin07ux.cn/markdown/1577364389547.png")

管理界面显示类似下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1577364437701.png)

这里提供了添加、编辑、调试、删除等管理功能，可以方便地对脚本进行管理。

## 二、脚本编写

除了使用别人已经写好的脚本，也可以自己编写脚本来实现想要的功能。

Tampermonkey 的脚本就是 JavaScript 代码，只需要按照 JavaScript 的语法编写即可。当然，除了语法上，还需要遵循一些 Tampermonkey 脚本特定的写作规范，包括一些参数的设置等。

### 2.1 创建脚本

在 Tampermonkey 脚本管理页面的右上角顶部点击「+」号，会显示如下图所示的页面：

![](http://cnd.qiniu.lin07ux.cn/markdown/1577364598504.png)

这里提供了初始化代码，如下：

```JavaScript
// ==UserScript==
// @name         New Userscript
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  try to take over the world!
// @author       You
// @match        https://mp.weixin.qq.com/s/r3MUVEPos2Rm5uKno8HysQ
// @grant        none
// ==/UserScript==

(function() {
    'use strict';

    // Your code here...
})();
```

### 2.2 配置项

初始化代码的顶部是一些注释，这些注释提供了 Tampermonkey 脚本的一些配置，通过为这些配置设置合适的值，可以指定该脚本的名称、版本、描述、生效站点等。

下面简单介绍下`UserScript Header`的一些参数定义。

*描述性配置*：

* `@name` 脚本的名称，就是在控制面板显示的脚本名称。
* `@namespace` 脚本的命名空间。
* `@version` 脚本的版本，主要是做版本更新时用。
* `@author` 作者。
* `@description` 脚本描述。
* `@homepage / @homepageURL / @website / @source` 作者主页，用于在 Tampermonkey 选项页面上从脚本名称点击跳转。请注意，如果`@namespace`标记以`http://`开头，此处也要一样。
* `@icon / @iconURL / @defaulticon` 低分辨率图标。
* `@icon64 / @icon64URL` 64x64 高分辨率图标。

*更新及问题报告相关配置*：

* `@updateURL` 检查更新的网址，需要定义`@version`。
* `@downloadURL` 更新下载脚本的网址，如果定义成`none`就不会检查更新。
* `@supportURL` 报告问题的网址。

*脚本生效页面配置*：

* `@include` 生效页面，可以配置多个，但注意这里并不支持 URL Hash。例如： 

    ```JavaScript
    // @include http://www.tampermonkey.net/*
    // @include http://*
    // @include https://*
    // @include *
    ```

* `@match` 约等于`@include`标签，可以配置多个。
* `@exclude` 不生效页面，可配置多个，优先级高于`@include`和`@match`。
* `@noframes` 此标记使脚本在主页面上运行，但不会在 iframe 上运行。
* `@nocompat` 由于部分代码可能是专门为专门的浏览器所写，通过此标记，Tampermonkey 会知道脚本可以运行的浏览器。例如：

    ```JavaScript
    // @nocompat Chrome
    ```

*脚本资源配置*：

* `@require` 附加脚本网址，相当于引入外部的脚本，这些脚本会在自定义脚本执行之前执行，比如引入一些必须的库(如 jQuery)，可以支持配置多个`@require`参数。例如：

    ```JavaScript
    // @require https://code.jquery.com/jquery-2.1.4.min.js
    // @require https://code.jquery.com/ui/1.12.1/jquery-ui.min.js
    ```

* `@resource` 预加载资源，可通过`GM_getResourceURL`和`GM_getResourceText`读取。
* `@connect` 允许被`GM_xmlhttpRequest`访问的域名，每行一个。

*脚本运行时间设置*：

* `@run-at` 脚本注入的时刻，如页面刚加载时，某个事件发生后等等。例如：

    - `document-start` 尽可能地早执行此脚本。
    - `document-body` DOM 的 body 出现时执行。
    - `document-end` DOMContentLoaded 事件发生时或发生后后执行。
    - `document-idle` DOMContentLoaded 事件发生后执行，即 DOM 加载完成之后执行，这是默认的选项。
    - `context-menu` 如果在浏览器上下文菜单（仅限桌面 Chrome 浏览器）中点击该脚本，则会注入该脚本。注意：如果使用此值，则将忽略所有`@include`和`@exclude`语句。

*GM 函数使用权限配置*：

* `@grant` 用于添加 GM 函数到白名单，相当于授权某些 GM 函数的使用权限。如果没有定义过 @grant 选项，Tampermonkey 会猜测所需要的函数使用情况。例如：

    ```JavaScript
    // @grant GM_setValue
    // @grant GM_getValue
    // @grant GM_setClipboard
    // @grant unsafeWindow
    // @grant window.close
    // @grant window.focus
    ```

### 2.3 插件 API

Tampermonkey 定义了一些 API，通过在脚本中使用这些 API 可以方便的完成一些操作。可以查看 [Tampermonkey API 文档](https://www.tampermonkey.net/documentation.php) 来查找需要的 API。

常用的一些 API 如下所示：

* `GM_log` 将日志输出到控制台。
* `GM_setValue` 将参数内容保存到 Storage 中。
* `GM_addValueChangeListener` 为某个变量添加监听，当这个变量的值改变时，就会触发回调。
* `GM_xmlhttpRequest` 发起 Ajax 请求。
* `GM_download` 下载某个文件到磁盘。
* `GM_setClipboard` 将某个内容保存到粘贴板。


