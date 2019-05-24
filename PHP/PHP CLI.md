一般情况都是使用 PHP 作为脚本语言来开发 web 网站，而其实 PHP 也可以像 shell 一样在命令行状态下执行。从 PHP 5.4 开始，PHP 还内置了一个简单的 web 服务器，可以方便进行测试。

在脚本中可以通过`php_sapi_name()`函数判断是否是在命令行下运行的：

```shell
php -r 'echo php_sapi_name(), PHP_EOL;'  # 输出：cli
```

## 一、CLI 参数详解

使用`php -h`可以查看 PHP 所有的命令行参数。

```
-a               以交互式 shell 模式运行
-c <path>|<file> 指定 php.ini 文件所在的目录
-n               指定不使用 php.ini 文件
-d foo[=bar]     定义一个 INI 实体，key 为 foo，value 为 'bar'
-e               为调试和分析生成扩展信息
-f <file>        解释和执行文件 <file>.
-h               打印帮助
-i               显示 PHP 的基本信息
-l               进行语法检查(lint)
-m               显示编译到内核的模块
-r <code>        运行 PHP 代码 <code>，不需要使用标签 <?..?>
-B <begin_code>  在处理输入之前先执行 PHP 代码 <begin_code>
-R <code>        对输入的每一行作为 PHP 代码 <code> 运行
-F <file>        为每行输入格式化并执行 <file>
-E <end_code>    在每次处理完输入之后执行 <end_code> PHP 代码
-H               隐藏外部输入参数
-S <addr>:<port> 运行内建的 web 服务器.
-t <docroot>     指定用于内建 web 服务器的文档根目录 <docroot>
-s               输出 HTML 语法高亮的源码
-v               输出 PHP 的版本号
-w               输出去掉注释和空格的源码
-z <file>        载入 Zend 扩展文件 <file>

args...          传递给要运行的脚本的参数。当第一个参数以`-`开始或者是脚本是从标准输入读取的时候，使用`--`参数

--ini            显示 PHP 的配置文件名

--rf <name>      显示关于函数 <name> 的信息
--rc <name>      显示关于类 <name> 的信息
--re <name>      显示关于扩展 <name> 的信息
--rz <name>      显示关于 Zend 扩展 <name> 的信息
--ri <name>      显示扩展 <name> 的配置信息
```

## 二、运行

### 2.1 以交互式 shell 模式运行 PHP

[官方文档](http://php.net/manual/en/features.commandline.interactive.php)

使用参数`-a`可以进入 PHP CLI Shell，在这个 Shell 是一个 PHP 代码解释执行器，类似 Chrome 浏览器的 DevTools：

```shell
php -a
```

### 2.2 运行内建 web 服务器

[官方文档](http://php.net/manual/en/features.commandline.webserver.php)

从 PHP 5.4.0 开始，PHP 的命令行模式提供了一个内建的 web 服务器。使用`-S`参数开始运行 web 服务：

```shell
php -S localhost:8000
```

默认情况下，`php -S`会将当前命令行所在目录作为网站根目录，也可以使用`-t`参数来改变网站的根目录：

```shell
php -S localhost:8000 -t public/
```

如果使用了单入口，而且还是用了 PATHINFO 模式，那么直接运行内置服务器可能就有问题了，需要指定一个文件对请求进行预处理：

```shell
php -S localhost:8000 router.php
```

而`router.php`中的代码类似如下：

```php
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// 判断是否存在该文件，如果不存在继续加载入口文件
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
```

### 2.3 命令行脚本开发

在使用 PHP 开发命令行脚本的时候，提供了两个全局变量`$argc`和`$argv`用于获取命令行输入：

* `$argc`包含了`$argv`数组包含元素的数目；
* `$argv`是一个数组，包含了提供的参数，第一个参数总是脚本文件名称。

假设有一个名为`console.php`的命令行脚本文件：

```php
<?php
echo '命令行参数个数: ' . $argc . "\n";
echo "命令行参数:\n";
foreach ($argv as $index => $arg) {
    echo "    {$index} : {$arg}\n";
}
```

在命令行下执行该脚本：
```shell
$ php console.php hello world
命令行参数个数: 3
命令行参数:
    0 : console.php
    1 : hello
    2 : world
```

需要注意的是，如果提供的第一个参数是以`-`开头的话，需要在前面增加`--`，以告诉 php 这后面的参数是提供给脚本作为输入参数的，而不是 php 命令参数：

```shell
php -r 'var_dump($argv);' -- -h
# array(2) {
#  [0]=>
#  string(1) "-"
#  [1]=>
#  string(2) "-h"
# }

php -r 'var_dump($argv);' -h
# 输出 php 的帮助信息
```

## 三、其他应用

### 3.1 查找 php.ini 文件位置

使用`--ini`参数，可以列出当前 PHP 的配置文件信息。

### 3.2 查看类/函数/扩展信息

通常，可以使用`php --info`命令或者在在 web 服务器上的 php 程序中使用函数`phpinfo()`显示 php 的信息，然后再查找相关类、扩展或者函数的信息，这样做实在是麻烦了一些：

```shell
$ php --info | grep redis
redis
Registered save handlers => files user redis
This program is free software; you can redistribute it and/or modify
```

可以使用下列参数更加方便的查看这些信息：

```
--rf <name>      显示关于函数 <name> 的信息.
--rc <name>      显示关于类 <name> 的信息.
--re <name>      显示关于扩展 <name> 的信息.
--rz <name>      显示关于 Zend 扩展 <name> 的信息.
--ri <name>      显示扩展 <name> 的配置信息.
```

### 3.3 语法检查

有时只需要检查 php 脚本是否存在语法错误，而不需要执行它，比如在一些编辑器或者 IDE 中检查 PHP 文件是否存在语法错误。

使用`-l (--syntax-check)`可以只对 PHP 文件进行语法检查：

```shell
$ php -l index.php
No syntax errors detected in index.php
```




