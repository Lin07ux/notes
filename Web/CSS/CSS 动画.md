CSS 动画库：[animate.css](https://daneden.github.io/animate.css/)

这个动画库有如下特点：
    1. 关键帧的选定，不仅有整数，还有小数，并且也不是说有规律的，几的倍数。
    2. 速度曲线的选定，使用的是`cubic-bezier`函数，自定义贝塞尔曲线。这里有个[在线制作贝塞尔曲线的工具](http://cubic-bezier.com/#.17,.67,.83,.67)。
    3. 全程使用`transform`属性来实现动画效果，使用了大量的`translate3d`、`rotate3d`、`scale3d`，这样能开启硬件加速。
    4. 使用`perspective`属性设置镜头到元素平面的距离。

对于使用动画的元素，不能一开始设置为`display: none;`，然后在动画开始的时候设置为`display: block;`，因为`display`不能应用动画。

如果要实现元素一个个应用动画显现出来，则需要将元素设置透明`opacity: 0;`，并设置`animation-fill-mode: backwards;`，这样就能让元素在动画进行之前，保持延迟时的状态(透明)，这样就不会出现意外了。

下面的两个图片，分别是设置了透明和设置了`display`的效果，可以看到设置`display`的时候(第二张图片)是先全部显示出来，然后才进行了动画效果的：
![opacity](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-13%20animate-opacity.gif)
![display](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-13%20animate-display.gif)

页面中动画可能有很多，如果需要在动画结束后触发一些效果，可以绑定`webkitTransitionEnd`事件来触发。比如，我们需要在当前页面的动画都走完了之后，才能继续向下滑动：

```js
$slider.on("webkitTransitionEnd", 'li', function() {
  isSlide = false; //slide动画结束 防止暴力切换
});
$slider.on('touchstart', function(e) {
  var touch = e.touches[0];
  startX = touch.clientX;
  startY = touch.clientY;
  if (isSlide) {
    e.preventDefault();
  }
}).on('touchmove', function(e) {
  var touch = e.touches[0],
    posY = touch.clientY,
    posX = touch.clientX;
  offsetY = posY - startY;
  offsetX = posX - startX;
  isMove = true;
  e.preventDefault();
}).on("touchend", function(e) {
  if (!offsetY || Math.abs(offsetY) < 30 || !isMove) {
    return;
  }
  $this = $(e.target);
  if ($this[0].tagName != 'LI') {
    $this = $this.closest('li');
  }
  var current = $this.index();
  $this.siblings('li').removeClass('play'); //防止出现重叠BUG
  if (offsetY > 0) { //向下滑动
    direction = "down";
    $next = $this.prev();
  } else { //向上滑动
    direction = "up";
    $next = $this.next();
  }

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


