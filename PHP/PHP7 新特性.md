### 1. 标量类型声明
我们知道PHP是一种弱类型的编程语言,因此没有提供任何方法来指定输入参数和返回值的类型，PHP7突破了这种现状，增加了对标量类型（int、float、string、bool）的声明支持，增加`declare(strict_types=1)`指令声明是否严格类型校验。

目前有效的类型有：`class/interface name`、`self`、`array`、`callable`、`bool`、`float`、`int`和`string`。

```php
declare(strict_types=1);
function add(int $x, int $y) : int {
    return $x + $y;
}
```

### 2. NULL 合并运算符
PHP7 中添加了 NULL 合并运算符`??`，有了它我们就能很方便的获取一个参数，并能在其为空的情况下提供一个默认值。如果`??`运算符左侧值存在并且不为 NULL，则返回左侧，否则将返回其右侧值。

这个操作符可以连用，表示：从左往右第一个存在且不为 NULL 的操作数。如果都没有定义且不为 NULL，则返回 NULL。

```php
// PHP5 中如下实现
$username = isset($_GET['user']) ? $_GET['user'] : 'nobody';

// PHP7 中如下实现
$username = $_GET['user'] ?? 'nobody';

// 还可以连用
$username = $_GET['user'] ?? $_GET['name'] ?? 'nobody';
```

### 3. 匿名类
顾名思义没有类名称，其声明和实例化是同时的，PHP7 支持通过 new class 来实例化一个匿名类，可以用来替代一些”用后即焚”的完整类定义。

```php
echo (new class() {
    public function myMethod ()
    {
        return "Hello World";
    }
})->myMethod();
```

### 4. 结合比较运算符 <=>
`$a <=> $b`，当`$a`小于、等于、大于`$b`时，分别返回一个小于、等于、大于 0 的 integer 值。这就是结合比较运算符的作用。

```php
// 以前比较大小的时候，需要这样写
$c = $a > $b ? 1 : ( $a==$b ? 0 : -1 );

// 使用结合比较运算符就很简单了
$c = $a <=> $b;
```
### 5. 定义数组常量
过去我们在用`define()`定义常量时，数据类型只支持标量，但在 PHP7 中，支持定义数组类型的常量。

```php
define('MYCONSTANT', array('a','b','c'));
```




