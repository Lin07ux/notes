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




