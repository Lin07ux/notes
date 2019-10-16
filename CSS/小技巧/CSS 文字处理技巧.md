> 转摘：[CSS实用技巧：文字处理](http://developer.51cto.com/art/201910/604187.htm)

### 1. 文本两端对齐

CSS 最常用的对齐方式，居中对齐、左对齐（默认）、右对齐，而且实现起来也是非常的简单，使用`text-align: center|left|right`即可。

但是两端对齐如果使用`text-align: justify`则可能效果并会如预期一样，此时就需要使用**`text-align-last: justify`**了。

`text-align-last` 描述的是一段文本中最后一行在被强制换行之前的对齐规则。可选值有：`auto`、`start`、`end`、`left`、`right`、`center`、`justify`、`inherit`、`initial`、`unset`。

比如：

```html
<ul class="justify-text">
  <li>账号</li>
  <li>密码</li>
  <li>电子邮件</li>
</ul>
```

```scss
.justify-text {
  li {
    list-style: none;
    padding: 0 20px;
    width: 100px;
    line-height: 40px;
    color: #fff;
    background-color: #f13f84;
    text-align-last: justify;
  }
  
  li + li {
    margin-top: 10px;
  }
}
```

效果类似如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1571115467368.png" width="194"/>

### 3. 文本从右至左排列

CSS `writing-mode`属性可以调整文本排版方向，定义文本在水平或垂直方向上如何排布。

`writing-mode`目前主要有三个可用值，各个值的描述如下：

* `horizontal-tb` 水平方向自上而下的书写方式，即`left-right-top-bottom`
* `vertical-rl` 垂直方向自右而左的书写方式，即`top-bottom-right-left`
* `vertical-lr` 垂直方向内内容从上到下，水平方向从左到右

> CSS Level 4 还添加了更多的模式，目前支持性不是很好，暂不介绍。

可以看到，有水平的从左向右方式，但是没有水平从右向左方式，但是可以通过负的`letter-spacing`来实现。

比如：

```html
<div class="vertical-text">
  <h3>诗经</h3>
  <p>死生契阔，<br>与子成说。<br>执子之手，<br>与子偕老。</p>
</div>
```

```scss
.vertical-text {
  h3 {
    padding-left: 10px;
    font-weight: bold; 
    font-size: 18px;
    color: #d60f5c; 
  }
    
  p {
    margin-left: 7em;
    line-height: 1.6;
    letter-spacing: -2.7em;
    color: #ee1166;
  }
}
```

这里最需要注意的就是`letter-spacing: -2.7em;`这个配置，这里设置的词间距是负值，而且至少要是`font-size`的 2 倍。另外，`margin-left: 7em;`是为了避免文字被遮蔽。

效果如下：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1571116590261.png" width="160"/>

### 3. 多行文字溢出
 
单行文字溢出效果各个浏览器基本都已经实现了，多行文字溢出则需要使用一些尚未标准化的 CSS 属性：

```scss
.ellipsis {
  width: 400px;
  display: -webkit-box;
  overflow: hidden;
  text-overflow: ellipsis;
  -webkit-line-clamp: 3;
  /* autoprefixer: off */
  -webkit-box-orient: vertical;
  /* autoprefixer: on */
}
```

这里`-webkit-line-clamp: 3;`表示展示 3 行，超过 3 行的内容会被折叠，并显示省略号。

特别注意的是，**使用文本溢出处理时，容器一定要定义宽度。**

### 4. 文本选中后的颜色

在浏览器中，选择文本时，会自动为选中的文本设置背景色和文字颜色。可以通过使用`::selection`伪类来自定义文本选择颜色。

比如：

```html
<div class="select-color">  
 <p>红豆生南国，</p>  
 <p class="special">春来发几枝。</p>  
 <p>愿君多采撷，</p>  
 <p class="special">此物最相思。</p>  
</div> 
```

```scss
// 全局文本选择样式  
::selection {
  background-color: #f13f84;
  color: #fff;
}
.select-color {
  line-height: 30px;
  font-weight: bold;
  font-size: 30px;
  color: #d60f5c;
}
// 具体某个选择器下 文本选择样式
.special::selection {
  background-color: #00b7a3;
}
```

效果如下：

![](http://cnd.qiniu.lin07ux.cn/markdown/3iu6JjB.gif)


