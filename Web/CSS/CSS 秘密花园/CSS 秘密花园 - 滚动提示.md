转摘：[CSS秘密花园： 滚动提示](http://www.w3cplus.com/css3/css-secrets/scrolling-hints.html)

### 实现效果
滚动条主要是用来告诉用户当前显示的并不是所有的内容，滚动的话可以查看更多。但是，它们往往显示得很笨拙而且分散了用户的注意力，所以现代操作系统中已经开始对它们进行简化，通常是把它们完全隐藏起来，等到用户和可滚动的元素元素有实际交互的时候再出现。但是如果你没有和它进行交互的话，你根本不会发现内容其实有更多。

Google Reader 的 UX 设计师找到了一个非常优雅的方法来提示这一点：当有更多内容的时候，侧边栏的顶部或底部会显示一个细微的阴影：
![](http://cnd.qiniu.lin07ux.cn/2016-04-11%20Google%20Reader.png)

- 左图：可向上滚动
- 中间的图：可上下滚动
- 右图：可向下滚动

下面我们用 CSS 来实现这个效果。

### 实现方案
首先是一些简单的 HTML 骨架：

```html
<ul>
    <li>Ada Catlace</li>
    <li>Alan Purring</li>
    <li>Schrödingcat</li>
    <li>Tim Purrners-Lee</li>
    <li>WebKitty</li>
    <li>Json</li>
    <li>Void</li>
    <li>Neko</li>
    <li>NaN</li>
    <li>Cat5</li>
    <li>Vector</li>
</ul>
```

然后为 <ul> 元素添加一些 CSS 样式，让容器小于内容的高度，这样就能够实现滚动效果了：

```css
ul {
    overflow: auto;
    width: 10em;
    height: 8em;
    padding: .3em .5em;
    border: 1px solid silver;
}
li {
	line-height: 1.5;
}
```

现在，已经能够在 ul 元素中滚动了。我们在顶部应用一个阴影，通过一个径向渐变：

```css
ul {
    background: radial-gradient(at top, rgba(0,0,0,.2), transparent 70%) no-repeat;
    background-size: 100% 15px;
}
```

目前，阴影停在我们刚开始滚动的地方。这是默认情况下我们的背景图片显示的结果：它们的位置是相对于元素固定的，不考虑元素滚动了多少。

这也适用于带有`background-attachment: fixed`的图像。它们唯一的区别是，当页面本身滚动的时候，它们还留在原地。有没有什么办法可以让背景图像随着元素的内容一起滚动呢？

 CSS3 给`background-attachment`添加了一个新的关键字：`local`。但是，它给我们完全相反的结果：当我们一路滚动到顶部的时候我们得到了一个阴影，但是当我们向下滚动的时候，阴影消失了。
 
新的技巧是使用两个背景：一个用于生成阴影，一个基本上是白色矩形，用来覆盖阴影，充当蒙版的角色。阴影背景有默认的`background-attachment(scroll)`，因为我们希望它一直停留在原地。然后，蒙版背景的`background-attachment`为`local`，这样当我们滚动到顶部的时候它会覆盖阴影，然后当我们向下滚动的时候，它会随内容滚动，从而显示阴影。

我将使用线性渐变来创建蒙版矩形，和元素的背景保持同样的颜色（在我们的示例中，颜色是 white ）：

```css
ul {
    background: linear-gradient(white, white),
                radial-gradient(at top, rgba(0,0,0,.2),transparent 70%);
    background-repeat: no-repeat;
    background-size: 100% 15px;
    background-attachment: local, scroll;
}
```

此时可以看到它在滚动的不同阶段的效果。它似乎是产生了我们预期的效果，但是有一个很明显的缺点：当我们只是轻轻滚动的时候，阴影显示的方式非常地不连贯和别扭。有什么办法可以让它平滑一些吗？

我们可以利用我们的“蒙版”背景其实是一个线性渐变这个事实，把它转换成一个 white 到白色透明的`white ( hsla(0,0%,100%,0)`或`rgba(255,255,255,0)`的线性渐变，这样我们的阴影就可以非常平滑地显示了：

```css
background: linear-gradient(white, hsla(0,0%,100%,0)),
            radial-gradient(at top, rgba(0,0,0,.2),transparent 70%);
```

> 为什么是白色渐变而不是直接一个`transparent`呢？后者实际上是`rgba(0,0,0,0)`的别名，这样如果它是从不透明白色到透明黑色的过渡的话，渐变可能包含灰色阴影。如果浏览器是通过`premultiplied RGBA space`插入颜色，这应该不会发生。

阴影确实是逐渐显示的，和我们的期望相符。但是，它目前有一个非常严重的缺陷：当我们滚动到顶部的时候，它就不能像之前那样把阴影覆盖掉了。我们可以通过把 white 色标下移一些（ 15px 是精确值，和我们阴影的高度值一致）来解决这个事情，这样在开始褪色之前我们就可以得到一块纯白色的区域，正好将阴影完全覆盖。此外，我们需要增加蒙版背景的大小，让它比阴影大，否则我们就得不到渐变。确切的高度取决于我们希望效果的平滑度是多少（比如说，当我们滚动的时候，阴影显示的快慢？）经过试验， 50px 是一个合理值。最后的代码如下所示：

```css
background: linear-gradient(white 30%, transparent),
            radial-gradient(at 50% 0, rgba(0,0,0,.2),transparent 70%);
background-repeat: no-repeat;
background-size: 100% 50px, 100% 15px;
background-attachment: local, scroll;
```

当然，为了实现原始效果，我们需要再加两个渐变用于底部阴影和它的蒙版，但是逻辑基本上是一样的。

注：在 Chrome 49 中貌似没有效果？！！




