## 一、基础

字符串 String 类型是 JavaScript 中的一个基本类型。JavaScript 中，字符串是由一对单引号或者一对双引号包裹的字符集合。

JavaScript 里的字符串类似于数组，都是一个一个字符拼凑在一起组成的，因此可以用`length`属性取得字符串的长度。也能够像使用数组一样，通过索引获取字符串指定位置的字符，比如：

```javascript
var string = 'abcdefg';
console.log(string.length);   // 7
console.log(string[2]);       // 'c'
```

## 二、方法

字符串上都很多方法，其实这些方法都是 String 对象上的，在字符串调用这些方法的时候，会隐式的先将字符串转成 String 对象，然后再调用对象上的方法。

需要注意的是：**这些方法都不会改变源字符串自身，返回的结果都是源字符串的一个副本**。

### 2.1 charAt()

语法：`String.charAt(n)`

返回字符串的第 n 个字符，如果 参数 n 不在 0~str.length-1之间，则返回一个空字符串。

其作用类似于通过数组方式访问字符串，只是对于不存在的索引位置，`charAt()`返回的是一个空字符串。

```javascript
var str = "javascript";
str.charAt(2);  // 'v'
str.charAt(12); // ''
str[12];        // undefined
```

### 2.2 indexOf()

语法：`String.indexOf(subString[, start])`

返回 subString 在字符串 String 中首次出现的位置，从 start 位置开始查找，如果不存在，则返回 -1。 

start 可以是任意整数，默认值为 0。如果 start < 0 则查找整个字符串（如同传进了 0）。如果 start >= str.length，则该方法返回 -1，*除非被查找的字符串 subString 是一个空字符串，此时返回 str.length*。

```javascript
var str = "javascript";
str.indexOf('s');    // 1
str.indexOf('s',6);  // -1
str.indexOf('',11);  // 10
str.indexOf('',8);   // 8
```

### 2.3 lastIndexOf()

语法：`String.lastIndexOf(subString[, start])`

返回 subString 在字符串 String 中*最后出现的位置*，从 start 位置 向前开始查找，如果不存在，则返回 -1。和`String.indexOf()`方法类似。

```javascript
'javascript'.lastIndexOf('a'); // 3
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

### 2.12 toLowerCase()
语法：`String.toLowerCase()`

将字符串转换为小写，并返回一个副本，不影响字符串本身的值。

```javascript
var str = 'JavaScript';
str.toLowerCase(); // 'javascript'
console.log(str);  // 'JavaScript'
```

### 2.13 toUpperCase()
语法：`String.toUpperCase()`

将字符串转换为大写，并返回一个副本，不影响字符串本身的值。

```javascript
var str = 'JavaScript';
str.toUpperCase(); // 'JAVASCRIPT'
console.log(str);  // 'JavaScript'
```


