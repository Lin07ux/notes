> 转载声明：本文转载自 _ACFTOFE_ 的博客 [《“位置”在 css 里的细节》](http://acgtofe.com/posts/2015/10/xyz-in-css/)。有删节修改。

## 0x00 盒模型

盒模型是 CSS 中的基础布局模型。一个盒子，有边框，和其他盒子之间会有间距，和里面装的东西之间有距离，里面装的东西也有大小。一个元素也像盒子一样，有`content`，有`padding`，有`border`，有`margin`，这些形成一个元素的盒模型。

![盒模型](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20box-model.png)

在确定元素的准确位置的时候，需要借助图中的四个`edge`。它们按照范围，从小到大分别是：

- **content edge**  也叫做 **inner edge** （注意和 JavaScript 中的 innerWidth 区分开）。它围成的区域代表内容区，一般由元素内的具体内容决定。它确定该的范围叫做 content box，也是 CSS 属性`box-sizing`的默认值`content-box`的范围。

- **padding edge**  它确定 padding box 的范围。W3C 规范已经从`box-sizing`移除了这个值。

- **border edge**  确定 border box 的范围，对应`box-sizing`中的`border-box`值。

- **margin edge**  也叫做 **outer edge**，确定 margin box 的范围。`box-sizing`中也没有对应这个范围的值。

这就是一个盒模型最基本的构成，也是理解元素位置最重要的参考依据。

## 0x01 背景的位置

背景分为“背景图”(`background-image`) 和“背景色”(`background-color`)。其中，背景图位于背景色之上。如果使用了多个背景图，声明靠前的则位于上方。

> 渐变也属于背景图。

除`backround-clip`之外的其他 CSS 背景属性，如`background-postion`、`background-size`等，都只作用于背景图，对背景色没有作用。

> 因为背景色很单纯，没有起终点，总是铺满整个元素空间。

背景图和背景色的位置有如下的一些规则：

- 背景色铺满整个 border box；
- 默认情况下，背景色铺满整个 padding box，但背景色能够被调整显示在整个 border box 中；
- `background-clip`能够调整背景图/背景色的可见范围，但是不能调整背景图的起始点；
- `background-origin`能够调整背景图的起始点，默认值为`padding-box`。

对于一个定义了如下 CSS 样式的元素，同时有背景色和背景图，而且有内外边距，边框：

```css
.bg-element {
    width: 100px;
    height: 100px;
    margin: 20px;
    padding:20px;
    border: 5px dashed #386365;
    background: #2aace9 url(pattern.png) no-repeat;
}
```

其效果如下图：

![效果图1](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-1.png)

可以看出，在默认情况下，背景图的起始点为 padding box 的左上角，而背景色没有起始点，将铺满整个 border-box （margin box 不会有背景色）。

需要注意的是，背景图的可视范围也是 border box，而不是仅仅局限于 padding box。调整一下背景图的位置(`backround-position`)，或者扩展(`background-repeat`)，就能看出来：

![效果图2](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-2.png)

`background-clip`能够调整背景图/背景色的可见区域，但是并不能改变背景图的起始点。该属性的默认值是`border-box`，如果更改了，效果如下：

![效果图3](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-3.png)

无论怎么修改可见范围，这个例子中的背景图的起始点都是 padding box 左上角，这就是背景图起始点的概念。它对应的是css3中新增的`background-origin`，其默认值正是`padding-box`。如果修改了`background-origin`，那么属性`background-position`产生的位置偏移，包括`right`、`bottom`等关键字的情况，都会对应地改变参考的边。

## 0x02 定位元素的位置

元素默认情况下`position`属性的值是`static`，都处于正常文档流中。但是对于`position`为`absolute`或`fixed`，以及`float`不为`none`的元素，他们都会脱离正常文档流。但是这几种情况下的脱离，其实也并不完全相同。下面来看看这几种情况下的位置情况。

### 1、文档流

CSS 规范中这样描述绝对定位：

> In the absolute positioning model, a box is removed from the normal flow entirely and assigned a position with respect to a containing block.

请注意这里的`entirely`，这是在说，**绝对定位是完全脱离文档流的**。

为什么要强调完全呢？因为，脱离文档流是一个比较暧昧的概念，还有不完全的。请看浮动的描述：

> In the float model, a box is first laid out according to the normal flow , then taken out of the flow and shifted to the left or right as far as possible.

这里提到，浮动元素会先基于文档流取得一次位置，然后再向左或向右移动。所以，**浮动不是完全脱离文档流的**。

所以，对于下面的代码的中的情况，按照传统的“浮动元素是脱离文档流的”的理解，右浮动元素应该浮动到整个容器的右上角；但是这个右浮动元素只是浮动到了它所在行的右边，正是由于浮动不是完全脱离文档流的。

```html
<div class="scheme-container">
    <div class="scheme-element-normal">normal</div>
    <div class="scheme-element-float">float</div>
</div>
```

![效果图4](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-4.png)

### 2、absolute

看一下下面的例子：

HTML 代码：

```html
<div class="pos-container">
    <div class="pos-element"></div>
</div>
```

CSS 代码：

```css
.pos-container{
    position: relative;
    width: 140px;
    height: 140px;
    margin: 20px;
    padding: 20px;
    border: 5px dashed #789;
}
.pos-element{
    position: absolute;
    width: 70px;
    height: 70px;
    margin: 10px;
    padding: 20px;
    border: 5px dashed #a74;
    background: #e5c5a5;
    left: 0;
    top: 0;
}
```

显示的结果如下：

![效果图5](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-5.png)

注意两个元素都有完整的内外边距和边框，而此时`div.pos-element`的坐标是(0, 0)，图中间距为 10px。经过分析可以得知，这个 10px 间距来自`div.pos-element`的`margin`。所以可以得到什么结论呢？结论如下：

![效果图6](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-6.png)

首先，网页的平面坐标系和通常的数学平面直角坐标系不同，y 轴的正方向是朝下的。这种搭配的情况下，构成坐标系 x/y 轴的是用作容器的元素的 padding edge。其中 padding edge 的左上角即为坐标系的原点。

绝对定位的元素设置的 left、top 所形成的坐标位置点，位于该元素的 margin edge 的左上角。也就是，**绝对定位元素是用 margin edge 的左上角这个点来对齐坐标原点的。**

### 3、relative

处于正常文档流中的元素，其位置也略有不同。

- **正常文档流中的元素，都是 _完全_ 处于父元素的`content-box`范围内**

这里的“完全”，是指子元素的完整的盒模型都会被父元素包围。也即是说，正常流中的元素，会用其 margin edge 的左上角来对齐父元素的 content box 范围的左上角。（如果是从右向左排列的，则将“左上角”替换成“右上角”。）如下例所示：

```html
<div class="container">
    <div class="son"></div>
</div>
```

```css
.container {
	background-color: #912578;
	padding: 10px;
	margin: 10px;
	width: 100px;
}
.son {
	width: 50px;
	height: 50px;
	margin: 10px;
	padding: 10px;
	border: 5px dashed #239356;
	background-color: #65c567;
}
```

效果如下图：

![效果图9](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-9.png)

- **`display`被设置为`inline-block`的元素之间，`margin`不会相互重叠，否则会进行`margin`重叠计算**

关于元素的`margin`重叠，这里就不做具体展开，大致意思就是，两个设置了`margin`的相邻元素之间，他们的`margin`会相互重叠，而不是简单的保留每个元素的`margin`。但是对于声明`display: inline-block`的元素，则会将元素的`margin`完整的保留下来。
>想象两个真实的盒子，每个盒子都声明了自己和别的盒子之间需要保持一个距离 n，那么这俩个盒子之间只需要保留 n 的距离就够了，而不需要保留 2n 的距离。

对于有如下定义的元素，将子元素分别定义为`display: block`和`display: inline-block`，就可以看出他们之间的区别：

```html
<div class="container">
	<div class="son"></div>
	<div class="son"></div>
</div>
```

```css
.container {
	background-color: #912578;
	padding: 10px;
	margin: 10px;
	width: 120px;
}
.son {
	width: 50px;
	height: 50px;
	margin: 10px;
	padding: 10px;
	border: 5px dashed #239356;
	background-color: #65c567;
}
```

效果如下图所示，可以看出，被定义为`inline-block`的时候，两个子元素之间的间距明显增大，变成 20px。这一点是在设置元素位置的时候，也是需要加以考虑的。

![效果图10](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-10.png)

### 4、float

前面提到了浮动元素不是完全脱离正常文档流的，这有两方面的意思：

(1)、浮动元素可以影响文档流。

一般的文字环绕效果，就是将图片设置`float`之后形成的效果，这些位于文档流内的文字，仍然会为浮动元素留出空间，而并非互不相干。这其实是浮动元素影响行框（line box）的宽度的结果。

![效果图7](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-7.png)

(2)、浮动元素也会受到文档流的影响

规范里列出的 浮动元素的精确特性规则 中有这样一条：

> The outer top of a floating box may not be higher than the outer top of any block or floated box generated by an element earlier in the source document.

这里的`outer top`就是 margin edge (outer edge) 的 top edge。意思是，**浮动元素不可以高于任何在源文档之前的块元素或浮动元素**。

我们很熟悉浮动元素是会一个接一个地寻找空间排列的，但这一条却告诉我们，如果前面还有块元素，那么它们也会影响浮动元素的上边缘位置。如下图：

![效果图8](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-8.png)

那浮动元素的位置是如何确定的呢？

- **浮动元素之间的`margin`不会重叠**。这个和`inline-block`的元素相同。
- **浮动元素 _完全_ 处于父元素的`content-box`范围内**。这点和正常文档流中的元素相同。

对于下面的这个例子：

```
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

效果见下图：

![效果图11](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-11.png)

浮动元素的盒模型是完全从父元素的 content box 的左上角(右排显示则为右上角)开始计算的。所以在上面代码生成的效果中，浮动元素和父元素的上边界之间的距离是 20px。

同时，两个浮动元素之间的`margin`并没有产生重叠，而是完全的展现出来的。


## 0x03 结语

大部分情况下，元素的位置都是能够很好进行推测和设置的，但是如果要完全的弄清楚所有情况下元素的位置，还需要熟练掌握盒模型，并根据每种情况的特殊性进行分析才行。

关于 CSS 中的位置的相关内容，还可以参见我的另外一篇博客：[[CSS] BFC 块级格式化上下文](http://lin07ux.github.io/2015/10/29/bfc-in-css/)


