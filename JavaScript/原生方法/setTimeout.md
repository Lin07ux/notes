`setTimeout`是 JavaScript 中常见的一个 Window 对象方法，用于设置一个定时器，在到达设定的毫秒数延迟之后运行指定的函数或代码。

### 基础语法

有如下三种常用方式：

```JavaScript
var timeoutId = setTimeout(code[, delay]);
var timeoutId = setTimeout(func, delay);
var timeoutId = setTimeout(func[, delay, param1, params2, ...]);
```

其中：

* `timeoutID`是该延时操作的数字 ID，此 ID 随后可以用来作为`window.clearTimeout`方法的参数。
* `func`是要在`delay`毫秒之后执行的函数。
* `code`在第二种语法，是指你想要在`delay`毫秒之后执行的代码字符串。该语法不推荐，因为要执行的代码字符串是使用`eval()`函数来执行，安全性差（可被植入恶意代码），执行效率低（需要将字符串解析为代码再执行。
* `delay`是延迟的毫秒数(1 秒 = 1000 毫秒)，函数的调用会在该延迟之后发生。如果省略该参数，取默认值 0。

> 需要注意的是，IE9 及更早的 IE 浏览器不支持第三种语法中向延迟函数传递额外参数的功能。

### 单线程与事件队列机制

由于 JavaScript 引擎使用的是单线程执行，也即是说一个时间内只能运行一段代码，其他的代码和事件回调等都需要在当前的代码执行完之后才能执行。

而 JavaScript 引擎会维持一个队列，这个队列里就是等待被执行的代码。当事件触发时，就会向这个队列的队尾压入事件的回调函数。当定时任务到达指定的延时后，就会触发对应的事件，从而将其要执行的代码也压入到队列的队尾。

JavaScript 引擎会从队首开始，一个个的执行队列中的代码。如果执行了队列中的全部代码，则会停下，继续等待任务的到来。

所以，**一个定时任务，不管设置的延时是多少（即便是 0），也会在其后的同步代码之后执行；定时任务真正执行的延时，不会小于设定的延时，也就是说，有可能会比设定的延时更长**。

比如，如下的两段代码：

```JavaScript
// 示例 1：
setTimeout(function () {
    alert("Hello World");
}, 1000);

while(true){};  // 该函数会陷入死循环，1 秒后并不会弹出提醒

// 示例 2：
setTimeout(function () {
    console.log('a')
}, 0);

console.log('b');
// 先输入 'b'，然后输出 'a'
```

需要注意的是：`setTimeout(fn, 0)`是在下一轮“事件循环”开始时执行`fn`，而立即`resolve`的`Promise`对象，是在本轮“事件循环”（event loop）的结束时执行对应的成功回调。如下代码所示：

```JavaScript
setTimeout(function () {
  console.log('three');
}, 0);

Promise.resolve().then(function () {
  console.log('two');
});

console.log('one');

// one
// two
// three
```

### 延时代码中的 this

在定时任务被执行的代码中，`this`默认是会指向到`window`的，而非定义时的对象。

如下示例：

```JavaScript
var a=1;
var obj={
    a:2,
    b:function(){
        setTimeout(function () {
            console.log(this.a);
        }, 2000);
    }
};
obj.b();
// 函数输出为 1
```

如果要让`this`绑定到定义时的对象，则可以通过定义特殊变量的方式来进行，或者通过闭包来实现，或者使用 ES6 的箭头函数。


### 分片代码提升性能

如果`list`很大，下面的这段递归代码会造成堆栈溢出：

```JavaScript
var list = readHugeList();

var nextListItem = function() {
    var item = list.pop();
    if (item) {
        // process the list item...
        nextListItem();
    }
};
```

要提升性能，可以通过使用定时任务：

```JavaScript
var list = readHugeList();

var nextListItem = function() {
    var item = list.pop();
    if (item) {
        // process the list item...
        setTimeout(nextListItem, 0);
    }
};
```

### 转摘

[前端计划——JavaScript中关于setTimeout的那些事](https://segmentfault.com/a/1190000009776999)


