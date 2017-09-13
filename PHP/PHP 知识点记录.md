## static 后期静态绑定

PHP 类内部调用其自身内部的静态方法时，有两种方式：`self::static_func()`、`static::static_func()`，这两种方式大部分情况下，效果是一样的，但是也有一些特别的情况下会有所区别。

```php
class A {
    public static function who() {
        echo __CLASS__;
    }
    
    public static function test() {
        // self::who();
        static::who(); // 后期静态绑定
    }
}

class B extends A {
    public static function who() {
        echo __CLASS__;
    }
}

B::test();
```

上面的示例中，如果是`self::who()`调用，会输出：`A`。如果是`static::who()`会输出`B`。这就是 Static 的后期静态绑定。

> 注意：如果 B 中没有重写`who()`方法，那么输出都会是`A`。


## json_encode()、json_decode()

`json_encode`后保持中文编码函数：`json_encode("试试", JSON_UNESCAPED_UNICODE);`

`json_decode`默认情况下，会把 json 解码成一个对象，如果要转成关联数组，则需要设置第二个参数为 true：`json_decode($arr, true);`

## PHP 对特殊字符串的处理方案

普通的字符串截取，一般采用`substr()`或者`mb_substr()`。但这两个方法仅限于英文阿拉伯数字的处理，对中文的截取处理是很不友好的。不仅涉及到字符编码 gbk 和 utf-8 的区分，还有一些乱码情况。因此对于字符串的截取，特别是包含中文的字符串，首推正则匹配处理：

`preg_match_all(regexp, string, resultArray);`

`preg_replace(regexp, replaceString, string);`

```php
$chstr = "交通运输大类";
$match = "/类|类大/";
$chstr = preg_replace($match,"",$chstr);
echo $chstr;	// "交通运输大"
```

## PHP 字符集相关函数

* 如果不清楚字符串的编码格式，可以用`mb_detect_encoding`函数来进行检查。

* 如果要转换编码，使用函数`iconv()`，如将 GB2312  转 UTF-8：`iconv("GB2312","UTF-8",$text);`

* 当遇到无法确定原编码是何种编码，或者`iconv()`转化后无法正常显示时可用`mb_convert_encoding()`函数：`$str = mb_convert_encoding($str, "UTF-8");`

> 其实在 php.ini 中有个选项设置 `default_charset = "UTF-8"; `，很多字符串处理函数如`htmlentities()`会使用这个默认字符集。

## 隐藏 PHP 信息

一些简单的方法可以帮助隐藏 PHP，这样做可以提高攻击者发现系统弱点的难度。

* 在`php.ini`文件里设置`expose_php = off`，可以去除响应头信息中的`X-Powered_By`，可以减少他们能获得的有用信息。
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


## 系统常量

* `__FILE__` 当前 PHP 文件的相对路径
* `__LINE__` 当前 PHP 文件中所在的行号
* `__FUNCTION__` 当前函数名，只对函数内调用起作用
* `__CLASS__` 当前类名，只对类起作用
* `__METHOD__` 表示类方法名，比如 B::test
* `PHP_VERSION` 当前使用的 PHP 版本号
* `PHP_OS` 当前 PHP 环境的运行操作系统
* `E_ERROR` 最近的错误之处
* `E_WARNING` 最近的警告之处
* `E_PARSE` 剖析语法有潜在问题之处
* `$_SERVER` 返回服务器相关信息，返回一个数组
* `$_GET` 所有 GET 请求过来的参数
* `$_POST` 所有 POST 过来的参数
* `$_COOKIE` 所有 HTTP 提交过来的 cookie
* `$_FILES` 所有 HTTP 提交过来的文件
* `$_ENV` 当前的执行环境信息
* `$_REQUEST` 相当于`$_POST`、`$_GET`、`$_COOKIE`提交过来的数据，因此这个变量不值得信任
* `$_SESSION` session 会话变量

## 大小写

PHP 中可以忽略大小写的东西有：

* 用户定义类
* 用户定义函数
* 内置结构
* 关键字

剩下的基本都是大小写敏感的，当然，一定要记住 **变量是区分大小写的**！

## PHP 标签

### 标签类型

#### 1. XML 型标签

这个标签中的 php 的声明不是大小写敏感的，你可以`<?PhP ... ?>`也是完全可行的。

```php
<?php echo "In PHP Tag~"?>  
```

#### 2. 短标签（SGML 型标签）

短标签有两种，一种是需要在 php.ini 配置文件中配置的，另一种是不需要配置的。

**`<? ?>`**

比如：`<? echo "In Tag!" ?>`

这种标签是否发挥作用，取决于你的 PHP 配置是否开启了`short_open_tag`。

需要说明的是，一旦使用关闭了`short_open_tag`的话，`<? ... ?>`的内容是不会显示在用户界面上的，也就是这些东西直接不见了，也不会执行，就当是被 DROP 掉了吧~

**<?=...?>**

比如：`<?="In Tag!"?>`

这个标签并不需要开启`short_open_tag`就可以起作用，缺点就是这个标签相当于一个`echo`语句，所以用法也相当受到限制：

```php
// 输出一个字符串
<?='This short Tag just for echo~'?>
// 函数调用
<?=test()?>
```

#### 3. ASP 风格标签

如果想要使用这种风格的标签，需要确保`asp_tags`打开。并且一定要注意的是，这个和短标签的区别是：当短标签配置是关闭的时候，短标签（包括短标签内部）的东西是不会让用户看到的！然而如果`asp_tags`关闭时，你使用这种标签就会造成他的内容被用户看到，包括 ASP 风格标签和标签内部的内容。 

```php
<% echo 'IN TAG!' %>  
```

#### 4. Script 风格标签

这个标签类型大家可能之前也还是见过的：

```php
<script language=PhP>Echo 'In Tags'</script>
```

这个用法中`script`、`language`、`php`的大小写可以随意转换。

### 标签的 Trick

根据上面的介绍，可以写出如下的代码：

```php
<?php
FuNcTiON test(){
?>
<?php echo 'This is in the test function'?>
<? Echo '<br>Short Tag may be useful' ;?>

   <script language=Php>echo '<br> Now in script style !';};</script>

<br>

<?=test()?>
```

把一个`test`函数肢解在了三种标签中，最后使用`<?=?>`短标签来调用，函数的定义并没有被破坏，而且可以成功调用。

## PHP 松散比较

![PHP 松散比较表格](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1479796890234.png)


