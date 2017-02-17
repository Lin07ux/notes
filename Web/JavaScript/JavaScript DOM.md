DOM 是面向 HTML 和 XML 文档的 API，为文档提供了结构化表示，并定义了如何通过脚本来访问文档结构。DOM 独立于具体的编程语言(通常通过 JavaScript 访问 DOM，不过并不严格要求这样)，可以使用任何脚本语言来访问 DOM，这要归功于其一致的 API。

文档中的每个元素都是 DOM 的一部分，这就使得 JavaScript 可以访问元素的属性和方法。DOM1 级定义了一个 Node 接口，该接口将由 DOM 中的所有节点类型实现。

> 这个 Node 接口在 JavaScript 中是作为 Node 类型实现的，而在 IE8- 浏览器中的所有 DOM 对象都是以 COM 对象的形式实现的。
> 在现代浏览器中，Node 对象具有一些基本的属性，常用的比如节点的类型值等。

## 节点

DOM 是由一个个的节点构成的。DOM 针对节点提供了大量的方法和属性，以便更好的处理节点。

### 节点类型

每个节点都有一个`nodeType`属性，用于表明节点的类型。节点类型在 Node 类型中定义下列 12 个数值常量来表示：

* `Node.ELEMENT_NODE = 1`   元素节点

* `Node.ATTRIBUTE_NODE = 2` 属性节点

*	`Node.TEXT_NODE = 3` 	  文本节点

* `Node.CDATA_SECTION_NODE = 4`    CDATA 区段(只出现在 XML 文档中，表示的是 CDATA 区域)

* `Node.ENTITY_REFERENCE_NODE = 5` 实体引用

* `Node.ENTITY_NODE = 6` 	  实体

* `Node.PROCESSING_INSTRUCTION_NODE = 7` 处理指令

* `Node.COMMENT_NODE = 8`   注释节点

* `Node.DOCUMENT_NODE = 9`  文档节点(在 HTML 中就是`document`对象的类型)

* `Node.DOCUMENT_TYPE_NODE = 10`     文档类型节点(在 HTML 中就是顶部首行的文档类型声明语句的类型，如`<!DOCTYPE html>`，可以通过`document.firstChild`来访问)

* `Node.DOCUMENT_FRAGMENT_NODE = 11` 文档片段节点(该类型在文档中没有对应的标记，是一种轻量级的文档)

* `Node.NOTATION_NODE = 12` 标记节点(DTD 中声明的符号)

常用的为前三个节点类型。


### 节点属性

* `childNodes` 返回当前节点所有子节点的数组(返回值类型为 NodeList，包含元素节点和文本节点)

* `firstChild` 返回当前节点的第一个下级子节点

* `lastChild`  返回当前节点的最后一个子节点

* `nextSibling` 返回紧跟在当前节点后面的节点

* `nodeName` 节点的名称(元素节点返回元素标签名称的大写字符串，文本节点返回'#text'，属性节点返回属性名称，文档节点返回'#document')

* `nodeValue` 节点的值(元素节点总是为 null 且只读，文本节点返回文本值且可读写，属性节点返回属性的值且可读写)

* `nodeType`  节点的类型(12 种节点类型中的一种)

* `parentNode` 返回节点的父节点 

* `previousSibling` 返回紧邻当前节点的前面的节点

还有一些其他的属性，这些属性和节点的类型有关，比如，文本节点具有`textContent`、`wholeText`属性，元素节点则没有；而元素节点具有很多的事件属性和位置属性等，文本元素就没有。

下面是一些 *元素节点* 中比较常用的属性：

* `innerHTML` 返回该节点中全部子节点的 HTML 代码。可读写，可用来改变该节点元素内的代码

* `innerText` 返回全部子节点中的文本内容。可读写，赋值后，该元素内部的 HTML 代码奖杯清除，并用新的文本内容填充。赋值的文本内容中即便有 HTML 也会被转义，从而保证能显示成文本。

* `attributes` 返回元素节点的全部属性组成的 NamedNodeMap 对象。可按照数组方式进行访问，每个子元素均为属性节点对象，其中也会包含节点的共有属性。


### 节点方法

* `hasChildNodes()` 返回一个布尔值，指示元素是否有子元素。


### 获取元素节点
可以通过如下的几个方法来获取元素节点：

* `document.getElementById(id)` 获取有指定惟一 ID 属性值文档中的元素。

* `<NodeElement>.getElementsByTagName(name)` 返回有指定标记名的元素的数组，返回值是一个即时的 HTMLCollection 类型。如果不存在指定标签的元素，该接口返回的不是 null，而是一个空的 HTMLCollection。可以传递一个星号`*`来获取元素中的所有子元素节点。

* `<NodeElement>.getElementsByClassName(class)` 返回有指定类名的元素的数组，返回值是一个即时的 HTMLCollection 类型。如果不存在指定类的元素，该接口返回的不是 null，而是一个空的 HTMLCollection。可以传入多个类名(使用空格连接成字符串)，匹配的时候会忽略传入的类名的次序。

* `<NodeElement>.querySelector()` w3c 规范新定义的方法，接受选择符(#id/.class/tagName)作为自己的参数，选中符合参数的第一个元素

* `<NodeElement>.querySelectorAll()` w3c 规范新定义的方法，接受选择符(#id/.class/tagName)作为自己的参数，选中符合参数的所有元素。返回一个即时的 HTMLCollection。

这里，**即时性**表示的是仅在进行选择的时候出现在文档中的复合条件的元素集合，在选择之后，通过 JavaScript 等方式新添加如文档中的元素不会出现在这个集合中。

注意：上面的五个方法中，**`getElementById()`方法只存在于`document`对象中，不能由其他的元素节点进行调用**。


### 获取、设置和删除元素的属性

这三个方法属于元素，不能被`document`调用。

* `<NodeElement>.getAttribute(attribute)` 返回元素的属性值，属性由 attribute 参数指定。如果未设置对应的属性，则返回 null。

* `<NodeElement>.setAttribute(attribute, value)` 设置元素对象的属性的值。如果元素中未设置过该属性，则会先创建该属性，再设置值；如果已经设置过，则会覆盖之前设置的值。

* `<NodeElement>.removeAttribute(name)`  从元素中删除属性 name

一般来说，元素节点中的属性都能像对象属性一样进行调用，但是`class`属性则不行。因为`class`是 JavaScript 中的一个关键字，不能用于变量名、属性名等，此时就需要使用`className`来获取元素的类名。

另外需要注意的是，通过`getAttribute()`方法获取属性值的时候，如果属性未设置，则会返回 null，如果设置了属性，而没有设置值，则会返回空字符串(有些属性是例外的)；通过属性方式访问则不一样，不论是没有设置属性，还是没有设置属性值，都会返回空字符串。

同样，通过给对象属性赋值的方式，也可以给元素设置属性值，效果和`setAttribute()`方法的效果相同。

> IE 在`setAttribute()`上有很大的问题，最好尽可能使用属性赋值方式。

### 动态更改节点

* `document.createElement(tagName)`  创建由 tagName 指定的元素

* `document.createDocumentFragment()` 创建文档碎片节点，不需要参数

* `document.createTextNode(text)`  创建一个包含静态文本的节点

* `<element>.appendChild(childNode)`  将指定的节点增加到当前元素的子节点列表的最末处(作为一个新的子节点)

* `<element>.insertBefore(newNode, targetNode)`  将节点 newNode 作为当前元素的子节点插到 targetNode 元素前面

* `<element>.removeChild(childNode)`  从元素中删除子元素 childNode

* `<element>.replaceChild(newNode, oldNode)`  将节点 oldNode 替换为节点 newNode

一旦把节点添加到`document.body`(或者它的后代节点)中，页面就会更新并反映出这个变化。对于少量的更新，这是很好的。然而，当要向`document`添加大量数据时，如果逐个添加这些变动，这个过程有可能会十分缓慢。为解决这个问题，可以创建一个文档碎片，把所有的新节点附加其上，然后把文档碎片的内容一次性添加到`document`中。这就是`document.createDocumentFragment()`方法的作用。

### 获取元素样式

#### getComputedStyle

该方法用来获取应用到元素后的样式，也即是元素最终渲染的样式。比如，假设某个元素并未设置高度而是通过其内容将其高度撑开，这时候要获取它的高度就要用到该方法。

语法：`window.getComputedStyle(element[, pseudoElt])`

参数：`element`是要获取样式的元素节点，`pseudoElt`指定一个伪元素进行匹配，也即是获取元素节点的伪元素的样式。

返回值：返回一个 CSSStyleDeclaration 对象。通过该对象可以访问到元素计算后的样式。

#### getBoundingClientRect

该方法用来返回元素的大小以及相对于浏览器可视窗口的位置。

语法：`element.getBoundingClientRect()`

返回值：返回值是一个 DOMRect 对象，包含`left`、`top`、`right`、`bottom`、`height`和`width`属性，含义分别如下：
    
- `left` 元素左边界距离视窗口左边界的距离
- `right` 元素右边界距离视窗口左边界的距离
- `top` 元素上边界距离视窗口上边界的距离
- `bottom` 元素下边界距离视窗口上边界的距离
- `height` 元素的最终高度
- `width` 元素的最终宽度

一般情况下会有`clientRect.bottom - clientRect.top = clientRect.height`和`clientRect.right - clientRect.left = client.width`。

通过该方法获取的结果是静态的，也就是说，结果中的数值仅为获取时的数据，即便之后元素的位置或大小发生了变化，也不会反映到之前的数据中，只能重新进行获取。

> 该方法除了 IE9 以下浏览器，都支持。

### 元素节点的滚动

DOM 规范中并没有规定各浏览器需要实现怎样的滚动页面区域，各浏览器实现了相应的方法，可以使用不同的方式控制页面区域的滚动。这些方法作为 HTMLElement 类型的扩展存在，所以它能在【所有元素】上使用。

1. `scrollIntoView(alignWithTop)`

    滚动浏览器窗口或容器元素，以便在当前视窗的可见范围看见当前元素。
    
    如果`alignWithTop`为 true，或者省略它，窗口会尽可能滚动到自身顶部与元素顶部平齐。
    
    目前各浏览器均支持。

2. `scrollIntoViewIfNeeded(alignCenter)`

    只在当前元素在视窗的可见范围内不可见的情况下，才滚动浏览器窗口或容器元素，最终让当前元素可见。如果当前元素在视窗中可见，这个方法不做任何处理。

    如果将可选参数`alignCenter`设置为 true，则表示尽量将元素显示在视窗中部（垂直方向）。

    Safari、Chrome 实现了这个方法。

3. `scrollByLines(lineCount)`

    将元素的内容滚动指定的行数的高度，`lineCount`的值可以为正值或是负值。

    Safari、Chrome 实现了这个方法。

4. `scrollByPages(pageCount)`

    将元素的内容滚动指定的页面的高度，具体高度由元素的高度决定。

    Safari、Chrome 实现了这个方法。

`scrollIntoView()`和`scrollIntoViewIfNeeded()`作用的是元素的窗口，而`scrollByLines()`、`scrollByPages()`影响元素自身。

由于只有`scrollIntoView()`被各浏览器均支持，所以这个方法最为常用。

示例如下：

```JavaScript
// 将页面主体滚动 5 行
document.body.scrollByLines(5);

// 确保当前元素可见
document.getElementById(“test”).scrollIntoView();

// 确保只在当前元素不可见的情况下才使其可见
document.getElementById(“test”).scrollIntoViewIfNeeded();

// 将页面主体往回滚 1 页
document.body.scrollByPages(-1);
```

### 特殊内容

除了上述的一些相对普遍适用的方法和属性，DOM 中针对不同的元素节点还存在一些特别的方法和属性。

#### document

document 作为文档的根节点(对应于 html 元素)，还附带有很多的其他属性。

- `title` 取得文档的标题
- `URL`	  取得完整的 URL（注意要大写 URL）
- `domain` 取得域名
- `referrer` 取得打开此页面的那个页面的 URL
- `body` 取得文档中的 body 元素
- `head` 取得文档中的 head 元素

#### table

为了协助建立表格，HTML DOM 给`table`、`tbody`和`tr`等元素添加了一些特性和方法。 

给`table`元素添加了以下内容：

* `caption` 指向`caption`元素（如果存在）
* `tBodies` `tbody`元素的集合 
* `tFoot` 指向`tfoot`元素（如果存在）
* `tHead` 指向`thead`元素（如果存在） 
* `rows` 表格中所有行的集合
* `createTHead()` 创建`thead`元素并将其放入表格
* `createTFoot()` 创建`tfoot`元素并将其放入表格
* `createCpation()` 创建`caption`元素并将其放入表格
* `deleteTHead()` 删除`thead`元素
* `deleteTFood()` 删除`tfoot`元素
* `deleteCaption()` 删除`caption`元素
* `deleteRow(index)` 删除指定位置上的行
* `insertRow(index)` 在 rows 集合中的指定位置上插入一个新行

`tbody`元素添加了以下内容：

- `rows` `tbody`中所有行的集合
- `deleteRow(index)` 删除指定位置上的行
- `insertRow(index)` 在 rows 集合中的指定位置上插入一个新行

`tr`元素添加了以下内容：

- `cells` `tr`元素中所有的单元格的集合
- `deleteCell(index)` 删除给定位置上的单元格
- `insertCell(index)` 在 cells 集合的给点位置上插入一个新的单元格


## DOM 助手对象

除节点外，DOM 还定义了一些助手对象，它们可以和节点一起使用，但不是 DOM 文档必有的部分。

### NodeList
NodeList 是一个“节点的集合”（可以包含元素和其他节点）。

该对象有一个`length`属性和一个`item()`方法：

* `length` 表示所获得的 NodeList 对象的节点个数（是节点的个数，不是元素的个数）。
* `item()` 可以传入一个索引来访问 NodeList 中相应索引的元素。

```html
<body>
	<div id="node">
		文本节点
		<!-- 注释节点 -->
		<span>node1</span>
		<span>node2</span>
		<span>node3</span>
	</div>
</body>
<script type="text/javascript">
	var node = document.getElementById('node'),
		 nodeLists = node.childNodes;
		 
	console.log(nodeLists.length); // 输出为9
</script>
```

上面的示例，之所以输出是 9，是因为：“文本节点”和父节点子节点的空格（连着的文本）算做一个文本节点，然后是一个注释节点和注释节点和元素节点之间的空格（换行会产生空格，空格算做文本节点）的文本节点，紧接着的是一个元素节点和元素节点之间的换行的文本节点，三个元素节点和元素节点间的两个文本节点，最后是最后得元素节点和父元素之间的空格产生的文本节点，总共是 9 个节点。

可以通过`childNodes`属性和`querySelectAll()`等方法来返回一个 NodeList 对象。但是这些方法获取的 NodeList 对象也是有区别的：

* **通过`childNodes`属性创建的 NodeList 对象**的一大特点是：它**包含的内容是动态的**（live）。也就是说，我们上面示例中的代码获取 NodeList 是类似于“指针”的东西，所以在获取 NodeList 之后再向 DOM 中插入一个创建的 span 标签之后，会看到 NodeList 对象的长度增长了。

* 而**通过`querySelectAll()`等方法创建的 NodeList 对象则是一个静态(static)的**，而且是元素节点的集合。也就是说，通过这些方法获取到的 NodeList 中仅仅包含元素节点，而不包含文本节点、注释节点等。而且，在获取 NodeList 之后再向 DOM 中插入一个创建的新的标签，会看到 NodeList 对象的长度和内容不会发生变化。

```html
<div id="node">
	文本节点
	<!-- 注释节点 -->
	<span>node1</span>
	<span>node2</span>
	<span>node3</span>
</div>

<script>
	var node = document.getElementById('node'),
		 nodeLists = node.childNodes,
		 queryNodes = node.querySelectorAll('span');

	node.appendChild(document.createElement('span'));
	console.log(nodeLists.length);
	// 输出为 10，不是 11
	// 因为通过 appendChild 追加子元素的时候，不会在两个元素节点之间生成空白文本节点
	// 如果我们写 HTML 代码的时候在两个元素之间不添加空格其实也是不会生成文本节点的
	
	console.log(queryNodes.length);  	// 输出为 3，不包含文本节点，只有元素，而且是之前获取到的元素节点
</script>
```

### HTMLCollection

HTMLCollection 是一个元素集合。

该对象和 NodeList 很像：有`length`属性来表示该对象的长度，也可以通过`elements.item(index)`来访问。

该对象还有一个`nameItem()`方法，可以返回集合中`name`属性或`id`属性值为指定值的元素。

HTMLDocument 接口的许多属性都是 HTMLCollection 对象，它提供了访问诸如表单、图像和链接等文档元素的便捷方式。比如`document.images`和`document.forms`属性都是 HTMLCollection 对象。

HTMLCollection 对象也是动态的，获取的是元素集合的一个引用。

```html
<img src="test.png" id="image1">
<img src="test.png" id="image2">
<img src="test.png" id="image3">

<script>
	console.log(document.images.namedItem('image1')); 	//<img src="test.png" id="image1">
</script>
```

> HTMLCollection 和 NodeList 的实时性非常有用，但我们有时要迭代一个 NodeList 或 HTMLCollection 对象的时候，通常会选择生成当前对象的一个快照或静态副本：
> 
> ```JavaScript
> var staticLists = Array.prototype.slice.call(nodeListOrHtmlCollection, 0);
> ```


### NamedNodeMap
NamedNodeMap 是一个同时使用数值和名字进行索引的节点表，用于表示元素特性。可以同时使用数组和对象的方式进行访问。

当 NamedNodeMap 用于表示特性时，其中每个节点都是属性节点，其 nodeName 属性被设置为特性名称，而 nodeValue 属性被设置为特性的值。

该对象也有一个`length`属性来指示它所包含的节点的数量。



