### 清除移动端 tap 事件后元素周边出现的一个高亮

```css
*{
    -webkit-tap-highlight-color: rgba(0,0,0,0);
}
```

### 单行文本溢出省略号

```css
p {
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
}
```

### 多行文本溢出省略号

在一个段落中，文本要保留多行，此时就不能用上一种方法了。对于`-webkit`内核的浏览器，可以使用下面的代码，其中，`-webkit-line-clamp`属性是来设置显示行数的。
    
```css
.multi-ellipsis {
   overflow: hidden;
   display: -webkit-box;
   -webkit-box-orient: vertical;
   -webkit-line-clamp: 3;  /* 行数 */
}
``` 

对于非`-webkit`核心的浏览器，就没有直接的 CSS 属性来设置了，需要变通方法：设置文本行高，然后设置高度为：显示的行数 * 行高。但是这样则不好设置最后的省略号了，需要借助 js 代码来实现。

### hover 浮动效果

```css
li:hover {
    box-shadow: 0 17px 50px 0 rgba(0,0,0,.19);
    transform: translate3d(0,-2px,0);
    transition: all .3s cubic-bezier(.55,0,.1,1);
}
```

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1468997594653.png" width="485"/>

> 参考：[一加官网](http://www.oneplus.cn/)

### 给文本画上斑马线

使用`linear-gradient`生成条纹背景图，并进行 repeat 即可。主要是要控制好每个条纹的宽度/高度(使用 em 单位)。

```css
pre {
    width: 100%;
    padding: .5em 0;
    line-height: 1.5;
    color: #333;
    font-size: 16px;
    background: #f5f5f5;
    background-image: linear-gradient(rgba(0,0,120,.1) 50%, transparent 0);
    background-size: auto 3em;
    background-origin: content-box;
    tab-size: 2;
}
```

这里由于设置了`lin-height: 1.5;`，所以两行文本的高度就是 3em，那么背景中就要设置高度为 3em，默认即为可重复的。另外，`backgrouun-origin: content-box;`表示背景是从文本区域开始展示的，避免斑马纹和文本之间出现错位。

![斑马纹](http://cnd.qiniu.lin07ux.cn/markdown/1472349064385.png)


### 保持在页面最底部

网页开发中，会遇到需要将一部分内容(比如版权信息)放在页面的最底部的需求。

这里的放在页面最底部，不是指用`position: fixed;`或`position: absolute;`来定位在某个固定的地方，而是指：在正常的页面布局中，将其放在页面的最底部；如果页面内容的总高度不足一个可视窗口的高度，仍旧能将其布局在可视窗口的底部；如果页面内容的总高度大于一个可视窗口的高度，那么其会布局在整个文档流的末尾，不滚动时会被遮住。

HTML 结构如下：

```html
<html>
<body>
  <main class="page-wrap">This is an article about CSS.</main>
  <footer class="footer">Copyright@ Harttle Land 2016</footer>
</body>
</html>
```

对应的 CSS 代码如下：

```css
* { margin: 0; }

/* .footer 的每一级父元素都为 100% 高 */
html, body { height: 100%; }

.page-wrap {
  /* 页面内容至少撑满 100% 的屏幕 */
  min-height: 100%;
  /* 负边距大小即为页脚高度 */
  margin-bottom: -60px; 
}

/* 用来填充被页脚遮挡部分 */
.page-wrap:after {
  content: "";
  display: block;
}

/* 填充块和页脚一样高 */
.footer, .page-wrap:after {
  height: 60px; 
}
``` 

### 感知子元素的个数

要实现如下的效果：如果`.list`里面`li`元素个数大于等于 4，则显示为红色。

```css
.list li:nth-last-child(n+4) ~ li,
.list li:nth-last-child(n+4):first-child {
  color: red
}
```

原理是：

* `:nth-last-child(n+4)` 这一个选择器的意思就是倒数第四个以及之前的元素，后面加了 `~ li`，就是表示符合前面条件的元素之后的 li 元素。
* 如果元素总数不足 4，则不会存在符合`:nth-last-child(n+4)`的元素（一共没有四个，也就不存在倒数第四个），那么`li:nth-last-child(n+4) ~ li`就不会选择任何的元素了。
* 但是如果只用`~ li`，是不会匹配到第一个 li 的，所以又加上了`li:nth-last-child(n+4):first-child`。

### 设置光标颜色

在文本框中`input/textarea`中如果要改变光标的颜色，可以通过设置文本的颜色来搞定。但是假如只想改变光标的颜色，而不想改变文本的颜色的话，`caret-color`属性是一个实现方案：

```css
input, textarea, [contenteditable] {
  caret-color: red;
}
```

### 隐藏鼠标

```css
.hide-cursor {
    cursor: none!important;
}
```

### 禁止用户选中文本

```css
.can-not-select {
    user-select: none;
}
```

### 禁用鼠标事件

```css
.disabled {
    pointer-events: none;
}
```

如果一个连接使用了该样式，则不会响应点击事件了。

### 文字模糊

```css
.text-fuzzy {
    color: transparent;
    text-shadow: #111 0 0 5px;
}
```

### 更改 input placeholder 文字的样式

```css
::-webkit-input-placeholder { /* Chrome/Opera/Safari */
  color: pink;
}
::-moz-placeholder { /* Firefox 19+ */
  color: pink;
}
:-ms-input-placeholder { /* IE 10+ */
  color: pink;
}
:-moz-placeholder { /* Firefox 18- */
  color: pink;
}
```

### 页面变灰

在南京大屠杀纪念日的时候，不少相关站点都将网站全部变为灰色，以表示哀悼。这种效果是可以用纯 CSS 来实现的。主要代码如下(也可以选择性的针对不同的元素变灰)：

```css
* {
  -webkit-filter: grayscale(100%);
  -moz-filter: grayscale(100%);
  -ms-filter: grayscale(100%);
  -o-filter: grayscale(100%);
  filter: grayscale(100%);
}
```

### 控制单元格宽度

在给表格设置宽度时，经常会遇到宽度不起作用的问题。这是因为单元格的宽度是根据其内容进行调整的。

表格有个`table-layout`属性，其浏览器默认值是`auto`，也就是单元格的宽度是根据内容展示来自动调整布局，而不会严格遵守宽度设置。当设置为`fixed`的时候，给`th/td`标签设置的宽度就起作用了：

```css
table {
 table-layout: fixed;
 width: 100%;
}
```

![](http://cnd.qiniu.lin07ux.cn/markdown/1562386994698.png")

