### background-attachment: fixed

微信浏览器目前不支持`background-attachment: fixed;`背景图设置，如果需要背景图固定，可以考虑使用当前元素的伪元素设置背景，并伪元素的大小设置为当前元素的可视大小：

```css
body::before{
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top,#000A16,#0e4179 40%,#041424);
    z-index: -1;
}
```

### 表单元素失焦后关闭键盘导致页面底部空缺问题

移动端页面中有一个 input 框，点击时会自动弹出软键盘，关闭时页面底部空白出一部分，然后滑动一下又恢复原状了。

解决方案是：为 input 绑定一个`blur`事件，当其触发时，使页面的`scrollTo`为 0。

例如：

```JavaScript
let inputs = document.getElementsByTagName('input')

function inputBlur () {     window.scrollTo(0, 0) }

for (var i = 0; i < inputs.length; i++) {
    inputs[i].addEventListener('blur', inputBlur)
}
```

> 转摘：[微信浏览器input关闭键盘后导致页面底部空缺问题](https://blog.csdn.net/xiasohuai/article/details/88537188)

### 表单元素点击时不允许弹出软键盘

默认情况下，点击 input 表单元素时，会自动弹出软键盘。如果不想软键盘弹出，可以将该表单元素禁用，但这样的话在微信中就无法触发表单元素的相关事件(比如 click、focus 等)。

解决方法：为 input 元素设置`readonly`属性，这样该元素就是只读的，不会自动弹出软键盘。不过这样也可能会弹出一个只有上下箭头和“完成”按钮的键盘头部。


