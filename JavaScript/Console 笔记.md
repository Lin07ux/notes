现代浏览器，如 Chrome、FireFox、Safari 等都提供了一个调试用工具方法对象`console`。


## 输出信息
console 有多个方法可以能在控制台中输出不同的信息。

![console 几种输出效果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470923380548.png)


### log() 方法
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


## 分组
可以使用`group()`开始一个分组输出，用`groupEnd()`结束最近打开的一个分组。

```javascript
console.group("第一组信息");
console.log("第一组第一条: Google");
console.log("第一组第二条: 百度");
console.groupEnd();

console.group("第二组信息");
console.log("第二组第一条: Lin07ux");
console.log("第二组第二条: anran");
console.groupEnd();
```

![输出效果](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470929042022.png)


## dir() 查看对象信息
`console.dir()`可以显示一个对象所有的属性和方法。

```javascript
var info = {
    baidu: 'http://www.baidu.com',
    google: 'http://www.google.com',
    message: "程序爱好者欢迎你的加入"
};

console.dir(info);
```

![效果图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470929214090.png)


## dirxml() 显示某个 DOM 节点的内容
`console.dirxml()`用来显示网页的某个节点(node)所包含的 html/xml 代码。

```html
<div id="info">
    <h3>搜索引擎</h3>
    <p>Google、百度，和其他</p>
</div>

<script type="text/javascript">
    var info = document.getElementById('info');
    console.dirxml(info);
</script>
```

![效果图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470929648198.png)


## assert() 断言
`console.assert()`用来判断一个表达式或变量是否为真。如果结果为否，则在控制台输出一条相应信息，并且抛出一个异常。如果结果是真，则不会有输出。

```javascript
var result = 1;
console.assert( result );

var year = 2014;
console.assert(year == 2018 );
```

![效果图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470929795902.png)


## trace() 追踪函数的调用
`console.trace()`用来追踪函数的调用轨迹。需要追踪哪个函数，就在那个函数中添加一句`console.trace()`方法的调用即可。

```javascript
function add(a,b){
    console.trace();
    return a+b;
}
var x = add3(1,1);
function add3(a,b){return add2(a,b);}
function add2(a,b){return add1(a,b);}
function add1(a,b){return add(a,b);}
```

![效果图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470929994180.png)


## time() 计时功能
`console.time()`和`console.timeEnd()`，用来显示代码的运行时间。在开始计时的地方调用`console.time()`，在结束计时的时候，调用`console.timeEnd()`。

```javascript
console.time("控制台计时器一");
for(var i=0;i<1000;i++){
    for(var j=0;j<1000;j++){}
}
console.timeEnd("控制台计时器一");
```

![效果图](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1470930119046.png)


## profile() 性能分析
`console.profile()`可以分析程序各个部分的运行时间，找出瓶颈所在。

```javascript
function All(){
    alert(11);
    for(var i=0;i<10;i++){
        funcA(1000);
    }
    funcB(10000);
}

function funcA(count){
　　for(var i=0;i<count;i++){}
}

function funcB(count){
    for(var i=0;i<count;i++){}
}

console.profile('性能分析器');
All();
console.profileEnd();
```


## 参考
1. [让console充满情怀](https://aotu.io/notes/2016/06/22/An-interesting-experience-on-console/)
2. [Console API Reference - Google Chrome](https://developer.chrome.com/devtools/docs/console-api)


