> 转摘：[CSS scroll-behavior和JS scrollIntoView让页面滚动平滑](https://www.zhangxinxu.com/wordpress/2018/10/scroll-behavior-scrollintoview-%E5%B9%B3%E6%BB%91%E6%BB%9A%E5%8A%A8/)

点击 HTML 页面的锚点或使用非鼠标方式滚动页面时，页面会立即滚动到对应的位置处，如果需要有个平滑的动画效果，则可以使用 CSS 的`scroll-behaviour`属性或者 JavaScript 中的`scrollIntoView()`方法。

### scroll-behaviour

`scroll-behavior`属性的初始值是`auto`，如果设置滚动容易元素的`scroll-behavior`的属性值为`smooth`则可以让容器（非鼠标手势触发）的滚动变得平滑。

**凡是需要滚动的地方都加一句`scroll-behavior: smooth;`就好了！**

在 PC 浏览器中，网页默认滚动是在`<html>`标签上的，移动端大多数在`<body>`标签上，于是加上这么一句：

```css
html, body {
    scroll-behavior: smooth;
}
```

这样就可以让页面的滚动效果很平滑了。

### scrollIntoView

DOM 元素的`scrollIntoView()`方法是一个 IE6 浏览器也支持的原生 JS API，可以让元素进入视区，通过触发滚动容器的定位实现。

随着 Chrome 和 Firefox 浏览器开始支持 CSS `scroll-behavior`属性，顺便对`scrollIntoView()`方法进行了升级，使支持更多参数，其中一个参数就是可以使滚动平滑。

语法如下：

```JavaScript
target.scrollIntoView({
    behavior: "smooth"
});
```

随便打开一个有链接的页面，把首个链接滚动到屏幕外，然后控制台输入类似下面代码，就可以看到页面平滑滚动定位了：

```JavaScript
document.links[0].scrollIntoView({
    behavior: "smooth"
});
```

如果网页已经通过 CSS 设置了`scroll-behavior: smooth;`声明，直接执行`target.scrollIntoView()`方法就会有平滑滚动，无需再额外设置`behavior`参数。

> 升级后的`scrollIntoView()`方法除了支持`behavior`参数，还支持`block`、`inline`等参数，有兴趣可以参阅 [MDN 相关文档](https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView)。


