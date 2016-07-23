## HTML
### 基本结构
`input`的类型设为`file`：

```html
<label for="img_input">选择文件</label>
<input id="img_input" type="file"/>
```

如果想上传多文件，可以给`input`元素添加`multiple`属性：

```html
<input type='file' name='img' multiple='multiple' />
```

限制提交的文件类型用`accept="MIME_type"`属性，该属性值可以为用逗号隔开的多个 [MIME 类型](https://developer.mozilla.org/zh-CN/docs/Web/HTML/Element/Input)。同时，服务器端也要做好类型检测，双保险：

```html
<input type="file" accept="image/gif, image/jpeg" />
<input type="file" accept="image/*"/>
```

### 上传框美化
由于默认情况下，`input`标签在各个浏览器中样式不统一，而且都很丑。我们可以使用其他方法来将其美化一下。

**方法一**
使用 JavaScript。
将`input`元素隐藏，然后通过其他元素的事件来触发`input`元素的点击事件。比如，可以设置一个`div`元素包裹着设置了`display: none;`的`input`元素，然后当`div`元素被点击的时候，使用 JavaScript 代码触发`input`元素的`click`事件，就能进行文件选择了。

**方法二**
使用`label`元素。
由于`label`元素可以指向一个`input`元素，当点击`label`的时候，其指向的`input`元素就会被激活，所以，我们可以将`input`元素隐藏，而修改`label`元素的样式，也能达到美化效果。

## 相关 API
[W3C文档](https://www.w3.org/TR/file-upload/)

目前 HTML 5 提供了以下的一些接口来操作文件：

- FileList - File 对象的类数组序列（考虑多文件上传或者从桌面拖动目录或文件）。
- File - 独立文件；提供只读信息，例如名称、文件大小、mimetype 和对文件句柄的引用。
- FileReader - 读取File或Blob
- DataTransfer - 通过拖拽来生成 FileList 对象
- Blob - 可将文件分割为字节范围。
- URL scheme

可以通过下面的方式来验证浏览器是否支持 File API：

```js
// 检测是否支持File API
if (window.File && window.FileReader && window.FileList && window.Blob) {
  //  支持
} else {
  alert('不支持');
}
```

### FileList
[MDN FileList](https://developer.mozilla.org/zh-CN/docs/Web/API/FileList)

一个 FileList 对象通常来自于一个 HTML input 元素的 files 属性，或者来自用户的拖放操作(查看 [DataTransfer](https://developer.mozilla.org/zh-CN/docs/DragDrop/DataTransfer) 对象了解详情)。

可以通过这个对象访问到用户所选择的文件。这个对象一般是由一个或多个 [File](https://developer.mozilla.org/zh-CN/docs/Web/API/File) 对象组成的类数组对象。每个 File 对象在其中是通过数字索引来获取(索引从 0 开始)。所以这个对象也有`length`属性来表示其中包含的 File 对象的个数。

比如，对于`type`属性为`file`的`<input>`元素，我们可以通过下面的方式获得 FileList 对象，以及其中的第一个 File 文件对象：

```js
// FileList 对象
var list = document.getElementById('fileItem').files;
// File 对象
var file = list[0];
```

### File
[MDN File](https://developer.mozilla.org/zh-CN/docs/Web/API/File)

File 接口提供了文件的信息，以及存取文件内容的方法。

通常情况下，File 对象是来自用户在一个`<input>`元素上选择文件后返回的[`FileList`对象](https://developer.mozilla.org/zh-CN/docs/Web/API/FileList)(files 属性)，也可以是来自由拖放操作生成的 [DataTransfer](https://developer.mozilla.org/zh-CN/docs/DragDrop/DataTransfer) 对象。

**属性**

- `name` 当前 File 对象所引用文件的文件名，不包含任何路径信息。只读。
- `size` 当前 File 对象所引用文件的文件大小，单位为字节，64位整数。只读。
- `type` 当前 File 对象所引用文件的类型(MIME类型)，如果类型未知，则返回空字符串。只读。
- `lastModifiedDate` 当前 File 对象所引用文件最后修改时间。只读。

**方法**
虽然 File 对象也有一些方法可以用来获取文件内容，但都是非标准的，建议使用 [FileReader](https://developer.mozilla.org/zh-CN/docs/Web/API/FileReader) 对象来获取文件内容(可以查看 [如何在web应用程序中使用文件](https://developer.mozilla.org/zh-CN/Using_files_from_web_applications))

### FileReader
[MDN FileReader](https://developer.mozilla.org/zh-CN/docs/Web/API/FileReader)

使用 FileReader 对象，web 应用程序可以异步的读取存储在用户计算机上的文件(或者原始数据缓冲)内容，可以使用 File 对象或者 Blob 对象来指定所要处理的文件或数据。其中 File 对象可以是来自用户在一个 <input> 元素上选择文件后返回的 FileList 对象，也可以来自拖放操作生成的 DataTransfer 对象，还可以是来自在一个 HTMLCanvasElement 上执行 mozGetAsFile() 方法后的返回结果。

创建一个 FileReader 对象：

```js
var reader = new FileReader();
```

**属性**

- `error` 在读取文件时发生的错误。只读。
- `readyState`	 表明 FileReader 对象的当前状态，整数。值为状态常量中的一个。只读。
- `result` 读取到的文件内容。这个属性只在读取操作完成之后才有效，并且数据的格式取决于读取操作是由哪个方法发起的。只读。

**状态常量**

| 常量名   |   值   |   描述           |
|---------|--------|-----------------|
| EMPTY   |   0    | 还没有加载任何数据 |
| LOADING |   1    | 数据正在被加载    |
| DONE    |   2    | 完成全部的读取请求 |

**方法**

- `void abort()`
    中止该读取操作。在返回时，readyState 属性的值为`DONE`。
    当该 FileReader 对象没有在进行读取操作时(也就是 readyState 属性的值不为`LOADING`时)，调用该方法会抛出该异常。
    
- `void readAsArrayBuffer(in Blob blob)`
    开始读取指定的 Blob 对象或 File 对象中的内容。
    参数`blob`表示将要读取到一个 ArrayBuffer 中的 Blob 对象或者 File 对象。
    当读取操作完成时，readyState 属性的值会成为`DONE`。
    如果设置了`onloadend`事件处理程序，则调用之。
    同时，result 属性中将包含一个`ArrayBuffer`对象以表示所读取文件的内容。
    
- `void readAsBinaryString(in Blob blob)`
    和`readAsArrayBuffer()`方法类似，只是其调用结束后，result 属性中将包含所读取文件的`原始二进制数据`。
    
- `void readAsDataURL(in Blob blob)`
    和`readAsArrayBuffer()`方法类似，只是其调用结束后，result 属性中将包含一个`data: URL`格式的字符串以表示所读取文件的内容。
    一般用这个方法来读取一个图片，从而实现图片的本地预览。(注: IE10 以下的版本不支持 FileReader() 构造函数。不过可以利用滤镜来兼容旧版本的 IE：[兼容 IE 的图片本地预览](https://mdn.mozillademos.org/files/3699/crossbrowser_image_preview.html))
    
- `void readAsText(in Blob blob, [optional] in DOMString encoding)`
    和`readAsArrayBuffer()`方法类似，只是其调用结束后，result 属性中将包含一个`字符串`以表示所读取的文件内容。
    其中，第二个参数可选，表示返回数据所使用的编码。如果不指定，默认为 UTF-8。

**事件**

- `onabort` 当读取操作被中止时调用。
- `onerror` 当读取操作发生错误时调用
- `onload` 当读取操作成功完成时调用。
- `onloadend` 当读取操作完成时调用，不管是成功还是失败。该处理程序在 onload 或者 onerror 之后调用。
- `onloadstart` 当读取操作将要开始之前调用。
- `onprogress` 在读取数据过程中周期性调用。

**兼容性**
Safari 和 IE 10- 不支持。

### DataTransfer
参见：[MDN DataTransfer](https://developer.mozilla.org/zh_CN/docs/Web/API/DataTransfer)

## JS 操作
可以参考 MDN 的一个文档：[在web应用中使用文件](https://developer.mozilla.org/zh-CN/docs/Using_files_from_web_applications)

### 获取文件内容
`input`元素有一个`files`属性，当选择了文件之后，我们可以通过原生 JavaScript 代码或者 jQuery 代码操作这个属性，来获得这个(这些)文件的内容。如下：

```js
// JavaScript
var file = document.getElementById('img_input').files[0];

// jQuery
var file = $('#img_input').prop('files')[0];
```

### 上传文件
XMLHttpRequest Level 2 添加了一个新的接口`FormData`。利用 FormData 对象，我们可以通过 JavaScript 用一些键值对来模拟一系列表单控件。比起普通的Ajax，使用 FormData 的最大优点就是我们可以异步上传一个二进制文件。

> 当然，也可以设置`form`表单元素的`enctype="application/x-www-form-urlencoded"`，而不能用一般的`enctype="multipart/form-data"`，然后用 jQuery 序列化表单内容再上传。

```js
// 创建 FormData 对象
var form_data = new FormData();

// 获取文件内容
var file_data = $("#img_input").prop("files")[0];

// 添加表单信息
form_data.append("id", "001");
form_data.append("name", "test");
form_data.append("img", file_data);

$.ajax({
    type: "POST",
    url: "....",
    dataType : "json",
    processData: false,  // 注意：让jQuery不要处理数据
    contentType: false,  // 注意：让jQuery不要设置contentType
    data: form_data
}).success(function(msg) {
    console.log(msg);
}).fail(function(msg) {
    console.log(msg);
});
```

### 多文件上传
* 方法一：如果后台接口允许多文件上传，就把文件存到一个变量后上传。
* 方法二：如果后台接口要求单个文件，就循环获取文件信息提交，Ajax 使用同步上传`async: false`。

### 选取并预览图片：
本地的图片没有办法通过一个 URI 来引入浏览器中进行预览，所以就只能获取选取的图片的内容，并转换成 Base64 编码，以便能在浏览器中显示出来。

示例代码如下：

```html
<style>
.preview_box img {
  width: 200px;
}
</style>

<input id="img_input" type="file" accept="image/*"/>
<label for="img_input"></label>
<div class="preview_box"></div>

<script>
$("#img_input").on("change", function(e){

  var file = e.target.files[0]; //获取图片资源

  // 只选择图片文件
  if (!file.type.match('image.*')) {
    return false;
  }

  var reader = new FileReader();

  reader.readAsDataURL(file); // 读取文件内容

  // 渲染文件
  reader.onload = function(arg) {

    var img = '<img class="preview" src="' + arg.target.result + '" alt="preview"/>';
    $(".preview_box").empty().append(img);
  }
});
</script>
```

### 拖拽上传
拖拽上传和一般的`input`元素选择文件进行上传没有什么不同，只是其操作形式上的区别：可以通过将文件拖拽到指定区域从而进行上传。

拖拽的三个相关事件：
    * dragover
    * dragenter
    * drop
    
相关的 HTML 代码：

```html
<div id="drop_zone">Drop files here</div>
<ul id="list"></ul>
```

原生 JavaScript 代码：

```js
 // 必须阻止dragenter和dragover事件的默认行为，这样才能触发 drop 事件
function fileSelect(evt) {

  evt.stopPropagation();
  evt.preventDefault();

  var files = evt.dataTransfer.files; // 文件对象
  var output = [];

  // 处理多文件
  for (var i = 0, f; f = files[i]; i++) {
    output.push('<li><strong>', 
                escape(f.name), 
                '</strong> (', 
                f.type || 'n/a', 
                ') - ',
                f.size, 
                ' bytes, last modified: ',
                f.lastModifiedDate.toLocaleDateString(), 
                '</li>');
  }
  // 显示文件信息
  document.getElementById('list').innerHTML = output.join('');
}

function dragOver(evt) {
  evt.stopPropagation();
  evt.preventDefault();
  evt.dataTransfer.dropEffect = 'copy';
}

// Setup the dnd listeners.
var dropZone = document.getElementById('drop_zone');
dropZone.addEventListener('dragover', dragOver, false);
dropZone.addEventListener('drop', fileSelect, false);
```

jQuery 代码：
其他代码可以不变，注意监听事件的时候的，由于 jQuery 的封装，数据存放的字段有变，传参是`e.originalEvent`而不是`e`。

```js
$("#drop_zone").on('dragover', function(e){
  e.stopPropagation();
  e.preventDefault();
  handleDragOver(e.originalEvent);
});

$("#drop_zone").on('drop', function(e){
  e.stopPropagation();
  e.preventDefault();
  handleFileSelect(e.originalEvent);
});
```


