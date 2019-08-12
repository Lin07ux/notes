> 转摘：[PHP获取类私有属性的几种方式](https://www.tlanyan.me/ways-to-access-php-class-private-members/)

类中定义的私有属性一般是无法通过类外部的方法来获取的，但是有一些小技巧可以得到类私有属性的值。

### 1. 反射

反射可以获取类的详细信息，要获取私有属性的值，只需将对应属性的`ReflectionProperty`实例设置为可访问，然后再取值即可。

示例代码如下：

```php
class Foo
{
  private $bar = "Foo bar!";
}

// 获取反射类及反射属性
$class = new \ReflectionClass(Foo::class);
$property = $class->getProperty("bar");

// 设置属性可访问
$property->setAccessible(true);

$foo = new Foo;
// 获取对象属性值
// 注意：只能通过 ReflectionProperty 实例的 getValue 方法访问
// 不能这样直接访问： $foo->bar;
echo $property->getValue($foo), PHP_EOL:
// 输出： Foo bar!
```

### 2. 转换成数组

将对象强制转换成数组，再通过键获取其值。将对象转成数组时，每个属性对应的数组的 key 的名称有如下规则：

1. `public`属性，key 是`属性名`；
2. `protected`属性，key 是`\0*\0属性名`；
3. `private`属性，key是`\0类名\0属性名`。

**注意**：`\0`是一个字符（不是两个），对应的 ASCII 码是数字 0，是字符串的结束符。编程时要用**双引号**将其引起来，不能使用单引号，否则转义失效，那就是两个字符。

**注意**：拼接`private`属性时类名应该是“完全限定类名”，建议通过类似`Foo::class`的方式获取。

示例代码如下：

```php
class Foo
{
  private $bar = "Foo bar!";
}

$foo = new Foo;

// 强制转型
$attrs = (array)$foo;

// 拼接key，注意 "\0" 不能改成单引号！
$key = "\0" . Foo::class . "\0" . "bar";

// 输出： Foo bar!
echo $attrs[$key], PHP_EOL;
```

### 3. 闭包和 call

`call`是 PHP 7 中引入的一个方法，可以被一个闭包变量调用，表示将闭包中的`$this`绑定到`call`传入的对象后，执行闭包。对应的，在 PHP 5.4 引入了`bindTo`方法，和`call`类似。

示例如下：

```php
class Foo
{
  private $bar = "Foo bar!";
}

$foo = new Foo;

// 闭包（匿名函数）是 PHP5.3 引入的功能
$closure = function() { return $this->bar; };

// PHP 5.4 起支持 bindTo 方法
$method = $closure->bindTo($foo, Foo::class);
// 输出：Foo bar!
echo $method(), PHP_EOL;

// PHP7 引入 call 方法，可绑定 this 直接执行
// 输出：Foo bar!
echo $closure->call($foo), PHP_EOL;
```

`bindTo`方法的第二个参数注意传入对象的“完全限定类名”，指示函数应该放置在该类的作用域下，从而可以访问私有属性。

### 4. 总结

* 性能：数组 > 反射 > 闭包
* 易用性：闭包 > 数组 > 反射
* 推荐：闭包 > 反射 > 数组


