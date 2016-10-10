半透明颜色的一种应用是：使用它们作为背景，在照片或者颜色比较复杂的背景下，通过降低对比度，提高文本的可读性。

这样虽然能够有一定的效果，但是阅读起来仍然会不是那么好，特别是非常低透明度的颜色或非常杂乱的背景的情况下。比如下图的效果：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476076910428.png" width="341"/>

上图的主体 HTML 框架如下：

```html
<main>
    <blockquote>
        “The only way to get rid of a temptation is to yield to it. Resist it, and your soul grows sick with longing for the things it has forbidden to itself, with desire for what its monstrous laws have made monstrous and unlawful.”
        <footer>—
            <cite>
                Oscar Wilde, The Picture of Dorian Gray
            </cite>
        </footer>
    </blockquote>
</main>
```

对应的，CSS 代码如下：

```css
body {
    min-height: 100vh;
    box-sizing: border-box;
    margin: 0;
    padding-top: calc(50vh - 6em);
    font: 150%/1.6 Baskerville, Palatino, serif;
    background: url("http://csssecrets.io/images/tiger.jpg") 0 / cover fixed;
}

main {
    position: relative;
    margin: 0 auto;
    padding: 1em;
    max-width: 23em;
    background: hsla(0,0%,100%,.25) border-box;
    overflow: hidden;
    border-radius: .3em;
    text-shadow: 0 1px 1px hsla(0,0%,100%,.3);
}

blockquote { font-style: italic }
blockquote cite { font-style: normal; }
```

可以看到，文本内容确实很难阅读，因为文本背后的图像非常杂乱，而且背景颜色也只是 25% 的不透明值。当然，我们可以通过增加背景颜色的 alpha 参数的值来提高可读性，但是效果可能就不是那么有趣了。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476077200883.png" width="338"/>

这个问题通常可以通过模糊容纳文本内容的那块区域的背景来解决的。模糊后的背景就不会那么杂乱了，所以，上边的文本可读性也提高了。

在 CSS 中我们可以通过`blur()`滤镜做出模糊元素的效果，这本质上是 SVG 模糊滤镜原语的一个对应的硬件加速版本。但是，如果我们直接给我们示例中的内容元素应用一个`blur()`滤镜，整个元素都会被模糊，这样可读性就更差了。

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476077316928.png" width="336"/>

所以我们应该将模糊只应用在元素的 backdrop 上（也就是元素后面的背景的一部分）。

最佳选择就是伪元素：将伪元素进行绝对定位，而且`z-index`设置为 -1，就能使其处于元素内容下方，不会遮盖内容。再将伪元素进行模糊即可。

> 需要注意的是：如果伪元素不设置任何背景色或者背景图片，那么模糊效果将不会显现出来。

对应更改的 CSS 代码如下：

```css
body, main::before {
    background: url("tiger.jpg") 0 / cover fixed;
}

main {
    position: relative;
    /* [Rest of styling] */
}

main::before {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    z-index: -1;
}
```

此时可以得到如下的效果：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476077717316.png" width="338"/>

基本达到我们的需求了。但是仔细观察可以看到模糊效果在边界特别是边角处不明显。这是因为模糊半径（blur radius）会减少覆盖有纯色模糊的面积。

为了规避这个问题，我们让伪元素至少比它的容器的尺寸大 20px（和模糊半径的值相等），通过应用一个 -20px 或更小的 margin 值来让它保持在一个安全的区域内，因为不同的浏览器可能会使用不同的模糊算法。

```css
main::before {
    margin: -25px;
}
```

得到的效果类似如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476077888305.png" width="339"/>

这虽然修复了边缘处褪色模糊的问题，但是现在在容器外边也有一些模糊，这使得它看起来像污迹而不像磨砂。这个问题也容易解决：我们只要应用为 main 应用`overflow: hidden;`，把多余的模糊剪掉即可。效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476077944109.png" width="337"/>

[demo](http://codepen.io/airen/pen/qbRPpb) 或 [demo](http://codepen.io/Lin07ux/pen/qaoYOg)

作为降级，建议将容器元素添加一个半透明的背景色，这样即便不支持滤镜，也能够尽量的保持内容的可识别性。

转摘：[CSS秘密花园:磨砂玻璃效果](http://www.w3cplus.com/css3/css-secrets/frosted-glass-effect.html)


