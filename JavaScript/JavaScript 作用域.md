### var 与 let 作用域
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

### TDZ 暂时性死区
JavaScript 规范规定：一个*已经声明但未初始化*的变量不能被赋值，甚至不能被引用。

规范里用来声明`var/let`变量的内部方法是`CreateMutableBinding()`，初始化变量用`InitializeBinding()`，为变量赋值用`SetMutableBinding()`，引用一个变量用`GetBindingValue()`。在执行完`CreateMutableBinding()`后没有执行`InitializeBinding()`就执行`SetMutableBinding()`或者`GetBindingValue()`是会报错的，这种表现有个专门的术语（非规范术语）叫**TDZ(Temporal Dead Zone)**，通俗点说就是一个变量在声明后且初始化前是完完全全不能被使用的。

因为 var 变量的声明和初始化（成`undefined`）都是在“预处理”过程中同时进行的，所以永远不会触发 TDZ 错误。

let 的话，声明和初始化是分开的，只有真正执行到 let 语句的时候，才会被初始化。如果只声明不赋值，比如`let foo`，foo 会被初始化成`undefined`，如果有赋值的话，只有等号右侧的表达式求值成功（不报错），才会初始化成功。一旦错过了初始化的机会，后面再没有弥补的机会。这是因为赋值运算符`=`只会执行`SetMutableBinding()`，并不会执行`InitializeBinding()`，所以如果在定义并初始化的 let 变量的语句中如果错误了，那么这个变量就被永远困在了 TDZ 里，不能读取和赋值了。

比如，可以在浏览器的调试工具中进行如下的操作：

![TDZ 示例](http://cnd.qiniu.lin07ux.cn/markdown/1479273008633.png)

在第一步中，声明并赋值一个 let 变量 map。但是在赋值的时候，由于`Map()`不带 new 不能被调用，所以就出错了。此时变量 map 就已经被困在了 TDZ 里面而无法再对其进行操作了。

于是，在第二步、第三步中，都会出错。其中，第二步的错误是因为变量 map 已经被声明过了，无法重复声明。但是第三步的错误提示其实是有问题的，应该是提示 map 未初始化错误。

const 声明的变量也有类似的问题。

转摘：[不要在控制台上使用 let/const](http://www.cnblogs.com/ziyunfei/p/6063426.html)



