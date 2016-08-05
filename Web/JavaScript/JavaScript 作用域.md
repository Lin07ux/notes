在 ES2015 之前最常见的两种作用域，全局作用局和函数作用域（局部作用域）。函数作用域可以嵌套，这样就形成了一条作用域链，如果我们自顶向下的看，一个作用域内部可以嵌套几个子作用域，子作用域又可以嵌套更多的作用域，这就更像一个“作用域树”而非作用域链了，作用域链是一个自底向上的概念，在变量查找的过程中很有用的。

在 ES3 时，引入了`try...catch`语句，在`catch`语句中形成了新的作用域，外部是访问不到`catch`语句中的错误变量。代码如下：

```javascript
try {
  throw new Error()
} catch(err) {
  console.log(err)
}
console.log(err) //Uncaught ReferenceError
```

再到 ES5 的时候，在严格模式下（use strict），函数中使用 eval 函数并不会再在原有函数中的作用域中执行代码或变量赋值了，而是会动态生成一个作用域嵌套在原有函数作用域内部。如下面代码：

```javascript
'use strict'
var a = function() {
    var b = '123'
    eval('var c = 456;console.log(c + b)') // '456123'
    console.log(b) // '123'
    console.log(c) // 报错
}
```

在非严格模式下，a 函数内部的`console.log(c)`是不会报错的，因为 eval 会共享 a 函数中的作用域，但是在严格模式下，eval 将会动态创建一个新的子作用域嵌套在 a 函数内部，而外部是访问不到这个子作用域的，也就是为什么`console.log(c)`会报错。

通过`let`关键字来声明变量也通过`var`来声明变量的语法形式相同，在某些场景下你甚至可以直接把`var`替换成`let`。但是使用`let`来申明变量与使用`var`来声明变量最大的区别就是作用域的边界不再是函数，而是包含`let`变量声明的代码块（`{}`）。下面的代码将说明`let`声明的变量只在代码块内部能够访问到，在代码块外部将无法访问到代码块内部使用`let`声明的变量。

```javascript
if (true) {
  let foo = 'bar'
}
console.log(foo) // Uncaught ReferenceError
```

在上面的代码中，foo 变量在 if 语句中声明并赋值。if 语句外部却访问不到 foo 变量，报 ReferenceError 错误。

在 ECMAScript 2015 中，let 也会提升到代码块的顶部，在变量声明之前去访问变量会导致 ReferenceError 错误，也就是说，变量被提升到了一个所谓的“temporal dead zone”（以下简称TDZ）。TDZ 区域从代码块开始，直到显示得变量声明结束，在这一区域访问变量都会报ReferenceError 错误。如下代码：

```javascript
function do_something() {
  console.log(foo); // ReferenceError
  let foo = 2;
}
```

而通过 var 声明的变量不会形成 TDZ，因此在定义变量之前访问变量只会提示 undefined，也就是上文以及讨论过的 var 的变量提升。

在全局环境中，通过 var 声明的变量会成为 window 对象的一个属性，甚至对一些原生方法的赋值会导致原生方法的覆盖。比如下面对变量 parseInt 进行赋值，将覆盖原生 parseInt 方法。

```javascript
var parseInt = function(number) {
  return 'hello'
}
parseInt(123) // 'hello'
window.parseInt(123) // 'hello'
```

而通过关键字 let 在全局环境中进行变量声明时，新的变量将不会成为全局对象的一个属性，因此也就不会覆盖 window 对象上面的一些原生方法了。如下面的例子：

```javascript
let parseInt = function(number) {
  return 'hello'
}
parseInt(123) // 'hello'
window.parseInt(123) // 123
```

