window 就是指当前的浏览器窗口。JavaScript 的所有对象都存在于一个运行环境之中，这个运行环境本身也是对象，称为“顶层对象”。这就是说，JavaScript 的所有对象，都是“顶层对象”的下属。不同的运行环境有不同的“顶层对象”，在浏览器环境中，这个顶层对象就是`window`对象。所有浏览器环境的全局变量，都是`window`对象的属性。

## window 对象的属性

### document
`window.document`是一个指向 [document](https://developer.mozilla.org/en/DOM/document) 对象的引用。

### fullScreen
是一个布尔值：

* true   窗口处于全屏模式下
* false  窗口未处于全屏模式下

> 注：在常规窗口与全屏窗口之间切换会在相应的窗口中触发`resize`事件。

### screenX 和 screenY
返回浏览器窗口左上角相对于当前屏幕左上角`(0, 0)`的水平距离和垂直距离，单位为像素。

### innerWidth 和 innerHeight
返回网页在当前窗口中可见部分的宽度和高度，即“视口”（viewport），单位为像素。浏览器的视口，**不包括工具栏和滚动条**。

> 对于 Internet Explorer 8、7、6、5 需要使用：`document.documentElement.clientHeight`、`document.documentElement.clientWidth`或者`document.body.clientHeight`、`document.body.clientWidth`。

### outerWidth 和 outerHeight
返回浏览器窗口的宽度和高度，**包括浏览器菜单和边框**，单位为像素。

### scrollX 和 scrollY
前者返回文档/页面水平方向滚动的像素值，后者返回文档/页面垂直方向滚动的像素值。

### name
name 属性用于获取或设置当前浏览器窗口的名字。

```javascript
// 获取名称
string = window.name;
// 设置名称
window.name = string;
```

### closed
返回一个布尔值，是一个只读属性，表示窗口是否被关闭。

* true：窗口已经被关闭；
* false: 窗口开启着；

示例：

```javascript
//检查当前窗口的父窗口存在并且没有关闭
if (window.opener && !window.opener.closed) {
    window.opener.location.href = "http://www.mozilla.org";
}
```

### opener
opener 属性返回一个引用打开窗口的父窗口，从另一个窗口打开窗口时(使用`Window.open()`打开)，它维护一个参考`window.opener`作为第一个窗口。如果当前窗口没有父窗口,该方法返回 NULL。

通过`opener`属性，可以获得父窗口的的全局变量和方法，比如`windowOp.window.propertyName`和`windowOp.window.functionName()`。 该属性只适用于两个窗口属于同源的情况，且其中一个窗口由另一个打开。

### location
返回一个只读的位置对象与文档当前的位置信息，用于获取窗口当前的 URL 信息，等同于`document.location`。

```javascript
// 导航到一个新页面
location.assign("http://www.mozilla.org"); // or
location = "http://www.mozilla.org";

// 强制刷新当前页面
location.reload(true);

// 替换当前页面，并替换历史记录
location.replace('https://github.com');
```

### history
只读的，返回一个引用对象。但是它提供了一个接口，可以来操纵浏览器会话历史(页面访问当前页面的选项卡或框架加载)。

```javascript
history.back();  //相当于单击后退按钮
history.go(1);   //相当于history.back();
```

在顶级页面，你可以看到会话历史上的列表页面，通过历史对象，在浏览器旁边的后退和前进按钮。出于安全原因，历史对象不允许非特权的代码来访问会话历史上其他页面的 url，但它确实使它导航会话历史。没有办法清除会话历史或禁用后退/前进导航从无特权的代码。解决方案是可用的最友好的`location.replace()`方法,取代当前项的会话历史提供的 URL。

### frames
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

### navigator
只读的，返回一个`navigator`对象，可以查询信息应用程序运行脚本。

#### navigator.userAgent 属性
返回浏览器的 User-Agent 字符串，用来标示浏览器的种类。

```javascript
var sBrowser, sUsrAg = navigator.userAgent;

if(sUsrAg.indexOf("Chrome") > -1) {
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

#### navigator.plugins 属性
返回一个类似数组的对象，成员是浏览器安装的插件，比如 Flash、ActiveX 等。

### screen
返回一个屏幕与窗口相关的引用，包含了显示设备的信息。

`screen.width`和`screen.height`两个属性，一般用来了解设备的分辨率。除非调整显示器的分辨率，否则这两个值可以看作常量，不会发生变化。显示器的分辨率与浏览器设置无关，缩放网页并不会改变分辨率。

`screen.availHeight`和`screen.availWidth`返回屏幕可用的高度和宽度，单位为像素。它们的值为屏幕的实际大小减去操作系统某些功能占据的空间，比如系统的任务栏。

`screen.colorDepth`属性返回屏幕的颜色深度，一般为16（表示16-bit）或24（表示24-bit）。


## window 的方法

### close() 和 open()
`window.open`方法用于新建另一个浏览器窗口，并且返回该窗口对象。

语法：`var windowObjectReference = window.open(strUrl, strWindowName, [strWindowFeatures]);`

`windowObjectReference`：新创建的窗口的引用。如果调用失败，这将是 null。引用可用于访问新窗口提供了它的属性和方法符合同源 policysecurity 需求。

`strUrl`：新加载窗口的 URL，strUrl 可以成为在 web 上被浏览器支持的 HTML 文档、图像文件或任何资源。

`strWindowName`：一个新窗口的字符串名字。这个名字可以作为链接和表单使用的目标的目标属性  or 元素，不应该包含任何空格字符的名称，strWindowName 并不指定新窗口的标题。

`strWindowFeatures`：一个新窗口可选参数清单的功能(大小、位置、滚动条等)的字符串。字符串必须不包含任何空格,每个特性名称及其值必须由一个逗号分开。

