> 转摘：[寻根问底之——元素隐藏你知多少？](https://segmentfault.com/a/1190000020302219)

让一个元素隐藏起来有很多方式，但常规的有三种：`display: none`、`opacity: 0`和`visibility: hidden`，这三种方式在各个层面上各有特点。

### 1. 动画

设置元素显隐时，只有`opacity`属性可以支持动画效果，其他两者都只能要么显示要么隐藏。

`opacity`配合`animation`或者`transition`可以实现淡入淡出效果。

### 2. 子元素

* `display`不会被子元素继承，但是设置了`display: none`的元素其子元素都将会被隐藏。
* `opacity`不会被子元素继承，而一旦设置了`opacity`，其子元素的透明度将会按照这个值被打折，比如父元素设置了`opacity: 0.5`，子元素即便设置了`opacity: 1`其透明度也将变成`0.5 * 1 = 0.5`。所以只要父元素是全透明`opacity: 0`，那么子元素也将会全透明。
* `visibility`可以被子元素继承，这就导致默认情况下，设置了`visibility: hidden`的元素的子元素都会被隐藏。但如果为特定的子元素设置了`visibility: visible`，那么该子元素将是可见的。

### 3. 层叠上下文(Stacking Context)

HTML 中的元素都有自身的层叠水平，但是在某些情况下，元素会形成层叠上下文，从而”拔高“自身以及子元素的层叠水平。而元素间不同的层叠水平会决定在它们发生重叠的时候，谁将在 Z 轴上更高一筹，也就是谁将离用户的眼镜更近(从而更难被遮挡)。

> 形成层叠上下文的情况可以参考 [MDN 文档](https://developer.mozilla.org/zh-CN/docs/Web/Guide/CSS/Understanding_z_index/The_stacking_context)。

`display`和`visibility`的值不会形成层叠上下文，而当元素的`opacity`的值小于 1 时，就会形成层叠上下文。

比如：

```html
<div style="position: relative;">
    <div style="position: absolute;background: green; top: 0;width: 200px;height: 200px">
    </div>
    <div style="background: red;width: 100px;height: 100px"></div>
</div>
```

这种情况下，两个子元素中，设置了绝对定位的绿色方块由于形成了层叠上下文，所以其会在红色的方块上方，从而导致红色方块是无法被看到的。

如果调整红色方块的`opacity`属性的值，如下：

```html
<div style="position: relative;">
    <div style="position: absolute;background: green;top: 0;width: 200px;height: 200px">
    </div>
    <div style="opacity: 0.5;background: red;width: 100px;height: 100px">
    </div>
</div>
```

此时，由于红色方块的`opacity`值小于 1，也形成了层地上下文，与绿色方块属于相同层叠水平了。而由于红色方块在 HTML 中处于绿色方块后方，按照后者居上的原则，红色方块就会叠在上方了，从而能够被看到了。

当设置`opacity: 0`的时候，虽然元素无法被看到，但是由于其形成层叠上下文的原因，在某些情况下，还是会有无法忽略的影响的。

### 4. 可交互性/可访问性

当元素设置了`display: none`之后，其整体都仿佛不在 DOM 树中了一样，自然无法响应任何绑定在其上的事件(以及其子元素的事件)。

设置了`visibility: hidden`之后，元素将会忽略`event.target`为其自身的事件。也就是说，该元素会接受到子元素的事件冒泡并执行相应的事件处理器，但是无法在其自身上直接触发事件并执行相应的事件处理器。而且也无法通过 Tab 键访问到(也就是无法 focus)。此外，它还会失去 Accessibility，也就是不能进行无障碍访问，比如屏幕阅读软件将无法访问到这个元素。

设置了`opacity: 0`的元素，虽然无法被看到，但是其还是真实存在于 DOM 树中的，它没有上面的限制，可以响应事件，也可以被选中。结合前面提到的层叠上下文可知，设置了`opacity: 0`的元素虽然看不到了，但是还是可能会因为层叠水平更高而且可以被点击，有时会造成一些意料之外的问题。

### 5. 回流与重绘

回流与重绘是 DOM 元素增改删以及一些 CSS 属性改变时，浏览器自动进行的响应，而回流总会引起重绘，重绘并不一定会引起回流。当重绘的频率增多到一定程度时，会导致 CPU 或 GPU 使用率飙高，从而引起卡顿。所以一般应尽量减少这两者的产生。

* `display: none`切换显隐时会导致 reflow(回流)，从而引起 repaint(重绘)。
* `opacity`和`visibility`切换显隐时，只会引起重绘。但是`opacity`会导致该元素及其子元素都被重绘。

### 6. 结合使用

如果既想要实现动画效果，又要避免元素不可见之后响应 DOM 事件，可以将它们结合使用起来。

比如：

```css
.box {
  animation: fade 0.5s linear 0s forwards;
}
@keyframes fade {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
    visibility: hidden;
  }
}
```

