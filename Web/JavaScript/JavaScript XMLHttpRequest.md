转摘：[你真的会使用XMLHttpRequest吗？](https://segmentfault.com/a/1190000004322487)

## Ajax 和 XMLHttpRequest
我们通常将 Ajax 等同于 XMLHttpRequest，但细究起来，它们是属于两个不同维度的概念。

以下是我认为对Ajax较为准确的解释：（摘自[what is Ajax](http://www.tutorialspoint.com/ajax/what_is_ajax.htm)）

> AJAX stands for Asynchronous JavaScript and XML. AJAX is a new technique for creating better, faster, and more interactive web applications with the help of XML, HTML, CSS, and Java Script.
>
> AJAX is based on the following open standards:
> 
> * Browser-based presentation using HTML and Cascading Style Sheets (CSS).
> * Data is stored in XML format and fetched from the server.
> * Behind-the-scenes data fetches using XMLHttpRequest objects in the browser.
> * JavaScript to make everything happen.

从上面的解释中可以知道：**Ajax 是一种技术方案，但并不是一种新技术**。它依赖的是现有的 CSS/HTML/Javascript，而其中最核心的依赖是浏览器提供的`XMLHttpRequest`对象，是这个对象使得我们可以通过 JavaScript 让浏览器发出 HTTP 请求与接收 HTTP 响应。

所以我用一句话来总结两者的关系：我们使用`XMLHttpRequest`对象来发送一个`Ajax`请求。


## XMLHttpRequest 的发展历程
XMLHttpRequest 一开始只是微软浏览器提供的一个接口，后来各大浏览器纷纷效仿也提供了这个接口，再后来 W3C 对它进行了标准化，提出了 XMLHttpRequest 标准。XMLHttpRequest 标准又分为 Level 1和 Level 2。

XMLHttpRequest Level 1 主要存在以下缺点：

* 受同源策略的限制，不能发送跨域请求；
* 不能发送二进制文件（如图片、视频、音频等），只能发送纯文本数据；
* 在发送和获取数据的过程中，无法实时获取进度信息，只能判断是否完成；

Level 2 对 Level 1 进行了改进，XMLHttpRequest Level 2 中新增了以下功能：

* 在服务端允许的情况下，可以发送跨域请求；
* 支持发送和接收二进制数据；
* 新增 FormData 对象，支持发送表单数据；
* 发送和获取数据时，可以获取进度信息；
* 可以设置请求的超时时间；

当然更详细的对比介绍，可以参考[阮老师的这篇文章](http://www.ruanyifeng.com/blog/2012/09/xmlhttprequest_level_2.html)，文章中对新增的功能都有具体代码示例。


## XMLHttpRequest 兼容性
关于 xhr 的浏览器兼容性，大家可以直接查看“Can I use”这个网站提供的结果 [XMLHttpRequest 兼容性](http://caniuse.com/#search=XMLHttpRequest)，下面提供一个截图：

![XMLHttpRequest 兼容性](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1469078921728.png)

从图中可以看到：

* IE8/IE9、Opera Mini 完全不支持 xhr 对象
* IE10/IE11 部分支持，不支持`xhr.responseType`为`json`
* 部分浏览器不支持设置请求超时，即无法使用`xhr.timeout`
* 部分浏览器不支持`xhr.responseType`为`blob`


## 细说 XMLHttpRequest 如何使用
先来看一段使用 XMLHttpRequest Level 2 发送 Ajax 请求的简单示例代码。

```javascript
function sendAjax() {
    // 构造表单数据
    var formData = new FormData();
    formData.append('username', 'johndoe');
    formData.append('id', 123456);
    
    // 创建 xhr 对象
    var xhr = new XMLHttpRequest();
    // 设置 xhr 请求的超时时间
    xhr.timeout = 3000;
    // 设置响应返回的数据格式
    xhr.responseType = 'text';
    // 创建一个 post 请求，采用异步
    xhr.open('post', '/server', true);
    // 注册相关事件回调处理函数
    xhr.onload = function (e) {
        if (this.status == 200 | this.status == 304) {
            alert(this.responseText);
        }
    };
    xhr.ontimeout = function (e) { ... };
    xhr.onerror   = function (e) { ... };
    xhr.upload.onprogress = function (e) { ... };
    
    // 发送数据
    xhr.send(formData);
}
```

上面是一个使用 xhr 发送表单数据的示例，整个流程可以参考注释。

接下来我将站在使用者的角度，以问题的形式介绍 xhr 的基本使用。

> 我对每一个问题涉及到的知识点都会进行比较细致地介绍，有些知识点可能是你平时忽略关注的。


### 如何设置 request header
在发送 Ajax 请求（实质是一个 HTTP 请求）时，我们可能需要设置一些请求头部信息，比如content-type、connection、cookie、accept-xxx 等。xhr 提供了`setRequestHeader`来允许我们修改请求 header。

> 定义：`void setRequestHeader(DOMString header, DOMString value);`

注意点：

* 方法的第一个参数 header 大小写不敏感，即可以写成`content-type`，也可以写成`Content-Type`，甚至写成`content-Type`;
* Content-Type 的默认值与具体发送的数据类型有关，请参考本文【可以发送什么类型的数据】一节；
* `setRequestHeader`必须在`open()`方法之后，`send()`方法之前调用，否则会抛错；
* `setRequestHeader`可以调用多次，最终的值不会采用覆盖 override 的方式，而是采用追加 append 的方式。

下面是一个示例代码：

```javascript
var client = new XMLHttpRequest();

client.open('get', '/demo.php');
client.setRequestHeader('X-Test', 'one');
client.setRequestHeader('X-Test', 'two');
client.send();

// 最终的 request header 中的 X-Test 为：one, two
```

### 如何获取 response header
xhr 提供了2个用来获取响应头部的方法：`getAllResponseHeaders`和`getResponseHeader`。前者是获取 response 中的所有 header 字段，后者只是获取某个指定 header 字段的值。另外，`getResponseHeader(header)`的 header 参数不区分大小写。

> 定义：
> `DOMString getAllResponseHeaders()`
> `DOMString getResponseHeader(DOMString header)`

这2个方法看起来简单，但却处处是坑儿：

1. 使用`getAllResponseHeaders()`看到的所有 response header 与实际在控制台 Network 中看到的 response header 不一样。
2. 使用`getResponseHeader()`获取某个 header 的值时，浏览器抛错`Refused to get unsafe header "XXX"`。

经过一番寻找最终在 [Stack Overflow](http://stackoverflow.com/questions/7462968/restrictions-of-xmlhttprequests-getresponseheader) 找到了答案。

* 原因1：[W3C 的 xhr 标准中做了限制](https://www.w3.org/TR/XMLHttpRequest/)，规定客户端无法获取 response 中的`Set-Cookie`、`Set-Cookie2`这2个字段，无论是同域还是跨域请求；
* 原因2：[W3C 的 cors 标准对于跨域请求也做了限制](https://www.w3.org/TR/cors/#access-control-allow-credentials-response-header)，规定对于跨域请求，客户端允许获取的 response header 字段只限于“simple response header”和“Access-Control-Expose-Headers”（两个名词的解释见下方）。

> "simple response header"包括的 header 字段有：`Cache-Control`, `Content-Language`, `Content-Type`, `Expires`, `Last-Modified`, `Pragma`;
> "Access-Control-Expose-Headers"：首先得注意是"Access-Control-Expose-Headers"进行跨域请求时响应头部中的一个字段，对于同域请求，响应头部是没有这个字段的。这个字段中列举的 header 字段就是服务器允许暴露给客户端访问的字段。

所以`getAllResponseHeaders()`只能拿到**限制以外**（即被视为safe）的 header 字段，而不是全部字段；而调用`getResponseHeader(header)`方法时，header 参数必须是**限制以外**的 header 字段，否则调用就会报`Refused to get unsafe header`的错误。


### 如何指定 xhr.response 的数据类型
有些时候我们希望`xhr.response`返回的就是我们想要的数据类型。比如：响应返回的数据是纯 JSON 字符串，但我们期望最终通过`xhr.response`拿到的直接就是一个 js 对象，我们该怎么实现呢？

有2种方法可以实现，一个是 level 1 就提供的`overrideMimeType()`方法，另一个是 level 2 才提供的`xhr.responseType`属性。

**1. xhr.overrideMimeType()**

`overrideMimeType`是 xhr level 1 就有的方法，所以浏览器兼容性良好。这个方法的作用就是用来重写 response 的 content-type，这样做有什么意义呢？比如：server 端给客户端返回了一份 document 或者是 xml 文档，我们希望最终通过`xhr.response`拿到的就是一个 DOM 对象，那么就可以用`xhr.overrideMimeType('text/xml; charset = utf-8')`来实现。

再举一个使用场景，我们都知道 xhr level 1 不支持直接传输 blob 二进制数据，那如果真要传输 blob 该怎么办呢？当时就是利用`overrideMimeType`方法来解决这个问题的。

下面是一个获取图片文件的代码示例：

```javascript
var xhr = new XMLHttpRequest();
// 向 server 端获取一张图片
xhr.open('GET', '/path/to/image.png', true);

// 这行是关键！
// 将响应数据按照纯文本格式来解析，字符集替换为用户自己定义的字符集
xhr.overrideMimeType('text/plain; charset=x-user-defined');

xhr.onreadystatechange = function(e) {
  if (this.readyState == 4 && this.status == 200) {
    // 通过 responseText 来获取图片文件对应的二进制字符串
    var binStr = this.responseText;
    // 然后自己再想方法将逐个字节还原为二进制数据
    for (var i = 0, len = binStr.length; i < len; ++i) {
      var c = binStr.charCodeAt(i);
      // String.fromCharCode(c & 0xff);
      // 在每个字符的两个字节之中，只保留后一个字节，将前一个字节扔掉。
      // 原因是浏览器解读字符的时候，
      // 会把字符自动解读成 Unicode 的 0xF700-0xF7ff 区段。
      var byte = c & 0xff; 
    }
  }
};

xhr.send();
```

代码示例中 xhr 请求的是一张图片，通过将 response 的 content-type 改为`'text/plain; charset=x-user-defined'`，使得 xhr 以纯文本格式来解析接收到的 blob 数据，最终用户通过`this.responseText`拿到的就是图片文件对应的二进制字符串，最后再将其转换为 blob 数据。

**2. xhr.responseType**

`responseType`是 xhr level 2 新增的属性，用来指定`xhr.response`的数据类型，目前还存在些兼容性问题，可以参考本文的【XMLHttpRequest的兼容性】这一小节。`responseType`可以设置为如下的值：

|      值       |  xhr.response 数据类型  |             说明              |
|---------------|------------------------|------------------------------|
| `""`	       |  String 字符串           | 默认值(在不设置 responseType 时)|
| `text`	       |  String 字符串           |                               |
| `document`    |	 Document 对象           | 希望返回 XML 格式数据时使用      |
| `json`      	  | javascript 对象         | 存在兼容性问题，IE10/IE11 不支持 |
| `blob`        |  Blob 对象              |                               |
| `arrayBuffer` |  ArrayBuffer 对象       |                               |

下面是同样是获取一张图片的代码示例，相比`xhr.overrideMimeType`，用`xhr.responseType`来实现简单得多。

```javascript
var xhr = new XMLHttpRequest();

xhr.open('get', '/path/to/image.png', true);
// 可以将`xhr.responseType`设置为`"blob"`也可以设置为`" arrayBuffer"`
// xhr.responseType = 'arrayBuffer';
xhr.responseType = 'bolb';
xhr.onload = function (e) {
    if (this.status == 200) {
        var blob = this.response;
        ...
    }
};
xhr.send();
```

**3. 小结**

虽然在 xhr level 2 中，这两者是共同存在的。但其实不难发现，`xhr.responseType`就是用来取代`xhr.overrideMimeType()`的，`xhr.responseType`功能强大的多，`xhr.overrideMimeType()`能做到的`xhr.responseType`都能做到。所以我们现在完全可以摒弃使用`xhr.overrideMimeType()`了。

### 如何获取 response 数据
xhr 提供了3个属性来获取请求返回的数据，分别是：`xhr.response`、`xhr.responseText`、`xhr.responseXML`。

- `xhr.response`
    * 默认值：空字符串""
    * 当请求完成时，此属性才有正确的值
    * 请求未完成时，此属性的值可能是""或者 null，具体与`xhr.responseType`有关：当`responseType`为""或"text"时，值为""；`responseType`为其他值时，值为 null
- `xhr.responseText`
    * 默认值为空字符串""
    * 只有当`responseType`为"text"、""时，xhr 对象上才有此属性，此时才能调用`xhr.responseText`，否则抛错
    * 只有当请求成功时，才能拿到正确值。请求未完成、请求失败情况下值都为空字符串""：
- `xhr.responseXML`
    * 默认值为 null
    * 只有当`responseType`为"text"、""、"document"时，xhr 对象上才有此属性，此时才能调用`xhr.responseXML`，否则抛错
    * 只有当请求成功且返回数据被正确解析时，才能拿到正确值。以下3种情况下值都为 null：请求未完成、请求失败、请求成功但返回数据无法被正确解析时

### 如何追踪 ajax 请求的当前状态
在发一个ajax请求后，如果想追踪请求当前处于哪种状态，该怎么做呢？

用`xhr.readyState`这个属性即可追踪到。这个属性是只读属性，总共有 5 种可能值，分别对应 xhr 不同的不同阶段。每次`xhr.readyState`的值发生变化时，都会触发`xhr.onreadystatechange`事件，我们可以在这个事件中进行相关状态判断。

```javascript
xhr.onreadystatechange = function (e) {
    switch (this.readyState) {
        case 1:  // OPENED
            // do something
            break;
        case 2:  // HEADERS_RECEIVED
            // do something
            break;
        case 3:  // LOADING
            // do something
            break;
        case 4:  // DONE
            // do something
            break;
    }
}
```

每个状态值的说明如下表：

|  值	 |  状态            |  描述                                        |
|-----|------------------|-------------------------------------|
|  0  | UNSENT           | (初始状态，未打开) 此时 xhr 对象被成功构造，`open()`方法还未被调用 |
|  1  | OPENED           | (已打开，未发送)`open()`方法已被成功调用，`send()`方法还未被调用。<br>注意：只有 xhr 处于`OPENED`状态，才能调用`xhr.setRequestHeader()`和`xhr.send()`，否则会报错 |
|  2  | HEADERS_RECEIVED | (已获取响应头)`send()`方法已经被调用, 响应头和响应状态已经返回 |
|  3  | LOADING          |(正在下载响应体)响应体(response entity body)正在下载中，此状态下通过`xhr.response`可能已经有了响应数据，但是不完整 |
|  4  | DONE             | (整个数据传输过程结束)整个数据传输过程结束，不管本次请求是成功还是失败 |


### 如何设置请求的超时时间
如果请求过了很久还没有成功，为了不会白白占用的网络资源，我们一般会主动终止请求。XMLHttpRequest 提供了`timeout`属性来允许设置请求的超时时间。

单位：milliseconds 毫秒
默认值：0，即不设置超时

很多同学都知道：从请求开始 算起，若超过 timeout 时间请求还没有结束（包括成功/失败），则会触发ontimeout事件，主动结束该请求。

*【那么到底什么时候才算是请求开始？】*
—— `xhr.onloadstart`事件触发的时候，也就是你调用`xhr.send()`方法的时候。
因为`xhr.open()`只是创建了一个连接，但并没有真正开始数据的传输，而`xhr.send()`才是真正开始了数据的传输过程。只有调用了`xhr.send()`，才会触发`xhr.onloadstart`。

*【那么什么时候才算是请求结束？】*
—— `xhr.loadend`事件触发的时候。

另外，还有2个需要注意的坑儿：

* 可以在`send()`之后再设置此`xhr.timeout`，但计时起始点仍为调用`xhr.send()`方法的时刻。
* 当 xhr 为一个 sync 同步请求时，`xhr.timeout`必须置为 0，否则会抛错。原因可以参考本文的【如何发一个同步请求】一节。

### 如何发一个同步请求
xhr 默认发的是异步请求，但也支持发同步请求（当然实际开发中应该尽量避免使用）。到底是异步还是同步请求，由`xhr.open()`传入的`async`参数决定。

`xhr.open()`方法语法如下：

`open(method, url [, async = true [, username = null [, password = null]]])`

参数如下：

* `method`：请求的方式，如`GET/POST/HEADER`等，这个参数不区分大小写
* `url`：请求的地址，可以是相对地址如`example.php`，这个相对是相对于当前网页的 url 路径；也可以是绝对地址如`http://www.example.com/example.php`
* `async`：默认值为 true，即为异步请求。若`async=false`，则为同步请求

在我认真研读 W3C 的 xhr 标准前，我总以为同步请求和异步请求只是阻塞和非阻塞的区别，其他什么事件触发、参数设置应该是一样的，事实证明我错了。

W3C 的 xhr 标准中关于`open()`方法有这样一段说明：

> Throws an "InvalidAccessError" exception if async is false, the JavaScript global environment is a document environment, and either the timeout attribute is not zero, the withCredentials attribute is true, or the responseType attribute is not the empty string.

从上面一段说明可以知道，当xhr为同步请求时，有如下限制：

* `xhr.timeout`必须为0
* `xhr.withCredentials`必须为 false
* `xhr.responseType`必须为""（注意置为"text"也不允许）

若上面任何一个限制不满足，都会抛错。而对于异步请求，则没有这些参数设置上的限制。

之前说过页面中应该尽量避免使用sync同步请求，为什么呢？因为我们无法设置请求超时时间（`xhr.timeout`为0，即不限时）。在不限制超时的情况下，有可能同步请求一直处于`pending`状态，服务端迟迟不返回响应，这样整个页面就会一直阻塞，无法响应用户的其他交互。

另外，标准中并没有提及同步请求时事件触发的限制，但实际开发中我确实遇到过部分应该触发的事件并没有触发的现象。如在 chrome中，当 xhr 为同步请求时，在`xhr.readyState`由2变成3时，并不会触发`onreadystatechange`事件，`xhr.upload.onprogress`和`xhr.onprogress`事件也不会触发。

### 如何获取上传、下载的进度
在上传或者下载比较大的文件时，实时显示当前的上传、下载进度是很普遍的产品需求。

我们可以通过`onprogress`事件来实时显示进度，默认情况下这个事件每 50ms 触发一次。需要注意的是，上传过程和下载过程触发的是不同对象的`onprogress`事件：

* 上传触发的是`xhr.upload`对象的`onprogress`事件
* 下载触发的是 xhr 对象的`onprogress`事件

```javascript
xhr.onprogress = updateProgress;
xhr.upload.onprogress = updateProgress;
function updateProgress(event) {
    if (event.lengthComputable) {
      var completedPercent = event.loaded / event.total;
    }
 }
```

### 可以发送什么类型的数据
调用`xhr.send()`方法的时候，可以传入一些数据，表示通过这个请求发送到服务器端。

语法：`void send(data)`

`xhr.send(data)`的参数`data`可以是以下几种类型：

* `ArrayBuffer`
* `Blob`
* `Document`
* `DOMString`
* `FormData`
* `null`

如果是`GET/HEAD`请求，`send()`方法一般不传参或传 null。不过即使你真传入了参数，参数也最终被忽略，`xhr.send(data)`中的 data 会被置为 null.

`xhr.send(data)`中 data 参数的数据类型会影响请求头部`content-type`的默认值：

* 如果 data 是 Document 类型，同时也是 HTML Document 类型，则`content-type`默认值为`text/html;charset=UTF-8;`，否则为`application/xml;charset=UTF-8;`
* 如果 data 是 DOMString 类型，`content-type`默认值为`text/plain;charset=UTF-8;`
* 如果 data 是 FormData 类型，`content-type`默认值为`multipart/form-data; boundary=[xxx]`
* 如果 data 是其他类型，则不会设置`content-type`的默认值

当然这些只是`content-type`的默认值，但如果用`xhr.setRequestHeader()`手动设置了`content-type`的值，以上默认值就会被覆盖。

另外需要注意的是，若在断网状态下调用`xhr.send(data)`方法，则会抛错：`Uncaught NetworkError: Failed to execute 'send' on 'XMLHttpRequest'`。一旦程序抛出错误，如果不 catch 就无法继续执行后面的代码，所以调用`xhr.send(data)`方法时，应该用`try-catch`捕捉错误。

```javascript
try{
    xhr.send(data)
}catch(e) {
    // doSomething...
};
```

### xhr.withCredentials 与 CORS 什么关系
我们都知道，在发同域请求时，浏览器会将 cookie 自动加在 request header 中。但在发送跨域请求时，cookie 并没有自动加在 request header 中。

造成这个问题的原因是：在 CORS 标准中做了规定，默认情况下，浏览器在发送跨域请求时，不能发送任何认证信息(credentials)如"cookies"和"HTTP authentication schemes"。除非`xhr.withCredentials`为 true（xhr 对象有一个属性叫`withCredentials`，默认值为false）。

所以根本原因是 cookies 也是一种认证信息，在跨域请求中，client 端必须手动设置`xhr.withCredentials=true`，且 server 端也必须允许 request 能携带认证信息（即 response header 中包含`Access-Control-Allow-Credentials:true`），这样浏览器才会自动将 cookie 加在 request header 中。

另外，要特别注意一点，一旦跨域 request 能够携带认证信息，server 端一定不能将`Access-Control-Allow-Origin`设置为`*`，否则就会面临攻击危险。


## xhr 相关事件

### 事件分类
xhr 相关事件有很多，有时记起来还挺容易混乱。但当我了解了具体代码实现后，就容易理清楚了。下面是`XMLHttpRequest`的部分实现代码：

```javascript
interface XMLHttpRequestEventTarget : EventTarget {
  // event handlers
  attribute EventHandler onloadstart;
  attribute EventHandler onprogress;
  attribute EventHandler onabort;
  attribute EventHandler onerror;
  attribute EventHandler onload;
  attribute EventHandler ontimeout;
  attribute EventHandler onloadend;
};

interface XMLHttpRequestUpload : XMLHttpRequestEventTarget {

};

interface XMLHttpRequest : XMLHttpRequestEventTarget {
  // event handler
  attribute EventHandler onreadystatechange;
  readonly attribute XMLHttpRequestUpload upload;
};
```

从代码中我们可以看出：

1. `XMLHttpRequestEventTarget`接口定义了7个事件：
    * `onloadstart`
    * `onprogress`
    * `onabort`
    * `ontimeout`
    * `onerror`
    * `onload`
    * `onloadend`
2. 每一个`XMLHttpRequest`里面都有一个`upload`属性，而`upload`是一个`XMLHttpRequestUpload`对象
3. `XMLHttpRequest`和`XMLHttpRequestUpload`都继承了同一个`XMLHttpRequestEventTarget`接口，所以 xhr 和 xhr.upload 都有第一条列举的7个事件
4. `onreadystatechange`是`XMLHttpRequest`独有的事件

所以这么一看就很清晰了：
**xhr 一共有8个相关事件：7个`XMLHttpRequestEventTarget`事件 + 1个独有的`onreadystatechange`事件；而`xhr.upload`只有7个`XMLHttpRequestEventTarget`事件。**

### 事件触发条件
下面是我自己整理的一张 xhr 相关事件触发条件表，其中最需要注意的是`onerror`事件的触发条件。

|      事件        |     触发条件                                      |
|-----------------|--------------------------------------------------|
| `onreadystatechange` | 每当`xhr.readyState`改变时触发；但`xhr.readyState`由非0值变为0时不触发。|
| `onloadstart	`        | 调用`xhr.send()`方法后立即触发，若`xhr.send()`未被调用则不会触发此事件。 |
| `onprogress`         | `xhr.upload.onprogress`在上传阶段(即`xhr.send()`之后，`xhr.readystate=2`之前)触发，每 50ms 触发一次；`xhr.onprogress`在下载阶段（即`xhr.readystate=3`时）触发，每 50ms 触发一次。 |
| `onload`             | 当请求成功完成时触发，此时`xhr.readystate=4`|
| `onloadend`	         | 当请求结束（包括请求成功和请求失败）时触发 |
| `onabort`            | 当调用`xhr.abort()`后触发 |
| `ontimeout`          | `xhr.timeout`不等于0，由请求开始即`onloadstart`开始算起，当到达`xhr.timeout`所设置时间请求还未结束即`onloadend`，则触发此事件。 |
| `onerror`            | 在请求过程中，若发生`Network error`则会触发此事件（若发生`Network error`时，上传还没有结束，则会先触发`xhr.upload.onerror`，再触发`xhr.onerror`；若发生`Network error`时，上传已经结束，则只会触发`xhr.onerror`）。注意，只有发生了网络层级别的异常才会触发此事件，对于应用层级别的异常，如响应返回的`xhr.statusCode`是 4xx 时，并不属于`Network error`，所以不会触发`onerror`事件，而是会触发`onload`事件。 |

### 事件触发顺序
当请求一切正常时，相关的事件触发顺序如下：

1. 触发`xhr.onreadystatechange`(之后每次`readyState`变化时，都会触发一次)
2. 触发`xhr.onloadstart`
//上传阶段开始：
3. 触发`xhr.upload.onloadstart`
4. 触发`xhr.upload.onprogress`
5. 触发`xhr.upload.onload`
6. 触发`xhr.upload.onloadend`
//上传结束，下载阶段开始：
7. 触发`xhr.onprogress`
8. 触发`xhr.onload`
9. 触发`xhr.onloadend`

### 发生 abort/timeout/error 异常的处理
在请求的过程中，有可能发生`abort/timeout/error`这3种异常。那么一旦发生这些异常，xhr 后续会进行哪些处理呢？后续处理如下：

1. 一旦发生`abort`或`timeout`或`error`异常，先立即中止当前请求
2. 将`readystate`置为4，并触发`xhr.onreadystatechange`事件
3. 如果上传阶段还没有结束，则依次触发以下事件：
    * `xhr.upload.onprogress`
    * `xhr.upload.[onabort或ontimeout或onerror]`
    * `xhr.upload.onloadend`
4. 触发`xhr.onprogress`事件
5. 触发`xhr.[onabort或ontimeout或onerror]`事件
6. 触发`xhr.onloadend`事件

### 在哪个 xhr 事件中注册成功回调？
从上面介绍的事件中，可以知道若 xhr 请求成功，就会触发`xhr.onreadystatechange`和`xhr.onload`两个事件。 那么我们到底要将成功回调注册在哪个事件中呢？我倾向于`xhr.onload`事件，因为`xhr.onreadystatechange`是每次`xhr.readyState`变化时都会触发，而不是`xhr.readyState=4`时才触发。

```javascript
xhr.onload = function () {
    //如果请求成功
    if(xhr.status == 200){
      //do successCallback
    }
  }
```

上面的示例代码是很常见的写法：先判断 http 状态码是否是200，如果是，则认为请求是成功的，接着执行成功回调。这样的判断是有坑儿的，比如当返回的 http 状态码不是200，而是201时，请求虽然也是成功的，但并没有执行成功回调逻辑。所以更靠谱的判断方法应该是：当 http 状态码为 2xx 或 304 时才认为成功。

```javascript
xhr.onload = function () {
    //如果请求成功
    if((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304){
      //do successCallback
    }
  }
```

## 结语
终于写完了......
看完那一篇长长的W3C的xhr 标准，我眼睛都花了......
希望这篇总结能帮助刚开始接触XMLHttpRequest的你。

最后给点扩展学习资料，如果你：

* 想真正搞懂`XMLHttpRequest`，最靠谱的方法还是看 [W3C的xhr 标准](https://www.w3.org/TR/XMLHttpRequest/)；
* 想结合代码学习如何用`XMLHttpRequest`发各种类型的数据，可以参考 [html5rocks上的这篇文章](http://www.html5rocks.com/zh/tutorials/file/xhr2/)；
* 想粗略的了解`XMLHttpRequest`的基本使用，可以参考 [MDN的XMLHttpRequest介绍](https://developer.mozilla.org/zh-CN/docs/Web/API/XMLHttpRequest)；
* 想了解`XMLHttpRequest`的发展历程，可以参考 [阮老师的文章](http://www.ruanyifeng.com/blog/2012/09/xmlhttprequest_level_2.html)；
* 想了解 Ajax 的基本介绍，可以参考 [AJAX Tutorial](http://www.tutorialspoint.com/ajax/index.htm)；
* 想了解跨域请求，则可以参考 [W3C的 cors 标准](https://www.w3.org/TR/cors/)；
* 想了解 http 协议，则可以参考 [HTTP Tutorial](http://www.tutorialspoint.com/http/http_header_fields.htm)。


