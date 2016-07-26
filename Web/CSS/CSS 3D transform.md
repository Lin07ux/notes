## 坐标系统
在常规的网页排版布局中，每一个 HTML 元素，比如`<div>`，都有一个初始坐标系统：坐标原点位于元素的左上角，X 轴水平向右(排版方向为从左向右时)，Y 轴垂直向下，Z 轴指向观察者（也就是屏幕外的我们）。初始坐标系的 Z 轴并不算是三维空间，而是像`z-index`那样作为参照，决定网页元素的绘制顺序，绘制顺序靠后的元素将覆盖绘制顺序靠前的。

但是，对元素进行 3D transform 变化的时候，所参照的坐标系统并不是元素的初始坐标系统，而是一个新的坐标系统：坐标原点位于元素的中心点，而 X、Y、Z 轴的方向不变。如果想要改变这个坐标系的原点位置，使用 CSS `transform-origin`属性。`transform-origin`的默认值是`50% 50%`，因此，默认情况下，transform 坐标系的原点位于元素中心。

![CSS transform 坐标系统](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466693091340.png)


## transform 变换
通过 CSS `transform` 属性，能给元素设置一个或多个变换。这些变换包括：

- `rotate` 旋转指定角度。包括：`rotate(x-deg, y-deg, z-deg)`、`rotateX(deg)`、`rotateY(deg)`、`rotateZ(deg)`。
- `translate` 移动一定距离。包括：`translate(x, y, z)`、`translateX(x)`、`translateY(y)`、`translateZ(z)`。
- `skew` 变形。


## 变换顺序
**每一个变换函数不仅改变了元素的显示，同时也会同步改变和元素关联的 transform 坐标系统**。

当变换函数依次执行时，后一个变换函数总是基于前一个变换后的新 transform 坐标系。所以使用多个变换函数的时候，需要注意到其使用顺序。

例如，下面一个包含两个变换函数的 transform 的效果（gif）：

![两个变换函数效果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/transform-1.gif)

如果交换这两个变换函数的顺序，是这样的效果：

![交换顺序](http://7xkt52.com1.z0.glb.clouddn.com/markdown/transform-2.gif)

可以看到，由于坐标系会随着每一次变换发生改变，因此不同顺序的情况下，元素最终的位置也不同。

对此还有一种解释，即变换函数是通过数学上的矩阵乘法运算完成的，而矩阵的乘法是不满足交换律的。任意坐标空间内的变换函数或者变换函数的组合，都可以转换为一个矩阵（还有一个[矩阵小工具](http://meyerweb.com/eric/tools/matrix/)可以帮你做这个转换）。

### 示例：正方体
现在来做一个正方体，现在先不用考虑`perspective`。正方体有六个面，然后需要用一个元素来装这六个面，所以 html 是：

```html
<div class="cube">
    <div class="surface surface-1">1</div>
    <div class="surface surface-2">2</div>
    <div class="surface surface-3">3</div>
    <div class="surface surface-4">4</div>
    <div class="surface surface-5">5</div>
    <div class="surface surface-6">6</div>
</div>
```

对应的 css 是（边长 120px，省略浏览器私有前缀，后文同）：

```css
.cube{
    position: absolute;
    transform-style: preserve-3d;
}
.cube .surface{
    position: absolute;
    width: 120px;
    height: 120px;
    border: 1px solid #ccc;
    background: rgba(255,255,255,0.8);
    box-shadow: inset 0 0 20px rgba(0,0,0,0.2);
    line-height: 120px;
    text-align: center;
    color: #333;
    font-size: 100px;
}
.cube .surface-1 {
    transform: translateZ(60px);
}
.cube .surface-2 {
    transform: rotateY(90deg) translateZ(60px);
} 
.cube .surface-3 {
    transform: rotateX(90deg) translateZ(60px);
}
.cube .surface-4 {
    transform: rotateY(180deg) translateZ(60px);
}
.cube .surface-5 {
    transform: rotateY(-90deg) translateZ(60px);
}
.cube .surface-6 {
    transform: rotateX(-90deg) translateZ(60px);
}
```

其中，`transform-style: preserve-3d;`保证所有子元素都处于同一个三维空间（这里是三维渲染上下文 3D rendering context）内，也就是告诉浏览器你是想用这些元素做一个三维场景，而不仅仅只是要单个元素的简单三维效果。

`position: absolute;`是一个习惯做法，因为三维物体并不符合一般平面网页内容的排版，所以我们会比较多地希望它不要占据布局空间。

6 个面位置都不一样，但却都有`translateZ(60px);`，你已经知道这是因为巧妙搭配了在它之前的变换函数。

一旦构成正方体的 6 个`div.surface`的位置确定后，就可以操作它们的父元素`div.cube`来整体移动、旋转这个正方体。

效果可以查看这里：[正方体](http://codepen.io/Lin07ux/pen/rLWEjB?editors=0100)


## 三维空间视觉效果
网页里的三维场景摄像机效果需要用的是 CSS 中的`perspective`和`perspective-origin`属性。

`perspective`定义摄像机（也就是作为观众的我们）到屏幕的距离，`perspective-origin`定义摄像机观察到的画面中的灭点（vanishing point，消失点）的位置。虽然它们并不能方便地让你直接定义摄像机的位置和观察角度等，但只要适当地应用它们，是可以一定程度上控制摄像机的画面效果的。

网页里的摄像机一般是这样用的：

```html
<div class="camera">
    <div class="cube1"></div>
    <div class="cube2"></div>
    <!-- more 3d objects... -->
</div>
```

```css
.camera{
    position: relative;
    perspective: 1200px;
    perspective-origin: 50% 50%;
    transform-style: preserve-3d;
}
```

在网页里，无论你搭建了怎样的三维场景，只要你希望它显示出来，就应该像这样把构成场景的三维物体都放在一个容器元素里，然后为容器元素添加摄像机属性（`perspective`和`perspective-origin`）。

此外，还需要注意添加`transform-style: preserve-3d;`以保证多个三维物体都位于同一空间（这样才有三维引擎的味道，对吧？）

例子：[这个场景](http://runjs.cn/detail/daqoq5tf)里有三个正方体，然后摄影师正在做弹跳练习（限支持3d transform的浏览器）：

`perspective-origin`的两个值有一点像指定 x 轴和 y 轴的感觉。想要向更深处前进，不能这样移动摄像机，你需要换一个思路，参照相对运动的关系，改为让整个三维场景向你移动。不过，说到这里，前面提到的摄像机的另一个属性，perspective，为什么它不行呢？

`perspective`代表摄像机距离屏幕的距离，看上去和 z 轴深度非常近似。但是，它并不等同于摄像机的 z 坐标位置（`perspective`还只能取正值），而是会影响摄像机本身的其他属性。下面用这个图说明`perspective`的值变化的效果（修改自 w3c 的配图）：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466696033006.png)

图中`d1`和`d2`分别表示两个不同的`perspective`的值，其中`d2`小于`d1`。然后，你会惊奇地发现，一个原本位于屏幕之后（`z`坐标为负值）的物体，竟然是随着“走近”而变得更小了！显然，这不符合我们在三维空间里运动的基本感受。其原因是，网页的三维投影平面是固定的，`perspective`在改变摄像机的位置的同时，也同时改变了摄像机本身的其他属性。

所以，一般来说，`perspective`应维持一个固定的值。想要用 3d transform 做出在三维空间里自由移动的效果（就像各种 3d 游戏），应该通过相对运动的方法实现。


## 补充
### 对布局的影响
transform 影响的是视觉渲染，而不是布局。因此，除以下情况外，transform 不会影响到布局：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466696154612.png)

这个因为`overflow`生成滚动条从而影响布局的反例，也发生于`position: relative;`再进行偏移的情况。

### left、top 等常规属性对 3d transform 的影响
相对于 transform 的`translate3d()`这类改变空间位置的变换函数，原来 css 里就有的定位属性`left`、`top`似乎会让情况变得很复杂。

对此，有一个比较推荐的分析方式：就三维空间的位置而言，常规属性`left`、`top`，甚至`margin-left`等，是先生效的，它们的效果其实只有一个，就是改变元素的初始位置，从而改变元素的`transform-origin`的那个原点位置，然后三维空间的 transform 是后生效的，它会再基于前面的`transform-origin`继续改变位置。

### perspective-origin 和 transform-origin 的区别
`perspective-origin`是一个摄像机的属性，定义的是透视画面的灭点，而`transform-origin`是任意元素都有的，定义的是的元素的 transform 坐标系的原点。


## 参考
1. [3d transform 坐标系统](https://segmentfault.com/a/1190000004233074)

