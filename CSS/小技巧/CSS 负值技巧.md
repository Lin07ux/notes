> 转摘：[你所不知道的 CSS 负值技巧与细节](https://www.cnblogs.com/coco1s/p/11319676.html)

### 1. 使用负值 outline-offset 实现加号

`outline`可以设置一个元素的外部光圈效果，`outline-offset`是用来控制该效果与元素的距离的，而且可以设置为负值。当修改`outline-offset`到一个合适的负值时，`outline`边框就会向内缩进为一个加号。

要使用负的`outline-offset`生成一个加号有一些简单的限制：

1. 容器得是个正方形
2. `outline`边框本身的宽度不能太小
3. `outline-offset`的取值范围为：`-(容器宽度的一半 + outline宽度的一半) < x < -(容器宽度的一半 + outline宽度)`


比如：

```css
div {
    width: 200px; height: 200px;
    outline: 20px solid #000;
    outline-offset: -118px;
}
```

为该效果添加动画效果，大概是这样：

![](http://cnd.qiniu.lin07ux.cn/markdown/6BfQ3iq.gif)

### 2. box-shadow 单侧投影

大部分时候，`box-shadow`都是用来生成一个两侧的投影，或者一个四侧的投影，如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1565333684434.png)

如果要生成一个单侧投影，就需要将阴影的模糊半径与负的扩张半径一致，并将阴影向偏向的一侧偏移。如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1565333766642.png)


### 3. 使用 scale(-1) 实现翻转

通常要实现一个元素的 180° 翻转，会使用`transform: rotate(180deg)`，而使用`transform: scale(-1)`也可以达到同样的效果。

如下所示：

```css
.scale {
    transform: scale(1);
    animation: scale 10s infinite linear;
}

@keyframes scale{
    50% {
        transform: scale(-1);
    }  
    100% {
        transform: scale(-1);
    }
}
```

效果如下(效果上面第一行是使用了`transform: rotate(180deg)`的效果)：

![](http://cnd.qiniu.lin07ux.cn/markdown/3yiEFzv.gif)

### 4. 动画延时的负值立刻开始动画


CSS 动画及过渡均提供了延时属性，可以延迟动画的进行。类似如下动画：

```html
<div class="g-container">
    <div class="item"></div>
    <div class="item"></div>
    <div class="item"></div>
</div>
```

```css
.item {
    transform: rotate(0) translate(-80px, 0) ;
}

.item:nth-child(1) {
    animation: rotate 3s infinite linear;
}

.item:nth-child(2) {
    animation: rotate 3s infinite 1s linear;
}

.item:nth-child(3) {
    animation: rotate 3s infinite 2s linear;
}


@keyframes rotate {
    100% {
        transform: rotate(360deg) translate(-80px, 0) ;
    }
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/uANZZvJ.gif)

如果想去掉这个延迟，希望在一进入页面的时候，3 个球就是同时运动的。这时只需要把正向的`animation-delay`改成负向的即可：

```css
.item:nth-child(1) {
    animation: rotate 3s infinite linear;
}

.item:nth-child(2) {
    animation: rotate 3s infinite -1s linear;
}

.item:nth-child(3) {
    animation: rotate 3s infinite -2s linear;
}
```

这里有个小技巧，被设置了`animation-dealy`为负值的动画会立刻执行，开始的位置是其动画阶段中的一个阶段。所以，动画在一开始的时刻就是下面这样：

![](http://cnd.qiniu.lin07ux.cn/markdown/uM7B3iE.gif)

以上述动画为例，一个被定义执行 3s 的动画，如果`animation-delay`为 -1s，起点相当于正常执行时，第 2s（3-1）时的位置。

### 5. 负值 margin 设置等高列

在 Flexbox 布局规范还没流行之前，实现多行等高布局还是需要下一番功夫的。其中一种方法便是使用正`padding`和负`margin`相消的方法。

比如，有如下一个布局，左右两栏的内容都是不确定的，也就是高度未知。但是希望无论左侧内容较多还是右侧内容较多，两栏的高度始终保持一致：

![](http://cnd.qiniu.lin07ux.cn/markdown/1565334626498.png)

其中一种 Hack 办法便是使用一个很大的正`padding`和相同的负`margin`相消的方法填充左右两栏：

```css
.g-left {
  ...
  padding-bottom: 9999px;
  margin-bottom: -9999px;
}

.g-right {
  ...
  padding-bottom: 9999px;
  margin-bottom: -9999px;
}
```

这样就可以做到，无论左右两栏高度如何变化，高度较低的那一栏都会随着另外一栏变化。

