## 简介
SVG（Scalable Vector Graphics）是可缩放矢量图形的缩写，基于可扩展标记语言 XML 来描述二维矢量图形的一种图形格式。

特点如下：

* 和 PNG、GIF 比较起来，**文件体积更小，且可压缩性强**；
* 由于采用 XML 描述，可以**轻易的被读取和修改**，描述性更强；
* 在放大或改变尺寸的情况下其图形质量不会有所损失，与分辨率无关，是**可伸缩**的；
* SVG 是面向未来 (W3C 标准)的，同时浏览器兼容性好；
* 使用 CSS 和 JS 能很**方便的进行控制**，同时可以很轻易地描述路径动画；

SVG 文件可以像图片文件一样在 HTML 和 CSS 中被引用。


## SVG 基本形状
SVG 中，预定义了 6 种基本的形状：`rect`、`circle`、`ellipse`、`line`、`polyline`、`polygon`，这六种基本形状都可以通过`path`路径转换实现。

![SVG 六种基本形状](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484654797629.png)

这六种形状的基本属性如下：

![六种形状的基本属性](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1485783241809.png)


### 1. rect 矩形
SVG 中`rect`元素用于绘制矩形、圆角矩形，含有 6 个基本属性用于控制矩形的形状以及坐标，具体如下：

- `x`      矩形左上角 x 位置，默认值为 0 
- `y`      矩形左上角 y 位置，默认值为 0
- `rx`     圆角 x 方向半径，不能为负值否则报错
- `ry`     圆角 y 方向半径，不能为负值否则报错
- `width`  矩形的宽度，不能为负值，否则报错。0 值不绘制
- `height` 矩形的高度，不能为负值，否则报错。0 值不绘制

这里需要注意，`rx`和`ry`的还有如下规则：

* `rx`和`ry`都没有设置，则`rx = 0, ry = 0`
* `rx`和`ry`有一个值为 0，则相当于`rx = 0, ry = 0`，圆角无效
* `rx`和`ry`有一个被设置，则全部取这个被设置的值
* `rx`的最大值为`width`的一半，`ry`的最大值为`height`的一半

> 使用 JavaScript 代码表述如下：
> 
> ```javascript
> rx = rx || ry || 0;
> ry = ry || rx || 0;
> 
> rx = rx > width / 2 ? width / 2 : rx;
> ry = ry > height / 2 ? height / 2 : ry;
> 
> if (0 === rx || 0 === ry) {
>   rx = 0,
>   ry = 0;  //圆角不生效，等同于，rx，ry都为0
> }
> ```

示例：

```xml
<svg width="240" height="240" viewBox="0 0 120 120"
  xmlns="http://www.w3.org/2000/svg">
  
  <rect x="10" y="10" width="40" height="40" />
  <rect x="60" y="10" rx="10" ry="10" width="40" height="40" fill="#1baae6" />
</svg>
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484656171054.png" width="231"/>


### 2. circle 圆形
SVG 中`circle`元素用于绘制圆形，含有 3 个基本属性用于控制圆形的坐标以及半径，具体如下：

- `r`  半径
- `cx` 圆心 x 位置，默认为 0
- `cy` 圆心 y 位置，默认为 0

示例：

```xml
<svg width="240" height="240" viewBox="0 0 120 120" 
  xmlns="http://www.w3.org/2000/svg">
  
  <circle cx="60" cy="60" r="30" fill="#1baae6" />
</svg>
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484656388176.png" width="174"/>


### 3. ellipse 椭圆
SVG 中`ellipse`元素用于绘制椭圆，是`circle`元素更通用的形式，含有 4 个基本属性用于控制椭圆的形状以及坐标，具体如下：

- `rx`  椭圆 x 半径
- `ry`  椭圆 y 半径
- `cx`  圆心 x 位置，默认为 0
- `cy`  圆心 y 位置，默认为 0

示例：

```xml
<svg width="240" height="240" viewBox="0 0 120 120" 
  xmlns="http://www.w3.org/2000/svg">
  
  <ellipse rx="30" ry="20" cx="60" cy="60" fill="#1baae6" />
</svg>
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484656683061.png" width="174"/>


### 4. line 直线
`line`绘制直线。它取两个点的位置作为属性，指定这条线的起点和终点位置。

- `x1` 起点的 x 位置
- `y1` 起点的 y 位置
- `x2` 终点的 x 位置
- `y2` 终点的 y 位置

> `line`元素需要指定`stroke-width`属性，否则默认`stroke-width = 0`，就不会渲染出`line`元素了。

示例：

```xml
<svg width="240" height="240" viewBox="0 0 120 120" 
  xmlns="http://www.w3.org/2000/svg">
  
  <line x1="10" y1="10" x2="60" y2="60" stroke="#1baae6" stroke-width="2 />
</svg>
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484657055729.png" width="131"/>


### 5. polyline 折线
`polyline`是一组连接在一起的直线。因为它可以有很多的点，折线的的所有点位置都放在一个`points`属性中：

- `points`  点集数列，每个数字用空白、逗号、终止命令符或者换行符分隔开，每个点必须包含2个数字，一个是 x 坐标，一个是 y 坐标，如`0 0, 1 1, 2 2`。

> `polyline`元素需要指定`stroke-width`属性，否则默认`stroke-width = 0`，就不会渲染出`polyline`元素了。

示例：

```xml
<svg width="240" height="240" viewBox="0 0 120 120" 
  xmlns="http://www.w3.org/2000/svg">
  
  <polyline points="20 20, 25 35, 30 30, 35 45, 40 40, 45 55, 50 50, 55 75" fill="#fff" stroke-width="2" stroke="#1baae6" />
</svg>
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484657870315.png" width="126"/>

### 6. polygon 多边形
`polygon`和折线很像，它们都是由连接一组点集的直线构成。不同的是，`polygon`的路径在最后一个点处自动回到第一个点。需要注意的是，矩形也是一种多边形，如果需要更多灵活性的话，你也可以用多边形创建一个矩形。

- `points`  点集数列，每个数字用空白、逗号、终止命令符或者换行符分隔开，每个点必须包含 2 个数字，一个是 x 坐标，一个是 y 坐标 如`0 0, 1 1, 2 2`，路径绘制完闭合图形

示例：

```xml
<svg width="240" height="240" viewBox="0 0 120 120" 
  xmlns="http://www.w3.org/2000/svg">
  
  <polygon points="50 20, 55 40, 70 40, 60 50, 65 65, 50 55, 35 65, 40 50, 30 40, 45 40" stroke-width="1" stroke="#f00" fill="#1baae6" />
</svg>
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484658397797.png" width="147"/>


## SVG path 路径
### path 基本知识
SVG 的路径`path`功能非常强大，它不仅能创建其他基本形状，还能创建更多复杂的形状。

`path`路径是由一些命令来控制的，每一个命令对应一个字母，并且区分大小写，**大写表示绝对定位，小写表示相对定位(相对上一个坐标)**。

`path`通过属性`d`来定义路径，`d`是一系列命令的集合，主要有以下几个命令：

- `M x y` = move to 移动到坐标(x, y)

- `L x y` = line to 画一条直线到坐标(x, y)

- `H x` = horizontal line to 水平画一条直线到坐标 x(y 坐标和上一个点的 y 坐标相同)

- `V y` = vertical line to 垂直画一条直线到坐标 y(x 坐标和上一个点的 x 坐标相同)

- `C cx1 cy1 cx2 cy2 x y` = curve to 从当前点画一条三次贝塞尔曲线到坐标(x, y)，其中曲线的起点控制点和终点的控制点分别为(cx1, cy1)和(cx2, cy2)

- `S cx2 cy2 x y` = smooth curve to 此命令只能用于`C`命令之后，用于继续平滑画出一段曲线。假设用`C`命令生成了曲线 s，`S`命令的的作用是再画一条到坐标(x, y)的三次贝塞尔曲线，该曲线的终点控制点是(cx2, cy2)，起点控制点是曲线 s 的终点控制点关于 s 终点的对称点。

- `Q cx cy x y` = quadratic Belzier curve 从当前点画一条到坐标(x, y)的二次贝塞尔曲线。曲线的控制点是(cx, cy)。

- `T x y` = smooth quadratic Belzier curveto 此命令只能跟随在`Q`命令之后使用。假设`Q`命令生成了曲线 s，那么`S`命令的作用是从曲线 s 的终点再画一条到(x, y)的二次贝塞尔曲线，该曲线的控制点为曲线 s 的控制点关于 s 终点的对称点。`T`命令生成的曲线会非常顺滑。

- `A rx ry x-axis-rotation large-arc sweep x y` = elliptical Arc 画一段到(x, y)的椭圆弧。椭圆弧的 x、y 轴半径分别为 rx、ry。椭圆相对 x 轴旋转 x-axis-rotation 度。large-arc=0 表示椭圆弧小于 180°，large-arc=1 表示椭圆弧大于 180°。sweep 表示弧线逆时针旋转，sweep=1 表示弧线顺时针旋转。具体可以查看如何绘制椭圆弧。

- `Z` = close path 封闭路径，不需要坐标参数。表示从最后一个点画一条直线连接到路径的起点。

这些命令除了最后一个，都有小写的格式，表示的相对坐标。比如：

```
// 以下两个等价
d='M 10 10 20 20'     // (10, 10) (20 20) 都是绝对坐标
d='M 10 10 L 20 20'

// 以下两个等价
d='m 10 10 20 20'     // (10, 10) 绝对坐标, (20 20) 相对坐标
d='M 10 10 l 20 20'
```

### 基本形状转换成 path
#### rect to path
如下图所示，一个`rect`是由 4 个弧和 4 个线段构成；如果 rect 没有设置 rx 和 ry 则 rect只是由 4 个线段构成。 rect 转换为 path 只需要将 A ~ H 之间的弧和线段依次实现即可。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484660996224.png)

```javascript
function rect2path(x, y, width, height, rx, ry) {
    /*
    * rx 和 ry 的规则是：
    * 1. 如果其中一个设置为 0 则圆角不生效
    * 2. 如果有一个没有设置则取值为另一个
    */
    rx = rx || ry || 0;
    ry = ry || rx || 0;

   // 非数值单位计算，如当宽度像 100% 则移除
   if (isNaN(x - y + width - height + rx - ry)) return;

   rx = rx > width / 2 ? width / 2 : rx;
   ry = ry > height / 2 ? height / 2 : ry;
   
   var path = '';

   // 如果其中一个设置为 0 则圆角不生效
   // 不推荐用绝对路径，相对路径节省代码量
   if (0 == rx || 0 == ry) {
         path =
             'M' + x + ' ' + y +
             'h' + width +
             'v' + height +
             'h' + -width;                
   } else {
         path =
             'M' + x + ' ' + (y+ry) +
             'a' + rx + ' ' + ry + ' 0 0 1 ' + rx + ' ' + (-ry) + 
             'h' + (width - rx - rx) +
             'a' + rx + ' ' + ry + ' 0 0 1 ' + rx + ' ' + ry + 
             'v' + (height - ry -ry) +
             'a' + rx + ' ' + ry + ' 0 0 1 ' + (-rx) + ' ' + ry + 
             'h' + (rx + rx -width) +
             'a' + rx + ' ' + ry + ' 0 0 1 ' + (-rx) + ' ' + (-ry);        
   }
   
   path += 'z';

   return path;
}
```

#### circle/ellipse to path
圆可视为是一种特殊的椭圆，即`rx`与`ry`相等的椭圆，所以可以放在一起讨论。

如下图，椭圆可以看成 A 点到 C 做 180 度顺时针画弧、C 点到 A 做 180 度顺时针画弧即可。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484661268446.png)

```javascript
function ellipse2path(cx, cy, rx, ry) {
   // 非数值单位计算，如当宽度像 100% 则移除
   if (isNaN(x - y + rx - ry)) return;

   var path =
       'M' + (cx - rx) + ' ' + cy +
       'a' + rx + ' ' + ry + ' 0 1 0 ' + 2 * rx + ' 0' +
       'a' + rx + ' ' + ry + ' 0 1 0 ' + (-2 * rx) + ' 0' +
       'z'; 

   return path;
}
```

#### line to path
直线段转成 path 相对简单，如下：

```javascript
function line2path (x1, y1, x2, y2) {
    x1 = x1 || 0;
    y1 = y1 || 0;
    x2 = x2 || 0;
    y2 = y2 || 0;

    return 'M' + x1 + ' ' + y1 + 'L' + x2 + ' ' + y2;
}
```

#### polyline/polygon to path
`polyline`折线、`polygon`多边形的转换为 path 比较类似，差别就是`polygon`多边形会闭合。

```javascript
// polygon 折线转换
points = [x1, y1, x2, y2, x3, y3 ...];
function polyline2path (points){
    var path = 'M' + points.slice(0,2).join(' ') +
                      'L' + points.slice(2).join(' '); 
    return path;
}

// polyline/polygon 多边形转换
// points = [x1, y1, x2, y2, x3, y3 ...];
function poly2path (points, isPolygon) {
    if (points.length < 4) return;

    var path = 
        'M' + points.slice(0,2).join(' ') +
        'L' + points.slice(2).join(' ') + 
        ( isPolygon ? 'z' : '');

    return path;
}
```

## 其他重要元素
### <svg>
SVG 的根元素，并且可以相互嵌套。

```xml
<svg width="200" height="200">
    <rect x="10" y="10" width="100" height="80" stroke="red" fill="#ccc" />
    <circle cx="120" cy="80" r="40" stroke="#00f" fill="none" stroke-width="8" />
</svg>
```

### <g>
用来将 SVG 中的元素进行分组操作，分组后可以看成一个单独的形状，统一进行转换，同时 g 元素的样式可以被子元素继承，但是它没有 X, Y 属性，不过可以通过`transform`来移动它。

```xml
<svg width="200" height="200">
    <g transform="translate(-10, 350)" stroke-width="20" stroke-linejoin="round">
        <path d="M0,0 Q170,-50 260, -190 Q310, -250 410,-250" fill="none" />
    </g>
</svg>
```

### <defs> 与 <use>
这两者结合使用可以进行代码复用。

其中，前者用于定义在 SVG 中可重用的元素，defs 元素不会直接展示出来；后者通过引用 defs 元素的 ID 来进行复用展示。

```xml
<svg width="200" height="200">
    <defs>
        <g id="shapeGroup">
            <rect x="10" y="10" width="100" height="80" stroke="red" fill="#ccc" />
            <circle cx="120" cy="80" r="40" stroke="#00f" fill="none" stroke-width="8" />
        </g>
    </defs>
    <use xlink:href="#shapeGroup" transform="translate(20,0) scale(0.5)" />
    <use xlink:href="#shapeGroup" transform="translate(50,0) scale(0.7)" />
    <use xlink:href="#shapeGroup" transform="translate(80,0) scale(0.25)" />
</svg>
```

### 图案和渐变
图案 pattern 类似于 Canvas 中的背景图做法。渐变也分为线性渐变和放射性渐变，和 Canvas 类似。

```xml
<svg width="200" height="200">
    <defs>
        <pattern id="GravelPattern" patternUnits="userSpaceOnUse" x="0" y="0" width="100" height="67" viewBox="0 0 100 67">
            <image x="0" y="0" width="100" height="67" xlink:href:"gravel.jpg"></image>
        </pattern>
        <linearGradient id="Gradient">
            <stop offset="0%" stop-color="#000"></stop>
            <stop offset="100%" stop-color="#f00"></stop>
        </linearGradient>
    </defs>
    <rect x="10" y="10" width="100" height="80" stroke="red" fill="url(#Gradient)" />
    <circle cx="120" cy="80" r="40" stroke="#00f" fill="url(#GravelPattern)" stroke-width="8" />
</svg>
```

### <text>
可以用它来实现 word 中的那种“艺术字”，很神奇的一个功能。

```xml
<svg width="200" height="200">
    <text x="10" y="80" font-family="Droid Sans" stroke="#00f" fill="#00f" font-size="18px">hello SVG</text>
</svg>
```

### <image>
用它可以在 SVG 中嵌套对应的图片，并可以在图片上和周围做对应的处理。

需要注意的是，image 引用的外部资源，不是通过 src 属性，而是通过`xlink:href`属性。


## 样式
### stroke 轮廓
stroke 用于设置绘制对象线条的颜色，有如下属性：

* `stroke-width`：设置轮廓的宽度；
* `stroke-linecap`：设置轮廓结尾处的渲染方式，可取值有`butt`(直接一刀切断)、`square`(保留一点切断)、`round`(圆弧切断)；
* `stroke-linejoin`：用于设置两条线之间的连接方式，可取值有`miter`(尖角连接)、`round`(圆弧连接)、`bevel`(切断连接)；
* `stroke-opacity`：用于设置描边的不透明度；
* `stroke-dasharray` + `stroke-dashoffset`：前者用于使用虚线呈现 SVG 形状的描边，需要提供一个数值数组来描述，定义破折号和空格的长度；后者用于设置虚线模式中的开始点。

常见的路径动画就是以操作 path 元素中`stroke-dasharray`和`stroke-dashoffset`来实现的。

### fill 填充
fill 用来描述 SVG 对象内部的颜色，有如下两个属性：

* `fill-opacity`：用于设置填充颜色的不透明度；
* `fill-rule`：用于设置填充的方式，可取值有`nonzero`、`evenodd`两个值：
    - `nonzero`：从一个点往任何方向上绘制一条射线，形状中的路径每次穿过此射线时，如果路径从左到右穿过射线，则计数器加 1，如果路径从右到左穿过射线，则计数器减 1。计数器总数为 0 时候，则该点被认为在路径外。如果计数器非 0，则该点被认为在路径内。
    - `evenodd`：从一个点往任何方向上绘制一条射线。每次路径穿过射线，计数器加 1。如果总数是偶数，则点在外部。如果总计数为奇数，点在形状内。

每个元素都有该属性，用于表示填充颜色。其值可以是有效的 CSS 颜色值(颜色名、十六进制色号、RGB、RGBA 等)。

### transform 变换
此属性和 css3 中的`transform`相类似，有`translate`、`rotate`、`scale`、`skew`(`skewX`和`skewY`函数使 x 轴和 y 轴倾斜)、`matrix`(矩阵变换)。这些变换可以将它们组合进行变换。


## 动画
在 SVG 中动画元素主要分成如下 4 类，同时也可以自由组合。

* `<set>`：用于设置延迟，譬如设置 5s 后元素位置颜色变化，但是此元素没有动画效果；
* `<animate>`：基础动画属性，用于实现单属性的动画过度效果；
* `<animateTransform>`：实现 transform 变换动画效果，可以类比 CSS3 中的 transform；
* `<animateMotion>`：实现路径动画效果，让元素沿着对于 path 运动。

有了元素以后还需要有对应的属性用来表示动画的特征，譬如：要动画的元素属性名称、起始值、结束值、变化值、开始时间、结束时间、重复次数、动画速度曲线函数等等。

![动画参数](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1485784622866.png)


## 优化和工具
### svgo
svgo 一个比较厉害的压缩优化 SVG 的工具，可以去除我们编写的 SVG 中的无用信息，同时对代码进行压缩，项目地址：[https://github.com/svg/svgo](https://github.com/svg/svgo)。

### SVGOMG
SVGOMG 是 svgo 的可视化界面工具，操作起来很方便，同时还提供了一些其他有用的功能，展示地址：[SVGOMG - SVGO's Missing GUI](https://jakearchibald.github.io/svgomg/)。

### Snap.svg
Snap.svg 一个用于操作 SVG 的 JavaScript 库，可以写出更加复杂的 SVG 效果，同时文档超级齐全，项目地址：[Snap.svg - Home](http://snapsvg.io/)。

### Convert image to the SVG format
我们可以通过这个转换平台，将普通图片转成 SVG 的格式。此处转换可能结果不是我们想要的，但是可以将其当做初成品，在此基础上在进行调整优化，最终实现 SVG 的转换。

平台地址：[http://image.online-convert.com/convert-to-svg](http://image.online-convert.com/convert-to-svg)。

## 转摘
1. [聊聊 SVG 基本形状转换那些事](https://aotu.io/notes/2017/01/16/base-shapes-to-path/)
2. [SVG (一) 图形, 路径, 变换总结; 以及椭圆弧线, 贝塞尔曲线的详细解释](https://segmentfault.com/a/1190000004393817)
3. [路径 - MDN](https://developer.mozilla.org/zh-CN/docs/Web/SVG/Tutorial/Paths)
4. [SVG 新司机开车指南](https://zhuanlan.zhihu.com/p/25016633)

