JavaScript 的语法基础部分。

## 语句
JavaScript 中语句是组成脚本的基本单位。

每条语句可以单独成行，不需要带有分号`;`进行分割：

*first statement*
*second statement*

但是如果要把多条语句写在同一行，就需要在每条语句后面添加分号进行分割了。如下所示：

*first statement; second statement;*

> 建议给每条语句都添加分号。

## 注释
注释可以使用`//`、`<!--`、`/* */`标记来表示。

其中，前两种标记属于单行注释，解释器会将在这两种标识同一行中、后面的内容作为注释；第三种则是块注释，解释器会将在这个块中的内容都作为注释。

> 建议单行注释使用`//`。

## 变量
变量是指其值可以发生变化的值的名称。

## 基本类型
### Boolean
布尔类型有两个值：true、false。分别称为真值和假值。

在 JavaScript 中下面的这些值都是假值：

* false
* 0 (数字 0)
* '' (空字符串)
* null
* undefined
* NaN

除了上述的六个值之外，剩下的都是真值，包括字符串"0"、字符串"false"、空函数、空数组、空对象。

假值的六个值中，`false`、`0`、`''`三者是相等的(`==`)。

```JavaScript
var c = (false == 0);  // true
var d = (false == ""); // true
var e = (0 == "");     // true
```

而`null`、`undefined`则只相互相等，而和其他的假值不相等：

```JavaScript
var f = (null == false); // false
var g = (null == null);  // true
var h = (undefined == undefined); // true
var i = (undefined == null);      // true
```

最后一个假值`NaN`则不和任何值相等，包括其自身：

```JavaScript
var j = (NaN == null); // false
var k = (NaN == 0);    // false
var l = (NaN == NaN);  // false
```


## 事件
DOM 事件流是先由外向内先进行捕获阶段，然后再向外冒泡，相应的事件回调也会按照这个顺序进行。不过，对于触发事件的元素来说则并不完全相同：触发元素是事件的目标元素，它的事件的捕获和触发是根据事件注册的先后顺序的不同来执行的，如果先注册的是捕获阶段的事件，则先进行捕获，否则先进行冒泡。

全部的 DOM 事件类型如下：

* UI 事件：当用户与页面上的元素交互时触发，如 load、scroll 等；
* 焦点事件：当元素获得或失去焦点时触发，如 blur、focus；
* 鼠标事件：当用户通过鼠标在页面执行操作时触发，如 click、dbclick、mouseup 等；
* 滚轮事件：当使用鼠标滚动或类似的设备时触发，如 mousewheel 等；
* 文本事件：挡在文档中输入文本时触发，如 textInput 等；
* 键盘事件：当用户通过键盘在页面上执行操作时触发，如 keydown、keypress 等；
* 合成事件：当用 IME（输入法编辑器）输入字符时触发，如 compositionstart 等；
* 变动事件：当底层 DOM 解构发生变化时触发，如 DOMsubtreeModified 等。

注册 DOM 事件的回调函数有多种方式：

- 直接写在 HTML 中，通过设置元素的`on + eventType`属性
- 使用 DOM Element 上面的`on + eventType`属性 API
- 使用 DOM Element 的`addEventListener`方法或`attachEvent`方法

前两种方式只能对一种事件绑定一个回调，第三种方式则能够绑定多个回调。其实还有一种非常规方法：使用 a 元素的 href 属性，在其中写入简单的 JavaScript 语句。

如果这三种方法同时出现，则第二种方式绑定的回调函数会覆盖掉第一种方法绑定的回调，第三种方法则不会有影响。

```javascript
<a href="javascript:alert(1)" onclick="alert(2)" id="link">click me</a> 
<script> 
    var link = document.getElementById('link'); 
    link.onclick = function() { alert(3); } 
 
    $('#link').bind('click', function() { alert(4); }); 
    $('#link').bind('click', function() { alert(5); }); 
</script>
```

上例弹出的顺序是：3、4、5、1。因为 2 的那个被 3 的回调给覆盖了。而 jQuery 中的 bind 方法其实就是调用的 addEventListener 方法。

## delete 操作
delete 是一元运算符，可以用它来删除对象的属性或者数组的元素。

delete 期望的操作数是一个左值，如果我们误用使得他的操作数不是左值，那么 delete 就不会进行任何操作并且返回 true。

> 所谓“左值”，简单点说就是可以被赋值的表达式，在 ES 规范中是用内部类型**引用(Reference)**描述的，其作用为存放数据空间，且存放是允许的。

当前，并不是所有的属性都是能够删除的：用户用 var 声明的变量、自定义的函数、函数参数、内置核心属性等是不能删除的，如果进行删除会抛出删除非法的错误。而且**delete 运算符只能删除自有属性，不能删除继承属性**。

delete 这种删除只是断开属性和宿主对象的联系，而没有将其销毁。（销毁是由 GC 来进行的）。

```javascript
var a = { b: { c: 1 } };
var d = a.b;

delete a.b;

console.log(d.c);  // 输出 1
```

当 delete 操作成功的时候，返回 ture，失败返回 false：

```javascript
var o = { a: 1 };

delete o.a;     // 删除 a 属性，返回 true
delete o.x;     // x 属性不存在，所以什么都不做，并返回 true
delete o.toString;  // 因为 toString 是继承来的，所以什么都不做，并返回 true

delete 110;     // 没有实际意义，返回 true

delete Object.prototype; // 返回 false

var b = 1;
delete this.b;  // 返回 false

function f() {}
delete this.f;  // 返回 false
```

## touch事件
`touchstart`、`touchmove`、`touchend`。

直接使用`event.clientX`是不起作用的，要使用`event.changedTouches[0].clientX`才好；如果是 jQuery 的 event 对象，使用`event.originalEvent.changedTouches[0].clientX`。


