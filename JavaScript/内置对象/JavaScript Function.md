JavaScript 中函数是一等公民：可以作为一个逻辑整体执行，也可以作为变量赋值，还可以作为参数进行传递，也能够作为函数的返回值。这一切的根源在于：函数实际上是对象。

每个函数都是 Function 类型的实例，与其他引用类型一样具有属性和方法。同时，函数也是引用类型，所以函数名也只是一个指向函数对象的指针，不会与某个函数绑定，自然也就是无法重载了——每次重新定义函数名只是将函数名指向另一个函数对象而已。

## 一、定义函数

定义函数的方式有三种：

* 函数声明
* 函数表达式
* Function 构造函数

一般使用前两种方式即可，第三种方式不建议使用。

### 1.1 函数声明和函数表达式

函数声明和函数表达式定义如下：

```JavaScript
// 函数声明
function sum (num1, num2) {
    return num1 + num2
}

// 函数表达式，注意最后面的分号
var sum2 = function (num1, num2) {
    return num1 + num2
};
```

这两种方式基本相同，除了什么时候可以通过函数名访问函数：

* 解析器会率先读取函数声明，并使其在执行任何代码之前可用(可以访问)，也就是函数声明提升(function declaration hoisting)。
* 函数表达式必须要等到解析器执行到它所在的代码行才会被解释执行。虽然有变量提升，但提升的仅仅是变量名(函数名)，具体的值(函数)还是要等到解释器执行到后才生效。

### 1.2 构造函数

Function 作为一个类提供了构造函数方式创建函数。可以接收任意数量的参数，但最后一个参数始终被看成是函数体，而前面的参数则枚举了新函数的参数：

```JavaScript
var sum = new Function('num1', 'num2', 'return num1 + num2');
```

从技术角度讲，这是一个函数表达式。但是不推荐使用这种方式定义函数，因为这种语法会导致解析两次代码：第一次是解析常规 ECMAScript 代码，第二次是解析传入构造函数中的字符串，从而会影响性能。

## 二、属性

函数作为一种对象，可以附带一些属性。同时，JavaScript 解释器会在函数内部会自动注入一些特殊变量，以供函数内部代码调用，这些属性被称为**内部属性**。

### 2.1 length

函数的`length`属性表示函数希望接收的命名参数的个数。

比如：

```JavaScript
function sayName (name) {
    return name;
}

function sum (num1, num2) {
    return num1 + num2;
}

function sayHi () {
    return 'Hi';
}

sayHi.length;   // 0
sayName.length; // 1
sum.length;     // 2
```

### 2.2 prototype

对于 ECMAScript 中的引用类型而言，`prototype`属性是保存它们所有示例方法的真正所在。在创建自定义类型以及实现继承的时候，`prototype`属性的作用是极为重要的。通过该属性，可以将每个引用对象最终和 Object 及 null 联系起来。可以参考 [JavaScript prototype](../杂项/JavaScript prototype.md)

在 ECMAScript 5 中，`prototype`属性是不可枚举的，因此`for-in`无法发现该属性。

### 2.3 内部属性 arguments

这是函数内部的一个类数组对象，包含了调用函数时传入的所有参数，主要用途是保存函数参数。

因为 JavaScript 函数调用时可以接收超过定义数量的参数，这些多余的参数都可以通过 arguments 对象获取到。

另外，arguments 对象上还有一个`callee`属性，该属性是一个指针，指向当前函数本身。

比如下面的阶乘函数：

```JavaScript
function factorial (num) {
    if (num <= 1) {
        return 1;
    } else {
        return num * factorial(num - 1)
    }
}
```

该函数利用递归算法，调用了自身，在为其定义了名称，且名称不变的情况下，是没有问题的。但这就导致函数逻辑和其名称紧紧耦合在一起了，不方便以后的修改。可以使用`arugments.callee`来替换名称，改成如下方式：

```JavaScript
function factorial (num) {
    if (num <= 1) {
        return 1;
    } else {
        return num * arguments.callee(num - 1)
    }
}
```

这样，无论引用函数时使用的是什么名字，都可以保证正常完成递归调用：

```JavaScript
var trueFactorial = factorial;

factorial = function () {
    return 0;
}

trueFactorial(5); // 120
factorial(5);     // 0
```

### 2.4 内部属性 this

函数内部的`this`变量应用的是函数执行的环境上下文对象。JavaScript 中的`this`是复杂多变的，具体可以参考 [JavaScript 图解 this 的指向](../知识点/JavaScript 图解 this 的指向.md)。

比如：

```JavaScript
window.color = 'red';
var o = { color: 'blue' };

function sayColor() {
    return this.color;
}

sayColor();  // "red"

o.saycolor = sayColor;
o.sayColor(); // "blue"
```

### 2.5 内部属性 caller

ECMAScript 5 为函数对象定义了一个新的属性：`caller`。这个属性中保存着**调用当前函数的函数**的引用。如果是在全局作用域中调用当前函数，那么其值为 null。

比如：

```JavaScript
function outer () {
    inner();
}

function inner () {
    return inner.caller;
    // 或者
    // return arguments.callee.caller;
}

outer();
```

上面的代码会返回`outer()`函数的源代码。

> 当函数在严格模式下运行时，访问`arguments.callee`会导致错误。

> ECMAScript 5 还定义了`arguments.caller`属性，但在严格模式下访问它也会导致错误，而在非严格模式下，这个属性值总是 undefined。

> 严格模式还有一个限制：不能为函数的`caller`属性赋值，否则会导致错误。

## 三、函数方法

### 3.1 call()/apply()

这两个方法的用途都是在特定的作用域中调用函数，实际上等同于改变函数内部的`this`对象的指向。它们唯一的不同在于接收参数的形式不同：

* 首先，这两个方法都会接收一个运行函数的上下文对象作为第一个参数；
* 然后，`call`可以继续接收任意多个参数，这些参数都会按顺序依次传递给函数；而`apply`在会将传递给函数的参数按顺序组成一个数组作为第二个参数。

比如：

```JavaScript
window.color = 'red';
var o = { color: 'blue' };

function sayColor(color) {
    return color || this.color;
}

sayColor();  // "red"

sayColor.call(this);   // "red"
sayColor.call(window); // "red"
sayColor.call(o);      // "blue"

sayColor.call(o, 'green');    // "green"
sayColor.apply(o, ['green']); // "green"
```

可以看到：`apply/call`方法真正强大的地方在于其能够扩充函数赖以运行的作用域，从而能够完成方法和对象的解耦。

> 在严格模式下，为指定环境对象而调用函数，则`this`值不会转型为 window。除非明确把函数添加到某个对象或调用`apply`、`call`方法，否则`this`值将是 undefined。

### 3.2 bind()

ECMAScript 5 中为函数新增了一个`bind()`方法，这个方法会创建一个函数的实例，该实例的值内部的`this`会被绑定到传给`bind()`方法的值。

比如：

```JavaScript
window.color = 'red';
var o = { color: 'blue' };

function sayColor(color) {
    return color || this.color;
}

var objectSayColor = sayColor.bind(o);

sayColor();       // "red"
objectSayColor(); // "blue"
```

### 3.3 toString()/toLocalString()/valueOf()

这三个方法都是返回函数的代码。不过返回的代码的格式则因浏览器而异：有的返回与源代码一样的函数代码，有的则返回函数点的内部表示，即由解析器删除了注释并对某些代码做了改动后的代码。

