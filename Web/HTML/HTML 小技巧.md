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

## 不支持 JavaScript
当页面不支持 js 的时候，可以使用`<noscript>内容</noscript>`标签来进行提示信息的展示。如果浏览器支持 js，那么这个标签就不会被显示在页面中。

