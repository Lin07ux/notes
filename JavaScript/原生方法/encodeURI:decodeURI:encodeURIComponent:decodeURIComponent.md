JavaScript 内置了四个 URI 编码和解码方法，这些方法能够对 URI 中的特殊字符进行编码和解码，以便浏览器和 JavaScript 代码操作。

### 1. encodeRUI()/encodeURIComponent()

有效的 URI 中不能包含某些字符，而通过这两个方法可以将这些无效字符用特殊的 UTF-8 编码替换，从而让浏览器能够接受和理解。其中：

* `encodeURI()` 主要用于整个 URI(包含协议头、域名、端口等)的编码，它不会对本身属于 URI 的特殊字符(如`:/?#`)进行编码，而只会将空格替换成`%20`。
* `encodeURIComponent()` 主要用于对 URI 中的某一段(如查询参数)进行编码，对任何非字母数字字符都会进行编码。

比如：

```JavaScript
var uri = 'http://www.wrox.com/illegal value.html#start';

encodeURI(uri); // "http://www.wrox.com/illegal%20value.html#start"
encodeURIComponent(uri); // "http%3A%2F%2Fwww.wrox.com%2Fillegal%20value.html%23start"
```

### 2. decodeURI()/decodeURIComponent()

这两个方法是前面两个方法的匿方法，他们可以对已经编码过的 URI 进行解码，得到原始的 URI 字符串。其中：

* `decodeURI()` 只能对使用`encodeURI()`后替换的字符进行解码，也就是只会将`%20`替换成空格，但对`%23`就不会替换成`#`了。
* `decodeURIComponent()` 对使用`decodeURIComponent()`编码所有的字符都会解码，即它可以解码任何特殊字符的编码。

比如：

```JavaScript
var uri = 'http%3A%2F%2Fwww.wrox.com%2Fillegal%20value.html%23start'

decodeURI(uri); // http%3A%2F%2Fwww.wrox.com%2Fillegal value.html%23start
decodeURIComponent(uri); // http://www.wrox.com/illegal value.html#start
```


