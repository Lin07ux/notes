原理其实很简单，可以将文本或者 JS 字符串信息借助 Blob 转换成二进制，然后，作为`<a>`元素的`href`属性，配合`download`属性，实现下载。

代码也比较简单，如下示意（兼容 Chrome 和 Firefox）：

```javascript
var funDownload = function (content, filename) {
    // 创建隐藏的可下载链接
    var eleLink = document.createElement('a');
    eleLink.download = filename;
    eleLink.style.display = 'none';
    // 字符内容转变成blob地址
    var blob = new Blob([content]);
    eleLink.href = URL.createObjectURL(blob);
    // 触发点击
    document.body.appendChild(eleLink);
    eleLink.click();
    // 然后移除
    document.body.removeChild(eleLink);
};
```

其中，`content`指需要下载的文本或字符串内容，`filename`指下载到系统中的文件名称。

不止是 .html 文件， .txt , .json 等只要内容是文本的文件，都是可以利用这种小技巧实现下载的。

在 Chrome 浏览器下，模拟点击创建的`<a>`元素即使不 append 到页面中，也是可以触发下载的，但是在 Firefox 浏览器中却不行，因此，上面的`funDownload()`方法有一个`appendChild`和`removeChild`的处理，就是为了兼容 Firefox 浏览器。

> `download`属性从 Edge13 开始支持，理论上，Edge 也应该支持直接 JS 触发的浏览器文件下载，但是没有测过。




