> 转摘：[css掩人耳目式海浪动效🌊，这可能是最简单的实现方式了吧？](https://segmentfault.com/a/1190000020017297)

使用 CSS 实现简单的海浪运动效果，类似下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/2363035153-5d4cce78c65a5.gif)

由于蓝色部分不宜实现，可以考虑通过调整白色部分的运动来达到相同的效果。主要用到一个元素和其两个伪元素。代码如下：

```html
<div class="wave"></div>
```

对应的 css 如下：

```scss
// 简单的盒子
.wave {
  position: relative;
  width: 150px;
  height: 150px;
  background-color: #5291e0;
  overflow: hidden;
  
  // 两个不规则圆形（相对盒子进行定位，距离底部距离则为波浪高度）
  &::before,
  &::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: 15%;
    width: 500%;
    height: 500%;
    border-radius: 45%;
    background-color: #fff;
    transform: translateX(-50%); // 居中
    animation: rotate 15s linear infinite;
  }
  
  // 其中一个不规则圆形调整一下样式，以便区分（或者调整 animation 的参数来区分）
  &::before {
    bottom: 10%;
    opacity: .5;
    border-radius: 47%;
  }
 }
}

// 旋转动画
@keyframes rotate {
  from {
    transform: translateX(-50%) rotateZ(0deg);
  }

  to {
    transform: translateX(-50%) rotateZ(360deg);
  }
}
```

这里通过将容器元素设置为蓝底，而两个伪元素设置为白底(其中一个半透明)，并通过设置容器的`overflow: hidden;`将伪元素超出部分隐藏掉，然后旋转伪元素即可实现类似波浪运动效果了。


