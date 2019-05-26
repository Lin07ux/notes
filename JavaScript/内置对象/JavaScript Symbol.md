## 简介

Symbol 是新的基本类型，从此 JavaScript 有 7 种类型：

* Number
* String
* Boolean
* undefined
* null
* Object
* Symbol

Symbol 唯一的用途就是标识对象属性，表明对象支持的功能。 相比于字符属性名，Symbol 的区别在于唯一，可避免名字冲突。 这样 Symbol 就给出了唯一标识类型信息的一种方式，从这个角度看有点类似 C++ 的Traits。

## 使用

常见使用方式如下：

```JavaScript
const symbol1 = Symbol();
const symbol2 = Symbol(42);
const symbol3 = Symbol('foo');

console.log(typeof symbol1);
// expected output: "symbol"

console.log(symbol3.toString());
// expected output: "Symbol(foo)"

console.log(Symbol('foo') === Symbol('foo'));
// expected output: false
```

除了可以使用内建的 Symbol 来自定义一些行为，还可以使用 Symbol 来达到 JavaScript 中实现的功能。

### 常量枚举

JavaScript 没有枚举类型，常量概念也通常用字符串或数字表示。例如：

```JavaScript
const COLOR_GREEN = 1
const COLOR_RED = 2

function isSafe(trafficLight) {
    if (trafficLight === COLOR_RED) return false
    if (trafficLight === COLOR_GREEN) return true
    throw new Error(`invalid trafficLight: ${trafficLight}`)
}
```

虽然定义了常量，但是方法的调用依旧可以使用其他等同的值调用，或者直接传入原始值。如果使用 Symbol 定义常量，则就不会出现这个问题了。

```JavaScript
const COLOR_GREEN = Symbol('green')
const COLOR_RED = Symbol('red')
```

### 私有属性

由于没有访问限制，JavaScript 曾经有一个惯例：私有属性以下划线起始来命名。 这样不仅无法隐藏这些名字，而且会搞坏代码风格。 可以利用 Symbol 来隐藏这些私有属性：

```JavaScript
let speak = Symbol('speak')
class Person {
    [speak]() {
        console.log('harttle')
    }
}
```

如下几种访问都获取不到 speak 属性：

```JavaScript
let p = new Person()

Object.keys(p)                      // []
Object.getOwnPropertyNames(p)       // []
for(let key in p) console.log(key)  // <empty>
```

但 Symbol 只能隐藏这些函数，并不能阻止未授权访问。 仍然可以通过`Object.getOwnPerpertySymbols()`和`Reflect.ownKeys(p)`来枚举到 Symbol 属性。

## 转换

* 转换为字符串：可以使用`symbolObj.toString()`或`String(symbol)`转成字符串，但不能通过`+`转换为字符串，也不能直接用于模板字符串输出。后两种情况都会产生`TypeError`，这是为了避免把它当做字符串属性名来使用。
* 转换为数字：**不可转换为数字**。`Number(symbol)`或四则运算都会产生`TypeError`。
* 转换为布尔：`Boolean(symbolObj)`和取非运算都可以，总是返回 true。这是为了方便判断是否包含属性。
* 转换为对象：Symbol 是基本类型，但不能用`new Symbol(sym)`来包裹成对象，需要使用`Object(sym)`。除了判等不成立外，包裹对象的使用与原 Symbol 实例几乎相同。

```JavaScript
let sym = Symbol('author')
let wrapped = Object(sym)
let obj = {
    [sym]: 'harttle'
}

s12 === s1                  // false
s12 == s1                   // true
wrapped instanceof Symbol   // true，真的是true!!!
obj[sym]                    // 'harttle'
obj[wrapped]                // 'harttle'
```

## 内建 Symbol

JavaScript 内建了一些在 ECMAScript 5 之前没有暴露给开发者的 symbol，它们代表了内部语言行为。更多内建 symbol 可以查看 [MDN Symbol 文档](https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Global_Objects/Symbol#Well-known_symbols)。

### Symbol.iterator

所有支持迭代的对象（比如 Array 、 Map 、 Set）都要实现`@@iterator`方法，也就是`Symbol.iterator`。只要实现了`Symbol.iterator`那么这个对象就是可迭代的了。可以在任意对象中定义该方法以便进行迭代。

```JavaScript
if (Symbol.iterator in arr) {
    for(let n of arr) console.log(n)
}
```

### Symbol.match

`Symbol.match`在`String.prototype.match()`中用于获取 RegExp 对象的匹配方法。可以改写`Symbol.match`标识的方法，以自定义字符串的匹配。下面的例子来自 MDN：

```JavaScript
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp/@@match
class RegExp1 extends RegExp {
  [Symbol.match](str) {
    var result = RegExp.prototype[Symbol.match].call(this, str);
    
    return result ? 'VALID' : 'INVALID';
  }
}

console.log('2012-07-02'.match(new RegExp1('([0-9]+)-([0-9]+)-([0-9]+)')));
// expected output: "VALID"
```

### Symbol.toPrimitive

`Symbol.toPrimitive`属性用来定义将对象转换为基本类型的行为。

在对象进行运算时经常会变成`"[object Object]"`，这是对象转换为字符串（基本数据类型）的默认行为，定义在`Object.prototype.toString()`。

可以自定义对象的`Symbol.toPrimitive`属性以实现自定义转换，如下所示：

```JavaScript
var count = {
    value: 3
};
count + 2     // "[object Object]2"

count[Symbol.toPrimitive] = function () {
    return this.value
};
count + 2     // 5
```

### Symbol.replace

定义一个替换匹配字符串的子串的方法，使用`String.prototype.replace()`。

### Symbol.search

定义一个返回一个字符串中与正则表达式相匹配的索引的方法。使用`String.prototype.search()`。

### Symbol.split

定义一个在匹配正则表达式的索引处拆分一个字符串的方法。使用`String.prototype.split()`。

## 跨 Realm 使用

JavaScript Realm 是指当前代码片段运行的上下文，包括全局变量，比如 Array、Date 这些全局函数。在打开新标签页、加载 iframe 或加载 Worker 进程时，都会产生多个 JavaScript Realm。

跨 Realm 通信时这些全局变量是不同的，例如从 iframe 中传递给数组 arr 给父窗口，父窗口中收到的`arr instanceof Array`为 false，因为它的原型是 iframe 中的那个 Array 。

但是一个对象在 iframe 中可以迭代（Iterable），那么在父窗口中也应当能被迭代。这就要求 Symbol 可以跨 Realm，当然`Symbol.iterator`可以。如果自定义的 Symbol 也需要跨 Realm，请使用 Symbol Registry API：

```JavaScript
// 在 Symbol Registry 中注册一个跨 Realm Symbol
let sym = Symbol.for('foo')
// 获取 Symbol 的键值字符串
Symbol.keyFor(sym)      // 'foo'
```

内置的跨 Realm Symbol 其实不在 Symbol Registry 中：

```JavaScript
Symbol.keyFor(Symbol.iterator)  // undefined
```


