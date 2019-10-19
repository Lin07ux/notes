> 转摘：[实现a元素href URL链接自动刷新或新窗口打开](https://www.zhangxinxu.com/wordpress/2019/10/a-href-target-window-blank-refresh/)

### 1. 需求

希望实现这样一个功能：点击一个链接，如果这个链接浏览器已经打开过，则刷新已经打开的那个窗口；如果这个链接没有打开过，则使用新窗口打开这个链接页面。

这个功能并不需要 JS 的参与，使用 HTML a 元素的`target`特性就可以实现这样的需求。

### 2. target 特性值

无论是`<a>`链接元素还是`<form>`表单元素都有一个名叫`target`的属性，支持的值包括下面这些：

* `_self` 默认值。当前浏览器上下文。
* `_blank` 通常是一个新的标签页，但是用户可以配置浏览器，是否在新窗口打开。
* `_parent` 当前浏览器上下文的的父级上下文，如果没有父级，则行为类似`_self`。
* `_top` 最顶级的浏览器上下文。如果没有祖先上下文环境，则行为类似`_self`。

实际上，`target`还有一个隐藏特性，那就是可以指定为具体的 URL 地址。例如：

```html
<a href="blank.html" target="blank.html">空白页</a>
```

此时，如果浏览器已经有标签页的地址是`blank.html`，则点击上面的链接并不会打开新窗口，是直接刷新已经打开的`blank.html`；如果浏览器中没有地址是`blank.html`的标签页，则此时`target`属性的行为表现类似`_blank`。

也就是说，要想实现链接地址自动刷新和新窗口打开的这个需求，直接设置链接元素和表单元素的`target`属性值为目标 URL 地址值就好了。

需要注意的是，URL 的查询参数不同也会被作为不同的目标，例如：`target="blank.html?s=1"`和`target="blank.html?s=2"`会认为是两个独立的页面，不会互相刷新。

[查看示例](https://www.zhangxinxu.com/study/201910/new-window-or-refresh-demo.php)

