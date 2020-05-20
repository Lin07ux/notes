
### 1. 函数声明和函数表达式

```js
var f = function g () { return 2; };

typeof g();
```

这样会报错。因为在这里`function g () { return 2; }`作为一个函数表达式(`function expression`)，被赋值给了变量`f`。函数实际上是绑定到变量`f`，不是`g`。

不过，在`function g () { return 2; }`函数内部是能够使用`g`来指代这个函数的。

同样的，下面的代码也是函数表达式的问题：

```javascript
var x = 1;
if (function f(){}) {
    x += typeof f;
}
x; // "1undefined"
```

### 2. 组合语句

```js
var f = (function f(){ return "1"; }, function g(){ return 2; })();

typeof f;
```

输出为‘number’。

当有一系列的组合在一起，并由逗号分隔的表达式，它们从左到右进行计算，但只有最后一个表达式的结果保存。

比如：

```js
var x = (1, 2, 3);

x;   // 3
```

### 3. 条件语句

```js
var x = 1;

if (function f(){}) {
    x += typeof f;
}

x;
```

输出：`1undefined`。

函数声明只能出现在程序或函数体内。从句法上讲，它们不能出现在 Block(块`{ … }`)中，例如不能出现在 if、while 或 for 语句中。因为 Block（块） 中只能包含 Statement 语句，而不能包含函数声明这样的源元素。另一方面，仔细看一看规则也会发现，唯一可能让表达式出现在 Block（块）中情形，就是让它作为表达式语句的一部分。但是，规范明确规定了表达式语句不能以关键字 function 开头。而这实际上就是说，函数表达式同样也不能出现在 Statement 语句或 Block（块）中（因为 Block（块）就是由 Statement 语句构成的）。

所以，`f`在这了没有被定义，所以`typeof f`是字符串”undefined” ，字符与数字相加结果也是一个字符串，所以最后的 x 就是”1undefined”了。

### 4. 原型与类

```js
function f(){ return f; }

new f() instanceof f;
```

返回`false`。在这里，`f`最终返回的是其自身的定义，也就是说每一次执行`f()`就是对之前的`f`的覆盖。如果`f`的形式为`function f(){return this}`或`function f(){}`，结果就不一样。

### 5. typeof

```js
var x = [typeof x, typeof y][1];

typeof x;
```

返回`string`。

这里其实可以写成如下的形式：

```js
var x;

x = [typeof x, typeof y][1];
// 等同于 x = ["undefined", "undefined"][1];

typeof x;
// 等同于 typeof "undefined";
```

所以输出的就是字符串类型了。


### 6. delete 操作符

```javascript
(function(x){
    delete x;
    return x;  // 1
})(1);
```

`delete`操作符可以从对象中删除属性，只能作用在对象的属性上，对变量和函数名无效。所以这里的`delete x`是没有意义的。

另外，`delete`是不会直接释放内存的，她只是间接的中断对象引用


### 7. 逗号操作符

```javascript
var f = (function f(){ return '1'; }, function g(){ return 2; })();
typeof f;  // "number"
```

逗号操作符对它的每个操作对象求值（从左至右），然后返回最后一个操作对象的值

所以`(function f(){ return '1'; }, function g(){ return 2; })`的返回值就是函数 `g`，然后执行她，那么结果是 2；最后再`typeof 2`，根据问题一的表格，结果自然是`number`。



### 8. 运算符优先级

```JavaScript
var val = 'smtg';
console.log('Value is ' + (val === 'smtg') ? 'Something' : 'Nothing');
```

**+ 的优先级高于 ?**

所以上面表达式的执行顺序是：

```JavaScript
val === 'stmg'      // => true
'Value is' + true   // => 'Value is true'
'Value is true' ? 'Something' : 'Nothing'  // => 'Something'
```

所以结果为`'Something'`。

### 9. 最大的数值

```JavaScript
var END = Math.pow(2, 53);
var START = END - 100;
var count = 0;
for (var i = START; i <= END; i++) {
    count++;
}
console.log(count);
```

JS 里`Math.pow(2, 53)`是可以表示的最大值，最大值加 1 还是最大值。

```javascript

```

所以 i 永远不可能大于 END，最终的结果是**无限循环**。

### 10. Switch 的比较

```JavaScript
function showCase(value) {
    switch(value) {
    case 'A':
        console.log('Case A');
        break;
    case 'B':
        console.log('Case B');
        break;
    case undefined:
        console.log('undefined');
        break;
    default:
        console.log('Do not know!');
    }
}

showCase(new String('A'));
showCase(String('A'));
```

**switch是严格比较。**所以不仅仅要比较传入的值与 case 的值是否相等，还要他们的类型都匹配。

另外，对于 JavaScript 中的基本类型的值，可以通过创建对象的方式创建，但是加`new`和不加`new`的时候，其结果并不完全一致。

```JavaScript
typeof (new String('A'));  // "object"
typeof (String('A'));      // 'string'
```

所以第一个的函数调用的输出为`Do not know`，第二个函数的调用结果为：`Case A`。


### 11. 数值：奇偶与无穷大

```JavaScript
function isOdd(num) {
    return num % 2 == 1;
}
function isEven(num) {
    return num % 2 == 0;
}
function isSane(num) {
    return isEven(num) || isOdd(num);
}
var values = [7, 4, '13', -9, Infinity];
values.map(isSane);
```

解析：主要在于`-9 % 2 == -1`保留正负号。`Infinity % 2`得到的是`NaN`，但是注意`NaN`与所有值都不相等包括本身。

所以结果为`[true, true, true, false, false]`。

### 12. arguments 对象

```JavaScript
function sideEffecting (ary) {
    arguments[1] = 10;
    console.log('sideEffecting 1:', ary);
    ary[0] = ary[2];
    console.log('sideEffecting 2:', ary);
}

function bar (a, b, c) {
    c = 10;
    console.log('bar 1:', arguments);
    sideEffecting(arguments);
    console.log('bar 2:', arguments);
    
    return a + b + c;
}

console.log(bar(1, 1, 1));
```

`arguments`是函数中的一个特殊的类数组对象。当将对象作为参数传递给一个函数时，在函数内部对该对象参数的修改会传递到外部，产生副作用。同时，在函数内部修改形参的值时，也会更改到`arguments`对象中。

所以，上面的代码执行时会有如下的输出：

```
bar 1: Arguments(3) [1, 1, 10, callee: ƒ, Symbol(Symbol.iterator): ƒ]
sideEffecting 1: Arguments(3) [1, 1, 10, callee: ƒ, Symbol(Symbol.iterator): ƒ]
sideEffecting 2: Arguments(3) [10, 1, 10, callee: ƒ, Symbol(Symbol.iterator): ƒ]
bar 2: Arguments(3) [10, 1, 10, callee: ƒ, Symbol(Symbol.iterator): ƒ]
21
```

### 13. function.name

函数是一类特殊的对象，其具有一个只读的`name`属性。

```JavaScript
function x () {}

var oldName = x.name;
x.name = 'bar';
console.log(oldName, x.name);  // 'x' 'x'

var parent = Object.getPrototypeOf(x);
console.log(typeof eval(x.name));  // 'function'
console.log(typeof eval(parent.name)); // 'undefined'
```

由于函数的原型对象是`Function.prototype`，是一个对象，其并没有`name`属性，所以`parent.name = ''`。在`eval()`函数中，`x.name`就代表着 x，执行之后返回的就是 x 函数，所以其类别就是`'function'`；而对于空字符串，`eval()`执行的结果就是`undefined`。




