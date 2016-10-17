HTML5 中提供了在网页文档之间相互接收与发送信息的功能。可以使用它来向其它的 window 对象发送消息，无论这个 window 对象是属于同源或不同源。使用这个功能，只要获取到网页所在窗口对象的实例，不仅仅同源(域+端口号)的 web 网页之间可以互相通信，甚至可以实现跨域通信。

浏览器支持程度：IE8+，firefox4+，chrome8+ opera10+。

### 思路
1. 首先，要想接收从其他的窗口发过来的消息，就必须对窗口对象的`message`事件进行监听，如下代码：

    ```javascript
    window.addEventListener(“message”, function(){},false);
    ```

2. 其次，需要使用 window 对象的`postMessage`方法向其他窗口发送消息，该方法定义如下所示：

    ```javascript
    otherWindow.postMessage(message, targetOrigin);
    ```
    
    该方法使用 2 个参数，第一个参数为所发送的消息文本，但也可以是任何 javascript 对象；第二个参数是接收消息的对象窗口的 url 地址(比如：`http:127.0.0.1:8080/`)，但是我们也可以在 url 地址字符串中使用通配符”*”, 指定全部的域下，但是我们还是建议使用特定的域名下，`otherWindow`为要发送窗口对象的引用。

使用的时候，有以下几点须知：

1. 通过对 window 对象的`message`事件进行监听，可以接收消息。
2. 通过访问`message`事件的`origin`属性，可以获取消息的发送源。可能包含协议、域名和端口。一般需要用这个属性来验证数据源。
3. 通过访问`message`事件的`data`属性，可以取得消息内容。
4. 通过访问`message`事件的`source`属性，可以获取消息发送源的窗口对象(准确的说，应该是窗口的代理对象)。
5. 使用 window 对象的`postMessage`方法发送消息。


### 示例一
假如现在 hosts 文件中绑定 2 个域名如下：

```
127.0.0.1       abc.example.com
127.0.0.1       longen.example.com
```

现在假如在`abc.example.com`域下有一个`abc.html`页面，在`longen.example.com`域下有`def.html`页面，现在希望这 2 个不同域名下的页面能互相通信，`abc.html`中关键代码如下：

```html
<form>  
  <p>  
    <label for="message">给iframe子窗口发一个信息：</label>  
    <input type="text" name="message" value="send" id="message" />  
    <input type="submit" value="submit" id="submit"/>  
  </p>  
</form>

<h4>目标iframe传来的信息：</h4>  
<p id="test">暂无信息</p> 

<iframe id="iframe" src="http://longen.example.com/webSocket/def.html" style="display:none"></iframe>
 
<script>
var win = document.getElementById("iframe").contentWindow;

document.getElementById("submit").onclick = function(e){
    e.preventDefault();
    win.postMessage(document.getElementById("message").value, "http://longen.example.com"); 
}  

window.addEventListener("message", function(e){
     e.preventDefault();
     document.getElementById("test").innerHTML = "从" + e.origin + "那里传过来的消息:\n" + e.data;
}, false);
</script>
```

`def.html`中的关键代码如下：

```html
<form>  
  <p>  
    <label for="message">给父窗口abc.html发个信息：</label>  
    <input type="text" name="message" value="send" id="message" />  
    <input type="submit" />  
  </p>  
 </form>  
 <p id="test2">暂无信息。</p>

<script>
var parentwin = window.parent; 
window.addEventListener("message", function(e){
       document.getElementById("test2").innerHTML = "从父窗口传来的域" + e.origin + "，和内容数据：" + e.data;  
       parentwin.postMessage('HI!你给我发了"<span>'+e.data+'"</span>。',"http://abc.example.com");
},false);
</script>
```

当点击`abc.html`页面后，从`def.html`返回内容了。效果如下：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1476800511534.png" width="275"/>


### 示例二
有了上面的了解，我们可以延伸为实现 ajax POST 跨域的问题。

原理也很简单，假如我们的域名`abc.example.com`下的`abc.html`页面需要发 ajax 请求(跨域，域名为`longen.example.com`)下。那么我们还是先跨页面文档的形式，和上面一样，我们可以现在`longen.example.com`下建立一个页面，比如叫`def.html`。

现在还是在`abc.html`页面嵌入一个隐藏域 iframe，`src`路径指向`longen.example.com`域下`def.html`页面。过程还是和跨文档类似，只是现在在`def.html`页面中，在`window.onmessage`事件内写 ajax 请求即可。

`abc.html`页面的 HTML 代码不变，仅仅更改部分 JavaScript 代码：

```javascript
var win = document.getElementById("iframe").contentWindow;

document.getElementById("submit").onclick = function(e){
      e.preventDefault();
      win.postMessage(document.getElementById("message").value,"http://longen.example.com/"); 
}  

window.addEventListener("message",function(e){
    e.preventDefault();
    
    // alert(typeof e.data)
    
    var json = JSON.parse(e.data);
    
    console.log(json);
    
    // alert(json.url)
}, false);
```

`def.html`的 HTML 代码也不变，JavaScript 代码如下：

```javascript
//获取跨域数据  
window.onmessage = function(e){  
  $.ajax({
    url: 'http://longen.example.com/webSocket/test.php',
    type:'POST',
    dataType:'text',
    data: {msg:e.data},
    success: function(res) {
      var parentwin = window.parent;  
      parentwin.postMessage(res,"http://abc.example.com");//跨域发送数据
    }
  });
};
```

对应的后端服务器的处理很简单，如下：

```php
<?php 
    $data=array(  
     url =>1,
      name =>'2',
      'xx-xx'=>"xx"
 );
 echo json_encode($data);
```

这样，一个简单的跨域访问的需求就实现了。

### 转摘
[使用HTML5中postMessage实现Ajax中的POST跨域问题](http://www.webhek.com/postmessage-cross-domain-post)

