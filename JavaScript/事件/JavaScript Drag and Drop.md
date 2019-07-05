> MDN 文档：
> [Drag and Drop](https://developer.mozilla.org/zh-CN/docs/tag/drag%20and%20drop)
> [Drag and Drop API](https://developer.mozilla.org/zh-CN/docs/Web/API/HTML_Drag_and_Drop_API)

> 示例参考：[Codepen](https://codepen.io/Lin07ux/pen/VJBZoq)

## 一、基础

最早在网页中引入 JavaScript 拖放功能是 IE4。当时，网页中只有两种对象可以拖放：图像和某些文本。在 IE4 中，唯一有效的放置目标是文本框。到了 IE5，拖放功能得到拓展，添加了新的事件，而且几乎网页中的任何元素都可以作为放置目标。IE5.5 更进一步让网页中的任何元素都可以拖放。HTML5 以 IE 的实例为基础指定了拖放规范。

拖放和拖拽有点小区别：拖放是指用户在一个元素上点击并按住鼠标按钮，拖动它到别的位置，然后松开鼠标按钮将元素放到那儿。而拖拽则只是对元素的拖动。所以可以理解为拖放操作是需要拖拽操作来支持的。

在拖动操作过程中，被拖动元素会以半透明形式展现，并跟随鼠标指针移动。

### 1.1 条件

默认情况下，在一个 web 页面中，**选中的文本**、**images 图片**和**链接**默认可以被拖动，其它元素默认是不可拖拽的，要使其他元素要能被拖动，需要设置其 Draggable 属性为 true。

任何元素都可以**指定`draggable="true"`使其可被拖动**，同样的，设置`draggable="false"`也可以使其不可被拖动。

如下是一个示例 HTML：

```html
<div id="drag-container">
  <div class="dropzone">
    <div id="draggable" draggable="true">
      Drag Me
    </div>
  </div>
  <div class="dropzone"></div>
  <div class="dropzone"></div>
</div>
```

> **注意**：一旦元素为被设置为可拖拽，元素中的文本或其他元素就无法通过鼠标点击或拖拽来实现选中了，但可通过`Alt + 鼠标`选中文本。

### 1.2 事件

在拖拽和拖放元素的过程中，会在不同的元素上触发多个事件。HTML 的 drag 和 drop 使用了 DOM Event Model 以及从 Mouse Events 继承而来的 Drag Events。拖拽相关的事件也是会冒泡的，所以可以在拖拽相关元素的父元素上监听相应的拖拽事件。

在进行拖放操作的不同阶段会触发数种事件，被拖动元素和放置元素会分别触发不同的事件。一个典型的拖拽操作相关的事件如下：

* `dragstart` 拖起元素时，被拖起的元素上触发该事件。
* `dragenter` 当拖动元素 A 进入另一个元素 B 时，会触发 B 的`dragenter`事件。
* `dragover` 当拖动元素 A 在另一个元素 B 中移动/停止时触发 B 的`dragover`事件。文档说是每几百毫秒触发一次，Chrome 实测 1ms 左右触发；Firefox 大概是 300ms。
* `dragleave` 与`dragenter`相对应，当拖动元素 A 离开元素 B 时，触发 B 的`dragleave`事件。
* `drop` 当在拖动元素 A 到元素 B 上，释放鼠标时触发 B 的`drop`事件，相当于元素 B 接收了元素 A。
* `dragend` 在`drop`事件之后，还会触发元素 A 的`dragend`事件，这里可以对元素 A 作一些清理工作。

除了上面的事件外，还有两个一般用不到的事件：

* `drag` 拖动元素时，在被拖动的元素上持续触发该事件。
* `dragexit` 这个事件只有 Firefox 支持，和`dragleave`作用几乎相同，发生在`dragleave`之前。

可以看到，拖拽过程中的事件主要有两个源：一个是被拖拽的元素，另一个则是拖拽中经过的其他元素。

**注意**：在拖拽的时候只会触发拖拽的相关事件，而鼠标事件，例如`mousemove`是不会触发的。

### 1.3 被拖动元素

拖动元素时，在被拖放元素上将依次触发下列事件：

1. `dragstart` 拖动开始时触发，一次拖放只触发一次。在这个事件中，一般会在监听器中设置与这次拖拽相关的信息，例如拖动的数据、反馈图片、拖拽效果等。一般，只有拖拽数据是必须的，其他都可以自适应的。
2. `drag` 拖动过程中，会不断的触发，类似`touchmove`事件。
3. `dragend` 拖动停止时触发，一次拖动只触发一次。无论是把元素放到了有效的放置目标，还是放到了无效的放置目标上，都会触发该事件。

> **注意**：当从操作系统拖拽文件到浏览器的时候，`dragstart`和`dragend`事件不会触发。

浏览器在拖动过程中，不会改动被拖动元素的样式，但是可以用 js 监听`dragstart`事件，在`dragstart`事件中改动被拖动元素的样式。例如，对于前面的 HTML 代码，可以为`#draggable`元素设置拖动时半透明，拖动结束恢复原状：

```JavaScript
let draggable = document.getElementById('draggable')

draggable.addEventListener('dragstart', event => {
    event.target.style.opacity = 0.5
})

draggable.addEventListener('dragend', event => {
  event.target.style.opacity = 1
})
```

另外，在拖动时，浏览器会根据被拖动元素生成一个缩略图，跟随鼠标移动而移动。当然也能够设置这个缩略图。

### 1.4 放置元素

当元素被拖放到一个有效的放置目标上时，下列事件会依次发生在放置目标元素上：

1. `dragenter` 当拖拽中的鼠标第一次进入一个元素的时候触发。这个事件的监听器需要指明是否允许在这个区域释放鼠标。如果没有设置监听器，或者监听器没有进行操作，则默认不允许释放。当想要通过类似高亮或插入标记等方式来告知用户此处可以释放，将需要监听这个事件。
2. `dragover` 一个元素被拖动到这个元素上后，只要被拖动的元素还在该元素的范围内移动时，就会持续触发该事件。大多数时候，这个事件的监听器与`dragenter`事件的监听器是一样的。
3. `dragleave` 当拖拽中的鼠标离开元素时触发。一般这个事件的监听器需要将作为可释放反馈的高亮或插入标记去除。
4. `drop` 这个事件在拖拽操作结束时于释放元素上触发。一般这个事件的监听器用来接收被拖拽的数据并插入到释放之地。

这几个事件有一些注意事项：

* 如果用户在非可释放目标上释放了拖放，则不会触发`drop`事件，而是触发`dropleave`事件；
* 当用户取消了拖拽操作时，例如按下了 Escape（ESC）按键，不会触发`drop`事件，也不会触发`dropleave`事件。
* 当用户没有在当前元素中释放，而是继续拖动出去，则不会在当前元素上触发`drop`事件。

可以把任何元素变成有效的放置目标，方法是取消该元素`dragenter`和`dragover`事件的默认行为。而且为了避免一些元素(如`a`链接元素)的默认行为，还需要阻止其`drop`事件的默认行为：

```js
function allowDrop (event) {
  event.preventDefault()
}

let dropzones = document.querySelectorAll('.dropzone')
dropzones.forEach(dropzone => {
  dropzone.addEventListener('dragenter', allowDrop)
  dropzone.addEventListener('dragover', allowDrop)
  dropzone.addEventListener('drop', event => {
    // event.preventDefault()
    event.target.appendChild(document.getElementById('draggable'))
  })
})
```

**注意**：正常情况下，如果将可拖拽元素拖拽出去放置在其他元素中，事件的目标属性`e.target`会指向被放置的元素。但是，如果可拖拽元素初始时是在一个可放置元素的内部，先把可拖拽元素拖出去，不松开鼠标再拖拽放回来，将会触发 drop 事件，但是此时`e.target`却是被拖拽的元素自身，此时如果操作 DOM 可能就就会发生错误或者非预期效果。所以应该判断 target 元素再进行操作。关于这点可以查看示例 [Codepen](https://codepen.io/Lin07ux/pen/VJBZoq)，打开控制台，将绿色的 Drag Me 元素拖出去，不释放鼠标，再拖回来，控制台会打印出错误，显然代码没有考虑到这一点。

## 二、拖放数据

拖动是最终目的是为了对源和目标元素做一些操作。为了完成操作，需要在源和目标传输数据，可以通过设置/读取全局变量来完成，这并不是一个好习惯。在 HTML 5 中可以通过 DataTransfer 完成。

在拖放过程中的相关事件中，每个事件对象都会包含一个`dataTransfer`对象，在该对象中包含相应的拖拽数据。

> 可参考：[DataTransfer](../内置对象/JavaScript%20DataTransfer.md)

### 2.1 数据类型

拖拽发生时，数据需与拖拽关联，以标识谁在被拖拽。如，当拖拽文本框中的选中文本时，关联到拖拽的数据即是文本本身；类似，拖拽网页上的链接时，拖拽数据即是链接的 URL；拖拽图像时，拖拽数据即是该图像。

拖拽数据包含两类信息：类型(`type`)或者格式(`format`)或者数据(`data`)，和值(`data value`)。

- 格式即是一个表示类型的字符串(如，对于文本数据来说，格式为`"text/plain"`)，数据值为文本字串。当拖拽开始时，需要通过提供一个类型以及数据来为拖拽添加数据。
- 类型指的是 MIME-type，如`text/plain`或者`image/jpeg`。也可以自定义类型。常用类型列表参见：[Drag Types](https://developer.mozilla.org/zh-CN/docs/DragDrop/Recommended_Drag_Types)。

常用的数据类型有如下几种：

* `text/plain` 文本类型，拖拽网页上的文本框内文本及已选文本时会自动使用这个类别。旧版本的浏览器中使用`Text`作为文本类型。
* `text/uri-list` URL 链接类型，表示其内容是一个 URL 链接。旧版本中会使用`url`作为 URL 链接类型。
* `text/html` HTML 类型。这种类型的数据需要是序列化的 HTML。例如，使用元素的`innerHTML`属性值来设置这个类型的值是合适的。
* `text/xml` XML 类型，和 HTML 类型类似，但要确保数据值是格式良好的 XML。

当然，总是可以使用`text/plain`文本类型存储这些数据，

建议：总是添加`text/plain`类型数据作为不支持其它类型的应用或拖放目标的降级，除非没有可选的符合逻辑的文本。总是将纯文本类型添加在最后，因为这是最不具体的数据。

另外，除了上面介绍的三种常用数据，还有以下几种数据类型：Files 文件数据，Images 图片数据、Nodes 节点数据，甚至可以自定义数据。不过，这几种数据类型的支持可能并不是很好，每个浏览器可能有不同的实现。

比如，在 FireFox 浏览器中，需要使用`dataTransfer.mozSetDataAt()`方法设置数据，使用`dataTransfer.mozGetDataAt()`方法来获取设置的数据。

### 2.2 设置拖拽数据

除了文本、链接和图像被拖拽时自动设置的数据，还可以在拖拽事件`dragstart`中设置拖拽数据。

设置拖拽数据需要使用到`event.dataTransfer.setData()`方法。该方法接受两个参数：数据格式、数据内容。常见的，可以设置数据有文本、链接、文件等数据。

```JavaScript
draggable.addEventListener('dragstart', event => {
  event.target.style.opacity = 0.5
  
  let dt = event.dataTransfer
  
  // 设置文本内容
  dt.setData('text/plain', 'test')
  // 也可以使用下面的方式设置文本内容
  dt.setData('text', 'test')
  
  // 设置链接内容
  dt.setData('text/uri-list', 'https://www.baidu.com')
  
  // 设置自定义类别的数据
  dt.setData("application/x-bookmark", 'bookmarkString');
})
```

需要注意的是，可以同时为 DataTransfer 设置多种类别的数据，但每种格式的数据只能设置一种，后面的会覆盖前面设置的同类型的数据，数据的位置还是和旧的一样。

另外，数据内容需要和数据类别相符，比如对于链接类型，如果设置的不是一个合法的链接，那么该数据将不会设置成功，而且在`event.dataTransfer.items`中也不会有该类别。但对于自定义类别，则没有这个限制。

> Firefox 在其第 5 个版本之前不能正确地将`URL`和`text`分别映射为`text/uri-list`和`text/plain`，但是却能把`Text`映射为`text/plain`。

### 2.3 获取拖拽数据

拖拽数据`dataTransfer`的内容在`drop`事件中通过`event.dataTransfer.getData()`获取，该方法必须要提供一个参数表示获取的数据的类别，如：

```JavaScript
dropzone.addEventListener('drop', event => {
  // event.preventDefault()
  
  let dt = event.dataTransfer
  
  // 读取文本
  console.log(dt.getData('text/plain'))
  console.log(dt.getData('Text'))
  
  // 读取链接，为了兼容最好使用两种方式读取链接
  console.log(dt.getData('text/uri-list') || dt.getData("url"))
})
```

拖拽过程的`dragenter`、`dragover`、`dragleave`等事件的处理方法中，是不能获得拖拽数据内容的，但是可以通过 DateTransfer 对象`items`或`types`判断是否包含有指定类型的数据，然后根据数据类型检测进行相关操作，如是否允许放置拖拽的元素。

如，接收超链接的放置元素可以检测是有链接类型`text/uri-list`数据来决定是否运行当前被拖拽元素放置。

```JavaScript
dropzone.addEventListener('dragenter', event => {
  let canDrop = [].some.call(
    event.dataTransfer.items, item => item.type === 'text/uri-list'
  )
  
  // 或者可以使用 event.dataTransfer.types 数组来判断
  // let canDrop = event.dataTransfer.types.some(type => type === 'text/uri-list')
  
  canDrop && event.preventDefault()
})
```

## 三、拖放效果

拖动过程中，可以借助`dataTransfer`对象的`setDragImage`方法，自定义一个拖动反馈图片。

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

## 四、总结

> MDN文档：[拖放操作](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_and_Drop)

处理拖放通常有以下几个步骤：

- 定义可拖动目标。将我们希望拖动的元素的`draggable`属性设为`true`。更多信息参阅 [draggable 属性](https://developer.mozilla.org/zh-CN/docs/Web/Guide/HTML/Drag_operations#draggableattribute)。

- 定义被拖动的数据，可能为多种不同格式。例如，文本型数据会包含被拖动文本的字符串。更多信息参阅[拖动数据](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#Drag_Data)。

- 自定义拖动过程中鼠标指针旁边会出现的拖动反馈图片。如果未设定，默认图片会基于鼠标按钮按下的元素（正在被拖动的元素）来自动生成。要了解更多关于拖动反馈图片的内容，请参阅[设置拖动反馈图片](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#Setting_the_drag_feedback_image)。

- 设置允许拖拽的效果。有三种效果可以选择：`copy`用来指明拖拽的数据将从当前位置复制到释放的位置；`move`用来指明被拖拽的数据将被移动；`link`用来指明将在源位置与投放位置之间建立某些形式的关联或连接。在拖拽的过程中，可以修改拖拽效果来指明在某些位置允许某些效果。如果允许，将可以把数据释放到那个位置。更多信息参阅[拖拽操作](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#Drag_Effects)。

> 注意，这里影响的**是**拖拽的**数据**的行为，而**不是**拖拽的元素的**行为**。

- 定义放置区域。默认情况下，浏览器阻止任何东西向 HTML 元素放置拖拽的发生。要使一个元素成为可放置区域，需要阻止浏览器的默认行为，也就是要监听 dragenter 和 dragover 事件，在监听器中阻止浏览器的默认行为(`return false;`或者`event.preventDefault();`)。更多信息参阅[指定放置目标](https://developer.mozilla.org/zh-CN/docs/DragDrop/Drag_Operations#droptargets)。

- 在`drop`发生时做一些事情，可能想要获取拖拽目标携带的数据并做某些相应的事情。更多信息请参阅[执行放置(Performing a Drop)](https://developer.mozilla.org/zh-CN/docs/Web/Guide/HTML/Drag_operations#drop)。

对于拖拽中使用的通用数据类型列表，主要有以下几种，具体请参阅 [推荐的拖拽类型](https://developer.mozilla.org/zh-CN/docs/DragDrop/Recommended_Drag_Types)。

- 文本 Text
- 链接 Links
- HTML 和 XML
- 文件 Files
- 图片 Images
- 文档节点 Document Nodes


