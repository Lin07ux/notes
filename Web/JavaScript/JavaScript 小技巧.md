## 格式化输入

```js
// 如果输入非数字，则替换为''，如果输入数字，则在每4位之后添加一个空格分隔
this.value = this.value.replace(/[^\d]/g, '').replace(/(\d{4})(?=\d)/g, "$1 ");
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

