有时候需要保护页面上的内容，不被随意的复制，可以使用 CSS 中的`user-select`属性来做简单的防护。

> 当然，在前端页面上做防护，永远不能彻底的保护内容。因为总会有方法内容获取到，在此只是做一个最基础的防护功能。

可取值如下：

- `none` 禁止选择
- `auto` 浏览器来决定是否允许选择
- `all`  可以选择任何内容
- `text` 只能选择文本
- element 指定一个元素，使得可以选择这个元素以内的内容

`user-select`是一个 CSS 2 中的属性，但是并没有被各浏览器以标准的行为来实现，所以使用中还要加上各种前缀：

```css
.no-select {
    -moz-user-select: none;
    -ms-user-select: none;
    -webkit-user-select: none;
    user-select:none;
}
```

> IE8 及更早的 IE 不支持。

另外，还可以使用 JavaScript 来禁止选择，这样就能兼容到低版本的 IE 浏览器了：

```js
// 禁用选择
function disabledSelection () {
    // IE 浏览器
    document.onselectstart = function () { return false }
    // 其他浏览器
    document.onmousedown = function () { return false }
}
// 启用选择
function enableSelection () {
    // IE 浏览器
    document.onselectstart = null;
    // 其他浏览器
    document.onmousedown = null;
}
```



