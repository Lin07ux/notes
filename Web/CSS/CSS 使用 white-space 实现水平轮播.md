转摘：[white-space:nowrap 的妙用](https://segmentfault.com/a/1190000004909362)

对于水平轮播效果，一般都是想办法将轮播子元素水平排列(float)，然后将轮播包含元素设置固定的宽度(有时候还需要水平`overflow-x:auto`)。

比如，对于如下的轮播结构：

```html
div.wrap
    div.row
        div.col
        div.col
        div.col
```

**一般做法**

首先设置`div.wrap`为一个固定宽度，设定`div.row`的宽度为`div.col宽度 * div.col的个数`，然后设置`div.col`为`float:left`或`display:inline-block`。
然后，对于`float:left`, `div.row`需要清除浮动；对于`display:inline-block`，需要压缩 html 或者为`div.row`设置`font-size:0`以去除`div.col`之间的水平间隙，后者也顺便去除了垂直方向的间隙（`line-height`为相对单位时，其最终值为`line-height值 * font-size`）。

这样就设定好轮播的基本样式了，之后就可以使用动画(和适当的延时)或者 JavaScript 来控制轮播效果。

**white-space 方法**

对于行内元素，当其父元素设置了`white-space: nowrap;`的时候，行内元素在遇到父元素的边界的时候，就不会自动换行了，仍旧保持一行的状态。所以我们可以使用这个属性，让子元素自动排在一行，而不需要设置`div.row`元素的宽度了。

CSS 代码如下：

```css
*{
    margin: 0;
    padding: 0;
}
.row{
    white-space: nowrap; // 让div.col放置在同一行
    background-color: rgb(0,0,0); // 背景色，以方便观察
    font-size: 0; // 去除水平+垂直间隙
}
.col{
    display: inline-block;
    *display: inline; // 兼容IE 6/7，模拟inline-block效果
    *zoom: 1; // 兼容IE 6/7，模拟inline-block效果
    width: 20%; 
    margin-right: 30px;
    height: 100px;
    background-color: red;
    font-size: 14px; // 覆盖父元素的font-size
    vertical-align: top; // 向上对齐，同时去除垂直间隙
}
```

这样做的好处在于：兼容性好（ IE5 都正常），无须计算宽度，可任意放多个`div.col`，而`div.row`的宽度等于其父元素的宽度（但 IE6 则会将`div.row`撑大，在 IE6 中，`width`如同`min-width`效果，`height`也是）。

