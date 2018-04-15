### 1. (0, _classCallCheck3.default)(this, Person)

通过`transform-runtime`插件转换类之后，会出现类似下面的代码：

```js
var Person = function Person() {
  (0, _classCallCheck3.default)(this, Person);
};
```

这里奇怪的是为什么要写成`(0, _classCallCheck3.default)(this, Person);`。

首先，对于这个语句，第一个括号中的语句会按照 [comma operator](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Comma_Operator) 来执行，也就是先执行语句`0`，再执行语句`_classCallCheck3.default`，最终这个括号的返回值是最后一个语句的返回值，也就是函数`_classCallCheck3.default`。

然后，第一个括号执行完成之后，得到一个函数。函数后跟随一个小括号就是立即执行该函数。

那么，整个语句的作用就是调用`_classCallCheck3.default`函数。

为什么要这样调用呢？其实这是一个小技巧，为了能够在调用`_classCallCheck3.default`函数时，`this`不会指向其自身。

也就说，这个语句的作用，和下面的代码相同：

```js
const func = _classCallCheck3.default;
func(this, Person);
```

