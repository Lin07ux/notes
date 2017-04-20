> 转载声明：本文转载自 _dwqs_ 的 [_《CSS 中的 BFC》_](http://www.ido321.com/1642.html)。

## 0x00 什么 BFC

在一个 Web 页面的 CSS 渲染中，BFC（块级格式化上下文 Block Fromatting Content）是按照块级盒子布局的。W3C 对 BFC 的定义如下：

> 浮动元素和绝对定位元素，非块级盒子的块级容器(例如`inline-block`，`table-cells`，和`table-captions`)，以及`overflow`值不为`visible`的块级盒子，都会为他们的内容创建新的 BFC（块级格式化上下文）。

为了便于理解，我们换一种方式来重新定义 BFC。

一个 HTML 元素要创建 BFC，则需要满足下列四个条件中的任意一个或多个：

1. `float`的值不是`none`；
2. `position`的值不是`static`或者`relative`；
3. `display`的值是`inline-block`、`table-cell`、`flex`、`table-caption`或者`inline-flex`；
4. `overflow`的值不是`visible`。

所以，有多种方式来创建 BFC，比如，给元素添加类似 `overflow: scroll`、`overflow: hidden`、`float: left`、`display: flex`、`display: table`、`display: inline-flex`或者`display: inline-block`的 CSS 规则即可。虽然上面的任意一条都能创建 BFC，但是各自也会有一些其他的影响，需要根据实际的需要来考虑。一般情况下，我们是可以通过设置`overflow: hidden`来创建的。


## 0x01 BFC 的影响

### 1、BFC 对盒模型对齐的影响

BFC 是一个独立的布局环境，其中的元素布局是不受外界的影响的，并且在一个 BFC 中，块盒和行盒（行盒由一行中所有的内联元素所组成）都会垂直的沿着其父元素的边框排列。W3C 中给出的规范是：

> 在 BFC 中，每一个盒子的左外边缘(margin-left)会触碰到容器的 content box 的左边缘(padding-left)（对于从右到左的格式来说，则触碰到右边缘）。浮动也是如此（尽管盒子里的行盒子 line box 可能由于浮动而变窄），除非盒子创建了一个新的 BFC（在这种情况下，这个盒子本身可能由于浮动而变窄）。

可以参考下面这个例子：

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
  width: 100px;
  overflow: hidden;
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

![BFC 对盒模型的影响](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-29%20BFC-float-1.png)

可以看到，这里两个子元素`div.son`和父元素的上边距、左边距、下边距都是 20px，说明子元素是完全包含在父元素的 content box 中的。也即是：子元素的左上角，将会对准父元素的 content box 的左上角。（对于从右到左的格式来说，则是子元素的右上角对准父元素的 content box 的右上角）。

### 2、BFC 对外边距折叠的影响

正常文档流布局时，盒子都是垂直排列，两者之间的间距由各自的外边距所决定，但不是简单的二者外边距之和。

在上面的例子中，我们可以看到，两个子元素都设置了外边距`margin: 10px`，但是他们之间的边距不是 20px，而是仅有 10px。这就是外边距折叠（Collapsing Margins）的结果。

BFC 可能会造成外边距折叠，也可以避免外边距折叠。

BFC 产生外边距折叠需要满足一个条件：**两个相邻元素要处于同一个BFC中**。所以，若两个相邻元素在不同的 BFC 中，就能避免外边距折叠。

对于上面的例子，我们可以设置资源的显示方式为`inline-block`，设置设置为浮动`float: left`都能够改变外边距折叠的影响。见下图：

> 有一点没有搞明白，如果设置这两个子元素的`overflow`不为`visible`并不能避免外边距折叠发生，谁能指导下？

![BFC 对盒折叠边距的影响](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-29%20BFC-float-2.png)

### 3、BFC 对浮动包含元素的影响

浮动元素是会脱离文档流的(绝对定位元素会脱离文档流)。如果一个没有高度或者`height`是`auto`的容器的子元素是浮动元素，则该容器的高度是不会被撑开的。我们通常会利用伪元素(`:after`或者`:before`)来解决这个问题。BFC 能包含浮动，也能解决容器高度不会被撑开的问题。

下面的这个例子：

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

如果父元素没有创建一个 BFC，那么父元素将不会被两个子元素所撑开。而如果将父元素设置了`overflow: hidden`，那么将给父元素创建一个 BFC，从而能够被子元素所撑开了。在这个新的 BFC 中浮动元素又回归到页面的常规流之中了。效果见下图：

![BFC 对浮动元素包含的影响](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-28%20effect-picture-11.png)

### 4、BFC 对行盒元素的影响

对于浮动图片和元素组成的布局中，会形成文字环绕图片的效果，如 Figure1 所示。之所以会出现这种状况，是由于浮动元素会影响行盒元素的宽度。

![BFC 对行盒的影响1](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-29%20BFC-float-3.jpg)

如果我们不想出现这样的效果，而是让图片和文字分开布局，如 Figure2 中那样，这时可以使用外边距，也可以用 BFC 来处理。


示例代码：

```html
<div class="container">
    <div class="floated">Floated div</div>
    <p>The width of a line box is determined by a containing block and the presence of floats. The height of a line box is determined by the rules given in the section on line height calculations.</p>
</div>
```

```css
.container {
	width: 340px;
	padding: 10px;
	border: 1px solid #ccc;
	border-radius: 3px;
}
.floated {
	float: left;
	width: 60px;
	height: 55px;
	margin: 10px;
	padding: 10px;
	background-color: #65c567;
	text-align: center;
}
p {
	margin: 10px;
	background-color: #912578;
}
```

给 div.floated 元素添加左浮动后，显示效果如下图中的左图所示，此时文字环绕左侧的浮动元素；给文字添加`overflow: hidden`样式之后，效果如下图右侧图所示，文字和浮动元素左右分开了。（如果给文字添加的是`display:inline-block`样式，那么文字会像一个块元素一样显示在浮动元素的下方了。）

![BFC 对行盒的影响1](http://7xkt52.com1.z0.glb.clouddn.com/2015-10-29%20BFC-float-4.png)

### 5、BFC 在多列布局中的应用

如果我们创建一个占满整个容器宽度的多列布局，在某些浏览器中最后一列有时候会掉到下一行。这可能是因为浏览器四舍五入了列宽从而所有列的总宽度会超出容器。但如果我们在多列布局中的最后一列里创建一个新的 BFC，它将总是占据其他列先占位完毕后剩下的空间。

例如：

```html
<div class="container">
    <div class="column">column 1</div>
    <div class="column">column 2</div>
    <div class="column">column 3</div>
</div>
```

```css
.column {
    width: 31.33%;
    background-color: green;
    float: left;
    margin: 0 1%;
}
/* Establishing a new block formatting context in the last column */
.column:last-child {
    float: none;
    overflow: hidden;
}
```

现在尽管盒子的宽度稍有改变，但布局不会打破。当然，对多列布局来说这不一定是个好办法，但能避免最后一列下掉。这个问题上弹性盒或许是个更好的解决方案，但这个办法可以用来说明元素在这些环境下的行为。


## 0x02 补充：外边距折叠

**外边距折叠**：在 CSS 当中，相邻的两个盒子（可能是兄弟关系也可能是祖先关系）的外边距可以结合成一个单独的外边距。

这种合并外边距的方式就被称为外边距折叠，并且因而所结合成的外边距称为折叠外边距。

折叠的结果按照如下**规则**计算：

- 两个相邻元素的外边距都是正数时，折叠结果是它们两者之间较大的值。
- 两个相邻元素的外边距都是负数时，折叠结果是两者绝对值的较大值。
- 两个相邻元素的外边距一正一负时，折叠结果是两者的相加的和。


