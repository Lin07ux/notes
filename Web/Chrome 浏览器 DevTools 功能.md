## 选取 DOM 元素
使用 jQuery 能够方便的选择 DOM 中的元素，而在没有引入 jQuery 的情况下，在控制台里也可以通过类似的方法选取 DOM，只不过要使用的是`$$`这样的符号来代替 jQuery 中的`$`，用法则和 jQuery 完全相同。

选择返回的结果是一个数组，可以通过数组的方法来访问其中的单个元素。

例如：`$$('.className')`会返回给你所有包含 className 类属性的元素，之后可以通过`$$('className')[0]`和`$$('className')[1]`来访问其中的某个元素。

## 让 Chrome 变成所见即所得的编辑器
如果想能够在网页上直接编辑相关的文字来查看效果，可以使用 HTML5 添加的一个属性使得网页能够直接随意编辑。

打开 Chrome 的开发者控制台，输入：

```javascript
document.body.contentEditable = true;
```

然后我们就能够直接编辑网页上的内容了。

## 获取某个 DOM 元素绑定的事件
在调试的时候，如果需要知道某个元素上面绑定了什么触发事件，可以使用`getEventListeners($('selector'))`方法以数组对象的格式返回某个元素绑定的所有事件。你可以在控制台里展开对象查看详细的内容。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1475038798915.png" width="536"/>

如果需要选择其中的某个事件，可以通过下面的方法来访问：

```javascript
getEventListeners($('selector')).eventName[0].listener
```

这里的 eventName 表示某种事件类型，例如，获取 ID 为"firstName"的元素绑定的 click 事件：

```javascript
getEventListeners($('#firstName')).click[0].listener
```

## 监测事件
当你需要监视某个DOM触发的事件时，也可以用到控制台。例如下面这些方法：

* `monitorEvents($('selector'))` 会监测某个元素上绑定的所有事件，一旦该元素的某个事件被触发就会在控制台里显示出来。
* `monitorEvents($('selector'),'eventName')` 可以监听某个元素上绑定的具体事件。第二个参数代表事件类型的名称。例如`monitorEvents($('#firstName'),'click')`只监测 ID 为 firstName 的元素上的 click 事件。
* `monitorEvents($('selector'),['eventName1','eventName3',….])` 同上。可以同时检测具体指定的多个事件类型。
* `unmonitorEvents($('selector'))` 用来停止对某个元素的事件监测。


## 用计时器来获取某段代码块的运行时间
通过`console.time('labelName')`来设定一个计时器，其中的 labelName 是计时器的名称。通过`console.timeEnd('labelName')`方法来停止并输出某个计时器的时间。例如：

```javascript
console.time('myTime'); // 设定计时器开始 - myTime
console.timeEnd('mytime'); // 结束并输出计时时长 - myTime

// 输出: myTime:123.00 ms
```

再举一个通过计时器来计算代码块运行时间的例子：

```javascript
// 开始计时 - myTime
console.time('myTime');

for(var i=0; i < 100000; i++){
  2+4+5;
}

// 结束并输出计时时长 - myTime
console.timeEnd('mytime');

// 输出 - myTime:12345.00 ms
```

## 以表格的形式输出数组
假设我们有一个像下面这样的数组：

```javascript
var myArray=[{a:1,b:2,c:3},{a:1,b:2,c:3,d:4},{k:11,f:22},{a:1,b:2,c:3}]
```

要是直接在控制台里输入数组的名称，Chrome 会以文本的形式返回一个数组对象。但你完全可以通过`console.table(variableName)`方法来以表格的形式输出每个元素的值。例如下图：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1475039172089.png" width="300"/>


## 通过控制台方法来检查元素
可以直接在控制台里输入下面的方法来检查元素

* `inspect($('selector'))` 会检查所有匹配选择器的 DOM 元素，并返回所有选择器选择的 DOM 对象。例如`inspect($('#firstName'))`选择所有 ID 是 firstName 的元素，`inspect($('a')[3])`检查并返回页面上第四个 a 元素。

* `$0`, `$1`, `$2` 等等会返回你最近检查过的几个元素，例如`$0`会返回你最后检查的元素，`$1`则返回倒数第二个。


## 列出某个元素的所有属性
`dir($('selector'))`会返回匹配选择器的DOM元素的所有属性，可以展开输出的结果查看详细内容。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1475039323110.png" width="263"/>


## 获取最后计算结果的值
可以把控制台当作计算器使用。当在 Chrome 控制台里进行计算时，可以通过`$_`来获取最后的计算结果值。

```javascript
2+3+5
9 //- The Answer of the SUM is 9 

$_
9 // Gives the last Result

$_ * $_
81 // As the last Result was 9

Math.sqrt($_)
9 // As the last Result was 81

$_
9 // As the Last Result is 9
```

## 清空控制台输出
输入`clear()`方法，然后回车运行，即可清空控制台中的所有输出信息。


## 转摘
[天啦噜！原来Chrome自带的开发者工具还能这么用！](https://zhuanlan.zhihu.com/p/22665710)

