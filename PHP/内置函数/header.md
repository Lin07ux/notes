`header()`函数的作用是：发送一个原始 HTTP 标头[Http Header]到客户端。
标头 (header) 是服务器以 HTTP 协义传 HTML 资料到浏览器前所送出的字串，在标头
与 HTML 文件之间尚需空一行分隔。在 PHP 中送回 HTML 资料前，需先传完所有的标头。

一般来说在 header 函数前不能输出 html 内容，类似的还有 setcookie() 和 session 函数，这些函数需要在输出流中增加消息头部信息。如果在 header() 执行之前有 echo 等语句，当后面遇到 header() 时，就会报出 “Warning: Cannot modify header information - headers already sent by ….”错误。就是说在这些函数的前面不能有任何文字、空行、回车等，或者修改 php.ini 打开缓存(output_buffering)。

## 使用范例

* 1. 使浏览器重定向

    ```php
    <?php
        header("Location: http://www.php.net";);
        exit;   // 在每个重定向之后都必须加上“exit",避免发生错误后，继续执行
    ```

* 2. 定时跳转

    ```php
    <?php
        // header重定向就等价于替用户在地址栏输入url
        header("refresh:3;url=http://axgle.za.net");
        print('正在加载，请稍等...<br>三秒后自动跳转~~~');
    ```

* 3. 禁止页面缓存

    要使用者每次都能得到最新的资料，而不是 Proxy 或 cache 中的资料，可以使用下列的标头：
    
    ```php
    header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
    header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
    header( 'Cache-Control: no-store, no-cache, must-revalidate' );
    header( 'Cache-Control: post-check=0, pre-check=0', false );
    header( 'Pragma: no-cache' ); //兼容 http1.0 和 https
    ```
    
    Expires，如果服务器上的网页经常变化，就把它设置为 -1，表示立即过期。如果一个网页每天凌晨 1 点更新，可以把 Expires 设置为第二天的凌晨 1 点。当 HTTP1.1 服务器指定`Cache-Control = no-cache`时，浏览器就不会缓存该网页。
    
    旧式 HTTP 1.0 服务器不能使用 Cache-Control 标题。所以为了向后兼容 HTTP 1.0 服务器，IE 使用`Pragma:no-cache` header 对 HTTP 提供特殊支持。如果客户端通过安全连接 (https://) 与服务器通讯，且服务器在响应中返回`Pragma:no-cache` header，则 Internet Explorer 不会缓存此响应。
    
    > 注意：`Pragma:no-cache`仅当在安全连接中使用时才防止缓存，如果在非安全页中使用，处理方式与`Expires:-1`相同，该页将被缓存，但被标记为立即过期。
    > 
    > 在 html 页面中可以用`http-equiv meta`来标记指定的 http 消息头部。老版本的 IE 可能不支持 html meta 标记，所以最好使用 http 消息头部来禁用缓存。

* 4. 发出 404 状态

    ```php
    header(”http/1.1 404 Not Found”);
    ```

* 5. 下载档案(隐藏文件的位置)

    html 标签就可以实现普通文件下载。如果为了保密文件，就不能把文件链接告诉别人，可以用 header 函数实现文件下载（需在 php 中读取文件并输出）。
    
    ```php
    <?php
    header("Content-type: application/x-gzip");
    header("Content-Disposition: attachment; filename=文件名");
    header("Content-Description: PHP3 Generated Data");
    ```


