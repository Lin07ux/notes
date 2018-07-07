转摘：[CSS秘密花园：通过模糊来De-emphasize(去强调)](http://www.w3cplus.com/css3/css-secrets/de-emphasize-by-blurring.html)

使用一个半透明的黑色覆盖层来让 Web 页面上的一些部分 de-emphasize。但是，当页面上有很多东西的时候，我们需要调暗很多，才可以为其上的文本提供足够的对比度，或是把用户的注意力转移到突出显示的盒子上或其它元素上边。

还有一种更优雅的方式，如下图所示，把其它的所有东西都模糊。这看起来更真实，因为它模仿了我们看对象的视觉创建了一个深度，也就是当我们专心看物理上离我们近的东西时的视线：
![通过模糊来 de-emphasize](http://7xkt52.com1.z0.glb.clouddn.com/2IBRr2a.png%21web)

这个效果需要滤镜的支持，比如`blur()`滤镜。但是要怎么应用模糊滤镜呢，如果我们想要把它应用到除了某个元素之外的所有东西上？如果我们把它应用给 <body> 元素，页面上的所有东西都会被模糊，包括我们希望吸引用户注意力的元素。

显然，我们不能使用伪元素，因为所有东西都在我们的对话框后边，而不仅仅是一个背景图片。那就说明，我们肯定是需要一个元素，而且需要这个元素包含着所有元素，除了那个我们不想要让它变模糊的元素。

`<main>`元素非常适合，因为：它可以包裹页面的主要内容(对话框通常不是主要内容)。其代码如下所有。当然，如果你有其他的元素或代码组织方式，也是可以的。

```HTML
<main>Bacon Ipsum dolor sit amet…</main>
<dialog>
    O HAI, I’m a dialog. Click on me to dismiss.
</dialog>
<!-- any other dialogs go here too -->
```

相应的是 CSS 代码如下：

```css
main.de-emphasized {
    filter: blur(5px);
}
```

我们假设所有的`<dialog>`元素都是初始情况下隐藏的，任何时候都是最多只有一个可见。然后每次 dialog 元素出现的时候，给 main 元素加上滤镜模糊即可。

这样已经能实现所需要的效果了，但是现在模糊应用得非常快，看起来有些不自然。因为滤镜效果支持动画，那我们可以给滤镜效果添加一个动画效果，让其在一定时间内慢慢过渡到最终模糊效果：

```css
main {
    transition: .6s filter;
}
main.de-emphasized {
    filter: blur(5px);
}
```

把调光和模糊这两种 de-emphasizing 效果结合起来使用是一个不错的主意。一种方法是使用`brightness()`和/或`contrast()`滤镜：

```css
main.de-emphasized {
    filter: blur(3px) contrast(.8) brightness(.8);
}
```

通过 CSS 滤镜调光意味着，如果它们不被支持的话，没有降级。所以通过其它方法来实现调光效果可能会更好，也可以把它作为一个降级（如使用 box-shadow 方法）。这还可以帮我们解决光圈效应的问题(在元素边缘会有一些白边)。




