JavaScript DOM 中定义了多种节点类型，并对每种类型的节点提供了不同的属性和方法，以实现差异化操作和管理。

## 一、Document 类型

JavaScript 通过 Document 类型表示文档。在浏览器中，`document`对象就是 HTMLDocument(继承自 Document)的一个实例，表示整个 HTML 页面。而且`document`对象是 window 对象的一个属性，因此可以将其作为全局对象来访问。

### 1.1 特征

Document 节点具有如下特征：

* `nodeType`的值为 9；
* `nodeName`的值为`"#document"`；
* `nodeValue`的值为 Null；
* `parentNode`的值为 Null；
* `ownerDocument`的值为 Null。

### 1.2 属性

document 对象还附带有很多的其他属性：

- `title` 文档的标题，可读写。设置新值时可以更改页面展示的标题，并同步修改`<title></title>`中的文本内容
- `URL` 完整的 URL（注意要大写 URL），只读
- `domain` 文档所在域名，可读写，但由于安全限制，不能将这个属性设置为 URL 中不包含的域，只能将子域名改成主域名。更改之后并不会刷新页面，只会影响浏览器对这个页面的安全限制
- `referrer` 打开此页面的那个页面的 URL，只读
- `body` 文档中的 body 元素
- `head` 文档中的 head 元素
- `documentElement` 文档中的 html 元素
- `anchors` 文档中所有带有`name`特性的`<a>`元素
- `links` 文档中素有带有`href`特性的`<a>`元素
- `forms` 文档中所有的`<form>`元素
- `images` 文档中所有的`<img>`元素

### 1.3 方法

document 对象中还有一些方法：

* `getElementById(id)` 通过 ID 获取文档中的元素节点
* `getElementsByTagName(name)` 通过元素标签名获取文档中的元素节点集合
* `getElementsByClassName(class)` 通过类名获取文档中的元素节点集合
* `querySelector(selector)` 通过 CSS 选择符获取文档中的元素节点
* `querySelectorAll(selector)` 通过 CSS 选择符获取文档中的元素节点集合
* `createElement(tagName)` 创建由`tagName`指定的新的元素节点
* `createDocumentFragment()` 创建文档碎片节点
* `createTextNode(text)` 创建文本节点
* `createComment()` 创建注释节点
* `createCDataSection()` 创建 CDATA 区域
* `write(text)` 将 text 写入到文档输出流中
* `writeln(text)` 将 text 写入到文档输出流中，并在字符串的末尾梯恩将一个换行符(`\n`)
* `open()` 打开网页的输出流
* `close()` 关闭网页的输出流

> **注意**：通过`write()`和`writeln()`方法写入的内容会被浏览器当做 HTML 文本进行解析，如果包含 HTML 标签，也可以正常展示。

## 二、Element 类型

Element 类型是 Web 编程中常用的类型，用于表示 XML 或 HTML 元素，提供了对元素的标签名、子节点及特性的访问。

### 2.1 特性

Element 类型具有如下特性：

* `nodeType`的值为 1；
* `nodeName`的值为元素的标签名；
* `tagName`的值为元素的标签名
* `nodeValue`的值为 Null；
* `parentNode`的值可能为 Document 或 Element；
* `ownerDocument`的值为 document 对象。

要访问元素的标签名，可以使用`nodeName`或`tagName`，后者更清晰一些。

### 1.2 属性

所有的 HTML 元素都由 HTMLElement 类型(继承自 Element)表示，设置了如下一些常用的属性：

* `id` 元素在文档中的唯一标识符
* `title` 有关元素的附加说明信息，一般通过工具提示条显示
* `lang` 元素内容的语言代码，很少使用
* `dir` 语言的方向，值为`ltr`(从左至右)或`rtl`(从右至左)
* `className` 元素的类名，与元素的`class`特性对应
* `attributes` 元素的特性集合，是一个`NamedNodeMap`类型的对象，是动态的集合
* `childNodes` 元素的子节点集合

### 1.3 方法

元素中具有一些常用的方法，可以用来操作子节点或者特性：

* `getAttribute()` 获取特性值
* `setAttribute()` 设置特性值
* `removeAttribute()` 删除特性

## 三、Text 类型

文本节点由 Text 类型表示，包含的是可以照字面解释的纯文本内容。纯文本可以包含转义后的 HTML 字符，但不能包含 HTML 代码。

### 3.1 特征

Text 节点具有如下特征：

* `nodeType`的值为 3；
* `nodeName`的值为`#text`；
* `nodeValue`的值为节点所包含的文本；
* `parentNode`是一个 Element。

Text 节点不支持(没有)子节点。

### 3.2 属性

Text 节点具有如下的属性：

* `data` 节点的文本内容
* `length` 节点中字符的数目

可以通过`data`属性或`nodeValue`属性访问 Text 节点中包含的文本。

### 3.3 方法

Text 节点具有如下的方法，都是和节点中的文本相关：

* `appendData(text)` 将 text 添加到节点的末尾
* `deleteData(offset, count)` 从 offset 指定的位置开始删除 count 个字符
* `insertData(offset, text)` 在 offset 指定的位置处插入 text
* `replaceData(offset, count, text)` 用 text 替换从 offset 指定的位置开始的 count 个字符
* `splitText(offset)` 从 offset 指定的位置开始当前文本节点分成两个文本节点
* `substringData(offset, count)` 提取从 offset 指定的位置开始的 count 个字符

## 四、Comment 类型

注释在 DOM 中是通过 Comment 类型来表示的。

### 4.1 特征

Comment 具有如下特征：

* `nodeType`的值为 8；
* `nodeName`的值为`#comment`；
* `nodeValue`的值为注释的内容；
* `parentNode`是 Document 或 Element。

Comment 不支持子节点。

### 4.2 属性

* `data` 注释的内容。

### 4.3 方法

Comment 类型与 Text 类型继承自相同的基类，所有它拥有除`splitText()`之外的所有文本节点字符串操作方法。

## 五、Attr 类型

元素的特性在 DOM 中以 Attr 类型来表示。从技术角度讲，特性就是存在于元素的 attributes 属性中的节点。

> 在所有的浏览器(包括 IE 8)中，都可以访问 Attr 累心的构造函数和原型。

### 5.1 特征

特性节点具有如下特征：

* `nodeType`的值为 2；
* `nodeName`的值是特性的名称；
* `nodeValue`的值是特性的值；
* `parentNode`的值为 Null。 

在 HTML 中，Attr 类型不支持子节点。尽管特性也是节点，但不被认为是 DOM 文档树的一部分。

### 5.2 属性

Attr 对象有三个属性：

* `name` 特性的名称，与 nodeName 的值相同
* `value` 特性的值，与 nodeValue 的值相同
* `specified` 是一个布尔值，用意区别特性是否为在代码中指定的，还是默认的

### 5.3 方法

开发人员一般可以使用元素的`getAttribute()`、`setAttribute()`和`removeAttribute()`方法来操作特性，很少需要直接引用特性节点。

