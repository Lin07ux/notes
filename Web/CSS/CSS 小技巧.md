### transform 问题
在 Chrome 和 Opera 浏览器下，使用 CSS3 的`transform: translate(0, 0)`转化位置节点，其所有使用`position:fixed`定位的子孙节点的定位功能均无效。


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


