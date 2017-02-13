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

把值存入变量的过程称为赋值。在 JavaScript 中赋值是使用`=`来表示的：

```JavaScript
mood = 'happy';
age = 33;
```

可以看到，赋值操作时，变量名在左侧，值在右侧。

> 在 JavaScript 中，可以直接对变量进行赋值而不需声明，但是建议提前进行声明(使用`var`)，否则可能会造成一些困惑。
> 
> ```JavaScript
> var mood = 'happy';
> var age = 33;
> 
> // 或同时声明
> var mood = 'happy', age = 33;
> ```

变量名的要求：

* 不能是关键词，如`var`、`for`等；
* 需要是字母和数字，以及`$`、`_`这些符号；
* 不能以数字开头；
* 区分大小写。

JavaScript 中，变量的值可以分为多种类型：Boolean、Number、String、undefined、null、Object 等。其中前五种称为基本类型，其他的都是对象类型。

不过 JavaScript 是弱语言类型，可以随时为变量赋值任意类型的值。

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

### String
字符串由零个或多个字符构成；字符包括但不限于字母、数字、标点符号和空格；字符串必须包在引号(单引号或双引号)中，但需要保证字符串前后的引号相同。

还可以在字符串中使用转义字符。转义字符由`\`开头。

### Number
JavaScript 支持整数、小数(浮点数)、正数、负数，以及一个特殊的数值`NaN`。

> JavaScript 中的小数计算并不是完全正确的，只是在一定精度内可以接受。比如`0.1 + 0.2 !== 0.3`。

### Object
对象和前面的类型不同，是使用一个名称表示一组值，对象的每个值都是对象的一个属性。

对象的属性可以使用`对象名.属性名`或`对象名[属性名]`的方式进行表示。属性值可以是任意的 JavaScript 值，包括对象自己。

> 对象的属性名必须是一个字符串或数值。当属性名为数值的时候，必须使用方括号方式访问，而不能使用点号方式访问。

对象中不仅可以存放值，还能存放函数。对象中的函数称为方法，调用方式和对象的属性调用方式相同，都是用点号访问：

```JavaScript
Object.property;
Object.method();
```

JavaScript 和 JavaScript 的宿主(如浏览器)中内建了很多的对象，不同的内建对象还有很多的方法和属性。但是所有的对象的最终原型都是 null。

### Array
Array 是一种特殊类型的对象，表示数组。不过 Array 中主要的属性名称是数字(其他的一些属性也可以是字符串)。

和其他大多数语言一样，Array 的下标也是从 0 开始的。Array 中有一个特殊的属性`length`表示数组中包含的数据的个数。

> 还有一种类似数组的对象，也有`length`属性，可以和数组一样进行遍历，但是并不具备完整的数组特性，称为类数组对象，可以使用`[].prototype.slice.apply(varLikeArray)`的方式转成数组。


## 操作

要用 JavaScript 做一些有用的工作，还需要能够进行计算和处理数据，也就是需要完成一些操作(operation)。

### 算数运算符

* `+` 加法，可以进行数值和的计算，或者字符串的拼接(两个操作变量中有一个为字符串则是进行字符串的拼接)。
* `-` 减法，对数值进行差计算。
* `*` 乘法，对数值进行积计算。
* `/` 除法，对数值进行商计算。
* `--` 自减，将操作数进行减 1 操作。
* `++` 自增，将操作数进行加 1 操作。
* `+=` 加法操作后赋值。
* `-=` 减法操作后赋值。
* `*=` 乘法操作后赋值。
* `/=` 除法操作后赋值。

> `--`和`++`两个操作对操作数的影响相当于`-= 1`和`+= 1`，但是这两个符号在变量的前面和后面时，可能会引起不同的结果：自增/自减符号在变量前面的时候，表示先对操作数进行运算，然后再进行其他的操作；在变量后面的时候，表示先对操作数进行其他的操作，然后进行运算。

### 比较操作符

* `>` 大于
* `>=` 大于或等于(不小于)
* `<` 小于
* `<=` 小于或等于(不大于)
* `==` 等于(非严格想等，可以进行隐式转换变量值)
* `===` 恒等(严格想等，除值想等，变量类型还要相同)
* `!=` 不等于(非严格，可以进行隐式转换变量值)
* `!==` 恒不等(数值和类型任意一个不相同)

### 逻辑操作符

* `&&` 逻辑与(前后的条件均需要满足)
* `||` 逻辑或(前后的添加有至少有一个满足)
* `!` 逻辑非(条件取反)

> 逻辑与和逻辑或会发生短路。也就是说：对于逻辑与，如果前面的条件判断为假，则后面的条件就不会进行判断了；对于逻辑或，如果前面的条件判断为真，则后面的条件就不会进行判断了。

### delete 操作
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


## 条件语句

语法如下：

```JavaScript
if (condition1) {
    statements1;
} else if (condition2) {
    statements2;
} else {
    statements3;
}
```

## 循环

循环是为了在满足条件的情况下，重复执行一段代码块。使用循环的时候，需要设定好循环的退出条件，否则将会陷入死循环，造成程序假死，无法继续后面的运行。

### while

语法如下：

```JavaScript
while (condition) {
    statements;
}
```

### do...while

语法如下：

```JavaScript
do {
    statements;
} while (condition)
```

> 该循环和`while`类似，只是其条件语句后置，表示至少执行其中的代码块一次，而`while`循环则有可能会一次也不执行。 

### for

for 循环是 while 循环的一个变体写法：

```JavaScript
for (inital condition; test condition; alter condition) {
    statements;
}
```

## 函数

如果需要多次使用同一段代码，可以将其封装起来，称为一个函数。

*函数(function)* 就是一组允许在你的代码里随时调用的语句。每个函数实际上是一段短小的脚本。

```JavaScript
function ([args]) {
    statements;
    
    [return values];
}
```

函数可以传入参数，也可以不传入。即便在定义的时候，写了参数，在调用的时候也可以不传入；在使用的时候还能够传入更多数量的参数。

函数可以有返回值，也可以不写`return`语句。如果不写，则默认函数会返回`undefined` 值。

函数不仅仅能够被调用以执行一段代码，还能够将其当做一种数据来使用，赋值给其他变量。

### 变量的作用域

由于函数的存在，所以需要对变量的作用范围进行区别，以免出现混乱和干扰。这就是变量的作用域。

变量的作用域分为两种：*全局作用域*、*局部作用域*。其中，全局作用域的变量能够在代码中的任意位置被调用，而局部作用域的变量则只能在其作用域中被调用。


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


### touch事件
`touchstart`、`touchmove`、`touchend`。

直接使用`event.clientX`是不起作用的，要使用`event.changedTouches[0].clientX`才好；如果是 jQuery 的 event 对象，使用`event.originalEvent.changedTouches[0].clientX`。


