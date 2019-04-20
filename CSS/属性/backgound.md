`background`属性是 CSS 中最常见的属性之一，它是一个简写属性，其包含`background-color`、`background-image`、`background-repeat`、`background-attachment`、 `background-position`、`background-clip`、`background-origin`和`background-size`。

### 1. background-position

`background-position`是用来控制元素背景图片的位置。它接受三种值：

- 关键词，比如`top`、`right`、`bottom`、`left`和`center`
- 长度值，比如`px`、`em`、`rem`等
- 百分值`%`

其常用的值对应的坐标如下图所示：

![](http://cnd.qiniu.lin07ux.cn/markdown/1484662450241.png)

**注意**

常见的值对应的效果都比较好理解，但是如果取值是非常规的百分比值的时候，其计算方法就需要注意了。

[W3C规范](https://www.w3.org/TR/css3-background/#the-background-origin)是这样描述的：

> A percentage for the horizontal offset is relative to (width of background positioning area – width of background image). A percentage for the vertical offset is relative to (height of background positioning area – height of background image), where the size of the image is the size given by 'background-size'.

也就是说：当背景图片尺寸(`background-size`)不做任何的重置时，水平百分比的值等于容器宽度减去背景图片宽度得到的差值的百分比值。垂直百分比的值等于容器高度减去背景图片高度得到的差值的百分比值。

比如前面的示例，如果取值`background-position: 75% 50%;`，背景图片的起始位置：

* 水平位置 x 轴： (410 - 100) * 75% = 232.5px
* 垂直位置 y 轴： (210 - 100) * 50% = 55px

通过一个 Gif 图来描述其对应的效果：

![](http://cnd.qiniu.lin07ux.cn/Zz6n6vY.jpg)

**将来的特性**

`background-position`未来将具有的一个特性，就是可以显式的通过关键词指定背景图片距离容器的位置。

```css
background-position: left 10px top 15px;  /* 10px, 15px */
background-position: left      top    ;   /*  0px,  0px */
background-position:      10px    15px;   /* 10px, 15px */
background-position: left          15px;  /*  0px, 15px */
background-position:      10px top    ;   /* 10px,  0px */
background-position: left      top 15px;  /*  0px, 15px */
background-position: left 10px top    ;   /* 10px,  0px */
```

> 转摘：[你真的了解background-position](http://www.w3cplus.com/css/background-position-with-percent.html)

### 2. background-clip 背景剪裁

背景裁剪确定了背景画布的区域。可应用于所有元素。无继承性。

可选值有：

- `border-box`：背景图片或者颜色描绘区域延伸到边框边界，这是默认值
- `padding-box`：背景图片或者颜色描绘区域只能在盒子 padding 区域
- `content-box`：背景图片或者颜色描绘只能在内容区域起作用。

### 3. background-origin 背景图片原点

背景图片原点原点属性严格意义上来说是针对 css 中使用图片属性`background-image`的情况下使用的，因为只有引用了背景图片之后才能发挥其原点的微妙区别。

可选值：

- `padding-box`：这是它的默认值，与背景裁剪属性默认值有所不同。该值确定了背景相对填充框作为原点位置，并且拉伸整个元素 padding 盒子，即从左上角到右下角拉伸，整个背景被拉伸自适应元素的宽高，这点在有`border-width`的时候特别明显；
- `border-box`：规定了背景图片原点位置相对边框盒子
- `content-box`：规定了背景图片原地位置相对内容区盒子

**注意**：

* 1. 假如背景图片使用了`background-attachment: fixed`那么这个值是不起作用的，背景区域就是初始包含块。
* 2. 假如`background-clip: padding-box`，`background-origin: border-box`，`background-position: top left`(也就是初始位置)，并且元素有一个非 0 数值边框宽度，那么左侧和顶部边框的图片会被裁剪。


### 转摘
[你真的了解background-position](http://web.jobbole.com/89957/)

