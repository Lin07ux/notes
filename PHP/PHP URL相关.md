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

