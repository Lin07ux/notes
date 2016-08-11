## 格式化输入

```js
// 如果输入非数字，则替换为''，如果输入数字，则在每4位之后添加一个空格分隔
this.value = this.value.replace(/[^\d]/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
```

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

