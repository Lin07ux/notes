> 转摘：[如何更好的去除谷歌浏览器中input自动填充背景？](https://segmentfault.com/a/1190000021296139)

浏览器为 input 提供了自动填充的功能，但是自动填充之后输入框背景会自动变成淡紫色(或者橙黄色，或者其他颜色)。而这种自动填充的颜色经常会和页面效果不一致，就需要对其进行替换。

## 一、自动填充的背景色是如何产生的？

Chrome 浏览器为输入框自动填充之后，会为输入框激活`-internal-autofill-selected`伪类，对应如下浏览器预设 CSS 样式：

```css
input:-internal-autofill-selected {
  background-color: rgb(232, 240, 254) !important;
  background-image: none !important;
  color: rgb(0, 0, 0) !important;
}
```

可以看到，这个伪类会对背景和前景色都设置了固定颜色，并使用了`!important`标识符，这样就会将输入框设置的样式给覆盖掉了。

![](http://cnd.qiniu.lin07ux.cn/markdown/3163271686-fa6130675bc17add_articlex.gif)

## 二、解决方法

如果直接通过输入框的`-internal-autofill-selected`伪类进行样式覆盖，并不会其作用，可能是因为这个伪类是浏览器内部使用的，无法在外部定义使用。这样就需要使用其他的方法来解决了。

### 2.1 关闭自动填充

由于是因浏览器的自动填充功能导致的，那么将自动填充关闭就不会出现这个问题了：

```html
<input autocomplete="off">
```

当然，直接不使用自动填充可能也不太方便，使用 js 自定义自动填充功能也并非很好的解决方法。

### 2.2 内阴影覆盖

背景色无法覆盖，那么可以考虑使用内阴影进行覆盖：

```css
input {
    box-shadow: 0 0 0px 1000px white inset;
}
```

此时效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1576565512132.png)

大部分情况下都可以使用这种方式来解决，只要使用和设计时的背景色相同的内阴影，就能够很好的屏蔽背景色。

但是对于输入框背景透明的设计来说，这种方式就不合适了。

### 3. 利用`background-clip: content-box;`

既然无法覆盖背景，那么就将其裁掉。

背景色默认是渲染到`padding-box`的，可以设置`background-clip: content-box;`之渲染到`content-box`，这样背景就看不到了(当然还需要同时指定高度为 0)。

```css
input {
    height: 0;
    padding: 1.2em .5em;
    background-clip: content-box;
}
```

这时自动填充时的背景就已经消失了。

不过这样的话还有一个小问题，填充的文字颜色也是无法直接修改的(默认为黑色)，也是因为自动填充时触发的伪类的`color`属性的设置。这里可以使用伪类`::first-line`来覆盖颜色：

```css
input::first-line {
    color: #fff;
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1576572574762.png)

### 4. 利用`animation-fill-mode: forwards;`

设置`animation-fill-mode: forwards;`后，动画效果会一直停留在最后一帧，这个已经和默认的样式不是一个维度了，不管设置了什么样式，都会保留最后一帧的状态。

比如，为输入框设置一个动画，就能重置自动填充的背景色了：

```css
input {
  animation: resetBg .1s forwards;
}

@keyframes resetBg {
  to {
    color: #fff;
    background: transparent;
  }
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1576573103252.png)

这种方式相对来说更加方便，基本无副作用。

