`$_SERVER['SCRIPT_FILENAME']`是客户端请求的资源文件的路径；`__FILE__`是当前运行脚本所在的资源文件路径。

初看它们好像应该是同一个文件。确实，一般情况下，它们会是同一个文件，但是当有文件引用(`include`或`require`)的时候，它们就有区别了。看下面的例子。

文件 a.php：

```php
<?php
include 'b.php';
```

文件 b.php：

```php
<?php
define('s', $_SERVER['SCRIPT_FILENAME']);
define('f', __FILE__);

echo s, '<br>', f;
```

1. 此时，访问 a.php 的时候，会显示类似如下的输出：

/usr/share/nginx/html/a.php;
/usr/share/nginx/html/b.php;

也即是说，访问 a.php 的时候，在 b.php 文件中`$_SERVER['SCRIPT_FILENAME']`和`__FILE__`分别是 a.php 和 b.php 文件的绝对路径。

2. 再看直接访问 b.php 的时候，会显示类似如下的输出：

/usr/share/nginx/html/b.php;
/usr/share/nginx/html/b.php;

也即是说，直接访问 b.php 的时候，`$_SERVER['SCRIPT_FILENAME']`和`__FILE__`都是 b.php 文件的绝对路径。

综上，我们的结论是：

- 共同点：这两者返回的都是 php 文件的绝对路径，在没有引用包含的情况下，他们是相同的。
- 不同点：如果有包含关系的时候，`$_SERVER['SCRIPT_FILENAME']`永远指向的是当前请求资源的绝对路径及文件名，而`__FILE__`则是指向原始文件(被包含文件)的绝对路径和文件名。


