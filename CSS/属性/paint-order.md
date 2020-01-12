> 转摘：[CSS paint-order祝大家元旦快乐](https://www.zhangxinxu.com/wordpress/2020/01/css-paint-order/)

CSS `paint-order`作用在 SVG 图形元素上，设置是先绘制描边还是先绘制填充。

在 SVG 中，描边和填充同时都有设置是很常见的，而 SVG 图形的描边和 CSS 中的`-webkit-text-stroke`描边是一样的，都是居中描边。

![](http://cnd.qiniu.lin07ux.cn/markdown/1578475771647.png)

如果描边粗一些，文字原本颜色说不定就看不见了，那就不是描边效果，是加粗效果了，并不符合预期，有没有什么办法可以实现外描边效果呢？对于 CSS 文本可以使用`text-shadow`代替描边，在 SVG 中就可以使用本文这里的`paint-order`控制描边和填充的顺序。

### 1. 语法

`paint-order`属性的语法如下：

```
paint-order: normal | [ fill || stroke || markers ]
```

其中：

* `normal` 默认值。绘制顺序是`fill stroke markers`。图形绘制是后来居上的，因此默认是描边覆盖填充，标记覆盖描边。
* `fill` 先填充，然后再描边或标记，和默认值效果相同。
* `stroke` 先描边，然后再填充或标记。
* `markers` 先标记，然后再填充或描边。

> `||`表示或者，意味着属性值可以共存，

合法写法示例：

```css
paint-order: normal;

paint-order: fill;
paint-order: stroke;
paint-order: markers;

paint-order: fill markers;
paint-order: markers stroke;
...

paint-order: fill markers stroke;
paint-order: markers fill stroke;
paint-order: stroke markers fill;
...
```

### 2. 作用元素

`paint-order`可以作用在 SVG 的以下这些元素上：

* `<circle>`
* `<ellipse>`
* `<line>`
* `<path>`
* `<polygon>`
* `<polyline>`
* `<rect>`
* `<text>`
* `<textPath>`
* `<tspan>`

`paint-order`也可以直接作为 XML 属性用在上面这些元素上。

### 3. 兼容性

目前(2020-01-01)该属性的兼容性还比较好，基本主流的浏览器都支持了，但是 IE 还是不支持：

![](http://cnd.qiniu.lin07ux.cn/markdown/1578476307896.png)


