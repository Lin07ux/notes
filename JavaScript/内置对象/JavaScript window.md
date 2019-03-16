window 就是指当前的浏览器窗口。JavaScript 的所有对象都存在于一个运行环境之中，这个运行环境本身也是对象，称为“顶层对象”。这就是说，JavaScript 的所有对象，都是“顶层对象”的下属。不同的运行环境有不同的“顶层对象”，在浏览器环境中，这个顶层对象就是`window`对象。所有浏览器环境的全局变量，都是`window`对象的属性。

## 一、window 对象的属性

### 1.1 document

`window.document`是一个指向 [document](https://developer.mozilla.org/en/DOM/document) 对象的引用。

### 1.2 fullScreen

是一个布尔值：

* true   窗口处于全屏模式下
* false  窗口未处于全屏模式下

> 注：在常规窗口与全屏窗口之间切换会在相应的窗口中触发`resize`事件。

### 1.3 screenX 和 screenY

返回浏览器窗口左上角相对于当前屏幕左上角`(0, 0)`的水平距离和垂直距离，单位为像素。

### 1.4 innerWidth 和 innerHeight

返回网页在当前窗口中可见部分的宽度和高度，即“视口”（viewport），单位为像素。浏览器的视口，**不包括工具栏和滚动条**。

> 对于 Internet Explorer 8、7、6、5 需要使用：`document.documentElement.clientHeight`、`document.documentElement.clientWidth`或者`document.body.clientHeight`、`document.body.clientWidth`。

### 1.5 outerWidth 和 outerHeight

返回浏览器窗口的宽度和高度，**包括浏览器菜单和边框**，单位为像素。

### 1.6 scrollX 和 scrollY

前者返回文档/页面水平方向滚动的像素值，后者返回文档/页面垂直方向滚动的像素值。

### 1.7 name

name 属性用于获取或设置当前浏览器窗口的名字。

```javascript
// 获取名称
string = window.name;
// 设置名称
window.name = string;
```

### 1.8 closed

返回一个布尔值，是一个只读属性，表示窗口是否被关闭。

* true：窗口已经被关闭；
* false: 窗口开启着；

示例：

```javascript
// 检查当前窗口的父窗口存在并且没有关闭
if (window.opener && !window.opener.closed) {
    window.opener.location.href = "http://www.mozilla.org";
}
```

### 1.9 opener

opener 属性返回一个引用打开窗口的父窗口，从另一个窗口打开窗口时(使用`Window.open()`打开)，它维护一个参考`window.opener`作为第一个窗口。如果当前窗口没有父窗口，该方法返回 NULL。

通过`opener`属性，可以获得父窗口的的全局变量和方法，比如`windowOp.window.propertyName`和`windowOp.window.functionName()`。 该属性只适用于两个窗口属于同源的情况，且其中一个窗口由另一个打开。

### 1.10 location

返回一个只读的位置对象与文档当前的位置信息，用于获取窗口当前的 URL 信息，等同于`document.location`。

#### 1.10.1 属性

其属性如下：

> 以`http://localhost:80/dir/index.html?q1=abc&q2=efg&q3=h#anchor`为例。

|   属性    |   定义          |   示例                    |
|----------|--------------------------------------|-----------------------|
| hash     | URL 中的 hash，没有则为空，包含 #        | #anchor               |
| host     | 服务器地址，即：主机名（域名）＋ 端口号    | localhost:80          |
| hostname | 服务器域名                             | localhost             |
| href     | 当前页面的完整 url                      | http://localhost:80/dir/index.html?q1=abc&q2=efg&q3=h#anchor |
| origin   | 服务器域，包含协议、域名和端口             | http://localhost:80   |
| pathname | URL 中的目录和文件名                    | /dir/index.html       |
| port     | 端口号（如果没有指定则为空字符串）          | 80                   |
| protocol | 协议                                   | http                 |
| search   | 查询字符串。这个字符以问号开头，没有则为空   | ?q1=abc&q2=efg&q3=h   |

location 的这 9 个属性都是可读写的。其中，改变`location.href`会跳转到新的 URL 页面，而修改`location.hash`会跳到当前页面中锚点位置。每次修改`window.location`的属性（hash 除外），页面都会以新的 URL 重新加载，并在浏览器的历史纪录中生成一条新纪录。

#### 1.10.2 方法

* `assign(url)` 打开新的 URL，并在浏览器的历史纪录中生成一条记录。
* `replace(url)` 打开新的 URL，但是不会在浏览器的历史纪录中生成新纪录。
* `reload(force)` 刷新当前页面。force 为 true 时从服务器端重新加载；为 false 时从浏览器缓存中重新加载。默认值 false。

其中，`location.assign(url)`的效果跟下列两行代码的效果完全一样：

```javascript
window.location = url;
location.href = url;
```

位于`location.reload()`调用之后的代码可能会也可能不会执行，这取决于网络延迟或系统资源等因素。因此，最好将`location.reload()`放在代码的最后一行。


### 1.11 history

只读的，返回一个引用对象。但是它提供了一个接口，可以来操纵浏览器会话历史(页面访问当前页面的选项卡或框架加载)。

```javascript
history.back();  //相当于单击后退按钮
history.go(1);   //相当于history.back();
```

在顶级页面，你可以看到会话历史上的列表页面，通过历史对象，在浏览器旁边的后退和前进按钮。出于安全原因，历史对象不允许非特权的代码来访问会话历史上其他页面的 url，但它确实使它导航会话历史。没有办法清除会话历史或禁用后退/前进导航从无特权的代码。解决方案是可用的最友好的`location.replace()`方法,取代当前项的会话历史提供的 URL。

### 1.12 frames

frames 属性返回窗口本身， 这是一个数组类对象，成员为所有框架的窗口，包括 frames 元素和 iframe 元素。

`window.frames[0]`表示页面中第一个框架窗口，`window.frames['someName']`则是根据框架窗口的 name 属性的值（不是 id 属性），返回该窗口。另外，通过`document.getElementById()`方法也可以引用指定的框架窗口。

```javascript
window.frames[0] === getElementsByTagName(“iframe”)[0].contentWindow);

// window.length 属性返回当前页面中所有框架窗口总数
window.frames.length === window.length // true
```

window.frames 的每个成员对应的是框架内的窗口（即框架的 window 对象）。如果要获取每个框架内部的 DOM 树，需要使用`window.frames[0].document`的写法。

`iframe`元素遵守同源政策：只有当父页面与框架页面来自同一个域名，两者之间才可以用脚本通信，否则只有使用`window.postMessage`方法。iframe 窗口内部，使用`window.parent`引用父窗口。如果当前页面没有父窗口，则`window.parent`属性返回自身。因此，可以通过`window.parent`是否等于`window.self`，判断当前窗口是否为 iframe 窗口。

```javascript
if (window.parent != window.self) {
    // 当前窗口是子窗口
}
```

### 1.13 navigator

只读的，返回一个`navigator`对象，可以查询信息应用程序运行脚本。

#### 1.13.1 navigator.userAgent 属性

返回浏览器的 User-Agent 字符串，用来标示浏览器的种类。

```javascript
var sBrowser, sUsrAg = navigator.userAgent;

if (sUsrAg.indexOf("Chrome") > -1) {
    sBrowser = "Google Chrome";
} else if (sUsrAg.indexOf("Safari") > -1) {
    sBrowser = "Apple Safari";
} else if (sUsrAg.indexOf("Opera") > -1) {
    sBrowser = "Opera";
} else if (sUsrAg.indexOf("Firefox") > -1) {
    sBrowser = "Mozilla Firefox";
} else if (sUsrAg.indexOf("MSIE") > -1) {
    sBrowser = "Microsoft Internet Explorer";
}

console.log("You are using: " + sBrowser);
```

通过 userAgent 属性识别浏览器，不是一个好办法。因为必须考虑所有的情况（不同的浏览器，不同的版本），非常麻烦，而且无法保证未来的适用性，更何况各种上网设备层出不穷，难以穷尽。所以，现在一般不再识别浏览器了，而是使用“功能识别”方法，即逐一测试当前浏览器是否支持要用到的 JavaScript 功能。

不过，通过 userAgent 可以大致准确地识别手机浏览器，方法就是测试是否包含“mobi”字符串。

#### 1.13.2 navigator.plugins 属性

返回一个类似数组的对象，成员是浏览器安装的插件，比如 Flash、ActiveX 等。

### 1.14 screen
返回一个屏幕与窗口相关的引用，包含了显示设备的信息。

`screen.width`和`screen.height`两个属性，一般用来了解设备的分辨率。除非调整显示器的分辨率，否则这两个值可以看作常量，不会发生变化。显示器的分辨率与浏览器设置无关，缩放网页并不会改变分辨率。

`screen.availHeight`和`screen.availWidth`返回屏幕可用的高度和宽度，单位为像素。它们的值为屏幕的实际大小减去操作系统某些功能占据的空间，比如系统的任务栏。

`screen.colorDepth`属性返回屏幕的颜色深度，一般为16（表示16-bit）或24（表示24-bit）。


## 二、window 的方法

### 2.1 close() 和 open()

`window.open`方法用于新建另一个浏览器窗口，并且返回该窗口对象。`window.close`方法用于关闭指定窗口，一般用来关闭`window.open`方法新建的窗口。

语法：`var windowObjectReference = window.open(strUrl, strWindowName, [strWindowFeatures]);`

`windowObjectReference`：新创建的窗口的引用。如果调用失败，这将是 null。引用可用于访问新窗口提供了它的属性和方法符合同源 policysecurity 需求。

`strUrl`：新加载窗口的 URL，strUrl 可以成为在 web 上被浏览器支持的 HTML 文档、图像文件或任何资源。

`strWindowName`：一个新窗口的字符串名字。这个名字可以作为链接和表单使用的目标的目标属性  or 元素，不应该包含任何空格字符的名称，strWindowName 并不指定新窗口的标题。

`strWindowFeatures`：一个新窗口可选参数清单的功能(大小、位置、滚动条等)的字符串。字符串必须不包含任何空格，每个特性名称及其值必须由一个逗号分开。

```javascript
var popup = window.open(
  'index.html',
  'DefinitionsWindows',
  'height=200,width=200,location=no,resizable=yes,scrollbars=yes'
);
```

> 由于 open 这个方法很容易被滥用，许多浏览器默认都不允许脚本新建窗口。因此，有必要检查一下打开新窗口是否成功。

在关闭窗口之前，做好验证下其是否还存在，没有被关闭：

```javascript
if ((popup !== null) && !popup.closed) {
  // 窗口仍然打开着
}
```

### 2.2 moveTo()

用于移动窗口到指定位置。语法为：`window.moveTo(x, y);`

参数 x、y 分别是窗口左上角距离屏幕左上角的水平距离和垂直距离，单位为像素。

### 2.3 moveBy()

将窗口移动到一个相对当前位置偏移的位置。语法：`window.moveBy(deltaX, deltaY);`

参数 x、y 分别是窗口左上角向右移动的水平距离和向下移动的垂直距离，单位为像素。

### 2.4 resizeTo()

动态调整窗口到指定的宽高。语法：`window.resizeTo(oWidth, oHeight);`

参数 oWidth、oHeight 分别是窗口 outerWidth 和 outerHeight 的整数(包括滚动条、标题栏等)，单位为像素。

### 2.5 resizeBy()

根据当前窗口的宽高动态调整窗口的大小。语法：`window.resizeBy(xDelta, yDelta);`

参数 xDelta、yDelta 分别是窗口水平增长、垂直增长的数量，单位为像素。

比如，将创建宽高都减少 300px：`window.resizeBy(-300, -300);`

### 2.6 print()

跳出打印对话框，与用户点击菜单里面的“打印”命令效果相同。

```javascript
//页面上的打印按钮代码如下
document.getElementById('printLink').onclick = function() {
  window.print();
}

//非桌面设备（比如手机）可能没有打印功能，需要判断
if (typeof window.print === 'function') {
  // 支持打印功能
}
```

### 2.7 focus() 和 blur()

`window.focus()`方法会激活指定当前窗口，使其获得焦点。
`window.blur()`把键盘焦点从顶层窗口移开。

> blur 方法好像并没有明显的效果。

### 2.8 matchMedia()

返回一个新的 MediaQueryList 对象代表指定的媒体属性的解析结果。

语法：`window.matchMedia(mediaQueryString);`

mediaQueryString 是一个代表媒体查询返回一个 newMediaQueryList 对象的字符串。

```javascript
console.log(window.matchMedia("(min-width: 400px)"));
// MediaQueryList {
//    media: "(min-width: 400px)",
//    matches: false, 
//    onchange: null,
//    __proto__: MediaQueryList
// }
```

> 我也不知道为什么这里 min-width 竟然不匹配。。。

### 2.9 MediaQueryList()

该方法给出了所有元素的CSS属性的值在应用活动样式表和包含可能解决任何基本计算的值。

语法：`var style = window.getComputedStyle(element[, pseudoElt]);`

第一个参数表示一个 DOM 元素，第二个参数 pseudoElt 可选，用一个字符串指定伪元素匹配。必须为常规元素(或者为空)。

```html
<style>
 h3::after {
   content: ' rocks!';
 }
</style>

<h3>generated content</h3> 

<script>
  var h3       = document.querySelector('h3'), 
      result   = getComputedStyle(h3, ':after').content;

  console.log('the generated content is: ', result); // returns ' rocks!'
</script>
```

### 2.10 postMessage()

window.postMessage 方法能够安全地跨起源沟通。通常情况下，在不同的页面中的脚本可以访问对方，当且仅当执行他们的网页都在使用相同的协议，主机位置（通常都 HTTPS），端口号（443 是 HTTPS 的默认设置），以及（模文件,域由两个网页为相同的值）被设置。 window.postMessage 提供了一种控制机制，在某种程度上正确的使用是安全的绕过这个限制。该 window.postMessage 方法被调用时，会导致 MessageEvent 消息在目标窗口被分派时，任何未决的脚本，必须执行完毕后（例如，剩余的事件处理程序，如果 window.postMessage 是从事件处理函数调用，先前设置的挂起超时等）的 MessageEvent 有类型的消息，它被设置为提供给 window.postMessage，对应于窗口调用 window.postMessage 在主文档的原点的原点属性的第一个参数的值的数据属性时间 window.postMessage 被调用，并且它是从哪个 window.postMessage 称为窗口源属性。（事件的其他标准属性都存在与他们的预期值。）

语法：`otherWindow.postMessage(message, targetOrigin, [transfer]);`

参数：

* windowObj: 接受消息的 Window 对象。
* message: 数据被发送到其它窗口中。数据使用的结构化克隆算法序列。这意味着你可以通过各种各样安全数据对象目标窗口，而无需自己序列化。在最新的浏览器中可以是对象。
* targetOrigin: 目标的源，* 表示任意。
* tansfer 可选 是与该消息传送转换对象序列。这些对象的所有权被提供给目的地和它们不再在发送端使用。
* message 事件就是用来接收 postMessage 发送过来的请求的。函数参数的属性有以下几个：
* origin: 发送消息的 window 的源。
* data: 数据。
* source: 发送消息的 Window 对象。

```javascript
//example1 

// 其他窗口可以监听通过执行下面的 JavaScript 派出的消息：
window.addEventListener("message", receiveMessage, false);

function receiveMessage (event) {
    var origin = event.origin || event.originalEvent.origin; // For Chrome, the origin property is in the event.originalEvent object.
  
    if (origin !== "http://example.org:8080")
        return;

    // ...
}


//example2

/*
 * In window A's scripts, with A being on :
 */
var popup = window.open(...popup details...);

// When the popup has fully loaded, if not blocked by a popup blocker:
// This does nothing, assuming the window hasn't changed its location.
popup.postMessage("The user is 'bob' and the password is 'secret'",
                  "https://secure.example.net");

// This will successfully queue a message to be sent to the popup, assuming
// the window hasn't changed its location.
popup.postMessage("hello there!", "http://example.org");

function receiveMessage (event) {
    // Do we trust the sender of this message?  (might be
    // different from what we originally opened, for example).
    if (event.origin !== "http://example.org")
        return;

    // event.source is popup
    // event.data is "hi there yourself!  the secret response is: rheeeeet!"
}
window.addEventListener("message", receiveMessage, false);


//example3

/*
 * In the popup's scripts, running on :
 */
// Called sometime after postMessage is called
function receiveMessage (event) {
    // Do we trust the sender of this message?
    if (event.origin !== "http://example.com:8080")
        return;

  // event.source is window.opener
  // event.data is "hello there!"

  // Assuming you've verified the origin of the received message (which
  // you must do in any case), a convenient idiom for replying to a
  // message is to call postMessage on event.source and provide
  // event.origin as the targetOrigin.
  event.source.postMessage("hi there yourself!  the secret response " +
                           "is: rheeeeet!",
                           event.origin);
}

window.addEventListener("message", receiveMessage, false)
```

### 2.11 alert()、confirm()、prompt()

alert()、prompt()、confirm() 都是浏览器与用户互动的全局方法。它们会弹出不同的对话框，要求用户做出回应。需要注意的是：这三个方法弹出的对话框，都是浏览器统一规定的式样，是无法定制的。

**alert()**

alert 方法弹出的对话框，只有一个“确定”按钮，往往用来通知用户某些信息。其参数只能是字符串，没法使用 CSS 样式，但是可以用`\n`指定换行。

**confirm()**

confirm 方法弹出的对话框，除了提示信息之外，只有“确定”和“取消”两个按钮，往往用来征询用户的意见。confirm 方法返回一个布尔值，如果用户点击“确定”，则返回 true；如果用户点击“取消”，则返回 false。confirm 的一个用途是，当用户离开当前页面时，弹出一个对话框，问用户是否真的要离开。

**prompt()**

prompt 方法弹出的对话框，在提示文字的下方，还有一个输入框，要求用户输入信息，并有“确定”和“取消”两个按钮。它往往用来获取用户输入的数据。返回值是一个字符串（有可能为空）或者null，具体分成三种情况：

* i: 用户输入信息，并点击“确定”，则用户输入的信息就是返回值。
* ii: 用户没有输入信息，直接点击“确定”，则输入框的默认值就是返回值。
* iii: 用户点击了“取消”（或者按了 Esc 按钮），则返回值是null。

prompt 方法的第二个参数是可选的，但是如果不提供的话，IE 浏览器会在输入框中显示undefined。因此，最好总是提供第二个参数，作为输入框的默认值。

## 三、window 对象的事件

### 3.1 window.onerror

window.onerror 是一个针对错误事件的事件处理程序，错误事件是对不同类型的错误目标。当一个 JavaScript 运行时错误（包括语法错误）时，使用接口的 ErrorEvent 一个错误事件在窗口 window.onerror 被触发时被调用。当资源（如一个 <img> 或 <script>）未能加载，使用接口事件的错误事件的元素，发起负载被触发，并且该元素上的 onerror() 调用处理程序。这些错误事件不冒泡到窗口，但（至少在 Firefox）可以用一个捕获 window.addEventListener 处理。安装一个全球性的错误事件处理程序对错误报告自动收集有用的。

语法：由于历史的原因，传递给`window.onerror`和`element.onerror`处理程序的参数不相同。

**window.onerror**

`window.onerror = function(message, source, lineno, colno, error) { ... };`

参数：

* message: 错误消息（string）。可作为 HTML 的 onerror="" 的处理事件；
* source： 脚本的URL，其中引发的错误（string）；
* lineno:  出错的错误行号（number）；
* colno:   对发生错误的行号列（number）；
* error:   错误的对象；

当函数返回 true，可以取消默认事件处理程序的处理。

```javascript
window.onerror = function (msg, url, lineNo, columnNo, error) {
    var string = msg.toLowerCase();
    var substring = "script error";
    if (string.indexOf(substring) > -1){
        alert('Script Error: See Browser Console for Detail');
    } else {
        alert(msg, url, lineNo, columnNo, error);
    }
  return false;
};
```

**element.onerror**

`element.onerror = function(event) { ... };`

element.onerror 接受带有类型事件的单个参数的函数。

> 并不是所有的错误，都会触发 JavaScript 的 error 事件（即让 JavaScript 报错），只限于以下三类事件：
> 1. JavaScript 语言错误
> 2. JavaScript脚本文件不存在
> 3. 图像文件不存在
>
> 以下两类事件不会触发 JavaScript 的 error 事件。
> 1. CSS 文件不存在
> 2. iframe文件不存


