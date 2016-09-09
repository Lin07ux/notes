### 移动端标签点击后变暗
在移动端使用 a,button,input,optgroup,select,textarea 标签的时候，点击后会出现一个"暗色的"背景，这时候我们需要在css加入如下代码即可禁用这个效果：

```css
a,button,input,optgroup,select,textarea{
    -webkit-tap-highlight-color: rgba(0,0,0,0);
}
```

### webkit 表单输入框 placeholder 的颜色值改变：
如果想要默认的颜色显示红色，代码如下：

`input::-webkit-input-placeholder { color: red; }`

如果想要用户点击变为蓝色，代码如下：

`input:focus::-webkit-input-placeholder { color: blue; }`

### 移动端 iOS 手机下清除输入框内阴影
`input, textarea { -webkit-appearance: none; }`

### 在 iOS 中 禁止长按链接与图片弹出菜单
`a, img { -webkit-touch-callout: none; }`

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


### 保持在页面最底部
网页开发中，会遇到需要将一部分内容(比如版权信息)放在页面的最底部的需求。

这里的放在页面最底部，不是指用`position: fixed;`或`position: absolute;`来定位在某个固定的地方，而是指：在正常的页面布局中，将其放在页面的最底部；如果页面内容的总高度不足一个可视窗口的高度，仍旧能将其布局在可视窗口的底部；如果页面内容的总高度大于一个可视窗口的高度，那么其会布局在整个文档流的末尾，不滚动时会被遮住。

HTML 结构如下：

```html
<html>
<body>
  <main class="page-wrap">This is an article about CSS.</main>
  <footer class="footer">Copyright@ Harttle Land 2016</footer>
</body>
</html>
```

对应的 CSS 代码如下：

```css
* { margin: 0; }

/* .footer的每一级父元素都为100%高 */
html, body { height: 100%; }

.page-wrap {
  /* 页面内容至少撑满100%的屏幕 */
  min-height: 100%;
  /* 负边距大小即为页脚高度 */
  margin-bottom: -60px; 
}

/* 用来填充被页脚遮挡部分 */
.page-wrap:after {
  content: "";
  display: block;
}

/* 填充块和页脚一样高 */
.footer, .page-wrap:after {
  height: 60px; 
}
```


