通过 DOM 提供的 API，可以使用 JavaScript 来操作文档的样式，虽然并不推荐这样做，但这确实是可行的。

### style 属性

任何支持`style`属性的 HTML 元素在 JavaScript 中都有一个对应的`style`属性。这个属性是 cssStyleDelaration 的实例，包含着通过 HTML 的 style 属性指定的所有样式信息(HTML 中使用`style=""`方式定义的样式)，但不包含与外部样式表或嵌入样式表中经层叠而来的样式。

对于使用中划线的 CSS 属性名，必须将其转成驼峰大小写形式才能通过 JavaScript 访问。

> **注意**：IE8- 浏览器不支持属性名`cssFloat`，IE 浏览器支持属性`styleFloat`，所有浏览器都支持`float`属性。

```html
<div class="box" id="box">123</div>
```

```JavaScript
var oBox = document.getElementById('box');
// IE8-浏览器不支持该属性
oBox.style.cssFloat = 'right';
// IE浏览器支持该属性
oBox.style.styleFloat = 'right';
// 所有浏览器都兼容
oBox.style.float = 'right';
```

该属性下还有很多的其他属性和方法可供使用：

1. `cssText`
    
    通过这个属性能够访问到 style 特性中的 css 代码。在读模式下，cssText 返回浏览器对 style 特性中 css 代码的内部表现形式；在写模式中，赋给 cssText 的值会重写整个 style 特性的值。设置 cssText 是为元素应用多项变化最快捷的方法，因为可以依次性应用所有的变化。
    
    ```JavaScript
    oBox.style.cssText = "height:40px; width:40px; background-color:blue;"; 
    ```

2. `length` 返回应用到元素的 css 的属性的数量（只包含内联样式）。
    
    这个属性是为了和下面的`item()`方法配套使用，以便迭代在元素中定义的 css 属性，此时 style 对象实际上就相当于一个集合(IE8- 不支持)

3. `item(index) / item[index]` 返回给定位置的 css 属性的名称，也可以使用方括号语法。

4. `getPropertyValue(property)` 返回给定属性的字符串值(IE8- 不支持)。

5. `getPropertyPriority(property)`

    判断某个样式属性是否设置了重要。如果给定的属性使用了`!important`设置，则返回 "important"，否则返回空字符串(IE8- 不支持)。

    ```html
    <div class="box" id="box" style="height: 100px; background-color: red; width: 100px!important;">123</div>
    ```
    
    ```JavaScript
    var oBox = document.getElementById('box');
    	
    // IE8-浏览器输出undefined,其他浏览器输出3
    console.log(oBox.style.length);
    
    // IE9+浏览器输出width，IE8-浏览器输出大写的WIDTH，其他浏览器输出height
    // IE 浏览器的输出顺序并不是按照内联样式中定义的顺序
    console.log(oBox.style[0]);
    
    // IE8-浏览器不支持，其他浏览器输出100px
    console.log(oBox.style.getPropertyValue(oBox.style[0]));
    
    // IE8-浏览器不支持，其他浏览器输出空字符串
    console.log(oBox.style.getPropertyPriority('height'));
    
    // IE8-浏览器不支持，其他浏览器输出important
    console.log(oBox.style.getPropertyPriority('width'));
    ```

6. `getPropertyCSSValue(property)` 返回包含两个属性的 CSSRule 类型，这两个属性分别是：`cssText`和`cssVlaueType`。（只有 Safari 支持）

    `cssText`属性的值与`getPropertyValue()`返回的值相同，`cssValueType`属性则是一个数值常量，表示值的类型：
        
    - 0  表示继承的值
    - 1  表示基本的值
    - 2  表示值列表
    - 3  表示自定义的值

7. `removeProperty(property)` 从内联样式中删除给定的属性，并返回被删除属性高的属性值（IE8- 不支持）。
    
8. `setProperty(property, value, priority)` 将给定属性设置为相应的值，并可指定加上优先权标志("important"或一个空字符串) (IE8- 不支持)。

### getComputedStyle()

该方法用来获取应用到元素后的样式，也即是元素最终渲染的样式。参与计算的样式不仅仅是内联样式，还包括页面嵌入样式、外部引入样式。比如，假设某个元素并未设置高度而是通过其内容将其高度撑开，这时候要获取它的高度就要用到该方法。

语法：`window.getComputedStyle(element[, pseudoElt])`

参数：`element`是要获取样式的元素节点，`pseudoElt`指定一个伪元素进行匹配，也即是获取元素节点的伪元素的样式。

返回值：返回一个 CSSStyleDeclaration 对象。通过该对象可以访问到元素计算后的样式。

> 注意 1：对于`font`、`background`、`border`等复合样式，各浏览器处理不一样。chrome 和 opera 会返回整个复合样式，而 IE9+、firefox 和 safari 则什么都不输出。
>
> 注意 2：不论以什么格式设置颜色，浏览器都以`rgb()`或`rgba()`的形式输出。
> 
> 注意 3：所有计算的样式都是只读的，不能修改计算后样式对象中的 CSS 属性

### currentStyle

虽然 IE8- 浏览器不支持`getComputedStyle()`方法，但在 IE 中每个具有 style 属性的元素还有一个`currentStyle`属性，这个属性是 CSSStyleDeclaration 的实例，包含当前元素全部计算后的样式。

> **其他浏览器不支持这个属性。**

### CSSStyleSheet

该对象为样式表对象。可以使用`document.styleSheets`来获取文档中的样式表对象集合。

样式表集合具有如下的属性和方法：

1. `length` 表示有多少样式表，外部样式表中有一个`<link>`算一个，内部样式表中一个`<style></style>`算一个。
2. `item() / item[]` 表示第几个样式表，也可以使用方括号方法。`item()`方法中序号的顺序与在解析页面的顺序一致。

样式表对象具有如下的属性和方法：

1. `href` 如果样式表是通过`<link>`包含的外部样式表，则表示样式表的 URL；否则为 null。
2. `disabled` 表示样式表是否被禁用。布尔值卡类型。这个属性可读写，设置为 true 则会禁用样式表。
3. `media` 表示当前样式表支持的素有媒体类型的集合。在 IE8- 浏览器中输出 media 特性值的字符串。
4. `ownerNode` 表示拥有当前样式表的指针，返回`<link>`或`<style>`元素对象。IE8- 浏览器不支持。
5. `parentStyleSheet` 在当前样式表是通过`@import`导入的情况下，这个属性是一个指向当如它的样式表的指针，否则为 null。
6. `title` ownerNode 中 title 属性的值。
7. `type` 表示样式表类型的字符串。对于 CSS 样式表而言，这个字符串就是 "text/css"。IE8- 浏览器不支持。
8. `cssRules / rules` 表示样式表中包含的样式规则的集合。 前者 IE8- 浏览器不支持，后者 FireFox 浏览器不支持。兼容性的写法为`sheet.cssRules || sheet.rules;`。(对于`rules`属性，IE8- 浏览器不识别`@import`。)
9. `ownerRule` 如果样式表是通过`@import`导入的，这个属性就是一个指针，指向表示导入的规则；否则就是 null。（IE8- 浏览器不支持）
10. `deleteRule(index) / removeRule(index)` 删除 cssRules 集合中，指定位置的规则，无返回值。前者 IE8- 浏览器不支持，后者 FireFox 浏览器不支持。兼容写法：
    
    ```JavaScript
    function deleteRule(sheet, index) {
    	(typeof sheet.deleteRule == "function") ?
    	   sheet.deleteRule(index) :
    	   sheet.removeRule(index);
    }	
    ```
11. `insertRule(rule, index) / addRule(ruleKey, ruleValue, index)` 向 cssRules 集合中指定的位置处，插入`rule`字符串，前者返回当前样式表的索引值，后者返回 -1。前者 IE8- 浏览器不支持，后者 FireFox 浏览器不支持。兼容写法：  
    ```JavaScript
    function insertRule(sheet, ruleKey, ruleValue, index) {
    	return sheet.insertRule ? 
    		sheet.insertRule(ruleKey + '{' + ruleValue + '}', index) : 
    		sheet.addRule(ruleKey, ruleValue, index);
    }
    ```
### 实例

HTML 代码如下：

```html
<link rel="stylesheet" href="sheet1.css" media = "all" title="sheet1">
<style>
	@import url(sheet2.css);
	body{
	  	height: 100px;
	  	border: 10px solid black;
	}
</style>
```

JavaScript 操作代码如下：

```JavaScript
// 因为有外部和内部样式表各 1 个，所以结果为 2
console.log(document.styleSheets.length);

// 因为 <link> 标签在 <style> 标签上面，所以外部样式表为第 0 个，内部样式表为第 1 个
var oOut = document.styleSheets.item(0);
var oIn = document.styleSheets[1];

console.log(oOut.disabled);  // disabled 属性默认为 false
console.log(oOut.disabled = true); // 若设置 disabled 为 true 则禁用样式表

console.log(oOut.href); // 外部样式表显示 URL : sheet1.css
console.log(oIn.href); // IE8-浏览器什么都不输出，其他浏览器输出 null

// IE8- 浏览器输出字符串 "all"，其他浏览器输出一个 MediaList 对象
console.log(oOut.media); // MediaList 对象的第 0 项为 all
console.log(oIn.media);  // MediaList 对象的第 0 项为 undifined

// IE8- 返回 undefined，其他浏览器输出 <link>
console.log(oOut.ownerNode);
// IE8- 返回 undefined，其他浏览器输出 <style>
console.log(oIn.ownerNode);

console.log(oOut.parentStyleSheet); // null
console.log(oIn.parentStyleSheet);  // null

console.log(oOut.title); // sheet1
console.log(oIn.title);  // IE 浏览器什么都不输出，其他浏览器输出 null

console.log(oOut.type);  // text/css，IE8- 浏览器下什么都不输出
console.log(oIn.type);   // text/css，IE8- 浏览器下什么都不输出
```




