HTML5 提供了 File API 相关规范，主要涉及 File 和 FileReader 对象，还涉及到 FileList。

## 一、基本

### 1.1 兼容性

File API 相关的接口在当前主流的 Chrome、FireFox、Safari 中支持度都较好，而且在移动端的兼容性也很好，但在 IE/Edge、Opera 中还是有些兼容问题的。

兼容性如下图所示：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1563942268039.png" width="1273"/>

[File API - caniuse](https://caniuse.com/#search=file)

可以通过如下方式检查兼容性：

```JavaScript
if (!(window.File && window.FileReader && window.FileList && window.Blob)) {
  throw new Error("当前浏览器对 File API 的支持不完善");
}
```

### 1.2 JavaScript 获取本地文件

为了确保安全，浏览器中 JavaScript 一般不能直接和本地系统的文件进行交互，需要由用户的操作来实现。比如，通常情况下， File 对象是来自用户在一个`<input type="file">`元素上选择文件后返回的 FileList 对象，或者是来自由拖放操作生成的 DataTransfer 对象。

选择文件：

```html
<input type="file" id="files" multiple />

<script>
document.querySelector("#files").addEventListener("change", function (event) {
  const files = event.target.files;
  
  if (!files.length) {
    console.log("没有选择文件");
    return;
  }

  console.log("选中的文件信息是：", files);
}, false);
</script
```

拖拽文件：

```html
<div id="container"></div>

<script>
const target = document.querySelector("#container");

target.addEventListener("dragover", function (event) {
  event.stopPropagation();
  event.preventDefault();
});

target.addEventListener("drop", function (event) {
  event.stopPropagation();
  event.preventDefault();
  
  const files = event.dataTransfer.files;
  console.log(files);
});
</script>
```

## 二、相关对象

### 1.1 File

JavaScript 中，文件对应的对象就是 File。File 对象是特殊类型的 [Blob](https://developer.mozilla.org/zh-CN/docs/Web/API/Blob)，且可以用在任意的 Blob 类型的 context 中。比如说，`FileReader`、`URL.createObjectURL()`、`createImageBitmap()`及`XMLHttpRequest.send()`都能处理 Blob 和 File。

[MDN 文档](https://developer.mozilla.org/zh-CN/docs/Web/API/File)

#### 1.1.1 属性

File 对象继承与 Bolb，有如下一些属性：

* `lastModified` 只读，返回当前 File 对象所引用文件最后修改时间，自 UNIX 时间起始值(1970年1月1日 00:00:00 UTC)以来的毫秒数。
* `lastModifiedDate` 只读，返回当前 File 对象所引用文件最后修改时间的 Date 对象。但是这个是废弃属性，不建议使用了。
* `name` 只读，返回当前 File 对象所引用文件的名字。
* `size` 只读，返回文件的大小。
* `webkitRelativePath` 只读，返回 File 相关的 path 或 URL。该属性并非全部浏览器都支持。
* `type` 只读，返回文件的 MIME Type，如常见的`images/jpeg`、`images/png`、`text`等。

#### 1.1.2 方法

File 接口没有定义任何方法，但是它从 Blob 接口继承了以下方法：

* `slice([start[, end[, contentType]]])` 返回一个新的 Blob 对象，它包含有源 Blob 对象中指定范围内的数据。

该方法可以用来从 File 对象中获取一部分内容。使用方法和字符串的`String.slice()`方法很像，可以传入三个参数，分别表示如下作用：

* `start` 表示第一个会被被拷贝进新的 Blob 的字节的起始位置，默认为 0，如果传入负数，则表示从数据的末尾从后到前开始计算。
* `end` 表示会被拷贝进新的 Blob 的截止位置，而且该位置的数据不会被拷贝进新的 Blob 中。默认值为当前文件的长度(`File.size`)。
* `contentType` 给新的 Blob 赋予一个新的文档类型，这将会把它的`type`属性设为被传入的值。默认值是一个空的字符串。

#### 1.1.3 示例

下面的示例，展示了使用`File.slice()`分片读取文件内容的方法：

```JavaScript
document.querySelector("#files").addEventListener("change", function (event) {
  let files = event.target.files;
  
  if (!files.length) {
    return;
  }
  
  // 为了方便说明，这里仅仅读取第一个文件的前 5 个字节的内容
  const file = files[0].slice(0, 5);
  const reader = new FileReader();
  
  // 控制台输出结果应该是：hello
  reader.onload = ev => console.log(ev.target.result);
  reader.readAsText(file);
}, false);
```

### 1.2 FileReader

FileReader 对象允许 Web 应用程序异步读取存储在用户计算机上的文件（或原始数据缓冲区）的内容，通过 FileReader 对象的方法可以从 File 或 Blob 对象读取文件或数据。

[MDN 文档](https://developer.mozilla.org/zh-CN/docs/Web/API/FileReader)

#### 1.2.1 属性

FileReader 对象具有如下三个只读属性：

* `error` 一个 DOMException 对象，表示在读取文件时发生的错误。
* `readyState` 表示 FileReader 状态的数字，可以为如下的几种值：
    - `FileReader.EMPTY` 也就是 0，表示还没有加载任何数据；
    - `FileReader.LADING` 也就是 1，表示正在加载数据中；
    - `FileReader.DONE` 也就是 2，表示已完成全部的加载请求。
* `result` 文件的内容。该属性仅在读取操作完成后才有效，数据的格式取决于使用哪个方法来启动读取操作。

#### 1.2.2 方法

FileReader 中主要有四个读取方法和一个中断方法：

* `FileReader.abort()` 中止读取操作。在返回时，`readyState`属性为`FileReader.DONE`。
* `FileReader.readAsArrayBuffer(blob)` 开始读取指定的 Blob 中的内容，读取完成后`result`属性中保存的将是被读取文件的 ArrayBuffer 数据对象。
* `FileReader.readAsBinaryString(blob)` 开始读取指定的 Blob 中的内容。读取完成后`result`属性中将包含所读取文件的原始二进制数据。
* `FileReader.readAsDataURL(blob)` 开始读取指定的 Blob 中的内容。读取完成后`result`属性中将包含一个`data: URL`格式的字符串以表示所读取文件的内容。
* `FileReader.readAsText(blob)` 开始读取指定的 Blob 中的内容。读取完成后`result`属性中将包含一个字符串以表示所读取的文件内容。

可以看到，这四个方法分别是使用不同的方式来读取文件内容，并使得读取完成后的数据是指定格式的。

#### 1.2.3 事件

FileReader 的读取方法都是异步的，也就是说，FileReader 在读取文件过程中，会触发一系列的事件，通过这些事件可以很方便的控制文件的读取。

因为 FileReader 继承自`EventTarget`，所以 FileReader 触发的所有事件可以通过`addEventListener`方法监听使用。

FileReader 触发的事件有如下几种：

* `abort` 该事件在读取操作被中断时触发。
* `error` 该事件在读取操作发生错误时触发。
* `load` 该事件在读取操作完成时触发。
* `loadstart` 该事件在读取操作开始时触发。
* `loadend` 该事件在读取操作结束时（要么成功，要么失败）触发。
* `progress` 该事件在读取 Blob 过程中触发，可以监听当前读取进度，类似 XMLHttpRequest 的`process`事件。

#### 1.2.4 示例

下面展示读取文件，并监听读取进度，主要用到 FileReader 的`readAsArrayBuffer()`方法和`process`事件：

```JavaScript
document.querySelector("#files").addEventListener("change", function (event) {
  let files = event.target.files;
  
  if (!files.length) {
    return;
  }

  const handleLoadStart = (ev, file) => ;
  const handleProgress = (ev, file) => ;

  for (let file of files) {
    const reader = new FileReader();
    
    reader.onloadstart = () => console.log(`>>> Start load ${file.name}`);
    
    reader.onprogress = ev => {
      // 计算进度，并且以百分比形式展示
      if (ev.lengthComputable) {
        const percent = Math.round((ev.loaded / ev.total) * 100);
        
        console.log(`<<< Loding ${file.name}, progress is ${percent}%`);
      }
    };
    
    reader.readAsArrayBuffer(file);
  }
}, false);
```

### 1.3 FileList

FileList 是一个文件列表对象，与 Array 行为类似，具有长度属性，并支持数组索引方式访问其中的文件。

一个 FileList 对象通常来自于一个 HTML`<input>`元素的`files`属性，可以通过这个对象访问到用户所选择的文件，还有可能来自用户的拖放操作。

如：

```html
<input id="fileItem" type="file">
```

```JavaScript
var file = document.getElementById('fileItem').files[0];
```

[MDN 文档](https://developer.mozilla.org/zh-CN/docs/Web/API/FileList)

#### 1.3.1 属性

FileList 只含有一个属性`length`，表示其中包含的文件数量，和`Array.length`类似。

#### 1.3.2 方法

FileList 只有一个方法`item()`，用于获取其中的 File 文件对象：

* `FileList.item(index)` 根据给定的索引值，返回 FileList 对象中对应的 File 对象。参数`index`表示 File 对象在 FileList 对象中的索引值，从 0 开始。


