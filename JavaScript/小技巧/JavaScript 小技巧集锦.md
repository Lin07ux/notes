### 1. 格式化输入

```js
// 如果输入非数字，则替换为''，如果输入数字，则在每4位之后添加一个空格分隔
this.value = this.value.replace(/[^\d]/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
```

### 2. ~~ 的作用

`~~`会将其后面的表达式的值转换为整数，而且可以去除小数部分。如下：

```JavaScript
~~true === 1
~~false === 0
~~"" === 0
~~[] === 0

~~undefined === 0
~~!undefined === 1
~~null === 0
~~!null === 1

~~("1nd") === 0

~~1.21 === 1
```

### 3. 滚动到最底部

对于一个不时更新的容器内，如果在每次更新的时候，都要滚动到最底部，以显示最新内容，那么可以使用`scrollIntoView()`方法。这是 HTML5 新增的一个 DOM 方法。

```javascript
function onGetMessage(context) {
    msg.innerHTML += context;
    msg_end.scrollIntoView(); 
} 
```

> 还可以使用锚标记要滚动到的位置，然后通过`click`方法模拟点击滚动到锚所在位置。

### 4. 字符串换行

直接在字符串行结束时添加`\`可以将一个字符串分行书写：

```javascript
var str = "this string \
is broken \
across multiple\
lines."

console.log(str); // this string is broken across multiplelines.
```

### 5. 移动 web 端自定义 tap 事件

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

### 4. 判断是否为 pc 端

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

### 5. 倒计时

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

### 6. 浏览器鼠标选取操作

1. 屏蔽鼠标右键 `oncontextmenu='window.event.returnValue=false'`
2. 取消选取、防止复制 `onselectstart='return false'`
3. 不允许粘贴 `onpaste='return false'`
4. 防止复制、剪切 `oncopy='return false;' oncut='return false;'`

### 7. 防止被人 frame

```html
<script>< !–
if (top.location != self.location)top.location=self.location;
// –></script>
```

## 8. 查看网页源代码

`onclick='window.location="view-source:" + window.location.href'>`

### 9. ENTER 键可以让光标移到下一个输入框

也就是当按下 Enter 键的时候，改成 Tab 键：

`<input onkeydown='if(event.keyCode==13) event.keyCode=9'>`

### 10. 自定义异常

```javascript
// 创建一个对象类型UserException
function UserException(message) {
    this.message = message;
    this.name = "UserException";
}

// 重写 toString 方法，在抛出异常时能直接获取有用信息
UserException.prototype.toString = function() {
    return this.name + ': "' + this.message + '"';
}

// 创建一个对象实体并抛出它
throw new UserException("Value too high");
```

### 11. 替代 document.write 方法

`document.write` 可以在当前执行的 script 标签之后插入任意的 HTML 源码。但是这个方法会造成浏览器的阻塞，或者预加载和预解析失败。而且如果写入的是一另一个 script 标签加载另一个脚本的时候，如果加载的脚本有问题，会造成整个页面都要等待其加载完成才能完成渲染和执行。

如果要动态加载其他的 js 脚本，替代这个方法的方式是：**使用`document.createElement("script")`配合`appendChild/insertbefore`插入 script**。通过这种方式插入的 script 都是异步的。

```javascript
<script>
    document.head.appendChild(document.createElement('script')).src = '//w.cnzz.com/c.php?id=30086426'
</script>
```

### 12. window.status

当鼠标指向一个链接时，浏览器窗口左侧底部的状态栏通常显示该链接的 URL。然而，可以用 JavaScript 在状态栏显示自己的信息，有时这对用户是有益的，比如，可以用一个友好简单的页面描述来代替 URL 链接。

使用 js：`window.status = "状态栏显示这些文字"`即可修改状态栏的值。

任何时候都可以把其他文本赋给`window.status`属性。如，当光标位于链接上时，为了改变链接的状态栏文本，应该使用`link`对象的`onMouseOver`事件处理触发一个动作。设置状态栏的`onMouseOver`事件处理要求一个附加语句(`return true`)，它必须是事件处理程序的一部分。

### 13. iOS 点击两次

在 iOS 上，点击链接的时候，如果链接设置了`hover`一类的效果的时候，会先出现`hover`效果，然后又开始进行链接的跳转效果，类似于两次点击效果。这是由于移动端对触摸和点击的判断条件不同导致的。可以使用如下的方式进行规避：

```JavaScript
$('a').on('touchend', function (e) {
    $(this).click();
})
```

### 14. 取整(去除小数部分)

```JavaScript
~~3.14 === 3;
3.14 >> 0 === 3;
3.14 | 0 === 3;
'3.14' | 0 === 3;
'3.14' ^ 0 === 3;

~~-3.14 === -3;
-3.14 >> 0 === -3;
-3.14 | 0 === -3;

'-3.14' | 0 === -3;
'-3.14' ^ 0 === -3;
```

当使用上面的方式取整时，对正数是向下取整，对负数是向上取整。

### 15. 变量值交换

```JavaScript
var a = 1, b =2;

a = [b, b = a][0];
```

### 16. 截断数组

直接设置数组的`length`值可以对数组进行截断处理，也就是将超出该长度的值去除：

```JavaScript
var arr = [1, 2, 3, 4, 5, 6];

arr.length = 3;
console.log(arr);  // [1, 2, 3]
```

这样并没有调用`Array.slice()`方法的效率高，所以可以尽量使用该方法：

```JavaScript
var arr = [1, 2, 3, 4, 5, 6];

arr = arr.slice(0, 3);
```

> `Array.slice()`方法不会修改原数组。

### 17. 幂运算

从 ES7 开始，可以使用`**`进行幂运算，比使用`Math.power(2,3)`要快得多。

```JavaScript
console.log(2**3);   // Result: 8
```

可以使用位左移运算符`<<`来表示以 2 为底的幂运算：

```JavaScript
// 以下表达式是等效的:
Math.pow(2, n)；
2 << (n -1);
2**n;
```

### 18. 序列化 JSON 时增加空格

`JSON.stringify()`方法可以接受两个额外的参数，一个是函数（形参为`replacer`），用于过滤要显示的 JSON，另一个是空格个数（形参为`space`），可以是一个整数，表示空格的个数，也可以是一个字符串（比如`\t`表示制表符），这样得到的 JSON 更容易阅读。

```JavaScript
console.log(JSON.stringify({ alpha: 'A', beta: 'B' },null, '\t'));
// Result:
// '{
//     "alpha": A,
//     "beta": B
// }'
```

### 19、返回一个键盘

下面这段代码运行之后会返回一个图形键盘：

```JavaScript
(_=>[..."`1234567890-=~~QWERTYUIOP[]\\~ASDFGHJKL;'~~ZXCVBNM,./~"].map(x=>(o+=`/${b='_'.repeat(w=x<y?2:' 667699'[x=["BS","TAB","CAPS","ENTER"][p++]||'SHIFT',p])}\\|`,m+=y+(x+'    ').slice(0,w)+y+y,n+=y+b+y+y,l+=' __'+b)[73]&&(k.push(l,m,n,o),l='',m=n=o=y),m=n=o=y='|',p=l=k=[])&&k.join`
`)()
```

运行结果如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1557040294520.png"/>

这段代码格式化后，就是对一个数组执行`map`操作，在`[].map()`方法中传递了三个参数，但只有第一个参数是真正有用的，另外两个其实是用于变量初始化操作。

