> 转摘：[link rel=alternate 网站换肤功能最佳实现](https://www.zhangxinxu.com/wordpress/2019/02/link-rel-alternate-website-skin/)

### 1. JavaScript 动态修改 class 或 css

网站整站换肤功能的实现，一般能想到的方式有如下两种方式：

1.	一个全局`class`控制样式切换；
2.	改变皮肤`link`元素的`href`地址。

比如，有如下 HTML 文件：

```html
<link id="skinLink" href="skin-default.css" rel="stylesheet" type="text/css">
```

换皮肤的时候 JS 改变`href`属性值：

```javascript
skinLink.href = 'skin-red.css';
```

这两种方式都不完美：

* 全局`class`控制样式提高了样式优先级，如果换肤样式很多，代码会非常啰嗦，不利于维护；
* 使用 JS 改变`href`属性会带来加载延迟，样式切换不流畅，体验不佳。

### 2. link rel=alternate 浏览器原生实现

实际上，浏览器有原生特性，非常适合实现网站换肤功能。此方法借助 HTML `rel`属性的`alternate`属性值实现。示意 HTML 如下：

```html
<link href="reset.css" rel="stylesheet" type="text/css">
                
<link href="default.css" rel="stylesheet" type="text/css" title="默认">
<link href="red.css" rel="alternate stylesheet" type="text/css" title="红色">
<link href="green.css" rel="alternate stylesheet" type="text/css" title="绿色">
```

上面 4 个`<link>`元素，加载了 3 种不同皮肤性质的 CSS 样式文件加载。`rel="stylesheet"`的`<link>`如果有`title`属性并有值，性质上就变成了一个可以使用 js 控制其渲染或者不渲染的特殊元素了。

使用 js 控制的时候：修改`<link>`元素 DOM 对象的`disabled`值为`false`，可以让默认不渲染的 CSS 开始渲染。注意，必须是 DOM 元素对象的`disabled`属性，而不是 HTML 元素的`disabled`属性，`<link>`元素是没有`disabled`属性的。

```JavaScript
// 渲染red.css这个皮肤
document.querySelector('link[href="red.css"]').disabled = false;
```

因此，要实现换肤功能，只要在页面上方几个换肤按钮，点击的时候改变对应`<link>`元素 DOM 对象的`disabled`值就可以了。

使用这种方式换肤有如下优点：

1. 兼容性非常好。IE9+（IE8 理论也支持），Chrome 和 Firefox 均支持这种更原生的换肤效果实现。
2. 语义非常好。用户，开发者，尤其搜索引擎或者其他辅助阅读设备能够准确识别网站还有其他替换CSS样式。（alternate 的语义就是可替换的）。
3. 交互体验更好。`rel=alternate方`法实现的换肤功能在网站样式变换的时候是瞬间切换，完全无感知。因为浏览器已经把换肤的 CSS 文件预加载好了，比 JS 改变`href`地址的体验要更好。配合 http 2.0，几乎可以说是完美无瑕的解决方案了。



