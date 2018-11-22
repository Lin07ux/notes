页面中的 img 元素，想要获取它的原始尺寸，以宽度为例，可能首先想到的是元素的 innerWidth 属性，或者 jQuery 中的 width() 方法。如下：

```html
<img id="img" src="1.jpg">

<script type="text/javascript">
    var img = document.getElementById("img");
    console.log(img.innerWidth); // 600
</script>
```

这样貌似可以拿到图片的尺寸。但是如果给 img 元素增加了 width 样式属性，比如图片实际宽度是 600，设置了 width 为 400。这时候 innerWidth 为 400，而不是 600。显然， 用 innerWidth 获取图片原始尺寸是不靠谱的。这是因为 innerWidth 属性获取的是元素盒模型的实际渲染的宽度，而不是图片的原始宽度。

jQuery 的 width() 方法在底层调用的是 innerWidth 属性，所以 width() 方法获取的宽度也不是图片的原始宽度。

那么该怎么获取 img 元素的原始宽度呢？

### naturalWidth / naturalHeight
现代浏览器(包括IE9)为img元素提供了 naturalWidth 和 naturalHeight 属性来获取图片的实际宽度与高度 。如下：

```sql
var naturalWidth = document.getElementById('img').naturalWidth,
    naturalHeight = document.getElementById('img').naturalHeight;
```

![naturalWidth / naturalHeight](http://cnd.qiniu.lin07ux.cn/markdown/1472484850577.png)

### IE7/8中的兼容性实现：

在 IE8 及以前版本的浏览器并不支持 naturalWidth 和 naturalHeight 属性。

在 IE7/8 中，我们可以采用 new Image() 的方式来获取图片的原始尺寸，如下：

```sql
function getNaturalSize (Domlement) {
    var img = new Image();
    img.src = DomElement.src;
    return {
        width: img.width,
        height: img.height
    };
}

// 使用
var natural = getNaturalSize (document.getElementById('img')),
    natureWidth = natural.width,
    natureHeight = natural.height;
```

其实这个方法也适用于现代浏览器。

### 总结
可以结合上的两种方式，组合成通用的方法。

```sql
function getNaturalSize (Domlement) {
    var natureSize = {};
    if(window.naturalWidth && window.naturalHeight) {
        natureSize.width = Domlement.naturalWidth;
        natureSizeheight = Domlement.naturalHeight;
    } else {
       var img = new Image();
        img.src = DomElement.src;
        natureSize.width = img.width;
        natureSizeheight = img.height;
    }
    return natureSize;
}

// 使用
var natural = getNaturalSize (document.getElementById('img')),
    natureWidth = natural.width,
    natureHeight = natural.height;
```

### 转载
[JS：获取图片的原始宽度和高度](http://www.dengzhr.com/js/974)

