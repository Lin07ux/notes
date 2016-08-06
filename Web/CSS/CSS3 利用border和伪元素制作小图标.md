## 边框
如果一个元素的边框宽度不为0，而高宽都为0，那么就会显示出如下的图形：

![边框](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470481211379.png)

这是由于浏览器处理后的效果：在两个边框交接处，会自动的给每个边框分一半的区域。所以，将其他边框的颜色设置为透明，只保留一个边框，我们就可以利用和特性来制作三角形了。

如下，可以分别制作出了四个不同方向的三角形：

### 四个方向的三角形

```html
<div class="triangle-top"></div>
<div class="triangle-right"></div>
<div class="triangle-bottom"></div>
<div class="triangle-left"></div>
<style>
    .triangle-top,
    .triangle-right,
    .triangle-bottom,
    .triangle-left{
        margin: 20px auto;
        width: 0;
        height: 0;
        border: 100px solid transparent;
    }
    
    .triangle-top{
        border-top-color: coral;
    }
    
    .triangle-right{
        border-right-color: lightblue;
    }
    
    .triangle-bottom{
        border-bottom-color: lightgreen;
    }
    
    .triangle-left{
        border-left-color: mediumpurple;
    }
</style>
```

效果如下图所示：

![边框制作三角形](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470481508651.png)

### 旗帜图标
利用边框可以实现如下图所示的边框：

![旗帜图标](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470481807148.png)

对应的 CSS 如下：

```css
.flag{
    width: 0;
    height: 0;
    border: 2rem solid #FF6600;
    border-top-width: 4rem;
    border-bottom-color: transparent;
    border-bottom-width: 2rem;
}
```

### 直角三角形
直角三角形其实就是保留了两个相邻的边框，另外两个边框设置为透明即可。如下：

```css
.ribbon:before,
.ribbon:after{
    content: "";
    position: absolute;
    display: block;
    border-style: solid;
    border-color: #bf004c transparent transparent transparent;
    bottom: -0.6rem;
}
 
.ribbon:before{
    left: 0;
    border-width: 0.6rem 0 0 0.6rem;
}
 
.ribbon:after{
    right: 0;
    border-width: 0.6rem 0.6rem 0 0;
}
```

效果如下图：

![直角三角形](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470484760525.png)

### 伪元素
利用伪元素，能够在不增加 HTML 结构的情况下，制作出一些辅助图标。比如，可以利用伪元素的边框制作出三角形。如下所示：

```css
.bubble{
    position: relative;
    background-color: #33AAEE;
    width: 10rem;
    height: 3rem;
    font-size: 2rem;
    line-height: 3rem;
    color: #FFF;
    text-align: center;
}
 
.bubble:before{
    position: absolute;
    content: "";
    right: 100%;
    top: 1rem;
    width: 0;
    height: 0;
    border-top: 0.6rem solid transparent;
    border-right: 0.6rem solid #33AAEE;
    border-bottom: 0.6rem solid transparent;
    border-left: 0.6rem solid transparent;
}
 
.bubble .text{
    display: inline-block;
}
```

效果如下图所示：

![伪元素和边框结合](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470481690670.png)

