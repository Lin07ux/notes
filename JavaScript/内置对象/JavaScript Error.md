> 转摘：[深入理解javascript错误处理机制](https://mp.weixin.qq.com/s/-1Q6BjHt8F2IDavPo8pD6w)

## 一、错误类别

JavaScript 错误，可分为编译时错误，运行时错误，资源加载错误。编译错误一般在开发阶段就会处理掉的，暂时不关注。

### 1.1 运行时错误

JavaScript 解析或运行时，一旦发生错误，引擎就会抛出一个错误对象。JavaScript 原生提供`Error`构造函数，所有抛出的错误都是这个构造函数的实例。每个实例对象有如下三个属性：

* `message`错误提示信息
* `name`错误名称
* `stack`错误的堆栈 
目前 JavaScript 中，运行时错误有六种，都是`Error`对象的派生对象：

* `SyntaxError` 语法错误，如代码不符合 JavaScript 的语法规则；
* `TypeError` 类型错误，如执行一个不是方法的变量；
* `RangeError` 范围错误，如创建数组时使用负数设置数组长度`new Array(-1)`；
* `ReferenceError` 引用错误，如引用一个不存在的变量；
* `EvalError` eval 执行错误，在`eval`函数没有正确执行时抛出；
* `URIError` URL 错误，指调`decodeURI`、`encodeURI`、`decodeURIComponent`、`encodeURIComponent`、`escape`、`unescape`时发生的错误。

### 1.2 资源加载错误

HTML 中的资源类标签(不包括`<link>`)中引用的资源加载出错时，会发生资源加载错误。资源类标签如下：

```
<img>
<input type="image">
<object>
<script>
<style>
<audio>
<video>
```

可以通过在资源标签元素上添加事件监听器来捕获错误事件，但需要注意的是，**资源加载错误不会冒泡，只能在事件流捕获阶段获取错误**。所以可以使用如下的两种方式来监听：

```
<img onerror="handleError">

# 第三个参数默认为false, 设为true, 表示在事件流捕获阶段捕获
window.addEventListener('error', handleError, true)
```

## 二、事件处理

在 JS 代码中，可以主动抛出错误，也可以在可能出现错误的代码外包裹`try...catch`来预防错误。

### 2.1 throw 抛出错误

`throw`语句用来抛出一个用户自定义的异常。当前函数的执行将被停止（`throw`之后的语句将不会执行），并且控制将被传递到调用堆栈中的第一个`catch`块。如果调用者函数中没有`catch`块，程序将会终止。

需要注意的是：抛出错误之后，如果没有被`catch`程序并不会完全停止，而是在`throw`后面的代码不会被执行，而在`throw`前面的设置的异步代码依旧会被正常处理。

比如，先设定一个定时器，然后再抛出错误，此时抛出错误后，后面的代码不会被执行，但是定时器依旧按照设定会继续执行。

```js
document.getElementById('btn-1').onclick = function() {
    console.log(1)
}

// 每1s打印一次
setInterval(() => {
    console.log('setInterval依然在执行')
}, 1000)

throw new Error('手动抛出异常')

// 这段代码不会执行
document.getElementById('btn-2').onclick = function() {
    console.log(2)
}
```

### 2.2 try...catch...finally 预处理

`try/catch`的作用是将可能引发错误的代码放在`try`块中，在`catch`中捕获错误，对错误进行处理，选择是否往下执行。

`try`代码块中的错误，会被`catch`捕获，如果在`catch`中没有再次手动抛出错误，就不会被`window`捕获，相当于这个错误已经被处理了。而`try...finally`则不能捕获错误。同时，`try...catch`只能捕获同步代码的错误，不能捕获异步代码错误。


> `catch`中抛出异常，用`throw e`，不要用`throw new Error(e)`，因为`e`本身就是一个 Error 对象了，具有错误的完整堆栈信息 stack，`new Error`会改变堆栈信息，将堆栈定位到当前这一行。

### 2.3 finally 的特性

#### 2.3.1 返回值

如果`finally`添加了`return`语句，则不管整个`try.catch`返回什么，返回值都是`finally`的`return`。

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
        return false; // 覆盖 try.catch 的返回
        console.log(4); //不会执行的代码
    }
    // "return false" is executed now 
    console.log(5); // not reachable
}
f(); // 输出 0, 1, 3; 返回 false
```

#### 2.3.2 finally 吞并异常

如果`finally`有`return`并且`catch中有`throw`异常，`throw`的异常不会被捕获，因为已经被`finally`的`return`覆盖了。

```javascript
function f() {
    try {
        throw "bogus";
    } catch(e) {
        console.log('caught inner "bogus"');
        throw e; // throw 语句被暂停，直到 finally 执行完成
    } finally {
        return false; // 覆盖 try.catch 中的 throw 语句
    }
    // 已经执行了"return false"
}

try {
    f();
} catch(e) {
    // 这里不会被执行，因为 catch 中的 throw 已经被 finally 中的 return 语句覆盖了
    console.log('caught outer "bogus"');
}
// 输出： caught inner "bogus"
```


## 三、使用

### 3.1 捕获全局异常

浏览器提供了全局的`onError`函数，我们可以使用它搜集页面上的错误：

```javascript
window.onerror = function(message, source, lineno, colno, error) { ... }
```

其中：

* mesage 为异常基本信息
* source 为发生异常的 Javascript 文件 url
* lineno 为发生错误的行号
* colno 为发生错误的列位置
* error 是错误事件对象。我们可以通过`error.stack`获取异常的堆栈信息。

下面是 chrome 中通过`window.onError`捕获的错误的例子：

```javascript
message: Uncaught ReferenceError: test is not defined
source:  http://test.com/release/attach.js
lineno:  16144
colno:   6
error:   ReferenceError: test is not defined
            at http://test.com/release/attach.js:16144:6
            at HTMLDocument.<anonymous> (http://test.com/release/vendor.js:654:71)
```

不过这种方法有个致命的问题：有些浏览器为了安全方面的考虑，对于不同域的 Javascript 文件，通过`window.onError`无法获取有效的错误信息。比如 Firefox 的错误消息只有`Script error`，而且无法获得确切的行号，更没有错误堆栈信息：

```javascript
message: Script error.
source:  "http://test.com/release/attach.js
lineno:  0
colno:   0
error:   null
```

为了使得浏览器针对`window.onError`的跨域保护失效，我们可以做如下操作：

* 在静态资源服务器或者 CDN 的 HTTP 头中加上如下允许跨域提示：`Access-Control-Allow-Origin: *`；
* 并在引用 Javascript 脚本时加上`crossorigin`属性：`<script crossorigin src=""></script>`

