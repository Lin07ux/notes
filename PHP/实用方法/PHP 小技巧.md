### 返回当前 php 文件的上级目录
可以先使用`dirname`获取当前文件的路径，然后拼接上级目录的路径`/../`，最后使用`realpath`来获取真实的上级目录路径。

```php
realpath(dirname(__FILE__) . '/../')
```

