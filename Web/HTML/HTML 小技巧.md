## 安全
### 禁用选项
在 body 标签中输入下面的一些定义即可：

* 禁止复制：`oncopy="event.returnValue=false"`
* 禁止拖拽：`ondragstart="window.event.returnValue=false"`
* 禁止右键菜单：`oncontextmenu="window.event.returnValue=false"`
* 禁止选择：`onselectstart="event.returnValue=false"`
		
### 避免在新标签打开的页面引用
当从一个网上打开一个`target="_blank"`的链接时，新打开的链接可以通过 JavaScript 中的`window.opener`来访问原页面，从而做一些操作。

为避免这种操作，对于打开非本站的链接的时候，将`target="_blank"`的链接都加上`rel="noopener noreferrer"`这个属性。同时，要像关注 xss 一样关注一下外链图片的问题。

## 在收藏夹中显示出图标
`<link rel='Bookmark' href='favicon.ico'>`

## 关闭输入法
`<input style='ime-mode:disabled'>`

## TEXTAREA 自适应文字行数的多少
`<textarea rows=1 name=s1 cols=27 onpropertychange='this.style.posHeight=this.scrollHeight'></textarea>`

## 在规定时间内跳转
`<META http-equiv=V='REFRESH' content='5;URL=http://www.51js.com'>`

## 在 android 或者 iOS 下拨打电话
`<a href="tel:15602512356">打电话给: 15602512356</a>`

## 发短信(winphone 系统无效)
`<a href="sms:10010">发短信给: 10010</a>`

## 调用手机系统自带的邮件功能
### 发邮件
`<a href="mailto:tugenhua@126.com">发电子邮件</a>`

当浏览者点击这个链接时，浏览器会自动调用默认的客户端电子邮件程序，并在收件人框中自动填上收件人的地址。

### 抄送
在IOS手机下，在收件人地址后用`?cc=`开头：

`<a href="mailto:tugenhua@126.com?cc=879083421@qq.com">填写抄送地址</a>`

在 android 手机下，如下代码：

`<a href="mailto:tugenhua@126.com?879083421@qq.com">填写抄送地址</a>`

### 密送
在 iOS 手机下：紧跟着抄送地址之后，写上`&bcc=`，填上密件抄送地址：

`<a href="mailto:tugenhua@126.com?cc=879083421@qq.com&bcc=aa@qq.com">填上密送地址</a>`

在安卓下，如下代码：

`<a href="mailto:tugenhua@126.com?879083421@qq.com?aa@qq.com">填上密件抄送地址</a>`

### 多个收件人、抄送、密件抄送人
用分号隔(`;`)开多个收件人的地址即可实现。

`<a href="mailto:tugenhua@126.com;879083421@qq.com;aa@qq.com">包含多个收件人、抄送、密件抄送人，用分号隔(;)开多个收件人的地址即可实现</a>`

### 包含主题
用`?subject=`可以填上主题。如下代码：

`<a href="mailto:tugenhua@126.com?subject=【邀请函】">包含主题，可以填上主题</a>`

### 包含内容
用`?body=`可以填上内容(需要换行的话，使用`%0A`给文本换行)，代码如下：

`<a href="mailto:tugenhua@126.com?body=我来测试下">包含内容，用?body=可以填上内容</a>`

### 内容包含链接
含`http(s)://`等的文本自动转化为链接。如下代码：

`<a href="mailto:tugenhua@126.com?body=http://www.baidu.com">内容包含链接，含http(s)://等的文本自动转化为链接</a>`

## 其他

### 不支持 JavaScript

当页面不支持 js 的时候，可以使用`<noscript>内容</noscript>`标签来进行提示信息的展示。如果浏览器支持 js，那么这个标签就不会被显示在页面中。

### Web 页面链接到百度地图

在 web 页面中，可以通过一个 a 链接直接链接到百度地图上，并可以指定打开地图时的显示标题和地址信息。

```html
<a class="content" href="http://api.map.baidu.com/marker?location=30.2481330000,120.0771000000&title=馒头山&content=浙江省杭州市西湖区求和路馒头山&output=html">
```

在 a 链接中需要指定一个经纬度信息。具体的经纬度可以从 [在线地图经纬度查询](http://www.gpsspg.com/maps.htm) 或 [百度坐标系统](http://api.map.baidu.com/lbsapi/getpoint/index.html) 中查询到。

### 设置元素可编辑

给元素添加`contenteditable`属性即可。如：

```html
<p contenteditable="true">可编辑</p>
```

也可以通过 js 来设置整个页面可编辑：

```js
document.contentEditable = 1;
```

### 移动端非全屏播放视频

```html
<video src="test.mp4" webkit-playsinline playsinline></video>
```

### a 链接

a 链接的`href`属性可以有如下几种值：

- 空：如果该属性没有值，那么点击该 a 元素后会刷新页面。
- url：如果设置成一个 url 那么点击该 a 标签将会跳转到指定的 url。
- 锚点(`#`)：如果设置成锚点，那么点击之后会跳转到当前页面的某个元素(该元素的 ID 和锚点中的值相同)。
- JavaScript 代码：点击后会执行该代码，不过一般通过设置`href="javascript:(void)0;"`

> 需要注意的是如果锚点值仅为`#`那么点击后就会跳转到页面顶部。



