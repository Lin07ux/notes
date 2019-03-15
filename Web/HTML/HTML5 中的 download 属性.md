## 一、静态下载

在目前很多场景中，需要用户能够在页面中直接触发下载行为，以保存文件。之前是通过后端在响应中添加`Content-disposition`响应头来指示下载，现在也可以通过 HTML5 中的`download`属性来实现。

### 1.1 download 属性

常规的`<a>`标签通过`href`实现链接跳转，如果只想下载文件而不是跳转预览，最好的方式是在`<a>`标签中添加`download`属性，就能很简单地实现下载操作。

`download`是 HTML5 中`<a>`标签新增的一个属性，此属性会强制触发下载操作，指示浏览器下载 URL 而不是导航到它，并提示用户将其保存为本地文件。

目前大部分主流的浏览器基本都已经支持`download`属性，而 IE 浏览器都不支持。

### 1.2 后端响应式下载

在常规的 HTTP 应答中，`Content-Disposition`消息头指示回复的内容该以何种形式展示，是以内联的形式（即网页或者页面的一部分），还是以附件的形式下载并保存到本地。

在 HTTP 场景中，第一个参数或者是`inline`（默认值，表示回复中的消息体会以页面的一部分或者整个页面的形式展示），或者是`attachment`（意味着消息体应该被下载到本地；大多数浏览器会呈现一个“保存为”的对话框，将`filename`的值预填为下载后的文件名）。

如下所示：

```
Content-Disposition: attachment; filename="cool.html"
```

## 二、动态下载

对于已经存在的文件，只要有 url 就可以进行下载，而对于动态的内容，例如一些在线绘图工具所生成的图片，只使用前面两种方式并不能进行下载。此时就需要通过 JavaScript 来实现动态下载了。

### 2.1 Blob

为了实现 JavaScript 下载，需要将动态生成的图片转成 blob 数据。

Blob 数据，即 Binary Large Object，二进制类型的大对象，表示一个不可变的原始数据的类文件对象。上传文件时常用的`File`对象就继承于`Blob`，并进行了扩展用于支持用户系统上的文件。

可以通过`Blob()`构造函数来创建一个新的 Blob 对象：

```JavaScript
Blob(blobParts[, options])
```

示例如下：

```JavaScript
var debug = {hello: "world"};
var blob = new Blob([JSON.stringify(debug, null, 2)], {type : 'application/json'});

// 此时 blob 的值为：Blob(22) {size: 22, type: 'application/json'}
```

Blob 对象存在两个只读属性：

* `size` Blob 对象中所包含数据的大小（字节）。
* `type` 一个字符串，表明该 Blob 对象所包含数据的 MIME 类型。如果类型未知，则该值为空字符串。

### 2.2 URL 对象

为了能将动态文件下载下来，首先就需要为这些动态内容生成一个临时 URL。尽管 HTTP URL 需要位于同一源中，但是可以使用 `blob: URL`和`data: URL`来将下载。

通过前面的方法可以得到二进制内容，然后就可以通过`createObjectURL(blob)`来生成`blob: url`了。这会创建一个`URL(DOMString)`，包含一个唯一的 blob 链接（该链接协议为以`blob: url`，后跟唯一标识浏览器中的对象的掩码）。这个 URL 的生命周期和创建它的窗口中的 document 绑定。

在使用完成这个`blob: url`之后可以通过`URL.revokeObjectURL(objectURL)`来销毁 URL 实例。浏览器会在文档退出的时候自动释放它们，但是为了获得最佳性能和内存使用状况，应该在安全的时机主动释放掉它们。

### 2.3 实现动态下载

有了 Blob 和 URLs 对象，就可以配合`download`属性完成下载：

```JavaScript
const downloadText = (text, filename = '') {
    // text 指需要下载的文本或字符串内容
    const blob = new Blob([text], {type: 'text/plain'})  
    const a = document.createElement('a')
  
    a.download = filename
    // 会生成一个类似blob:http://localhost:8080/d3958f5c-0777-0845-9dcf-2cb28783acaf 这样的URL字符串
    a.href = window.URL.createObjectURL(blob)

    document.body.appendChild(a)
    a.click()
    a.remove()
}
```

## 三、参考

* [前端JS实现字符串/图片/excel文件下载](https://mp.weixin.qq.com/s/VwRo2XDpmwP7Yf96IusGnQ)
* [浅析 HTML5 中的 download 属性](https://zhuanlan.zhihu.com/p/58888918)


