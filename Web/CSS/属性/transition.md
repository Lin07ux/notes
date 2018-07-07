CSS3 中的 transition 属性可以简单高效的方式实现 UI 状态间的补间动画。

> 转摘：[CSS魔法堂：Transition就这么好玩](https://www.cnblogs.com/fsjohnhuang/p/9143035.html)

### 一、语法

```css
transition: <transition-property> <transition-duration> <transition-timing-function> <transition-delay>;

/*
 * 设置启用 transition 效果的 CSS 属性
 * 注意：仅会引发 repaint 或 reflow 的属性可启用 transition 效果
 */
<transition-property>: all | none | <property> [,<property>]*

/*
 * 设置过渡动画持续时间，单位为 s 或 ms
 */
<transition-duration>: 0s | <time> [, <time>]*

/*
 * 设置过渡动画的缓动函数
 * cubic-bezier的值从0到1
 * [一个很好用的cubic-bezier配置工具](http://cubic-bezier.com)
 */
<transition-timing-function>: linear|ease|ease-in|ease-out|ease-in-out|cubic-bezier(n,n,n,n)

/* 
 * 设置过渡动画的延时，单位为s或ms
 */
<transition-delay>: 0s | <time> [, <time>]
```

> 可以实现动画的属性如下：[CSS_animated_properties](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_animated_properties)。

可以一次性为多个 CSS 属性启动 transition 效果：

```css
transition: width 1s ease .6s,
            color .5s linear,
            background 2s ease-in-out;
```

### 二、触发方式

transition 是 UI 状态间的补间动画，所以有且仅有修改 UI 状态时才能让动画动起来。那么就有 3 种方式了：

1.	伪类：`:link`、`:visited`、`:hover`、`:active`和`:focus`；
2.	通过 JS 修改 CSS 属性值；
3.	通过 JS 修改 className 值。

### 三、transitionend 事件

```js
el.addEventListener("transitionend", e => {
    const pseudoElement = e.pseudoElement // 触发动画的伪类
        , propertyName = e.propertyName   // 发生动画的CSS属性
        , elapsedTime = e.elapsedTime     // 动画的持续时间
    // ..................
})
```

注意：**每个启用 transition CSS 属性的分别对应独立的`transitionend`事件**。

```css
/* 会触发 3 个 transitionend 事件 */
transition: width 1s ease .6s,
            color .5s linear,
            background 2s ease-in-out;
```

### 四、特例

#### 4.1 visibility 也能 transition

在可启用 transition 的 CSS 属性中，有一个很特别的 CSS 属性——`visibility`。`visibility`与`display`有类似的效果，但是它能启用 transition，而 display 不行呢。

`visibility`是离散值，0(`hidden`)表示隐藏，1(`visible`)表示完全显示，非 0 表示显示。那么`visibility`状态变化就存在两个方向的差异了：

1.	**从隐藏到显示**：由于非 0 就是显示，那么从值从 0 到 1 的过程中，实际上是从隐藏直接切换到显示的状态，因此并没有所谓的变化过程；

2.	**从显示到隐藏**，从 1 到 0 的过程中，存在一段时间保持在显示的状态，然后最后一瞬间切换到隐藏，因此效果上是变化延迟，依然没有变化过程。

虽然启用 transition 的 visibility 并没有补间动画的视觉效果，但是它可以不影响/辅助其他 CSS 属性的补间动画。其中最明显的例子就是辅助 opacity 属性实现隐藏显示的补间动画：如果不对`visibility`启用 transition，那么即便对`opacity`启用了动画，元素也只是会直接被隐藏，或者显示。

> `visibility:hidden`时，元素不显示且不拦截鼠标事件，所以在补间动画的最后设置`visibility:hidden`为不俗的解决办法。

#### 4.2 display 让 transition 时效的补救

当改变`display`的值来显示或隐藏元素时，元素上其余 CSS 属性的 transition 均失效。

1. 当`display`从其他值变成`none`时，为了让其他属性依旧有动画效果，需要延迟`display`值的变化，可以借助`transitionend`事件来完成。
2. 当`display`从`none`变成其他值时，可以在变化后，立即强制元素进行重绘，从而使的其他属性的 transition 被启用。

```html
<style>
.box{
  display: none;
  background: red;
  height: 20px;
}
</style>
<div class="box"></div>
<button id="btn1">Transition has no effect</button>
<button id="btn2">Transition takes effect</button>
<script>
const box = document.querySelector(".box")
    , btn1 = document.querySelector("#btn1")
    , btn2 = document.querySelector("#btn2")
btn1.addEventListener("click", e => {
  box.style.display = "block"
  box.style.background = "blue"
})
btn2.addEventListener("click", e => {
  box.style.display = "block"
  box.offsetWidth              // 强制执行reflow
  box.style.background = "blue"
})
</script>
```

上面的代码，当我们点击 btn1 时背景色的 transition 失效，而点击 btn2 则生效，关键区别就是通过`box.offsetWidth`强制执行 reflow，让元素先加入渲染树进行渲染，然后再修改背景色执行 repaint。

