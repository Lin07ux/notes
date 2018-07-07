### 活版文本
这个效果最好用于暗色文本搭配中等亮度背景的情况中，但是它也可以用于较暗背景下有较亮文本的情况下，只要文本不是黑色的以及背景也不是全白或全黑的。

它基于相同的前提：在文字底部有一个较亮的阴影（或在文字顶部有较暗的阴影）创建了一个对象被“刻在”表面上的假象；类似的，在文字底部有一个较暗的阴影（或在文字顶部有较亮的阴影），创建了一个对象被从表面挤出的错觉。

它的工作原理是：**我们通常假设光源是在我们之上的，所以表面凸出的对象会在它下面创建一个阴影，一个凹进去的对象在底部会很亮**。当我们在较亮的背景上有较暗的文本时，在文字底部有一个较亮的阴影比较合适；当我们在较暗的背景上有较亮的文本时，在文字的顶部设置一个较暗的阴影会比较合适。

比如，对于下面的 css 样式：

```css
background: hsl(210, 13%, 60%);
color: hsl(210, 13%, 30%);
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476149142998.png" width="252"/>

背景较亮，文字较暗，我们可以给文字底部设置一个较亮的阴影：

```css
text-shadow: 0 1px 1px hsla(0,0%,100%,.8);
```

对应的效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476149164926.png" width="254"/>

> 这里阴影的亮度取决于想要的效果以及确切的颜色，所以你需要用 alpha 参数做一些试验，直到找到合适的亮度值。
> 
> 这里使用像素作为单位而不是 em，但是如果你的文本尺寸是不确定的，可能非常小也可能非常大，用 em 可能更好：`text-shadow: 0 .03em .03em hsla(0,0%,100%,.8);`

类似的，在一个较暗的背景上有一个较亮的文本的情况下，我们在文字的顶部创建一个较暗的阴影：

```css
background: hsl(210, 13%, 40%);
color: hsl(210, 13%, 75%);
text-shadow: 0 -1px 1px black;
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476149431553.png" width="133"/>

### 描边文本
实现描边效果，最普遍的方法是给图层应用多个不同偏移量的`text-shadow`值，使其包裹在文字四周：

```css
background: deeppink;
color: white;
text-shadow: 1px 1px black, -1px -1px black, 1px -1px black, -1px 1px black;
```

实现的效果如下图：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476149504998.png" width="131"/>

如果描边的效果需要很粗，那么使用多个不同偏移量的 text-shadow 可能就会有不好的效果了，而且代价较高。


### 发光文本
发光文本在悬停链接、或某些类型网站的标题上都是一种颇为常见的效果。这也是最容易创建的效果之一。最简单的形式是你只需要使用一组分层的`text-shadow`，而不需要任何偏移量，颜色也和文本一样，只需要设置不同的模糊范围即可：

```css
background: #203;
color: #ffc;
text-shadow: 0 0 .1em, 0 0 .3em;
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476149795197.png" width="132"/>

自然，也可以对这个发光效果做动画效果，需要设置 transition。


### 立体文字
其主要思想是有大量堆积阴影，没有模糊，只有 1px 的区别，且阴影颜色逐渐变暗，在最后带有一个高度模糊的深色阴影，模拟阴影。

```css
background: #58a;
color: white;
text-shadow: 0 1px hsl(0,0%,85%),
             0 2px hsl(0,0%,80%),
             0 3px hsl(0,0%,75%),
             0 4px hsl(0,0%,70%),
             0 5px hsl(0,0%,65%),
             0 5px 10px black;
```

效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476150126718.png" width="243"/>

这个要转换成mixin更容易，或者说用一个函数来实现更恰当：

```scss
@function text-retro($color: black, $depth: 8) {
    $shadows: (1px 1px $color,);
    @for $i from 2 through $depth {
        $shadows: append($shadows,
        ($i*1px) ($i*1px) $color, comma);
    }
    @return $shadows;
}

h1 {
    color: white;
    background: hsl(0,50%,45%);
    text-shadow: text-retro();
}
```


### 资料
* 转摘：[CSS秘密花园： 逼真的文本效果](http://www.w3cplus.com/css3/css-secrets/realistic-text-effects.html) 或 [CSS秘密花园： 逼真的文本效果](http://www.tuicool.com/articles/nmuUj26)
* 对比度测试：[contrast-ratio](leaverou.github.io/contrast-ratio)


