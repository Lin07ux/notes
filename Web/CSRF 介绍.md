CSRF(Cross-site request forgery)，中文名称：跨站请求伪造，也被称为：one click attack/session riding，缩写为：CSRF/XSRF。

简单来说，可以这样理解：攻击者盗用了你的身份，以你的名义发送恶意请求。

通过 CSRF 攻击能够做的事情包括：以你名义发送邮件，发消息，盗取你的账号，甚至于购买商品，虚拟货币转账等等，造成个人隐私的泄露和财产的损失。

### 基本原理
下图简单阐述了 CSRF 攻击的思想：

<img src="http://7xkt52.com1.z0.glb.clouddn.com/markdown/1474465651170.png" width="526"/>

从图中可以看出，要完成一次 CSRF 攻击，受害者必须依次完成两个步骤：

1. 登录受信任网站 A，并在本地生成身份认证用的 Cookie。
2. 在不登出 A 的情况下(A 站中身份认证的 Cookie 未过期)，访问危险网站 B。

有了这两步，攻击者就可以在 B 站中用你的身份操作 A 站中的相关内容了。

自然，如果能不满足这两者中的任何一个条件，那么就不会受到 CSRF 攻击了。但是一般很难避免的：

1. 你不能保证你登录了一个网站后，不再打开一个 tab 页面并访问另外的网站。
2. 你不能保证你关闭浏览器了后，你本地的 Cookie 立刻过期，你上次的会话已经结束。（事实上，关闭浏览器不能结束一个会话，但大多数人都会错误的认为关闭浏览器就等于退出登录/结束会话了。）
3. 上图中所谓的攻击网站 B，可能是一个存在其他漏洞的可信任的经常被人访问的网站。

需要注意的是：**CSRF 并不涉及到跨域限制问题**。这是因为：跨域限制是浏览器为了安全作出的限制，而且仅仅是限制获取到跨域访问的返回信息，而不是限制跨域访问请求的发出。也就是说，虽然浏览器做了跨域限制，也仅仅是限制了访问请求的响应，而不能限制访问的发出和服务器对请求的执行。

### 简单示例
#### 示例1
银行网站 A，它以 GET 请求来完成银行转账的操作，如：`http://www.mybank.com/Transfer.php?toBankId=11&money=1000`。

危险网站 B，它里面有一段 HTML 的代码如下：`<img src=http://www.mybank.com/Transfer.php?toBankId=11&money=1000>`

首先，你登录了银行网站 A，然后访问危险网站 B，这时你会发现你的银行账户少了 1000 块。

为什么会这样呢？原因是银行网站 A 违反了 HTTP 规范，使用 GET 请求更新资源。在访问危险网站 B 的之前，你已经登录了银行网站 A，而 B 中的 <img> 以 GET 的方式请求第三方资源（这里的第三方就是指银行网站了，原本这是一个合法的请求，但这里被不法分子利用了），所以你的浏览器会带上你的银行网站 A 的 Cookie 发出 Get 请求，去获取资源`http://www.mybank.com/Transfer.php?toBankId=11&money=1000`，结果银行网站服务器收到请求后，认为这是一个更新资源操作（转账操作），所以就立刻进行转账操作。

#### 示例2
为了杜绝上面的问题，银行决定改用POST请求完成转账操作。银行网站 A 的 WEB 表单如下：

```html
<form action="Transfer.php" method="POST">
　　　<p>ToBankId: <input type="text" name="toBankId" /></p>
　　　<p>Money: <input type="text" name="money" /></p>
　　　<p><input type="submit" value="Transfer" /></p>
</form>
```

后台处理页面 Transfer.php 如下：

```php
<?php
session_start();
if (isset($_REQUEST['toBankId'] &&　isset($_REQUEST['money'])) {
    buy_stocks($_REQUEST['toBankId'],　$_REQUEST['money']);
}
```

危险网站 B，仍然只是包含那句 HTML 代码：`<img src=http://www.mybank.com/Transfer.php?toBankId=11&money=1000>`。

和示例 1 中的操作一样，你首先登录了银行网站 A，然后访问危险网站 B，结果，和示例 1 一样，你再次没了 1000块，这次事故的原因是：银行后台使用了`$_REQUEST`去获取请求的数据，而`$_REQUEST`既可以获取 GET 请求的数据，也可以获取 POST 请求的数据，这就造成了在后台处理程序无法区分这到底是 GET 请求的数据还是 POST 请求的数据。在 PHP 中，可以使用`$_GET`和`$_POST`分别获取 GET 请求和 POST 请求的数据。在 JAVA 中，用于获取请求数据 request 一样存在不能区分 GET 请求数据和 POST 数据的问题。

#### 示例3：
　　经过前面 2 个惨痛的教训，银行决定把获取请求数据的方法也改了，改用`$_POST`，只获取 POST 请求的数据，后台处理页面 Transfer.php 代码如下：

```php
<?php

session_start();
if (isset($_POST['toBankId'] &&　isset($_POST['money']))
{
    buy_stocks($_POST['toBankId'],　$_POST['money']);
}
```

然而，危险网站 B 与时俱进，它改了一下代码：

```html
<html>
　　<head>
　　　　<script type="text/javascript">
　　　　　　function steal()
　　　　　　{
          　　　　 iframe = document.frames["steal"];
　　     　　      iframe.document.Submit("transfer");
　　　　　　}
　　　　</script>
　　</head>

　　<body onload="steal()">
　　　　<iframe name="steal" display="none">
　　　　　　<form method="POST" name="transfer"　action="http://www.myBank.com/Transfer.php">
　　　　　　　　<input type="hidden" name="toBankId" value="11">
　　　　　　　　<input type="hidden" name="money" value="1000">
　　　　　　</form>
　　　　</iframe>
　　</body>
</html>
```

如果用户仍是继续上面的操作，很不幸，结果将会是再次不见1000块。因为这里危险网站 B 暗地里发送了 POST 请求到银行!

总结一下上面 3 个例子，CSRF 主要的攻击模式基本上是以上的 3 种，其中以第 1、2 种最为严重，因为触发条件很简单，一个`<img>`就可以了，而第 3 种虽然稍微麻烦，需要使用 JavaScript，但现在来说并不是问题。无论是哪种情况，只要触发了 CSRF 攻击，后果都有可能很严重。

理解上面的 3 种攻击模式，其实可以看出，**CSRF 攻击是源于 WEB 的隐式身份验证机制！**WEB 的身份验证机制虽然可以保证一个请求是来自于某个用户的浏览器，但却无法保证该请求是用户批准发送的！

### 防御
CSRF 的防御可以从服务端和客户端两方面着手，防御效果是从服务端着手效果比较好，现在一般的 CSRF 防御也都在服务端进行。

#### HTTP Referer
根据 HTTP 协议，在 HTTP 头中有一个字段叫 Referer，它记录了该 HTTP 请求的来源地址。

在通常情况下，访问一个安全受限页面的请求来自于同一个网站，比如需要访问`http://bank.example/withdraw?account=bob&amount=1000000&for=Mallory`，用户必须先登陆`bank.example`，然后通过点击页面上的按钮来触发转账事件。这时，该转帐请求的 Referer 值就会是转账按钮所在的页面的 URL，通常是以`bank.example`域名开头的地址。

而如果黑客要对银行网站实施 CSRF 攻击，他只能在他自己的网站构造请求，当用户通过黑客的网站发送请求到银行时，该请求的 Referer 是指向黑客自己的网站。因此，要防御 CSRF 攻击，银行网站只需要对于每一个转账请求验证其 Referer 值，如果是以`bank.example`开头的域名，则说明该请求是来自银行网站自己的请求，是合法的。如果 Referer 是其他网站的话，则有可能是黑客的 CSRF 攻击，拒绝该请求。

这种方法的显而易见的好处就是简单易行，网站的普通开发人员不需要操心 CSRF 的漏洞，只需要在最后给所有安全敏感的请求统一增加一个拦截器来检查 Referer 的值就可以。特别是对于当前现有的系统，不需要改变当前系统的任何已有代码和逻辑，没有风险，非常便捷。

然而，这种方法并非万无一失。Referer 的值是由浏览器提供的，虽然 HTTP 协议上有明确的要求，但是每个浏览器对于 Referer 的具体实现可能有差别，并不能保证浏览器自身没有安全漏洞。使用验证 Referer 值的方法，就是把安全性都依赖于第三方（即浏览器）来保障，从理论上来讲，这样并不安全。事实上，在一些低版本的浏览器中确实能够被修改 Referer。

另外，对于注重隐私的用户来说，如果他们禁用了 Referer，那么服务器就无法获取 Referer 而不执行正常用户的请求了。

#### Token
CSRF 攻击之所以能够成功，是因为黑客可以完全伪造用户的请求，该请求中所有的用户验证信息都是存在于 cookie 中，因此黑客可以在不知道这些验证信息的情况下直接利用用户自己的 cookie 来通过安全验证。

要抵御 CSRF，关键在于在请求中放入黑客所不能伪造的信息，并且该信息不存在于 cookie 之中。可以在 HTTP 请求中传递一个随机产生的 token，并在服务器端建立一个拦截器来验证这个 token，如果请求中没有 token 或者 token 内容不正确，则认为可能是 CSRF 攻击而拒绝该请求。

至于如何传递 token，则有很多不同的方式了：

**1. Url Token**
这种方式就是直接在 Url 中追加一个 Token 参数，简单易行，但是这样也会导致 Token 暴露，而且对于一些老网站来说，给每个 Url 添加 Token 工作量会非常大。

**2. Cookie Hashing**
这可能是最简单的解决方案了，因为攻击者不能获得第三方的 Cookie(理论上)，所以表单中的数据也就构造失败了(无法在表单中加入正确的伪随机值)。

```php
<?php
//构造加密的 Cookie 信息
$value = “DefenseSCRF”;
setcookie(”cookie”, $value, time()+3600);
```

在表单里增加 Hash 值，以认证这确实是用户发送的请求。

```php
<?php
$hash = md5($_COOKIE['cookie']);
?>

<form method=”POST” action=”transfer.php”>
    <input type=”text” name=”toBankId”>
    <input type=”text” name=”money”>
    <input type=”hidden” name=”hash” value=”<?=$hash;?>”>
    <input type=”submit” name=”submit” value=”Submit”>
</form>
```

然后在服务器端进行 Hash 值验证：

```php
<?php
if (isset($_POST['check'])) {
    $hash = md5($_COOKIE['cookie']);
    if($_POST['check'] == $hash) {
        doJob();
    } else {
        //...
    }
} else {
    //...
}
```

这个方法已经可以杜绝大部分的 CSRF 攻击了，但是由于用户的 Cookie 很容易由于网站的 XSS 漏洞而被盗取，所以如果需要 100% 的杜绝，这个不是最好的方法。

**3. Session Token**
Cookie 可能会被盗取，而更好的办法是将伪随机值保存在服务器端的 session 中，就避免了 Cookie 被盗用的风险。基本思路是和上面的 Cookie Hashing 方法类似，在有表单的地方都插入存在于 session 中的 Token。

**4. HTTP Header Token**
另外，还可以将 token 存放在 HTTP 请求头的自定义头中。这种方式适合于 Ajax 请求中，因为 Ajax 请求能够方便的添加自定义的请求头。另外，这种方式可能需要服务器的配合，比如 Nginx 可能会禁止用户自定义请求头的传入。

**5. One-Time Tokens(不同的表单包含一个不同的伪随机值)**
上面使用的 token 都是在用户登录的时候就设置好的，在整个会话过程中不会改变的，这样就会有 token 泄露的风险。而 One-Time Tokens 则是对于每个表单都生成唯一的随机值，从而可以避免 token 泄露。

在实现 One-Time Tokens 时，需要注意一点：就是“并行会话的兼容”。如果用户在一个站点上同时打开了两个不同的表单，CSRF 保护措施不应该影响到他对任何表单的提交。考虑一下如果每次表单被装入时站点生成一个伪随机值来覆盖以前的伪随机值将会发生什么情况：用户只能成功地提交他最后打开的表单，因为所有其他的表单都含有非法的伪随机值。必须小心操作以确保 CSRF 保护措施不会影响选项卡式的浏览或者利用多个浏览器窗口浏览一个站点。


### 转摘
1. [浅谈CSRF攻击方式](http://www.cnblogs.com/hyddd/archive/2009/04/09/1432744.html)
2. [CSRF 攻击的应对之道](https://www.ibm.com/developerworks/cn/web/1102_niugang_csrf/)

