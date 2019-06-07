HTML 中，各个元素默认是以标准流按照在代码中的顺序从前到后显示出来的，而 CSS 中`float`属性可以让元素浮动，脱离标准布局流，实现特殊的效果。

## 一、使用

### 1.1 用法

`float`可选值如下：

```
float: none | left | right;
```

其中：

* `none`表示不浮动
* `left`表示将元素向左浮动
* `right`表示将元素向右浮动

### 1.2 浮动布局

HTML 中每个元素一般默认是块元素或行元素，块元素是在标准流中占据独立的一行(高度不定)，即便一行的宽度足够容纳两个块元素，而行元素则可以在同一行中出现。而设置浮动之后，该元素将会靠左或靠右排列，而且多个浮动元素可以并列。

**浮动元素的布局位置**：对于一个浮动的块元素`A`，如果`A`的上一个元素也是浮动的，那么`A`将会跟随在上一个元素的后边(如果一行内放不下这两个元素，那么`A`将会被挤到下一行内)；如果`A`的上一个元素是标准流中的元素，那么`A`的垂直位置不会改变，即，仍是其不浮动时在标准流中的垂直位置，也就是说，总是和上一个元素的底部对齐(不考虑margin)。

在页面中，元素总是从上往下，从前到后的排列。而在浮动流中，靠近页面边缘的一侧是“前”，而远离页面边缘的一段是“后”。也就是说，如果元素向左浮动，那么页面的左侧就是前，处于代码中靠前位置的浮动元素会紧靠左侧，然后其后面的浮动元素靠在其后面。对于向右浮动，则刚好相反，示例如下：

向左浮动：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
  <div id="2" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: red; float: left;">Div2</div>
  <div id="3" style="width: 30%; height: 30px; padding: 0; margin: 0; background-color: yellow; float: left;">Div3</div>
  <div id="4" style="width: 30%; height: 40px; padding: 0; margin: 0; background-color: #0066CC; float: left;">Div4</div>
</div>

向右浮动：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
  <div id="2" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: red; float: right;">Div2</div>
  <div id="3" style="width: 30%; height: 30px; padding: 0; margin: 0; background-color: yellow; float: right;">Div3</div>
  <div id="4" style="width: 30%; height: 40px; padding: 0; margin: 0; background-color: #0066CC; float: right;">Div4</div>
</div>

可以看到，不管向左还是向右浮动，`Div2`元素都是在靠“前”的位置上。

## 二、特性

`float`属性可不仅可以改变元素的布局流，还能使元素具有一些特殊性质。

### 2.1 高度塌陷

高度塌陷指的是，当子元素浮动之后，父元素的高度会收缩塌陷，仿佛这个浮动的子元素不存在一样。

有如下的 HTML 和 CSS：

```html
<div class="parent">
  <div class="son"></div>
</div>
```

```css
.parent {
  width: 200px;
  padding: 5px;
  background-color: green;
}
.son {
  float: left;
  width: 100px;
  height: 100px;
  background-color: blue;
}
```

效果如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1559888648856.png" width="236"/>

可以看到，父元素`div.parent`只有 padding 的高度，而不包含子元素`div.son`的高度，也就是出现了高度塌陷。

### 2.2 块状化

一旦元素`float`的属性不为`none`，则其`display`计算值就是`block`或者`table`，表示类似于块元素。

```JavaScript
/* JavaScript代码 */
var span = document.createElement('span')
document.body.appendChild(span)
console.log('1.' + window.getComputedStyle(span).display)

// 设置元素左浮动
span.style.cssFloat = 'left'
console.log('2.' + window.getComputedStyle(span).display)
```

### 2.3 包裹性

所谓"包裹性"，其实是由"包裹"和"自适应"两部分组成。将元素浮动之后，浮动元素会自动包裹住其子元素，而且如果周边有行元素，那么行元素也会自动包裹该浮动元素。

既然设置`float`后，元素就块状化了，那么怎么还能产生包裹性的效果呢？因为浮动元素的块状化意思是可以像`block`元素一样设置宽和高，并不是真正的块元素。

### 2.4 margin 不重叠

设置了`float`属性的元素没有任何的`margin`重叠，这和普通的元素`margin`重叠不一样。

```html
<div class="parent">
  <div class="son"></div>
  <div class="son"></div>
</div>
```

```css
.parent {
  width: 300px;
  padding: 5px;
  background-color: green;
}
.son {
  float: left;
  width: 100px;
  height: 100px;
  margin: 10px;
  background-color: blue;
}
```

可以看到，两个子元素都是浮动的，但是他们之间的`margin`并没有发生重叠。

## 三、其他

## 3.1 用 clear 清除浮动

元素浮动之前，也就是在标准流中，是竖向排列的，而浮动之后可以理解为横向排列。清除浮动则可以理解为打破横向排列。语法为：

```
clear: none | left | right | both
```

其中：

- `none` 默认值。不清除浮动，允许两边都可以有浮动的对象。
- `left` 不允许该元素的左侧有浮动对象。
- `right` 不允许该元素的右侧有浮动元素。
- `both` 该元素左右两侧都不允许有浮动元素。

这样的解释其实会让人产生误解的。比如，对于下面的两个都向左浮动的元素：

```html
<div class="parent">
  <div class="son"></div>
  <div class="son second"></div>
</div>
```

```css
.parent {
  width: 300px;
  padding: 1px;
  background-color: green;
}
.son {
  float: left;
  width: 100px;
  height: 100px;
  margin: 10px;
  background-color: blue;
}
```

这样设置的时候，两个元素的并列排序的：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1559900973918.png" width="315"/>

如果想让`Div2`不跟随在`Div1`的后面，而是处于`Div1`的下面，想当然的可能会设置`Div1`清除右浮动`clear: right`，但是这样是没有效果的。因为：**对于`CSS`的浮动清除`clear`，只能影响使用清除样式的元素本身的行为，而不能影响其他元素。**

所以增加如下 css，第二个子元素就在第一个子元素下面了：

```css
.second {
  clear: left;
}
```

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1559901014439.png" width="321"/>

怎么去理解呢？以上面的例子为例：需要使`Div2`另起一行，不跟随在`Div1`的后面，也就是说，需要的是使`Div2`有所改变，就需要将`Div2`设置清除，而且是清除左浮动`clear: left;`。而如果设置`Div1`清除右浮动是没有办法影响到`Div2`的，所以这样并不能让`Div2`移动到下一行中去。

同样，对于两个向右浮动的元素`Div1`和`Div2`，需要使他们各占一行，则还是需要设置处于“后面”的`Div2`清除浮动`clear: right;`。同样，由于清除浮动只影响其自身，所以如果其后面还有其他浮动元素，其他浮动元素将会继续跟随在这个元素的后面。

比如，下面的示例中，设置了三个元素都左浮动，但是将`Div2`设置了清除左浮动`clear: left;`，可以看到，`Div3`依旧跟随在`Div2`后面：

```html
<div class="parent">
  <div class="son"></div>
  <div class="son second"></div>
  <div class="son"></div>
</div>
```

```css
.parent {
  width: 300px;
  padding: 1px;
  background-color: green;
}
.son {
  float: left;
  width: 100px;
  height: 100px;
  margin: 10px;
  background-color: blue;
}
.second {
  clear: left;
}
```

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1559906227248.png" width="312"/>

### 3.2 用 BFC 撑开元素

BFC(块级格式上下文 Block Formatting Context)是一个独立的布局环境。通过设置产生一个新的 BFC，也能够消除浮动之后引起的父元素高度塌陷的问题。

由于浮动元素是会脱离正常文档流的，所以如果一个没有高度或者`height: auto`的容器的子元素是浮动元素，那么该容器的高度是不会被撑开的。通常会利用伪元素(`:after`或者`:before`)来解决这个问题。BFC 能包含浮动元素，也能解决容器高度不会被撑开的问题。

> 更多 BFC 相关的内容，可以见另一篇博客 [《CSS 中的 BFC》](../知识点/CSS%20BFC%20块级格式上下文.md)

看下面的例子：

```html
<div class="container">
	<div class="float"></div>
	<div class="float"></div>
</div>
```

```css
.container {
	background-color: #912578;
	padding: 10px;
	margin: 10px;
	width: 220px;
}
.float {
	float: left;
	width: 50px;
	height: 50px;
	margin: 10px;
	padding: 10px;
	border: 5px dashed #239356;
	background-color: #65c567;
}
```

在这里，`div.container`的两个子元素是浮动元素，所以这个元素的高度就只有 20px（就是其内边距的大小）。而如果给父元素添加一个`overflow: hidden`的属性，使其建立一个 BFC，那它就能被撑开了。具体效果见下图：

![用 BFC 撑开元素](http://cnd.qiniu.lin07ux.cn/2015-10-28%20BFC-float.png)


