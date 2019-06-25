`margin`属性用于控制**元素之间的间距**，一般不属于元素自身大小范围。它是一个和成属性，可以分解成四个子属性，分别设置值：

* `margin-top`
* `margin-right`
* `margin-botton`
* `margin-left`

在合成写法时，可以设置一个、两个、三个、四个值：

* 设置一个值时四个子属性采用相同的设置；
* 设置两个值时`margin-top`和`margin-botton`采用第一个值，`margin-right`和`margin-left`采用第二个值；
* 设置三个值时，`margin-top`采用第一个值，`margin-right`和`margin-left`采用第二个值，`margin-botton`采用第三个值；
* 设置四个值时，按照上、右、下、左分别使用这四个值。

## 可选值

```
auto | <num>px | <num>%
```

## auto 的计算方式

设置`margin`的值为`auto`时，浏览器会自动计算元素的各个方向的间距，但是在不同的上下文中，会有不同的效果。

### BFC 的 auto

CSS 文档中有如下的描述：

> If both margin-left and margin-right are auto, their used values are equal, causing horizontal centring.
> —— [CSS2 Visual formatting model details: 10.3.3](https://www.w3.org/TR/CSS2/visudet.html#blockwidth)
>
> If margin-top, or margin-bottom are auto, their used value is 0.
> —— [CSS2 Visual formatting model details: 10.6.3](https://www.w3.org/TR/CSS2/visudet.html#Computing_heights_and_margins)

也就是说：

* 在块格式化上下文中，如果`margin-left`和`margin-right`都是`auto`，则它们的表达值相等，从而导致元素的水平居中。(这里的计算值为元素剩余可用剩余空间的一半)。
* 而如果`margin-top`和`margin-bottom`都是`auto`，则他们的值都为 0，也就无法造成垂直方向上的居中。

### FFC/GFC 的 auto

FFC 和 GFC 也就是指 flex 布局和 grid 布局，在这两种布局方式中，margin 的 auto 值会有与 BFC 中完全不同的计算方式：

> Prior to alignment via justify-content and align-self, any positive free space is distributed to auto margins in that dimension.
> —— [CSS Flexible Box Layout Module Level 1 -- 8.1. Aligning with auto margins](https://www.w3.org/TR/2018/CR-css-flexbox-1-20181119/#auto-margins)
> 
> auto margins absorb positive free space prior to alignment via the box alignment properties.
> —— [CSS Grid Layout Module Level 1 -- 10.2. Aligning with auto margins](https://www.w3.org/TR/css-grid-1/#auto-margins)

在 flex 格式化上下文中，设置了`margin: auto`的元素，在通过`justify-content`和`align-self`进行对齐之前，任何正处于空闲的空间都会分配到该方向的自动 margin 中去，而 grid 布局中，`margin: auto`元素将会尽可能的占用剩余的空间。

所以，在 FFC 和 GFC 中，`margin: auto`会将元素在水平和垂直方向上都居中。

CSS 文档中还有如下一段提示：

> Note: If free space is distributed to auto margins, the alignment properties will have no effect in that dimension because the margins will have stolen all the free space left over after flexing.
> —— [CSS Flexible Box Layout Module Level 1 -- 8.1. Aligning with auto margins](https://www.w3.org/TR/2018/CR-css-flexbox-1-20181119/#auto-margins)

意思是，如果任意方向上的可用空间分配给了该方向的自动 margin ，则对齐属性`justify-content`/`align-self`在该维度中不起作用，因为 margin 将在排布后窃取该纬度方向剩余的所有可用空间。

也就是使用了自动 margin 的 flex 子项目，它们父元素设置的`justify-content`以及它们本身的`align-self`将不再生效，也就是这里存在一个优先级的关系。

## 使用

### 1. 水平垂直居中

```scss
.parent {
    width: 100vw;
    height: 100vh;
    
    display: flex;
    // display: grid;
    // display: inline-flex;
    // display: inline-grid;
    
    .children {
        margin: auto;
    }
}
```

### 2. 使用自动 margin 实现 flex 布局下的 space-around

flex 的`space-around`就是将剩余空间分布在所有子元素的周边，根据 FFC 下的`margin: auto`特性，可以实现如下：

```html
<ul class="g-flex">
    <li>liA</li>
    <li>liB</li>
    <li>liC</li>
    <li>liD</li>
    <li>liE</li>
</ul>
```

```scss
.g-flex {
    display: flex;
    // justify-content: space-around;
    
    li {
        margin: auto;
    }
}
```

效果：

![](http://cnd.qiniu.lin07ux.cn/markdown/1558921726363.png)

### 3. 使用自动 margin 实现 flex 布局下的 space-between

根据上面的示例可知，`margin: auto`会将剩余空间分布到 flex 布局中每个子元素的四周，而如果控制首尾两个子元素的左或右边距，那么就可以实现`space-between`效果了：

```html
<ul class="g-flex">
    <li>liA</li>
    <li>liB</li>
    <li>liC</li>
    <li>liD</li>
    <li>liE</li>
</ul>
```

```scss
.g-flex {
    display: flex;
    // justify-content: space-between;
    
    li {
        margin: auto;
    }
     
    li:first-child {
        margin-left: 0;
    }
     
    li:last-child {
        margin-right: 0;
    }
}
```

### 4. 使用自动 margin 实现不规则两端对齐布局

flex 布局中，可以设置全部元素为自动 margin，使其平分剩余空间，自然也可以设置特定的一个或多个元素为自动 margin，使这些元素而不是全部元素平分剩余空间，从而可以实现不一样的布局：

```html
<ul class="g-nav">
    <li>导航A</li>
    <li>导航B</li>
    <li>导航C</li>
    <li>导航D</li>
    <li class="g-login">登陆</li>
</ul>
```

```scss
.g-nav {
    display: flex;
    
    .g-login {
        margin-left: auto;
    }
}
```

### 自动 margin 实现粘性 footer 布局

页面存在一个 footer 页脚部分，如果整个页面的内容高度小于视窗的高度，则 footer 固定在视窗底部，如果整个页面的内容高度大于视窗的高度，则 footer 正常流排布（也就是需要滚动到底部才能看到 footer），算是粘性布局的一种。

![](http://cnd.qiniu.lin07ux.cn/markdown/b739286fafa6.gif)

这个需求使用 flex `justify-content: space-between`可以很好的解决，同理使用`margin-top: auto`也非常容易完成：

```html
<div class="g-container">
    <div class="g-real-box">
        ...
    </div>
    <div class="g-footer"></div>
</div>
```

```css
.g-container {
    height: 100vh;
    display: flex;
    flex-direction: column;
}
 
.g-footer {
    margin-top: auto;
    flex-shrink: 0;
    height: 30px;
    background: deeppink;
}
```

## 转摘

[探秘 flex 上下文中神奇的自动 margin](https://www.cnblogs.com/coco1s/p/10910588.html)


