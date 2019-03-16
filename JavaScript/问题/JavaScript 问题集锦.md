
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

### 5. 数组

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

### 8. 数组 map 的回调

下面的方法调用返回的是什么？

```JavaScript
["1", "2", "3"].map(parseInt)
```

解析：`.map(callback(value, index, array))`回调函数传入三个参数，`parseInt(string, radix)`接收两个参数。

所以`map`传递给`parseInt`的参数是这样的（`parseInt`忽略`map`传递的第三个参数）`[1, 0], [2, 1], [3, 2]`。

然后`parseInt()`解析传过来的参数，相当于执行以下语句：

```JavaScript
parseInt('1', 0);   // 当 radix 为 0 时，默认为 10 进制，所以返回 1
parseInt('2', 1);   // 没有 1 进制，所以返回 NaN
parseInt('3', 2);   // 二进制中只有数字 1、2，没有数字 3，所以返回 NaN
```

`parseInt(string, radix)`中`radix`可选，表示要解析的数字的基数。该值介于`2 ~ 36`之间。如果省略该参数或其值为 0，则数字将以 10 为基础来解析，如果`string`以`0x`或`0X`开头，将以 16 为基数。如果该参数小于 2 或者大于 36，则`parseInt()`将返回`NaN`。

所以最终的结果是：`[1, NaN, NaN]`。


### 9. 数组 reduce 的回调

下面的调用的输出是什么？

```JavaScript
[ [3,2,1].reduce(Math.pow), [].reduce(Math.pow) ]
```

解析：`arr.reduce(callback, [initialValue])`的回调方法可以接收四个参数，依次为：

* `accumulator` 上一次调用回调返回的值，或者是提供的初始值（initialValue）
* `currentValue` 数组中正在处理的元素
* `currentIndex` 数组中正在处理的的元素索引
* `array` 调用 reduce 的数组 
另外，`reduce`的第二个参数可选，其值用于第一次调用`callback`的第一个参数。如果没有提供，则对数组的第一个参数的调用的回调方法会直接返回该元素的值。但如果数组为空并且没有提供`initialValue`，会抛出`TypeError`。

那么，第一个表达式等价于`Math.pow(3, 2) => 9, Math.pow(9, 1) => 9`。

而第二个表达式就直接抛出`TypeError`错误了。

所以最终的结果是：`Uncaught TypeError`。


### 10. 运算符优先级

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

### 11. 最大的数值

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


### 12. 稀疏数组和密集数组

```JavaScript
var ary = [0,1,2];
ary[10] = 10;
ary.filter(function(x) { return x === undefined;});
```

首先需要理解稀疏数组和密集数组。

遍历稀疏数组时，会发现这个数组并没有元素，js 会跳过这些坑。

```JavaScript
//第一种情况
var a = new Array(3); 
console.log(a);   // [undefined x 3]

//第二种情况
var arr = [];
arr[0] = 1;
arr[100] = 100;

arr.map(function (x, i) {return i}); // [0, 100]
```

而对于密集数组则可以看到对应的数组元素：

```JavaScript
var a = Array.apply(null, Array(3));
console.log(a);   // [undefined, undefined, undefined]

a.map(function (x, i) {return i}); // [0, 1, 2]
```

这道题目里的数组是一个稀疏数组，不会遍历到从索引 3 - 9 的“坑”，这些索引都不存在数组中，所以永远筛选不到等于 undefined 的值。

所以结果为`[]`。

### 13. Switch 的比较

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


### 14. 数值：奇偶与无穷大

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

### 15. 数组 prototype

```JavaScript
Array.isArray( Array.prototype )
```

`Array.prototype`本身是一个数组，这只能牢牢记住了~。所以结果为`true`。

### 16. 数组的 bool 值

```JavaScript
var a = [0];
if ([0]) {
  console.log(a == true);
} else {
  console.log("wut");
}
```

所有对象都是`true`，但是当执行`a == true`时会进行隐式转换。所以结果为`false`。


