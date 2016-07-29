给文本添加下划线能很简单的就实现，但是其实各有优劣。下面提供四种方案，其中第四个方案则是目前来说是最理想的。

转摘：[33.给文字加上下划线 ](https://github.com/ccforward/cc/issues/33)

### 1. text-decoration
给文字添加下划线，最简单的就是使用`text-decoration: underline;`，但是下划线的颜色不好控制(`text-decoration-color`并不被主流浏览器所支持)。


### 2. border-bottom
也可以使用下边框`border-bottom: 1px solid #333;`模拟下划线。但是`border-bottom`和文本之间的间距太大(只能在文字最下边)。虽然可以通过设置一个较小的`line-height`来实现，但是还有一个问题，边框会阻止文本的正常换行行为。如下图：

```css
display: inline-block;
border-bottom: 1px solid #333;
line-height: .9;
```

![阻止换行](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1469627422880.png)


### 3. box-shadow
使用内嵌的阴影来模式下划线：`box-shadow: 0 -1px #333 inset;`。这样虽然能让下划线离文本近一点，但是实际效果也并不太好。


### 4. background
最理想的解决方案是利用`background`属性来模拟下划线。

```css
background: linear-gradient(#f00, #f00) repeat-x;
background-size: 100% 1px;
background-position: 0 1em;
```

这样就显得很优雅柔和了：

![background 模拟下划线](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1469627717910.png)

不过还有问题，字母 p 和 y 被下划线穿过了，如果遇到字母能自动避开会更好，所以，假如背景是一片实色，即可以给文字设置两层与背景色相同的`text-shadow`来模拟这种效果：

```css
background: linear-gradient(#f00, #f00) repeat-x;
background-size: 100% 1px;
background-position: 0 1em;
text-shadow: .05em 0 #fff, -.05em 0 #fff;
```

![background 和 text-shadow](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1469627826813.png)

此外，使用背景渐变来实现下划线可以做到相当灵活的转换。比如一条绿色虚线下划线：

```css
background: linear-gradient(90deg, #f00 70%, transparent 0) repeat-x;
background-size: .2em 2px;
background-position: 0 1em;
text-shadow: .05em 0 #fff, -.05em 0 #fff;
```

![渐变背景色](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1469627916379.png)

可以通过色标的百分比调整虚线的虚实比例，用`background-size`来调整虚线的疏密。


 

