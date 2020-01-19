> 转摘：[CSS any-hover any-pointer media查询与交互体验提升](https://www.zhangxinxu.com/wordpress/2020/01/css-any-hover-media/)

`any-hover`和`any-pointer`这两个媒体查询条件可以用来区分屏幕密度，从而可以区分多倍屏和一般屏幕，实现不同屏幕的不同展示效果。

### 1. any-hover

`any-hover`用于测试是否有任意可用的输入装置可以 hover 悬停在元素上。因此，`any-hover`媒体查询可以用来精确控制不同设备上的 hover 交互行为，尤其对于跨平台的网页、响应式网站，非常有用。

`any-hover`功能检测支持下面的两个关键字值：

* `none` 没有什么输入装置可以实现 hover 悬停，或者没有可以指向的输入装置。
* `hover` 一个或多个输入装置可以触发元素的 hover 悬停交互。

比如，对于网页中的按钮，如果网站是专为移动端开发的，只需要写按钮默认态和`:active`激活态就可以；但是如果希望写一个按钮组件在移动端和 PC 端通用，那么，PC 端需要的`:hover`效果用在移动端就不合适，此时可以借助`any-hover`媒体查询优化体验：

```css
button {
    background-color: #fff;
}
button:active {
    background-color: #f0f0f0;
}
@media (any-hover: hover) {
  button:hover {
    background-color: #f5f5f5;
  }
}
```

`any-hover`的兼容性如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1579422248528.png)

IE 不可用，而且 Edge 浏览器也需要较新版本才行。

### 2. hover

`hover`媒体查询和`any-hover`类似，可选的值也相同，唯一的区别就在于`any-hover是`检测任意的输入装置，而`hover`只检测主要的输入装置。

可选值的说明如下：

* `none` 主输入装置根本无法悬停或无法方便地悬停（例如，许多移动设备在用户执行不方便的长点击来模拟悬停），或者没有主指向输入装置。
* `hover` 主输入装置可以触发元素的 hover 悬停交互。

`hover`媒体查询兼容到 Edge12，window8+ 以上操作系统都可以使用：

![](http://cnd.qiniu.lin07ux.cn/markdown/1579422409256.png)

### 3. pointer 和 any-pointer

与`hover`、`any-hover`查询相对应的还有`pointer`、`any-pointer`查询。`hover`是悬停事件相关，而`pointer`则是与点击事件相关。

`pointer`、`any-pointer`查询主要用来识别当前的环境，是否可以非常方便的进行点击操作。这两者的区别也是前者只检测主要输入装置的操作，后者则检测任意输入装置的操作。

可选值如下：

* `none` 没有可用的点击设备。或：主输入装置点击不可用。
* `coarse` 至少一个设备的点击不是很精确。例如手机移动端，都是使用萝卜一样粗的手指进行操作，就属于点击不精确。或：主输入装置点击不精确。
* `fine` 有设备可以让点击很精准，例如有鼠标的桌面电脑浏览器。或：主输入装置点击很精确，

例如，在点击不精确的时候让复选框尺寸变大，以方便用户操作：

```css
@media (pointer: coarse) {
  input[type="checkbox"] {
    width: 30px;
    height: 30px;
  }
}
```

这两个媒体查询的兼容性基本相同，都是从 Edge 12+ 开始的，兼容性还不错：

![](http://cnd.qiniu.lin07ux.cn/markdown/1579422728462.png)

