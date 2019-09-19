> 转摘：[JS异常函数之-箭头函数](https://segmentfault.com/a/1190000020169304)

箭头函数是最有价值的新功能之一，它具有上下文透明性和简短的语法，能够方便开发。

## 一、箭头函数特征行为

箭头函数除了写法上与常规函数不同之外，在一些行为上也与常规函数有差异：

* 无论在严格模式还是非严格模式下，箭头函数都不能具有重复的命名参数。
* 箭头函数没有`arguments`绑定，但是可以访问最接近的非箭头父函数的`arguments`对象。
* 箭头函数永远不能用作构造函数，自然的不能使用`new`关键字调用。因此，对于箭头函数不存在`prototype`属性。
* 在函数的整个生命周期中，箭头函数内部的上下文(也就是`this`)保持不变，并且总是与接近的非箭头父函数中的上下文绑定。

### 1.1 函数命名参数

在非严格模式下，JavaScript 常规函数的命名参数允许有重复的名称，调用时后面的参数的值会覆盖前面的参数的值。但在严格模式下不允许。

比如：

```JavaScript
function logParams (first, second, first) {
  console.log(first, second);
}

// first => 'Hello'
// second => 'World'
// first => '!!!'
logParams('Hello', 'World', '!!!'); // "!!!"  "World"

// first => { o: 3 }
// second => [ 1, 2, 3 ]
// first => undefined
logParams({ o: 3 }, [ 1, 2, 3 ]); // undefined  [1, 2, 3]
```

与常规函数不同，**无论在严格模式还是非严格模式下，箭头函数都不允许重复参数**，重复的参数将引发语法错误。

### 1.2 arguments

箭头函数中不存在`arguments`特殊变量，但是可以访问非箭头父函数的`arguments`对象。这就导致不能像常规函数一样访问不定量的参数。但是可以使用 ES6 语法中的 rest 参数来实现相同的目的。

使用 ES6 rest 参数可以得到一个数组，该数组保存了传递给该函数的所有的参数。rest 语法适用于所有类型的函数，无论是常规函数还是箭头函数。

比如下面实现计算参数的平均值的函数：

```JavaScript
const average = (...args) => {
  if (args.length == 0) return 0;
  const sumReduceFn = function (a, b) { return a + Number(b) };

  return args.reduce(sumReduceFn, 0) / args.length;
}
```

### 1.3 构造函数

当使用`new`关键字调用常规 JavaScript 函数时，将调用函数内部`[[Construct]]`方法来创建一个新的实例对象并分配内存。之后，函数体将正常执行，并将`this`映射到新创建的实例对象。最后，函数隐式地返回`this`(新创建的实例对象)。

此外，所有常规 JavaScript 函数都有一个`prototype`属性，包含函数创建的所有实例对象在用作构造函数时共享的属性和方法。

而与常规函数不同，箭头函数永远不能使用`new`关键字调用，因为它们没有`[[Construct]]`方法。同时，箭头函数也不存在`prototype`属性。

```JavaScript
const Square = (length = 10) => {
  this.length = parseInt(length) || 10;
}

console.log(Square.prototype); // undefined

// throws an error
const square = new Square(5);

// throws an error
Square.prototype.getArea = function() {
  return Math.pow(this.length, 2);
}
```

### 1.4 上下文

JavaScript 常规函数每次调用时，其内部上下文取决于函数是如何调用的，或者在哪里调用的，是动态的。

与常规函数不同，**箭头函数是没有自己的上下文的，它的上下文将被解析为最接近的非箭头父函数或全局对象的值，而且无法改变**。也就是说，箭头函数中的`this`总是绑定到定义时其所在的上下文，而且无法通过`bind`、`apply`、`call`等方法进行改变。

比如，对于如下的倒计时计时器，使用`setInterval()`进行倒计时，直到持续时间过期或间隔被清除为止：

```JavaScript
function Timer (seconds = 60) {
  this.seconds = parseInt(seconds) || 60;
  console.log(this.seconds);

  this.interval = setInterval(function () {
    console.log(--this.seconds);

    if (this.seconds == 0) {
      this.interval && clearInterval(this.interval);
    }
  }, 1000);
}

const timer = new Timer(30);
```

执行这段代码，会发现倒计时计时器似乎被打破了，在控制台上一直打印 NaN。

这里的问题是，在传递给`setInterval()`的回调函数中，`this`指向全局 Window 对象，而不是`Timer()`函数作用域内新创建的实例对象。因此，`this.seconds`和`this.interval`都是 undefined 的。

要修复这个问题，可以将传递给`setInterval()`的回调方法使用`bind()`绑定当前上下文，也可以用一个箭头函数替换`setInterval()`回调函数，这样它就可以使用最近的非箭头父函数的`this`值：

```JavaScript
function Timer (seconds = 60) {
  this.seconds = parseInt(seconds) || 60;
  console.log(this.seconds);

  this.interval = setInterval(() => {
    console.log(--this.seconds);

    if (this.seconds == 0) {
      this.interval && clearInterval(this.interval);
    }
  }, 1000);
}
```

此时，回调方法中的`this`就是倒计时计时器了，功能能够正常执行了。

## 二、什么时候不应使用箭头函数

由于箭头函数改变了普通函数`this`指向的动态性，使其在一些特定场合中并不适合使用。

### 2.1 定义对象上的方法

在 JavaScript 中，方法是存储在对象属性中的函数。当调用该方法时，`this`将指向该方法所属的对象。但是如果使用箭头函数定义的话，则该方法中的`this`总是指向定义时的上下文：

```JavaScript
const calculate = {
  array: [1, 2, 3],
  sum1: () => {
    console.log(this === window); // => true
    return this.array.reduce((result, item) => result + item);
  },
  sum2 () {
    console.log(this === calculate); // => true
    return this.array.reduce((result, item) => result + item);
  }
};

console.log(this === window); // => true
calculate.sum1(); // => Throws "TypeError: Cannot read property 'reduce' of undefined"
calculate.sum2(); // => 6
```

同样的规则也适用于在原型对象上定义方法。在对象上使用箭头函数来定义`sayCatName`方法，其`this`也会指向 window：

```JavaScript
function MyCat(name) {
  this.catName = name;
}

MyCat.prototype.sayCatName = () => {
  console.log(this === window); // => true
  return this.catName;
};

const cat = new MyCat('Mew');
cat.sayCatName(); // => undefined
```

### 2.2 动态上下文的回调函数

由于箭头函数会在声明上静态绑定上下文，并且无法使其动态化，这在上下文经常变化的情况(如 DOM 事件监听器)会造成问题。

下面的示例尝试为 DOM 元素的点击事件的处理程序使用箭头函数：

```JavaScript
const button = document.getElementById('myButton');
button.addEventListener('click', () => {
  console.log(this === window); // => true
  this.innerHTML = 'Clicked button';
});
```

定义该事件监听器的时候，全局上下文中`this`指向 window。 当发生单击事件时，浏览器尝试使用按钮上下文调用处理函数，但箭头函数不会更改其预定义的上下文。`this.innerHTML`相当于`window.innerHTML`，没有任何意义。

这时候就应该使用一般函数来设置事件监听器，从而允许根据目标元素更改`this`：

```JavaScript
const button = document.getElementById('myButton');
button.addEventListener('click', function() {
  console.log(this === button); // => true
  this.innerHTML = 'Clicked button';
});
```

### 2.3 调用构造函数

`this`在构造调用中是新创建的对象。当执行`new MyFunction()`时，构造函数`MyFunction`的上下文是一个新对象：

```JavaScript
this instanceof MyFunction === true
```

箭头函数不能用作构造函数，JavaScript 会通过抛出异常来隐式阻止这样做。由于箭头函数中`this`是来自封闭上下文的设置，而不是新创建的对象，所以箭头函数构造函数调用没有意义，而且是模糊的。

如果尝试这样做：

```JavaScript
const Message = (text) => {
  this.text = text;
};

// Throws "TypeError: Message is not a constructor"
const helloMessage = new Message('Hello World!');
```

可以看到视图实例化一个箭头函数会触发异常。

