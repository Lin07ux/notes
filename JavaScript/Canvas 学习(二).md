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
* `scale(scaleX, scaleY)`：缩放图像，在 X 方向乘以 scaleX，在 y 方向乘以 scaleY。scaleX 和 scaleY 的默认值是 1.0。
* `translate(x, y)`：将坐标原定移动到(x, y)。执行这个变换之后，坐标(0,0)会变成之前由(x,y)表示的点。
* `transform(m1_1, m1_2, m2_1, m2_2, dx, dy)`：直接修改变换矩阵，方法是乘以如下矩阵: 
```
m1_1 m1_2 dx
m2_1 m2_2 dy
0    0    1
```

* 	`setTransform(m1_1, m1_2, m2_1, m2_2, dx, dy)`：将变换矩阵重置为默认状态，然后再调用 transform。 
示例：

```javascript
var drawing = document.getElementById("drawing");
// 确定浏览器是否支持canvas元素
if (drawing.getContext) {
    var context = drawing.getContext("2d");
    context.beginPath();
    context.arc(100, 100, 90, 0, 2 * Math.PI, false);
    // 变换原点
    context.translate(100, 100);
    context.moveTo(0, 0);
    // 旋转
    context.rotate(1);        
    context.lineTo(0, -80);
    context.stroke();
}
```

## 状态的保存和恢复
可以通过`save()`在绘图堆栈中保存设置；而通过`restore()`方法恢复上一级保存的状态。而且可以连续使用`save()`和`restore()`方法。

**注意：`save()`保存的只是对绘图上下文的设置和变换，不会保存绘图上下文的内容。**

```javascript
var drawing = document.getElementById("drawing");
//确定浏览器是否支持canvas元素
if (drawing.getContext) {
    var context = drawing.getContext("2d");
    context.fillStyle = "red";
    context.save(); //第一次存储

    context.fillStyle="yellow";
    context.translate(100,100);
    context.save(); //第二次存储

    context.fillStyle = "blue";
    context.fillRect(0,0,100,200); //因为translate把原点放在了100,100所以，从这个点开始绘制

    context.restore(); //第一次恢复
    context.fillRect(10,10,100,200); //绘制黄色图形因为运用了一次restore();

    context.restore(); //第二次恢复
    context.fillRect(0,0,100,200); //原点恢复为0,0，绘制红色图形，因为又运用了一次restore();
}
```

## 绘制图像
使用`drawImage()`方法来绘制图像。

有三种不同的参数组合:

* 传入一个 HTML <img> 元素，以及绘制该图像的起点的 x 和 y 坐标。如：`context.drawImage(img, 0, 0);`，表示从(0,0)处插入绘制图片。
* 再多传两个参数，分别表示目标宽度和目标高度(进行缩放，不影响上下文的变换矩阵)。如：`context.drawImage(img, 0, 0, 300, 200);`，表示从(0,0)处插入绘制图片，并设置长度为300，高度为 200；
* 再多传 4 个参数,表示目标图像的(x,y)坐标和目标图像的宽度和高度(并没有进行缩放):如：`context.drawImage(img, 0, 0, 300, 200, 100, 100, 100, 80);`，表示从(0,0)处插入绘制图片，并设置长度为 300，高度为 200；绘制到上下文的(100,100)处，宽度为 100 高度为 80。

> 注意：图像不能来自其他域，否则 toDataURL() 会抛出错误。


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






