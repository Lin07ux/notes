在 commonJS 的模块化写法中，经常会遇到`exports`和`module.exports`。这两者的关系经常难以区分。下面就简单梳理下。

![exports 与 module.exports 的关系](http://cnd.qiniu.lin07ux.cn/markdown/1479006903041.png)

当一个 js 文件需要模块化的时候，node 环境中会给文件注入`exports`和`module.exports`这两个变量，并且刚开始的时候这两个变量是指向同一个空对象的，随后你就可以拿着这两个变量做了一系列操作，都会修改同一个对象。关键处在于：**只有`module.exports`会被返回**以便后续其他模块 require 引用使用。

既然这两者指向的都是同一个对象，而且最终只有`module.exports`会被返回，那么为什么还要注入`exports`变量呢？其实只是因为懒：每次给模板添加一个新的属性的时候都要写`module.exports`太麻烦了，而写`exports`则简洁很多。

那这样的话，就都用`exports`而不用`module.exports`不就好了吗？这就需要小心了，不然很容易出错：

```JavaScript
exports = function A() {}
```

你想导出一个类，写成这个样子，想想上面那张图上的话，返回的可是`module.exports`，这里的赋值把`exports`和`module.exports`的关系打断了，所以你引用的时候会得到什么呢？没错，空的对象。

所以，有一下的一些使用技巧：

1. 如果要导出的属性可以直接在空对象上进行扩展的时候，直接使用`exports`就行了，省时省力；
2. 如果要导出的模块需要完全覆盖原本的空对象的时候，就需要使用`module.exports`；
3. 如果有不确定的地方，使用`module.exports`就行了。


