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
将`input`元素隐藏，然后通过其他元素的事件来触发`input`元素的点击事件。比如，可以设置一个`div`元素包裹着设置了`display: none;`的`input`元素，然后当`div`元素被点击的时候，使用 JavaScript 代码触发`input`元素的`click`事件，就能进行文件选择了。

## JS 操作
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


