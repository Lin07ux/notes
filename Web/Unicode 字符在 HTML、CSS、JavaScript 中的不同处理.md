Unicode 字符在 HTML、CSS 和 JavaScript 中的表示方法均不相同：

### CSS 中的表示
CSS 中一般会在伪元素中使用 Unicode 字符，用于显示一个特殊的字符或 icon。

语法：`'\ + 16进制的unicode编码'`

比如，一段很常见的 bootstrap 的字体图标代码：

```css
.glyphicon-home:before {
    content: "\e021";
}
```

上面代码中的`e021`就是这个字符的 Unicode 码，是 16 进制。

### JavaScript 中的表示
JavaScript 和 CSS 中的语法很像，只是多了一个字母`u`。

语法：`'\u + 16进制的unicode编码'`

```javascript
// 如：'\u5b89'表示汉字“安”
console.log('\u5b89'); // 输出“安”
```

JavaScript 中，可以使用`charCodeAt()`或者`codePointAt()`方法来获取字符的 Unicode 码，结果用十进制表示：

```javascript
'安'.charCodeAt();   // 23433
'安'.codePointAt();  // 23433
```

由于获取到的是十进制数，所有如果想在 js 和 css 里面用的话，就需要用`toString(16)`转 16 进制，然后再做进一步处理了：

```javascript
// 输出字符串："\u8317"
var unicode = '\\u' + '茗'.charCodeAt().toString(16);
// 输出汉字："茗"
JSON.parse('"' + unicode + '"');
// 或者使用eval解析也可以
eval('"' + unicode + '"');
```

### HTML 中的表示
HTML 特殊一点，使用的是 10 进制，而且格式也不太一样。

语法：`'&# + 10 进制的 unicode 编码 + 英文分号;'`

比如，`&#23433;`表示中文中的“安”。

另外，HTML 一些特殊字符还有其它表示，也就是常说的 HTML 转义字符，如：`$nbsp;`表示`&#160;`，也就是空格。

完整的 HTML 转义字符可以看这里：[站长工具](http://tool.oschina.net/commons?type=2)。

