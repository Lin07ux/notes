在页面中，经常会用到如下的打点加载效果：

![dot loading](http://cnd.qiniu.lin07ux.cn/dot-loading.gif)

有多种实现方法：

### CSS3 animation
HTML 代码如下：

```html
订单提交中<dot>...</dot>
```

CSS 代码如下：


```css
dot {
    display: inline-block;
    height: 1em;
    line-height: 1;
    vertical-align: -.25em;
    overflow: hidden;
}

dot::before {
    display: block;
    content: '...\A..\A.';
    white-space: pre-wrap;
    animation: dot 3s infinite step-start both;
}

@keyframes dot {
    33% { transform: translateY(-2em); }
    66% { transform: translateY(-1em); }
}
```

这里，关键点在于伪元素中的`content`属性中的值使用了`\A`来进行换行。然后在动画中将其在 Y 轴进行移动，而且使用的是`step`模式的动画。

> `\A`其实指的是换行符中的 LF 字符，其 unicode 编码是`000A`，在 CSS content 属性中则直接写作`\A`；换行符除了 LF 字符还有 CR 字符，其 Unicode 编码是`000D`，在 CSS content 属性中则直接写作`\D`。CR 字符和 LF 字符分别指回车（CR）和换行（LF）。

另外，这种方法的好处还有：

* 使用自定义 dot 元素，对于低版本的 IE，由于不认识，则会将其对应的 css 样式都忽略掉，只显示默认的三个点，从而避免了兼容性问题。
* 使用`::before`伪元素，而且设置`display`为 block，可以将 dot 元素中的三个点挤到隐藏区域中，仅显示伪元素 content 中的内容。

转摘：[CSS content换行技术实现字符animation loading效果](http://www.zhangxinxu.com/wordpress/2016/11/css-content-pre-animation-character-loading/)
参考：[tawian/text-spinners](https://github.com/tawian/text-spinners)
更多示例：[text-spinners](http://tawian.io/text-spinners/)

### 基于 border + background 实现的打点效果
HTML：

```html
订单提交中<span class="dotting"></span>
```

CSS：

```css
.dotting {
    display: inline-block;
    width: 10px;
    min-height: 2px;
    padding-right: 2px;
    border-left: 2px solid currentColor;
    border-right: 2px solid currentColor;   
    background-color: currentColor;
    background-clip: content-box;
    box-sizing: border-box;
    animation: dot 4s infinite step-start both;
    *zoom: expression(this.innerHTML = '...'); /* IE7 */
}
.dotting:before { content: '...'; } /* IE8 */
.dotting::before { content: ''; }
:root .dotting { margin-left: 2px; padding-left: 2px; } /* IE9+ */

@keyframes dot {
    25% {  /* 0个点 */
        border-color: transparent;
        background-color: transparent;
    }
    50% { /* 1个点 */
        border-right-color: transparent;
        background-color: transparent;
    }
    75% { /* 2个点 */
        border-right-color: transparent; 
    }
}
```

说明：

* 是 4 秒动画，每秒钟显示 1 个点；
* IE7/IE8 是内容生成，静态的，如果无需兼容 IE7/IE8, 可以按照注释说明删除一些 CSS；
* currentColor 关键字可以让图形字符化，必不可少；
* background-clip 属性可以让 IE9+ 浏览器下左右 padding 没有背景色，于是形成了等分打点效果。

转摘：[再说CSS3 animation实现点点点loading动画](http://www.zhangxinxu.com/wordpress/2014/12/css3-animation-dotting-loading/)

### 等宽字体
另外，还可以考虑使用等宽字体和 CSS 中的 ch 单位来控制显示的内容。

HTML：

```html
订单提交中<dot>...</dot>
```

CSS：

```css
dot {
   display: inline-block; 
   width: 3ch;
   text-indent: -1ch;
   vertical-align: bottom; 
   overflow: hidden;
   /* 等宽字体很重要 */
   font-family: Consolas, Monaco, monospace;
   animation: dot 3s infinite step-start both;
}

@keyframes dot {
    33% { text-indent: 0; }
    66% { text-indent: -2ch; }
}
```

但是这种方式的兼容性并不太好。

转摘：[等宽字体在web布局中应用以及CSS3 ch单位嘿嘿](http://www.zhangxinxu.com/wordpress/2016/07/monospaced-font-css3-ch-unit/)

