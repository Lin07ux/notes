### `$request_uri`、`$uri`、`$document_uri`变量的区别

- `$request_uri` 这个变量就是 HTTP 头部的`path`+`query_string`，例如`/my/act?a=1`，即请求的完整路径。

- `$uri` 这个变量对应到服务器上的一个文件(资源), 所以, 可能不等于请求的 URI， 因为可能被 rewrite 过. 例如浏览器请求`/my/act?a=1`，对应的资源(`$uri`) 可能被 rewrite 了，变成了`/dir/file.php`，当然，`query_string`不属于 uri 的一部分。

- `$document_uri` 表示请求的 URI，和`$uri`相同。


### 关闭错误日志

`error_log off`并不能关闭日志记录功能，而是将日志文件写入一个文件名为`off`的文件中。

如果你想关闭错误日志记录功能，应使用以下配置： 

```conf
error_log /dev/null crit; 
```


