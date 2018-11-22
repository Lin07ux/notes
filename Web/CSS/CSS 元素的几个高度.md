转摘：[搞清clientHeight、offsetHeight、scrollHeight、offsetTop、scrollTop](http://imweb.io/topic/57c5409e808fd2fb204eef52)

每个 HTML 元素都具有`clientHeight`、`offsetHeight`、`scrollHeight`、`offsetTop`、`scrollTop`这 5 个和元素高度、滚动、位置相关的属性，单凭单词很难搞清楚分别代表什么意思之间有什么区别。通过阅读它们的文档总结出规律如下：

clientHeight 和 offsetHeight 属性和元素的滚动、位置没有关系它代表元素的高度。

1. `clientHeight`：包括 padding，但不包括 border、水平滚动条、margin 的元素的高度。对于 inline 的元素这个属性一直是 0，单位 px，只读元素。

    ![clientHeight](http://cnd.qiniu.lin07ux.cn/markdown/1472957836645.png)

2. `offsetHeight`：包括 padding、border、水平滚动条，但不包括 margin 的元素的高度。对于 inline 的元素这个属性一直是 0，单位 px，只读元素。

    ![offsetHeight](http://cnd.qiniu.lin07ux.cn/markdown/1472957923577.png)

接下来讨论出现有滚动条时的情况：当本元素的子元素比本元素高且`overflow: scroll;`时，本元素会 scroll。

3. `scrollHeight`：因为子元素比父元素高，父元素不想被子元素撑的一样高就显示出了滚动条，在滚动的过程中本元素有部分被隐藏了，`scrollHeight`代表包括当前不可见部分的元素的高度。而可见部分的高度其实就是`clientHeight`，也就是`scrollHeight >= clientHeight`恒成立。在有滚动条时讨论 scrollHeight 才有意义，在没有滚动条时 `scrollHeight == clientHeight`恒成立。单位 px，只读元素。

    ![scrollHeight](http://cnd.qiniu.lin07ux.cn/markdown/1472958097602.png)

4. `scrollTop`：代表在有滚动条时，滚动条向下滚动的距离也就是元素顶部被遮住部分的高度。在没有滚动条时`scrollTop==0`恒成立。单位 px，可读可设置。

    ![scollTop](http://cnd.qiniu.lin07ux.cn/markdown/1472958309350.png)


5. offsetTop : 当前元素顶部距离最近父元素顶部的距离,和有没有滚动条没有关系。单位px，只读元素。

    ![offsetTop](http://cnd.qiniu.lin07ux.cn/markdown/1472958356283.png)





