### 动画状态
动画并不总是页面加载的时候就开始。往往，我们都想通过用户的操作来触发动画，比如鼠标的悬浮(`:hover`)或者鼠标按下时(`:active`)。默认情况下，如果我们结束动画，那么元素就会突然跳到最初始状态，会影响用户的体验。

比如，假设我们一个很长的景观照片，如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476285354163.png)

但我们要显示的空间只有`150px X 150px`的正方形。解决这个问题的一个方法就需要使用动画：默认显示图像的从图像的左边缘开始，当用户有交互行为时，图片慢慢向左滚动(例如，鼠标悬浮在图片上时)。我们使用单个元素，并且给他设置一个背景图片，通过动画修改`background-position`值来实现。

```css
.panoramic {
    width: 150px; 
    height: 150px;
    background: url("img/naxos-greece.jpg");
    background-size: auto 100%;
    animation: panoramic 10s linear infinite alternate;
}

@keyframes panoramic {
    to {
        background-position: 100% 0;
    }
}
```

目前，它看起来如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1476285475261.png" width="180"/>

它能正常的运行。效果看起来就像一个全景，从左向右看。然而，而面加载动画就被触发，这样一来可能会分散用户的集中力。我们可以设置为当用户鼠标悬浮在图片上时，才触发动画，这样看起来更形象。

```css
.panoramic {
    width: 150px;
    height: 150px;
    background: url("img/naxos-greece.jpg");
    background-size: auto 100%;
}
.panoramic:hover,
.panoramic:focus {
    animation: panoramic 10s linear infinite alternate;
}
```

当鼠标悬浮在图片上时动画被触发：初始状态显示图像的最左边的部分，慢慢滚动显示图像的其他部分。然而，当我们的鼠标移出图像时，图像又突然跳到图像的最左边，如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1476285946085.png" width="275"/>

为了解决这个问题，我们需要的是鼠标悬浮在图片是有动画，图片从左向右移动，鼠标移出图片时，动画暂停。庆幸的是，动画有一个属性`animation-play-state`可以实现这样的效果。

因此，我们把动画的暂停状态运用在`.panoramic`上，而鼠标悬浮状态(`:hover`)时又播放动画。因为它不再是播放和取消一个动画，只是暂停和恢复现有的动画，这样动画不会有突然跳跃。最后的代码如下：

```css
.panoramic {
  width: 150px; 
  height: 150px;
  background: url("http://www.w3cplus.com/sites/default/files/blogs/2015/1507/naxos-greece-big.jpg");
  background-size: auto 100%;
  animation: panoramic 10s linear infinite alternate;
  animation-play-state: paused;
}
.panoramic:hover,
.panoramic:focus {
  animation-play-state: running;
}
@keyframes panoramic {
  to {
    background-position: 100% 0;
  }
}
```

效果可以看[这里](http://codepen.io/airen/pen/PqeGPz)。

转摘：[CSS秘密花园： 动画状态](http://www.w3cplus.com/css3/css-secrets/smooth-state-animations.html) 或 [CSS秘密花园： 动画状态](http://www.tuicool.com/articles/ra2MNrM)


### 圆周动画
经常，我们会遇到将一个或一些元素绕着一个圆形路径进行循环转动的效果。但是在转动的时候，还要保持元素不被调整方向而造成难以辨识的状态。也就是说，我们需要在转动的时候，将元素进行同角度的逆旋转。

基本的 HTML 代码如下：

```html
<div class="path">
    <img src="lea.jpg" class="avatar" />
</div>
```

基础的 CSS 样式如下：

```css
.path {
    width: 300px; 
    height: 300px;
    padding: 20px;
    border-radius: 50%;
    background: #fb3;
  margin: 50px auto;
}
.avatar {
    width: 50px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    display: block;
}
```

原始效果如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1476320880169.png" width="344"/>

然后写动画效果。首先是将头像移到圆中，绕着橙色的路径旋转一圈：

```css
@keyframes spin {
    to { transform: rotate(1turn); }
}
.avatar {
    animation: spin 3s infinite linear;
    transform-origin: 50% 150px; /* 150px = path radius */
}
```

现在，可以绕着圆周运动了，但是会将`.avatar`元素给颠倒方向了，不便于识别了。如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1476321084947.png" width="350"/>

这有两种方式可以解决：

**两层动画**

既然会被颠倒，那么我在动画外面再添加一个动画，而且旋转方向相反，那么就可以将其摆正了。

HTML 代码改为如下：

```html
<div class="path">
    <div class="avatar">
        <img src="lea.jpg" />
    </div>
</div>
```

对应的动画 CSS 如下：

```css
@keyframes spin {
    to { 
        transform: rotate(1turn); 
    }
}
@keyframes spin-reverse {
    from { 
        transform: rotate(1turn); 
    }
}
.avatar {
    animation: spin 3s infinite linear;
    transform-origin: 50% 150px; /* 150px = path radius */
}
.avatar > img {
    animation: spin-reverse 3s infinite linear;
}
```

效果如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1476321303371.png" width="327"/>

我们可以将 CSS 代码继续简化：首先，两个元素的动画参数大部分重复了，修改起来比较麻烦；其次，两个动画的运行除了方向其他都相同，可以将动画合并，而设置动画方向相反即可。

```css
@keyframes spin {
    to { transform: rotate(1turn); }
}
.avatar {
    animation: spin 3s infinite linear;
    transform-origin: 50% 150px; /* 150px = path radius */
}
.avatar > img {
    animation: inherit;
    animation-direction: reverse;
}
```

**使用动画分解**

如果不能更改 HTML 代码，那么我们就需要在一个元素上同时实现两个动画变化的效果。这确实是可能的，它基于如下的基本原理：

**`transform-origin` 就是一个语法糖，可以使用`translate()`替代。**

事实证明，每个`transform-origin`可以模拟两次`translate()`。例如下面的两个代码段是等价的：

```css
transform: rotate(30deg);
transform-origin: 200px 300px;

/* 等价于 */
transform: translate(200px, 300px)
           rotate(30deg)
           translate(-200px, -300px);
transform-origin: 0 0;
```

这是因为`transform`函数不是独立的：每个`transform`属性不仅应用在元素上，而且整个坐标系统运用在同一个元素上，也将影响其后所有的`transform`。这也说明为什么不同的`transfrom`顺序很重要，不同顺序的相同转换可能前生的结果会完全不同。上面的变换解释图如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1476321848824.png" width="344"/>

那么，重回原先的 HTML 代码：

```html
<div class="path">
    <img src="lea.jpg" class="avatar" />
</div>
```

使用多个`transform`来代替设置`transform-origin`，思路是：先将元素以父元素的中心进行渲染，然后以自身中心逆旋转，这样就能保证`.avatar`元素移动了位置但是不旋转自身。可以得到如下的样式：

```css
@keyframes spin {
  from {
    transform: translate(50%, 150px)
      rotate(0turn)
      translate(-50%, -150px)
      translate(50%,50%)
      rotate(1turn)
      translate(-50%,-50%)
  }
  to {
    transform: translate(50%, 150px)
      rotate(1turn)
      translate(-50%, -150px)
      translate(50%,50%)
      rotate(0turn)
      translate(-50%, -50%);
  }
}
.avatar { 
    animation: spin 3s infinite linear; 
}
```

不过，上面的代码仍旧有很多重复，特别是`translate(-50%,-150px)`和`translate(50%,50%)`。虽然百分比和绝对长度不能结合在一起（除非我们使用`calc()`），但是水平的`translate`是可以相互取消的。所有简化的代码如下：

```css
@keyframes spin{
  from{
    transform: translateY(150px)
        translateY(-50%)
        rotate(0turn)
        translateY(-150px)
        translateY(50%)
        rotate(1turn);
  }
  to {
    transform: translateY(150px)
        translateY(-150%)
        rotate(1turn)
        translateY(-150px)
        translateY(50%)
        rotate(0turn);
  }
}
```

如果我们一开始将`.avatar`定位在父元素的中心位置，那么就可以继续减少两次`translate`：

```css
@keyframes spin{
  from{
    transform: rotate(0turn)
        translateY(-150px)
        translateY(50%)
        rotate(1turn);
  }
  to {
    transform: rotate(1turn)
    translateY(-150px)
    translateY(50%)
    rotate(0turn);
  }
}

.avatar {
    display: block;
    width: 50px;
    margin: calc(50% - 25px) auto 0;
    border-radius: 50%;
    overflow: hidden;
    animation: spin 3s infinite linear;
}
```

这可能就是最简单的 HTML 结构和 CSS 代码了吧。最后的 Demo 看[这里](http://codepen.io/airen/pen/eNrLeP)

转摘：[CSS秘密花园： 沿着路径的动画](http://www.w3cplus.com/css3/css-secrets/animation-along-a-circular-path.html)  或  [CSS秘密花园： 沿着路径的动画](http://www.tuicool.com/articles/ZzUBRfb)


