## Flex 布局是什么
Flex 是 Flexible Box 的缩写，意为"弹性布局"，用来为盒状模型提供最大的灵活性。

Flex 是为了解决传统 CSS 布局中难以解决的问题，比如让子元素自适应的扩展或者收缩宽度/高度，可以简便、完整、响应式地实现各种页面布局等。

Flex 布局可以方便的实现以下的功能：

- 如果元素容器没有足够的空间，我们无需计算每个元素的宽度，就可以设置他们在同一行；
- 可以快速让他们布局在一列；
- 可以方便让他们对齐容器的左、右、中间等；
- 无需修改结构就可以改变他们的显示顺序；
- 如果元素容器设置百分比和视窗大小改变，不用提心未指定元素的确切宽度而破坏布局，因为容器中的每个子元素都可以自动分配容器的宽度或高度的比例。

注意，设为 Flex 布局以后，其子元素的`float`、`clear`和`vertical-align`属性将失效。

## Flex 属性设置
Flex 布局中，分为容器和容器成员(flex item)两个级别。这两个级别分别对应不同的设置，从而展示不同的结果。

其中，Flex 容器一般是用来设置 flex 属性，使其成为 flex 盒子，还可以设置其他属性来控制其内子元素的布局方式、宽高对齐方式等；在容器成员级别中，可以设置子元素的宽高伸缩度等。下面是 Flex 的属性图：

![Flex 属性图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466860693911.png)


容器默认存在两根轴：水平的主轴(`main axis`)和垂直的交叉轴(`cross axis`)。主轴的开始位置（与边框的交叉点）叫做`main start`，结束位置叫做`main end`；交叉轴的开始位置叫做`cross start`，结束位置叫做`cross end`。

项目默认沿主轴排列。单个项目占据的主轴空间叫做`main size`，占据的交叉轴空间叫做`cross size`。

![Flex 轴线](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466784316721.png)


### Flex 容器属性
flex 容器可以从`flex-direction`、`flex-wrap`、`flex-flow`、`justify-content`、`align-items`、`align-content`这 6 个方面进行设置


#### 定义 Flex 盒子
任何一个容器都可以指定为 Flex 布局：

```css
.box {
    display: flex;
}
```

行内元素也可以使用 Flex 布局：

```css
.box {
    display: inline-flex;
}
```

由于 IE10、IE11 目前使用中出现了大量的 bug，Android 4.3 只支持老的 flexbox 规范，所以在使用的时候，一般需要添加上一些老的写法：

```css
.box{
    display: box;  /* 最老版本 */
    display: flexbox; /* 过渡版本 */
    display: -webkit-flex; /* webkit */
    display: flex;
}
```

或者：

```css
.box{
    display: inline-box;  /* 最老版本 */
    display: inline-flexbox; /* 过渡版本 */
    display: -webkit-inline-flex; /* webkit */
    display: inline-flex;
}
```

#### flex-direction 属性
该属性设置 flex 的主轴方向。可选值如下：

* column-reverse  主轴在垂直方向，起点在下沿。
* column  主轴在垂直方向，起点在上沿。
* row  默认值。主轴在水平方向，起点在左端。
* row-reverse  主轴在水平方向，起点在右端。

效果分别如下所示：

![fle-direction](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466837517430.png)


#### flex-wrap 属性
默认情况下，容器项目都排在一条线（又称"轴线"）上。

`flex-wrap`属性定义：如果一条轴线排不下，如何换行。可选值有：

* nowrap  默认值。不换行。所有的容器成员都排在一条线上。

![flex-wrap nowrap](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466841315540.png)


* wrap  换行。第一行在上方，其他行依次往下排列。

![flex-wrap wrap](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466841431629.png)


* wrap-reverse  换行。第一行在下方，其他行依次往上排列。

![flex-wrap](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466841487572.png)


#### flex-flow 属性
这个属性是`flex-direction`和`flex-wrap`两个属性的简写方式。默认值是`row nowrap`。

#### justify-content 属性
定义了容器成员在主轴上的对齐方式。可选值有：

* flex-start  默认值。主轴起始端对齐。
* flex-end  主轴结束端对齐。
* center  沿主轴居中对齐。
* space-between  两端对齐。容器成员之间的空格间隙相同。
* space-around  两端对齐。每个项目两侧的间隔相等。所以，项目之间的间隔比项目与边框的间隔大一倍。

![justify-content](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466842047964.png)

#### align-items 属性
定义容器成员在交叉轴上如何对齐。可选值有：

* flex-start  交叉轴的起始点对齐。
* flex-end  交叉轴的结束端对齐。
* center  沿交叉轴居中对齐。
* baseline  容器成员的第一行文字的基线对齐。
* stretch  默认值。如果项目未设置高度或高度设为`auto`，将占满整个容器的高度。

![align-items](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466842633692.png)

#### align-content 属性
该属性定义了多根轴线的对齐方式。如果项目只有一根轴线，该属性不起作用。

> Flex 容器中，每一沿主轴方向排列的行或列都属于一根轴线。所以如果换行(列)了，那就会有多根轴线。

可选值有：

* flex-start  与交叉轴起始端对齐。
* flex-end  与交叉轴终端对齐。
* center  沿交叉轴居中对齐。
* space-between  与交叉轴两端对齐，轴线之间的间隔平均分布。
* space-around  每根轴线两侧的间隔都相等。所以，轴线之间的间隔比轴线与边框的间隔大一倍。
* stretch  默认值。轴线扩展高度/宽度，占满整个交叉轴

![align-content](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466844908229.png)


### Flex 成员属性
容器盒子的成员也有 6 个属性来设置成员的一些布局状态等：`order`、`flex-grow`、`flex-shrink`、`flex-basis`、`flex`、`align-self`。

> 容器盒子的成员只是其内的一级子元素，而不包含子元素的子元素等。

#### order 属性
定义项目的排列顺序。其值为数值。数值越小，排列越靠前，默认为0。也可以取负值。

```css
.item {
    order: <integer>;
}
```

![order](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466845429220.png)

#### flex-grow 属性
定义容器成员的放大比例，默认为 0，即：如果存在剩余空间，也不放大。

```css
.item {
    flex-grow: <number>; /* default 0 */
}
```

如果所有项目的`flex-grow`属性都为 1，则它们将等分剩余空间（如果有的话）。如果一个项目的`flex-grow`属性为 2，其他项目都为 1，则前者占据的剩余空间将比其他项多一倍。

![flex-grow](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466845515760.png)

注意：这个属性是指定容器成员对**剩余空间**的分配比例情况。也就是说，将容器主轴线方向上的长度，减去轴线上所有成员总长度，得到的结果如果是正数，则这部分空间会被按照成员这个属性设置的比例分配给各个容器成员。

比如说：容器盒子的宽度是 480px，`flex-direction: row;`的情况下，其内有三个`flex-basis`为 100px 的容器成员。我们从左到右给予每个容器成员的`flex-grow`值分别为 3、2、1，那么当 flex 作用之后，最左边的容器成员实际增加的宽度可以算到增加的宽度是 90px，于是最后最左边成员的宽度是 190px。

![flex-grow 计算](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466852134445.png)

#### flex-shrink 属性
定义了项目的缩小比例，默认为 1，即：如果空间不足，该项目将缩小。

```css
.item {
    flex-shrink: <number>; /* default 1 */
}
```

如果所有项目的`flex-shrink`属性都为 1，当空间不足时，都将等比例缩小。如果一个项目的`flex-shrink`属性为 0，其他项目都为 1，则空间不足时，前者不缩小。

负值对该属性无效。

![flex-shrink](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466845926932.png)

默认状态下，伸缩项目不会收缩至比其最小内容尺寸（最长的英文词或是固定尺寸元素的长度）更小。可以靠设置`min-width`或`min-height`属性来改变这个默认状态。

注意：`flex-shrink`和`flex-grow`的计算方式并不相同。因为当在加的时候无所谓，但是在减的时候，如果只计算赋予的`flex-shrink`值，那么很有可能最后减少的宽度比`flex-basis`大，于是容器成员的宽度就变成负值。而元素最小的宽度就只能是 0。那么就要把`flex-basis`当成参数计算进去，这样就能保证减少的宽度永远小于`flex-basis`。

例如：容器宽度为 480px，主轴方向为水平方向。三个容器成员的`flex-basis`都是 200px，`flex-shrink`分别为 3、2、1。那么就需要这样计算：首先，这三个容器成员总共需要收缩出来`300px * 2 - 480px = 120px`的宽度；然后，这三个成员总的收缩空间为`200px * 3 + 200px  * 2 + 200px * 1 = 1200px`；对应的，每一份收缩比例中，对应的收缩空间为`120px * (200px / 1200px) = 20px`；所以，三个容器成员的最终的收缩空间为：`20px * 3 = 60px`、`20px * 2 = 40px`、`20px * 1 = 20px`，对应的最终宽度就分别是`200px - 60px = 140px`、`200px - 40px = 160px`、`200px - 20px = 180px`。

![flex-shrink 计算](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466857530766.png)


#### flex-basis 属性
定义了在分配多余空间之前，项目占据的主轴空间(`main size`)。浏览器根据这个属性，计算主轴是否有多余空间。它的默认值为`auto`，即项目的本来大小。

```css
.item {
    flex-basis: <length> | auto; /* default auto */
}
```

它可以设为跟`width`或`height`属性一样的值(如具体 px 值、百分比等)，则项目将占据固定空间。

另外，容器成员设置`flex-basis`为具体值的时候，如果还设置`width`(主轴水平)或者`height`(主轴垂直)时，会以`flex-basis`的值为准。

#### flex 属性
`flex`属性是`flex-grow`、`flex-shrink`和`flex-basis`的简写，默认值为`0 1 auto`。后两个属性可选。

```css
.item {
    flex: none | [ <'flex-grow'> <'flex-shrink'>? || <'flex-basis'> ]
}
```

该属性有两个快捷值：`auto`(`1 1 auto`) 和 `none`(`0 0 auto`)。

建议优先使用这个属性，而不是单独写三个分离的属性，因为浏览器会推算相关值。

#### align-self 属性
`align-self`属性允许单个项目有与其他项目不一样的对齐方式，可覆盖`align-items`属性。

默认值为`auto`，表示继承父元素的`align-items`属性，如果没有父元素，则等同于`stretch`。

该属性可能取 6 个值，除了`auto`，其他都与`align-items`属性完全一致。

```css
.item {
    align-self: auto | flex-start | flex-end | center | baseline | stretch;
}
```

![align-self](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466846264126.png)


## 参考
1. [Flex 布局教程：语法篇 -- 阮一峰](http://www.ruanyifeng.com/blog/2015/07/flex-grammar.html)


