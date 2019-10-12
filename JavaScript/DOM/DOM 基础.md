DOM 是面向 HTML 和 XML 文档的 API，为文档提供了结构化表示，并定义了如何通过脚本来访问文档结构。DOM 独立于具体的编程语言，可以使用任何脚本语言来访问 DOM，这要归功于其一致的 API。

DOM 目前发展出了 3 个版本，每个新的版本都在之前的版本上增加了功能。其中，DOM1 级定义的 Node 类型是 DOM API 的基础。

> Node 类型是一个 Object 对象，但在 IE8- 浏览器中的所有 DOM 对象都是以 COM 对象的形式实现的，没有提供 Node 类型的构造函数。

## 一、节点

DOM 结构是由一个个的节点构成的，比如元素节点、特性节点、文档节点等。

### 1.1 节点类型

每个节点都有一个`nodeType`属性，用于表明节点的类型。节点类型在 Node 类型中定义下列 12 个数值常量来表示：

* `Node.ELEMENT_NODE = 1` 元素节点
* `Node.ATTRIBUTE_NODE = 2` 属性节点
*	`Node.TEXT_NODE = 3` 文本节点
* `Node.CDATA_SECTION_NODE = 4` CDATA 区段(只出现在 XML 文档中，表示的是 CDATA 区域)
* `Node.ENTITY_REFERENCE_NODE = 5` 实体引用
* `Node.ENTITY_NODE = 6` 实体
* `Node.PROCESSING_INSTRUCTION_NODE = 7` 处理指令
* `Node.COMMENT_NODE = 8` 注释节点
* `Node.DOCUMENT_NODE = 9` 文档节点(在 HTML 中就是`document`对象的类型)
* `Node.DOCUMENT_TYPE_NODE = 10` 文档类型节点(在 HTML 中就是顶部首行的文档类型声明语句的类型，如`<!DOCTYPE html>`，可以通过`document.firstChild`来访问)
* `Node.DOCUMENT_FRAGMENT_NODE = 11` 文档片段节点(该类型在文档中没有对应的标记，是一种轻量级的文档)
* `Node.NOTATION_NODE = 12` 标记节点(DTD 中声明的符号)

由于 IE 没有公开 Node 构造函数，所以不可使用类似`Node.ELEMENT_NODE`这种方式表示节点类型，而需要直接使用具体数值(如 1)来表示。

> 并不是所有节点类型都被浏览器支持，但是常用的元素节点、属性节点、文本节点是被支持的。

### 1.2 共同属性

除了`nodeType`属性，每个 DOM 节点还具有如下共同属性：

  property        |  description
------------------|--------------------------------------------------------------------
  nodeName        | 节点的名称。元素节点返回元素标签名称的大写字符串，文本节点返回`'#text'`，属性节点返回属性名称，文档节点返回`'#document'`。
  nodeValue       | 节点的值。元素节点总是为 null 且只读，文本节点返回文本值且可读写，属性节点返回属性的值且可读写。
  parentNode      | 节点的父节点。该值永远是一个元素节点，因为只有元素节点才可能有子节点。唯一例外的是`document`节点，它没有父节点，即`document.parentNode = null`。
  childNodes      | 节点的所有子节点的数组。是一个 NodeList 实例，包含元素节点和文本节点。*只读属性*。
  firstChild      | 节点的第一个下级子节点。如果某个节点没有任何子节点，该属性将返回 null。*只读属性*。
  lastChild       | 节点的最后一个子节点。如果某个节点没有任何子节点，该属性将返回 null。*只读属性*。
  nextSibling     | 节点后面的一个紧邻的兄弟节点。如果给定节点的后面没有属于同一个父节点的节点，该属性将返回 null。*只读属性*。
  previousSibling | 节点前面的一个紧邻的兄弟节点。如果给定节点的前面没有属于同一个父节点的节点，该属性将返回 null。*只读属性*。
  ownerDocument   | 指向表示整个文档的文档节点，也就是`document`。

这些属性中，`childNodes`、`firstChild`、`lastChild`、`nextSibling`、`previousSibling`会根据 DOM 树的变动而实时更新。

### 1.3 节点方法

下面这两个方法都是在 HTML5 中提供的，对所有的节点都有效：

  method                       |  description
-------------------------------|------------------------------------------------------
 contains(node)                | 判断 node 节点是否是当前元素的后代节点，是的话返回 true，否则返回 false
 compareDocumentPosition(node) | 判断 node 节点与当前元素的位置关系，返回一个表示该关系的位掩码

`contains()`判断的是包含关系，只要参数节点是当前节点的后代，即返回 true。如：`document.documentElement.contains(document.body) === true`。

`compareDocumentPosition()`方法返回的值主要有如下几种(还有其他一些值未列出)：

* `2` 居前，表示给定的节点在当前节点之前(如果两个元素不是同级，则比较他们同级的父元素的前后顺序)
* `4` 居后，表示给定的节点在当前节点之后(如果两个元素不是同级，则比较他们同级的父元素的前后顺序)
* `10` 包含，表示给定的节点包含当前节点(给定节点是当前节点的祖先节点)
* `20` 包含，表示给定的节点在当前节点中(给定节点是当前节点的后代节点)

## 二、元素节点属性

有一些属性是和节点的类型有关的，比如，文本节点具有`textContent`、`wholeText`属性，元素节点则没有；而元素节点具有很多的事件属性和位置属性等，文本元素就没有。

### 2.1 常规属性

下面是元素节点中一些比较常用的属性：

  property    |  description
--------------|--------------------------------------------------------------------
  tagName     | 元素节点的标记名称，为大写字母。*只读*。
  className   | 元素节点的类名字符串
  classList   | 元素节点的类名集合，是一个 DOMTokenList 类型的实例，可以更方便的操作类名
  innerHTML   | 元素节点中全部子节点的 HTML 代码。*可读写*，可用来改变该节点元素内的代码。大多数浏览器中通过`innerHTML`插入`<script>`元素并不会执行其中的脚本。
  outerHTML   | 当前元素及其全部子节点的 HTML 代码。*可读写*。当设置该属性时，会根据指定的 HTML 字符串创建新的 DOM 子树，然后替换掉调用元素。
  innerText   | 元素节点的全部子节点中的文本内容。*可读写*，赋值后，该元素内部的 HTML 代码将被清除，并用新的文本内容填充。赋值的文本内容中即便有 HTML 也会被转义，从而保证能显示成文本。
  attributes  | 元素节点的全部属性节点组成的 NamedNodeMap 对象。可按照数组方式进行访问，每个子元素均为属性节点对象，其中也会包含节点的共有属性。
  children    | 一个 HTMLCollection 实例，只包含元素中的子元素节点(不包括文本节点、注释节点等)

> **注意**：不支持`innerHTML`属性的元素有：`<col>`、`<colgroup>`、`<table>`、`<tbody>`、`<thead>`、`<tfoot>`、`<tr>`、`<frameset>`、`<head>`、`<html>`、`<style>`、`<>`

`classList`是 HTML5 新增的元素节点属性，便于更方便的操作元素节点的类名，如：

```JavaScript
// 删除 disabled 类
div.classList.remove('disabled');

// 添加 current 类
div.classList.add('current');

// 切换 user 类，也就是如果节点当前含有 user 类则去除该类，否则为节点添加 user 类
div.classList.toggle('user');

// 判断是否有 page 类
div.classList.contains('page');
```

### 2.2 Element Traversal API

为避免不同浏览器对空格文本处理方式的不同，Element Traversal 规范为 DOM 元素节点新定义了一组属性，以便更好的遍历子元素节点：

  property              |  description
------------------------|---------------------------------------------
 childElementCount      | 子元素(不包括文本节点和注释)的个数
 firstElementChild      | 第一个子元素(firstChild 的元素版)
 lastElementChild       | 最后一个子元素(lastChild 的元素版)
 previousElementSibling | 前一个同辈元素(previousSibling 的元素版)
 nextElementSibling     | 后一个同辈元素(nextSibling 的元素版)

## 三、元素节点方法

### 3.1 获取元素节点

随着 DOM 标准的更新，逐渐引入了一些更方便的获取元素节点的方法，这些方法已经在大部分的浏览器中获得了支持，兼容性相对较好。

  method                       |  description
-------------------------------|------------------------------------------------------
 getElementById(id)            | 获取有指定惟一 ID 属性值文档中的元素。返回值是 DOM 节点或 null。如果一个文档中有多个具有相同的 id 属性的元素，也只会返回第一个元素。
 getElementsByTagName(name)    | 获取全部有指定标记名的子元素。返回值总是一个即时的 HTMLCollection 实例，即便不存在指定标签的子元素。可以传递一个星号`*`来获取元素中的所有子元素节点。
 getElementsByClassName(class) | 获取全部有指定类名的子元素。返回值总是一个即时的 HTMLCollection 实例，即便不存在具有指定类名的子元素。可以传入多个类名(使用空格连接成字符串)，表示同时具有这些类名的子元素，匹配的时候会忽略传入的类名的次序。
 querySelector(selector)       | 根据指定的 css 选择符来获取相应的子元素中的第一个。参数 selector 可以是任意的合法 CSS 选择符。
 querySelectorAll(selector)    | 根据指定的 css 选择符来获取相应的全部子元素。参数 selector 可以是任意的合法 CSS 选择符。

上面的五个方法中，**`getElementById()`方法只存在于`document`对象中，不能由其他的元素节点进行调用**，其他的四个方法可以由任意的 DOM 元素节点调用，包括`document`对象。

比如：

```JavaScript
var app = document.getElementById('app');
var wrapper = app.getElementsByClassName('wrapper');
var paragraphs = app.getElementsByTagName('p');
var images = app.querySelectorAll('img');
var figure = app.querySelector('img');
```

### 3.2 操作元素节点属性

一般来说，元素节点中的属性都能像对象属性一样进行调用，但是`class`属性则不行。因为`class`是 JavaScript 中的一个关键字，不能用于变量名、属性名等，此时就需要使用`className`来获取元素的类名。

DOM 标准中，为元素节点提供了如下三个 API，时的元素节点可以通过方法来操作自身的属性。方法列表如下：

  method                        |  description
--------------------------------|------------------------------------------------------
 getAttribute(attribute)        | 获取元素节点指定的属性值。如果未设置对应的属性，则返回 null。
 setAttribute(attribute, value) | 设置元素节点指定的属性的值。
 removeAttribute(attribute)     | 从元素节点中删除指定属性。

> 注意：这三个方法均为元素节点所特有，不能被`document`对象调用。

通过对象属性名方式获取属性值和使用`getAttribute()`方法获取属性值是一样的。同样，通过给对象属性赋值的方式，也可以给元素设置属性值，效果和`setAttribute()`方法的效果相同。

但通过方法和通过属性方式获取属性值有点细微的区别：

* 通过`getAttribute()`方法获取属性值的时候，如果属性未设置，则会返回 null；如果设置了属性，但没有设置值，则会返回空字符串(有些属性是例外的)。
* 通过属性方式访问则不一样，不论是没有设置属性，还是没有设置属性值，都会返回空字符串。

> 通过对象属性方式访问和设置元素属性的方式是非 DOM Core 中的 API，仅针对的是 HTML 文档。
> 
> IE 在`setAttribute()`上有很大的问题，最好尽可能使用属性赋值方式。

### 3.3 动态更改节点

  method                           |  description
-----------------------------------|------------------------------------------------------
 createElement(tagName)            | 创建由`tagName`指定的新的元素节点，返回值是一个指向新建元素节点的引用指针
 createDocumentFragment()          | 创建文档碎片节点，不需要参数
 createTextNode(text)              | 创建一个包含静态文本的节点
 cloneNode(deep)                   | 为给定节点创建一个副本。返回值是一个指向新建克隆节点的引用指针。参数`deep`是 Boolean 类型，指定是否拷贝子节点。(属性节点总会被复制，但不会复制 DOM 中的 JavaScript 属性，如事件处理器等)
 appendChild(childNode)            | 将指定的节点增加到当前元素的子节点列表的最末处(作为一个新的子节点)。返回一个指向新增子节点的引用指针
 insertBefore(newNode, targetNode) | 将节点 newNode 作为当前元素的子节点插到 targetNode 元素前面。如果不指定 targetNode，将会作为当前元素的最后一个子元素插入。返回一个指向新增子节点的引用指针
 removeChild(childNode)            | 从元素中删除子元素 childNode。返回一个指向被删除元素的指针。当某个元素节点被该方法删除时，其所有的子节点都被从原位置删除。
 replaceChild(newNode, oldNode)    | 将节点 oldNode 替换为节点 newNode。被替换的节点必须属于给定的父节点。返回一个已被替换的那个节点(oldNode)的引用指针
 insertAdjacentHTML(position, str) | 向 DOM 中插入标记，参数 position 指定插入的位置，str 表示要插入的 HTML 标记字符串
 normalize()                       | 处理文档树中的文本节点。由于解析器的实现或 DOM 操作等原因，可能会出现文本节点不包含文本，或者连续出现两个文本节点的情况。在节点上调用这个方法可以在该节点的后代节点中查找这两种情况，自动删除空文本节点，并合并相邻的文本节点。

> **注意 1**：`createElement(tagName)`、`createDocumentFragment()`、`createTextNode(text)`需要由`document`对象调用，其他的方法则由具体的 DOM 元素节点对象调用。

> **注意 2**：`appendChild()`方法和`insertBefore()`方法用于将已存在于文档中的节点插入到另一个元素中时，将会先将该节点从原位置删除，然后再把它重新插入到新的位置中去，不必先用`removeChild()`方法删除。

> **注意 3**：`removeChild()`删除节点的时候，并没有将该节点销毁，只是从原位置去除掉了。之后还可以将该节点加入到文档中，和调用`document.createDocumentFragment()`方法创建一个文档片段类似。

> **注意 4**：新创建和复制的节点不会自动加入到文档中，没有`nodeParent`属性。如要要添加入文档，需要用`appendChild()`或`insertBefore()`或`replaceChild()`方法。

> **注意 5**：IE 中，`cloneNode()`会复制 DOM 节点的 JavaScript 属性，建议在复制之前最好能移除该 DOM 节点的 JavaScript 属性。

一旦把节点添加到`document.body`(或者它的后代节点)中，页面就会更新并反映出这个变化。对于少量的更新，这是很好的。然而，当要向`document`添加大量数据时，如果逐个添加这些变动，这个过程有可能会十分缓慢。为解决这个问题，可以创建一个文档碎片，把所有的新节点附加其上，然后把文档碎片的内容一次性添加到`document`中。这就是`document.createDocumentFragment()`方法的作用。

#### 3.3.1 insertAfter()

DOM API 中默认没有提供`insertAfter()`方法，但是可以借助`appendChild()`和`insertBefore()`方法来实现该功能：

```JavaScript
function insertAfter (newElement, targetElement) {
    var parent = targetElement.parentNode;
    
    if (parent.lastChild == targetElement) {
        parent.appendChild(newElement);
    } else {
        parent.insertBefore(newElement, targetElement.nextSibling);
    }
}
```

#### 3.3.2 insertAdjacentHTML()

`insertAdjacentHTML(position, str)`方法在 HTML5 添加，最早是在 IE 中出现的。它接收两个参数：插入位置和要插入的 HTML 文本(与 innerHTML 和 outerHTML 的值相同)。

其中，参数 position 必须是下列的值之一(必须是小写形式)：

* `beforebegin` 在当前元素之前插入一个紧邻的同辈元素(可以实现元素节点的`insertBefore()`方法的功能)
* `afterbegin` 在当前元素的第一个子元素之前插入新的子元素(即新的元素作为当前元素的第一个子元素插入)
* `beforeend` 在当前元素的最后一个子元素之前插入新的子元素(即新的元素作为当前元素的最后一个子元素插入)
* `afterend` 在当前元素之后插入一个紧邻的同辈元素(可以实现前面的`insertAfter()`方法的功能)

例如：

```JavaScript
// 作为前一个同辈元素插入
element.insertAdjacentHTML('beforebegin', '<p>Hello World!</p>');

// 作为第一个子元素插入
element.insertAdjacentHTML('afterbegin', '<p>Hello World!</p>');

// 作为最后一个子元素插入
element.insertAdjacentHTML('beforeend', '<p>Hello World!</p>');

// 作为后一个同辈元素插入
element.insertAdjacentHTML('afterend', '<p>Hello World!</p>');
```

### 3.4 元素节点的滚动

DOM 规范中并没有规定各浏览器需要实现怎样的滚动页面区域，各浏览器实现了相应的方法，可以使用不同的方式控制页面区域的滚动。这些方法作为 HTMLElement 类型的扩展存在，所以它能在【所有元素】上使用。

  method                             |  description
-------------------------------------|------------------------------------------------------
 scrollIntoView(alignWithTop)        | 滚动浏览器窗口或容器元素，以便在当前视窗的可见范围看见当前元素。如果`alignWithTop`为 true，或者省略它，窗口会尽可能滚动到自身顶部与元素顶部平齐。目前各浏览器均支持。
 scrollIntoViewIfNeeded(alignCenter) | 只在当前元素在视窗的可见范围内不可见的情况下，才滚动浏览器窗口或容器元素，最终让当前元素可见。如果当前元素在视窗中可见，这个方法不做任何处理。如果将可选参数`alignCenter`设置为 true，则表示尽量将元素显示在视窗中部（垂直方向）。Safari、Chrome 实现了这个方法。
 scrollByLines(lineCount)            | 将元素的内容滚动指定的行数的高度，`lineCount`的值可以为正值或是负值。Safari、Chrome 实现了这个方法。
 scrollByPages(pageCount)            | 将元素的内容滚动指定的页面的高度，具体高度由元素的高度决定。Safari、Chrome 实现了这个方法。

> **注意**：`scrollIntoView()`和`scrollIntoViewIfNeeded()`作用的是元素的窗口，而`scrollByLines()`、`scrollByPages()`影响元素自身。

示例如下：

```JavaScript
// 将页面主体滚动 5 行
document.body.scrollByLines(5);

// 确保 #test 元素可见
document.getElementById("test").scrollIntoView();

// 确保只在 #test 元素不可见的情况下才使其可见
document.getElementById("test").scrollIntoViewIfNeeded();

// 将页面主体往回滚 1 页
document.body.scrollByPages(-1);
```

### 3.5 其他元素节点方法

除了上面列出的节点操作方法之外，元素节点对象上还具有如下的方法，可以用来实现相关功能：

  method                       |  description
-------------------------------|------------------------------------------------------
 getBoundingClientRect()       | 返回一个 DOMRect 对象，表示元素的大小以及相对于浏览器可视窗口的位置。通过该方法获取的结果是静态的，也就是说，结果中的数值仅为获取时的数据，即便之后元素的位置或大小发生了变化，也不会反映到之前的数据中，只能重新进行获取。需 IE 9+ 浏览器支持。
 hasChildNodes()               | 返回一个布尔值，表示当前元素是否有子元素。
 matchesSelector(selector)     | 返回一个布尔值，表示元素是否与指定的 CSS 选择符 selector 匹配

> **注意**：IE 还不支持`matchesSelector()`方法，需要使用`msMatchesSelector()`替代，如：`document.body.msMatchSelector('body.page')`。

## 四、DOM 助手对象

除节点外，DOM 还定义了一些助手对象，它们可以和节点一起使用，但不是 DOM 文档必有的部分。

### 4.1 NodeList

NodeList 是一种类数组对象，用于保存一组有序的节点，可以通过为止来访问这些节点。有一个`length`属性和一个`item()`方法：

* `length` 表示所获得的 NodeList 对象的节点个数（是节点的个数，不是元素的个数）。
* `item()` 可以传入一个索引来访问 NodeList 中相应索引的元素。

除了可以使用`item()`方法获取元素外，还能如同数组一样，使用中括号获取元素。

比如：

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
	console.log(nodeLists.item(3); // "<span>node1</span>"
	console.log(nodeLists[3];      // "<span>node1</span>"
</script>
```

上面的示例，之所以输出是 9，是因为：“文本节点”和父节点子节点的空格（连着的文本）算做一个文本节点，然后是一个注释节点和注释节点和元素节点之间的空格（换行会产生空格，空格算做文本节点）的文本节点，紧接着的是一个元素节点和元素节点之间的换行的文本节点，三个元素节点和元素节点间的两个文本节点，最后是最后得元素节点和父元素之间的空格产生的文本节点，总共是 9 个节点。

可以通过`childNodes`属性和`querySelectAll()`等方法来返回一个 NodeList 对象。但是这些方法获取的 NodeList 对象也是有区别的：

* **通过`childNodes`属性创建的 NodeList 对象**的一大特点是：它**包含的内容是动态(live)的**。也就是说，上面示例中的代码获取 NodeList 是类似于“指针”的东西，所以在获取 NodeList 之后再向 DOM 中插入一个创建的 span 标签之后，会看到 NodeList 对象的长度增长了。

* 而**通过`querySelectAll()`等方法创建的 NodeList 对象则是一个静态(static)的**，而且是元素节点的集合。也就是说，通过这些方法获取到的 NodeList 中仅仅包含元素节点，而不包含文本节点、注释节点等。而且，在获取 NodeList 之后再向 DOM 中插入一个创建的新的标签，会看到 NodeList 对象的长度和内容不会发生变化。

比如：

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
	// 如果写 HTML 代码的时候在两个元素之间不添加空格其实也是不会生成文本节点的
	
	console.log(queryNodes.length);  	// 输出为 3，不包含文本节点，只有元素，而且是之前获取到的元素节点
</script>
```

### 4.2 HTMLCollection

HTMLCollection 是一个元素集合。该对象和 NodeList 对象很像：有`length`属性来表示该对象的长度，也可以通过`elements.item(index)`来访问。

该对象还有一个`namedItem()`方法，可以返回集合中`name`属性或`id`属性值为指定值的元素。另外，当使用中数组括号方式访问元素集合时，如果传入的索引键是字符串，则会自动调用`namedItem()`方法来获取元素。

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

### 4.3 NamedNodeMap

NamedNodeMap 是一个同时使用数值和名字进行索引的节点表，用于表示元素特性。可以同时使用数组和对象的方式进行访问。

当 NamedNodeMap 用于表示特性时，其中每个节点都是属性节点，其 nodeName 属性被设置为特性名称，而 nodeValue 属性被设置为特性的值。

该对象也有一个`length`属性来指示它所包含的节点的数量。

NamedNodeMap 对象拥有如下方法：

* `getNamedItem(name)` 返回 nodeName 属性等于 name 的属性节点
* `setNamedItem(attrNode)` 向列表中添加属性节点，以节点的 nodeName 属性作为索引
* `removeNamedItem(name)` 从列表中移除 nodeName 属性等于 name 的属性节点
* `item(pos)` 返回列表中索引为 pos 的节点

### 4.4 DOMRect

DOMRect 对象中的属性和元素节点的位置和大小关系相关，其值是最终呈现在浏览器中的效果对应的值，主要包含如下属性：
    
- `left` 元素左边界距离视窗口左边界的距离
- `right` 元素右边界距离视窗口左边界的距离
- `top` 元素上边界距离视窗口上边界的距离
- `bottom` 元素下边界距离视窗口上边界的距离
- `height` 元素的最终高度
- `width` 元素的最终宽度

一般情况下会有`clientRect.bottom - clientRect.top = clientRect.height`和`clientRect.right - clientRect.left = client.width`。

### 4.5 DOMTokenList

DOMTokenList 是 HTML5 中新增的集合类，与其他 DOM 集合类型类似，该类型有一个`length`属性表示其包含多少元素，获取元素可以使用`item()`方法或者方括号方式。

DOMTokenList 还具有如下一些方法：

* `add(value)` 将给定的字符串值添加到列表中，如果已经存在，则不作任何操作。
* `contains(value)` 检查列表中是否存在给定的值，如果存在则返回 true，否则返回 false。
* `remove(value)` 从列表中删除给定的字符串。
* `toggle(value)` 如果列表中已经存在给定的值，则删除它；如果列表中没有给定的值，则添加它。


