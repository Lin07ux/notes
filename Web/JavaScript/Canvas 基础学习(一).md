HTML5 新增的 <canvas> 元素类似一个图片元素，但是我们可以使用 JavaScript 在这个元素上绘制图像。

用 Canvas 绘制出的对象不属于页面 DOM 结构或者任何命名空间——这点被认为是一个缺陷。

Canvas 画布元素是一个矩形区域，可以用代码控制其每一像素。它拥有多种绘制路径、矩形、圆形、字符以及添加图像的方法，但是这些方法并不是定义在元素自身上，而是定义在通过这个元素的`getContent()`方法返回的对象上。

Canvas 元素和其他 HTML 元素的使用是一样的，区别仅仅在于它可以通过 JS 代码绘制图片而已。

[MDN Canvas](https://developer.mozilla.org/zh-CN/docs/Web/API/Canvas_API)

## 基础
### HTML 中的 Canvas
在页面中创建一个 <canvas> 元素和创建一般的元素没有什么不同，直接定义即可：

```html
<canvas id="myCanvas'>亲，您的浏览器不支持canvas</canvas>
```
在 <canvas> 标签之间的文字，会在浏览器不支持 <canvas> 的时候显示出来。一般会在这里做一个不兼容提示。

由于 <canvas> 要适配不同终端的分辨率，所以尽可能的在标签内设置高度和宽度，这个也符合 W3C 的标准：

```html
<canvas id="myCanvas" width="200" height="200">
    亲，您的浏览器不支持canvas
</canvas>
```

<canvas> 的`width`和`height`属性的单位是像素(px)，宽默认情况为 300，高默认为 150。

**注意**：<canvas> 元素没有 src 和 alt 属性，只有 width 和 height 属性。这两个属性均是可选的，而且可以通过 DOM 的 properties 来设置。

### Canvas 的 CSS 样式
同样，也可以通过 CSS 来定义 <canvas> 的样式，和其他 HTML 元素完全相同：

```css
canvas {
  background-color: #424242;
  width: 300px;
  height: 200px;
}
```

**注意**：不建议使用 CSS 定义 <canvas> 元素的宽高。因为，在渲染的过程中 <canvas> 元素中的内容会根据情况缩放来适应 CSS 设置的大小，而当 css 中设置的宽高比和其自身的 width/height 比不同时，往往会使其变形。(<canvas> 默认的宽高分别是 300 和 150)。

<canvas> 元素可以像任何一个普通的图像一样（有margin，border，background等属性）被设计。这些样式不会影响在 canvas 中的实际图像。当开始时没有为 canvas 规定样式规则，其将会完全透明。

### 在 JavaScript 中绘制图像
大多数 Canvas 绘图 API 都没有定义在`<canvas>`元素本身上，而是定义在通过画布的`getContext()`方法获得的一个**渲染上下文**对象上。所以，基本上我们要操作一个 canvas 元素都需要先获得的这个渲染上下文对象。

渲染上下文对象是通过 Canvas 对象的 getContext() 方法，并且把直接量字符串 "2d" 作为唯一的参数传递给它而获得的。

> 在未来，如果 <canvas> 标签扩展到支持 3D 绘图，getContext() 方法可能允许传递一个 "3d" 字符串参数。

下面是一个简单的示例，在 canvas 上绘制一个宽 150px、高 100px 的红色矩形。

```js
var canvas = document.getElementById('myCanvas');
var cxt    = canvas.getContext('2d');
cxt.fillStyle = '#ff0000';
cxt.fillRect(0, 0, 150, 100);
```

当然，考虑到兼容性，我们需要先验证 canvas API 的支持性：

```js
var canvas = document.getElementById('tutorial');

if (canvas.getContext){
  var ctx = canvas.getContext('2d');
  // drawing code here
} else {
  // canvas-unsupported code here
}
```

## 绘制图形 API
在绘制图形之前，需要先了解下画布栅格(canvas grid)以及坐标空间。

canvas 元素默认被网格所覆盖。通常来说网格中的一个单元相当于 canvas 元素中的一像素。栅格的起点为左上角(坐标为(0,0))。所有元素的位置都相对于原点定位。如下图所示。

![画布栅格](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-21%20Canvas_default_grid.png)

HTML 中的元素 canvas 只支持一种原生的图形绘制：矩形。所有其他的图形的绘制都至少需要生成一条路径。我们拥有众多路径生成的方法让复杂图形的绘制成为了可能。

> 其实矩形也是能够使用路径来绘制的。


### 路径
图形的基本元素是路径。路径是通过不同颜色和宽度的线段或曲线相连形成的不同形状的点的集合。一个路径，甚至一个子路径，都是闭合的。使用路径绘制图形需要一些额外的步骤：

- 首先，需要创建路径起始点；
- 然后使用画图命令去画出路径；
- 之后把路径封闭(可选)；
- 一旦路径生成，你就能通过描边或填充路径区域来渲染图形。

在 canvas 中，路径的绘制需要通过`beginPath()`和`closePath()`这两个函数进行包裹，主要用于分割各个画图，表示开始和结束。路径的绘制主要调用方法是`moveTo(x,y)`、`lineTo(x,y)`、`stroke()`、`arc()`、`arcTo()`、`fill()`，使用的属性包括`lineWidth`、`lineCap`、`lineJoin`、`strokeStyle`、`fillStyle`等。

结束绘制路径之前，可以使用`isPointInPath()`检测某个坐标是否在路径轨迹上，其返回的值是布尔值。

#### 1. 基本方法
绘制路径中，一般都会用到下面的四个方法。这四个方法并不是真正用来绘制具体的路径的，而是用于绘制路径的初始化或者渲染路径样式的。

- `beginPath()` 新建一条路径，生成之后，图形绘制命令被指向到路径上生成路径。
- `closePath()` 闭合路径之后图形绘制命令又重新指向到上下文中。
- `stroke()`    通过线条来绘制图形轮廓。
- `fill()`      通过填充路径的内容区域生成实心的图形。

上面这四个方法均不需要传入参数。

绘制路径之前，都要调用`beginPath()`方法来初始化一条路径。本质上，路径是由很多子路径构成，这些子路径都是在一个列表中，所有的子路径（线、弧形等）构成图形。而每次这个方法调用之后，列表清空重置，然后我们就可以重新绘制新的图形。

> 注意：当前路径为空，即调用`beginPath()`之后，或者 canvas 刚建的时候，第一条路径构造命令通常被视为是`moveTo()`，无论最后的是什么。出于这个原因，你几乎总是要在设置路径之后专门指定你的起始位置。

闭合路径`closePath()`不是必需的。这个方法会通过绘制一条从当前点到开始点的直线来闭合图形。如果图形是已经闭合了的，即当前点为开始点，该函数什么也不做。

> 注意：当你调用`fill()`函数时，所有没有闭合的形状都会自动闭合，所以你不需要调用`closePath()`函数。但是调用`stroke()`时不会自动闭合。


#### 2. 移动笔触
`moveTo()`方法不会再画布上绘制出任何效果，但却是上面介绍的路径列表中的一部分。它的作用就是移动绘制路径的起始点到一个指定的坐标位置。可以想象一下在纸上作业，一支钢笔或者铅笔的笔尖从一个点到另一个点的移动过程。

> 如果在绘制路径之前不调用这个方法，则不会有任何效果。

语法：

```js
moveTo(x, y);
```

参数：
    两个参数分别表示指定位置的 x 和 y 坐标。


#### 3. 直线
绘制直线，需要用到的方法`lineTo()`。

语法：

```js
lineTo(x, y)
```

参数：
    两个参数分别表示坐标系中直线结束的点的坐标。
    
> 直线的开始点和之前的绘制路径有关，之前路径的结束点就是接下来的开始点，也可以通过`moveTo()`方法改变。


```js
// 使用线段绘制一个填充三角形
var canvas = document.getElementById('myCanvas');
var cxt = canvas.getContext('2d');

cxt.beginPath();
cxt.moveTo(25, 25);
cxt.lineTo(105, 25);
cxt.lineTo(25, 105);
cxt.fill();  // 默认填充色为黑色

// 使用线段绘制一个描边三角形
cxt.beginPath();
cxt.moveTo(125, 125);
cxt.lineTo(125, 45);
cxt.lineTo(45, 125);
cxt.closePath();  // 描边图形需要手动闭合路径，否则不会闭合
cxt.stroke();  // 默认描边色为黑色
```

#### 4. 圆弧
使用`arc()`方法绘制圆弧或者圆。当然可以使用`arcTo()`，不过这个的现实并不是那么的可靠，所以不作介绍。

- `arc(x, y, radius, startAngle, endAngle, anticlockwise)`
    画一个以（x,y）为圆心的以 radius 为半径的圆弧（圆），从 startAngle 开始到 endAngle 结束，按照 anticlockwise 给定的方向（默认为顺时针）来生成。
    anticlockwise 为 true 时，是逆时针方向，否则顺时针方向。但是，不管其为何值，角度的计算都是按顺时针的。也就是说，这个参数决定的是绘制的从起始角度到结束角度的两段弧线中的哪一段。
    
- `arcTo(x1, y1, x2, y2, radius)`
    根据给定的控制点和半径画一段圆弧，再以直线连接两个控制点。

> 注意：`arc()`函数中的角度单位是弧度，不是度数。角度与弧度的 js 表达式：`radians=(Math.PI/180)*degrees`。

圆弧的绘制和直线的绘制是一样的，区别仅仅在于参数的不同。


#### 5. 贝塞尔曲线
二次和三次贝塞尔曲线十分有用，一般用来绘制较为复杂但有规律的图形。

- `quadraticCurveTo(cp1x, cp1y, x, y)`
    绘制二次贝塞尔曲线，(x,y) 为结束点坐标，(cp1x,cp1y) 为控制点坐标。
    
- `bezierCurveTo(cp1x, cp1y, cp2x, cp2y, x, y)`
    绘制三次贝塞尔曲线，(x,y) 为结束点，(cp1x,cp1y) 为控制点一的坐标，(cp2x,cp2y)为控制点二坐标。
    
![二次和三次贝塞尔曲线](https://mdn.mozillademos.org/files/223/Canvas_curves.png)

使用贝塞尔曲线绘制图形和使用直线绘制图形的过程没有什么不同，都是先调用`beginPath()`方法(和`moveTo()`方法)，然后调用贝塞尔曲线方法。


### 矩形
canvas 提供了三种方法绘制矩形，分别会生成不同的类型，还提供了一个清除矩形区域的方法用来涂掉一个区域的内容：

- `fillRect(x, y, width, height)`    绘制一个填充的矩形
- `strokeRect(x, y, width, height)`  绘制一个矩形的边框
- `rect(x, y, width, height)`        将一个矩形路径增加到当前路径上
- `clearRect(x, y, width, height)`   清除指定矩形区域，让清除部分完全透明

这四个方法均有四个参数，含义分别如下：

* x 矩形左上角的 x 坐标(相对于画布栅格起点)
* y 矩形左上角的 y 坐标(相对于画布栅格起点)
* width 矩形的宽度
* height 矩形的高度

> 当`rect()`方法执行的时候，`moveTo()`方法自动设置坐标参数（0,0）。也就是说，当前笔触自动重置会默认坐标。 

绘制之前，可以设置相关的属性来影响绘制的图形的颜色样式：
* `fillStyle`属性决定`fillRect()`方法绘制的矩形的样式；
* `strokeStyle`属性决定`strokeRect()`方法绘制的矩形的样式；

示例，下面的代码绘制了一个红色矩形，一个淡蓝色边框，然后被清除掉了一部分，从而显示出了背景色：

```js
var canvas = document.getElementById('myCanvas');
var cxt    = canvas.getContext('2d');
cxt.fillStyle = '#ff0000';
cxt.fillRect(0, 0, 150, 100);

cxt.strokeStyle = 'rgba(0, 0, 200, .5)';
cxt.strokeRect(20, 20, 100, 100);

cxt.clearRect(50, 50, 100, 100);
```

效果图：

![Canvas 矩形](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-21%20Canvas-rect.png)


### Path2D 对象
[MDN Path2D 文档](https://developer.mozilla.org/zh-CN/docs/Web/API/Path2D)

Path2D 对象允许在 canvas 中根据需要创建可以保留并重用的路径。类似于 DOM 操作中创建一个 DOM 片段，这个创建出来的路径并没有在 canvas 中立即显示，除非显式的将其绘制到 canvas 中。

> 这个对象在较新的浏览器中可以使用，兼容性有一定的问题。

创建方法：

```js
new Path2D();     // 空的Path对象
new Path2D(path); // 克隆Path对象
new Path2D(d);    // 从SVG建立Path对象
```

`Path2D()`会返回一个新初始化的 Path2D 对象（可以将某一个路径作为变量来创建一个它的副本，或者将一个包含 SVG path 数据的字符串作为变量）。

所有的路径方法比如`moveTo`，`rect`，`arc`或`quadraticCurveTo`等，都可以在 Path2D 中使用。

示例：

```js
var canvas = document.getElementById('myCanvas');
var cxt = canvas.getContext('2d');

// 创建一个 Path2D 对象
var rect = new Path2D();
rect.rect(10, 10, 50, 50);

// 创建另一个 Path2D 对象
var circle = new Path2D();
circle.moveTo(125, 35);
circle.arc(100, 35, 25, 0, 2 * Math.PI);

// 绘制 Path2D 对象到 canvas 中
cxt.stroke(rect);
cxt.fill(circle);

// 还可以使用 SVG Path 数据来新建对象
// 这条路径将先移动到点 (M30 40) 然后再水平移动120个单位 (h 120)，
// 然后下移80个单位 (v 80)，接着左移80个单位 (h -80)，再回到起点处 (z)。
var p = new Path2D('M30 40 h 120 v 80 h -80 Z');
cxt.stroke(p);
```

Path2D API 添加了`addPath`作为将 path 结合起来的方法。当你想要从几个元素中来创建对象时，这将会很实用。

```js
Path2D.addPath(path [, transform])​
// 添加一条路径到当前路径（可能添加了一个变换矩阵）。
```


## 样式和颜色
在前面的示例中，只用到默认的线条和填充样式。实际上，canvas 有很多的样式，可以绘制出真正吸引人的内容。

### 颜色
绘制图形时有两种方法来展现效果：fill 和 stroke。对这两个方法，可以分别使用`fillStyle`和`strokeStyle`两个属性来影响其绘制出来的效果。

- `fillStyle = color`   设置图形的填充颜色。
- `strokeStyle = color` 设置图形轮廓的颜色。

属性的值 color 可以是表示 CSS 颜色值的字符串，渐变对象或者图案对象。默认情况下，线条和填充颜色都是黑色(CSS 颜色值`#000000`)。

> 注意：一旦设置了`strokeStyle`或者`fillStyle`的值，那么这个新值就会成为之后新绘制的图形的默认颜色。所以，如果要给每个图形上不同的颜色，需要在绘制图形时重新设置`fillStyle`或`strokeStyle`的值。

对于输入的颜色值，应该是符合 [CSS3 颜色值标准](https://www.w3.org/TR/2003/CR-css3-color-20030514/#numerical) 的有效字符串。下面的例子都表示的是同一种颜色：

```js
// 这些 fillStyle 的值均为 '橙色'
ctx.fillStyle = "orange";
ctx.fillStyle = "#FFA500";
ctx.fillStyle = "rgb(255,165,0)";
ctx.fillStyle = "rgba(255,165,0,1)";
```

示例：

```js
// fillStyle 示例：绘制36个色块
function draw() {
  var ctx = document.getElementById('canvas').getContext('2d');
  for (var i=0;i<6;i++){
    for (var j=0;j<6;j++){
      ctx.fillStyle = 'rgb(' + Math.floor(255-42.5*i) + ',' + 
                       Math.floor(255-42.5*j) + ',0)';
      ctx.fillRect(j*25,i*25,25,25);
    }
  }
}

// strokeStyle 示例：绘制36个描边圆
function draw() {
  var ctx = document.getElementById('canvas').getContext('2d');
  for (var i=0;i<6;i++){
    for (var j=0;j<6;j++){
      ctx.strokeStyle = 'rgb(0,' + Math.floor(255-42.5*i) + ',' + 
                    Math.floor(255-42.5*j) + ')';
      ctx.beginPath();
      ctx.arc(12.5+j*25,12.5+i*25,10,0,Math.PI*2,true);
      ctx.stroke();
    }
  }
}
```

[查看效果](http://codepen.io/Lin07ux/pen/PNBaga?editors=0010)

### 透明度
除了可以绘制实色图形，我们还可以用 canvas 来绘制半透明的图形。通过设置`globalAlpha`属性或者使用一个半透明颜色作为轮廓或填充的样式。

- `globalAlpha = transparencyValue`

这个属性影响到 canvas 里所有图形的透明度，有效的值范围是 0.0 （完全透明）到 1.0（完全不透明），默认是 1.0。

> globalAlpha 属性和 fillStyle、strokeStyle 属性一样，都是一旦设置就会影响后面的新建图形的效果，所以如果需要不同的透明度，需要每次绘制之前更改 globalAlpha 属性。

由于是影响 canvas 里所有图形的透明度，所有 globalAlpha 属性在需要绘制大量拥有相同透明度的图形时候相当高效。而在一般情况下，使用透明颜色(rgba)则可控性更强一些。

示例：

```js
var ctx = document.getElementById('canvas').getContext('2d');

// 画背景
ctx.fillStyle = '#FD0';
ctx.fillRect(0,0,75,75);
ctx.fillStyle = '#6C0';
ctx.fillRect(75,0,75,75);
ctx.fillStyle = '#09F';
ctx.fillRect(0,75,75,75);
ctx.fillStyle = '#F30';
ctx.fillRect(75,75,75,75);

ctx.fillStyle = '#FFF';

// 设置透明度值
ctx.globalAlpha = 0.2;

// 画半透明圆
for (var i=0;i<7;i++){
 ctx.beginPath();
 ctx.arc(75,75,10+10*i,0,Math.PI*2,true);
 ctx.fill();
}
```

在这个例子里，将用四色格作为背景，设置 globalAlpha 为 0.2 后，在上面画一系列半径递增的半透明圆。最终结果是一个径向渐变效果。圆叠加得越更多，原先所画的圆的透明度会越低。通过增加循环次数，画更多的圆，背景图的中心部分会完全消失。

[查看效果](http://codepen.io/Lin07ux/pen/LNBrwy?editors=0010)

### 线型样式
有很多属性来控制绘制的路径线条的样式，可以影响线的宽度、末端样式、接合样式等等。

#### 线段宽度
线段宽度可以使用`lineWidth`属性来控制：

`lineWidth = value`

值是线段宽度的数字，不需要设置单位。 0、负数、Infinity 和 NaN 会被忽略。

线宽是指给定路径的中心到两边的粗细。换句话说就是**在路径的两边各绘制线宽的一半**。因为画布的坐标并不和像素直接对应，当需要获得精确的水平或垂直线的时候要特别注意。

一般在画布的边缘以及所有宽度为奇数的线并不能精确呈现，这就是因为路径的定位问题。



想要获得精确的线条，必须对线条是如何描绘出来的有所理解。见下图，用网格来代表 canvas 的坐标格，每一格对应屏幕上一个像素点。在第一个图中，填充了 (2,1) 至 (5,5) 的矩形，整个区域的边界刚好落在像素边缘上，这样就可以得到的矩形有着清晰的边缘。

![Canvas lineWidth](http://7xkt52.com1.z0.glb.clouddn.com/2016-04-23%20canvas-lineWidth.png)

如果你想要绘制一条从 (3,1) 到 (3,5)，宽度是 1.0 的线条，你会得到像第二幅图一样的结果。实际填充区域（深蓝色部分）仅仅延伸至路径两旁各一半像素。而这半个像素又会以近似的方式进行渲染，这意味着那些像素只是部分着色，结果就是以实际笔触颜色一半色调的颜色来填充整个区域（浅蓝和深蓝的部分）。这就是上例中为何宽度为 1.0 的线并不准确的原因。

要解决这个问题，你必须对路径施以更加精确的控制。已知粗 1.0 的线条会在路径两边各延伸半像素，那么像第三幅图那样绘制从 (3.5,1) 到 (3.5,5) 的线条，其边缘正好落在像素边界，填充出来就是准确的宽为 1.0 的线条。

对于那些宽度为偶数的线条，每一边的像素数都是整数，那么你想要其路径是落在像素点之间 (如那从 (3,1) 到 (3,5)) 而不是在像素点的中间。同样，注意到那个例子的垂直线条，其 Y 坐标刚好落在网格线上，如果不是的话，端点上同样会出现半渲染的像素点。

虽然开始处理可缩放的 2D 图形时会有点小痛苦，但是及早注意到像素网格与路径位置之间的关系，可以确保图形在经过缩放或者其它任何变形后都可以保持看上去蛮好：线宽为 1.0 的垂线在放大 2 倍后，会变成清晰的线宽为 2.0，并且出现在它应该出现的位置上。
    
#### 末端样式
`lineCap`属性用来设置线条末端的样式。

`lineCap = type`

有三个可选值：`butt|round|square`。默认值是`butt`。

三个值的效果分别是：

* 线段末端以方形结束；
* 线段末端以圆形结束，但是在两端增加了一个半圆，这个半圆的直径是线段的宽度；
* 线段末端以方形结束，但是增加了一个宽度和线段相同，高度是线段宽度一半的矩形区域。

下面的示例能看出这三者的区别：

```js
var canvas = document.getElementById('myCanvas');
var ctx = canvas.getContext('2d');

ctx.fillStyle = '#F7F7F7';
ctx.fillRect(10, 10, 200, 150);

ctx.strokeStyle = '#09f';
ctx.beginPath();
ctx.moveTo(10,20);
ctx.lineTo(210,20);
ctx.moveTo(10,140);
ctx.lineTo(210,140);
ctx.stroke();

var lineCap = ['butt','round','square'];
// Draw lines
ctx.strokeStyle = 'black';
for (var i = 0; i < lineCap.length; i++) {
  ctx.lineWidth = 15;
  ctx.lineCap = lineCap[i];
  ctx.beginPath();
  ctx.moveTo(55+i*50, 20);
  ctx.lineTo(55+i*50,140);
  ctx.stroke();
}
```
在这个例子中绘制了3条线段， 每条线段都设置了不同的 lineCap 属性值。通过2条导航线能够精确地看到3条已绘制线段之间的不同。 每条线段的顶端和末端都能在导航线上准确的反映出来。

[查看示例](http://codepen.io/Lin07ux/pen/aNjjvp?editors=0010)


#### 线条接合处样式
当两个线条接合时，可以通过`lineJoin`属性来设置其接合样式。

`lineJoin = type`

用来设置2个长度不为0的相连部分（线段，圆弧，曲线）如何连接在一起的属性（长度为0的变形部分，其指定的末端和控制点在同一位置，会被忽略）。

> 注意：如果2个相连部分在同一方向，那么`lineJoin`不会产生任何效果，因为在这种情况下不会出现连接区域。

有三个可选值，默认值是`miter`：

* `bevel` 在相连部分的末端填充一个额外的以三角形为底的区域， 每个部分都有各自独立的矩形拐角。
* `round` 通过填充一个额外的，圆心在相连部分末端的扇形，绘制拐角的形状。 圆角的半径是线段的宽度。
* `miter` 通过延伸相连部分的外边缘，使其相交于一点，形成一个额外的菱形区域。这个设置可以通过`miterLimit`属性看到效果。

示例：

```js
var ctx = document.getElementById('myCanvas').getContext('2d');
var lineJoin = ['round','bevel','miter'];
ctx.lineWidth = 10;

for (var i = 0; i < lineJoin.length; i++) {
  ctx.lineJoin = lineJoin[i];
  ctx.beginPath();
  ctx.moveTo(15,15+i*40);
  ctx.lineTo(55,55+i*40);
  ctx.lineTo(95,15+i*40);
  ctx.lineTo(135,55+i*40);
  ctx.lineTo(175,15+i*40);
  ctx.stroke();
}
```

最上面一条是 round 的效果，边角处被磨圆了，圆的半径等于线宽。中间和最下面一条分别是 bevel 和 miter 的效果。当值是 miter 的时候，线段会在连接处外侧延伸直至交于一点，延伸效果受到下面将要介绍的 miterLimit 属性的制约。

[查看示例](http://codepen.io/Lin07ux/pen/BKPPrg?editors=0010)


#### 交接最大长度
交接处长度（斜接长度）是指线条交接处内角顶点到外角顶点的长度。`miterLimit`属性就是用来限制交接处最大长度的。

`miterLimit = value`

当获取属性值时，会返回当前的值（默认值是 10.0）。当给属性赋值时，0、负数、 Infinity 和 NaN 都会被忽略；除此之外都会被赋予一个新值。

当线段接合处使用 miter 效果，线段的外侧边缘会延伸交汇于一点上。线段直接夹角比较大的，交点不会太远，但当夹角减少时，交点距离会呈指数级增大。miterLimit 属性就是用来设定外延交点与连接点的最大距离，如果交点距离大于此值，连接效果会变成了 bevel，即直接拉平显示。

> 这个属性的具体行为并不是那么明显。


#### 虚线
前面介绍的方法画出来的都是实线，如果需要画实线，可以先调用`setLineDash()`方法，然后画线的时候就会画出虚线了。虚线段可以使用在所有的路径和矩形绘制中。

同时，对于设置的虚线格式，还可以使用`getLineDash()`方法来获取到；还可以使用`lineDashOffset`属性来设置虚线的起始偏移量。

1. 设置虚线样式

`setLineDash(Array)`方法设置当前虚线样式。

其参数是一个数组，数组的内容是一组描述交替绘制线段长度和间距（坐标空间单位）长度的数字。如果数组元素的数量是奇数，数组的元素会被隐式处理：复制并拼接到数组后面。例如， [5, 15, 25] 会变成 [5, 15, 25, 5, 15, 25]。

2. 获取虚线样式

`getLineDash()`方法获取当前虚线的样式。

其返回值是一个数组，数组内容是一组描述交替绘制线段和间距（坐标空间单位）长度的数字。其实就是获取调用`setLineDash()`方法设置的虚线样式的数组(经过处理后的，即是将奇数长度的样式数组复制并拼接之后的样式数组。) 例如，设置线段为 [5, 15, 25] 将会得到以下返回值 [5, 15, 25, 5, 15, 25]。

3. 虚线的效果起始偏移量

`lineDashOffset` 设置当前虚线的起始偏移量。偏移量是 float 精度的数字。初始值为 0.0。

当设置一个正值后，会将整个虚线段的显示效果向线段的起始位置进行偏移；设置一个负值后，显示效果会向线段的终点偏移。

> 注意：不管偏移量是多少，线段的长度并不会发生变化，变化的仅仅是显示的效果而已。


4. 示例

基础演示：

```js
var ctx = document.getElementById('myCanvas').getContext('2d');
var lineJoin = ['round','bevel','miter'];
ctx.lineWidth = 10;
ctx.lineDashOffset = 4; // 虚线段的效果偏移

// 虚线的格式为：第一段线段宽5，间隔10，第二个线段宽5，间隔5
// 第三个线段宽10，间隔5，然后依次重复这三个线段和间隔
ctx.setLineDash([5, 10, 5]);
console.log(ctx.getLineDash()); // [5, 10, 5, 5, 10, 5]

ctx.beginPath();
ctx.moveTo(0,100);
ctx.lineTo(400, 100);
ctx.stroke();
```

[查看效果](http://codepen.io/Lin07ux/pen/bpjxBe?editors=0010)

跑马灯效果：

```js
var canvas = document.getElementById("canvas");
var ctx = canvas.getContext("2d");
var offset = 0;

function draw() {
  ctx.clearRect(0,0, canvas.width, canvas.height);
  ctx.setLineDash([4, 2]);
  ctx.lineDashOffset = -offset;
  ctx.strokeRect(10,10, 100, 100);
}

function march() {
  offset++;
  if (offset > 16) {
    offset = 0;
  }
  draw();
  setTimeout(march, 20);
}

march();
```

### 渐变
就好像一般的绘图软件一样，我们可以用线性或者径向的渐变来填充或描边。我们用下面的方法新建一个 canvasGradient 对象，然后将这个对象赋给图形的 fillStyle 或 strokeStyle 属性作为填充或者描边颜色，从而能够实现渐变的效果。

1. 线性渐变
使用线性渐变需要先使用`createLinearGradient()`方法创建一个 canvasGradient 对象：

`createLinearGradient(x1, y1, x2, y2)`

该方法有四个参数：

* 前两个表示渐变的起点坐标(x1, y1)
* 后两个表示渐变的终点坐标(x2, y2)


2. 径向渐变

镜像渐变使用`createRadialGradient()`方法来创建：

`createRadialGradient(x1, y1, r1, x2, y2, r2)`

该方法有六个参数：

* 前三个定义一个以 (x1,y1) 为原点，半径为 r1 的圆
* 后三个参数则定义另一个以 (x2,y2) 为原点，半径为 r2 的圆。

3. 添加渐变色

使用上述的方法创建出 canvasGradient 对象之后，就可以使用这个对象的`addColorStop()`方法来设置渐变的颜色了：

`addColorStop(position, color)`

这个方法接受 2 个参数：

* position 参数必须是一个 0.0 与 1.0 之间的数值，表示渐变中颜色所在的相对位置。例如，0.5 表示颜色会出现在正中间。如果偏移值不在0到1之间，将抛出`INDEX_SIZE_ERR`错误。
* color 参数必须是一个有效的 CSS 颜色值（如 #FFF，rgba(0,0,0,1)等）。如果颜色值不能被解析为有效的CSS颜色值 <color>，将抛出`SYNTAX_ERR`错误。

可以根据需要，多次调用该方法，从而能够添加多种渐变色。

示例：

```js
var canvas = document.getElementById('myCanvas');
var ctx = canvas.getContext('2d');

// 线性渐变
var gradient_linear = ctx.createLinearGradient(10, 10, 100, 10);
gradient_linear.addColorStop(0, 'green');
gradient_linear.addColorStop(1, 'white');
ctx.fillStyle = gradient_linear;
ctx.fillRect(10, 10, 100, 80);

// 径向渐变
var gradient_radial = ctx.createRadialGradient(220, 140, 40, 220, 140, 0);
gradient_radial.addColorStop(0, 'white');
gradient_radial.addColorStop(1, 'green');
ctx.fillStyle = gradient_radial;
ctx.fillRect(180, 100, 80, 80);
```

[查看效果](http://codepen.io/Lin07ux/pen/NNBLBg?editors=0010)

### 图案
同样，我们也可以使用一个图案来作为填充或者描边的样式。

图案的应用跟渐变很类似的，使用`createPattern()`方法创建出一个 pattern 之后，赋给 fillStyle 或 strokeStyle 属性即可。

`createPattern(image, type)`

该方法接受两个参数：

* Image 可以是一个 Image 对象的引用，或者另一个 canvas 对象。
* Type 必须是下面的字符串值之一：repeat，repeat-x，repeat-y 和 no-repeat。

> 如果使用图片来创建，需要确认 image 对象已经装载完毕，否则图案可能效果不对的。这和 drawImage 不同。

示例：

```js
var ctx = document.getElementById('canvas').getContext('2d');

// 创建新 image 对象，用作图案
var img = new Image();
img.src = 'images/wallpaper.png';
img.onload = function(){

// 创建图案
var ptrn = ctx.createPattern(img,'repeat');
ctx.fillStyle = ptrn;
ctx.fillRect(0,0,150,150);
```

### 阴影
在 canvas 中也可以使用和 CSS 中的 box-shadow 的效果差不过阴影样式。这需要下面的四个属性来配合。

- `shadowOffsetX = float`
shadowOffsetX 用来设定阴影在 X 轴的延伸距离，它不受变换矩阵所影响的。负值表示阴影会往左延伸，正值则表示会往右延伸，默认为 0。

- `shadowOffsetY = float`
shadowOffsetY 用来设定阴影在 Y 轴的延伸距离，它不受变换矩阵所影响的。负值表示阴影会往上延伸，正值则表示会往下延伸，默认都为 0。

- `shadowBlur = float`
shadowBlur 用于设定阴影的模糊程度，其数值并不跟像素数量挂钩，也不受变换矩阵的影响，默认为 0。

- `shadowColor = color`
shadowColor 是标准的 CSS 颜色值，用于设定阴影颜色效果，默认是全透明的黑色。

示例，文字阴影：

```js
var ctx = document.getElementById('canvas').getContext('2d');

ctx.shadowOffsetX = 2;
ctx.shadowOffsetY = 2;
ctx.shadowBlur = 2;
ctx.shadowColor = "rgba(0, 0, 0, 0.5)";
 
ctx.font = "20px Times New Roman";
ctx.fillStyle = "Black";
ctx.fillText("Sample String", 5, 30);
```

### 填充规则
当我们用到 fill（或者 clip 和 isPointinPath）时，可以选择一个填充规则，该填充规则根据某处在路径的外面或者里面来决定该处是否被填充，这对于自己与自己路径相交或者路径被嵌套的时候是有用的。

两个可能的值：

- `nonzero` non-zero winding rule，默认值。全填充。
- `evenodd` even-odd winding rule。填充路径之间围起来的区域

这个例子，我们用填充规则 evenodd

```js
var canvas = document.getElementById('myCanvas');
var ctx = canvas.getContext('2d');

// 使用填充规则 nonzero
ctx.beginPath(); 
ctx.arc(50, 50, 30, 0, Math.PI*2, true);
ctx.arc(50, 50, 15, 0, Math.PI*2, true);
ctx.fill("nonzero");

// 使用填充规则 evenodd
ctx.beginPath(); 
ctx.arc(150, 50, 30, 0, Math.PI*2, true);
ctx.arc(150, 50, 15, 0, Math.PI*2, true);
ctx.fill("evenodd");
```

[查看效果](http://codepen.io/Lin07ux/pen/eZjLxP?editors=0010)


## 绘制文本
绘制文本通常有三个方法:

* `fillText()` 推荐使用
* `strokeText()`
* `measureText()`：一个参数，即要绘制的文本 
这两个方法都可以接收 4 个参数：要绘制的文本字符串，x 坐标，y 坐标和可选的最大像素值。而且这三个方法都以下列 3 个属性为基础：

* `font`：表示文本样式，大小及字体，用 CSS 中指定字体的格式来指定。
* `textAlign`：表示文本对其方式。可能的值有"start"、"end"、"left"、"right"和"center"。不推荐使用"left"和"right"。
* `textBaseline`：表示文本的基线。可能的值有"top"、"hanging"、"middle"、"alphabetic"、"ideographic"和"bottom"。值为 top，y 坐标表示文本顶端；值为 bottom ，y 坐标表示文本底端；值为 hanging、alphabetic 和 ideographic，则 y 坐标分别指向字体的特定基线坐标。 
如：

```javascript
var drawing = document.getElementById("drawing");

// 确定浏览器是否支持canvas元素
if (drawing.getContext) {
    var context = drawing.getContext("2d");
    // font 样式
    context.font = "24px monospace";
    // 对齐方式
    context.textAlign = "start";
    // 基线位置
    context.textBaseline = "bottom";
    // 填充样式
    context.fillStyle = "red";
    context.fillText("hello there",100,100);
    // 描边样式
    context.strokeStyle = "yellow";
    context.strokeText("hello there",100,100);
}
```

对于`measureText()`方法，会返回测量字符串相关数据的一个对象，目前只有 width 属性。

```javascript
// 返回 TextMetrics 对象，该对象目前只有 width 属性
console.log(context.measureText("Hello world"));
```

## 变换
* `rotate(angle)`：围绕原点旋转图像 angle 弧度。
* `scale(scaleX, scaleY)`：缩放图像,在X方向乘以scaleX,在y方向乘以scaleY.scaleX和scaleY的默认值是1.0 
	•	translate(x, y):将坐标原定移动到(x, y).执行这个变换之后,坐标(0,0)会变成之前由(x,y)表示的点. 
transform(m1_1, m1_2, m2_1, m2_2, dx, dy):直接修改变换矩阵, 



## 导出 canvas
`toDataURL()`可以方法导出在 canvas 元素上绘制的图像。

```javascript
var drawing = document.getElementById("drawing");

// 确定浏览器是否支持canvas元素
if (drawing.getContext) {
    // 取得图像数据的URL
    var imgURL = drawing.toDataURL("image/png");

    // 显示图像
    var image = document.createElement("img");
    image.src = imgURL;
    document.body.appendChild(image);
}
```


