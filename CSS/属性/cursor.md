摘自：[CSS秘密花园： 挑选合适的光标](http://www.w3cplus.com/css3/css-secrets/picking-the-right-cursor.html)

鼠标指针的意义并不仅仅是为了显示目前屏幕上的光标在何处，还是为了告诉用户可以进行哪些交互动作。

### 光标类别

目前，CSS 中有如下一些光标，其中，后者是新增的光标：

![css-cursor-01](http://cnd.qiniu.lin07ux.cn/2016-04-13%20css-cursor-01.png)

![css-cursor-02](http://cnd.qiniu.lin07ux.cn/2016-04-13%20css-cursor-02.png)

### 隐藏光标

在一切情景下，我们也需要隐藏掉光标，如，那些用于消息展示和机上娱乐的东西，或者是当你观看视频时。

在 CSS 2.1 中是可以使用一个透明的 1x1 的透明 GIF 图来隐藏光标，如下：

```css
video {
    cursor: url(transparent.gif);
}
```

现在，我们可以直接设置元素的光标为 none 即可：`cursor: none;`。
不过，为了兼容和优雅降级，我们一般可以如下设置：

```css
video {
    cursor: url('transparent.gif');
    cursor: none;
}
```



