### 1. 数组 map 的回调

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


### 2. 数组 reduce 的回调

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

### 3. 稀疏数组和密集数组

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

### 4. 数组 prototype

```JavaScript
Array.isArray( Array.prototype )
```

`Array.prototype`本身是一个数组，这只能牢牢记住了~。所以结果为`true`。

### 5. 数组的 bool 值

```JavaScript
var a = [0];
if ([0]) {
  console.log(a == true);
} else {
  console.log("wut");
}
```

所有对象都是`true`，但是当执行`a == true`时会进行隐式转换成数值或字符串。所以结果为`false`。

### 6. 数组元素的最后一个逗号

数组在定义的时候，可以在最后一个元素后面添加一个`,`，这样并不影响数组的长度。比如：

```JavaScript
var a = [,,,]

console.log(a, a.length); // [empty x 3]  3

a.join(', '); // ', , '
```

同样的，定义对象的时候，也可以在最后一个属性的值后面添加`,`，也不会影响对象的定义结果。

