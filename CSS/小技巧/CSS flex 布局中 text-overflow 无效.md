CSS 中设置一行元素超过指定长度之后自动隐藏，可以使用如下的样式：

```css
.text-ellipsis {
    display: block;
    text-overflow: ellipsis;
    overflow-x: hidden;
    white-space: nowrap;
}
```

但是这种样式**只对有设置宽度的块级元素有效**，使用 flex 后不会显示。

这是由于，在 flex 中如果容器的宽度小于 flex item 本身的宽度，flex item 会拒绝收缩，除非给 flex item 的`min-width`、`max-width`或`width`指定一个值。所以，为了在 flex 布局中生效，可以为 flex item 设置一个最小宽度即可：

```css
.text-ellipsis {
    display: block;
    text-overflow: ellipsis;
    overflow-x: hidden;
    white-space: nowrap;
    min-width: 0;
}
```

> 参考：[Text not truncated with ellipsis](https://bugzilla.mozilla.org/show_bug.cgi?id=1086218#c4)


