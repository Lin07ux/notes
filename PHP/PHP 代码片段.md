### 检查日期是否是指定的格式
```php
function checkDatetime($str, $format="Y-m-d H:i:s"){
    $unixTime  = strtotime($str);
    $checkDate = date($format, $unixTime);

    return $checkDate == $str;
}
```

### 获取当前页面的URL

```php
/**
 * 获取当前页面完整URL地址
 */
function get_url() {
    // 协议
    $protocol = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'
        ? 'https://' : 'http://';

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

    // 脚本名称
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

    // PATH_INFO
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';

    $relate_url = isset($_SERVER['REQUEST_URI'])
        ? $_SERVER['REQUEST_URI']
        : $php_self . (isset($_SERVER['QUERY_STRING'])
            ? '?' . $_SERVER['QUERY_STRING'] : $path_info);

    return $protocol . $host . $relate_url;
}
```


### 解析url中的query参数为数组

```php
/**
 * 获取url中的?后的查询参数
 *
 * @param string $url url地址
 *
 * @return array
 */
function convertUrlQuery($url)
{
    $arr    = parse_url($url);
    $query  = explode('&', $arr['query']);

    $params = array();
    foreach ($query as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }

    return $params;
}
```

> 参考：[PHP解析URL并得到URL中的参数](http://blog.csdn.net/wide288/article/details/17712989)

### 文件下载
对于浏览器不能直接打开的文件，比如 .zip、.exe、.xsl 等，可以直接使用一个 a 元素来指向这个文件资源，点击就能直接下载。而对于 .jpg 等文件，如果直接点击链接，就是在浏览器中打开了，而不是提示我们下载保存。

我们是通过 Header 请求头来发送文件下载信息，指定下载的是附件，下载后的文件名，content-length 来指定文件的大小，然后通过 readfile 函数来读取文件内容而实现文件下载：

```php
<?php 
 
$filename = $_GET['filename'];
header('content-disposition:attachment;filename='. basename($filename));
header('content-length:'. filesize($filename));
 
readfile($filename);
```

