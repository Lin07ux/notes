在 CSS2.1 中`background-repeat`具有四个属性值`repeat`、`repeat-x`、`repat-y`和`no-repeat`。在 [CSS3](https://www.w3.org/TR/css3-background/#background-repeat) 中，新增了两个值：`round`、`space`。

### repeat
`background-repeat`取值为`repeat`时，表示背景图片沿容器的 X 轴和 Y 轴平铺。将会平铺满整个容器。如果背景图片的尺寸和容器尺寸不能完全匹配之时，会造成背景图片不全。如下图所示：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1475211450381.png" width="421"/>

### repeat-x
`repeat-x`可以说是`repeat`的分值之一，表示背景图片沿容器的 X 轴平铺。和`repeat`有点类似，有可能在容器最右侧不具备背景图片展示的空间，造成背景图片显示不全。

### repeat-y
`repeat-y`和`repeat-x`类似，不同的是，`repeat-y`让背景图片沿容器 Y 轴方向平铺背景图片。同样的道理，如果使用`repeat-y`有可能造成容器底部没有足够的空间展示整个背景图片。

### no-repeat
`no-repeat`刚好和`repeat`相反，表示背景图片不做任何平铺，也就是说背景图片有多大，在容器显示出来的效果就有多大。使用`no-repeat`时也会有背景图片显示不全的情况，那就是当容器的尺寸小于背景图片尺寸的时候。

### round
取值为`round`时，会像`repeat`一样，背景图片会平铺整个容器，但和`repeat`不一样的是，他会根据容器尺寸和图片尺寸做一个自适应处理，不会像`repeat`一样，造成图片显示不全。

当图片重铺的次数不适合一个整数时，会重新调整，使图片按整数平铺在整个容器中。类似于`background-size`一样会自动计算背景图片尺寸。假设我们有一个 520 x 320 的容器，背景图片尺寸是一个 100 x 100 的，那么他会在 x 轴平铺 5 次，在 y 轴平铺 3 次，其计算如下：

```
round(520 / 100) = round(5.2) = 5
round(320 / 100) = round(3.2) = 3
```

这样，通过设置整数的重复次数，和调整背景图片的尺寸，会使其铺满整个背景而不会有截断。

### space
取值为`space`时，和`round`又会不一样，但也有类似之处。

类似之处是，背景图片会平铺整个容器，不会造成背景图片裁剪；但也和`round`将不一样，使用`space`时，不够整数背景图片平铺整个容器的时候，会将剩余的空间平均分配给相邻背景之间。

假设我们的容器是 520 x 320，而背景图片的尺寸是 100 x 100。那么水平方向将会平铺 5 张背景图，而相邻两张背景图之间会有一个 20 / 5 = 4 间距。同样道理，在垂直方向也会适当的间距。甚至，如果在某个方向上只有一个允许平铺的次数不超过两次的时候，就仅仅会重复一次。来看一个[效果](http://codepen.io/airen/pen/ZpJLAg)：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1475213018528.png" width="1277"/>

### 不同的 x 和 y 轴的 repeat 值
从规范中，我们可以获得`<repeat-style>`可以有 x 和 y 的值，也就是说，在`background-repeat`取值是，可以将 x 和 y 的值任意组合，比如`round space`、`space round`、`round repeat-y`之类的。

[示例](http://codepen.io/airen/pen/bwAgjx)

### background-position
上面我们看到的效果都是`background-position`取值为`0 0`的效果，在实际当中，`background-position`取值不同的对`background-repeat`的效果也将会有一定的影响。所以在实际使用之中，在使用`background-repeat`时，也要考虑到`background`其它的属性情况。

[示例](http://codepen.io/airen/pen/zKdNmG)

### 转摘
[单聊background-repeat](http://www.w3cplus.com/css3/css3-background-repeat-space-round.html)


