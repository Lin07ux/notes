现代浏览器，如 Chrome、FireFox、Safari 等都提供了一个调试用工具方法对象`console`。这个对象中有多个方法可以使用，能在控制台中输入不同的信息。

## log() 方法
最常用的就是`console.log()`方法了。这个方法会在控制台中输入指定的变量的值。

```js
console.log(variable [, variable, …],)
```

可以接收多个参数。如果每个参数都是一般的变量，那就会简单的在控制台输入这些变量的值。变量可以是任意类型的值，如：字符串，数值、数组、函数、对象等。

如果第一个变量是一个含有特殊字符(格式占位符)的字符串，那么就可以自定义一些输出效果。

目前有如下的一些格式占位符可以使用：

* `%s`  字符串
* `%d`或`%i` 正数
* `%f`  浮点数
* `%o` 可展开的 DOM
* `%O` 列出 DOM 的属性
* `%c` 根据提供的 CSS 样式格式化字符串

这些格式占位符的浏览器支持程度并不相同，`%o`和`%O`可能在某些浏览器中并不通用。而且这两者在不同的浏览器中的作用可能也不相同：对于普通对象这两者表现相同，都是输出对象及其属性；在 FireFox 中，这两者都是输入可展开的 DOM 元素，在 Chrome 中则分别对应其作用，而在 IE 中则不支持这两个格式占位符。下图分别展示其输出普通对象和 DOM 元素的效果：

![%o和%O输出普通对象](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467123474850.png)

![%o和%O输出DOM元素](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467123509660.png)

使用`%c`占位符时，对应的后面的参数必须是 CSS 语句，用来对输出内容进行 CSS 渲染。于是，利用`%c`配合 CSS 可以做出吊炸天的效果，比如背景色、字体颜色渐变、字体 3D 效果、图片等，甚至颜文字、emoji。如下图：

![%c效果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467123779641.png)

需要注意的是：

* console 不能定义 img，因此需要用背景图片代替。
* console 不支持`width`和`height`，利用空格和`font-size`代替；还可以使用`padding`和`line-height`代替宽高。
* chrome 不支持背景图！原因是[`ConsoleViewMessage.js`](https://src.chromium.org/viewvc/blink/trunk/Source/devtools/front_end/console/ConsoleViewMessage.js?pathrev=197345#l797)源码把 url 和谐掉了。不过可以下载 firebug 插件查看~ gif图片也是支持的~~~
* console 是默认换行的。

如果要输出字符画，其实就是输出一串字符串，只是对于需要换行的地方，使用`\n`来代替。如下图：

![%s字符画](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467123875031.png)

推荐三个 ASCII 字符画制作工具：

* 在线工具[picascii](http://picascii.com/)
* 在线工具[img2txt](http://www.degraeve.com/img2txt.php)
* ASCII Generator 功能比较齐全，不过需要下载使用~ 下载参考地址：[ASCII Generator Portable(将图片转为字符画) v2.0下载](http://pan.baidu.com/share/link?shareid=3161588673&uk=3509597415)

## 参考
1. [让console充满情怀](https://aotu.io/notes/2016/06/22/An-interesting-experience-on-console/)
2. [Console API Reference - Google Chrome](https://developer.chrome.com/devtools/docs/console-api)


