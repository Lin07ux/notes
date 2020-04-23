在 JavaScript 中的运算符中，`+`有以下的使用情况：

* 数字的加法运算，二元运算
* 字符串的连接运算，二元运算，最高优先
* 正号，一元运算，可延伸为强制转换其他类型的运算元为数字类型 
当然，如果考虑多个符号一起使用时，`+=`与`++`又是另外的用途。

另一个常见的是花括号`{}`，它有两个用途也很常见：

* 对象的字面文字定义
* 区块语句 
所以，要能回答这个问题，要先搞清楚重点是什么：

* 加号`+`运算在 JS 中在使用上的规定是什么。
* 对象在 JS 中是怎么转换为原始数据类型的值的。

### 加号运算符
除了上面说明的常见情况外，在标准中转换的规则还有以下几个，要注意它的顺序：

```
operand + operand = result
```

1. 使用`ToPrimitive`运算转换左与右运算元为原始数据类型值(primitive)
2. 在第 1 步转换后，如果有运算元出现原始数据类型是"字符串"类型值时，则另一运算元作强制转换为字符串，然后作字符串的连接运算(concatenation)
3.	在其他情况时，所有运算元都会转换为原始数据类型的"数字"类型值，然后作数学的相加运算(addition) 
这就涉及到了 JavaScript 实现中的`ToPrimitive`方法了。

### ToPrimitive 内部运算
由于加号运算符只能使用于原始数据类型，那么对于对象类型的值，就要转换为原始数据类型。下面说明是如何转换为原始数据类型的。

在[ECMAScript 6th Edition #7.1.1](http://www.ecma-international.org/ecma-262/6.0/#sec-toprimitive)，有一个抽象的`ToPrimitive`运算，它会用于对象转换为原始数据类型，这个运算不只会用在加号运算符，也会用在关系比较或值相等比较的运算中。

下面有关于`ToPrimitive`的说明语法：

```
ToPrimitive(input, PreferredType?)
```

`input`代表代入的值，而`PreferredType`可以是数字(Number)或字符串(String)其中一种，这会代表"优先的"、"首选的"的要进行转换到哪一种原始类型，转换的步骤会依这里的值而有所不同。但如果没有提供这个值也就是预设情况，则会设置转换的`hint`值为"default"。这个首选的转换原始类型的指示(`hint`值)，是在作内部转换时由 JS 视情况自动加上的，一般情况就是预设值。

而在 JS 的 Object 原型的设计中，都一定会有两个`valueOf`与`toString`方法，所以这两个方法在所有对象里面都会有，不过它们在转换有可能会交换被调用的顺序。

#### 当 PreferredType 为数字(Number)时
当 PreferredType 为数字(Number)时，以下是转换这个`input`值的步骤：

1.	如果`input`是原始数据类型，则直接返回`input`。
2.	否则，如果`input`是个对象时，则调用对象的`valueOf()`方法，如果能得到原始数据类型的值，则返回这个值。
3.	否则，如果`input`是个对象时，调用对象的`toString()`方法，如果能得到原始数据类型的值，则返回这个值。
4.	否则，抛出 TypeError 错误。 
#### 当 PreferredType 为字符串(String)时
上面的步骤 2 与 3 对调，如同下面所说:

1.	如果`input`是原始数据类型，则直接返回`input`。
2.	否则，如果`input`是个对象时，调用对象的`toString()`方法，如果能得到原始数据类型的值，则返回这个值。
3.	否则，如果`input`是个对象时，则调用对象的`valueOf()`方法，如果能得到原始数据类型的值，则返回这个值。
4.	否则，抛出 TypeError 错误。

#### PreferredType 没提供时，也就是 hint 为"default"时
一般情况下，会与 PreferredType 为数字(Number)时的步骤相同。

> 数字其实是预设的首选类型，也就是说在一般情况下，加号运算中的对象要作转型时，都是先调用`valueOf`再调用`toString`。

但这有两个异常，一个是 Date 对象，另一是 Symbol 对象，它们覆盖了原来的 PreferredType 行为，Date 对象的预设首选类型是字符串(String)。

> 因此你会看到在一些教程文件上会区分为两大类对象，一类是 Date 对象，另一类叫 非Date(non-date) 对象。因为这两大类的对象在进行转换为原始数据类型时，首选类型恰好相反。


### valueOf 与 toString 方法
valueOf 与 ToString 是在 Object 中的两个必有的方法，位于`Object.prototype`上，它是对象要转为原始数据类型的两个姐妹方法。

当然，对象的这两个方法都可以被覆盖，你可以自己实现这两个方法，从而能够更好的观察其执行的顺序。

#### Object 中的 valueOf 和 toString
在 JS 中所设计的 Object 纯对象类型的`valueOf`与`toString`方法，它们的返回如下：

* `valueOf`方法返回 对象本身。
* `toString`方法返回"[object Object]"字符串值，不同的内建对象的返回值是"[object type]"字符串，"type"指的是对象本身的类型识别，例如 Math 对象是返回"[object Math]"字符串。但有些内建对象因为覆盖了这个方法，所以直接调用时不是这种值。(注意: 这个返回字符串的前面的"object"开头英文是小写，后面开头英文是大写) 
所以，从上面的内容就可以知道，下面的这段代码的结果会是调用到`toString`方法(因为`valueOf`方法的返回并不是原始的数据类型)：

```JavaScript
1 + {}  // "1[object Object]"
```

一元正号(+)，具有让首选类型(也就是`hint`)设置为数字(Number)的功能，所以可以强制让对象转为数字类型，一般的对象会转为：

```JavaScript
+{}  // NaN   相当于 +"[object Object]"
```

我们可以用下面的代码来观察这两个方法的运行顺序，下面这个都是先调用`valueOf`的情况:

```JavaScript
let obj = {
  valueOf: function () {
      console.log('valueOf');
      return {}; // object
  },
  toString: function () {
      console.log('toString');
      return 'obj'; // string
  }
}
console.log(1 + obj);  // valueOf -> toString -> '1obj'
console.log(+obj);     // valueOf -> toString -> NaN
console.log('' + obj); // valueOf -> toString -> 'obj'
```

先调用`toString`的情况比较少见，大概只有 Date 对象或强制要转换为字符串时才会看到：

```JavaScript
let obj = {
  valueOf: function () {
      console.log('valueOf');
      return 1; // number
  },
  toString: function () {
      console.log('toString');
      return {}; // object
  }
}
alert(obj);  // toString -> valueOf -> alert("1");
String(obj); // toString -> valueOf -> "1";
```

而下面这个例子会造成错误，因为不论顺序是如何都得不到原始数据类型的值，错误消息是"TypeError: Cannot convert object to primitive value"，从这个消息中很明白的告诉你，它这里面会需要转换对象到原始数据类型：

```JavaScript
let obj = {
  valueOf: function () {
      console.log('valueOf');
      return {}; // object
  },
  toString: function () {
      console.log('toString');
      return {}; // object
  }
}

console.log(obj + obj);  // valueOf -> toString -> error!
```

#### Number、String、Boolean三个包装对象的 toValue 和 toString
包装对象是 JS 为原始数据类型数字、字符串、布尔专门设计的对象，所有的这三种原始数据类型所使用到的属性与方法，都是在这上面所提供。

包装对象的`valueOf`与`toString`的两个方法在原型上有经过覆盖，所以它们的返回值与一般的 Object 的设计不同:

* `valueOf`方法返回 对应的原始数据类型值
* `toString`方法返回 对应的原始数据类型值，转换为字符串类型时的字符串值 
`toString`方法会比较特别，这三个包装对象里的`toString`的说明如下：

* Number 包装对象的`toString`方法：可以有一个传参，可以决定转换为字符串时的进位(2、8、16)
* String 包装对象的`toString`方法：与 String 包装对象中的`valueOf`相同返回结果
* Boolean 包装对象的`toString`方法：返回"true"或"false"字符串 
另外，常被搞混的是直接使用`Number()`、`String()`与`Boolean()`三个强制转换函数的用法，这与包装对象的用法不同，包装对象是必须使用`new`关键字进行对象实例化的，例如`new Number(123)`，而`Number('123')`则是强制转换其他类型为数字类型的函数。

`Number()`、`String()`与`Boolean()`三个强制转换函数，所对应的就是在 ECMAScript 标准中的`ToNumber`、`ToString`、`ToBoolean`三个内部运算转换的对照表。而当它们要转换对象类型前，会先用上面说的`ToPrimitive`先转换对象为原始数据类型，再进行转换到所要的类型值。

不管如何，包装对象很少会被使用到，一般我们只会直接使用原始数据类型的值。而强制转换函数因为也有替换的语法，它们会被用到的机会也不多。


#### Array 和 Function 中的 toValue 和 toString
Array(数组)很常用到，虽然它是个对象类型，但它与 Object 的设计不同，它的`toString`有覆盖。

数组的`valueOf`与`toString`的两个方法的返回值如下：

* `valueOf`方法返回 对象本身。(与 Object 一样)
* `toString`方法返回 相当于用数组值调用`join(',')`所返回的字符串。也就是`[1,2,3].toString()`会是"1,2,3"，这点要特别注意。 
Function 对象很少会用到，它的`toString`也有被覆盖。Function 对象的`valueOf`与`toString`的两个方法的返回值如下：

* `valueOf`方法返回 对象本身。(与Object一样)
* `toString`方法返回 函数中包含的代码转为字符串值 
#### Date 对象的 valueOf 与 toString
Date 对象的`valueOf`与`toString`的两个方法的返回值：

* `valueOf`方法返回 给定的时间转为 UNIX 时间(自1 January 1970 00:00:00 UTC起算)，但是以微秒计算的数字值
* `toString`方法返回 本地化的时间字符串 
#### Symbols 类型
ES6 中新加入的 Symbols 数据类型，它不算是一般的值也不是对象，它并没有内部自动转型的设计，所以完全不能直接用于加法运算，使用时会报错。

### 实例
#### 空数组相加

```JavaScript
[] + []   // ""
```

两个数组相加，依然按照`valueOf -> toString`的顺序，但因为`valueOf`是数组本身，所以会以`toString`的返回值才是原始数据类型，也就是空字符串，所以这个运算相当于两个空字符串在相加，依照加法运算规则第2步骤，是字符串连接运算(concatenation)，两个空字符串连接最后得出一个空字符串。

#### 空对象相加

```JavaScript
{} + {}   // "[object Object][object Object]"
```

两个空对象相加，依然按照`valueOf -> toString`的顺序，但因为`valueOf`是对象本身，所以会以`toString`的返回值才是原始数据类型，也就是"[object Object]"字符串，所以这个运算相当于两个"[object Object]"字符串在相加，依照加法运算规则第 2 步骤，是字符串连接运算(concatenation)，最后得出一个"object Object"字符串。

但是这只是在 Chrome 浏览器上的结果(v55)，怎么说呢？

有些浏览器例如 Firefox、Edge 浏览器会把`{} + {}`直译为相当于`+{}`语句，因为它们会认为以花括号开头(`{`)的，是一个区块语句的开头，而不是一个对象字面量，所以会认为略过第一个`{}`，把整个语句认为是个`+{}`的语句，也就是相当于强制求出数字值的`Number({})`函数调用运算，相当于`Number("[object Object]")`运算，最后得出的是 NaN。

如果在第一个(前面)的空对象加上圆括号(`()`)，或是分开来先声明对象的变量值，这样 JS 就会认为前面是个对象，就可以得出同样的结果：

```JavaScript
({}) + {}   // "[object Object][object Object]"

let foo = {}, bar = {};
foo + bar;  // "[object Object][object Object]"
```

> 注 1：上面说的行为这与加号运算的第一个(前面)的对象字面值是不是个空对象无关，就算是里面有值的对象字面，例如`{a:1, b:2}`，也是同样的结果。
>
> 注 2：上面说的 Chrome 浏览器是在 v55 版本中的主控台直接运行的结果。其它旧版本有可能并非此结果。

#### 空对象 + 空数组
上面同样的把`{}`当作区块语句的情况又会发生，不过这次所有的浏览器都会有一致结果，如果`{}`(空对象)在前面，而`[]`(空数组)在后面时，前面(左边)那个运算元会被认为是区块语句而不是对象字面量。

所以`{} + []`相当于`+[]`语句，也就是相当于强制求出数字值的`Number([])`运算，相当于`Number("")`运算，最后得出的是数字 0。

```JavaScript
{} + []   // 0

[] + {}   // "[object Object]"
```

> 特别注意：所以如果第一个(前面)是`{}`时，后面加上其他的像数组、数字或字符串，这时候加号运算会直接变为一元正号运算，也就是强制转为数字的运算。这是个陷阱要小心。

#### Date 对象
Date对 象上面有提及是首选类型为"字符串"的一种异常的对象，在进行加号运算时时，它会优先使用`toString`来进行转换，最后必定是字符串连接运算(concatenation)。

> 这与其他的对象的行为不同(一般对象会先调用`valueOf`再调用`toString`)。

例如以下的结果：

```JavaScript
1 + (new Date())   // "1Sat Mar 11 2017 12:33:05 GMT+0800 (CST)"
```

要得出 Date 对象中的`valueOf`返回值，需要使用一元加号(+)，来强制转换它为数字类型，例如以下的代码：

```JavaScript
+new Date()    // 1489206830833
```

### 转摘
[JS的{} + {}与{} + []的结果是什么？](https://segmentfault.com/a/1190000008038678)


