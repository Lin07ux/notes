### transform 问题
在 Chrome 和 Opera 浏览器下，使用 CSS3 的`transform: translate(0, 0)`转化位置节点，其所有使用`position:fixed`定位的子孙节点的定位功能均无效。


### 实现父选择器效果
转摘：[如何在CSS中实现父选择器效果？ -- 张鑫旭](http://www.zhangxinxu.com/wordpress/2016/08/css-parent-selector/)

由于 HTML 和 CSS 的解析渲染顺序是从上往下进行，所以目前 CSS 还不支持后面的元素影响前面的元素的样式，或者子元素影响父元素的样式的功能。

我们可以使用影响后面的兄弟元素的样式来模拟影响父元素的样式的效果。关键点就在于 **把兄弟元素作为祖先元素使用** 。涉及的多个技术 tips 从 IE7 浏览器开始都是支持的，因此模拟父选择器效果兼容 IE7+ 浏览器。

示例：实现的效果如下图所示，当 input 处于 focus 状态的时候，外面的灰色框要变成高亮状态。

![HTML 结构](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470718599041.png)

![效果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470718591282.png)

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

> 绝对定位元素，如果没有具体的`width/height`限定，`left/right`以及`top/bottom`对立方位的数值会拉伸元素的尺寸。这里这里，全部都设为 0，和`.container`所在元素组合，就形成了类似宽高 100% 同时`box-sizing:border-box;`的效果。所以，如果你想让 IE7 浏览器实现类似`border-box`的盒尺寸计算，某些情况下，就可以使用绝对定位拉伸！

### 图片延迟加载方案图片处理
浏览器解析img标签的时候，如果src属性为空，浏览器会认为这个图片是坏掉的图，会显示出图片的边框，会影响显示的效果。

延迟加载过程中会改变图片的 class：默认`lazyload`，加载中`lazyloading`，加载结束：`lazyloaded`。结合这个特性我们有两种解决上述问题办法：

1、设置`opacity:0`，然后在显示的时候设置`opacity:1`。

```css
.lazyload,
.lazyloading{
    opacity: 0;
}
.lazyloaded{
    opacity: 1;
    transition: opacity 500ms; //加上transition就可以实现渐现的效果
}
```

2、用一张默认的图占位，比如1x1的透明图或者灰图。

```html
<img class="lazyload" 
    src="data:image/gif;base64,R0lGODlhAQABAAA
       AACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" 
    data-src="真实url" 
    alt="<%= article.title %>">
```


### 微信浏览器隐藏滚动条

```css
<style type="text/css">
    ::-webkit-scrollbar {
        width: 0;
        height: 0;
        background-color: rgba(240,240,240,0)
    }
</style>
```


### 用`attr()`显示 HTML 属性值
`attr()`功能早在CSS 2.1标准中已经出现，它提供了一个巧妙的方法在 CSS 中使用 HTML 标签上的属性，在很多情况下都能省去以往需要 Javascript 处理的过程。

要想使用这个功能，你需要用到三个条件：一个伪元素(`:before`或`:after`)的样式；CSS 中的`content`属性；和一个带有你想使用的 HTML 属性名称的`attr()`表达式。

> `content` 属性可以支持同时使用多个值，用空格分隔多个值。

例如，想去显示 <h3> 标题上的 data-prefix 属性的值，你可以写成这样：

```html
<h3 data-prefix="Custom prefix">This is a heading</h3>

<style type="text/css">
    h3:before {
        content: attr('data-prefix') " ";
</style>
```

另一个例子更有实用意义：当用户打印页面时将页面链接显示出来。

```html
<style type="text/css">
    @media print {
        a:after {
            content: " (link to " attr(href) ") ";
        }
    }
</style>

<a href="http://example.com">Visit our home page</a>
```

### 使用`counter()`在列表中自动添加序号
`counter()`也是在 CSS 2.1 就已经支持的功能。使用它能方便的在页面标题、区块和其它各种连续出现的页面内容上添加序号。有了它，就不必限制于只能使用`<ol>` 来实现这个效果，可以更灵活的在页面上使用自定义数字序列。

同样，它也是借助于伪元素样式中的`content`属性来实现效果的，但是它需要结合`counter-reset`和`counter-increment`属性来使用。

- `counter-reset` 属性设置某个选择器出现次数的计数器的值。默认为 0。
    语法为：`counter-reset: counter-name [init-val]`
    其中，第一个参数为计数器的名称，第二个参数可选，可以设置为任何值，可以是正值或负值。不设置时默认为 0。
    其作用相当于初始化了一个值为 0 的计数器，之后可以在任意地方引用这个计数器以获取其值。
    **注意**：`counter-reset`可以定义在任意元素上，但是建议定义在使用这个计数器的元素的祖先元素上，否则会出现不可预料的计数问题；在定义顺序上没有要求，可以在被引用的后面。
        
- `counter-increment` 属性设置某个选取器每次出现的计数器增量。默认增量是 1。
    语法：`counter-increment: counter-name [increment-val]`
    其中，第一个参数是计数器的名称，也就是之前`counter-reset`中设置的计数器。
    第二个参数可选，表示递增的量值，可以为正数或负数。如果没有提供，默认为 1。
    利用这个属性，计数器可以递增（或递减）某个值。

示例：下面的例子可以完成类似'1'，'1.1'，'1.2'这种效果。

基本的 html 代码：

```
main>section*3>h3+p{这是第$个元素}*3
```

css 样式：

```css
main { counter-reset: main-c; }
section { counter-reset: sub-c; }
h3:before {
  counter-increment: main-c;
  content: counter(main-c) ". ";
}
p:before { 
  counter-increment: sub-c;
  content: counter(main-c) "." counter(sub-c) " ";
}
```

[示例网址](http://codepen.io/Lin07ux/pen/LNrVjy?editors=1100)

### inline 元素 float
inline 元素 使用 float 之后，会“块”化，也就是按照块级元素的样式显示。


