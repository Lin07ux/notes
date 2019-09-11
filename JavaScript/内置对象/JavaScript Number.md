Number 是与数字值对应的引用类型。

## 一、基础

### 1.1 创建

创建 Number 对象和创建其他对象一样：

```JavaScript
var numberObject = new Number(1);
```

传入的参数会被转换成数值。如果不传入参数，则默认为 0；如果传入的参数无法转换成数值，则会转换成 NaN。

### 1.2 特性

由于 Number 实例也是一个对象，所以转换成布尔值时总是 true，而和创建 Number 对象时传入的数值无关。

比如：

```JavaScript
var numberObject = new Number(0);

if (numberObject) {
    console.log('nunberObject 转换成布尔值是 true')
} else {
    console.log('nunberObject 转换成布尔值是 false')
}
// nunberObject 转换成布尔值是 true
```

## 二、方法

### 2.1 基本方法

Number 实例重写了对象的`valueOf()`、`toString()`和`toLocalString()`方法：

* `valueOf()` 返回对象表示的基本类型的数值
* `toString()/toLocalString()` 返回字符串形式的数值

### 2.2 toFixed()

`toFixed()`方法会按照指定的小数位返回数值的字符串表示。

其只需要接收一个参数，表示结果中保留的小数位数：

* 如果数值本身包含的小数位比指定的多，那么会进行四舍五入处理。
* 如果数值本身包含的小数位比指定的少，那么会在后面补 0。

> 标准规范中，`toFixed()`方法可以表示带有 0 到 20 个小数位的数值，有些浏览器可以支持更多位数。

例如：

```JavaScript
var num = 10;
console.log(num.toFixed(2)); // "10.00"
```

### 2.3 toExponential()

`toExponential()`方法返回以指数表示法(也称 e 表示法)表示的数值的字符串形式。

该方法接受一个参数，指定输出结果中小数位数，与`toFixed()`方法一致。

比如：

```JavaScript
var num = 10;
console.log(num.toExponential(1)); // "1.0e+1"
```

### 2.4 toPrecision()

`toPrecision()`方法会根据要处理的数值决定调用`toFixed()`还是`toExponential()`，具体规则是看哪种格式更合适。

该方法也接受一个参数，指定返回结果中小数位数，与`toFixed()`方法一致。

比如：

```JavaScript
var num = 99;

console.log(num.toPrecision(1));  // "1e+2"
console.log(num.toPrecision(2));  // "99"
console.log(num.toPrecision(3));  // "99.0"
```


