## 基本
- 申明字符编码 `<mate charset="utf-8">`
- 使用最新版的 IE 和 Chrome `<mate http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">`
- 双核浏览器使用 webkit 内核 `<mate name="renderer" content="webkit">`
- 禁止百度的 Siteapp 对页面转码 `<mate http-equiv="Cache-Control" content="no-siteapp">`

## Viewport
经常在移动端会写成下面的方式，以禁止用户缩放，和设置页面的宽度为设备宽度：

`<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">`

其中，content 的参数的含义如下：

* `width` viewport 宽度(数值/device-width)
* `height` viewport 高度(数值/device-height)
* `initial-scale` 初始缩放比例
* `maximum-scale` 最大缩放比例
* `minimum-scale` 最小缩放比例
* `user-scalable` 是否允许用户缩放(yes/no)

iPhone 6 和 iPhone 6p 可以分别使用如下的设置：

`<meta name="viewport" content="width=375">`

`<meta name="viewport" content="width=414">`


## iOS 设备
- 页面添加到主屏幕后的标题 `<mate name="apple-mobile-web-app-title" content="设置标题">`
- 是否启用 webAPP 进入全屏模式 `<mate name="apple-mobile-web-app-capable" content="yes">`
- 状态栏的背景色 `<mate name="apple-mobile-web-app-statuss-bar-style" content="black-translucent">`
    content 参数可取如下值：
    * `default` 默认值。
    * `black` 状态栏背景是黑色。
    * `black-translucent` 状态栏背景是黑色半透明。
    如果设置为`default`或`black`，网页内容从状态栏底部开始；如果设置为`black-translucent`，网页内容充满整个屏幕，顶部会被状态栏遮挡。

- 禁止将数字识别为电话号码 `<mate name="format-detection" content="telephone=no">`

## iOS 图标
设置一个`link`标签的`rel`参数为`apple-touch-icon`，即可为 iOS 设备添加一个图标，图片自动处理成圆角和高光等效果；如果设置成`apple-touch-icon-precomposed`则禁止系统自动添加效果，直接显示设计原图。

- iPhone 和 iPod touch，默认 57×57px `<link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-57x57-precomposed.png" />`
- iPad，72×72px `<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/apple-touch-icon-72x72-precomposed.png" />`
- Retina iPhone 114×114px `<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/apple-touch-icon-114x114-precomposed.png" />`
- Retina iPad，144×144px `<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/apple-touch-icon-144x144-precomposed.png" />`
- iPhone 6+ 上是 180×180px，iPhone 6 是 120×120px。适配 iPhone 6+ `<link rel="apple-touch-icon-precomposed" sizes="180x180" href="retinahd_icon.png">`

## iOS 启动画面
iPad 启动画面不包括状态栏区域

`<link rel="apple-touch-startup-image" sizes="768x1004" href="/splash-screen-768x1004.png" />`

- iPad竖屏 768x1004px
- iPad 竖屏 1536×2008（Retina）
- iPad 横屏 1024×748（标准分辨率）
- iPad 横屏 2048×1496（Retina）

iPhone 和 iPod touch 的启动画面是包含状态栏区域的

`<link rel="apple-touch-startup-image" href="launch6.png" media="(device-width: 375px)">`

`<link rel="apple-touch-startup-image" href="launch6plus.png" media="(device-width: 414px)">`

- iPhone/iPod Touch 竖屏 320×480 (标准分辨率) 
- iPhone/iPod Touch 竖屏 640×960 (Retina)
- iPhone 5/iPod Touch 5 竖屏 640×1136 (Retina)
- iPhone 6 对应的图片大小是 750×1294
- iPhone 6 Plus 对应的是 1242×2148 。

添加智能 App 广告条 Smart App Banner（iOS 6+ Safari）

`<meta name="apple-itunes-app" content="app-id=myAppStoreID, affiliate-data=myAffiliateData, app-argument=myURL">`

## Android
Android Lollipop 中的 Chrome 39 增加 theme-color meta 标签，可以用来控制选项卡颜色。

`<meta name="theme-color" content="#db5945">`

## Windows 8
- Windows 8 磁贴颜色 `<meta name="msapplication-TileColor" content="#000"/>`
- Windows 8 磁贴图标 `<meta name="msapplication-TileImage" content="icon.png"/>`

## RSS
添加 RSS 订阅：

`<link rel="alternate" type="application/rss+xml" title="RSS" href="/rss.xml" />`

## 示例
```html
<!DOCTYPE html> <!-- 使用 HTML5 doctype，不区分大小写 -->
<html lang="zh-cmn-Hans"> <!-- 更加标准的 lang 属性写法 http://zhi.hu/XyIa -->
<head>
    <!-- 声明文档使用的字符编码 -->
    <meta charset='utf-8'>
    <!-- 优先使用 IE 最新版本和 Chrome -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <!-- 页面描述 -->
    <meta name="description" content="不超过150个字符"/>
    <!-- 页面关键词 -->
    <meta name="keywords" content=""/>
    <!-- 网页作者 -->
    <meta name="author" content="name, email@gmail.com"/>
    <!-- 搜索引擎抓取 -->
    <meta name="robots" content="index,follow"/>
    <!-- 为移动设备添加 viewport -->
    <meta name="viewport" content="initial-scale=1, maximum-scale=3, minimum-scale=1, user-scalable=no">
    <!-- width=device-width 会导致 iPhone 5 添加到主屏后以 WebApp 全屏模式打开页面时出现黑边 http://bigc.at/ios-webapp-viewport-meta.orz -->

    <!-- iOS 设备 begin -->
    <meta name="apple-mobile-web-app-title" content="标题">
    <!-- 添加到主屏后的标题（iOS 6 新增） -->
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <!-- 是否启用 WebApp 全屏模式，删除苹果默认的工具栏和菜单栏 -->

    <meta name="apple-itunes-app" content="app-id=myAppStoreID, affiliate-data=myAffiliateData, app-argument=myURL">
    <!-- 添加智能 App 广告条 Smart App Banner（iOS 6+ Safari） -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <!-- 设置苹果工具栏颜色 -->
    <meta name="format-detection" content="telphone=no, email=no"/>
    <!-- 忽略页面中的数字识别为电话，忽略email识别 -->
    <!-- 启用360浏览器的极速模式(webkit) -->
    <meta name="renderer" content="webkit">
    <!-- 避免IE使用兼容模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 针对手持设备优化，主要是针对一些老的不识别viewport的浏览器，比如黑莓 -->
    <meta name="HandheldFriendly" content="true">
    <!-- 微软的老式浏览器 -->
    <meta name="MobileOptimized" content="320">
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <!-- UC强制全屏 -->
    <meta name="full-screen" content="yes">
    <!-- QQ强制全屏 -->
    <meta name="x5-fullscreen" content="true">
    <!-- UC应用模式 -->
    <meta name="browsermode" content="application">
    <!-- QQ应用模式 -->
    <meta name="x5-page-mode" content="app">
    <!-- windows phone 点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <!-- iOS 图标 begin -->
    <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-57x57-precomposed.png"/>
    <!-- iPhone 和 iTouch，默认 57x57 像素，必须有 -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/apple-touch-icon-114x114-precomposed.png"/>
    <!-- Retina iPhone 和 Retina iTouch，114x114 像素，可以没有，但推荐有 -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/apple-touch-icon-144x144-precomposed.png"/>
    <!-- Retina iPad，144x144 像素，可以没有，但推荐有 -->
    <!-- iOS 图标 end -->

    <!-- iOS 启动画面 begin -->
    <link rel="apple-touch-startup-image" sizes="768x1004" href="/splash-screen-768x1004.png"/>
    <!-- iPad 竖屏 768 x 1004（标准分辨率） -->
    <link rel="apple-touch-startup-image" sizes="1536x2008" href="/splash-screen-1536x2008.png"/>
    <!-- iPad 竖屏 1536x2008（Retina） -->
    <link rel="apple-touch-startup-image" sizes="1024x748" href="/Default-Portrait-1024x748.png"/>
    <!-- iPad 横屏 1024x748（标准分辨率） -->
    <link rel="apple-touch-startup-image" sizes="2048x1496" href="/splash-screen-2048x1496.png"/>
    <!-- iPad 横屏 2048x1496（Retina） -->

    <link rel="apple-touch-startup-image" href="/splash-screen-320x480.png"/>
    <!-- iPhone/iPod Touch 竖屏 320x480 (标准分辨率) -->
    <link rel="apple-touch-startup-image" sizes="640x960" href="/splash-screen-640x960.png"/>
    <!-- iPhone/iPod Touch 竖屏 640x960 (Retina) -->
    <link rel="apple-touch-startup-image" sizes="640x1136" href="/splash-screen-640x1136.png"/>
    <!-- iPhone 5/iPod Touch 5 竖屏 640x1136 (Retina) -->
    <!-- iOS 启动画面 end -->

    <!-- iOS 设备 end -->
    <meta name="msapplication-TileColor" content="#000"/>
    <!-- Windows 8 磁贴颜色 -->
    <meta name="msapplication-TileImage" content="icon.png"/>
    <!-- Windows 8 磁贴图标 -->

    <link rel="alternate" type="application/rss+xml" title="RSS" href="/rss.xml"/>
    <!-- 添加 RSS 订阅 -->
    <link rel="shortcut icon" type="image/ico" href="/favicon.ico"/>
    <!-- 添加 favicon icon -->

    <title>标题</title>
</head>
```


