> 转摘：[css-虚线边框滚动效果](https://segmentfault.com/a/1190000019105239)

CSS 中元素的边框一般无法设置成虚线状态，而且难以实现动画移动效果，不过通过一定的技巧(背景色或背景图)的方式来达到虚线边框甚至虚线动画边框的效果。

整体的思路就是：子元素使用一种背景将父元素设置背景覆盖一部分，只留下部分作为边框效果。

下面的示例都是对如下的 HTML 结构进行设置：

```html
<div class="box">
  <p>测试测试</p>
</div>
```

### 1. 背景图

最简单的方式是使用一个背景图来模式边框，这种方式实现简单，但是也会有些限制，比如对边框宽度的控制就需要一定的方式来计算，而且颜色或动态的调整会复杂一些。

```scss
.box {
  width: 100px;
  height: 100px;
  position: relative;
  background: url(https://www.zhangxinxu.com/study/image/selection.gif);
  
  p {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    margin: auto;
    height: calc(100% - 2px);
    width: calc(100% - 2px);
    background-color: #fff;
  }
}
```

### 2. repeating-linear-gradient

父元素设置倾斜 135 度的线性渐变，子元素撑开高度，并覆盖父元素的部分背景。

```scss
.box {
  width: 100px;
  height: 100px;
  background: repeating-linear-gradient(
    135deg,
    transparent,
    transparent 4px,
    #000 4px,
    #000 8px
  );
  overflow: hidden; // 新建一个BFC，解决 margin 在垂直方向上折叠的问题
  animation: move 1s infinite linear;
  
  p {
    height: calc(100% - 2px);
    margin: 1px;
    background-color: #fff;
  }
}

@keyframes move {
  from {
    background-position: -1px;
  }
  to {
    background-position: -12px;
  }
}
```

### 3. linear-gradient && background

通过线性渐变以及`background-size`画出虚线，然后再通过`background-position`将其移动到四边。这种方式比较好的地方在于可以分别设置四条边的样式以及动画的方向。

```scss
.box {
  width: 100px;
  height: 100px;
  background: linear-gradient(0deg, transparent 6px, #e60a0a 6px) repeat-y,
    linear-gradient(0deg, transparent 50%, #0f0ae8 0) repeat-y,
    linear-gradient(90deg, transparent 50%, #09f32f 0) repeat-x,
    linear-gradient(90deg, transparent 50%, #fad648 0) repeat-x;
  background-size: 1px 12px, 1px 12px, 12px 1px, 12px 1px;
  background-position: 0 0, 100% 0, 0 0, 0 100%;
  animation: move2 1s infinite linear;
  
  p {
    margin: 1px;
  }
}

@keyframes move2 {
  from {
  }
  to {
    background-position: 0 -12px, 100% 12px, 12px 0, -12px 100%;
  }
}
```

### 4. linear-gradient && mask

mask 属性规范已经进入候选推荐规范之列。这里同样可以使用mask来实现相同的动画，并且可以实现虚线边框渐变色这种效果，与`background`不同的是 mask 需要在中间加上一块不透明的遮罩，不然 p 元素的内容会被遮盖住。

```scss
.box {
  width: 100px;
  height: 100px;
  background: linear-gradient(0deg, #f0e, #fe0);
  -webkit-mask: linear-gradient(0deg, transparent 6px, #e60a0a 6px) repeat-y,
    linear-gradient(0deg, transparent 50%, #0f0ae8 0) repeat-y,
    linear-gradient(90deg, transparent 50%, #09f32f 0) repeat-x,
    linear-gradient(90deg, transparent 50%, #fad648 0) repeat-x,
    linear-gradient(0deg, #fff, #fff) no-repeat;        // 这里不透明颜色随便写哦
  -webkit-mask-size: 1px 12px, 1px 12px, 12px 1px, 12px 1px, 98px 98px;
  -webkit-mask-position: 0 0, 100% 0, 0 0, 0 100%, 1px 1px;
  overflow: hidden;
  animation: move3 1s infinite linear;
  
  p {
    height: calc(100% - 2px);
    margin: 1px;
    background-color: #fff;
  }
}

@keyframes move3 {
  from {
  }
  to {
    -webkit-mask-position: 0 -12px, 100% 12px, 12px 0, -12px 100%, 1px 1px;
  }
}
```

