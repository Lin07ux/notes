Cookie 的两个重要属性：

`Set-Cookie: id=a3fWa; Expires=Wed, 21 Oct 2015 07:28:00 GMT; Secure; HttpOnly`

被标记为`Secure`的 Cookie 信息在 HTTP 请求中不会被传送，它只会在 HTTPS 请求中传送，避免数据被泄露。

被标记为`HttpOnly`的 Cookie 信息是无法通过 Javascript API 获取到的，它只会在请求中传送。这样可以避免黑客通过网页脚本方式窃取Cookie中的敏感信息。

因为 HTTP 协议的不安全性，请求数据包很容易被窃听，Cookie 中的会话信息很容易被盗。解决方案之一就是在会话中记录用户的终端信息和 IP 地址信息，如果这些信息突然发生改变，需要强制用户重新认证。



