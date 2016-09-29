## 格式化输入

```js
// 如果输入非数字，则替换为''，如果输入数字，则在每4位之后添加一个空格分隔
this.value = this.value.replace(/[^\d]/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
```

## 滚动到最底部
对于一个不时更新的容器内，如果在每次更新的时候，都要滚动到最底部，以显示最新内容，那么可以使用`scrollIntoView()`方法。这是 HTML5 新增的一个 DOM 方法。

```javascript
function onGetMessage(context) 
{
    msg.innerHTML += context;
    msg_end.scrollIntoView(); 
} 
```

> 还可以使用锚标记要滚动到的位置，然后通过`click`方法模拟点击滚动到锚所在位置。

## Canvas 保存为图片
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

## 字符串换行
直接在字符串行结束时添加`\`可以将一个字符串分行书写：

```javascript
var str = "this string \
is broken \
across multiple\
lines."

console.log(str); // this string is broken across multiplelines.
```

## 移动 web 端自定义 tap 事件
```js
// 自定义tap
$(document).on("touchstart", function(e) {
    if(!$(e.target).hasClass("disable")) $(e.target).data("isMoved", 0);
});
$(document).on("touchmove", function(e) {
    if(!$(e.target).hasClass("disable")) $(e.target).data("isMoved", 1);
});
$(document).on("touchend", function(e) {
    if(!$(e.target).hasClass("disable") && $(e.target).data("isMoved") == 0) 
        $(e.target).trigger("tap");
});
```

## 判断是否为 pc 端

```js
function IsPC() {    
    var Agents = ["Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod"];
    var userAgentInfo = navigator.userAgent;
    var flag = true;  
   
   for (var v = 0; v < Agents.length; v++) {  
       if (userAgentInfo.indexOf(Agents[v]) > 0) {
           flag = false;
           break;
       }  
   }
   
   return flag;  
}
```

## 倒计时

```js
function setTimer(obj, counter) {
   if (counter == 0) {
       obj.prop("disabled", false).text('获取验证码');
       return;
   } else {
       obj.prop('disabled', true).text('重新发送(' + counter + ')');
       counter--;
       setTimeout(function() { setTimer(obj, counter) }, 1000);
   }
}
```

## 浏览器鼠标选取操作
1. 屏蔽鼠标右键  `oncontextmenu='window.event.returnValue=false'`
2. 取消选取、防止复制  `onselectstart='return false'`
3. 不允许粘贴 `onpaste='return false'`
4. 防止复制、剪切 `oncopy='return false;' oncut='return false;'`

## 防止被人 frame

```html
<script>< !–
if (top.location != self.location)top.location=self.location;
// –></script>
```

## 查看网页源代码
`onclick='window.location="view-source:" + window.location.href'>`

## ENTER 键可以让光标移到下一个输入框
也就是当按下 Enter 键的时候，改成 Tab 键。
`<input onkeydown='if(event.keyCode==13) event.keyCode=9'>`


## 自定义异常
```javascript
// 创建一个对象类型UserException
function UserException(message) {
this.message = message;
this.name = "UserException";
}

//重写toString方法，在抛出异常时能直接获取有用信息
UserException.prototype.toString = function() {
return this.name + ': "' + this.message + '"';
}

// 创建一个对象实体并抛出它
throw new UserException("Value too high");
```


## 判断 webp 兼容性

```javascript
// 检测浏览器是否支持webp
// 之所以没写成回调，是因为即使isSupportWebp=false也无大碍，但却可以让代码更容易维护
(function() {
    function webpTest(src, name) {
        var img = new Image(),
            isSupport = false,
            className, cls;

        img.onload = function() {
            isSupport = !!(img.height > 0 && img.width > 0);

            cls = isSupport ? (' ' + name) : (' no-' + name);
            className = document.querySelector('html').className
            className += cls;

            document.querySelector('html').className = className.trim();
        };
        img.onerror = function() {
            cls = (' no-' + name);
            className = document.querySelector('html').className
            className += cls;

            document.querySelector('html').className = className.trim();
        };

        img.src = src;
    }

    var webpSrc = 'data:image/webp;base64,UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoB\
                AAEAAwA0JaQAA3AA/vuUAAA=',
        webpanimationSrc = 'data:image/webp;base64,UklGRlIAAABXRUJQVlA4WAoAAAA\
                            SAAAAAAAAAAAAQU5JTQYAAAD/////AABBTk1GJgAAAAAAAAAAAA\
                            AAAAAAAGQAAABWUDhMDQAAAC8AAAAQBxAREYiI/gcA';

    webpTest(webpSrc, 'webp');
    webpTest(webpanimationSrc, 'webpanimation');
})();
```

## try...catch...finally
**finally 返回值**：如果 finally 添加了 return 语句，则不管整个 try.catch 返回什么，返回值都是 finally 的 return。

```javascript
function f() {
    try {
        console.log(0);
        throw "bogus";
    } catch(e) {
        console.log(1);
        return true; // 返回语句被暂停，直到finally执行完成
        console.log(2); // 不会执行的代码
    } finally {
        console.log(3);
        return false; //覆盖try.catch的返回
        console.log(4); //不会执行的代码
    }
    // "return false" is executed now 
    console.log(5); // not reachable
}
f(); // 输出 0, 1, 3; 返回 false
```

**finally 吞并异常**：如果 finally 有return 并且 catch 中有 throw 异常，throw 的异常不会被捕获，因为已经被 finally 的 return 覆盖了。

```javascript
function f() {
    try {
        throw "bogus";
    } catch(e) {
        console.log('caught inner "bogus"');
        throw e; // throw语句被暂停，直到finally执行完成
    } finally {
        return false; // 覆盖try.catch中的throw语句
    }
    // 已经执行了"return false"
}

try {
    f();
} catch(e) {
    //这里不会被执行，因为catch中的throw已经被finally中的return语句覆盖了
    console.log('caught outer "bogus"');
}
// 输出
// caught inner "bogus"
```

### 数组去重
**基本数组去重**

```javascript
Array.prototype.unique = function () {
    var result = [];
    
    this.forEach(function (v) {
        if (result.indexOf(v) < 0) {
            result.push(v);
        }
    });
    
    return result;
}
```

**利用 hash 表去重，这是一种空间换时间的方法**

```javascript
Array.prototype.unique = function () {
    var result = [], hash = {};
    
    this.forEach(function (v) {
        if (!hash[v]) {
            hash[v] = true;
            result.push(v);
        }
    });
    
    return result;
}
```

上面的方法存在一个 bug，对于数组`[1, 2, '1', '2', 3]`，去重结果为`[1,2,3]`，原因在于对象对属性索引时会进行强制类型转换，`arr[‘1’]`和`arr[1]`得到的都是`arr[1]`的值，因此需做一些改变：

```javascript
Array.prototype.unique = function () {
    var result = [], hash = {};
    
    this.forEach(function (v) {
        var vType = typeof(v);
        
        hash[v] || hash[v] = [];
        
        if (hash[v].indexOf(vType) < 0) {
            hash[v].push(vType);
            result.push(v);
        }
    });
    
    return result;
}
```

**先排序后去重**

```javascript
Array.prototype.unique = function () {
    var result = [];
    this.sort();
    this.forEach(function (v) {
        // 仅与 result 最后一个元素比较
        v != result[result.length - 1] && result.push(v);
    });
}
```

### 快速排序算法
**方法一(尽可能不用 js 数组方法)**

```javascript
function quickSort(arr){
    qSort(arr,0,arr.length - 1);
}
function qSort(arr,low,high){
    if(low < high){
        var partKey = partition(arr,low,high);
        qSort(arr,low, partKey - 1);
        qSort(arr,partKey + 1,high);
    }
}
function partition(arr,low,high){
    var key = arr[low];  // 使用第一个元素作为分类依据
    while(low < high){
        while(low < high && arr[high] >= arr[key])
            high--;
        arr[low] = arr[high];
        while(low < high && arr[low] <= arr[key])
            low++;
        arr[high] = arr[low];
    }
    arr[low] = key;
    return low;
}
```

**方法二(使用 js 数组方法)**

```javascript
function quickSort(arr){
   if(arr.length <= 1) return arr;
   var index = Math.floor(arr.length/2);
   var key = arr.splice(index,1)[0];
   var left = [],right = [];
   arr.forEach(function(v){
       v <= key ? left.push(v) : right.push(v);
   });
   return quickSort(left).concat([key],quickSort(right));
}
```

### 替代 document.write 方法
`document.write` 可以在当前执行的 script 标签之后插入任意的 HTML 源码。但是这个方法会造成浏览器的阻塞，或者预加载和预解析失败。而且如果写入的是一另一个 script 标签加载另一个脚本的时候，如果加载的脚本有问题，会造成整个页面都要等待其加载完成才能完成渲染和执行。

如果要动态加载其他的 js 脚本，替代这个方法的方式是：**使用`document.createElement("script")`配合`appendChild/insertbefore`插入 script**。通过这种方式插入的 script 都是异步的。

```javascript
<script>
document.head.appendChild(document.createElement('script')).src = '//w.cnzz.com/c.php?id=30086426'
</script>
```


