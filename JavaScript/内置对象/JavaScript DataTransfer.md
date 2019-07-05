> [MDN DataTransfer](https://developer.mozilla.org/zh_CN/docs/Web/API/DataTransfer)

DataTransfer 对象是为了在拖放操作时实现数据交换而引入的。它是拖放事件对象的一个属性，用于从被拖动元素向放置目标传递字符串格式的数据。因为它是拖放事件对象的属性，所以只能在拖放事件的事件处理程序中对其访问，而且不能单独创建。

> 注意：在原生 js 代码中，DataTransfer 对象是位于事件对象上的，使用`e.dataTransfer`方式来引用；而在 jQuery 中，则需要使用`e.originalEvent.dataTransfer`的方式来引用。

## 一、方法

DataTransfer 对象有多个方法，一般会用到`setData`和`getData`方法来设置和获取数据。

### 1.1 addElement

设置拖动源。通常不需要改变这项，如果修改这项将会影响拖动的那个节点和 dragend 事件的触发。默认目标是被拖动的节点。

语法：`void addElement(element)`
    
参数`element`表示要添加的元素，需要为一个 Element 对象。

### 1.2 clearData

删除给定类型的数据。如果不指定类型，则会删除全部的数据。

语法：`void clearData([type])`
    
可选类型参数`type`表示要删除的数据类型。如果不存在指定类型的数据，或数据传输不包含任何数据，此方法将没有任何效果。

### 1.3 getData

检索（取得）给定类型的数据。这个方法将仅仅在放置动作发生时的`drop`事件中是有效的，也就是说，其他拖放事件中无法通过该方法获得有效数据。

语法：`String getData(type)`

参数`type`表示要检索的数据类型。

如果给定类型的数据不存在或者数据转存(data transfer)没有包含数据，方法将返回一个空字符串。如果试图获取从不同域中拖动的数据将发生一个安全性错误或者无法访问。

### 1.4 setData

为一个给定的类型设置数据。

语法：`void setData(type, data)`

参数`type`表示要添加的数据类型；参数`data`表示要添加的数据。
    
如果该数据类型不存在，它将添加到的末尾，这样类型列表中的最后一个项目将是新的格式。如果已经存在的数据类型，替换相同的位置的现有数据。也就是，当更换相同类型的数据时，不会更改类型列表的顺序。

> **注意**：设置和获取数据方法中的参数`type`：
>   IE 只定义了“text”和“URL”两种有效的数据类型，而 HTML5 则对此加以扩展，允许指定各种 MIME 类型。
>   HTML5 也支持“text”和“URL”，但这两种类型会被映射为“text/plain”和“text/uri-list”。

### 1.5 setDragImage

自定义一个期望的拖动时的图片。大多数情况下，这项不用设置，因为会从被拖拽的目标（即 dragstart 事件被触发的元素）处自动产生一个半透明的图片（反馈图片），该图片在拖拽过程中会跟随鼠标指针移动。

如果被拖动元素是 HTML img 元素、 HTML 画布元素或一个 XUL 图像元素，则会使用图像数据[拖动时的效果将使用这里的图片]。否则应该是可见的节点，根据这个可见的节点创建拖动图像。

语法：`void setDragImage(image, x, y)`

参数：

* `imgage`是指向一个图片元素对象的引用，通常是一个图片元素对象，但也可以是一个 Canvas 或其它任何元素对象。这个图片在页面上怎么显示，它作为拖拽的反馈图片就怎么显示。可以使用不在该文档中的图片或者 Canvas。如果参数`image`是`null`，任何自定义拖动图像都会被清除，并且使用默认值代替。
* `x`指定图像相对于鼠标光标位置的水平偏移量。
* `y`指定图像相对于鼠标光标位置的垂直偏移量。例如，为使鼠标在图像中心，可以将`x`和`y`分别使用图像宽度和高度的值的一半。

## 二、属性

dataTransfer 对象还有一些属性，通过这些属性，可以设置拖动的数据能够接受什么操作，或者获取拖拽的文件等。

### 2.1 items

`event.dataTransfer.items`是一个 DataTransferItemList 类型的对象，类似一个数组，包含全部已设置数据类别。其中的每个数据均为` DateTransferItem`对象，包含两个属性：

* `kind` 数据值的格式，常见的有 string 和 File。
* `type` 数据类型，常见的有`text/plain`、`text/url-list`。

### 2.2 types

`event.dataTransfer.types`是一个包含全部已设置数据的类型的数组，其中的类别是按照设置的数据的顺序排列的。如果没有添加数据将返回一个空列表。

如，使用`event.dataTransfer.setData('text/plain', 'test')`之后，`event.dataTransfer.types`即为`['text-plain']`。

### 2.3 files

是一个 FileList 对象，包含一个在数据传输上所有可用的本地文件列表。如果拖动操作不涉及拖动文件，此属性是一个空列表。

此属性访问指定的 FileList 中无效的索引将返回未定义(undefined)。

### 2.4 effectAllowed

用来指定拖动时，拖动数据被允许的操作。分配任何其他值时不会有任何影响，并且保留旧值。必须在`draggstart`事件处理程序中设置`effectAllowed`属性。

可能的值有如下几种：

* `copy` 可以将拖动数据从其目前的位置复制到放置位置
* `move` 可以将拖动数据从其目前的位置移动到放置位置
* `link` 可以为拖动数据在其目前的位置和放置的位置之间建立某些形式的关联或连接
* `copyLink` 允许`copy`和`link`操作
* `copyMove` 允许`copy`和`move`操作
* `linkMove` 允许`link`和`move`操作
* `all` 允许所有的操作
* `none` 禁止所有操作
* `uninitialized` 缺省值(默认值)，相当于`all`。

### 2.5 dropEffect

设置实际的放置效果，它应该始终设置成`effectAllowed`的可能值之一。分配任何其他值时不会有任何影响并且保留旧值。要使用该属性，必须在`draggenter`事件处理程序中*针对放置目标来设置*它。

另外，`dropEffect`属性只有搭配`effectAllowed`属性才有用。

可能的值有：

* `copy` 将正在拖动的数据从其目前的位置复制到要放置位置
* `move` 将正在拖动的数据从其目前的位置移动到要放置位置
* `link` 将正在拖动的数据在源位置与投放位置之间建立某些形式的关联或连接
* `none` 禁止放置（禁止任何操作）

该属性的值不能是形如`copyMove`这样的复合值。

### 2.6 effectAllowed 和 dropEffect 的使用

`effectAllowed`和`dropEffect`有两个用处：一是可以设置元素被拖拽时的鼠标样式，二是可以设置元素是否可被放置(也即是，可以影响是否会触发`drop`事件)。

在一次拖拽操作中，绑定在`dragenter`事件以及`dragover`事件上的监听器能够通过设置`dropEffect`属性的值来影响当前拖拽的元素能否在当前元素中被放置。

例如，当`effectAllowed`和`dropEffect`都支持`copy`操作的时候，拖拽元素到目标元素上时，鼠标会多出一个加号，表示允许放置操作。但是这种提示是依赖浏览器的实现的，兼容性和一致性和差。所以建议在事件监听器中特别设置`css`来进行操作提示(可放置、不可放置等)。

对于如下的 HTML：

```html
<div id="draggable" draggable="true">Drag Me</div>
<div id="dropzone"></div>
```

当在拖动`#draggable`的时候，设置`effectAllowed`为`copy`，而拖动到`.dropzone`设置`dropEffect`为`move`，此时释放拖动的元素，那么将不会触发`drop`事件：

```JavaScript
let draggable = document.getElementById('draggable')
draggable.addEventListener('dragstart', event => {
  event.target.style.opacity = 0.5  
  event.dataTransfer.effectAllowed = 'copy'
})

let dropzone = document.getElementById('dropzone')
dropzone.addEventListener('dragenter', event => {
  event.dataTransfer.dropEffect = 'move'
  event.preventDefault();
})
dropzone.addEventListener('dragover', event => {
  event.dataTransfer.dropEffect = 'move'
  event.preventDefault();
})
dropzone.addEventListener('drop', event => {
  console.log('drop')
})
```



