### Guzzle 请求和 set_time_limit

Guzzle 的请求一般是通过异步方式发送的，而使用`set_time_limit()`函数无法限制该请求的时间。如果需要限制请求时间，可以使用`timeout`选项：

```php
$client = new Client([
    'base_uri' => $base_uri,
    'timeout' => 50
]);
```



