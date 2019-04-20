目前，文本下划线(`text-decoration`)虽然在各个浏览器上渲染的结果都一致，但是其可自定义性就只有 on/off 选项和三个位置选项，并不能满足更多的个性化需求。不过，我们也可以使用其他的方式来模拟下划线效果。

```
text-decoration: inherit | initial | none | blink | line-through | overline | underline;
```

### border
首先能想到的应该就是 border 属性了，因为它和下划线最接近。

```css
a[href] {
    border-bottom: 1px solid gray;
    text-decoration: none;
}
```

虽然使用border-bottom来模拟文本下划线可以让我们控制颜色、粗线还有样式，但这不是完美的。你可以在下图中看到，这些“下划线”和文本内容之间有一个非常大的空隙，差不多就像是在文本的下一行的位置！

![](http://cnd.qiniu.lin07ux.cn/markdown/1476161554874.png)

我们可以尝试通过给它一个display: inline-block;以及一个较小的line-height值来解决这个问题：

```css
a {
    display: inline-block;
    border-bottom: 1px solid gray;
    line-height: .9;
}
```

这确实可以让下划线离文本更近，但是它影响了正常的文本换行，如图所示。

![](http://cnd.qiniu.lin07ux.cn/markdown/1476161607288.png)

### box-shadow
使用`box-shadow`也能生成一个类似下划线的效果：

```css
a {
    box-shadow: 0 -1px gray inset;
}
```

但是，这和`border-bottom`有相同的问题，除了它的投影稍微和文本更靠近了一些。

### background-image
可能会想不到，使用`background-image`却能够很好的实现各种想要的下划线效果。

```css
a {
    background: linear-gradient(gray, gray) no-repeat;
    background-size: 100% 1px;
    background-position: 0 1.15em;
}
```

示例中，我们通过 CSS3 的线性渐变生成背景图，不必产生额外的网络请求；然后使用`backgroun-size`来控制“下划线”的尺寸；再通过`background-position`来控制“下划线”显示的位置。效果非常不错：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476161897805.png)

我们还可以做一个小的提升。注意到我们的下划线是如何穿过一些像 p 和 y 这样的字母的吗？如果在它们周围有一些适当的空白，岂不是更好吗？如果我们的背景是纯色的，我们可以设置两个`text-shadows`，阴影的颜色和我们的背景颜色一样：

```css
p {
    background: linear-gradient(gray, gray) no-repeat;
    background-size: 100% 1px;
    background-position: 0 1.15em;
    text-shadow: .05em 0 white, -.05em 0 white;
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476162025397.png)

由于背景图中也能够使用线性渐变，所以我们可以很方便的创建各种其他样式的下划线，比如虚线：

```css
p {
    background: linear-gradient(90deg,gray 66%, transparent 0) repeat-x;
    background-size: .2em 2px;
    background-position: 0 1em;
}
```

这样，可以通过色标来控制虚线的间隙，通过`background-size`来控制它们的尺寸。效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476162212864.png)

或者，还可以创建波浪效果：

```css
p {
    background: linear-gradient(-45deg, transparent 40%, red 0, red 60%, transparent 0) 0 1em,
                linear-gradient(45deg, transparent 40%, red 0, red 60%, transparent 0) .1em 1em;
    background-repeat: repeat-x;
    background-size: .2em .1em;
    text-shadow: .05em 0 white, -.05em 0 white;
}
```

这里用到了两个不同方向的线性渐变。效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476162431910.png)

### 转摘
[CSS秘密花园： 自定义下划线](http://www.w3cplus.com/css3/css-secrets/custom-underlines.html) 或 [CSS秘密花园： 自定义下划线](http://www.tuicool.com/articles/MZJ7bi3)

