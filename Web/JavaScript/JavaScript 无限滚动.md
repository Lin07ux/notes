页面的无限滚动：当页面滚动到某个位置时就自动加载数据。

### 基础原理
无限滚动其实就是监听 window 对象的`scroll`事件，然后触发获取数据的函数，完成加载数据和显示数据的动作。

下面是一个最简单的例子：

```javascript
function fetchData() {
  fetch(path).then(res => doSomeThing(res.data));
}

window.addEventListener('scroll', fetchData);
```

上面的例子中还有很多问题，其中最大的问题就是：获取数据的函数(以后叫`fetch`函数)没有限制触发条件，只要页面一滚动，就会立即触发。


### 限制触发条件
一般情况下，fetch 函数的触发条件有哪些呢 ？

* 在 fetch 过程中不能重复触发
* 没有更多数据的时候不能再触发
* 屏幕距离容器边缘 xxx 的时候触发

前两点很好处理，只要加个`isLoading`和`isEnd`的变量就可以了。

添加这两个变量之后，我们的代码就变成下面的样子啦：

```javascript
var isLoading = false;
var isEnd = false;

function fetchData() {

  if ( !isLoading && !isEnd ) {

    isLoading = true;

    fetch(path).then(res => {
      isLoading = false;
      res.data.length === 0 && isEnd = true;
      doSomething(res.data);
    });

  }

}
window.addEventListener('scroll', fetchData);
```

第三点就需要结合实际的 DOM 知识来解决了。

### 计算屏幕与容器边缘的距离
我们以计算屏幕底部与容器底部边缘为例：

* 首先，我们需要获得浏览器可视窗口的高度：`window.innerHeight`；
* 然后，还要获得元素的边缘与可视窗口的距离：`Element.getBoundingClientRect`。

`Element.getBoundingClientRect`会得到这么一个类 Object 对象：

```javascript
ClientRect {
  width: 760,   // 元素宽度
  height: 2500, // 元素高度
  top: -1352,   // 元素上边缘与屏幕上边缘的距离
  bottom: 1239, // 元素下边缘与屏幕上边缘的距离
  left: 760,    // 元素左边缘与屏幕左边缘的距离
  right: 860    // 元素右边缘与屏幕左边缘的距离
}
```

如下图所示：

![getBoundingClientRect](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1472563795989.png)

通过上面这两个 API，就可以计算出元素底部边缘与屏幕底部边缘的位置了。修改代码如下：

```javascript
var isLoading = false;
var isEnd = false;
var triggerDistance = 200;

function fetchData() {

  var distance = container.getBoundingClientRect().bottom - window.innerHeight;
  if ( !isLoading && !isEnd && distance < triggerDistance ) {

    isLoading = true;

    fetch(path).then(res => {
      isLoading = false;
      res.data.length === 0 && isEnd = true;
      doSomething(res.data);
    });

  }

}
window.addEventListener('scroll', fetchData);
```

修改之后，当容器底部与屏幕底部距离小于 200 的时候，才会触发 fetch 函数，这样我们的无限滚动就更加实用啦！

当然，并不是只有 window 才可以滚动，拥有高度的级块元素只要设置了`overflow: scroll`都是可以滚动的。我们可以为其绑定滚动事件监听即可：

```javascript
document.getElementById('container').addEventListener('scroll', fetchData);
```

### 转摘
[如何实现无限滚动](http://scarletsky.github.io/2016/04/20/how-to-implement-infinite-scroll/)

