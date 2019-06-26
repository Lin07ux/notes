> 转摘：[CSS :visited伪类选择器隐秘往事回忆录 -- 张鑫旭](https://www.zhangxinxu.com/wordpress/?p=8060)

链接的 4 个伪类（后两个伪类后来拓展到几乎所有 HTML 标签元素）如果同时使用，其顺序是这样的：`:link` → `:visited` → `:hover` → `:active`。首字母连起来是 LVHA，顺序完全符合 love-hate，也就是爱恨，所谓由爱生恨。

### :link

直接用`a`选择器和使用`a:link`选择器大部分情况下是相同的，但是其实两者是有区别的：前者可以匹配所有的`a`元素，而后者只能匹配含有`href`属性的`a`元素。

```html
<a href="##">文字</a>
<a>文字2</a>
```

上面两个`a`元素都可以匹配`a`选择器，但只有第一个`a`元素可以匹配`a:link`选择器。

> 使用`a:link`选择器的时候， `a:visited`选择器也一定要设置（因为`a:link`在最前面），不然访问过的链接颜色就会跟着系统或者当前元素设置的 color 走，表现反而有些乱，因此，当下已经很少见到使用`:link`伪类选择器的了。

### :visited

1. 可设置属性有限

    或许是出于安全考虑，`:visited`伪类选择器支持 CSS 很有限，目前仅支持下面这些 CSS：`color`、`background-color`、`border-color`、`border-bottom-color`、`border-left-color`、`border-right-color`、`border-top-color`、`column-rule-color`以及`outline-color`。
    
    同时，类似`::before`、`::after`这些伪元素都不支持，例如，我们希望使用文字标示已经访问过的链接，如下：
    
    ```html
    a:visited::after{content:'visited';}
    ```
    
    但是并没有任何浏览器支持。
    
    同时，`:visited`伪类虽然支持子选择器，但所能控制的 CSS 属性和`:visited`一模一样，就那几个和颜色相关的属性，也不支持`::before`、`::after`这些伪元素。

2. 不支持半透明

    使用`:visited`伪类选择器控制颜色的时候，虽然语法上支持半透明色，但是表现上，要么纯色，要么全透明。
    
    ```css
    a:visited { color: rgba(255,0,0,.5); }
    ```
    
    这样并不会是半透明的红色，而且全红。

3. 只能重置，不能凭空设置

    `:visited`伪类选择器中的色值只能重置，不能凭空设置。也就是说，必须链接元素必须已经设置过某个颜色属性，这样其`:visited`伪类选择器才可以改变这个颜色属性的值。
    
    比如，对于下面的 CSS 设置，当链接被访问后其背景色并不会发生变化：
    
    ```css
    a { color: blue; }
    a:visited { color: red; background-color: gray; }
    ```
    
    修改成下面这样就可以了：
    
    ```css
    a { color: blue; background-color: white; }
    a:visited { color: red; background-color: gray; }
    ```

4. 无法用 js 获取经过 :visited 设置过的属性值

    `:visited`设置并呈现的色值无法获取，也就是说，当文字颜色值表现为`:visited`选择器设置的颜色值的时候，我们使用 JS 的`getComputedStyle()`是获取不到这个颜色值的。
    
    已知 CSS 如下：
    
    ```css
    a { color: blue; }
    a:visited { color: red; }
    ```
    
    当访问过这个链接之后，链接会变为红色，此时我们运行下面的 JavaScript 代码：
    
    ```JavaScript
    window.getComputedStyle(document.links[0]).color;
    ```
    
    结果得到的是`"rgb(0, 0, 255)"`，也就是蓝色 blue 对应的 RGB 值。

