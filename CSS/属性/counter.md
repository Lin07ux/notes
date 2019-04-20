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

content 甚至可以使用 url 放入图片图片的功能，下列的代码会呈现出三张图片：

```css
div::before{
  content:url(图片网址) url(图片网址) url(图片网址);
}
```

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1555641074448.png"/>



