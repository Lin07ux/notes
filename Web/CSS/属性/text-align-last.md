## 描述
这个属性主要用来设置一个块中的最后一行行内元素的对齐方式。

可以实现下图中，右侧的效果：

![text-align-text](http://cnd.qiniu.lin07ux.cn/markdown/1466949421003.png)

## 可选值
此属性与`text-align`属性相比少了`match-parent`和`justify-all`。

```css
text-align-last: auto | start | end | left | right | center | justify | inherit;
```

默认值是`auto`。

### auto
如果`text-align-last`取值为`auto`时，其效果会受`text-align`值所影响。

比如下面的示例，我们先给`figcaption`元素设置了`text-align:right`，同时设置`text-align-last`值为`auto`。那么其效果将会根据`text-align`值来生效。

![auto 效果](http://cnd.qiniu.lin07ux.cn/markdown/1466949927443.png)

当然，这也有另外的情况。当`text-align`取值为`justify`时，`text-align-last`值为`auto`时并不会根据`text-align:justify`来渲染效果，而是始终会左边对齐。

![auto 效果2](http://cnd.qiniu.lin07ux.cn/markdown/1466950013368.png)

### start
如果文本排版方向是 LTR，那么文本将左对齐，反之，如果文版排版方向是 RTL 时，文本右对齐。此时，`text-align`取值并不会对其产生任何影响。

![start 效果](http://cnd.qiniu.lin07ux.cn/markdown/1466950090680.png)

### end
如果文本排版方向是 LTR，那么文本右对齐；反之，如果文本排版方向是 RTL，文本左对齐。同样的，`text-align`取值不会对`text-align-last:end`有影响。

![end 效果](http://cnd.qiniu.lin07ux.cn/markdown/1466950185839.png)

### left
取值为`left`时，最后一行文本会靠容器左边缘对齐。此时，不会受到文本排版方向的影响。

### right
取值为`right`时，最后一行文本会靠容器右边缘对齐。此时，不会受到文本排版方向的影响。

### center
取值为`center`时，最后一行文本在容器中水平居中对齐。此时，不会受到文本排版方向的影响。

### justify
取值为`justify`时，文本效果和`text-justify`效果一样：浏览器会根据容器的宽度和内容的多少自动调整词与词之间的间距，让最后一行文本的第一个词靠容器左边缘对齐，文本的最后一个词靠容器的右边缘对齐。

![justify 效果](http://cnd.qiniu.lin07ux.cn/markdown/1466950396266.png)

### inherit
取值为`inherit`时，将会继承其父元素的`text-align-last`的取值效果。

## 效果
这里的**最后一行**是指：

* 一个块的最后一行
* `<br>`标签断行的最后一行

也就是说：其只能运用于块元素上，或者说块元素的断行内。对于行内元素来说是无效的。

例如：对于下面的三行文本，均设置了`text-align-last: center;`。

```html
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In metus mauris, blandit vitae auctor id, rhoncus id eros. Nullam sit amet nulla ac sapien eleifend ultrices. Curabitur ac dictum metus. Pellentesque ullamcorper dolor sit amet mi imperdiet egestas. Nam eu tellus sed nibh tincidunt rutrum eu sed augue. Cras vestibulum elementum tortor in gravida. Sed augue risus, tempor in justo non, fermentum faucibus nibh.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In metus mauris, blandit vitae auctor id, rhoncus id eros. Nullam sit amet nulla ac sapien eleifend ultrices. Curabitur ac dictum metus. Pellentesque ullamcorper dolor sit amet mi imperdiet egestas. <br />Nam eu tellus sed nibh tincidunt rutrum eu sed augue. Cras vestibulum elementum tortor in gravida. Sed augue risus, tempor in justo non, fermentum faucibus nibh.</p>
<span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In metus mauris, blandit vitae auctor id, rhoncus id eros. Nullam sit amet nulla ac sapien eleifend ultrices. Curabitur ac dictum metus. Pellentesque ullamcorper dolor sit amet mi imperdiet egestas. Nam eu tellus sed nibh tincidunt rutrum eu sed augue. Cras vestibulum elementum tortor in gravida. Sed augue risus, tempor in justo non, fermentum faucibus nibh.</span>
```

结果如下图：

![效果](http://cnd.qiniu.lin07ux.cn/markdown/1466949670442.png)

可以看到：

* 第一段文本使用的是`<p>`标签，其最后一行是居中对齐的；
* 第二段文本也使用的是`<p>`标签，但是其内还有一个`<br />`标签，所以会有两行文本是居中对齐的；
* 第三段文本使用的是`<span>`标签，这不是一个块级标签，所以虽然设置了`text-align-last: center;`，但是并不会起作用。

## 兼容性
![兼容性](http://cnd.qiniu.lin07ux.cn/markdown/1466950666711.png)

## 参考
[CSS Text3: text-align-last 大漠](http://www.w3cplus.com/css3/text-align-last.html)


