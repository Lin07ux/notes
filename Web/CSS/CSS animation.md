## 属性
### animation-fill-mode
`animation-fill-mode`这个 CSS 属性用来指定在动画执行之前和之后如何给动画的目标应用样式。有四个可选值：

```
animation-fill-mode: none | forwards | backwards | both;
```

* none 默认值。动画执行前后不改变任何样式。
* forwards 目标动画执行之后保持动画最后一帧的样式。最后一帧是哪个取决于`animation-direction`和`animation-iteration-count`。
    
    | animation-direction | animation-iteration-count | last keyframe |
    |---------------------|---------------------------|---------------|
    | normal              | even or odd               | 100% or to    |
    | reverse             | even or odd               | 0% or from    |
    | alternate           | even                      | 0% or from    |
    | alternate           | odd                       | 100% or to    |
    | alternate-reverse   | even                      | 100% or to    |
    | alternate-reverse   | odd                       | 0% or from    |

* backwards 目标在动画开始之前(`animation-delay`)保持在第一帧的样式。第一帧是哪个取决于`animation-direction`。

    |     animation-direction      | last keyframe |
    |------------------------------|---------------|
    | normal or alternate          | 0% or from    |
    | reverse or alternate-reverse | 100% or to    |

* both 动画元素在开始之前保持第一帧的样式，在结束之后保持最后一帧的样式。相当于同时设置了 backwards 和 forwards 效果。


另外，该属性可以应用多个参数，使用逗号隔开，各个参数应用于与次序相对应的动画名：

* `animation-fill-mode: none, backwards` 第一个动画不设置填充模式，第二个动画设置 backwards 填充模式。
* `animation-fill-mode: both, forwards, none` 第一个动画设置 both 填充模式，第二个动画设置 forwards 填充模式，第三个动画不设置填充模式。


为了加深理解，可以考虑如下的示例：

> 转摘：[如何理解animation-fill-mode及其使用？](https://segmentfault.com/q/1010000003867335)

```css
.box{
    transform: translateY(0);
}
.box.on{
    animation: move 1s;
}

@keyframes move{
    from{transform: translateY(-50px)}
    to  {transform: translateY( 50px)}
}
```

那么，对于`.box`元素，在加上`on`类名之前和之后的`translateY`属性的变化与时间的关系图如下：

> 横轴为表示 时间，为 0 时表示动画开始的时间，也就是向 box 加上 on 类名的时间，横轴一格表示 0.5s。如果设置了`animation-delay`，那么很轴的 0 就会有相应的变化。
> 
> 纵轴表示 translateY 的值，为 0 时表示 translateY 的值为 0，纵轴一格表示 50px。 

1. `animation-fill-mode: none`
    ![none](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1480571996893.png)

2. `animation-fill-mode: forwards`
    
    ![forwards](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1480572054242.png)

3. `animation-fill-mode: backwards`
    
    ![backwards](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1480572093181.png)

4. `animation-fill-mode: both`

    ![both](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1480572129979.png)


### animation-timing-function
该属性定义 CSS 动画在每一动画周期中执行的节奏。

可选值如下：

* ease
* ease-in
* ease-out
* ease-in-out
* linear
* <cubic-bezier(0.1, 0.7, 1.0, 0.1)>
* step-start
* step-end
* <steps(4, end)>

也可以同时设置多个属性值，使用逗号分隔，各个参数应用于与次序相对应的动画名，表示其独立的执行节奏。

需要注意的是：

* **对于关键帧动画来说，timing function 作用于一个关键帧周期而非整个动画周期**，即从关键帧开始开始，到关键帧结束结束。
* 对于逐帧动画，则是让每帧动画都瞬间完成。


## 小技巧
### 使用动画实现轮播
使用`animation-delay`属性可以实现简单的轮播效果。以下是一个四屏轮播的例子：

```scss
.slider__item {
    animation: ani6sinfinite linear both;
    
    @for $i from 1 to 4 {
        &:nth-child(#{$i}) {
            animation-delay: (-1+$i)*2s;
        }
    }
}

@keyframes ani {
 0%, 33.33% {opacity:1;visibility: visible;}
 33.34%, 100% {opacity:0;visibility: hidden;}
}
```

这样的一个问题在于，最终生成的代码比较多，而且不能在 HTML 中动态的增减轮播屏数。

See the Pen [listAni](http://codepen.io/Yetty/pen/BQWZWz/) by [Yetty](http://codepen.io/Yetty) on CodePen.

为了能够在鼠标移动到轮播图片上时，暂停动画，可以使用`animation-play-state: paused;`。也就是添加如下的设置：

```css
.slider:hover.slider__item{
    animation-play-state: paused;
}
```

### 有序动画
使用`animation-delay`可以实现一个有序动画，元素依次进行动画效果。

比如[京东2017海外招聘](http://jdc.jd.com/h5/jd-campus-2017/international/index.html)。效果如下：

![有序动画](http://7xkt52.com1.z0.glb.clouddn.com/UJBfQ3E.gif)

### 调试动画
将`animation-play-state`设置为`paused`，`animation-delay`设置为不同的负值，就可以查看动画在不同帧时的状态，便于进行动画调试。

## 注意
### 动画元素不要使用 display 来控制显示和隐藏
对于使用动画的元素，不能一开始设置为`display: none;`，然后在动画开始的时候设置为`display: block;`来展示元素，因为`display`不能应用动画。

如果要实现元素一个个应用动画显现出来，则需要将元素设置透明`opacity: 0;`，并设置`animation-fill-mode: backwards;`，这样就能让元素在动画进行之前，保持延迟时的状态(透明)，这样就不会出现意外了。

下面的两个图片，分别是设置了透明和设置了`display`的效果，可以看到设置`display`的时候(第二张图片)是先全部显示出来，然后才进行了动画效果的：

![opacity](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-13%20animate-opacity.gif)

![display](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-13%20animate-display.gif)

如果需要在动画结束后触发一些效果，可以绑定`webkitTransitionEnd`事件来触发。比如，我们需要在当前页面的动画都走完了之后，才能继续向下滑动：

```js
$slider.on("webkitTransitionEnd", 'li', function() {
    isSlide = false; // slide 动画结束 防止暴力切换
})
.on('touchstart', function(e) {
    var touch = e.touches[0];
    
    startX = touch.clientX;
    startY = touch.clientY;
    
    if (isSlide) {
        e.preventDefault();
    }
})
.on('touchmove', function(e) {
    e.preventDefault();

    var touch = e.touches[0],
        posY = touch.clientY,
        posX = touch.clientX;
  
    offsetY = posY - startY;
    offsetX = posX - startX;
    isMove = true;
})
.on("touchend", function(e) {
    if (!offsetY || Math.abs(offsetY) < 30 || !isMove) {
        return;
    }
    
    $this = $(e.target);
    if ($this[0].tagName != 'LI') {
        $this = $this.closest('li');
    }
  
    $this.siblings('li').removeClass('play'); // 防止出现重叠BUG
      
    if (offsetY > 0) { //向下滑动
        direction = "down";
        $next = $this.prev();
    } else { //向上滑动
        direction = "up";
        $next = $this.next();
    }

    var current = $this.index();
    if (current == 0 && direction == 'down') {
        return;
    }
    if (current == length - 1 && direction == 'up') {
        return;
    }

    if (direction == 'up') {
        $this.addClass('move-up');
    } else {
        $this.addClass('move-down');
    }
    
    isSlide = true;
    offsetY = 0;
    offsetX = 0;
    
    setTimeout(function() {
        $this.removeClass('play move-up move-down');
        $next.addClass('play').siblings('li').removeClass('play');
    }, 300);
});
```

在`touchstart`和`touchmove`都调用`e.preventDefault`方法，意思是阻止默认行为：第一个调用是为了防止在手指快速的上下滑动的时候触发`touchend`中的切换；第二个是 Android 4.0+ 的一个 BUG，就是有时候不会触发`touchmove`事件，加了这个后就能触发。不过加了这个后，相应位置的滚动就无效了。

转摘：[用CSS3动画](http://www.cnblogs.com/strick/p/5344826.html)


## 动画库
[animate.css](https://daneden.github.io/animate.css/)

这个动画库有如下特点：

1. 关键帧的选定，不仅有整数，还有小数，并且也不是说有规律的，几的倍数。
2. 速度曲线的选定，使用的是`cubic-bezier`函数，自定义贝塞尔曲线。这里有个[在线制作贝塞尔曲线的工具](http://cubic-bezier.com/#.17,.67,.83,.67)。
3. 全程使用`transform`属性来实现动画效果，使用了大量的`translate3d`、`rotate3d`、`scale3d`，这样能开启硬件加速。
4. 使用`perspective`属性设置镜头到元素平面的距离。





