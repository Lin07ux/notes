`background`属性是 CSS 中最常见的属性之一，它是一个简写属性，其包含`background-color`、`background-image`、`background-repeat`、`background-attachment`、 `background-position`、`background-clip`、`background-origin`和`background-size`。

### background-position
`background-position`是用来控制元素背景图片的位置。它接受三种值：

- 关键词，比如`top`、`right`、`bottom`、`left`和`center`
- 长度值，比如`px`、`em`、`rem`等
- 百分值`%`

其常用的值对应的坐标如下图所示：

![](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1484662450241.png)

**注意**

常见的值对应的效果都比较好理解，但是如果取值是非常规的百分比值的时候，其计算方法就需要注意了。

[W3C规范](https://www.w3.org/TR/css3-background/#the-background-origin)是这样描述的：

> A percentage for the horizontal offset is relative to (width of background positioning area – width of background image). A percentage for the vertical offset is relative to (height of background positioning area – height of background image), where the size of the image is the size given by 'background-size'.

也就是说：当背景图片尺寸(`background-size`)不做任何的重置时，水平百分比的值等于容器宽度百分比值减去背景图片宽度百分比值。垂直百分比的值等于容器高度百分比值减去背景图片高度百分比值。

比如前面的示例，如果取值`background-position: 75% 50%;`，背景图片的起始位置：

* 水平位置 x 轴： (410 - 100) * 75% = 232.5px
* 垂直位置 y 轴： (210 - 100) * 50% = 55px

通过一个 Gif 图来描述其对应的效果：

![](http://7xkt52.com1.z0.glb.clouddn.com/Zz6n6vY.jpg)

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


### 转摘
[你真的了解background-position](http://web.jobbole.com/89957/)

