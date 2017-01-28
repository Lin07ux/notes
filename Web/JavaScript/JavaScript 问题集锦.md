### Math.min() 为什么比 Math.max() 大
对于下面的代码，输出却是`false`：

```javascript
var min = Math.min();
var max = Math.max();
console.log(min < max);
```

查看 MDN 文档可以发现，`Math.min()`和`Math.max()`都可以接受 0 个或者多个参数：

* 如果传入多个参数，有任何一个不能转成数字，那么就返回 NaN，否则返回其中的最小值/最大值
* 如果传入 0 个参数，则分别会返回**`Infinity`和`-Infinity`**，也即是分别返回 JavaScript 中的最大的正数，和最小的负数。

所以，不传入参数的时候，`Math.min()`是比`Math.max()`大的。

那么，为什么会这样呢？`min`不是应该返回最小值吗？`max`不是应该返回最大值吗？其实这个和代码的实现有关。

一般我们比较数值的大小的时候，会设置一个初始标准值。比如，`Math.min()`需要将其参数和一个标准值来进行比较，较小的值作为中间结果，然后将中间结果与下一个参数继续比较，这样最终的最小值就是结果了。可以考虑如下的一个填空题：

```javascript
var min = ___;
arr.forEach(function(n) {
    if (n > min) {
        min = n;
    }
});
```

自然，这里我们应该讲`min`变量设置初始值为`Infinity`才能符合实际情况。


### 函数声明和函数表达式

```js
var f = function g () { return 2; };

typeof g();
```

会报错。

因为在这里`function g () { return 2; }`作为一个函数表达式(`function expression`)，被赋值给了变量`f`。函数实际上是绑定到变量`f`，不是`g`。

不过，在`function g () { return 2; }`函数内部是能够使用`g`来指代这个函数的。

同样的，下面的代码也是函数表达式的问题：

```javascript
var x = 1;
if (function f(){}) {
    x += typeof f;
}
x; // "1undefined"
```


### 组合语句

```js
var f = (function f(){ return "1"; }, function g(){ return 2; })();

typeof f;
```

输出为‘number’。

当你有一系列的组合在一起，并由逗号分隔的表达式，它们从左到右进行计算，但只有最后一个表达式的结果保存。

比如：

```js
var x = (1, 2, 3);

x;   // 3
```

### 条件语句

```js
var x = 1;

if (function f(){}) {

    x += typeof f;

}

x;
```

输出：`1undefined`。

函数声明只能出现在程序或函数体内。从句法上讲，它们不能出现在 Block(块 { … })中，例如不能出现在 if、while 或 for 语句中。因为 Block（块） 中只能包含 Statement 语句，而不能包含函数声明这样的源元素。另一方面，仔细看一看规则也会发现，唯一可能让表达式出现在 Block（块）中情形，就是让它作为表达式语句的一部分。但是，规范明确规定了表达式语句不能以关键字 function 开头。而这实际上就是说，函数表达式同样也不能出现在 Statement 语句或 Block（块）中（因为 Block（块）就是由 Statement 语句构成的）。

所以，`f`在这了没有被定义，所以`typeof f`是字符串”undefined” ，字符与数字相加结果也是一个字符串，所以最后的 x 就是”1undefined”了。

### 原型与类

```js
function f(){ return f; }

new f() instanceof f;
```

返回`false`。

在这里，`f`最终返回的是其自身的定义，也就是说每一次执行`f()`就是对之前的`f`的覆盖。

如果`f`的形式为`function f(){return this}`或`function f(){}`，结果就不一样。

### 数组

```js
var x = [typeof x, typeof y][1];

typeof typeof x;
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


### delete 操作符

```javascript
(function(x){
    delete x;
    return x;  // 1
})(1);
```

`delete`操作符可以从对象中删除属性，只能作用在对象的属性上，对变量和函数名无效。所以这里的`delete x`是没有意义的。

另外，`delete`是不会直接释放内存的，她只是间接的中断对象引用


### 逗号操作符

```javascript
var f = (function f(){ return '1'; }, function g(){ return 2; })();
typeof f;  // "number"
```

逗号操作符对它的每个操作对象求值（从左至右），然后返回最后一个操作对象的值

所以`(function f(){ return '1'; }, function g(){ return 2; })`的返回值就是函数 `g`，然后执行她，那么结果是 2 ；最后再`typeof 2`，根据问题一的表格，结果自然是`number`。


### 浮点数误差

```javascript
var a = 0.1,
    b = 0.2;
    
if (a + b === 0.3) {
    console.log('OK');
} else {
    console.log('NO');
}
```
上面输出是 'NO'。

在 JavaScript 中`0.1 + 0.2 ≠ 0.3`。这是由于 IEEE 754 标准的浮点数精度限制。

JavaScript 中，数字都是用浮点数表示的，并规定使用 IEEE 754 标准的双精度浮点数表示。

IEEE 754 规定了两种基本浮点格式：单精度和双精度。

* IEEE 单精度格式具有 24 位有效数字精度(包含符号号)，并总共占用 32 位。
* IEEE 双精度格式具有 53 位有效数字精度(包含符号号)，并总共占用 64 位。

- 十进制 0.1
=> 二进制 0.00011001100110011…(循环 0011 ) 
=> 尾数为 1.1001100110011001100…1100（共 52 位，除了小数点左边的 1），指数为 -4（二进制移码为 00000000010 ）,符号位为 0
=> 计算机存储为：0 00000000100 10011001100110011…11001
=> 因为尾数最多52位，所以实际存储的值为 0.00011001100110011001100110011001100110011001100110011001

- 十进制 0.2
=> 二进制 0.0011001100110011…(循环 0011)
=>尾数为 1.1001100110011001100…1100（共 52 位，除了小数点左边的 1），指数为 -3（二进制移码为 00000000011）,符号位为 0
=> 存储为：0 00000000011 10011001100110011…11001
=> 因为尾数最多 52 位，所以实际存储的值为0.00110011001100110011001100110011001100110011001100110011

- 那么两者相加得： 
0.00011001100110011001100110011001100110011001100110011001 + 0.00110011001100110011001100110011001100110011001100110011
= 0.01001100110011001100110011001100110011001100110011001100
转换成 10 进制之后得到：0.30000000000000004

参考：[知乎 - 刘浩博](https://www.zhihu.com/question/24415787/answer/57187211)


### 数组 map 的回调
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


### 数组 reduce 的回调
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


### 运算符优先级

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





