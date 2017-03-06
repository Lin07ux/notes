线上运行的代码，出于各种各样的情况，可能会有好多 Fatal Error、Exception。通过 PHP 自带的一些函数，能够在出现Fatal Error、Exception的时候自动捕获，并写到 Log 文件里。

有四个相关的函数可以相互配合完成这个功能。

> 转摘：[PHP超实用系列·自动捕获FATAL ERROR](http://www.talkpoem.com/post/design-patterns/error_hadler)

### 1. set_error_handler($callback)

设置一个用户的函数来处理脚本中出现的错误。回调函数有四个参数，分别表示错误代码、错误描述、发生错误的文件、发生错误的行数。
    
该方法只能捕获系统产生的一些 Warning、Notice 级别的 Error。以下级别的错误不能由用户定义的函数来处理：E_ERROR、E_PARSE、E_CORE_ERROR、E_CORE_WARNING、E_COMPILE_ERROR、E_COMPILE_WARNING，和在调用`set_error_handler()`函数所在文件中产生的大多数 E_STRICT。
    
```php
<?php
set_error_handler("error_handler");
function error_handler($errno,$errstr,$errfile,$errline){
   $str=<<<EOF
        "errno":$errno
        "errstr":$errstr
        "errfile":$errfile
        "errline":$errline
EOF;
//获取到错误可以自己处理，比如记Log、报警等等
   echo $str;
}
echo $test;//$test未定义，会报一个notice级别的错误
```
    
### 2. set_exception_handler($callback)

设置一个用户的函数来处理脚本中出现的异常。回调函数可以接受一个 Exception 类型的对象。
    
```php
<?php
//设置异常捕获函数
set_exception_handler("my_exception");
function my_exception($exception){
   echo 'Exception Catched:'.$exception->getMessage();
}
    
//抛出异常
throw new Exception("I am Exception");
```
    
当执行这个脚本的时候，就会提示：`Exception Catched: I am Exception`，说明是能够正常捕获到这个异常了。
    
### 3. register_shutdown_function($callback)

该函数把要注册进去的 function 放进类似队列的地方，等到脚本正常退出或显式调用`exit()`时，再把注册进去的 function 拉出来执行。其在下面三种情况下会被执行：
    
* 脚本正常退出时；
* 在脚本运行(run-time not parse-time)出错退出时；
* 用户调用exit方法退出时

需要特别注意的是：**在 parse-time 出错的时候，是不会调用该函数的。只有在 run-time 出错的时候，才会调用**。

**示例 1**

```php
<?php
register_shutdown_function("error_handler");
function error_handler () {
    echo "Yeah,it's worked!";
}

function test () {}
function test () {}
```

在执行该脚本的时候，由于重复定义了两个函数`test()`，在 php 的 parse-time 就出错了（不是run-time），所以不能回调`register_shutdown_function()`中注册的函数，也就无法捕捉到错误了。

**示例 2**

```php
<?php
register_shutdown_function("error_handler");
function error_handler () {
    echo "Yeah,it's worked!";
}

if (true) {
   function test () {}
}
function test () {}
```

更改前面的例子，增加一个条件语句，这样就能够捕获错误了。因为`if()`里面的`test()`方法相当于在一个闭包中，与外面的`test()`名称不冲突。所以上面的代码在 parse-time 没有出错，而在 run-time 的时候出错了，所以我们能够获取到 fatal error。

**示例 3**

error_handler.php

```php
<?php
register_shutdown_function("error_handler");
function error_handler () {
    echo "Yeah, it's worked!";
}
```

test.php

```php
<?php
include './error_handler.php';
function test () {}
function test () {}
```

执行`test.php`脚本的时候，并不会捕获错误，因为在`test.php`脚本中重复定义了两个`test()`函数，所以 php 的语法解析器在 parse-time 的时候就出错了。

**示例 4**

更改上面的 test.php 文件：

```php
<?php
function test () {}
function test () {}
```

然后添加一个新的文件：include_all.php

```php
<?php
require './error_handler.php';
require './test.php';
```

这时候执行`include_all.php`脚本的时候就能够捕获到错误了。因为在运行`include_all.php`的时候，`include_all.php`本身语法并没有出错，也就是在 parse-time 的时候并没有出错，而是`include`的文件出错了，也就是在 run-time 的时候出错了，这个时候是能回调`register_shutdown_function()`中的函数的。

所以，强烈建议：如果我们要使用`register_shutdown_function`进行错误捕捉，要使用示例 4 中的方法，可以确保错误都能捕捉到。


### 4. error_get_last()

获取脚本结束运行前，最后发生的错误。
    
该函数以数组的形式返回最后发生的错误，包含 4 个键和值：

* [type] - 错误类型
* [message] - 错误消息
* [file] - 发生错误所在的文件
* [line] - 发生错误所在的行

### 总结

结合上面的解释和示例，可以写一个专门的脚本来捕获对应的错误信息。

```php
<?php
register_shutdown_function( "fatal_handler" );
set_error_handler("error_handler");

define('E_FATAL',  E_ERROR | E_USER_ERROR |  E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR| E_PARSE);

// 获取 fatal error
function fatal_handler() {
    $error = error_get_last();
    
    if($error && ($error["type"]===($error["type"] & E_FATAL))) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        
        error_handler($errno,$errstr,$errfile,$errline);
    }
}

// 获取所有的 error
function error_handler($errno, $errstr, $errfile, $errline) {
    $str = <<<EOF
         "errno": $errno
         "errstr": $errstr
         "errfile": $errfile
         "errline": $errline
EOF;

    // 获取到错误可以自己处理，比如记 Log、报警等等
    echo $str;
}
```


