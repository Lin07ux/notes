> 转摘：[如何在CSS中实现父选择器效果？ -- 张鑫旭](http://www.zhangxinxu.com/wordpress/2016/08/css-parent-selector/)

由于 HTML 和 CSS 的解析渲染顺序是从上往下进行，所以目前 CSS 还不支持后面的元素影响前面的元素的样式，或者子元素影响父元素的样式的功能。但是可以使用影响后面的兄弟元素的样式来模拟影响父元素的样式的效果。关键点就在于**把兄弟元素作为祖先元素使用**。

> 涉及的多个技术 tips 从 IE7 浏览器开始都是支持的，因此模拟父选择器效果兼容 IE7+ 浏览器。

示例：实现的效果如下图所示，当 input 处于 focus 状态的时候，外面的灰色框要变成高亮状态。

![HTML 结构](http://cnd.qiniu.lin07ux.cn/markdown/1470718599041.png)

![效果](http://cnd.qiniu.lin07ux.cn/markdown/1470718591282.png)

HTML 基本结构如下：

```html
<div class="container">
    <input id="input" class="input">
    <!-- 下面的label就是新建的负责容器外观的元素 -->
    <label class="border" for="input"></label>
</div>
```

对应的 CSS 代码如下：

```css
/* 父级容器只处理结构，不负责外观 */
.container {
    min-height: 120px;
    position: relative;
    z-index: 1;
}

/* 模拟父元素的效果 */
.border {
    /* 尺寸自适应容器大小，假装是容器 */
    position: absolute;
    left: 0; right: 0; top: 0; bottom: 0; 
    border: 1px solid #bbb; /* 外观模拟 */
    z-index: -1;            /* 在输入框的下面 */
}

/* 通过相邻兄弟选择器，控制容器的样式变化 */
.input:focus + .border {
    border-color: #1271E0;    
}
```

> 注意：父级容器设置了`z-index: 1;`是为了创建新的层叠上下文，这样子元素设置`z-index:-1`后也不会超出容器。参见：[深入理解CSS中的层叠上下文和层叠顺序](http://www.zhangxinxu.com/wordpress/2016/01/understand-css-stacking-context-order-z-index/)

> 绝对定位元素，如果没有具体的`width/height`限定，`left/right`以及`top/bottom`对应方位的数值会拉伸元素的尺寸。这里，全部都设为 0，和`.container`所在元素组合，就形成了类似宽高 100% 同时`box-sizing:border-box;`的效果。所以，如果想让 IE7 浏览器实现类似`border-box`的盒尺寸计算，某些情况下，就可以使用绝对定位拉伸！



