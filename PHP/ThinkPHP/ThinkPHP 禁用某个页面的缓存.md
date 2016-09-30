如果要禁用所有页面的缓存，可以在 config.php 文件中设置`HTTP_CACHE_CONTROL`为对应的值，如`no-store, no-cache, must-revalidate, post-check=0, pre-check=0
`。

而如果要单独设置某个页面禁用缓存，可以在对应的控制器中的 ACTION 中重写`HTTP_CACHE_CONTROL`，并设置其他的一些缓存控制头信息：

```php
C('HTTP_CACHE_CONTROL', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT'); header('Pragma: no-cache'); // 兼容 http1.0 和 https
```



