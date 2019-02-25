## 一般元素

### a

`target`属性设置为`_blank`值时，会在新的浏览器标签页中打开相应页面。这样可能会有一些隐性问题存在：

* 新页面和原始页面占用同一个进程。这也意味着，如果这个新页面有任何性能上的问题，比如有一个很高的加载时间，这也将会影响到原始页面的表现。
* 如果打开的是一个同域的页面，那么将可以在新页面访问到原始页面的所有内容，包括`document`对象(`window.opener.document`)。

可以给`a`元素增加`rel="noopener"`来阻止这种特性。在老浏览器中，可以使用`rel="noreferrer"`属性达到同样的效果，但是，这样也会阻止 Referer header 被发送到新页面。如下所示：

```html
<a href="https://niteshsoni.info" target="_blank" rel="noopener noreferrer"></a>
```

> 如果是通过 js 来打开新的页面的话，可以使用如下方式避免这种情况：
> 
> ```js
> var newWindow = window.open();
> newWindow.opener = null;
> ```

## FORM

* 用户单击提交按钮或图像按钮时，就会提交表单，使用 input 或者 button 都可以提交表单，只需将 type 设置为 submit 或者 image 即可。
* 同样，将 input 或者 button 的 type 设置为 reset 就能重置表单。
* 访问表单字段：使用dom节点来访问；通过表单元素的 elements 属性。

每个表单都有`elements`属性，该属性：

* 是表单中所有表单元素集合；
* 是个有序列表；
* 包含着所有字段，比如有`input`、`textarea`、`button`、`fieldset`等；
* 可以通过索引次序或者表单元素的`name`属性值来访问相应的元素。
    比如：`formId.elements[0]`可以获取表单中的第一个表单元素；
    `formId.elements['select1']`可以获取到表单中名称为`select1`的表单元素。
    如果一个表单中，有多个`name`相同的属性，那么取得数据是一个集合。

## Table

### td 

`colspan`是横向合并；`rowspan`是纵向合并。

