> 转载说明：本文转载自 *博客园* 网站 *随他去吧* 发表的[《说说JSON和JSONP，也许你会豁然开朗》](http://www.cnblogs.com/dowinning/archive/2012/04/19/json-jsonp-jquery.html)。文章有个别修改。

## 0x00 前言

JavaScript 发展到今天，已经有了长足的进步。随着 Ajax 的大热，目前网站越来越多的开始使用这种无刷新的前后端数据交流方式了。

说到 AJAX 就会不可避免的面临两个问题：

* 第一个是 AJAX 以何种格式来交换数据？
* 第二个是跨域的需求如何解决？

第一个问题有多种解决方式，比如可以同自定义字符串、JSON 或者用 XML 来描述。而第二个问题，跨域也可以通过服务器端代理来解决，但是这种方法并没有使用 JSONP 来实现跨域方便。

在讲解 JSONP 之前，我们需要首先分清 JSON 和 JSONP 之间的差别：JSON(JavaScript Object Notation) 是一种数据交换格式；JSONP(JSON with Padding) 是一种依靠开发人员创造出来非官方跨域数据交互协议。打个比方：JSON 是地下党们用来书写和交换情报的暗号，而 JSONP 则是把用暗号书写的情报传递给自己同志时使用的接头方式。也就是说，一个是描述信息的格式，一个是传递双方约定的方法。

## 0x01 什么是 JSON

JSON 是一种基于文本的数据交换方式，或者叫做数据描述格式。具有如下优点：

1. 基于纯文本，跨平台传递极其简单；
2. JavaScript 原生支持，后台语言计划全部支持；
3. 轻量级数据格式，占用字符数量极少，特别适合互联网传递；
4. 可读性较强，虽然比不上XML那么一目了然，单在合理的依次缩进之后还是很容易识别的；
5. 容易编写和解析，当然，前提是你要知道数据结构。

JSON 当然也有缺点，但是目前可以不用在意。

**JSON 的格式/规则**

JSON 能够以非常简单的方式来描述数据，XML 能做的它都能做，因此在跨平台方面两者完全部分伯仲。

1. JSON 只有连中数据类型描述符：`大括号{}`和`方括号[]`，其余相关符号：`英文冒号:`是映射符，`英文逗号,`是分隔符，`英文双引号""`是定义符。

2. `大括号{}`用来描述一组“不同类型的无序键值对集合”(每个键值对可以理解为 OOP 的属性描述)；`方括号[]`用来描述一组“相同类型的有序数据集合”(可对应 OOP 的数组)。

3. 上面两种集合中若有多个子项，则通过`英文逗号,`进行分割。

4. 键值对以`英文冒号:`进行分割，并且建议键名都加上`英文双引号""`，以便于不同语言的解析。

5. JSON 内部常用数据类型就是字符串、数字、布尔值、日期、null 这几个。字符串必须用英文双引号引起来，其余的都不用。日期类型比较特殊，不详细讲述，只是建议如果客户端没有按日期排序功能需求的话，就把日期时间直接作为字符串传递就好，可以省去很多的麻烦。

**JSON 实例**

```javascript
// 描述一个人
var person = {
    "Name": "Bob",
    "Age": 32,
    "Company": "IBM",
    "Enginer": true
}
// 获取这个人的信息
var personAge = person.Age;

// 描述几个人
var members = [
    {
        "Name": "Bob",
        "Age": 32,
        "Company": "IBM",
        "Enginer": true
    },
    {
        "Name": "John",
        "Age": 20,
        "Company": "Oracle",
        "Enginer": false
    },
    {
        "Name": "Henry",
        "Age": 45,
        "Company": "Microsoft",
        "Enginer": false
    }
]

// 读取其中 Jhon 的公司的名称
var jhonCompany = members[1].Company;

// 描述一次会议
var conference = {
    "Conference": "Future Marketing",
    "Date": "2012-6-1",
    "Address":"Beijing",
    "Members":
        [
            {
                "Name": "Bob",
                "Age": 32,
                "Company": "IBM",
                "Enginer": true
            },
            {
                "Name": "John",
                "Age": 20,
                "Company": "Oracle",
                "Enginer": false
            },
            {
                "Name": "Henry",
                "Age": 45,
                "Company": "Microsoft",
                "Enginer": false
            }
        ]
}

// 读取参会者 Henry 是否是工程师
var henryIsAnEngineer = conference.Members[2].Engineer;
```

关于 JSON 更多的细节请在开发过程中查阅相关的资料深入学习。

## 0x02 什么是 JSONP

### 1、JSONP 是怎么产生的

JSONP 是为了解决跨域问题而产生的，其基本的产生思路如下：

1. 一个中所周知的问题，Ajax 直接请求普通文件存在跨域无权限访问的问题，不管是静态页面、动态网页、web 服务、WCF，只要是跨域请求，一律不准；

2. 不过我们又发现，Web 页面上调用 js 文件时则不受是否跨域的影响（不仅如此，我们还发现凡是拥有`src`这个属性的标签都拥有跨域的能力，比如`<script>`、`<img>`、`<iframe>`）；

3. 于是可以判断，当前阶段如果想通过纯 Web 端（ActiveX 控件、服务器端代理、属于 HTML5 之 Websocket 等方式不算）跨域访问数据就只有一种可能，那就是在远程服务器上设法把数据装进 js 格式的文件里，供客户端调用和进一步处理；

4. 恰恰我们已经知道有一种叫做 JSON 的纯字符数据格式可以见见的描述复杂数据，更妙的是 JSON 还被 js 原生支持，所以在客户端几乎可以随心所欲的处理这种格式的数据；

5. 这样子，解决方案就呼之欲出了，Web 客户端通过与调用脚本一模一样的方式来调用跨域服务器上动态生成的 js 格式文件（一般以 JSON 为后缀）。显而易见，服务器之所以要动态生成 JSON 文件，目的就在于把客户端需要的数据装入进去；

6. 客户端在对 JSON 文件调用成功之后，也就获得了自己所需要的数据，剩下的就是按照自己的需求进行处理和展现了。这种远程数据的的方式看起来非常像 Ajax，但其实并不一样；

7. 为了客户端使用数据，逐渐形成了一种非正式传输协议，人们把它称作 JSONP，该协议的一个要点就是允许用户传递一个`callback`参数给服务器，然后服务器端返回数据时会将这个`callback`参数作为函数名来包裹住 JSON 数据，这样客户端就可以随意定制自己的函数来自动处理返回数据了。

如果对于`callback`参数如果使用还有些模糊的话，我们后面会有具体的实例来讲解。

### 2、JSONP 客户端具体实现

不管 jQuery 也好，ExtJs 也罢，又或者是其他支持 JSONP 的框架，他们幕后所做的工作都是一样的，下面来循序渐进的说明一下 JSONP 在客户端的实现：

1、 我们知道，哪怕跨域 js 文件中的代码（当然指符合 Web 脚本安全策略的），Web 页面也是可以无条件执行的。

远程服务器 remoteserver.com 根目录下有个`remote.js`文件，代码如下：

```javascript
alert('我是远程文件');
```

本地服务器 localserver.com 下有个`jsonp.html`页面，代码如下：

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <script type="text/javascript" src="http://remoteserver.com/remote.js"></script>
</head>
<body>
</body>
</html>
```

毫无疑问，页面将会弹出一个提示窗体，显示跨域调用成功。

2、 现在我们在`jsonp.html`页面定义一个函数，然后在远程`remote.js`中传入数据进行调用。

jsonp.html 页面代码如下：

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <script type="text/javascript">
    var localHandler = function(data) {
        alert('我是本地函数，可以被跨域的remote.js文件调用，远程js带来的数据是：' + data.result);
    };
    </script>
    <script type="text/javascript" src="http://remoteserver.com/remote.js"></script>
</head>
<body>
</body>
</html>
```

`remote.js`文件代码如下：

```JavaScript
localHandler({"result": "我是远程js带来的数据"});
```

运行之后，查看结果，页面成功弹出提示窗口，显示本地函数被跨域的远程js调用成功，并且还收到了远程 js 带来的数据。很欣喜，跨域远程获取数据的目的基本实现了。但是又一个问题出现了，我怎么让远程 js 知道他们应该调用的本地函数叫什么名字呢？毕竟 JSONP 的服务器都需要面对着很多服务对象，而这些服务对象各自的本地函数都不相同啊。我们接着往下看。

3、 聪明的开发者很容易想到，只要服务器端提供的 js 脚本是动态生成的就行了呗。这样调用者可以传一个参数过去告诉服务器端“我想要一段调用 xx 函数的 js 代码，请你返回给我”于是服务器就可以按照客户端的需求来生成 js 脚本并相应了。

jsonp.html 页面的代码：

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <script type="text/javascript">
    // 得到航班信息查询结果后的回调函数
    var flightHandler = function(data) {
        alert('你查询的航班结果是：票价 ' + data.price + ' 元，' + '余票 ' + data.tickets + ' 张。');
    };

    // 提供 jsonp 服务的 url 地址（不管是什么类型的地址，最终生成的返回值都是一段 javascript 代码）
    var url = "http://flightQuery.com/jsonp/flightResult.aspx?code=CA1998&callback=flightHandler";
    // 创建 script 标签，设置其属性
    var script = document.createElement('script');
    script.setAttribute('src', url);
    // 把 script 标签加入 head，此时调用开始
    document.getElementsByTagName('head')[0].appendChild(script);
    </script>
</head>
<body>
</body>
</html>
```

这次代码的变化较大，不再直接把远程 js 文件写死，而是编码实现动态查询，而这也正是 JSONP 客户端实现的核心部分，本例中的重点也就在于如何完成 JSONP 调用的全过程。

我们看到调用的 url 中传递了一个 code 参数，告诉服务器我要查询的是 CA1998 次航班的信息；而 callback 参数则告诉服务器，我的本地回调函数叫做 flightHandler，所以请把查询结果传入这个函数中进行调用。

OK，服务器很聪明，这个叫做`flightResult.aspx`的页面生成了一段这样的代码提供给 jsonp.html（服务器端的实现这里就不演示了，与你选用的语言无关，说到底就是拼接字符串）：

```JavaScript
flightHandler ({
    "code": "CA1998",
    "price": 1780,
    "tickets": 5
});
```

我们看到，传递给 fightHandler 函数的是一个 JSON 数据，它描述了航班的基本信息。

运行一下页面，成功弹出提示窗口，JSONP 的执行全过程顺利完成！

4、 到这里为止的话，JSONP 客户端的实现原理基本都已经讲清楚了。剩下的就是如何把代码封装一下，以便与用户界面交互，从而实现多次和重复调用。

> 需要注意的是，如果需要经常的通过 JSONP 在一个页面上获取不同的数据，那么可能会导致 script 标签增加的非常多，这时就需要在加载运行完成之后，将 script 标签清除掉。可以参考下这里的一个实现 [百度搜索框下拉效果](https://github.com/hanzichi/hanzichi.github.io/blob/master/demo/searchList/index.h5.html) 的源码。

下面再给一段 jQuery 使用 JSONP 的代码（已然沿用上面的那个航班查询的例子）：

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Untitled Page</title>
    <script type="text/javascript" src="jquery.min.js"></script>
    <script type="text/javascript">
    jQuery(document).ready(function(){
        $.ajax({
            type: "get",
            async: false,
            url: "http://flightQuery.com/jsonp/flightResult.aspx?code=CA1998",
            dataType: "jsonp",
            // 传递给请求处理程序或页面的，用以获得 jsonp 回调函数名的参数名(一般默认为 callback)
            jsonp: "callback",  
            // 自定义的 jsonp 回调函数名称，默认为 jQuery 自动生成的随机函数名，也可以写"?"，jQuery 会自动为你处理数据
            jsonpCallback:"flightHandler",  
            success: function(json){
                alert('您查询到航班信息：票价： ' + json.price + ' 元，余票： ' + json.tickets + ' 张。');
            },
            error: function(){
                alert('fail');
            }
         });
    });
    </script>
</head>
<body></body>
</html>
```
是不是有点奇怪？为什么这次没有写 flightHandler 这个函数呢？而且竟然也能运行成功！这就是jQuery 的功劳了。jQuery 在处理 JSONP 类型的 Ajax 时（吐槽：虽然 jQuery 也把 JSONP 归入了 Ajax，但其实他们真的不是一回事儿），自动帮你生成回调函数并把数据取出来供 success 属性方法来调用了。

## 0x03 补充说明

### 1、Ajax 和 JSONP 的异同

Ajax 和 JSONP 这两种技术在调用方式上“看起来”很像，目的也一样，都是请求一个 url，然后把范玮琪返回的数据进行处理，因此 jQuery 和 Ext 等框架都把 JSONP 作为 Ajax 的一种形式进行了封装。

但是 Ajax 和 JSONP 其实本质上是不同的东西。Ajax 的核心是通过 XMLHttpRequest 获取非本页的内容，而 JSONP 的核心则是动态添加 script 标签来调用服务器提供的 js 脚本。

所以说，其实 Ajax 与 JSONP 的区别不在于是否跨域，Ajax 通过服务器端代理一样可以实现跨域，JSONP 本身也不排斥同于的数据的获取。

还有就是，JSONP 是一种方式或者说非强制性协议，如同 Ajax 一样，它也不替丁非要用 JSON 格式来传递数据，如果你愿意，字符串都行，只不过这样不利于 JSONP 提供公开服务。

总而言之，JSONP 不是 Ajax 的一个特例，哪怕 jQuery 等框架把 JSONP 封装进了 Ajax，也不能改变这一点！

### 2、同源策略限制（跨域）

同源策略阻止从一个域上加载的脚本获取或操作另一个域上的文档属性。也就是说，受到请求的 url 的域必须与当前 Web 页面的域相同。这意味着浏览器隔离来自不同源的内容，以防止它们之间的操作。这个浏览器策略很老，从 Netscape Navigator 2.0 版本开始就存在。

克服该限制的一个相对简单的方法是让 Web 页面向它源自的 Web 服务器请求数据，并且让 Web 服务器像代理一样将请求转发给真正的第三方服务器。尽管该技术获得了普遍使用，但它是不可伸缩的。另一种方式是使用框架要素在当前 Web 页面中创建新区域，并且使用 GET 请求获取任何第三方资源。不过，获取资源后，框架中的内容会受到同源策略的限制。

克服该限制更理想方法是在 web 页面中插入动态脚本元素，该页面源指向其他域中的服务 url 并且在自身脚本中获取数据，脚本加载时它开始执行。该方法是可行的，因为同源策略不阻止动态脚本插入，并且将脚本看作是从提供 Web 页面的域上加载的。但如果该脚本尝试从另一个域上加载文档，就不会成功。幸运的是，通过添加 JSON 可以改进该技术。


