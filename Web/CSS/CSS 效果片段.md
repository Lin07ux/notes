### hover 浮动效果
```css
li:hover {
    box-shadow: 0 17px 50px 0 rgba(0,0,0,.19);
    transform: translate3d(0,-2px,0);
    transition: all .3s cubic-bezier(.55,0,.1,1);
}
```

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468997594653.png" width="485"/>

> 参考：[一加官网](http://www.oneplus.cn/)

### 给文本画上斑马线
使用`linear-gradient`生成条纹背景图，并进行 repeat 即可。主要是要控制好每个条纹的宽度/高度(使用 em 单位)。

```css
pre {
    width: 100%;
    padding: .5em 0;
    line-height: 1.5;
    color: #333;
    font-size: 16px;
    background: #f5f5f5;
    background-image: linear-gradient(rgba(0,0,120,.1) 50%, transparent 0);
    background-size: auto 3em;
    background-origin: content-box;
    tab-size: 2;
}
```

这里由于设置了`lin-height: 1.5;`，所以两行文本的高度就是 3em，那么背景中就要设置高度为 3em，默认即为可重复的。另外，`backgrouun-origin: content-box;`表示背景是从文本区域开始展示的，避免斑马纹和文本之间出现错位。

![斑马纹](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472349064385.png)


