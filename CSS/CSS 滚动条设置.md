> 转摘：[CSS滚动条](http://www.cnblogs.com/xiaohuochai/p/5294409.html)

无论什么浏览器，默认滚动条均来自`<html>`，而不是`<body>`。

> 因为`<body>`元素默认有 8px 的 margin。若滚动条来自`<body>`元素，则滚动条与页面则应该有 8px 的间距，实际上并没有间距，所以滚动条来自`<html>`元素。

### 宽度

滚动条会占用浏览器的可用宽度为：

* chrome/firefox/IE  17px
* safari  21px

可以使用如下的方式测量：

```html
<div class="box">
    <div id="in" class="in"></div>
</div>
```

```css
.box{
    width: 400px;
    overflow: scroll;
}
.in{
    *zoom: 1;
}
```

```javascript
console.log(400-document.getElementById('in').clientWidth);
```

### 兼容

1. 默认情况下 IE7- 浏览器默认有一条纵向滚动条，而其他浏览器则没有
2. IE7- 浏览器与其他浏览器关于滚动条的宽度设定机制不同。父级 box 出现纵向滚动条，实际上子级 in 的可用宽度就缩小了。IE7- 浏览器的子级宽度忽略了该滚动条的宽度，子级宽度=父级宽度，则出现了横向滚动条；而其他浏览器的子级宽度考虑到该滚动条的宽度，子级宽度=(父级宽度-滚动条宽度)*100%。
3. 水平居中跳动问题：当一个元素在页面中水平居中时，页面中出现纵向滚动条会发生水平居中的跳出问题。解决方法如下:

```css
//IE8-默认
html{overflow-y: scroll}
//IE9+，100vw表示浏览器的宽度，100%表示可用内容的宽度
.container{padding-left: calc(100vw-100%)}
```

### 自定义

1. IE 浏览器支持通过 CSS 样式来改变滚动条的部件的自定义颜色。

```css
scrollbar-face-color       // 滚动条凸出部分的颜色
scrollbar-shadow-color     // 立体滚动条阴影的颜色
scrollbar-highlight-color  // 滚动条空白部分的颜色
scrollbar-3dlight-color    // 滚动条亮边的颜色
scrollbar-darkshadow-color // 滚动条强阴影的颜色
scrollbar-track-color      // 滚动条的背景颜色 
scrollbar-arrow-color      // 上下按钮上三角箭头的颜色 
scrollbar-base-color       // 滚动条的基本颜色
```

2. webkit 内核的浏览器支持滚动条自定义样式，但和 IE 不同，webkit 是通过伪类来实现的。

```css
::-webkit-scrollbar             // 滚动条整体部分
::-webkit-scrollbar-thumb       // 滚动滑块
::-webkit-scrollbar-track       // 外层轨道
::-webkit-scrollbar-track-piece // 内层轨道
::-webkit-scrollbar-corner      // 边角
::-webkit-scrollbar-button      // 两端按钮
```

> 注意：滚动条的层叠关系为 scrollbar 在最底层，往上依次是 track 外层轨道，track-piece 内层轨道。而 button 按钮、corner 边角和 thumb 滑块有最顶层。

3. 伪类相关

```css
:horizontal
//horizontal伪类适用于任何水平方向上的滚动条

:vertical
//vertical伪类适用于任何垂直方向的滚动条

:decrement
//decrement伪类适用于按钮和轨道碎片。表示递减的按钮或轨道碎片，例如可以使区域向上或者向右移动的区域和按钮

:increment
//increment伪类适用于按钮和轨道碎片。表示递增的按钮或轨道碎片，例如可以使区域向下或者向左移动的区域和按钮

:start
//start伪类适用于按钮和轨道碎片。表示对象（按钮 轨道碎片）是否放在滑块的前面

:end
//end伪类适用于按钮和轨道碎片。表示对象（按钮 轨道碎片）是否放在滑块的后面

:double-button
//double-button伪类适用于按钮和轨道碎片。判断轨道结束的位置是否是一对按钮。也就是轨道碎片紧挨着一对在一起的按钮。

:single-button
//single-button伪类适用于按钮和轨道碎片。判断轨道结束的位置是否是一个按钮。也就是轨道碎片紧挨着一个单独的按钮。

:no-button
no-button伪类表示轨道结束的位置没有按钮。

:corner-present
//corner-present伪类表示滚动条的角落是否存在。

:window-inactive
//适用于所有滚动条，表示包含滚动条的区域，焦点不在该窗口的时候。

::-webkit-scrollbar-track-piece:start {
/*滚动条上半边或左半边*/
}

::-webkit-scrollbar-thumb:window-inactive {
/*当焦点不在当前区域滑块的状态*/
}

::-webkit-scrollbar-button:horizontal:decrement:hover {
/*当鼠标在水平滚动条下面的按钮上的状态*/
}
```


