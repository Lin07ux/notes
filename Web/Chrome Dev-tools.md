## 快捷键

* `Cmd + Shift + C` 在审查模式下打开开发者工具或是在开发者工具已经打开的情况下开启查阅选项。
* `Cmd + o`  在 Source 选项卡中可以搜索文件。
* `Cmd + Shift + o`  在 Source 选项卡中搜索文件中的对应函数/特定选择器的一个选择框。


## console

`Console`提供了多个方法来方便进行调试和格式化信息的输出。

### assert 断言

`console.assert()`方法仅仅只当它的第一个参数为 false 时才显示一个错误信息字符串（它的第二个参数）。

在下面的代码中，如果在列表中的子节点的数量超过 500，将会在控制台中引起错误信息。

```JavaScript
console.assert(list.childNodes.length < 500, "Node count is > 500");
```

### group、groupEnd 分组

`console.group()`命令通过一个字符串的参数来给你的组命名。控制台将会把所有所有的输出信息组合到一块。

要结束分组，你只需要调用`console.groupEnd()`即可。

示例代码：

```JavaScript
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

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494903900862.png)

### table 表格输出

`console.table()`方法提供一个简单的方式来查看相似数据对象。这将给一个数据提供属性并且创建一个头。行数据将会从每一个索引属性值中获取。

示例代码：

```JavaScript
console.table([{a:1, b:2, c:3}, {a:"foo", b:false, c:undefined}]);
console.table([[1,2,3], [2,3,4]]);
```

结果如下图所示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494904007384.png)

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

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494904075198.png)

### time、timeEnd 统计时间

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

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494904332334.png)

当`console.time()`方法正在执行期间，将会在 TimeLine 选项卡中生成一个时间轴记录并为其做出注解。这对于追踪应用的使用以及其来源非常有用。

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494904417466.png)

### log 输出信息

最常用的是`console.log()`方法，该方法一般用来在调试的时候输出一些辅助信息，还能够为输出的信息设置 CSS 样式。

更改样式的示例如下：

```JavaScript
console.log("%cThis will be formatted with large, blue text", "color: blue; font-size: x-large");
```

结果如下图所示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1494904197280.png)

### info 提示类信息

### warn 警示信息

### error 错误信息

### count 统计代码执行次数

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

### dir 输出 DOM 对象

`console.dir(DOM)`将 DOM 节点以 JavaScript 对象的形式输出到控制台。与在元素审查时看到的结构相同。

### profile/timeLine 性能分析

分析 CPU 使用相关的信息，可以通过开发工具中的相关选项卡进行操作。

### trace

堆栈跟踪相关的调试可以使用`console.trace`。这个同样可以通过 UI 界面完成。

当代码被打断点后，可以在 Call Stack 面板中查看相关堆栈信息。


## 相关命令

### $_ 返回最近一次表达式执行的结果

功能跟按向上的方向键再回车是一样的，但它可以做为一个变量使用在你接下来的表达式中。

```JavaScript
2+2   // 4
$_+1  // 5
```

### $0-4 

`$0 ~ $4`代表了 5 个最近在调试工具中选择过的 DOM 节点。

在页面右击选择审查元素，然后在弹出来的 DOM 结点树上面随便点选，这些被点过的节点会被记录下来，而`$0`会返回最近一次点选的 DOM 结点，以此类推，`$1`返回的是上上次点选的 DOM 节点，
最多保存了 5 个，如果不够 5 个，则返回 undefined。

### $(selector)

返回的是满足选择条件的首个 DOM 元素。Chrome 控制台中原生支持类 jQuery 的选择器。

其实`$(selector)`是原生 JavaScript `document.querySelector()`的封装。

### $$(selector)

返回的是所有满足选择条件的元素的一个集合，是对`document.querySelectorAll()`的封装。

### copy

`copy(document.body)`通过此命令可以将在控制台获取到的内容复制到剪贴板。

### keys()、values()

前者返回传入对象所有属性名组成的数据，后者返回所有属性值组成的数组。

```JavaScript
var tboy = {name: 'wayou', gender: 'unknow', hobby: 'opposite to the gender'};
keys(tboy);
values(tboy);

// 输出
// ["name", "gender", "hobby"]
// ["wayou", "unknow", "opposite to the gender"]
```

### monitor、unmonitor

`monitor(function)`接收一个函数名作为参数，比如`monitor(a)`，每次 a 被执行了，都会在控制台输出一条信息，里面包含了函数的名称`a`及执行时所传入的参数。

而`unmonitor(function)`便是用来停止这一监听。

### debug、undebug

这两个方法接收一个函数名作为参数。当该函数执行时自动断下来以供调试，类似于在该函数的入口处打了个断点，可以通过debugger 来做到，同时也可以通过在 Chrome 开发者工具里找到相应源码然后手动打断点。

而`undebug`则是解除该断点。

