## 一、基础

在触发 DOM 上个的某个事件时，会产生一个事件对象`event`，这个对象中包含着所有与事件有关的信息。所有浏览器都支持 event 对象，但支持方式不同。

事件对象中包括：导致事件的元素、事件类型，以及其他特定事件的相关信息。

> 在 DOM 事件模型中，event 对象并不是一个全局对象，只有在 IE 浏览器中的 event 对象才是全局对象，是 window 对象的一个属性。

### 1.1 属性

event 对象一般都含有如下的属性：

- `bubbles` Boolean 类型值，只读，表明事件是否冒泡（默认为 true，且设置阻止冒泡之后，其值不改变）。冒泡是指：事件从一个底层元素向上传递给上一层的元素。
- `cancelable` Boolean 类型值，只读，表明是否可以取消事件的默认行为。
- `currentTarget` Element 类型值，只读，表明其事件处理程序当前正在处理事件的那个元素。
- `defaultPrevented` Boolean 类型值，只读，值为 true 表明已经调用了`preventDefault()`方法。
- `detail` Integer 类型值，只读，表示与事件相关的细节信息(IE11 和其他浏览器显示不一样)。
- `eventPhase` Integer 类型值，只读，表示调用事件处理程序的阶段(1：表示捕获阶段； 2：表示处于目标阶段； 3：表示冒泡阶段。)。
- `target` Element 类型值，只读，表示引发当前事件的元素对象，在 DOM 的事件模型中，文本节点也会触发 DOM 中的事件。
- `timeStamp` (仅对 Firefox 浏览器)：表示事件发生的时间。
- `type` 表示当前事件的名称。在需要通过一个函数处理多个事件时，可以使用`type`属性的值来进行分别判断。

下面的这些属性仅在 IE 中有效：

- `fromElement` 用以引用鼠标指针移出的元素。如：对于 onmouseover 等事件，当鼠标从元素 A 移到元素 B 上时，将在 B 上触发 onmouseover 事件，而`event.fromElement`属性表示的是“鼠标是从哪一个元素移动到B元素上的”，即鼠标指针在移动到元素 B 之前，鼠标指针所停留的哪一个元素，对此例，即是 A 元素。
- `srcElement` Element 类型值，只读，用以表示产生当前事件的元素，与`target`属性相同。
- `toElement` 表示鼠标指针移入的元素。如：在 mouseout 事件中，由于浏览器事先无法预测的用户的意图，因此只有当用户实际将鼠标移动到某个确定的元素上后，才会触发 onmouseout 事件，尽管这带有几毫秒的延迟。从而确定 toElement 属性所对应的值。
- `cancelBubble` Boolean 类型值，默认是 false。将其设置为 true 可以取消事件冒泡，与`stopPropagation()`方法的作用相同。虽然可以阻止冒泡，但并不能改变`bubbles`属性的值。
- `returnValue` Boolean 类型值，默认为 true。将其设置为 false 可以取消事件的默认行为，与`preventDefault()`方法的作用相同(IE9+ 浏览器不支持)


### 1.2 方法

event 对象中还包含一些方法，用于对事件行为进行处理：

- `preventDefault()` 只读，表示取消事件的默认行为。如果`cancelable`属性的值是 true，则可以使用这个方法。
- `stopPropagation() ` 只读，表示取消事件的进一步捕获或冒泡。如果 bubbles 为 true，则可以使用这个方法。
- `stopImmediatePropagation()` 只读，表示取消事件的进一步捕获或冒泡，同时阻止后续的该元素绑定的同类型的事件的处理程序被调用。该方法是前面两个方法作用的超集。

### 1.3 使用

兼容 DOM 浏览器在事件发生时会将一个 event 对象传入到该事件定义的处理程序中。但是调用的时候，有不同的情况：

* 在有些浏览器中，event 对象是事件处理程序函数的第一个参数；
* 在有些浏览器中，event 对象是事件处理程序函数中的默认含有的变量`event`。

```JavaScript
var oBox = document.getElementById('box');

oBox.onclick = function(e) {
	console.log(e);
}

oBox.onclick = function() {
  // firefox 浏览器输出 undefined，其他浏览器则输出事件对象
	console.log(event);
}

oBox.addEventListener('click', function() {
  // firefox 浏览器输出 undefined，其他浏览器则输出事件对象(除 IE8- 浏览器)
	console.log(event);
}, false);

// 兼容写法如下
oBox.onclick = function(e) {
	var e = e || event;
	console.log(e);
};
```

## 二、事件回调

每个元素的相关事件均可以添加一些响应操作，当元素上发生该事件时，会自动运行设置的回调。获取元素某一事件上绑定的所有事件回调函数：

```JavaScript
getEventListeners(document.querySelector('.someclass'));
```

### 2.1 事件传播机制

在给事件设置回调方法的时候，需要先了解下事件在浏览器中的传播机制。

浏览器中，当一个事件发生时，它会：

1. 先从浏览器顶级对象 window 一路向下传递，一直传递到触发这个事件的那个元素，这个过程被称为“事件捕获过程”；
2. 之后，这个事件又会从这个元素一路向上传递到 window 对象，这就是“事件冒泡过程”。

> 注意：在 IE8- 浏览器中，事件模型并未定义捕获过程，只有冒泡过程。

而注册事件回调方法也对应会有不同的方式，并有对应的区别。

### 2.2 在 HTML 标签中设置事件处理程序

在 HTML 标签中设置事件处理程序时，事件处理程序会在事件的冒泡阶段被触发执行。需要注意：

- 因为 HTML 里面不区分大小写，所以这里事件处理程序属性名大写、小写、大小混写均可，属性值就是相应事件处理程序的 JavaScript 代码。
- 使用这种方式时，若给同一元素写多个事件处理属性，浏览器**只执行第一个事件处理属性里面的代码，后面的会被忽略**。

```html
<div id="div1" onClick="console.log('div1');">
    div1
    <div id="div2" oNClick="console.log('div2');">
        div2
      	<div id="div3" onclick="console.log('div3');" onclick="console.log('div3333');">div3</div>
    </div>
    
</div>
```

由于是在冒泡阶段被触发执行，所以在点击 div3 之后会在控制台中依次输出："div3"、"div2"、"div1"。

### 2.3 在 JavaScript 中设置 DOM 对象属性注册事件处理程序

在 JavaScript 中可以通过设置某一事件目标的事件处理程序属性来为其注册相应的事件处理程序。 这种方式注册的事件也会在事件的冒泡阶段被触发。

事件处理程序属性名字由`on`后面跟着事件名组成，例如：`onclick`、`onmouseover`。需要注意：

- 因为 JavaScript 是严格区分大小写的，所以，这种形式下属性名只能按规定小写；
- 若给同一元素对象写多个事件处理属性，**后面写的会覆盖前面的**。(这是在修改一个对象属性的值，而属性的值是唯一确定的)。这点和在 HTML 中注册事件程序是不同的。

```html
<div id="div1">div1
    <div id="div2">div2
      	<div id="div3">div3</div>
    </div>
</div>
```

```JavaScript
var div1 = document.getElementById('div1');
var div2 = document.getElementById('div2');
var div3 = document.getElementById('div3');

div1.onclick = function () { console.log('div1'); };
div2.onclick = function () { console.log('div2'); };
div3.onclick = function () { console.log('div3'); };
div1.onclick = function () { console.log('div11111'); };
div1.onClick = function () { console.log('DIV11111'); };

// 点击 div3 之后依次输出 "div3"、"div2"、"div11111"
```

### 2.4 addEventListener(type, call, bool)

这个方法是标准事件模型中定义的。任何能够成为事件目标的对象都有这个方法。其参数如下：

- `type` 事件的类型，其值是字符串，但并不包括前缀“on”，如`click`、`focus`等；
- `call` 当指定类型的事件发生时应调用的函数；
- `bool` 布尔值，表示在什么阶段注册事件处理程序。默认是 false，表示在事件冒泡阶注册事件处理程序，可以省略不写（某些旧浏览器中不能省略）；设置为 true 的时候，表示在事件捕获阶段注册事件处理程序。

需要注意：

- 通过该方法给同一对象注册多个同类型的事件，并不会发生忽略或覆盖，而是会按顺序依次执行；
- 通过该方法给同一对象注册的事件中，注册在捕获阶段的程序先执行，冒泡阶段的后执行；
- 但是对于最终的触发事件的对象上，则是注册在冒泡阶段的程序先执行，注册在捕获阶段的程序后执行。

> 这个方法对应的删除事件的函数为`removeEventListener(type, call, bool)`。参数和这个方法相同。
> 
> 添加回调程序的时候，如果是通过匿名函数传入的，则无法单独删除该处理程序，只能通过删除该事件的全部回调的方式进行清除。

```JavaScript
var div1 = document.getElementById('div1');
var div2 = document.getElementById('div2');
var div3 = document.getElementById('div3');

div1.addEventListener('click', function () { console.log('div1-bubble'); }, false);
div2.addEventListener('click', function () { console.log('div2-bubble'); }, false);
div3.addEventListener('click', function () { console.log('div3-bubble'); }, false);
div3.addEventListener('click', function () { console.log('div3-bubble222'); }, false);
div1.addEventListener('click', function () { console.log('div1-capturing'); }, true);
div2.addEventListener('click', function () { console.log('div2-capturing'); }, true);
div3.addEventListener('click', function () { console.log('div3-capturing'); }, true);

// 点击 div3 区域之后输出：
// div1-capturing
// div2-capturing
// div3-bubble
// div3-bubble222
// div3-capturing
// div2-bubble
// div1-bubble
```

### 2.5 attachEvent(type, call)

IE8- 浏览器并不支持`addEventListener()`和`removeEventListener()`。而是相应的，定义了类似的方法`attachEvent()`和`detachEvent()`。

因为 IE8- 浏览器不支持事件捕获，所以`attachEvent()`并不能注册捕获过程中的事件处理函数。因此`attachEvent()`和`detachEvent()`只有两个参数：事件类型和事件处理函数。

需要注意：

- 这两个方法的事件类型参数是带`on`前缀的，如：`onclick`；
- 这种方式定义的事件处理程序也不会覆盖其他已定义的事件处理程序。

```JavaScript
var div1 = document.getElementById('div1');

function div1BubbleFun(){
  console.log('div1-bubble');
}

// 绑定事件处理程序
div1.attachEvent('onclick', div1BubbleFun);

// 删除事件处理程序
div1.detachEvent('onclick', div1BubbleFun);
```

### 2.6 事件回调的一些问题

1. 事件处理程序的参数

    通常事件对象作为参数传递给事件处理函数。但 IE8- 浏览器中全局变量 event 才是事件对象，在写相关代码时应该注意兼容性问题。

2. 事件处理程序的运行环境

    关于事件处理程序的运行环境，也就是在事件处理程序中调用上下文(this值)的指向问题。
    
    - 通过`attachEvent()`注册的事件处理程序中 this 指向 window。
    - 其他的三种方式注册的事件处理程序都指向发生事件的 DOM 对象本身。

3. 事件处理程序的调用顺序

    - 通过 HTML 属性注册的处理程序和通过设置对象属性的处理程序一直优先调用；
    - 使用`addEventListener()`注册的处理程序按照它们的注册顺序依次调用；
    - 使用`attachEvent()`注册的处理程序可能按照任何顺序调用，所以代码不应该依赖于调用顺序。

4. 事件默认动作的取消

    - 如果使用 HTML 属性或者 JavaScript DOM 对象属性方式注册的事件处理程序，可以在处理程序中返回 false 来取消事件的浏览器默认操作；
    - 在支持`addEventListener()`的浏览器中，也可以通过调用事件对象的`preventDefault()`方法来取消；
    - IE8- 浏览器可以通过设置事件对象的`returnValue`属性为 false 来取消事件的默认操作。


5. 事件传播的取消
    
    - 在支持`addEventListener() 的浏览器中，可以调用事件对象的`stopPropagation()`方法来阻止事件的继续传播，它能工作在事件传播期间的任何阶段（捕获阶段，事件目标阶段，冒泡阶段）。
    - 在 IE8- 浏览器中，可以设置事件对象的`cancelBubble`属性为 true 来阻止事件的冒泡(IE8- 浏览器没有事件捕获阶段)。

6. 在下面三种情况下，返回 false 时的结果：
    
    - jQuery 事件处理函数中：相当于 jQuery 的 event 对象连续调用了`preventDefault() `和`stopPropagation()`方法。
    - 超链接的原生 JavaScript onClick 事件处理函数中：会阻止浏览器默认的地址导航，并且阻止了 DOM 事件的冒泡传递。
    - 非超链接标签的原生 JavaScript onClick 事件处理函数中：此时不会起任何作用。

## 三、事件类型

目前 DOM 的事件有三个级别：DOM0、DOM2、DOM3。可以通过下面的代码判断浏览器是否支持 DOM2 和 DOM3 级事件：

```JavaScript
var isSupported2 = document.implementation.hasFeature('HTMLEvents', "2.0");
var isSupported3 = document.implementation.hasFeature('HTMLEvents', "3.0");
```

除了按照级别划分事件，还可以根据其触发调解分为以下多种类别。

### 3.1 UI 事件

UI 事件具有如下的几种常见事件：

1. `load` 资源加载完成后触发
   
    - 当页面完全加载后，在 window 上面触发
    - 当所有的框架都加载完毕时，在框架集上面触发
    - 当图像加载完毕时，在`<img>`元素上触发
    - 当嵌入的内容加载完毕后，在`<object>`元素上触发

2. `unload` 资源卸载完成后触发
   
    - 当页面完全卸载后，在 window 上面触发
    - 当所有的框架都卸载后，在框架集上面触发
    - 当嵌入的内容卸载完毕后，在`<object>`元素上面触发

3. `resize` 尺寸改变时触发
   
    - 当窗口或者框架的大小变化时，在 window 或 框架 上面触发

4. `scroll` 当用户滚动带滚动条的元素中的内容时，在该元素上触发
   
    - 滚动整个页面的时候，可以在 body 事件上触发
    - 如果某个元素中的内容超过其设定的长度，而且设置了`overflow:auto|scroll`则可以触发

### 3.2 错误事件

`error` 当发生错误的时候触发：

* 当发生 JavaScript 错误的时候，在 window 上面触发
* 当无法加载资源时触发，如`<img>`、`<object>`元素
* 当有一个或多个框架无法加载时，在框架集上面触发

### 3.3 焦点事件

1. `blur` 在元素失去焦点时触发。这个事件不会冒泡。而且所有的浏览器都支持。
2. `focus` 在元素获得焦点时触发。这个事件不会冒泡。而且所有的浏览器都支持。

### 3.4 鼠标事件

DOM 事件模型还引入了一个 MouseEvent 对象，该对象专门用以处理由鼠标操作所引发的各种事件。

尽管任何一个事件都会创建一个 event 对象，但是只有一组选定的事件集才能生成 MouseEvent 对象。

#### 3.3.1 鼠标事件的属性

MouseEvent 对象同时有 event 和下面的这些属性，都可以访问。

- `altKey` 一个逻辑值，表示当事件发生时，Alt 键是否被按下。
- `shiftKey` 一个逻辑值，表示当事件发生时，Shift 键是否被按下。
- `ctrlKey` 一个逻辑值，表示当前事件发生时，Ctrl 键是否被按下。
- `metaKey` 一个逻辑值，表示当事件发生时，Meta 键是否被按下。
- `keyCode` (在IE中)可以检查是否有键盘按键按下。返回一个数字，该数字表示与被按下的键所对应的 Unicode 字符编码。如果没有任何键被按下，则返回数字 0。
- `clientX` 表示当事件发生时，鼠标指针在客户端区域中(即浏览器或类似的)的横坐标，即 x 坐标。以像素为单位。
- `clientY` Y 坐标。
- `screenX` 表示当事件发生时，鼠标指针相对于计算机屏幕区域中的横坐标，即 x 坐标。
- `screenY` 表示当事件发生时，鼠标指针相对于计算机屏幕区域中的纵坐标，即 y 坐标。
- `relatedTarget` (仅对于支持 DOM 的浏览器)该属性表示与事件相关的元素节点，类似于 IE 浏览器中的`event.toElement`属性和`event.fromElement`属性。
- `button` 对于鼠标事件，该属性表示属性的哪一个按钮被按下。返回一个 0~7 的数字：
    * 0：没有按键被按下。
    * 1：鼠标左键被按下。
    * 2：鼠标右键被按下。
    * 3：鼠标左键和右键同时被按下。
    * 4：鼠标中键被按下。
    * 5：鼠标左键和中键被按下。
    * 6：鼠标右键和中键被按下。
    * 7：鼠标三个键都被按下。

#### 3.3.2 鼠标事件的种类

下面这些事件将会触发 MouseEvent 事件。

1. `click` 用户单击主鼠标按键（一般是鼠标左键），或者按下回车键时触发。能通过回车键触发这一点确保了元素的`onclick`事件是能够通过键盘来触发的。
2. `dbclick` 用户双击主鼠标按键（一般是鼠标左键）时触发。这个事件并不是 DOM2 级事件规范中规定的，但是得到了广泛的支持，而被纳入了 DOM3 级事件中。
3. `mousedown` 用户按下了任意鼠标按钮时触发。这个事件不能通过键盘触发。
4. `mouseup` 用户按下鼠标按钮后，在释放鼠标按钮的时候触发。不能通过键盘触发。
5. `mouseenter` 在鼠标光标从元素外部移动到元素范围内时触发。这个事件不能冒泡，而且在光标移动到元素上之后不会继续触发该事件。另外，光标在该元素的后代元素上的变动，也不会触发该元素的`mouseenter`事件。属于 DOM3 级事件，但是支持很广泛。
6. `mouseover` 鼠标光标从一个元素移动到 A 元素上时，在 A 元素上触发。不能通过键盘触发。当一个元素注册了这个事件后：
    - 鼠标从外部元素移动到 A 元素上会触发该事件，事件处理程序中，`e.target`为 A 元素，`this`为 A 元素；
    - 鼠标从 A 元素移动到子元素上会触发该事件，事件处理程序中，`e.target`为移动到的子元素，`this`为 A 元素；
    - 鼠标从 A 元素的子元素移动到 A 元素上会触发该事件，事件处理程序中，`e.target`为 A 元素，`this`为 A 元素。
7. `mouseleave` 在位于元素上方的鼠标光标移动到元素范围之外时触发。光标从元素移动到该元素的后代元素上也不会触发该事件。这个事件不冒泡。属于 DOM3 级事件，而且支持很广泛。
8. `mouseout` 鼠标光标从 A 元素上移动到另一个元素上时，在 A 元素上触发。另一个元素可以是 A 元素的子元素，也可以是外部元素。不能通过键盘触发该事件。
9. `mousemove` 当鼠标光标在元素内移动时触发。只要鼠标光标在元素内移动，就会一直重复触发该事件。
10. `mousewheel` 当用户通过鼠标滚轮与页面交互、在垂直方向滚动页面时（向上或向下），会触发该事件。

### 3.4 键盘与文本事件

1. `keydown` 当用户按下键盘上的任意键时触发。如果按住按键不放，会重复触发此事件。
2. `keyup` 当用户释放键盘上的按键时触发。
3. `keypress` 当用户按下键盘上的字符键时触发。如果按住按键不放，会重复触发此事件。按下 Esc 键也会触发这个事件。
4. `textInput` 在文本插入文本框之前会触发这个事件。DOM3 级事件。这个事件是对`keypress` 的补充，用意是在将文本显示给用户之前更容易拦截文本。支持这个事件的浏览器：IE9+、Safari 和 Chrome。

在发生`keydown`和`keyup`事件时，事件对象的`keyCode`属性中会包含一个代码，与键盘上特定的键对应。与 Shift 键的状态无关。

对数字、字母、字符按键，`keyCode`属性的值与 ASCII 码中对应的小写字母或数字的编码相同。因此：数字键 7 的`keyCode`值为 55；字母 A 键的`keyCode`值为 65。如果要对应大写字母，则需要考虑字母键和 Shift 键的状态。

### 3.5 触摸事件

1. `touchstart` 当手指触摸屏幕时触发，即使已经有手指放在了屏幕上也会触发。
2. `touchend` 当手指从屏幕上移开时触发。
3. `touchmove` 当手指在屏幕上滑动时连续地触发。在这个事件发生期间，调用事件对象的`preventDefault()`可以阻止滚动。

每个触摸事件的 event 对象都提供了在鼠标事件中常见的属性：`bubbles`、`cancelable`、`view`、`clientX`、`clientY`、`screenX`、`screenY`、`detail`、`altKey`、`shiftKey`、`ctrlKey`和`metaKey`。

除了常见的 DOM 属性外，触摸事件还包含下列三个用于跟踪触摸的属性：

- `touches` 表示当前跟踪的触摸操作的 Touch 对象的数组。
- `targetTouchs` 特定于事件目标的 Touch 对象的数组。
- `changeTouches` 表示自上次触摸以来发生了什么改变的 Touch 对象的数组。

每个 Touch 对象包含下列属性：

- `clientX` 触摸目标在视口中的 x 坐标。
- `clientY` 触摸目标在视口中的 y 坐标。
- `identifier` 标识触摸的唯一 ID。
- `pageX` 触摸目标在页面中的 x 坐标。
- `pageY` 触摸目标在页面中的 y 坐标。
- `screenX` 触摸目标在屏幕中的 x 坐标。
- `screenY` 触摸目标在屏幕中的 y 坐标。
- `target` 触摸的 DOM 节点目标。

### 3.6 DOM 变动事件

当我们改变了 DOM 结构的时候，会触发下面的一些事件：

- `DOMSubtreeModified` 在 DOM 结构中发生任何变化时触发。这个事件在下面的其他任何事件触发后都会触发。
- `DOMNodeInserted` 在一个节点作为子节点被插入到另一个节点中时触发。
- `DOMNodeRemoved` 在节点从其父节点中被移除时触发。
- `DOMNodeInsertedIntoDocument` 在一个节点被直接插入文档或通过子树间接插入文档之后触发。这个事件在 DOMNodeInserted 之后触发。
- `DOMNodeRemovedFromDocument` 在一个节点被直接从文档中移除或通过子树间接从文档中移除之前触发。这个事件在 DOMNodeRemoved 之后触发。
- `DOMAttrModified` 在元素节点属性被修改之后触发。
- `DOMCharacterDataModified` 在文本节点的值发生变化时触发。
`DOMSubtreeModified`、`DOMNodeInserted`、`DOMNodeRemoved`的兼容性：Firefox 3+、Safari 3+、Chrome 及 IE9+

### 3.7 HTML5 事件

除了上述的事件，在 HTML5 中还新增了一些更有用的事件。

- `contextmenu` 右键菜单事件。当用户在页面上点击鼠标右键(或其他相同作用的操作)时触发。
- `beforeunload` 资源被卸载前触发。
- `DOMContentLoaded` 形成完整的 DOM 树之后就会触发，不理会图像、JavaScript 文件、CSS 文件或其他资源是否已经下载完毕。对于不支持该事件的浏览器，建议在页面加载期间设置一个时间为 0 毫秒的超时调用。
- `readystatechange` IE 为 DOM 文档中的某些部分提供了该事件，目的是提供与文档或元素加载状态的有关信息。
- `pageshow` 在页面显示时触发。
- `pagehide` 在浏览器卸载页面或切换到别的标签页的时候触发。
- `hashchange` URL 的参数列表（即：URL 中“#”号后面的所有字符串）发生变化时触发。

## 四、事件回调的优化

影响内存和性能的因素：

- 每个函数都是对象，都会占用内存；内存中的对象越多，性能就越差。
- 必须事先指定所有事件处理程序而导致的 DOM 访问次数，会延迟整个页面的交互就绪时间。

对应的，优化方案如下：通过事件委托和移除没有必要的事件处理程序。

因为页面卸载后有些浏览器依然将事件处理程序保存在内存中，IE8 及更早版本在这种情况下依然是问题最多的浏览器，尽管其他浏览器或多或少也有类似的问题。所以我们需要在当页面的元素被替换前需要移除其事件处理程序，页面卸载前也需要移除事件处理程序(通过`onunload`事件处理程序移除所有事件处理程序)。


