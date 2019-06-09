### 1. 从 var 到 let/const

ES5 中声明使用`var`，这些变量都是函数级作用域的，它们的作用域是包含它们的最内层的函数。使用`var`声明的变量会出现命名提升的问题，从而会引起一些让人容易混淆的问题

在 ES6 中还可以使用`let`和`const`来申明变量。这类变量是块级作用域的，它们的作用域是包含它们最近的块。`let`可以理解为块级作用域中的`var`。`const`与`let`类似，只是用`const`申明的变量其值是不可修改的。虽然`let`和`const`没有命名提升的问题，但是会出现 TDZ(暂时性死区)问题。

`let`和`const`更为严格，会抛出更多异常(比如，在变量作用域内访问还没有申明的变量)。块级作用域有助于保持代码片段的作用更局限。相比函数级作用域来说，块级作用域更为主流，它使 JavaScript 更接近于其它编程语言。

不能盲目地将即存代码中的`var`替换为`let`或`const`。一些建议是：

* 首选`const`。所有不会改变值的变量都可以使用它。
* 其它的使用`let`，用于值会被改变的变量。
* 避免使用`var`。

### 2. 从 IIFE 到块

ES5 中如果想限制变量 tmp 的作用范围仅在某一块代码中有效，就不得不使用一个叫 IIFE(Immediately-Invoked Function Expression，立即执行函数表达式) 的模式：

```javascript
(function () {  // IIFE 开始
    var tmp = ···;
    ···
}());  // IIFE 结束

console.log(tmp); // ReferenceError
```

ECMAScript 6 中可以简单地使用块和`let`申明(或`const`申明)：

```javascript
{  // 块起始
    let tmp = ···;
    ···
}  // 块结束

console.log(tmp); // ReferenceError
```

### 3. 从字符串拼接，到模板字面量

**String 插值**

ES5 中你想把在字符串中引用一些值，你需要将那些值和一些零碎的字符串连接起来。ES6 中你可以在模板字面量中使用字符串插值：

```JavaScript
function printCoord(x, y) {
    console.log(`(${x}, ${y})`);
}
```

**多行文本**
在 ES5 中，如果需要用到多行文本，需要使用如下的定义方式：

```JavaScript
var HTML5_SKELETON =
    '<!doctype html>\n' +
    '<html>\n' +
    '<head>\n' +
    '    <meta charset="UTF-8">\n' +
    '    <title></title>\n' +
    '</head>\n' +
    '<body>\n' +
    '</body>\n' +
    '</html>\n';

// 或者：
var HTML5_SKELETON = '\
 <!doctype html>\n\
 <html>\n\
 <head>\n\
 <meta charset="UTF-8">\n\
 <title></title>\n\
 </head>\n\
 <body>\n\
 </body>\n\
 </html>';
```

ES6 的模板字面量允许多行文本：

```JavaScript
const HTML5_SKELETON = `
 <!doctype html>
 <html>
 <head>
 <meta charset="UTF-8">
 <title></title>
 </head>
 <body>
 </body>
 </html>`;
```

### 4. 从函数表达式到箭头函数

当前 ES5 代码中，在使用了函数表达式的时候，你必须小心处理`this`。一般需要将当前代码域中的`this`赋值给一个变量，然后才能在回调函数中使用当前的`this`。

而在 ES6 中，使用箭头函数将不用担心`this`有问题：

```JavaScript
function UiComponent() {
    var button = document.getElementById('myButton');
    button.addEventListener('click', () => {
        console.log('CLICK');
        this.handleClick();
    });
}

UiComponent.prototype.handleClick = function () {
    // ···
};
```

对于一些简短的只需要返回某个表达式值的简短回调，用箭头函数特别方便。

```JavaScript
var arr = [1, 2, 3];

// ES5
var squares = arr.map(function (x) { return x * x });

// ES6
var squares = arr.map(x => x * x);
```

### 5. 处理多个返回值

有一些函数或者方便会通过数组或对象返回多个值。在 ES5 中，你需要创建一个临时变量来访问那些值。但在 ES6 中你可以使用解构。

**通过数组返回多个值**
`exec()`以伪数组对象的形式返回匹配到的各组。ES5 中需要一个临时变量(下面示例中的`matchOjb`)，即使你只关心配到的组：

```JavaScript
var matchObj = /^(\d\d\d\d)-(\d\d)-(\d\d)$/.exec('2999-12-31');
var year = matchObj[1];
var month = matchObj[2];
var day = matchObj[3];
```

ES6 的解构让代码变得简单：

```JavaScript
// 数组样板最开始空了一个位置，这是用来跳过第 0 个数组元素的
const [, year, month, day] =
    /^(\d\d\d\d)-(\d\d)-(\d\d)$/
    .exec('2999-12-31');
```

**通过对象返回多个值**
`Object.getOwnPropertyDescriptor()`方法返回一个属性描述对象，这个对象在它的属性中包含了多个值。即使你只关心对象的属性，在 ES5 中你也必须使用临时变量(下例中的`propDesc`)：

```JavaScript
var obj = { foo: 123 };

var propDesc = Object.getOwnPropertyDescriptor(obj, 'foo');
var writable = propDesc.writable;
var configurable = propDesc.configurable;

console.log(writable, configurable); // true true
```

在 ES6 中就可以使用解构：

```JavaScript
const obj = { foo: 123 };

const {writable, configurable} =
    Object.getOwnPropertyDescriptor(obj, 'foo');

console.log(writable, configurable); // true true
```

> `{writable, configurable}`是一个缩写。完整的是：
> 
> ```JavaScript
> { writable: writable, configurable: configurable }
> ```

### 6. 从 for 到 forEach() 再到 for-of

在 ES5 之前，可以使用`for`来遍历数组，在 ES5 中可以使用`forEach()`方法来遍历数组。`for`循环的优势在于可以中止，`forEach()`则更简洁。

ES6 带来的`for-of`循环综合了两者的优点：

```JavaScript
const arr = ['a', 'b', 'c'];
for (const ele of arr) {
    console.log(ele);
}
```

如果你既需要元素索引又需要元素的值，`for-of`可以通过数组的`entries()`方法，配合使用解构来办到：

```JavaScript
for (const [index, elem] of arr.entries()) {
    console.log(index + '. ' + elem);
}
```

### 7. 默认参数值

在 ES5 中指定参数的默认值需要这样：

```JavaScript
function foo (x, y) {
    x = x || 0;
    y = y || 0;
    // ···
}
```

ES6 有更漂亮的语法：

```JavaScript
function foo (x = 0, y = 0) {
    // ···
}
```

ES6 默认参数语法的好处在于，只有`undefined`会被替换成默认值，而在前面的 ES5 代码中，所有判`false`的值都会被替换成默认值。

### 8. 命名参数

JavaScript 中处理命名参数的常用方法是使用对象字面量(所谓的**选项对象模式**)：

```JavaScript
selectEntries({ start: 0, end: -1 });
```

这种方式带来了两个好处：代码可自解释，而且很容易做到省略某些参数。

ES5 中如下实现`selectEntries()`：

```JavaScript
function selectEntries(options) {
    var start = options.start || 0;
    var end = options.end || -1;
    var step = options.step || 1;
    // ···
}
```

ES6 中可以在参数定义中使用解构，代码简单多了：

```JavaScript
function selectEntries({ start = 0, end = -1, step = 1 }) {
    // ···
}
```

在 ES5 中要使`options`成为可选(非必须)的，你需要添加代码中的 A 行：

```JavaScript
function selectEntries(options) {
    options = options || {}; // (A)
    var start = options.start || 0;
    var end = options.end || -1;
    var step = options.step || 1;
    // ···
}
```

ES6 可以指定参数的默认值：

```JavaSccript
function selectEntries({ start=0, end=-1, step=1 } = {}) {
    // ···
}
```

### 9. 从 arguments 到剩余参数

如果你想在 ES5 中让函数(或方法)接受任意数量的参数，必须使用特殊变量`arguments`：

```JavaScript
function logAllArguments() {
    for (var i=0; i < arguments.length; i++) {
        console.log(arguments[i]);
    }
}
```

ES6 中则可以通过`...`运算符定义一个剩余参数(在下面示例中是`args`)：

```JavaScript
function logAllArguments(...args) {
    for (const arg of args) {
        console.log(arg);
    }
}
```

如果有一部分固定参数，剩余参数就更适用了，而在 ES5 中就会比较麻烦了：

```JavaScript
// ES6
function format(pattern, ...args) {
    // ···
}

// ES5
function format(pattern) {
    var args = [].slice.call(arguments, 1);
    ···
}
```

### 10. 从 apply() 到扩展运算符 (...)

ES5 中可以用`apply()`把数组作为参数使用。ES6 使用扩展运算符解决这个问题。

```JavaScript
// ES5
Math.max.apply(Math, [-1, 5, 11, 3])
// ES6
Math.max(...[-1, 5, 11, 3])

var arr1 = ['a', 'b'];
var arr2 = ['c', 'd'];

// ES5
arr1.push.apply(arr1, arr2);
// ES6
arr1.push(...arr2);
```

### 11. 从 concat() 到扩展运算符 (...)

扩展运算符也能将其内容转换为数组元素。也就是说，它可以代替数组方法`concat()`。

```JavaScript
// ES5 – concat()
var arr1 = ['a', 'b'];
var arr2 = ['c'];
var arr3 = ['d', 'e'];

console.log(arr1.concat(arr2, arr3));

// ES6
console.log([...arr1, ...arr2, ...arr3]);
```

### 12. 从对象字符量的函数表达式到方法定义

JavaScript 的方法是值为函数的属性。

ES5 对象字面量中，添加方法和添加其它属性一样，其属性值是函数表达式。

```JavaScript
var obj = {
    foo: function () {
        ···
    },
    bar: function () {
        this.foo();
    }, // trailing comma is legal in ES5
}
```

ES6 引入了**方法定义**，专门用于添加方法的语法：

```JavaScript
const obj = {
    foo() {
        ···
    },
    bar() {
        this.foo();
    },
}
```

### 13. 从构造器到类

ES6 引入的类语法比原来的构建函数更为方便。

**基类**
ES5 中直接实现一个构造函数：

```JavaScript
function Person(name) {
    this.name = name;
}
Person.prototype.describe = function () {
    return 'Person called '+this.name;
};
```

ES6 的类语法提供了比构造函数稍微方便一些的语法：

```JavaScript
class Person {
    constructor(name) {
        this.name = name;
    }
    describe() {
        return 'Person called '+this.name;
    }
}
```

> 注意：简化的方法定义语法 —— 不再需要`function`关键字。
> 注意：类的各个部分之间没有逗号。

**派生类**
ES5 中实现子类是件麻烦的事情，尤其是引用父类构造函数和父类属性的时候。下面使用经典方法创建`Person`的子类构造函数`Employee`：

```JavaScript
function Employee(name, title) {
    Person.call(this, name); // super(name)
    this.title = title;
}
Employee.prototype = Object.create(Person.prototype);
Employee.prototype.constructor = Employee;
Employee.prototype.describe = function () {
    return Person.prototype.describe.call(this) // super.describe()
           + ' (' + this.title + ')';
};
```

ES6 内置支持子类，只需要使用`extends`子句：

```JavaScript
class Employee extends Person {
    constructor(name, title) {
        super(name);
        this.title = title;
    }
    describe() {
        return super.describe() + ' (' + this.title + ')';
    }
}
```

### 14. 从自定义错误构造函数到 Error 的子类

ES5 不能实现内置异常构造器 Error 的子类。下面的代码展示了如何让`MyError`实现一些重要的功能，比如栈跟踪：

```JavaScript
function MyError() {
    // Use Error as a function
    var superInstance = Error.apply(null, arguments);
    copyOwnPropertiesFrom(this, superInstance);
}
MyError.prototype = Object.create(Error.prototype);
MyError.prototype.constructor = MyError;

function copyOwnPropertiesFrom(target, source) {
    Object.getOwnPropertyNames(source)
    .forEach(function(propKey) {
        var desc = Object.getOwnPropertyDescriptor(source, propKey);
        Object.defineProperty(target, propKey, desc);
    });
    return target;
};
```

ES6 中所有内置构造器都可以被继承，下面的代码展示了在 ES5 只能模拟的东西：

```JavaScript
class MyError extends Error {
}
```

### 15. 从对象到 Map

为了处理字符串向其它类型值映射(一种数据结构)，将对象当作映射表一直都是 JavaScript 中的临时解决办法。最安全的方法是创建一个原型是 null 的对象。然后你还得确保永远不会有一个键是 `__proto__`，因为那个属性名称在很多 JavaScript 引擎中有着特殊的意义。

下面的 ES5 代码含有函数`countWords`，它把名为`dict`的对象作为映射表：

```JavaScript
var dict = Object.create(null);
function countWords (word) {
    var escapedWord = escapeKey(word);
    if (escapedWord in dict) {
        dict[escapedWord]++;
    } else {
        dict[escapedWord] = 1;
    }
}

function escapeKey (key) {
    if (key.indexOf('__proto__') === 0) {
        return key+'%';
    } else {
        return key;
    }
}
```

ES6 提供了内置数据结构 Map，使它的时候不需要对键进行转义。不过它有一个缺点是不太方便使用自增运算。

```JavaScript
const map = new Map();
function countWords(word) {
    const count = map.get(word) || 0;
    map.set(word, count + 1);
}
```

Map 带来的另一个好处是你可以使用任意类型的值，而不一定是字符串值，来作为键。


### 16. 新的字符串方法

#### 16.1 从 indexOf 到 startsWith

```JavaScript
 // ES5
if (str.indexOf('x') === 0) {}
// ES6
if (str.startsWith('x')) {}
```

#### 16.2 从 indexOf 到 endsWith

```JavaScript
// ES5
function endsWith (str, suffix) {
  var index = str.indexOf(suffix);
  return index >= 0 && index === str.length-suffix.length;
}

// ES6
str.endsWith(suffix);
```

#### 16.3 从 indexOf 到 includes

```JavaScript
// ES5
if (str.indexOf('x') >= 0) {}
// ES6
if (str.includes('x')) {}
```

#### 16.4 从 join 到 repeat (ES5 中重复字符串的方法更需要技巧)

```JavaScript
// ES5
new Array(3+1).join('#')
// ES6
'#'.repeat(3)
```

### 17. 新的数组方法

#### 17.1 从 indexOf 到 findIndex

后者可用于查找 `NaN`，这是前者无法做到的：

```JavaScript
const arr = ['a', NaN];

arr.indexOf(NaN); // -1
arr.findIndex(x => Number.isNaN(x)); // 1
```

> 顺便说一下，新的`Number.isNaN()`提供了更安全的方法来检测`NaN`(因为它不会将非数值类型强制转换为数值类型)：
> 
> ```
> > isNaN('abc')
> true
> > Number.isNaN('abc')
> false
> ```

#### 17.2 从 slice() 到 from() 或者扩展运算符

ES5 中使用`Array.prototype.slice()`把伪数组转换为数组。ES6 中可以使用`Array.from()`来做这个事情：

```JavaScript
var arr1 = Array.prototype.slice.call(arguments); // ES5
const arr2 = Array.from(arguments); // ES6
```

如果某个值是可枚举的(比如当前用伪数组表示的所有 DOM 数结构结构)，你可以使用扩展运算符(`...`) 将其转换为数组：


```JavaScript
const arr1 = [...'abc'];
    // ['a', 'b', 'c']
const arr2 = [...new Set().add('a').add('b')];
    // ['a', 'b']
```

#### 17.3 从 apply() 到 fill()

ES5 中可以通过一定的技巧使用`apply() 来创建任意长度的数组，其所有元素都是`undefined`：

```JavaScript
// Same as Array(undefined, undefined)
var arr1 = Array.apply(null, new Array(2));
// [undefined, undefined]
```

ES6 带来的`fill()`提供了更简单的方法：

```JavaScript
const arr2 = new Array(2).fill(undefined);
// [undefined, undefined]
```

如果想在创建数组的时候填入其它值，`fill()`则更实用：

```JavaScript
// ES5
var arr3 = Array.apply(null, new Array(2))
    .map(function (x) { return 'x' });
    // ['x', 'x']

// ES6
const arr4 = new Array(2).fill('x');
    // ['x', 'x']
```

`fill()`会把所有数组元素替换为给定的值。

### 18. 新的数值方法

`Number.parseInt`、`Number.parseFloat`与`parseInt`、`parseFloat`功能一致，在 ES6 中推荐使用`Number.`的方式进行调用，这么做的目的是为了让代码的使用方式尽可能减少全局性方法，使用得语言逐步模块化。

#### 18.1 是否是整数

```JavaScript
Number.isInteger(21)   // true
Number.isInteger(1.11) // false
```

#### 18.2 是否是 NaN

```JavaScript
Number.isNaN(NaN) // true
Number.isNaN(1)   // false
```
### 19. 从 CommonJS 模块到 ES6 模块

ES6 内置了对模块的支持，可惜目前还没有哪个 JavaScript 引擎原生支持这个特性。但像 browserify、webpack 和 jspm 这样的工具可以让你使用 ES6 语法来创建模块，让你的代码提前用上新语法。

#### 多项导出

**CommonJS 中的多项导出**
CommonJS 中像下面这样导出多个实例：

```JavaScript
//------ lib.js ------
var sqrt = Math.sqrt;
function square(x) {
    return x * x;
}
function diag(x, y) {
    return sqrt(square(x) + square(y));
}
module.exports = {
    sqrt: sqrt,
    square: square,
    diag: diag,
};

//------ main1.js ------
var square = require('lib').square;
var diag = require('lib').diag;

console.log(square(11)); // 121
console.log(diag(4, 3)); // 5
```

你也可以把整个模块作为一个对象导入，然后再通过它访问`square`和`diag`：

```JavaScript
//------ main2.js ------
var lib = require('lib');
console.log(lib.square(11)); // 121
console.log(lib.diag(4, 3)); // 5
```

**ES6 的多项导出**
ES6 中的多项导出被称为*命名的导出*，操作起来像这样：

```JavaScript
//------ lib.js ------
export const sqrt = Math.sqrt;
export function square(x) {
    return x * x;
}
export function diag(x, y) {
    return sqrt(square(x) + square(y));
}

//------ main1.js ------
import { square, diag } from 'lib';
console.log(square(11)); // 121
console.log(diag(4, 3)); // 5
```

将模块导入为对象的语法如下所示(A行)：

```JavaScript
//------ main2.js ------
import * as lib from 'lib'; // (A)
console.log(lib.square(11)); // 121
console.log(lib.diag(4, 3)); // 5
```

#### 单项导出

**CommonJS 的单项导出**
Node.js 扩展了 CommonJS 让你可以通过`module.exports`导出单个值：

```JavaScript
//------ myFunc.js ------
module.exports = function () { ··· };

//------ main1.js ------
var myFunc = require('myFunc');
myFunc();
```

**ES6 的单项导出**
ES6 中使用*默认导出*来做同样的事情(通过`export default`申明)：

```JavaScript
//------ myFunc.js ------
export default function () { ··· } // no semicolon!

//------ main1.js ------
import myFunc from 'myFunc';
myFunc();
```

### 转摘
[ES6 核心特性](http://www.zcfy.cc/article/core-es6-features-2267.html)


