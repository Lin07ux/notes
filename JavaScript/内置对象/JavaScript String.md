## 一、基础

String 类型是字符串的对象包装类型，可以和普通对象一样进行创建。

JavaScript 里的字符串类似于数组，都是一个一个字符拼凑在一起组成的，因此可以用`length`属性取得字符串的长度。也能够像使用数组一样，通过索引获取字符串指定位置的字符。

比如：

```javascript
var string = new String('abcdefg');
console.log(string.length);   // 7
console.log(string[2]);       // "c"
```

需要注意的是，即使字符串中包含双字节字符(不是占一个字节的 ASCII 字符)，每个字符也仍然算一个字符。所以 String 对象上的 length 属性有时候并不准确。

## 二、方法

String 对象的`valueOf()`、`toString()`和`toLocalString()`方法都会返回对象所表示的基本字符串值。

String 对象上还有很多方法，用于完成对字符串的解析和操作。**这些方法都不会改变源字符串自身，返回的结果都是源字符串的一个副本**。

### 2.1 charAt()/charCodeAt()

语法：`String.charAt(n)`、`String.charCodeAt(n)`

这两个方法都接受一个参数，用于指定要操作的字符的位置，从 0 开始。

其中：

* `charAt()` 返回字符串的第 n 个字符，如果参数 n 不在`0~str.length-1`之间，则返回一个空字符串。其作用类似于通过数组方式访问字符串，只是对于不存在的索引位置，`charAt()`返回的是一个空字符串。
* `charCodeAt()` 返回字符串的第 n 个字符的字符编码，相当于先用`charAt()`获取指定位置的字符，然后将这个字符转换成 ASCII 编码值返回。如果获取的字符不存在，则返回 NaN。

```javascript
var str = "javascript";
str.charAt(2);  // 'v'
str.charAt(12); // ''
str[12];        // undefined

str.charCodeAt(2);  // 118
str.charCodeAt(12); // NaN
```

### 2.2 indexOf()/lastIndexOf()

语法：`String.indexOf(subString[, start])`、`String.lastIndexOf(subString[, start])`

这两个方法都是用来查找字符串中子字符串的位置：

* `indexOf()` 返回子字符串在原字符串中首次出现的位置，从 start 位置开始，*向后查找*。如果不存在，则返回 -1。 
* `lastIndexOf()` 返回子字符串在原字符串中*最后出现的位置*，从 start 位置开始，*向前查找*，如果不存在，则返回 -1。、

这两个方法的参数如下：

* `substring` String，必须。表示要在原字符串中进行查找的子字符串
* `start` Integer，可选。表示查找的起始位置，可以是任意整数，默认值为 0。

```javascript
var str = "javascript";
str.indexOf('s');     // 4
str.indexOf('s', 6);  // -1
str.indexOf('', 8);   // -1

str.lastIndexOf('a');    // 3
str.lastIndexOf('a', 2); // 1
str.lastIndexOf('', 8);  // -1
```

### 2.3 concat()

语法：`String.concat(str1[...])`

该方法用于将当前字符串与一个或多个字符串拼接起来，并返回拼接后的新字符串。它可以接受任意多个参数。

这个方法的功能和直接对字符串使用`+`操作符的效果是一样的。一般情况也都是使用`+`操作符，更简便。

比如：

```JavaScript
var stringValue = 'hello';
var result = stringValue.concat(' ', 'world');

console.log(stringValue); // hello
console.log(result);      // hello world
```

### 2.4 substring()

语法：`String.substring(start[, end])`

返回从 start 到 end（不包括）之间的字符(范围集合是`[start, end)`)，start、end 均为 非负整数。若结束参数 end 省略，则表示从 start 位置一直截取到最后。

* 如果 start < 0，那么 start 会被重置为 0；
* 如果 start >= 0，end < 0，那么相当于从 0 截取到 start 位置(包含 start 处的字符)；
* 如果 start < 0，end < 0，那么结果总是空字符串''，即便 start 和 end 是 -0。其实这个也是满足上一个条件的特殊情况。

```javascript
var str = 'abcdefg';
str.substring(1, 4);   // "bcd"
str.substring(1);      // "bcdefg"
str.substring(-1);     // "abcdefg"   传入负值时会视为0
str.substring(-1, 2);  // "ab"
str.substring(-0, -1); // ''
```

### 2.5 substr()

语法：`String.substr(start[, length])`

返回字符串中从指定位置开始到指定长度的子字符串，start 可为负值，这是其和`substring()`不同的地方。start 为负值的时候，是从字符串的末尾开始计数，最后一个字符的索引是 -1。

```javascript
var str = "Just give me a reason";
str.substr(5, 10);  // "give me a "
str.substr(-4, 2);  // "as"
```

### 2.6 slice()

语法：`String.slice(start[, end])`

返回从 start 到 end （不包括）之间的字符，可传负值。传负值的时候，表示从字符串末尾开始计数，最后一个字符的索引为 -1。

```javascript
var str = 'this is awesome';
str.slice(4, -1);  // " is awesom"
```

### 2.7 replace()

语法：`String.replace(regexp|substr, newSubStr|function)`

参数：

1. `regexp|substr` 第一个参数可以是 RegExp 对象或字符串（这个字符串就被看做一个平凡的字符串）。
2. `newSubStr|function` 第二个参数可以是一个字符串或回调函数。

返回值：返回用第二个参数(或函数返回值)替换字符串中匹配第一个参数对应的子字符串后的结果。不会改变原来的字符串。

需要注意的是：

* 第一个参数为字符串的时候，只会替换第一个子字符串；如果是正则对象，而且指定了`g`修饰符，则会替换全部匹配正则的地方，否则只替换第一处。
* 第二个参数为字符串的时候，可以使用一些特殊的字符序列，引用正则表达式捕获组的值，如下：

    * `$n`：匹配第 n 个捕获组的内容，n 取0-9
    * `$nn`：匹配第 nn 个捕获组内容，nn 取 01-99
    * ``$` ``：匹配子字符串之后的字符串
    * `$'`：匹配子字符串之前的字符串
    * `$&`：匹配整个模式得字符串
    * `$$`：表示`$`符号

* 第二个参数是一个函数的时候，这个函数要返回一个字符串，表示要替换掉的匹配项。而且：

    * 在只有一个匹配项的情况下，会传递 3 个参数给这个函数：模式的匹配项、匹配项在字符串中的位置、原始字符串
    * 在有多个捕获组的情况下，传递的参数是模式匹配项、第一个捕获组、第二个、第三个...最后两个参数是模式的匹配项在字符串位置、原始字符串

```javascript
var str = "do you love me";
str.replace('love', 'hate');  // "do you hate me"
```

### 2.8 search()

语法：`String.search(regexp)`

查找字符串与一个正则表达式是否匹配。如果匹配成功，则返回正则表达式在字符串中首次匹配项的索引；否则，返回 -1。*如果参数传入的是一个非正则表达式对象，则会使用 new RegExp(obj) 隐式地将其转换为正则表达式对象。*

```javascript
var str = 'I love JavaScript!';
str.search(/java/);  // -1
str.search(/Java/);  // 7
str.search(/java/i); // 7
str.search('Java');  // 7
```

### 2.9 match()

语法：`String.match(regexp)`

参数：接受一个参数，表示进行匹配的正则表达式。如果参数传入的是一个非正则表达式对象，则会使用`new RegExp(obj)`隐式地将其转换为正则表达式对象。

返回一个包含匹配结果的数组，如果没有匹配项，则返回 null。

在字符串上调用`match`方法，本质上和在正则上调用`exec`相同，但是`match`方法返回的结果数组是没有`input`和`index`属性的。

```javascript
var str = 'Javascript java';
str.match(/Java/);   // ["Java"]
str.match(/Java/gi); // ["java", "Java"]
str.match(/ab/g);    // null
```

### 2.10 split()

语法：`String.split([separator][, limit])`

返回一个数组，使用分隔符 separator 来分割字符串，分割的生成的每个结果都作为返回数组的一部分。分隔符可以是一个字符串或正则表达式。如果设置了 limit 参数，那么返回的数组中，最多包含 limit 个元素。

```javascript
var str = "Hello?World!";
str.split();   // ["Hello?World!"]
str.split(''); // ["H", "e", "l", "l", "o", "?", "W", "o", "r", "l", "d", "!"]
str.split('?');  // ["Hello", "World!"]
str.split('',5); // ["H", "e", "l", "l", "o"]
```

### 2.11 trim()

语法：`String.trim()`

去除字符串开头和结尾处的空白字符。

```javascript
var str = '   abc  ';
str.trim();        // 'abc'
console.log(str);  // '   abc  '
```

### 2.12 toLowerCase()/toUpperCase()/toLocalLowerCase()/toLocalUpperCase()

语法：`String.toLowerCase()`、`String.toUpperCase()`、`String.toLocalLowerCase()`、`String.toLocalUpperCase()`

这几个方法分别返回字符串小写或大写格式的副本。

其中，`toLocalLowerCase()`和`toLocalUpperCase()`方法用于将字符串转成本地区的大小写格式，一般和对应的`toLowerCase()`、`toUpperCase()`的结果相同，仅在特定地区有所区别。不确定的时候，可以直接使用带有 local 的方法更稳妥。

比如：

```javascript
var str = 'JavaScript';

str.toLowerCase(); // 'javascript'
str.toUpperCase(); // 'JAVASCRIPT'
```

### 2.13 localCompare()

语法：`String.localCompare(str)`

这是一个与地区有关的字符串比较方法，它会根据本地区字母表的排序，依次比较当前字符串与参数 str 的相应位置的字符的大小。比如，首先取两个字符串的第一个字符，如果本字符串的第一个字符大于参数 str 的第一个字符，则立即返回正数；如果本字符串的第一个字符小于参数 str 的第一个字符，则返回负数；否则，两个字符相等，继续比较两个字符串的下一个字符，直到某个字符的结尾，或者不相等。如果都比完了依旧相等，则返回 0。

该方法的返回值与字符在字母表中的先后顺序有关：

* 如果本字符串在字符表中应排在字符串参数之前，则返回一个负数(一般是 -1，但并非强制)
* 如果本字符串等于字符串参数，则返回 0
* 如果本字符串在字符表中应牌子字符串参数之后，则返回一个正数(一般是 1，但并非强制)

比如：

```JavaScript
var stringValue = 'yellow';

console.log(stringValue.localCompare('brick'));  // 1
console.log(stringValue.localCompare('yellow')); // 0
console.log(stringValue.localCompare('zoo'));    // -1
```

### 2.14 fromCharCode()

语法：`String.fromCharCode()`

这是 String 构造函数的一个静态方法，不可在字符串对象上使用。

这个方法的功能是接收一个或多个字符编码，然后将它们转换成一个字符串。从本质上看，这个方法与字符串实例方法`charCodeAt()`执行的是相反的操作。

比如：

```JavaScript
String.fromCharCode(104, 101, 108, 108, 111); // "hello"
```

