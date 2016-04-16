最早在网页中引入 JavaScript 拖放功能是 IE4。当时，网页中只有两种对象可以拖放：图像和某些文本。在 IE4 中，唯一有效的放置目标是文本框。到了 IE5，拖放功能得到拓展，添加了新的事件，而且几乎网页中的任何元素都可以作为放置目标。IE5.5 更进一步让网页中的任何元素都可以拖放。HTML5 以 IE 的实例为基础指定了拖放规范。

MDN文档：[Drag and Drop](https://developer.mozilla.org/zh_CN/DragDrop/Drag_and_Drop)。

## 拖放操作
MDN文档：[拖放操作](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_and_Drop)

拖放和拖拽有点小区别：拖放是指用户在一个元素上点击并按住鼠标按钮，拖动它到别的位置，然后松开鼠标按钮将元素放到那儿。而拖拽则只是对元素的拖动。所以可以理解为拖放操作是需要拖拽操作来支持的。

在拖动操作过程中，被拖动元素会以半透明形式展现，并跟随鼠标指针移动。

另外，放置元素的位置可能会在不同的应用内。

处理拖放通常有以下几个步骤：

- 定义可拖动目标。将我们希望拖动的元素的 draggable 属性设为 true。更多信息参阅 [draggable 属性](https://developer.mozilla.org/zh-CN/docs/Web/Guide/HTML/Drag_operations#draggableattribute)。

- 定义被拖动的数据，可能为多种不同格式。例如，文本型数据会包含被拖动文本的字符串。更多信息参阅[拖动数据](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#Drag_Data)。


- （可选）自定义拖动过程中鼠标指针旁边会出现的拖动反馈图片。如果未设定，默认图片会基于鼠标按钮按下的元素（正在被拖动的元素）来自动生成。要了解更多关于拖动反馈图片的内容，请参阅[设置拖动反馈图片](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#Setting_the_drag_feedback_image)。


- 允许设置拖拽效果。有三种效果可以选择：copy 用来指明拖拽的数据将从当前位置复制到释放的位置；move 用来指明被拖拽的数据将被移动；link 用来指明将在源位置与投放位置之间建立某些形式的关联或连接。在拖拽的过程中，可以修改拖拽效果来指明在某些位置允许某些效果。如果允许，你将可以把数据释放到那个位置。更多信息参阅[拖拽操作](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#Drag_Effects)。

> 注意，这里影响的**是**拖拽的**数据**的行为，而**不是**拖拽的元素的**行为**。

- 定义放置区域。默认情况下，浏览器阻止任何东西向 HTML 元素放置拖拽的发生。要使一个元素成为可放置区域，需要阻止浏览器的默认行为，也就是要监听 dragenter 和 dragover 事件，在监听器中阻止浏览器的默认行为(`return false;`或者`event.preventDefault();`)。更多信息参阅[指定放置目标](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#droptargets)。


- 在 drop 发生时做一些事情。你可能想要获取拖拽目标携带的数据并做某些相应的事情。更多信息请参阅[执行放置(Performing a Drop)](https://developer.mozilla.org/zh-CN/docs/Web/Guide/HTML/Drag_operations#drop)。

对于拖拽中使用的通用数据类型列表，主要有以下几种，具体请参阅 [推荐的拖拽类型](https://developer.mozilla.org/zh-CN/docs/DragDrop/Recommended_Drag_Types)。

- 文本 Text
- 链接 Links
- HTML 和 XML
- 文件 Files
- 图片 Images
- 文档节点 Document Nodes


## 拖拽操作
MDN 文档：[拖拽操作](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations)

### Draggable 属性
Draggable 属性是用来设置 HTML 元素能否被拖动的。

在一个 web 页面中，**选中的文本**、**images 图片**和**链接**默认就可以被拖动的元素。其它元素默认是不可拖拽的，要使其他元素要能被拖动，需要设置其 Draggable 属性为 true。

当文本、图片或者链接被拖动时，这个被拖动的文本、图片或者链接的 URL 就会先被设置为“拖动数据”（drag data）。

> 注意：一旦元素为被设置为可拖拽，元素中的文本或其他元素就无法通过鼠标点击或拖拽来实现选中了。但可通过`Alt + 鼠标`选中文本。


### 拖拽事件
拖拽相关的事件也是会冒泡的，所以我们也可以在拖拽相关元素的父元素上监听相应的拖拽事件。

在进行拖放操作的不同阶段会触发数种事件，被拖动元素和放置元素会分别触发不同的事件。下面分别进行介绍。

**注意**：在拖拽的时候只会触发拖拽的相关事件，而鼠标事件，例如 mousemove，是不会触发的。

**注意**：当从操作系统拖拽文件到浏览器的时候，dragstart 和 dragend 事件不会触发。


#### 被拖动元素
拖动元素时，在被拖放元素上将依次触发下列事件：

* dragstart 拖动开始时触发，一次拖放只触发一次。在这个事件中，一般会在监听器中设置与这次拖拽相关的信息，例如拖动的数据、反馈图片、拖拽效果等。一般，只有拖拽数据是必须的，其他都可以自适应的。

* drag 拖动过程中，会不断的触发，类似 touchmove 事件；

* dragend 拖动停止时触发，一次拖动只触发一次。无论是把元素放到了有效的放置目标，还是放到了无效的放置目标上，都会触发该事件。

HTML5 为所有 HTML 元素规定了一个 draggable 属性，表示元素是否可以拖动。
所以任何元素都可以**指定 draggable 属性，从而能使其可被拖动**。

```html
<!-- 让这个图像不可以拖动 -->
<img src="smile.gif" draggable="false" alt="Smiley face">
<!-- 让这个元素可以拖动 -->
<div draggable="true">...</div>
```

浏览器在拖动过程中，不会改动被拖动元素的样式，但是我们可以用 js 监听 dragstart 事件，在 dragstart 事件中改动被拖动元素的样式。

另外，在拖动时，浏览器会根据被拖动元素生成一个缩略图，跟随鼠标移动而移动。当然，我们也能够设置这个缩略图。

#### 放置元素
当元素被拖放到一个有效的放置目标上时，下列事件会依次发生在放置目标元素上：

* dragenter 当拖拽中的鼠标第一次进入一个元素的时候触发。这个事件的监听器需要指明是否允许在这个区域释放鼠标。如果没有设置监听器，或者监听器没有进行操作，则默认不允许释放。当你想要通过类似高亮或插入标记等方式来告知用户此处可以释放，你将需要监听这个事件。

* dragover 一个元素被拖动到这个元素上后，只要被拖动的元素还在该元素的范围内移动时，就会持续触发该事件。大多数时候，这个事件的监听器与 dragenter 事件的监听器是一样的。

* dragleave 当拖拽中的鼠标离开元素时触发。一般这个事件的监听器需要将作为可释放反馈的高亮或插入标记去除。

* drop 这个事件在拖拽操作结束时于释放元素上触发。一般这个事件的监听器用来接收被拖拽的数据并插入到释放之地。这个事件只有在需要时才触发。如果用户在非可释放目标上释放了拖放，则不会触发 drop 事件，而是触发 dropleave 事件；当用户取消了拖拽操作时将不触发，例如按下了 Escape（ESC）按键，不会触发 drop 事件，也不会触发 dropleave 事件。

可以把任何元素变成有效的放置目标，方法是重写 dragenter 和 dragover 事件的默认行为，也即是取消元素的这两个事件的默认行为：

```js
var droptarget = document.getElementById("droptarget");

droptarget.ondragover = function() {
    event.preventDefault();
};

droptarget.ondragenter = function () {
    event.preventDefault();
};
```

在 Firefox 3.5+ 中，放置事件的默认行为是打开被放到放置目标上的 URL。因此，为了让 Firefox 支持正常的拖放，还要取消 drop 事件的默认行为，阻止它打开 URL：

```js
droptarget.ondrop = function () {
    event.preventDefault();
};
```


### dataTransfer 对象
所有的拖拽事件都有一个属性：`dataTransfer`，它包含着拖拽数据和操作拖放数据的一些方法。

dataTransfer 对象是为了在拖放操作时实现数据交换而引入的。它是事件对象的一个属性，用于从被拖动元素向放置目标传递字符串格式的数据。因为它是事件对象的属性，所以只能在拖放事件的事件处理程序中对其访问，但是不能单独创建。

**注意**：在原生 js 代码中，dataTransfer 对象是位于事件对象上的，使用`e.dataTransfer`方式来引用；而在 jQuery 中，则需要使用`e.originalEvent.dataTransfer`的方式来引用。

具体参见：[MDN DataTransfer](https://developer.mozilla.org/zh_CN/docs/Web/API/DataTransfer)


#### 方法
dataTransfer 对象有多个方法，一般我们会用到`setData`和`getData`方法来设置和获取数据。

1. `void addElement(in Element element)`
    设置拖动源。通常你不需要改变这项，如果修改这项将会影响拖动的那个节点和 dragend 事件的触发。默认目标是被拖动的节点。
    参数 element 表示要添加的元素。

2. `void clearData([in String type])`
    删除与给定类型关联的数据。
    可选类型参数 type 表示要删除的数据类型。
    如果类型为空或未指定，将删除所有类型相关联的数据。
    如果不存在指定类型的数据，或数据传输不包含任何数据，此方法将没有任何效果。

3. `String getData(in String type)`
    检索（取得）给定类型的数据，如果给定类型的数据不存在或者数据转存（data transfer）没有包含数据，方法将返回一个空字符串。
    如果你试图获取从不同域中拖动的数据将发生一个安全性错误或者无法访问。
    这个数据将仅仅在放置动作发生时的 drop 事件中是可用的。
    参数 type 表示要检索的数据类型。

4. `void setData(in String type, in String data)`
    为一个给定的类型设置数据。
    如果该数据类型不存在，它将添加到的末尾，这样类型列表中的最后一个项目将是新的格式。如果已经存在的数据类型，替换相同的位置的现有数据。
    就是，当更换相同类型的数据时，不会更改类型列表的顺序。
    参数 type 表示要添加的数据类型；参数 data 表示要添加的数据。

5. `void setDragImage(in nsIDOMElement image, in long x, in long y)`
    自定义一个期望的拖动时的图片。大多数情况下，这项不用设置，因为会从被拖拽的目标（即 dragstart 事件被触发的元素）处自动产生一个半透明的图片（反馈图片），该图片在拖拽过程中会跟随鼠标指针移动。
    如果被拖动元素是 HTML img 元素、 HTML 画布元素或一个 XUL 图像元素，则会使用图像数据[拖动时的效果将使用这里的图片]。否则应该是可见的节点，根据这个可见的节点创建拖动图像。
    参数 imgage 是指向一个图片的引用，通常是一个图片元素对象，但也可以是一个 canvas 或其它任何元素对象。这个图片在页面上怎么显示，它作为拖拽的反馈图片就怎么显示。你可以使用不在该文档中的图片或者 canvas。如果参数 image 是 null，任何自定义拖动图像都会被清除，并且使用默认值代替。
    参数 x，y 是分别是图像内的水平偏移量和垂直偏移量，指定图像相对于鼠标光标位置的偏移量。例如，为使鼠标在图像中心，可以使用图像宽度和高度的值的一半。
    

**注意**：设置和获取数据方法中的参数 type：IE 只定义了“text”和“URL”两种有效的数据类型，而 HTML5 则对此加以扩展，允许指定各种 MIME 类型。HTML5 也支持“text”和“URL”，但这两种类型会被映射为“text/plain”和“text/uri-list”。

**注意**：保存在 dataTransfer 对象中的数据只能在 drop 事件处理程序中读取。


#### 属性
dataTransfer 对象还有一些属性，通过这些属性，我们能够设置拖动的数据能够接受什么操作，或者获取拖拽的文件等。

1. `dropEffect` 
    设置实际的放置效果，它应该始终设置成 effectAllowed 的可能值之一。
    分配任何其他值时不会有任何影响并且保留旧值。
    要使用该属性，必须在 draggenter 事件处理程序中针对放置目标来设置它。
    另外，dropEffect 属性只有搭配 effectAllowed 属性才有用。
    可能的值：
    * copy: 将正在拖动的数据从其目前的位置复制到要放置位置
    * move: 将正在拖动的数据从其目前的位置移动到要放置位置
    * link: 将正在拖动的数据在源位置与投放位置之间建立某些形式的关联或连接
    * none: 禁止放置（禁止任何操作）
    
2. `effectAllowed`
    用来指定拖动时拖动数据被允许的操作。
    分配任何其他值时不会有任何影响并且保留旧值。
    必须在 draggstart 事件处理程序中设置 effectAllowed 属性。
    可能的值:
    * copy: 可以将拖动数据从其目前的位置复制到放置位置
    * move: 可以将拖动数据从其目前的位置移动到放置位置
    * link: 可以为拖动数据在其目前的位置和放置的位置之间建立某些形式的关联或连接
    * copyLink: 允许 copy 和 link 操作
    * copyMove: 允许 copy 和 move 操作
    * linkMove: 允许 link 和 move 操作
    * all: 允许所有的操作.
    * none: 禁止所有操作.
    * uninitialized: 缺省值（默认值），相当于 all。

    在一次拖拽操作中，绑定在 dragenter 事件以及 dragover 事件上的监听器能够检查 effectAllowed 属性来确认哪些操作是允许的。在这些事件中，可以设定一个相关的属性 dropEffect，用以指定一种允许的操作。该属性的值可以是 none, copy, move或者 link。不能是形如 copyMove 这样的复合值。
    
    **注意**：effectAllowed 和 dropEffect 能够在一定程度上影响拖拽时鼠标的形状。例如，当 effectAllowed 和 dropEffect 都支持 copy 操作的时候，拖拽元素到目标元素上时，鼠标会多出一个加号，表示允许放置操作。但是这种提示是依赖浏览器的实现的，兼容性和一致性和差。所以建议在事件监听器中特别设置 css 来进行操作提示(可放置、不可放置等)。

    
3. `files`
    一个 FileList 对象，包含一个在数据传输上所有可用的本地文件列表。
    如果拖动操作不涉及拖动文件，此属性是一个空列表。
    此属性访问指定的 FileList 中无效的索引将返回未定义（undefined）。
    
4. `types`
    保存一个被存储数据的类型列表作为第一项，顺序与被添加数据的顺序一致。
    如果没有添加数据将返回一个空列表。


### 拖拽数据
拖拽发生时，数据需与拖拽关联，以标识谁在被拖拽。如，当拖拽文本框中的选中文本时，关联到拖拽的数据即是文本本身。类似，拖拽网页上的链接时，拖拽数据即是链接的 URL。

拖拽数据包含两类信息：类型(type)或者格式(format)或者数据(data)，和数据的值(data value)。

- 格式即是一个表示类型的字符串（如，对于文本数据来说，格式为 "text/plain"），数据值为文本字串。当拖拽开始时，你需要通过提供一个类型以及数据来为拖拽添加数据。拖拽过程中，在 dragenter 和 dragover 事件侦听中，需根据数据类型检测是否支持 drop（放下）。如，接收超链接的 drop 目标会检测是否支持链接类型 text/uri-list。在 drop 事件中，侦听者（即事件处理函数）会获取拖拽中的数据并将其插入到 drop 位置。

- 类型指的是 MIME-type ，与 string 类似，如 text/plain 或者 image/jpeg。也可以自定义类型。常用类型列表参见：[Drag Types](https://developer.mozilla.org/zh-CN/docs/DragDrop/Recommended_Drag_Types)。

#### 设置拖放数据
在拖动文本框中的文本时，浏览器会调用 setData() 方法，将拖动的文本以“text”格式保存在 dataTransfer 对象中。类似地，在拖放链接或图像时，会调用 setData() 方法并保存 URL。当然，我们也可以在 dragstart 事件处理程序中调用 setData()，手工保存自己要传输的数据，以便将来使用。

下面就是简单的设置与获取数据的例子：

```js
// 设置文本数据：数据值是"Text to drag"，它的格式是"text/plain"
event.dataTransfer.setData("text/plain", "Text to drag");

// 设置 URL 数据：数据值是"HTML://www.w3cmm.com/"，格式是"URL"
event.dataTransfer.setData("URL", "HTML://www.w3cmm.com/");

// 也可以通过多次调用 setData 方法增加不同的格式。
// 格式顺序需从 具体 到 一般。
var dt = event.dataTransfer;
dt.setData("application/x-bookmark", bookmarkString);
dt.setData("text/uri-list", "http://www.mozilla.org");
dt.setData("text/plain", "http://www.mozilla.org");
```

示例中的“application/x-bookmark”是一个自定义类型。一般来说别的应用是不支持这种类型的，但可以在同一个网站或应用内的拖拽中使用这样的自定义类型。与此同时，通过提供更为一般的数据类型，你也可以使这一拖拽被其它应用支持。'application/x-bookmark'类型的数据你可以设置得具体一些，为了使其它应用支持而设置的较为一般的数据类型的数据可以简单些，比如仅仅设置一个 URL 或者一段文本。另外，"text/rui-list"和"text/plain"类型的数据内容都相同。这样的设定经常发生，但也不是非这么设定不可。

**注意**：若你试图两次以同一格式设定数据，新数据会替代旧数据，数据的位置还是和旧的一样。

**注意**：Firefox 在其第 5 个版本之前不能正确地将“URL”和“text”映射为“text/uri-list”和“text/plain”。但是却能把“Text”映射为“text/plain”。

为了更好地在跨浏览器的情况下从 dataTransfer 对象取得数据，最好在取得 URL 数据时检测两个值，而在取得文本数据时使用“text”：

```js
var dataTransfer = event.dataTransfer;
//读取URL
var url = dataTransfer.getData("url") || dataTransfer.getData("text/uri-list");
//读取文本
var text = dataTransfer.getData("Text");
```


### 拖动反馈图片
借助 dataTransfer 对象的 setDragImage 方法，可以自定义一个拖动反馈图片。

下面是一个设置拖动反馈图片为绘制的 canvas 元素的示例：

```js
function dragWithCustomImage(event)
{
  var canvas = document.createElementNS("http://www.w3.org/1999/xhtml","html:canvas");
  canvas.width = canvas.height = 50;

  var ctx = canvas.getContext("2d");
  ctx.lineWidth = 4;
  ctx.moveTo(0, 0);
  ctx.lineTo(50, 50);
  ctx.moveTo(0, 50);
  ctx.lineTo(50, 0);
  ctx.stroke();

  var dt = event.dataTransfer;
  dt.setData('text/plain', 'Data to Drag');
  dt.setDragImage(canvas, 25, 25);
}
```

### 指定拖放目标
为了能够将一个元素拖动到另一个元素中，需要在目标元素的 dragenter 和 dragover 事件中，阻止浏览器的默认行为，从而说明目标元素是允许放置拖放元素的。

当然，可能也会希望在某些特定情况下，目标元素才允许放置被拖动的元素。比如，希望被拖动的元素是一个链接元素，可以如下操作：

```js
function doDragOver(event)
{
  var isLink = event.dataTransfer.types.contains("text/uri-list");
  if (isLink)
    event.preventDefault();
}
```
示例中，通过 contains 方法来判断拖动数据中是否含有 type 为"text/uri-list"的数据。如果有这种类型的数据，说明就是 URL 元素，则阻止事件的默认行为，从而就能允许放置拖拽的元素。

当然，也可以设置 dropEffect，然后在 drop 事件中判断 dropEffect 是否和 effectAllowed 相符来决定是否执行放置操作。

### 执行放置操作
在目标元素上，当用户放置了元素时，会触发 drop 事件，此时可以在其监听器中做一些操作，以完成最终的拖放操作。

在 drop 监听器中，一般会调用 dataTransfer 对象的 getData() 方法来获取拖放数据，

另外，最好在 drop 监听器中，取消浏览器的默认放置行为，否则可能会有不希望的操作方式，比如，拖放一个 URL 的时候，如果不阻止浏览器默认行为，FireFox 浏览器会打开这个链接。

下面的示例是当我们拖放链接数据的时候，会在拖放目标中添加对应的链接：

```js
function doDrop(event)
{
  var links = event.dataTransfer.getData("text/uri-list").split("\n");
  for each (var link in links) {
    if (link.indexOf("#") == 0)
      continue;

    var newlink = document.createElement("a");
    newlink.href = link;
    newlink.textContent = link;
    event.target.appendChild(newlink);
  }
  event.preventDefault();
}
```

**注意**：如果 dropEffect 和 effectAllowed 设置的值不匹配('move'和'copyMove'匹配，和'copy'不匹配)，那么就不会拖拽结束的时候，不会触发 drop 事件。

被拖拽的元素的 dropend 事件是在目标元素的 drop 事件之后触发的。一般会在 dropend 事件中做一些拖放操作的清理工作，比如将被拖放的元素从原来的位置处删除。

由于不论拖放是否成功(即 drop 事件是否触发)，dropend 事件都会触发，所以建议在拖动元素的时候，从元素原本存在位置处清除拖动元素的操作可以放在 drop 事件中处理。

