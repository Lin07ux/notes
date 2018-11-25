## 一、快捷键

* `Cmd + Shift + C` 在审查模式下打开开发者工具或是在开发者工具已经打开的情况下开启查阅选项。
* `Cmd + o`  在 Source 选项卡中可以搜索文件。
* `Cmd + Shift + o`  在 Source 选项卡中搜索文件中的对应函数/特定选择器的一个选择框。

## 二、Console

`Console`对象提供了多个方法来方便进行调试和格式化信息的输出。

### 1. assert 断言

`console.assert()`方法只有当它的第一个参数为 false 时才显示一个错误信息字符串（它的第二个参数）。

在下面的代码中，如果在列表中的子节点的数量超过 500，将会在控制台中引起错误信息。

```JavaScript
console.assert(list.childNodes.length < 500, "Node count is > 500");
```

### 2. group、groupEnd 分组

`console.group()`命令通过一个字符串的参数来给你的组命名。控制台将会把所有所有的输出信息组合到一块。要结束分组，只需要调用`console.groupEnd()`即可。

示例代码：

```js
var user = "jsmith", authenticated = true, authorized = true;

// Top-level group
console.group("Authenticating user '%s'", user);

if (authenticated) {
    console.log("User '%s' was authenticated", user);

    // Start nested group
    console.group("Authorizing user '%s'", user);
    if (authorized) {
        console.log("User '%s' was authorized.", user);
    }

    // End nested group
    console.groupEnd();
}

// End top-level group
console.groupEnd();
console.log("A group-less log trace.");
```

结果如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494903900862.png)

### 3. table 表格输出

`console.table()`方法提供一个简单的方式来查看相似数据对象。这将给一个数据提供属性并且创建一个头。行数据将会从每一个索引属性值中获取。

示例代码：

```JavaScript
console.table([{a:1, b:2, c:3}, {a:"foo", b:false, c:undefined}]);
console.table([[1,2,3], [2,3,4]]);
```

结果如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494904007384.png)

`console.table()`中的第二个参数是可选项。你可以定义任何你想显示的属性字符串数组。

示例代码：

```JavaScript
function Person(firstName, lastName, age) {
  this.firstName = firstName;
  this.lastName = lastName;
  this.age = age;
}
var family = {};
family.mother = new Person("Susan", "Doyle", 32);
family.father = new Person("John", "Doyle", 33);
family.daughter = new Person("Lily", "Doyle", 5);
family.son = new Person("Mike", "Doyle", 8);
console.table(family, ["firstName", "lastName", "age"]);
```

结果如下所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494904075198.png)

### 4. time、timeEnd 统计时间

这两个方法结合，可以考量一段代码执行的耗时情况。

通过`console.time()`方法可以启动一个计时器。你必须输入一个字符串来识别时间的标记。当你要结束计算的时候，使用`console.timeEnd()`方法，并且传递一个相同的字符串给构造器。控制台会在`console.timeEnd()`方法结束的时候，记录下标签以及时间的花销。

示例代码：

```JavaScript
console.time("Array initialize");

var array= new Array(1000000);
for (var i = array.length - 1; i >= 0; i--) {
   array[i] = new Object();
};

console.timeEnd("Array initialize");
```

结果如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494904332334.png)

当`console.time()`方法正在执行期间，将会在 TimeLine 选项卡中生成一个时间轴记录并为其做出注解。这对于追踪应用的使用以及其来源非常有用。

![](http://cnd.qiniu.lin07ux.cn/markdown/1494904417466.png)

### 5. log 输出信息

最常用的是`console.log()`方法，该方法一般用来在调试的时候输出一些辅助信息，还能够为输出的信息设置 CSS 样式。

更改样式的示例如下：

```JavaScript
console.log("%cThis will be formatted with large, blue text", "color: blue; font-size: x-large");
```

结果如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1494904197280.png)

### 6. info 提示类信息

### 7. warn 警示信息

### 8. error 错误信息

### 9. count 统计代码执行次数

当你想了解某段代码执行了多少次的时候，可以使用这个方法很方便的输出计数。该方法也可以不传入参数。

```JavaScript
function foo(){
	console.count("foo 被执行的次数");
}

foo();
foo();
foo();
```

输出结果：

```
foo 被执行的次数:1
foo 被执行的次数:2
foo 被执行的次数:3
```

### 10. dir 输出 DOM 对象

`console.dir(DOM)`将 DOM 节点以 JavaScript 对象的形式输出到控制台。与在元素审查时看到的结构相同。

### 11. profile/timeLine 性能分析

分析 CPU 使用相关的信息，可以通过开发工具中的相关选项卡进行操作。

### 12. trace

堆栈跟踪相关的调试可以使用`console.trace`。这个同样可以通过 UI 界面完成。

当代码被打断点后，可以在 Call Stack 面板中查看相关堆栈信息。


## 三、选取 DOM 元素

### 1. `$(selector)`

返回的是满足选择条件的首个 DOM 元素。Chrome 控制台中原生支持类 jQuery 的选择器。其实`$(selector)`是原生 JavaScript `document.querySelector()`的封装。

### 2. `$$(selector)`

返回的是所有满足选择条件的元素的一个集合，是对`document.querySelectorAll()`的封装。使用方式类似 jQuery。

例如：`$$('.className')`会返回给你所有包含 className 类属性的元素，之后可以通过`$$('className')[0]`和`$$('className')[1]`来访问其中的某个元素。

### 3. `$0-4`

`$0 ~ $4`代表了 5 个最近在调试工具中选择过的 DOM 节点。

在页面右击选择审查元素，然后在弹出来的 DOM 结点树上面随便点选，这些被点过的节点会被记录下来，而`$0`会返回最近一次点选的 DOM 结点，以此类推，`$1`返回的是上上次点选的 DOM 节点，
最多保存了 5 个，如果不够 5 个，则返回 undefined。

## 四、相关命令

### 1. $_ 返回最近一次表达式执行的结果

功能跟按向上的方向键再回车是一样的，但它可以做为一个变量使用在你接下来的表达式中。

```JavaScript
2+2   // 4
$_+1  // 5
```

### 2. copy

`copy(document.body)`通过此命令可以将在控制台获取到的内容复制到剪贴板。

### 3. keys()、values()

前者返回传入对象所有属性名组成的数据，后者返回所有属性值组成的数组。

```JavaScript
var tboy = {name: 'wayou', gender: 'unknow', hobby: 'opposite to the gender'};
keys(tboy);
values(tboy);

// 输出
// ["name", "gender", "hobby"]
// ["wayou", "unknow", "opposite to the gender"]
```

### 4. monitor、unmonitor

`monitor(function)`接收一个函数名作为参数，比如`monitor(a)`，每次 a 被执行了，都会在控制台输出一条信息，里面包含了函数的名称`a`及执行时所传入的参数。

而`unmonitor(function)`便是用来停止这一监听。

### 5. debug、undebug

这两个方法接收一个函数名作为参数。当该函数执行时自动断下来以供调试，类似于在该函数的入口处打了个断点，可以通过debugger 来做到，同时也可以通过在 Chrome 开发者工具里找到相应源码然后手动打断点。

而`undebug`则是解除该断点。

## 五、其他

### 1. 让 Chrome 变成所见即所得的编辑器

如果想能够在网页上直接编辑相关的文字来查看效果，可以使用 HTML5 添加的一个属性使得网页能够直接随意编辑。

打开 Chrome 的开发者控制台，输入：

```javascript
document.body.contentEditable = true;
```

然后我们就能够直接编辑网页上的内容了。

### 2. 获取某个 DOM 元素绑定的事件

在调试的时候，如果需要知道某个元素上面绑定了什么触发事件，可以使用`getEventListeners($('selector'))`方法以数组对象的格式返回某个元素绑定的所有事件。你可以在控制台里展开对象查看详细的内容。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1475038798915.png" width="536"/>

如果需要选择其中的某个事件，可以通过下面的方法来访问：

```javascript
getEventListeners($('selector')).eventName[0].listener
```

这里的 eventName 表示某种事件类型，例如，获取 ID 为"firstName"的元素绑定的 click 事件：

```javascript
getEventListeners($('#firstName')).click[0].listener
```

### 3. 监测事件

当你需要监视某个DOM触发的事件时，也可以用到控制台。例如下面这些方法：

* `monitorEvents($('selector'))` 会监测某个元素上绑定的所有事件，一旦该元素的某个事件被触发就会在控制台里显示出来。
* `monitorEvents($('selector'),'eventName')` 可以监听某个元素上绑定的具体事件。第二个参数代表事件类型的名称。例如`monitorEvents($('#firstName'),'click')`只监测 ID 为 firstName 的元素上的 click 事件。
* `monitorEvents($('selector'),['eventName1','eventName3',….])` 同上。可以同时检测具体指定的多个事件类型。
* `unmonitorEvents($('selector'))` 用来停止对某个元素的事件监测。


### 4. 用计时器来获取某段代码块的运行时间

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

### 5. 以表格的形式输出数组

假设我们有一个像下面这样的数组：

```javascript
var myArray=[{a:1,b:2,c:3},{a:1,b:2,c:3,d:4},{k:11,f:22},{a:1,b:2,c:3}]
```

要是直接在控制台里输入数组的名称，Chrome 会以文本的形式返回一个数组对象。但你完全可以通过`console.table(variableName)`方法来以表格的形式输出每个元素的值。例如下图：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1475039172089.png" width="300"/>


### 6. 通过控制台方法来检查元素

可以直接在控制台里输入下面的方法来检查元素

* `inspect($('selector'))` 会检查所有匹配选择器的 DOM 元素，并返回所有选择器选择的 DOM 对象。例如`inspect($('#firstName'))`选择所有 ID 是 firstName 的元素，`inspect($('a')[3])`检查并返回页面上第四个 a 元素。

* `$0`, `$1`, `$2` 等等会返回你最近检查过的几个元素，例如`$0`会返回你最后检查的元素，`$1`则返回倒数第二个。


### 7. 列出某个元素的所有属性

`dir($('selector'))`会返回匹配选择器的DOM元素的所有属性，可以展开输出的结果查看详细内容。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1475039323110.png" width="263"/>

### 9. 清空控制台输出
输入`clear()`方法，然后回车运行，即可清空控制台中的所有输出信息。


## 转摘

[天啦噜！原来Chrome自带的开发者工具还能这么用！](https://zhuanlan.zhihu.com/p/22665710)

