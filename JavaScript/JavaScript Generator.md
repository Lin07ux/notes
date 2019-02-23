> 转摘：[详解 ECMAScript 6 中的生成器（Generator）](http://www.ibm.com/developerworks/cn/web/wa-es6-generator/index.html)

Generator(生成器)是 ECMAScript 6 中引入的新概念。通过 Generator 可以暂停程序的执行，还可以在程序执行中进行数据交换。

生成器的强大之处正是来源于可以暂停和继续生成器对象执行的能力。每个生成器对象都可以被看成是一个状态机。调用`next`方法会继续生成器的执行，触发内部的状态转换，运行到下一个`yield`表达式所在的位置。接着执行会被暂停，等待下一次`next`方法的调用。

## 一、基本概念

Generator 有两个最基本的概念：

* 生成器函数：用来创建生成器对象的一类特殊函数。
* 生成器对象：生成器函数的具体实例。

在代码中，是通过生成器对象来进行相关操作的，而生成器对象就是通过生成器函数执行得到的。可以这样理解：生成器函数像是一个类，而生成器对象则是这个“类”的实例化对象。同样的，生成器函数每次执行得到的实例化对象都是完全不同的对象，其相互之间不会有干扰。

当然，生成器对象和一般的类实例或函数是有所不同的：生成器对象的最大特点在于它们的执行可以被暂停和继续。JavaScript 中一个函数一旦开始执行，就会一直执行到结束，并把返回值传递给调用者(除非遇到错误而被中断执行)。而生成器对象则会在设定好的地方自动暂停执行，并返回一个值，而且在下次继续执行前，还能传递一个值到生成器对象中。

另外，生成器函数也可以接收参数，这点和一般的函数是一样的。而且在生成器函数中，可以正常使用任意的 JavaScript 合法语法结构，没有特别的限制。

## 二、生成器函数

要得到生成器对象，需要先有生成器函数。生成器函数也是一个函数，与普通函数的差别在于`function`和函数名称之间的星号`*`。另外生成器函数中使用`yield`表达式来产生值。

```JavaScript
function *simple() {
  yield 1;
  yield 2;
  yield 3;
}
```

生成器函数可以和一般的函数一样执行，只是执行生成器函数只是返回一个生成器对象，而不是真正的执行其内部代码。

## 三、生成器对象

### 3.1 next() 方法

执行上面的生成器函数就可以得到一个生成器对象，此时生成器函数中的代码并没有立即执行，而是出于暂停状态，直到调用了生成器对象的`next()`方法来让程序继续执行下去：

```JavaScript
let func = sample();
func.next();
// -> {value: 1, done: false}
func.next();
// -> {value: 2, done: false}
func.next();
// -> {value: 3, done: false}
func.next();
// -> {value: undefined, done: true}
func.next();
// -> {value: undefined, done: true}
```

每次调用生成器对象的`next()`方法时，可以依次返回多个不同的值，这些返回值通过`yield`或`return`来声明。生成器函数`simple()`中使用`yield`来产生了 1，2 和 3 共 3 个值。当`next()`方法调用时，这些值会被依次返回。

`next()`方法的返回值是一个包含了`value`和`done`两个的对象。属性`value`包含的是`yield`表达式所产生的值，而`done`用来表示是否还有更多值可以被获取。再次调用`next`，可以继续生成器对象执行，并执行到第二个`yield`表达式。如此循环，直到第四个`next`方法调用，`done`的值才变为`true`，表明已经没有更多值可以被获取了。

### 3.2 生成器对象互不影响

同一个生成器函数所创建的每个对象都在内部维护自己的状态，彼此并不会互相影响。

```JavaScript
let func1 = simple();
let func2 = simple();
func1.next();
// -> {value: 1, done: false}
func2.next();
// -> {value: 1, done: false}
func1.next();
// -> {value: 2, done: false}
```

### 3.3 调用 next 方法时传递值

生成器对象在执行过程中，可以与外界进行数据交换：生成器对象不仅能够返回一系列的值，还能在调用`next()`方法的时候传入参数。在没有传递参数给`next()`时，默认会将参数设置为 undefined。

如下，一个进行数学计算的生成器函数：

```JavaScript
function *doMath() {
  let x = yield 1;
  let y = yield x + 10;
  let z = yield y * 10;
}
```

当直接调用这个生成器函数的对象的`next()`时，得到的结果如下：

```JavaScript
let func = doMath();
func.next();
// -> {value: 1, done: false}
func.next();
// -> {value: NaN, done: false}
func.next();
// -> {value: NaN, done: false}
func.next();
// -> {value: undefined, done: true}
```

这是由于，执行到第一个`yield`时，生成器对象的`next()`是把 1 作为值返回了。在第二次调用`next()`时，会先执行`let x = ...`，由于没有传入参数，所以`yield 1`语句对应的值就是 undefined，于是`x`的值就是 undefined。在执行到第二个`yield`的时候，就得到的是 NaN 了。由此类推，第三个`next()`调用中，`y`的值同样也是 undefined，因此产生的值也是 NaN。

如果在调用`next()`方法的时候，传入相应的参数，那么结果就不一样了：

```JavaScript
let func = doMath();
func.next();
// -> {value: 1, done: false}
func.next(1);
// -> {value: 11, done: false}
func.next(2);
// -> {value: 20, done: false}
func.next(3);
// -> {value: undefined, done: true}
```

从上面可以看出：**调用`next()`方法时，传入的参数会作为最近一次被执行的`yield`语句的结果值(与`next()`执行的结果不是一个)。如果没有参数，那么这个`yield`语句的结果值就是 undefined。**

### 3.4 return() 方法

生成器对象的 return 方法可以用来返回给定值并结束它的执行。其使用效果类似于在生成器函数中使用`return`语句。

```JavaScript
function *values() {
  yield 'a';
  yield 'b';
  yield 'c';
}


let func = values();
func.next();
// -> {value: "a", done: false}
func.return('d');
// -> {value: "d", done: true}
func.next();
// -> {value: undefined, done: true}
```

在上面的代码中，调用`func.return('d')`会返回传入的值`d`，并结束生成器，也就是`done`的值变为`true`，即使生成器中仍然还有值 b 和 c 未被生成。方法`return`可以被多次调用，每次调用都返回传入的值。

### 3.5 throw() 方法

生成器对象的`throw`方法可以用来传入一个值，并使其抛出异常，它和`next()`都可以传入值到生成器对象中来改变其行为。通过`throw`传入的值则相当于把上一个`yield`语句替换到一个`throw`语句。

```JavaScript
function *sample() {
  let x = yield 1;
  let y = yield x + 1;
  yield y * 10;
}

let func = sample();
func.next();
// -> {value: 1, done: false}
func.next(1);
// -> {value: 2, done: false}
func.throw('hello');
// -> Uncaught hello
func.next();
// -> {value: undefined, done: true}
```

当`func.throw('hello')`被调用时，上一个`yield`表达式`yield x + 1`被替换成`throw 'hello'`。由于抛出的对象没有被处理，会被直接传递到 JavaScript 引擎，导致生成器的执行终止。

当然，我们也可以在生成器函数中使用`try...catch`语法来捕获异常。

## 四、yield * 表达式

目前我们看到的生成器对象每次只通过`yield`表达式来产生一个值。实际上，可以使用`yield *`表达式来生成一个值的序列。当使用`yield *`时，当前生成器对象的序列生成被代理给另外一个生成器对象或可迭代对象。

```JavaScript
function debug(values) {
  for (let value of values) {
    console.log(value);
  }
}

function *oneToThree() {
  yield* [1, 2, 3];
}

debug(oneToThree());
// -> 输出 1, 2, 3
```

通过`yield* [1, 2, 3]`来生成 3 个值，与中的生成器函数`simple`的结果是相同的，不过使用`yield *`的方式更加简洁易懂。

在一个生成器函数中可以使用多个`yield *`表达式。在这种情况下，来自每个`yield *`表达式的值会被依次生成。

```JavaScript
function *multipleYieldStars() {
  yield* [1, 2, 3];
  yield 'x';
  yield* 'hello';
}

debug(multipleYieldStars());
// -> 输出 1, 2, 3, 'x', 'h', 'e', 'l', 'l', 'o'
```

> 字符串`hello`会被当成一个可迭代的对象，也就是会输出其中包含的每个字符。

### 4.1 返回值

由于`yield *`也是表达式，它是有值的。它的值取决于在`yield *`之后的表达式。`yield *`表达式的值是其后面的生成器对象或可迭代对象所产生的最后一个值，也就是属性`done`为`true`时的那个值：

* 如果`yield *`后面是可迭代对象，那么`yield *`表达式的值总是 undefined，这是因为最后一个生成的值总是`{value: undefined, done: true}`。
* 如果`yield *`后面是生成器对象，我们可以通过在生成器函数中使用`return`来控制最后一个产生的值。

在下面的代码中，通过`return`来改变了生成器函数`abc`的返回值，因此`yield *abc()`的值为`d`：

```JavaScript
var result;

function loop(iterable) {
  for (let value of iterable) {
    //ignore
  }
}

function *abc() {
  yield* 'abc';
  return 'd';
}

function *generator() {
  result = yield* abc();
}

loop(generator());
console.log(result);
// -> "d"
```

### 4.2 嵌套 yield

表达式`yield`和`yield *`都可以进行嵌套。

在下面的代码中，最内层的`yield`表达式生成值 1，然后中间的`yield`表达生成 `yield 1`的值，也就是 undefined。这是因为在遍历调用`next()`时并没有传入参数。最外层的`yield`的值也是 undefined。

```JavaScript
function *manyYields() {
  yield yield yield 1;
}

debug(manyYields());
// 输出 1, undefined, undefined
```

## 五、其他

### 5.1 yield 与 return

在生成器函数中，`yield`可以暂停程序的执行，并返回一个结果，同样，生成器函数中也可以使用`return`语句，也可以返回一个结果。不同的是，`return`会结束掉生成器对象的执行。也就是说，如果执行到生成器函数中的`return`语句之后，再调用`next()`方法也不会继续执行其他的代码了。

```JavaScript
function *withReturn() {
  let x = yield 1;
  return x + 2;
}

let func = withReturn();
func.next();
// -> {value: 1, done: false}
func.next(1);
// -> {value: 3, done: true}
func.next();
// -> {value: undefined, done: true}
```

### 5.2 生成器与迭代器

从前面的代码中可以发现，`next()`方法的返回值并不是特别直观，需要通过属性`done`来判断是否还有值。实际上，这是因为**生成器对象本身也是迭代器(iterator)对象**，而迭代器对象用`next()`方法来获取其中的值。同时**生成器对象也是可被迭代的(iterable)**。因此，我们可以用 ECMAScript 6 中的其他新特性来遍历其中的值，包括`for-of`循环，`spread`操作符和新的集合类型：

```JavaScript
for (let value of simple()) {
  console.log(value);
}
// -> 输出 1，2 和 3
['a', ...simple(), 'b']
// -> [ 'a', 1, 2, 3, 'b' ]

let set = new Set(simple())
set.size
// -> 3
```

