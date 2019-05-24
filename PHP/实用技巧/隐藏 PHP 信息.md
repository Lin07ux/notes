一些简单的方法可以帮助隐藏 PHP，这样做可以提高攻击者发现系统弱点的难度：

* 在`php.ini`文件里设置`expose_php = off`，可以去除响应头信息中的`X-Powered_By`。
* 让 Web 服务器用 PHP 解析不同扩展名。无论是通过`htaccess`文件还是 Apache 的配置文件，都可以设置能误导攻击者的文件扩展名。

**使 PHP 看上去像其它的编程语言**

`AddType application/x-httpd-php .asp .py .pl`

**使 PHP 看上去像未知的文件类型**

`AddType application/x-httpd-php .bop .foo .133t`

**使 PHP 代码看上去像 HTML 页面**

`AddType application/x-httpd-php .htm .html`

要让此方法生效，必须把 PHP 文件的扩展名改为以上的扩展名。这样就通过隐藏来提高了安全性，虽然防御能力很低而且有些缺点。

> 参考：[隐藏 PHP](http://php.net/manual/zh/security.hiding.php)



