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

替换字符串中匹配第一个参数对应的子字符串，或者正则表达式匹配的子字符串为第二个参数 neSubStr。第二个参数可以是一个函数，从而动态的生成替换成的子字符串。

```javascript
var str = "do you love me";
str.replace('love','hate');  // "do you hate me"
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

返回一个包含匹配结果的数组，如果没有匹配项，则返回 null。如果参数传入的是一个非正则表达式对象，则会使用 new RegExp(obj) 隐式地将其转换为正则表达式对象。

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


