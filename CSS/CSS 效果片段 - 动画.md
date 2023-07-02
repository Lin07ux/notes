### 1. 脉冲动画

> 效果：[CSS 脉冲动画](https://www.jq22.com/code4371)

让元素周边不断出现并扩展，类似波纹荡漾开的效。其核心逻辑是为元素和元素的`before`、`after`元素设置`box-shadow`动画，让`box-shadow`不断的扩展并变淡，而且每个元素的动画延迟时间不同，从而形成层叠的脉冲动画。

```html
<div class="ripple"></div>
<div class="ripple" style="filter:hue-rotate(120deg)"></div>
<div class="ripple" style="filter:grayscale()"></div>
```

```css
.ripple {
  width: 1rem; /* control the size */
  background: #ff0; /* control the color here */
}
.ripple,
.ripple::before,
.ripple::after {
  content: "";
  display: grid;
  grid-area: 1/1;
  aspect-ratio: 1;
  border-radius: 50%;
  box-shadow: 0 0 0 0 #ff03; /* and here, 3 is the transparency */
  animation: r 3s linear infinite var(--s,0s);
}
.ripple::before {--s: 1s}
.ripple::after  {--s: 2s}

@keyframes r {
  to {box-shadow: 0 0 0 6rem #0000}
}

body {
  margin: 0;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 11rem;
  background: #000;
}
```

![](https://cnd.qiniu.lin07ux.cn/markdown/f6f168e97dd386b252e33fd266abffbe.jpg)
