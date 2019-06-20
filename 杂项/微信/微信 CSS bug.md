### background-attachment: fixed

微信浏览器目前不支持`background-attachment: fixed;`背景图设置，如果需要背景图固定，可以考虑使用当前元素的伪元素设置背景，并伪元素的大小设置为当前元素的可视大小：

```css
body::before{
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top,#000A16,#0e4179 40%,#041424);
    z-index: -1;
}
```



