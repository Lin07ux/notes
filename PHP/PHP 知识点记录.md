## json_encode()、json_decode()
json_encode 后保持中文编码函数：`json_encode("试试", JSON_UNESCAPED_UNICODE);`

json_decode 默认情况下，会把 json 解码成一个对象，如果要转成关联数组，则需要设置第二个参数为 true：`json_decode($arr, true);`

## 隐藏 PHP 信息
一些简单的方法可以帮助隐藏 PHP，这样做可以提高攻击者发现系统弱点的难度。

* 在`php.ini`文件里设置`expose_php = off`，可以减少他们能获得的有用信息。
* 另一个策略就是让 web 服务器用 PHP 解析不同扩展名。无论是通过`htaccess`文件还是 Apache 的配置文件，都可以设置能误导攻击者的文件扩展名。

**使PHP看上去像其它的编程语言**

`AddType application/x-httpd-php .asp .py .pl`

**使 PHP 看上去像未知的文件类型**

`AddType application/x-httpd-php .bop .foo .133t`

**使 PHP 代码看上去像 HTML 页面**

`AddType application/x-httpd-php .htm .html`

要让此方法生效，必须把 PHP 文件的扩展名改为以上的扩展名。这样就通过隐藏来提高了安全性，虽然防御能力很低而且有些缺点。

> 参考：[隐藏 PHP](http://php.net/manual/zh/security.hiding.php)

## include/include_once/require/require_once
* include 和 include_once 加载的文件不存在，或者文件内代码执行出错的时候，会继续执行 include/include_once 语句之后的代码；require/require_once 则会直接报错并停止执行后续代码。
* include 和 require 可以加载同一个文件多次；include_once 和 require_once 会保证同一个文件只被加载一次，如果之前该文件已经通过任何方式被引用，则不会再次加载该文件了。
* include 和 include_once 一般是放在流程控制的处理部分中使用，将文件内容引入；而 require 和 require_once 不受条件流程的控制，只要代码中出现了 require 和 require_once，则其所加载的文件都会被载入。
* include 和 include_once 执行时需要引用的文件每次都要进行读取和评估，且有返回值，所有执行速度稍慢。
* 从理论上说，include 和 require 后面加不加括号对执行的结果并没有什么区别，但是加上括号效率相对会较低，所以通常后面能不加括号就不要添加括号了！

示例：假设有一个文件中 a.php，里面只有一句`echo file name is a;`，下面的代码分别会输出不同数量的内容。

```php
<?php
 
echo 'include: ';
include 'a.php';

echo '<br>require: ';
require 'a.php';

echo '<br>include_once: ';
include_once 'a.php';

echo '<br>require_once: ';
require_once 'a.php';

// include: file name is a
// require: file name is a
// include_once: 
// require_once:
```

会输出两行`file name is a`。


```php
<?php

echo 'include_once: ';
include_once 'a.php';

echo '<br>require_once: ';
require_once 'a.php';
 
echo '<br>include: ';
include 'a.php';

echo '<br>require: ';
require 'a.php';

// include_once: file name is a
// require_once: 
// include: file name is a
// require: file name is a
```

会输出三行`file name is a`，其中 require_once 这一句没有输出，因为在他前面已经用 include_once 引入了 a.php 文件了。


