`CSS`中的浮动我们经常会用到，也感觉很熟悉了，但是对于浮动的一些基本的认识还不熟悉，有时候会感觉有些莫名，今天看到一篇讨论`float`的文章[《CSS浮动文摘》](http://www.cnblogs.com/metoy/p/4695364.html?utm_source=tuicool)，所以就参考他的理解，自己动手验证和熟悉一下。

## 0x00 基础知识

* HTML 中，各个元素默认是以标准流按照在代码中的顺序从前到后显示出来的。
* 块元素是在标准流中占据独立的一行(高度不定)，即便一行的宽度足够容纳两个块元素。
* 默认状态，`div`就是一个块元素。

如下所示，就是标准流中，各个`div`元素按照在代码中的顺序从上往下显示：

<div style="width:90%; height:102px; border:1px solid #eee; color:#000;">
    <div id="1" style="width:30%; height:20px; background-color: #1fff00">Div1</div>
    <div id="2" style="width:50%; height:20px; background-color: #f00">Div2</div>
    <div id="3" style="width:70%; height:30px; background-color: #ff0">Div3</div>
    <div id="4" style="width:90%; height:30px; background-color: #0017ff">Div4</div>
</div>

## 0x01 标准流

在标准流中，块元素是独占一行，自上而下，靠左排列的，如下图：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
    <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
    <div id="2" style="width: 50%; height: 20px; padding: 0; margin: 0; background-color: red;">Div2</div>
    <div id="3" style="width: 70%; height: 30px; padding: 0; margin: 0; background-color: yellow;">Div3</div>
    <div id="4" style="width: 90%; height: 30px; padding: 0; margin: 0; background-color: #0066CC;">Div4</div>
</div>

可以看出，即使`Div1`和`Div2`的宽度合起来还不足一行的宽度，他们还是各自独占一行从上往下靠左排列的。

## 0x02 浮动流

浮动可以理解为让元素脱离标准流，漂浮在标准流之上。浮动流和标准流就不是处于一个层次上的显示了。

例如，将上图中的`Div2`设置为向左浮动`float: left;`，那么这个元素就将脱离标准流，进入到浮动流中。效果如下：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
    <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
    <div id="2" style="width: 50%; height: 20px; padding: 0; margin: 0; background-color: red; float: left;">Div2</div>
    <div id="3" style="width: 70%; height: 30px; padding: 0; margin: 0; background-color: yellow;">Div3</div>
    <div id="4" style="width: 90%; height: 30px; padding: 0; margin: 0; background-color: #0066CC;">Div4</div>
</div>

此时，虽然`Div2`浮动了，但是其他的元素仍处于标准流中，所以`Div1`、`Div3`、`Div4`将继续按照标准流的显示方式，从上往下靠左排列，也就是`Div3`将会向上移动到`Div1`的下面排列。而处于浮动流中的`Div2`则是处于标准流之上，所以他会覆盖`Div3`的一部分。

当然，如果我们设置`Div2`向右浮动`float: right;`那么`Div2`就是靠右排列了：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
  <div id="2" style="width: 50%; height: 20px; padding: 0; margin: 0; background-color: red; float: right;">Div2</div>
  <div id="3" style="width: 70%; height: 30px; padding: 0; margin: 0; background-color: yellow;">Div3</div>
  <div id="4" style="width: 90%; height: 30px; padding: 0; margin: 0; background-color: #0066CC;">Div4</div>
</div>

如果我们浮动多个元素，比如把`Div2`和`Div3`都设置为向左浮动，`Div4`的高度设置高一点，那么将会展示为如下效果：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
  <div id="2" style="width: 50%; height: 20px; padding: 0; margin: 0; background-color: red; float: left;">Div2</div>
  <div id="3" style="width: 70%; height: 30px; padding: 0; margin: 0; background-color: yellow; float: left;">Div3</div>
  <div id="4" style="width: 90%; height: 50px; padding: 0; margin: 0; background-color: #0066CC;">Div4</div>
</div>

此时，处于标准流中的`Div1`和`Div4`仍旧按照从上到下靠左的方式排列，所以`Div4`紧靠着`Div1`的下边了。而`Div2`和`Div3`则处于浮动流中，所以会覆盖住`Div4`。

但是我们会发现，`Div2`和`Div3`好像在浮动流中也是按照从上往下排列的方式展示的。其实不是的，这是由于这两个元素的宽度和比父容器的宽度大，一行的内无法容下这两个元素造成的。现在，我们把这两个元素的宽度设置的短一点，那他俩就处于一行内了：

<div style="width: 90%; height: 102px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 30%; height: 20px; padding: 0; margin: 0; background-color: green;">Div1</div>
  <div id="2" style="width: 40%; height: 20px; padding: 0; margin: 0; background-color: red; float: left;">Div2</div>
  <div id="3" style="width: 40%; height: 30px; padding: 0; margin: 0; background-color: yellow; float: left;">Div3</div>
  <div id="4" style="width: 90%; height: 50px; padding: 0; margin: 0; background-color: #0066CC;">Div4</div>
</div>

## 0x03 浮动元素的位置

在上面的示例中，我们会发现，`Div2`浮动之后，总是处于`Div1`之后；而`Div3`浮动之后则会跟随在`Div2`之后。由此，我们可以得到这么一个结论：

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

## 0x04 用 clear 清除浮动

经过上边的学习，可以看出：元素浮动之前，也就是在标准流中，是竖向排列的，而浮动之后可以理解为横向排列。

清除浮动则可以理解为打破横向排列。语法为：

```
clear: none | left | right | both
```

取值的作用如下：

- `none` 默认值。不清除浮动，允许两边都可以有浮动的对象。
- `left` 不允许该元素的左侧有浮动对象。
- `right` 不允许该元素的右侧有浮动元素。
- `both` 该元素左右两侧都不允许有浮动元素。

这样的解释其实会让人产生误解的。比如，对于下面的两个都向左浮动的元素：

<div style="width: 90%; height: 42px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 40%; height: 30px; padding: 0; margin: 0; background-color: green; float: left;">Div1</div>
  <div id="2" style="width: 40%; height: 30px; padding: 0; margin: 0; background-color: red; float: left;">Div2</div>
</div>

如果我们想让`Div2`不跟随在`Div1`的后面，而是处于`Div1`的下面，那么我们想当然的可能会设置`Div1`清除右浮动`clear: right `，但是这样是没有效果的。为什么呢？因为：

**对于`CSS`的浮动清除`clear`，只能影响使用清除样式的元素本身的行为，而不能影响其他元素。**

怎么去理解呢？以上面的例子为例：我们需要使`Div2`另起一行，不跟随在`Div1`的后面，也就是说，我们需要的是使`Div2`有所改变，那我们就需要将`Div2`设置清除，而且是清除左浮动`clear: left;`。而如果我们设置`Div1`清除右浮动是没有办法影响到`Div2`的，所以这样并不能让`Div2`移动到下一行中去。

同样，对于两个向右浮动的元素`Div1`和`Div2`，需要使他们各占一行，则还是需要设置处于“后面”的`Div2`清除浮动`clear: right;`。(此处的“后面”参见前面的介绍。)

同样，由于清除浮动只影响其自身，所以如果其后面还有其他浮动元素，其他浮动元素将会继续跟随在这个元素的后面。

比如，下面的示例中，设置了三个元素都左浮动，但是将`Div2`设置了清除左浮动`clear: left;`，可以看到，`Div3`依旧跟随在`Div2`后面：

<div style="width: 90%; height: 72px; padding: 0; margin: 0; border: 1px solid #eee; color: #000;">
  <div id="1" style="width: 40%; height: 30px; padding: 0; margin: 0; background-color: green; float: left;">Div1</div>
  <div id="2" style="width: 40%; height: 30px; padding: 0; margin: 0; background-color: red; float: left; clear: left;">Div2</div>
  <div id="3" style="width: 40%; height: 30px; padding: 0; margin: 0; background-color: yellow; float: left;">Div3</div>
</div>

## 0x05 用 BFC 撑开元素

BFC(块级格式上下文 Block Formatting Context)是一个独立的布局环境。通过设置产生一个新的 BFC，也能够消除浮动之后引起的父元素无法被撑开的问题。

由于浮动元素是会脱离正常文档流的，所以如果一个没有高度或者`height`是`auto`的容器的子元素是浮动元素，那么该容器的高度是不会被撑开的。我们通常会利用伪元素(`:after`或者`:before`)来解决这个问题。BFC 能包含浮动元素，也能解决容器高度不会被撑开的问题。

> 更多 BFC 相关的内容，可以见另一篇博客 [《CSS 中的 BFC》](http://lin07ux.github.io//2015/10/29/bfc-in-css/)

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

在这里，`div.container`的两个子元素是浮动元素，所以这个元素的高度就只有 20px（就是其内边距的大小）。而如果给这个元素添加一个`overflow: hidden`的属性，使其建立一个 BFC，那它就能被撑开了。具体效果见下图：

![用 BFC 撑开元素](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20BFC-float.png)

以上，就是关于浮动的一些解释，基本上掌握了浮动的布局和清除浮动的影响，就不会有太多的问题了。


