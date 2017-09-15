### PHP 传递数组序列化后的字符串给 JavaScript

当需要将一个 PHP 数组通过 json 序列化后的字符串形式传递给 JavaScript，然后后者通过`JSON.parse()`方法进行解析的时候，有可能会遇到原 PHP 数组中的内容包含引号，从而导致 JavaScript 中的解析出错。

此时可以考虑使用 PHP 中的`addslashes()`方法来将序列化之后的字符串进行处理，以便将其中的引号(单引号、双引号)、反斜线(`\`)、null 进行特殊的转义处理，这样就可以避免出现问题了。

```php
addslashes(json_encode($array, JSON_UNESCAPED_UNICODE));
```



