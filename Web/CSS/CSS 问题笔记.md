### iOS 10.2.1 fixed 元素被遮盖
当给 fixed 元素的父元素设置了`overflow: hidden;`，且 fixed 元素有部分被定位到其父元素的外面的时候，fixed 元素会被父元素遮盖掉，超出部分无法显示出来。

这个问题在 Android 上和 Mac Safari 上不存在。


### transform 导致 fiexd 元素失效
在 Chrome 和 Opera 浏览器下，使用 CSS3 的`transform: translate(0, 0)`转化位置节点，其所有使用`position: fixed`定位的子孙节点的定位功能均无效。

