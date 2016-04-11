转摘：[CSS秘密花园： 交互式图像对比](http://www.w3cplus.com/css3/css-secrets/interactive-image-comparison.html)

有时候我们需要向别人展示两幅图像的视觉差异，通常一幅是修改前的图像、一幅是修改后的。一般我们可以把两幅图像并排放置在一起，展示照片处理的效果。但是，这样的话人的眼睛就只能注意到非常突出的差异、而察觉不到那些小的变化。

一个更实用的解决方案是“image comparison slider”。把两个图像叠加在一起，让用户拖动 divisor 进行选择，是显示这一个还是另一个。要实现这种效果，有两种解决方案：

### CSS resize 方案
image comparison slider 通常包括一张图像，和一个水平方向大小可调的元素，用于显示另一张图像。在 CSS3 中，`resize`属性即可完成这个功能。

> `resize`一般用在 textarea 元素中，但是其实它可以用在任意元素上，只要这个元素的`overflow`属性值不是`visible`。
> 几乎所有元素的`resize`属性的默认值都是 none，这使得它们不能调整大小。除了 both，它还接受 horizontal 和 vertical 值，用来限制哪个方向可以调整大小。

我们的第一个想法可能是使用两个`<img>`元素。但是，直接给`<img>`应用`resize`看起来非常丑，直接调整图像大小的话会导致扭曲。所以，还是把它应用到一个`<div>`容器中可能比较合理。因此，我们的 HTML 标签如下：

```html
<div class="image-slider">
    <div>
        <img src="adamcatlace-before.jpg" alt="Before" />
    </div>
    <img src="adamcatlace-after.jpg" alt="After" />
</div>
```

应用一些基础的 CSS 用于定位和设置尺寸：

```css
.image-slider {
    position:relative;
    display: inline-block;
}
.image-slider > div {
    position: absolute;
    top: 0; bottom: 0; left: 0;
    width: 50%; /* Initial width */
    overflow: hidden; /* Make it clip the image */
}
.image-slider img { display: block; }
```

给上层图片的父元素添加一个`resize`属性，就能够改变上层图片显示的宽度了：

```css
.image-slider > div {
    position: absolute;
    top: 0; bottom: 0; left: 0;
    width: 50%;
    overflow: hidden;
    resize: horizontal;
}
```
唯一的视觉变化就是，现在大小调整句柄出现在了 top 图像的右下角。我们现在可以拖动它，把它调整到我们的主要内容！
但是，拖动我们的小工具时，也发现了一些缺陷：
    * 我们可以调整`<div>`的大小到超过图像的宽度
    * 无法调整`<div>`的大小小到我们设置的图像的宽度
    * `resize`句柄很难点中

第一个问题很容易解决。我们只需要指定`max-width`的值为 100%。

第二个问题就是一个权衡的问题了，如果我们初始设置的 width 设置的太小又不太好，用户难以辨识，设置的太大最后用户又无拖动减小到最小。

第三个问题，现在还没有标准的方法来给`resize`句柄添加样式。一些渲染引擎支持私有伪元素（如`::-webkit-resizer`），但是它们的结果非常受限制，不仅是在浏览器支持方面，还有样式灵活性方面。但是，希望仍在：在`resize`句柄上覆盖一个伪元素不会干扰它的功能，甚至不需要设`pointer-events: none`。所以，给`resize`句柄添加样式的跨浏览器的解决方案就是在它的上边覆盖一个伪元素。如下：

```css
.image-slider > div::before {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 12px; height: 12px;
    background: white;
    cursor: ew-resize;
}
```

注意`cursor: ew-resize`这条声明：它增加了一个额外的支持，因为它提示用户说这块区域可以用作一个`resize`句柄。但是，我们不能依赖光标改变作为我们唯一的支持，因为它们是当用户和控件交互时才是可视的。
现在，我们的`resize`句柄变成了一个白色方块。在这里，我们可以接着往下，把它变成我们喜欢的样式。例如，把它变成一个距图像边距为 5px 的白色三角形。

```css
padding: 5px;
background:linear-gradient(-45deg, white 50%, transparent 0);
background-clip: content-box;
```

为了进一步改进，我们可以给两个图像都应用`user-select: none`。这样，没有抓取到`resize`句柄的话也不会白白导致它们被`selected`。

总结一下，完整的代码如下：

```css
.image-slider {
    position:relative;
    display: inline-block;
}

.image-slider > div {
    position: absolute;
    top: 0; bottom: 0; left: 0;
    width: 50%;
    max-width: 100%;
    overflow: hidden;
    resize: horizontal;
}

.image-slider > div::before {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 12px; height: 12px;
    padding: 5px;
    background:
    linear-gradient(-45deg, white 50%, transparent 0);
    background-clip: content-box;
    cursor: ew-resize;
}

.image-slider img {
    display: block;
    user-select: none;
}
```

### Range input 解决方案
在上面提到的 CSS resize 方法运行是非常 ok 的，而且代码量也非常小。但是，它有一些缺点：
    - 它不是 keyboard accessible（不能通过键盘调整 resize ）的
    - 拖动是调整上边的图像的唯一方法，这对于非常大的图像和有运动障碍的用户而言是很麻烦的。如果能够在用户点击一个点的时候，图像就调整到那个点，可以提供一个更好的用户体验。
    - 用户只能从右下角对顶部图像进行调整，这个右下角可能很那被注意到，即使我们把它变成我们之前描述的样式。

如果我们愿意使用一点脚本，我们就可以使用一个滑块控件（HTML range input）覆盖在图像的顶部，来控制调整大小，就可以把这三个问题都解决了。因为我们无论如何都是要使用 JS 的，我们可以通过脚本添加额外的元素，我们从如下简单的 HTML 标签开始：

```html
<div class="image-slider">
    <img src="adamcatlace-before.jpg" alt="Before" />
    <img src="adamcatlace-after.jpg" alt="After" />
</div>
```

这样，我们的 JS 代码会把它转变如下，然后给滑块添加一个事件，并设置 div 的宽度：

```html
<div class="image-slider">
    <div>
        <img src="adamcatlace-before.jpg" alt="Before" />
    </div>
    <img src="adamcatlace-after.jpg" alt="After" />
    <input type="range" />
</div>
```

JavaScript 代码相当简单：

```javascript
var sliders = document.querySelectorAll('.image-slider');
[].forEach.call(sliders, function(slider) {
    // Create the extra div and
    // wrap it around the first image
    var div = document.createElement('div');
    var img = slider.querySelector('img');
    slider.insertBefore(div, img);
    div.appendChild(img);

    // Create the slider
    var range = document.createElement('input');
    range.type = 'range';
    range.oninput = function() {
        div.style.width = this.value + '%';
    };
    slider.appendChild(range);
});
```

CSS 代码和上面的基本一样，只是去除了一些样式：

```css
* { padding: 0; margin: 0; }
.image-slider {
    position:relative;
    display: inline-block;
}
.image-slider > div {
    position: absolute;
    top: 0; left: 0;
    width: 50%; /* Initial width */
    overflow: hidden; /* Make it clip the image */
}
.image-slider img { display: block; width: 100vw; }
```

现在，测试这些代码，会发现它是可运行的，但是它看起来有点糟糕：我们的图片下方随意地放置了一个 range input 的控件。我们需要给它应用一些 CSS，让它定位在图片上边，并设置宽度和容器宽度一致：

```css
.image-slider input {
    position: absolute;
    left: 0;
    bottom: 10px;
    width: 100%;
    margin: 0;
}
```

> 还有几个私有伪元素可以给 range input 添加样式，让它变成我们期望的样子。包括：`::-moz-range-track`, `::-ms-track`, `::-webkit-slider-thumb`, `::-moz-range-thumb`, 和`::-ms-thumb`。像大多数私用特性，它们的结果往往是不一致的、脆弱的、并且无法预测的，所以我建议不要使用它们，除非你真的需要。

如果我们只是想要在视觉上将 range input 和控件统一，我们可以使用一个混合模式或滤镜。混合模式`multiply`、`screen`、`luminosity`应该可以生成不错的效果。还有，`filter: contrast(4)`可以让滑块变成黑白，小于 1 的对比值可以让它更偏灰色。可能性是无限的，没有什么通用的最佳选择。你甚至可以混合模式和滤镜一起用，如下：

```css
filter: contrast(.5);
mix-blend-mode: luminosity;
```

我们还可以扩展那块用户使用来调整大小的区域，使之成为更棒的用户体验（根据费茨法则）。可以减小宽度，并结合 CSS transform 来完成：

```css
width: 50%;
transform: scale(2);
transform-origin: left bottom;
```

这个方案的另一个好处是——尽管只是暂时性的——目前 range input 有比 resize 属性更好的浏览器支持。


