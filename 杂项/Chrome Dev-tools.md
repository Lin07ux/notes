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

### group 分组

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

### time 统计时间

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

