Safari 浏览器中，元素在设置了`border-radius`和`transform`时，`overflow: hidden`无法正常将超出边界的内容隐藏掉，甚至`box-shadow`也无法起作用。

这个问题可以通过为该元素添加如下的 CSS 来解决：

```css
// Add on element with overflow
-webkit-mask-image: -webkit-radial-gradient(white, black);
```

> 转摘：[gistfile1.css](https://gist.github.com/ayamflow/b602ab436ac9f05660d9c15190f4fd7b)


