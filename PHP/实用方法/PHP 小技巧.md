### 返回当前 php 文件的上级目录

可以先使用`dirname`获取当前文件的路径，然后拼接上级目录的路径`/../`，最后使用`realpath`来获取真实的上级目录路径。

```php
realpath(dirname(__FILE__) . '/../')
```

### 使用 fastcgi_finish_request 进行异步操作

PHP 运行在 FastCGI 模式时，FPM 提供了一个方法：`fastcgi_finish_request`。

[官方文档](http://php.net/manual/zh/function.fastcgi-finish-request.php)对该方法的解释是：冲刷(flush)所有响应的数据给客户端。

也就是说：在调用方法的时候，会发送响应，关闭连接，但是不会结束 PHP 的运行。

查看如下代码：

```php
echo date('Y-m-d H:i:s', time())."\r\n"; //会输出

fastcgi_finish_request();

set_time_limit(0);  //避免超时报错

ini_set('memory_limit', '-1');  //避免内存不足

sleep(5);

$time = date('Y-m-d H:i:s', time())."\r\n";

echo $time; //不会输出

file_put_contents('test.txt', $time, FILE_APPEND);、
```

执行这段函数后你会发现，可以实现异步操作，提高响应速度。

可以使用`fastcgi_finish_request()`函数集成队列，可以把消息异步发送到队列。

因为这个函数只在 FastCGI 模式下存在，考虑可移植性可以加上以下代码：

```php
if (!function_exists("fastcgi_finish_request")) {
      function fastcgi_finish_request()  {
      }
}
```

> 转摘：[PHP fastcgi_finish_request 方法](https://zhuanlan.zhihu.com/p/26117965)

### PHP 下载文件避免文件名乱码

PHP 中，如果要下载的文件名称为中文，在低版本的 IE 或 FireFox 浏览器中就会会出现文件标题乱码。此时就需要对标题进行编码，也就是说先进行`urlencode`，再放入`header`，问题就可以解决了。

```php
$ua = _SERVER["HTTP_USER_AGENT"];  

$filename = "中文 文件名.txt";
$encoded_filename = str_replace("+", "%20", urlencode($filename));  

header('Content-Type: application/octet-stream');  

if (preg_match("/MSIE/", $ua)) {
    header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
} elseif (preg_match("/Firefox/", $ua)) {
    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
} else {
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}
```

由于`urlencode()`编码后，会将空格替换成`+`，而 url 中的`+`可能会引起歧义，所以需要将其转成空格的 Unicode 编码`%20`。

> 注意：这个替换必须要在`urlencode()`之后进行，否则`urlencode()`会将`%20`编码成`%2520`。

### 判断是否是合法域名

```php
/**
 * 验证是否是合法域名
 *
 * @param string $domain 域名
 *
 * @return bool
 */
function isDomain ($domain)
{
    return !empty($domain) && strpos($domain, '--') === false &&
        preg_match('/^([a-z0-9]+\.)?[a-z0-9]+([a-z0-9-]*(?:[a-z0-9]+))?\.(us|tv|org\.cn|org|net\.cn|net|mobi|me|la|info|hk|gov\.cn|edu|com\.cn|com|co\.jp|co|cn|cc|biz)$/i', $domain);
}
```

