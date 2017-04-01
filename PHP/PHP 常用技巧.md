### 1. 不要使用相对路径，要定义一个根路径

使用相对路径可能会遇到多种问题：

* 它首先搜索php包括路径中的指定目录，然后查看当前目录。因此，会检查许多目录。
* 当一个脚本被包含在另一个脚本的不同目录中时，它的基本目录变为包含脚本的目录。
* 另一个问题是，当一个脚本从 cron 运行时，它可能不会将它的父目录作为工作目录。 
所以使用绝对路径就很有必要了。可以先定义一个绝对根路径，然后其他的路径都相对这个根路径来处理。如下：

```php
define('ROOT' , '/var/www/project/');
require_once(ROOT . '../../lib/some_class.php');

// 或者借助 __FILE__ 来动态的定义根路径
define('ROOT' , pathinfo(__FILE__, PATHINFO_DIRNAME));
require_once(ROOT . '../../lib/some_class.php');

// suppose your script is /var/www/project/index.php
// Then __FILE__ will always have that full path.
```

### 2. 省略脚本中的最后一行的 php 标签

在 PHP 文件中，如果最后一行是`?>`这个标签，建议省略掉。这样可以避免一些意外的问题，比如在结束标签后还有其他一些特殊字符，就会造成异常错误。

比如：

```php
<?php
class super_class
{
    function super_function()
    {
        //super code
    }
}
?>
// super extra character after the closing tag
```

然后在另一个文件中引用该文件：

```php
require_once('super_class.php');

// echo an image or pdf , or set the cookies or session data
```

此时就会出现错误的 Header。而如果去除掉`?>`这个结束标签，则完全没有问题了。


