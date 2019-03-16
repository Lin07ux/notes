## 一、导出 canvas

绘制好的 canvas 想存储为本地图片，可以使用`canvas.toDataURL()`方法，将其转成图片内容，然后保存即可。

`toDataURL()`接收一个 MIME 类型的参数，表示保存为什么图片格式，一般可以保存为`image/png`、`image/jpg`、`image/jpeg`、`image/gif`。

基本 HTML 结构如下：

```html
<canvas id="canvas"></canvas>
<button class="button-balanced" id="save">save</button>
<br />
<a href="" download="canvas_love.png" id="save_href">
    <img src="" id="save_img"/>
</a>
```

对应的 JavaScript 代码如下：

```javascript
function drawLove(canvas){
    let ctx = canvas.getContext("2d");
    ctx.beginPath();
    ctx.fillStyle="#E992B9";
    ctx.moveTo(75,40);
    ctx.bezierCurveTo(75,37,70,25,50,25);
    ctx.bezierCurveTo(20,25,20,62.5,20,62.5);
    ctx.bezierCurveTo(20,80,40,102,75,120);
    ctx.bezierCurveTo(110,102,130,80,130,62.5);
    ctx.bezierCurveTo(130,62.5,130,25,100,25);
    ctx.bezierCurveTo(85,25,75,37,75,40);
    ctx.fill();
}

var canvas = document.getElementById('canvas');
var button = document.getElementById('save');

drawLove(canvas); 

button.addEventListener('click', function(){
    var img   = document.getElementById('save_img');
    var aLink = document.getElementById('save_href');
    var temp  = canvas.toDataURL('image/png');
    
    img.src = temp;
    aLink.href = temp;
})
```

这样点击链接就能够下载得到图片了。[demo](http://codepen.io/Lin07ux/pen/RGkoxN)

## 二、问题

### 2.1 canvas 转 base64/jpeg 时，透明区域变成黑色背景的解决方案

在用 canvas 将 png 图片转 jpeg 时，会将透明区域填充成黑色。

基本 HTML 代码如下：

```html
<p>Canvas：</p>
<canvas id="canvas" style="border: 1px solid #ccc;"></canvas>
<br>

<p>Base64转码后的图片：</p>
<div id="base64Img"></div>
```

基础 JavaScript 代码如下：

```javascript
var base64Img = document.getElementById("base64Img"),
    canvas    = document.getElementById("canvas"),
    context   = canvas.getContext("2d");

// 获取canvas的base64图片的dataURL（图片格式为image/jpeg）
function getBase64(canvas, callback) {
   var dataURL = canvas.toDataURL("image/jpeg");

   if(typeof callback !== undefined) {
       callback(dataURL);
   }
}
```

一般的转换流程如下：

```javascript
// 创建新图片
var img = new Image();
img.src = "1.png";

img.addEventListener("load", function() {
    // 绘制图片到 canvas 上
    canvas.width  = img.width;
    canvas.height = img.height;

    context.drawImage(img, 0, 0);

    getBase64(canvas, function(dataUrl) {
        var newImg = document.createElement("img");
            newImg.src = dataUrl;

        // 展示 base64 位的图片
        base64Img.appendChild(newImg);
    });
}, false);
```

效果如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476249945069.png)

为什么 canvas 会将 png 的透明区域转成黑色呢？**canvas转换成jpeg之前移除alpha通道，所以透明区域被填充成了黑色**。而一般情况下，我们并不想转成黑色，而且转成白色。

一种方法是：绘制到 canvas 之后，在进行转换之前将 canvas 的像素进行处理，将透明颜色更改成白色。

```javascript
context.drawImage(img, 0, 0);

// 将canvas的透明背景设置成白色
var imageData = context.getImageData(0, 0, canvas.width, canvas.height);
for(var i = 0; i < imageData.data.length; i += 4) {
    // 当该像素是透明的，则设置成白色
    if(imageData.data[i + 3] == 0) {
        imageData.data[i] = 255;
        imageData.data[i + 1] = 255;
        imageData.data[i + 2] = 255;
        imageData.data[i + 3] = 255; 
    }
}
context.putImageData(imageData, 0, 0);

getBase64(canvas, function(dataUrl) { ... });
```

效果如下图：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476250274091.png)

可以看到，如果图片中有半透明的颜色，还是会被转成黑色。

另一种方法是：**先将 canvas 用白色填充，然后再绘制图像**。这样最终的结果就是：透明色变成了白色，半透明变成了黑灰色，符合预期。

```javascript
canvas.width  = img.width;
canvas.height = img.height;

// 在 canvas 绘制前填充白色背景
context.fillStyle = "#fff";
context.fillRect(0, 0, canvas.width, canvas.height);

context.drawImage(img, 0, 0);

// 展示 base64 位的图片
getBase64(canvas, function(dataUrl) { ... });
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/1476250746267.png)

转摘：[canvas转base64/jpeg时，透明区域变成黑色背景的解决方案](http://www.dengzhr.com/frontend/html/1096) 或 [canvas转base64/jpeg时，透明区域变成黑色背景的解决方案](http://www.tuicool.com/articles/iaUzUrv)


