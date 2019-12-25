> 转摘：[探索CSS单行文字居中，多行文字居左的实现方式](https://segmentfault.com/a/1190000021385669)

需求如下：

> 有一段动态文字，在页面操作时可能会被修改。如果其为多行，则需要靠左对齐；如果是单行，则居中对齐。

这个功能利用 JavaScript 来判断文字段落的高度添加相关的 CSS 类或样式是可以实现。但是由于文字内容是动态变化的，那么每次变化都需要调用 JavaScript 来更改，相对来说比较麻烦。如果考虑使用纯 CSS 来自动实现，那么效果会好很多，而且可以简化 JavaScript 的代码逻辑。

使用 CSS 方法有如下几种方式，兼容性各有不同，但使用的都是类似的思想：

### 1. first-line 伪类

默认情况下，文本是靠左对其的(除非有特别的定义文本方向)。当文本居中展示时，当其内容达到甚至超过一行时，第一行的展示效果其实是和居左对其是相同的了(因为第一行会填满整个容器盒子)。所以相当于只需要控制段落的第一行为居中展示即可。这可以使用`::first-line`伪类来实现：

```css
p {
    text-align: left;
}
p::first-line {
    text-align: center;
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/YRFZZnz.gif)

可以看到，这样设置还是有点小瑕疵的：当第一行铺满时，由于其仍旧是居中效果的，所以和下面的居左展示的行有一点没有对齐。这是由于一行的剩余空间不足一个字符的宽度时，该字符就会自动到下一行了，从而使得第一行的空白空间稍大一点。

解决方式也很简单，就是借用 CSS 中的`text-align-last`属性。该属性是用来设置文本段落最后一行的布局方式的。如果在`::first-line`伪类中使用`text-align-last`，那么就表示：既是首行、也是最后一行的内容设置为居中展示。也就是说，该设置将仅在段落只有一行时生效：

```css
p {
    text-center: left;
}
p::first-line {
    text-align-last: center;
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/3QBjErR.gif)

遗憾的是，`::first-line`([MDN](https://developer.mozilla.org/zh-CN/docs/Web/CSS/::first-line))伪类支持的样式非常有限(`text-align`相关属性不在其中)，上述实现方式仅在 Chrome 浏览器中生效：

![](http://cnd.qiniu.lin07ux.cn/markdown/1577266255405.png)

### 2. 父级居中 + 子级居左

默认情况下，内联元素的宽度是其实际内容的宽度，而且会随着内容的增加而增加，最大可以达到父级元素的宽度，也就是“包裹性”。同时，内联元素在父元素中的布局方式是由父元素的 CSS 来决定的。

利用这个特性，可以将一个`inline-block`子元素放在一个`block`父元素中，并设置分别父元素和子元素的布局方式：

```html
<div class="content">
    <span class="text">这段文字能不能这样判断一下，当文字不足一行时，让它居中显示，当文字超过一行就让它居左</span>
</div>
```

```css
.content {
    text-align: center;
}
.text {
    display: inline-block;
    text-align: left;
}
```

这样设置之后，里面的文本的展示效果按照如下规则来决定：

* 当文本较少时，`.text`的宽度跟随文本，此时`.text`元素整体会受父级元素的`text-align: center`影响而在父元素中居中展示，其自身设置的`text-align: left`无法表现出明显的效果；
* 当文本较多时，`.text`的宽度会达到父级元素的宽度，虽然父级元素设置了`text-align: center`，但`.text`元素整体已经无法表现出明显的居中效果了，而是和居左展示相同了。同时，设置`.text`元素的`text-align: left`可以让`.text`元素中的内容靠左展示。

这种方式兼容性非常好，浏览器都支持，只是这样会多了一层 HTML。

### 3. `width: fit-content` + `margin: auto`

如果能够将一个`block`元素的宽度跟随其实际内容，那么可以通过设置`margin: 0 auto`使得该元素在单行时居中、多行时居左。

使用`width: fit-content`可以实现元素收缩效果的同时，保持原本的`block`状态：

```css
p {
    width: -moz-fit-content; //火狐需要 -moz- 前缀
    width: fit-content;
    margin: 0 auto;
}
```

这样能够很好的实现需求，只是`fit-content`属性值支持性不是很好，在 IE 上不支持。

### 4. `display: table` + `margin: 0 auto`

虽然`fit-content`属性值在浏览器中的支持性不是很好，但是`display`属性的`table`值也具有相同的效果：既支持宽度跟随内部元素，又支持水平方向上 margin 居中：

```css
p {
    display: table;
    margin: 0 auto;
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/InEjQbB.gif)

这种方式的兼容性是非常好的，基本浏览器都支持。

### 5. `flex`和`grid`

对于最新推出的`flex`和`grid`布局来说，这种实现方式是很简单的。

在 flex 容器中，所有子项成为弹性项，包括纯文本节点(匿名盒子)，就好像包裹了一层，所以很容易通过`justify-content: center`实现居中，同时(匿名盒子)也跟随文本自适应宽度，当超过一行时，就按照默认的文本对齐方式。

```css
p {
    display: flex;
    justify-content: center;
}
```

grid 布局同理，只不过对齐方式需要通过`justify-items: center`：

```css
p {
    display: grid;
    justify-items: center;
}
```

相对于`flex`，`grid`的兼容性要差一些，所以尽量选取`flex`方式，至少移动端和 IE10(需要`-ms-`)是没问题的

