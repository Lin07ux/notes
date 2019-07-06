### 1. 标量类型提示以及返回类型

`int`，`float`，`string`和`bool`类型现在也都可以使用类型提示符了，而且函数或方法的返回值类型也可以进行提示了。

```php
<?php
function isValidStatusCode(int $statusCode): bool
{
    return isset($this->statuses[$statusCode]);
}
```

函数方法的`return`的返回类型提示，跟 PHPDoc 的`@return`注解是完全不同的两个方面：

* `@return`只是“好言规劝”或向 IDE “友好反馈”返回类型应该是什么，而对于实际的返回类型不具约束力。
* `return`的返回类型提示则具有对返回类型的运行时强制约束力。

### 2. 严格类型约束

默认情况下，对于标量类型提示，参数也罢、返回值也罢，其类型跟类型提示不一致也不影响程序运行（注：对象及数组类型具备约束力，注意区别）。

这可能不是想要的效果，解决办法就是在 PHP 脚本文件的第一条语句的位置放上：`declare(strict_types=1);`。这是个文件级别的指令，同时不影响其他包含文件——主要是考虑向后兼容及不影响各类扩展、内建代码。

开启严格类型约束之后，如果函数类型提示检测到不匹配的情况，就会产生一个可捕获的致命错误：

```php
<?php
declare(strict_types=1); // must be the first line

sendHttpStatus(404, "File Not Found"); // integer and string passed
sendHttpStatus("403", "OK");

// Catchable fatal error: Argument 1 passed to sendHttpStatus() must be of the type integer, string given
```

需要注意的是：**严格模式是由调用函数的文件决定的，而不是声明函数的文件决定的。**

### 3. 可空类型

PHP 7.1 引入了可空类型，对于同一类型的强制要求，可以设置其是否可空。但是需要注意的是，可空类型还是需要传入参数，而非可选参数。

```php
function foo (?int $in): int
{
    return $in ?? 0;
}

foo(null); // 0
foo(1); // 1
foo(); // TypeError: Too few arguments to function foo(), 0 passed ...
```

### 4. Void 返回类型

PHP 7.1 引入了 Void 返回类型，表示方法不返回任何值：

```php
function first(): void {
    // ...
}

function second(): void {
    // ...
    return;
}
```

### 5. iterable 伪类型

PHP 7.1 引入了 iterable 伪类型。iterable 类型适用于数组、生成器以及实现了 Traversable 的对象，它是 PHP 中保留类名。

```php
function fn (iterable $it): iterable {
    $result = [];
    
    foreach ($it as $value) {
        $result[] = $value + 1000;
    }
    
    return $result;
}
```

### 6. 空值合并操作符

空值合并操作符`??`：当左操作数不为 NULL 时返回左操作数，否则返回右操作数。

很重要的一点是：**当左操作数未定义时不会产生提示错误**。此特性更像是`isset()`函数，而不像`?:`三元操作符。

另外，还可以链式使用此操作符，它将返回第一个非空值。

```php
$config = $config ?? $this->config ?? static::$defaultConfig;
```

### 7. 组合比较操作符

组合比较操作符`<=>`也被称作宇宙飞船操作符。它的功能如同`strcmp()`或者`version_compare()`：当左操作数小于右操作数时返回 -1，相等时返回 0，如果左操作数大于右边则返回 1。主要不同在于它可以接受任意类型的操作数，不仅仅只是字符串类型，还包括整形、浮点型、数组等等。

最通常的用法是用于排序回调函数的实现：

```php
<?php
// Pre Spacefaring^W PHP 7
function order_func($a, $b) {
  return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
}

// Post PHP 7
function order_func($a, $b) {
  return $a <=> $b;
}
```

### 8. 可见性修饰符的变化

PHP 7.1 之前的类常量是不允许添加可见性修饰符的，此时类常量可见性相当于`public`。PHP 7.1 为类常量添加了可见性修饰符支持特性。总的来说，可见性修饰符使用范围如下所示：

* 函数/方法：`public`、`private`、`protected`、`abstract`、`final`。
* 类：`abstract`、`final`。
* 属性/变量：`public`、`private`、`protected`。
* 类常量：`public`、`private`、`protected`。

```php
class YourClass 
{
    const THE_OLD_STYLE_CONST = "One";

    public const THE_PUBLIC_CONST = "Two";
    private const THE_PRIVATE_CONST = "Three";
    protected const THE_PROTECTED_CONST = "Four";
}
```

### 9. Unicode 编码的转义语法

新增的转义字符`\u`允许在 PHP 字符串中直接加入 Unicode 编码字符：使用语法如下`\u{编码}`，例如绿色的心形 💚，可以直接用 PHP 字符串表示：`\u{1F49A}`。

### 10. 数组常量

过去在用`define()`定义常量时，数据类型只支持标量，但在 PHP7 中，支持定义数组类型的常量。数组常量的值都是不能修改的。

```php
define('MYCONSTANT', array('a', 'b', 'c'));

MYCONSTANT[0] = 'aa';
// PHP Fatal error:  Cannot use temporary expression in write context in...
```

### 11. 匿名类

顾名思义没有类名称，其声明和实例化是同时的，PHP7 支持通过`new class`来实例化一个匿名类，可以用来替代一些”用后即焚”的完整类定义。

```php
echo (new class() {
    public function myMethod ()
    {
        return "Hello World";
    }
})->myMethod();
```

### 12. use 组合声明

开发工具可以自动将类引入，对于其他没有使用自动完成工具的人来讲，PHP 7 现在提供了成组使用`use`申明的方法。它可以更快地更清晰引入同一个命名空间下的多个类。

```php
<?php
// 以前
use Framework\Component\SubComponent\ClassA;
use Framework\Component\SubComponent\ClassB as ClassC;
use Framework\Component\OtherComponent\ClassD;

// 成组申明
use Framework\Component\{
  SubComponent\ClassA,
  SubComponent\ClassB,
  OtherComponent\ClassD
};

// 它也可以与常量和函数的导入混用
use Framework\Component\{
  SubComponent\ClassA,
  function OtherComponent\someFunction,
  const OtherComponent\SOME_CONSTANT
};
```

### 13. Throwable 接口

PHP 5 的`try ... catch ... finally`无法处理传统错误，如果需要，通常会考虑用`set_error_handler()`来 Hack 一下，但是仍有很多错误类型是其捕捉不到的。

PHP 7 引入 Throwable 接口，错误及异常都实现了 Throwable。无法直接实现 Throwable，但可以扩展`\Exception`和`\Error`类：

* `\Exception`是所有 PHP 及用户异常的基类
* `\Error`是所有内部 PHP 错误的基类

可以用 Throwable 捕捉异常跟错误：

```php
$name = "Tony";
try {
    $name = $name->method();
} catch (\Error $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
}

try {
    $name = $name->method();
} catch (\Throwable $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
}

try {
    intdiv(5, 0);
} catch (\DivisionByZeroError $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
}
```

### 14. 引擎异常

在之前的 PHP 中处理并捕获致命错误几乎是不可能的。但随着新增加的引擎异常，许多这些错误将能够转化为异常抛出：当一个致命错误发生时，将抛出异常，这样你就可以优雅地处理它了。如果你不去处理它，则会产生传统的不可捕获的致命错误。

这些异常是`\EngineException`的实例，且与用户空间的异常不同，它并没有继承自基础的`\Exception`类。这样做是为了确保现有代码中捕获`\Exception`的地方不会变得能够捕获到这些致命错误并继续执行下去。这样就保证了代码向后兼容。

以后，如果想一起捕获传统的异常和引擎异常，那么需要使用到新增的、它们所共有的父类`\BaseException`。另外，在`eval()`函数使用时的解析错误会抛出一个`\ParseException`，同时类型不匹配会抛出一个`\TypeException`异常。

```php
<?php
try {
  nonExistentFunction();
} catch (\EngineException $e) {
  var_dump($e);
}
```

输入如下：

```
object(EngineException)#1 (7) {
  ["message":protected] => string(32) "Call to underfined function nonExistantFunction()"
  ["string":"BaseException":private] => string(0) ""
  ["code":protected] => int(1)
  ["file":protected] => string(17) "engine-exceptions.php"
  ["line":protected] => int(1)
  ["trace":"BaseException":private] => NULL
}
```

### 15. 一次捕捉多种类型的异常/错误

PHP 7.1 新添加了捕获多种异常/错误类型的语法——通过竖杠`|`来实现：

```php
try {
    throw new LengthException("LengthException");
    // throw new DivisionByZeroError("DivisionByZeroError");
    // throw new Exception("Exception");
} catch (\DivisionByZeroError | \LengthException $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
} catch (\Exception $e) {
    echo "出错消息 --- ", $e->getMessage(), PHP_EOL;
} finally {
    // ...
}
```

### 16. 闭包调用时绑定

PHP 5.4 版本时，提供了`Closure->bindTo()`和`Closure::bind()`方法，用于改变闭包中`$this`的绑定对象和作用域，同时复制一个新的闭包。

PHP 7 加入了一个更加简单的方法`Closure->call()`，使其在调用时绑定`$this`和作用域。此方法将绑定的对象作为第一个参数，其后的参数作为闭包参数传入，如下：

```php
<?php
class HelloWorld {
  private $greeting  = "Hello";
}

$closure = function ($whom) { echo $this->greeting . ' ' . $whom; }

$obj = new HelloWorld();
$closure->call($obj, 'World'); // Hello World
```

### 17. 生成器的改进

生成器加入了两个新特性：允许使用`return`语句、生成器代理。

#### 17.1 生成器使用 return 语句

生成器使用 Return 语句允许生成器执行完毕后，返回一个值。

在 PHP 7 之前，如果尝试返回任何东西都会报错。现在可以调用`$generatro->getReturn()`来取得返回值了。如果生成器还没有返回，或者已经抛出异常了，那么执行`$generator->getReturn()`就会抛出异常。如果没有`return`语句的生成器执行完毕，则返回 null 值。

```php
<?php
function gen() {
  yield "Hello";
  yield " ";
  yield "World!";
  
  return "Goodbye Moon!";
}

$gen = gen();
foreach ($gen as $value) {
  echo $value;
}
// 第一次输出 "Hello"，接着是" ", "World!"

echo $gen->getReturn(); // Goodbye Moon!
```

#### 17.2 生成器代理

生成器代理允许在一个生成器中使用另一个可以迭代的结构，并使其被遍历，如同在原生成器中被定义一样。

子结构的迭代是由最外层的循环来完成的，使用单层次的循环而非以递归的方式。当传递数据或异常给生成器时，就像直接调用子结构一样，也是直接传递给子结构的。

代理语法`yield from`，如下：

```php
<?php
function hello() {
  yield "Hello";
  yield " ";
  yield "World!";
  
  yield from goodbye();
}

function goodbye() {
  yield "Goodbye";
  yield " ";
  yield "Moon!";
}

$gen = hello();
foreach ($gen as $value) {
  echo $value;
}
```

每一次迭代的输出如下：

1.	“Hello”
2.	“ “
3.	“World!”
4.	“Goodbye”
5.	“ “
6.	“Moon!”

有一点需要小心：因为子结构自身也可能被迭代产生值，所以完全有可能同一个值被多个迭代所返回——如果这不是你想要的，则需要你自己去避免。

### 转摘

1. [PHP 7 有些什么值得期待？（二）](https://log.zvz.im/2016/01/17/PHP7-2/)
2. [PHP 7 有些什么值得期待？（一）](https://log.zvz.im/2015/10/24/PHP7-1/)
3. [PHP 7 新特性（完结篇）](https://zhuanlan.zhihu.com/p/29478077)

