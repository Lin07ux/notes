> 转摘：[css实现水平垂直居中的几种方式](http://www.cnblogs.com/formercoding/p/12826126.html)

下面的水平垂直居中方式都是利用如下的 HTML：

```html
<div class="box">
  <div class="child"></div>
</div>
```

### 1. flex 布局

利用 flex 布局的`align-items: center;`垂直居中，`justify-content: center;`水平居中。

```css
.box {
  width: 100vw;
  height: 500px;
  background: skyblue;

  display: flex;
  align-items: center;
  justify-content: center;
}

.child {
  width: 200px;
  height: 200px;
  background-color: deepskyblue;
}
```

### 2. 绝对定位和 margin

在使用相对定位的父容器下，子元素设置为绝对定位，并将上下左右都设置为 0，再设置`margin: auto;`即可实现居中。

```css
.box {
  width: 100vw;
  height: 500px;
  background: skyblue;

  position: relative;
}

.child {
  width: 200px;
  height: 200px;
  background-color: deepskyblue;

  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  margin: auto;
}
```

### 3. 绝对定位和 transform

子元素绝对定位后，设置`top`和`left`偏移父容器的 50%，再利用`transform: translate(-50%, -50%)`平移回补自身宽高的 50% 即可。

```css
.box {
  width: 100vw;
  height: 500px;
  background: skyblue;

  position: relative;
}

.child {
  width: 200px;
  height: 200px;
  background-color: deepskyblue;

  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
```

### 4. 利用 text-align 和 vertical-align

父元素使用`text-align: center`实现行内元素的水平居中，再利用`vertical-align: middle`实现行内元素的垂直居中。这样做的前提是要先加上伪元素并给设置高度为 100%（ElementUI 的消息弹窗居中实现方式就是如此）。

```css
.box {
  width: 100vw;
  height: 500px;
  background: skyblue;

  text-align: center;
}

.box:after {
  content: "";
  display: inline-block;
  height: 100%;
  width: 0;
  vertical-align: middle;
}

.child {
  display: inline-block;
  vertical-align: middle;
}
```

