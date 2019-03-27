在 CSS 中，`before/after`伪元素选择器中，有一个`content`属性，能够实现页面中的内容插入。

### 1. 插入纯文字

`content: "插入的文章"`，或者`content: none`不插入内容。

```html
<h1>这是h1</h1>
<h2>这是h2</h2>
```

```css
h1::after{
    content:"h1后插入内容";
    margin-left: 10px;
    color: red;
}
h2::after{
    content:none
}
```

显示效果：![插入纯文字](http://cnd.qiniu.lin07ux.cn/markdown/1470995058170.png)

 
### 2. 嵌入文字符号

可以使用`content`属性的`open-quote`属性值和`close-quote`属性值在字符串两边添加诸如括号、单引号、双引号之类的嵌套文字符号。`open-quote`用于添加开始的文字符号，`close-quote`用于添加结束的文字符号。 

```css
h1{
    quotes:"(" ")";  /*利用元素的quotes属性指定文字符号*/
}
h1::before{
    content:open-quote;
}
h1::after{
    content:close-quote;
}
h2{
    quotes:"\"" "\"";  /*添加双引号要转义*/
}
h2::before{
    content:open-quote;
}
h2::after{
    content:close-quote;
}
```

![效果图](http://cnd.qiniu.lin07ux.cn/markdown/1471010028745.png)

> `quotes`属性用于设置嵌套引用（embedded quotation）的引号类型。有三种赋值方式：
> 1. `none` 规定 "content" 属性的 "open-quote" 和 "close-quote" 的值不会产生任何引号。
> 2. `string string string string` 定义要使用的引号。前两个值规定第一级引用嵌套，后两个值规定下一级引号嵌套。后两个值可以不填写。
> 3. `inherit` 规定应该从父元素继承 quotes 属性的值。
> 其可以使用的引号字符如下表
> ![quotes 引号字符](http://cnd.qiniu.lin07ux.cn/markdown/1471009833718.png)


### 3. 插入图片

`content`属性也可以通过 url 来引入图片。

```html
<h3>这是h3</h3>
```

```css
h3::after{
    content:url(http://ido321.qiniudn.com/wp-content/themes/yusi1.0/img/new.gif)
}
```

![插入图片](http://cnd.qiniu.lin07ux.cn/markdown/1471010185632.png)


### 4. 插入元素的属性值

`content`属性最强大的功能是可以直接利用 attr 获取元素的属性，将其插入到对应位置。

```html
<a href="http:///www.ido321.com">这是链接</a>
```

```css
a:after{
    content:attr(href);
}
```

![插入元素属性值](http://cnd.qiniu.lin07ux.cn/markdown/1471010281150.png)


### 5. 插入项目编号

#### 5.1 基本编号

利用`content`的`counter`属性可以针对多个项目追加连续编号。

```html
<h1>大标题</h1>
<p>文字</p>
<h1>大标题</h1>
<p>文字</p>
<h1>大标题</h1>
<p>文字</p>
<h1>大标题</h1>
<p>文字</p>
```

```css
h1{
    counter-increment:my;
}
h1:before{
    content:counter(my)'.';
}
```

![编号](http://cnd.qiniu.lin07ux.cn/markdown/1471010384936.png)

> `counter-increment`默认情况下，会将其第一个参数对应的计数器加1，但是还可以指定第二个参数，表示计数器变化的值，比如：`counter-increment: my 10;`表示计数器 my 的值在这一步加 10；`counter-increment: my -2;`表示计数器 my 的值在这一步减2。

#### 5.2 编号修饰

还可以修饰项目的编号：

```css
h1{
    counter-increment:my;
}
h1:before{
    content:'第'counter(my)'章';
    color:red;
    font-size:42px;
}
```

![修饰编号](http://cnd.qiniu.lin07ux.cn/markdown/1471010457174.png)

#### 5.3 编号样式

项目的编号可以不仅仅是数字编号，还可以参考 ul 元素的`list-style-type`属性的值。

```css
h1{
    counter-increment:my;
}
h1:before{
    content:counter(my,upper-alpha);
    color:red;
    font-size:42px;
}
```

![其他类型的编号](http://cnd.qiniu.lin07ux.cn/markdown/1471010542048.png)

#### 5.4 嵌套编号

编号还能进行嵌套使用，每种类型的元素都可以有其独立的编号，而不互相影响：

```html
<h1>大标题</h1>
<p>文字1</p>
<p>文字2</p>
<p>文字3</p>
<h1>大标题</h1>
<p>文字1</p>
<p>文字2</p>
<p>文字3</p>
<h1>大标题</h1>
<p>文字1</p>
<p>文字2</p>
<p>文字3</p>
```

```css
h1::before{
    content:counter(h)'.';
}
h1{
    counter-increment:h;
}
p::before{
    content:counter(p)'.';
    margin-left:40px;
}
p{
    counter-increment:p;
}
```

![嵌套编号](http://cnd.qiniu.lin07ux.cn/markdown/1471010665514.png)

#### 5.5 重置编号

对于上面的例子，p 元素的编号是连续的。如果想对于每一个 h1 元素后的三个 p 重新编号的话，可以使用`counter-reset`属性重置：

```css
h1{
    counter-increment:h;
    counter-reset:p;
}
```

![重置编号](http://cnd.qiniu.lin07ux.cn/markdown/1471010777041.png)

#### 5.6 复杂编号

每一类元素的编号不仅仅能在其上使用，还可以在其他元素上被使用，这样就可以实现更复杂的编号：

```html
<h1>大标题</h1>
<h2>中标题</h2>
<h3>小标题</h3>
<h3>小标题</h3>
<h2>中标题</h2>
<h3>小标题</h3>
<h3>小标题</h3>
<h1>大标题</h1>
<h2>中标题</h2>
<h3>小标题</h3>
<h3>小标题</h3>
<h2>中标题</h2>
<h3>小标题</h3>
<h3>小标题</h3>
```

```css
h1::before{
    content:counter(h1)'.';
}
h1{
    counter-increment:h1;
    counter-reset:h2;
}
h2::before{
    content:counter(h1) '-' counter(h2);
}
h2{
    counter-increment:h2;
    counter-reset:h3;
    margin-left:40px;
}
h3::before{
    content:counter(h1) '-' counter(h2) '-' counter(h3);
}
h3{
    counter-increment:h3;
    margin-left:80px;
}
```

![嵌套编号](http://cnd.qiniu.lin07ux.cn/markdown/1471011090223.png)


### 6. 参考

1. [CSS3的content属性详解](https://github.com/dwqs/blog/issues/28)
2. [小tip:CSS计数器+伪类实现数值动态计算与呈现](http://www.zhangxinxu.com/wordpress/2014/12/css-counters-pseudo-class-checked-numbers/)

