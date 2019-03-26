> 转摘：
> 
> * [SOAP 介绍](https://segmentfault.com/a/1190000003762279)
> * [SOAP Web 服务介绍](https://segmentfault.com/a/1190000003772529)

## 一、简介

从目前的趋势上来看，REST 类型的 Web Service 目前已经远比 SOAP 类型的更流行了，很多新服务都开始使用 REST 风格的 Web Service，之前的 SOAP Web Service 大多也已经开始转型为 REST 类别的了。虽然如此，SOAP 依旧在很多地方有所使用，还是需要对其有相关的了解。

SOAP(Simple Object Access Protoco) 简单对象访问协议，是在分散或分布式的环境中交换信息的简单的协议，是一个基于 XML 的协议。此协议规范由 IBM、Microsoft、UserLand 和 DevelopMentor 在 1998 年共同提出，并得到 IBM、莲花(Lotus)、康柏(Compaq)等公司的支持，于 2000 年提交给万维网联盟(World Wide Web Consortium，即 W3C)。现在，SOAP 协议规范由万维网联盟的 XML 工作组维护。SOAP 1.2 版在 2003 年 6 月 24 日成为 W3C 的推荐版本。

SOAP 消息基本上是从发送端到接收端的单向传输，但它们常常结合起来执行类似于请求/应答的模式。所有的 SOAP 消息都使用 XML 编码。一条 SOAP 消息就是一个包含有一个必需的 SOAP 的封装包，一个可选的 SOAP 标头(Header)和一个必需的 SOAP 体块(Body)的 XML 文档。

SOAP 风格的 Web Service 有三个要素：

1.	SOAP(Simple Object Access Protoco) 简单对象访问协议
2.	WSDL(Web Services Description Language) 网络服务描述语言
3.	UDDI(Universal Description Discovery and Integration) 一个用来发布和搜索 WEB 服务的协议（非必须）

SOAP 用来描述传递信息的格式规范， WSDL 用来描述如何访问具体的接口（比如它会告诉你该服务有哪些接口可以使用，参数是什么等等）， UDDI 用来管理、分发和查询 Web Service。

## 二、SOAP

SOAP 是一个协议，定义了一个基于 XML 的可扩展消息信封格式，从而使得满足该协议的客户端和服务器能够进行消息传递。

### 2.1 协议内容

SOAP 协议包括以下四个部分的内容：

1.	SOAP envelop: 封装定义了一个描述消息中的内容是什么，是谁发送的，谁应当接受并处理它以及如何处理它们的框架；
2.	SOAP encoding rules: 编码规则定义了不同应用程序间交换信息时，需要使用到的数据类型；
3.	SOAP RPC representation: RPC 表示定义了一个表示远程过程调用和应答的协定；
4.	SOAP binding: 绑定定义 SOAP 使用哪种底层协议交换信息的协定。使用 HTTP/TCP/UDP 协议都可以；

虽然这四个部分都作为 SOAP 的一部分，是作为一个整体被定义的，但他们在功能上是相交的、彼此独立的。特别的，封装(envelop)和编码规则(encoding rules)是被定义在不同的 XML 命名空间中，这样使得定义更加简单。

### 2.2 消息格式

SOAP 消息的格式比较简单，如下图：

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1553505684605.png" width="224"/>

一条 SOAP 消息就是一个普通的 XML 文档，包含如下元素：

1.	必需的 Envelope 元素，据此可把该 XML 文档标识为一条 SOAP 消息；
2.	可选的 Header 元素，包含头部信息，一般用于身份验证；
3.	必需的 Body 元素，包含所有的调用和响应信息；
4.	可选的 Fault 元素，提供有关在处理此消息时，所发生的错误的描述信息；

下面是发送的一条 SOAP 消息的示例：

```xml
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <qqCheckOnline xmlns="http://WebXml.com.cn/">
            <qqCode>8698053</qqCode>
        </qqCheckOnline>
    </soap:Body>
</soap:Envelope>
```

下面是接收的一条 SOAP 消息的示例：

```xml
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <qqCheckOnlineResponse xmlns="http://WebXml.com.cn/">
            <qqCheckOnlineResult>Y</qqCheckOnlineResult>
        </qqCheckOnlineResponse>
    </soap:Body>
</soap:Envelope>
```

### 2.3 Envelope

`Envelope`是 SOAP 消息结构的主要容器，也是 SOAP 消息的根元素，它必须出现在每个 SOAP 消息中，用于把此 XML 文档标示为一条 SOAP 消息。

在 SOAP 中，一般使用命名空间(`xmlns`)将 SOAP 消息元素与应用程序自定义的元素区分开来，将 SOAP 消息元素的作用域限制在一个特定的区域。

```xml
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema">
</soap:Envelope>
```

上面的示例中，定义了三个命名空间，这三个命名空间分别绑定了一个前缀，分别是：`soap`、`xsi`、`xsd`。

任何使用了命名空间前缀的元素，都属于该命名空间。比如：

```xml
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <qqCheckOnline xmlns="http://WebXml.com.cn/">
            <qqCode>8698053</qqCode>
        </qqCheckOnline>
    </soap:Body>
</soap:Envelope>
```

这里，`Envelope`和`Body`元素是以`soap`为前缀的，那么这两个元素元素都是属于`http://schemas.xmlsoap.org/soap/envelope/`这个命名空间的。

上面示例中，`qqCheckOnline`元素上也定义了一个命名空间`http://WebXml.com.cn/`，值得注意的是，而且没有设置命名空间前缀(namespace prefix)，这种设置方式，会把当前元素及其所有子元素，都归属于该命名空间。

可以看到，SOAP 消息元素和应用程序本身的元素是属于不同的命名空间，这样有利于把 SOAP 消息元素与其他元素区分开来，当然也防止了与自定义元素重名的问题。

> SOAP 1.1 版本的协议规定了，SOAP 消息必须使用 SOAP Envelope 命名空间，所以`http://schemas.xmlsoap.org/soap/envelope/`这个命名空间是固定的不能变。所有 SOAP 消息元素，比如`Envelope`、`Header`、`Body`、`Fault`也都必须属于该命名空间。

> 了解更多 [XML 命名空间](http://www.w3school.com.cn/xml/xml_namespaces.asp)

### 2.4 Header

`Header`元素是可选的，如果需要添加`Header`元素，那么它必须是`Envelope`的第一个子元素。

`Header`可以包含 0 个或多个可选的子元素，这些子元素称为 Header 项，所有的 Header 项一般来说是属于某个特定与接口相关的命名空间。

有些接口需要提供`Header`元素，和`Body`信息一起发送，一般用于身份验证等作用。下面例子中的`AuthenHeader`和`sAuthenticate`都是接口自定义的参数。

```xml
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    soap:encodingStyle="http://www.w3.org/2003/05/soap-encoding">
    <soap:Header>
        <AuthenHeader xmlns="http://www.example.com">
            <sAuthenticate>string</sAuthenticate>
        </AuthenHeader>
    </soap:Header>
    <soap:Body>
    </soap:Body>
</soap:Envelope>
```

### 2.5 Body

`Body`元素里面，一般都是放一些请求和响应的内容，可以包含以下任何元素：

1.	远程过程调用(RPC)的方法及其参数
2.	目标应用程序（消息接收者即接口调用者）所需要的数据
3.	报告故障和状态消息的 SOAP Fault

所有`Body`元素的直接子元素都称为 Body 项，所有 Body 项一般是属于某个特定的命名空间的。

SOAP 请求消息例子：

```xml
<soap:Envelope
    xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
    soap:encodingStyle="http://www.w3.org/2003/05/soap-encoding">
    <soap:Body>
        <getMobileCodeInfo xmlns="http://www.example.com">
            <mobileCode>string</mobileCode>
            <userID>string</userID>
        </getMobileCodeInfo>
    </soap:Body>
</soap:Envelope>
```

SOAP 响应消息例子：

```xml
<soap:Envelope
    xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
    soap:encodingStyle="http://www.w3.org/2003/05/soap-encoding">
    <soap:Body>
        <getMobileCodeInfoResponse xmlns="http://www.example.com">
            <getMobileCodeInfoResult>string</getMobileCodeInfoResult>
        </getMobileCodeInfoResponse>
    </soap:Body>
</soap:Envelope>
```

### 2.6 Fault

当调用服务发生错误时，错误信息一般会被放置在`Fault`元素内。

如果 SOAP 消息中包括 Fault 元素，它必须作为一个 Body 的子元素出现，而且至多出现一次。`Fault`元素本身也包含有描述错误详细信息的子元素。它包含以下子元素：

* `faultcode`供识别故障的代码
* `faultstring`可供人阅读的有关故障的说明
* `faultactor`有关是谁引发故障的信息
* `detail`有关涉及 Body 元素的应用程序专用错误信息

其中`faultcode`是每一条错误消息都会提供的元素，它的值一般是以下错误代码之一：

* `VersionMismatch`无效的 SOAP Envelope 命名空间
* `MustUnderstand`无法理解`Header`中拥有属性`mustUnderstand = 1`的子元素
* `Client`消息结构错误，或包含了不正确的信息
* `Server`服务器出现错误

以上关于 SOAP Fault 的描述不完全适用于 SOAP 1.2 版本。因为 SOAP 1.2 版本在返回错误信息时，Fault 的子元素及其内容已经有所不同。具体看下面的例子：

SOAP v1.1 错误消息例子：

```xml
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
    soap:encodingStyle="http://www.w3.org/2001/12/soap-encoding">
    <soap:Body>
        <soap:Fault>
            <faultcode>soap:Client</faultcode>
            <faultstring>Input string was not in a correct format.</faultstring>
        <detail/>
    </soap:Fault>
</soap:Body>
</soap:Envelope>
```

SOAP v1.2 错误消息例子：

```xml
<soap:Envelope
    xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
    soap:encodingStyle="http://www.w3.org/2003/05/soap-encoding">
    <soap:Body>
        <soap:Fault>
            <soap:Code>
                <soap:Value>soap:Sender</soap:Value>
            </soap:Code>
            <soap:Reason>
                <soap:Text xml:lang="en">Input string was not in a correct format.</soap:Text>
            </soap:Reason>
            <soap:Detail/>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
```

### 2.7 属性

* `soap:encodingStyle`属性用于定义在文档中使用的数据类型。此属性可出现在任何 SOAP 元素中，并会被应用到该元素的内容及元素的所有子元素上。

## 三、WSDL

WSDL 文档一般是用一个 XML 格式的文档，用于描述该服务有哪些可用方法、参数的数据类型、命名空间等等信息，目的是使用户知道该如何使用该服务，包括调用的各种细节信息。WSDL 文档通常用来辅助生成服务器和客户端代码及配置信息。

一个 WSDL 文件也挺复杂的，一般不会去直接看这个文件，而是需要用到某个方法时，直接看该方法的调用说明。可以点击这里查看 [WSDL 实例](http://www.webxml.com.cn/webservices/qqOnlineWebService.asmx?WSDL)。

在开发 Web Service 过程中有两种实现模式：契约先行(Contract First)模式和代码先行(Code First)模式：

* **契约先行模式**：首要工作是定义针对这个 Web 服务的接口的 WSDL(Web Services Description Language，Web 服务描述语言) 文件。WSDL 文件中描述了 Web 服务的位置，可提供的操作集，以及其他一些属性。WSDL 文件也就是 Web 服务的 “契约”。“契约” 订立之后，再据此进行服务器端和客户端的应用程序开发。

* **代码先行模式**：与契约先行模式不同，代码先行模式中，第一步工作是实现 Web 服务端，然后根据服务端的实现，用某种方法（自动生成或手工编写）生成 WSDL 文件。

## 四、UDDI

UDDI 是一个专门用来管理 Web 服务的地方。Web Service 服务提供商可以通过两种方式来暴露它的 WSDL 文件地址：

1.	注册到 UDDI 服务器，以便被人查找
2.	直接告诉给客户端调用者 
是否需要注册到 UDDI 实际上是可选的，一般公司内部使用的服务，也不会注册到 UDDI。只有那些希望所有人都知道该服务的地址，才会注册到 UDDI。



