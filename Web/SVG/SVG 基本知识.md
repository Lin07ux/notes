## 基础

`fill` 每个元素都有该属性，用于表示填充颜色。其值可以是有效的 CSS 颜色值(颜色名、十六进制色号、RGB、RGBA 等)。

路径动画，以操作 path 中两个属性值`stroke-dasharray`和`stroke-dashoffset`来实现



## SVG 基本形状
SVG 中，预定义了 6 种基本的形状：`rect`、`circle`、`ellipse`、`line`、`polyline`、`polygon`，这六种基本形状都可以通过`path`路径转换实现。

![SVG 六种基本形状](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484654797629.png)

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

## 转摘
1. [聊聊 SVG 基本形状转换那些事](https://aotu.io/notes/2017/01/16/base-shapes-to-path/)
2. [SVG (一) 图形, 路径, 变换总结; 以及椭圆弧线, 贝塞尔曲线的详细解释](https://segmentfault.com/a/1190000004393817)
3. [路径 - MDN](https://developer.mozilla.org/zh-CN/docs/Web/SVG/Tutorial/Paths)

