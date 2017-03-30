### iOS 10.2.1 fixed 元素被遮盖

当给 fixed 元素的父元素设置了`overflow: hidden;`，且 fixed 元素有部分被定位到其父元素的外面的时候，fixed 元素会被父元素遮盖掉，超出部分无法显示出来。

这个问题在 Android 上和 Mac Safari 上不存在。


### transform 导致 fiexd 元素失效

在 Chrome 和 Opera 浏览器下，使用 CSS3 的`transform: translate(0, 0)`转化位置节点，其所有使用`position: fixed`定位的子孙节点的定位功能均无效。

### IE 10 屏幕的宽度和视口（viewport）的宽度的问题

Windows 8 和 Windows Phone 8 中的 IE 10 并没有对 屏幕的宽度 和 视口（viewport）的宽度 进行区分，这就导致 Bootstrap 中的媒体查询并不能很好的发挥作用。为了解决这个问题，可以引入下面列出的这段 CSS 代码暂时修复此问题：

```css
@-ms-viewport { width: device-width; }
```

然而，这样做并不能对 Windows Phone 8 Update 3 (a.k.a. GDR3) 版本之前的设备起作用，由于这样做将导致 Windows Phone 8 设备按照桌面浏览器的方式呈现页面，而不是较窄的“手机”呈现方式，为了解决这个问题，还需要加入以下 CSS 和 JavaScript 代码来化解此问题：

```css
@-webkit-viewport   { width: device-width; }
@-moz-viewport      { width: device-width; }
@-ms-viewport       { width: device-width; }
@-o-viewport        { width: device-width; }
@viewport           { width: device-width; }
```

```JavaScript
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement('style')
    
    msViewportStyle.appendChild(
        document.createTextNode('@-ms-viewport{width:auto!important}')
    )
    
    document.querySelector('head').appendChild(msViewportStyle)
}
```

### Safari 对百分比数字凑整的问题

OS X 上搭载的 v7.1 以前 Safari 和 iOS v8.0 上搭载的 Safari 浏览器的绘制引擎对于处理`.col-*-1`类所对应的很长的百分比小数存在 bug。也就是说，如果你在一行（row）之中定义了12个单独的列（`.col-*-1`），你就会看到这一行比其他行要短一些。除了升级 Safari/iOS 外，有以下几种方式来应对此问题：

* 为最后一列添加`.pull-right`类，将其暴力向右对齐；
* 手动调整百分比数字，让其针对 Safari 表现更好（这比第一种方式更困难）。


