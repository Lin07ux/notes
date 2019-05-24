PHP 中把以两个下划线`__`开头的方法称为魔术方法(Magic methods)，这些方法在 PHP 中充当了举足轻重的作用。目前 PHP 中的魔术方法有十六个：

### __construct() 类的构造方法

PHP 中构造方法是对象创建完成后第一个被对象自动调用的方法。在每个类中都有一个构造方法，如果没有显示地声明它，那么类中都会默认存在一个没有参数且内容为空的构造方法。

通常构造方法被用来执行一些有用的初始化任务，如对成员属性在创建对象时赋予初始值。 
### __destruct() 类的析构方法

与构造方法对应的就是析构方法。析构方法允许在销毁一个类之前执行的一些操作或完成一些功能，比如说关闭文件、释放结果集等。

> 析构方法是 PHP5 才引进的新内容。
 
### __call() 在对象中调用一个不可访问方法时调用

为了避免当调用的方法不存在时产生错误，而意外的导致程序中止，可以使用`__call()`方法来避免。该方法在调用的方法不存在时会自动调用，程序仍会继续执行下去。

该方法有两个参数，第一个参数会自动接收不存在的方法名，第二个参数则以数组的方式接收不存在方法的多个参数。

`__call()` 方法的格式如下：

```php
function __call(string $function_name, array $arguments)
{
    // 方法体
}
```

### __callStatic() 用静态方式中调用一个不可访问方法时调用

此方法与上面所说的`__call()`功能基本相同，除了`__callStatic()`是在调用不存在的**静态方法**时被自动调用。

### __get() 获得一个类的成员变量时调用

在 PHP 面向对象编程中，类的成员属性被设定为`private`后，如果我们试图在外面调用它则会出现“不能访问某个私有属性”的错误。那么为了解决这个问题，我们可以使用魔术方法`__get()`。

当然，也可以用这个魔术方法来访问实际不存在的类属性。 
### __set() 设置一个类的成员变量时调用

与`__set()`相对的，`__set($property, $value)`方法用来设置私有属性，给一个未定义的属性赋值时，此方法会被触发，传递的参数是被设置的属性名和值。 
### __isset() 当对不可访问属性调用 isset() 或 empty() 时调用

`isset()`是用来测定变量是否设定用的函数，传入一个变量作为参数，如果传入的变量存在则传回 true，否则传回 false。

那么如果在一个对象外面使用`isset()`这个函数去测定对象里面的成员是否被设定时，有两种情况：

* 如果对象里面成员是公有的，我们就可以使用这个函数来测定成员属性；
* 如果是私有的成员属性，这个函数就不起作用了，原因就是因为私有的被封装了，在外部不可见。

如果需要在类外部使用`isset()`函数来判断类的私有成员是否存在，只要在类里面加上一`__isset()`方法就可以了。此时当在类外部使用`isset()`函数来测定对象里面的私有成员是否被设定时，就会自动调用类里面的`__isset()`方法了帮我们完成这样的操作。

所以`__isset()`的作用就是：当对不可访问属性调用`isset()`或`empty()`时，`__isset()`会被调用。

### __unset() 当对不可访问属性调用 unset() 时被调用。

`unset()`这个函数的作用是删除指定的变量且传回 true，参数为要删除的变量。那么如果在一个对象外部去删除对象内部的成员属性用`unset()`函数可以吗？

这里也是分两种情况：

* 如果一个对象里面的成员属性是公有的，就可以使用这个函数在对象外面删除对象的公有属性。
* 如果对象的成员属性是私有的，使用这个函数就没有权限去删除。

虽然有以上两种情况，但是同样如果在一个对象里面加上`__unset()`这个方法，就可以在对象的外部去删除对象的私有成员属性了。在对象里面加上了`__unset()`这个方法之后，在对象外部使用`unset()`函数删除对象内部的私有成员属性时，对象会自动调用`__unset()`函数来帮我们删除对象内部的私有成员属性。

### __sleep() 执行 serialize() 时，先会调用这个函数

`serialize()`函数会检查类中是否存在一个魔术方法`__sleep()`。如果存在，则该方法会优先被调用，然后才执行序列化操作。

此功能可以用于清理对象，并返回一个包含对象中所有应被序列化的变量名称的数组。如果该方法未返回任何内容，则 NULL 被序列化，并产生一个 E_NOTICE 级别的错误。

`__sleep()`不能返回父类的私有成员的名字。这样做会产生一个 E_NOTICE 级别的错误。可以用`Serializable`接口来替代。

### __wakeup() 执行 unserialize() 时，先会调用这个函数

`unserialize()`会检查是否存在一个`__wakeup()`方法。如果存在，则会先调用`__wakeup()`方法，预先准备对象需要的资源。

`__wakeup()`经常用在反序列化操作中，例如重新建立数据库连接，或执行其它初始化操作。 
### __toString() 类被当成字符串时的回应方法

`__toString()`方法用于一个类被当成字符串时应怎样回应。例如`echo $obj;`应该显示些什么。

注意：**此方法必须返回一个字符串，否则将发出一条`E_RECOVERABLE_ERROR`级别的致命错误。**

警告：*不能在`__toString()`方法中抛出异常。这么做会导致致命错误。*

### __invoke() 调用函数的方式调用一个对象时的回应方法

当尝试以调用函数的方式调用一个对象时`__invoke()`方法会被自动调用。本特性只在 PHP 5.3.0 及以上版本有效。

```php
<?php
class Person
{
    public $sex;
    public $name;
    public $age;

    public function __construct($name="",  $age=25, $sex='男')
    {
        $this->name = $name;
        $this->age  = $age;
        $this->sex  = $sex;
    }

    public function __invoke() {
        echo '这可是一个对象哦';
    }

}

$person = new Person('小明'); // 初始赋值
$person();  // 删除结果：这可是一个对象哦
```

如果类中没有定义`__invoke`方法时，将类像调用方法一样使用则会发生致命错误。 
### __set_state() 调用 var_export() 导出类时，此静态方法会被调用

自 PHP 5.1.0 起，当调用`var_export()`导出类时，此静态方法会被自动调用。

本方法的唯一参数是一个数组，其中包含按`array('property' => value, ...)`格式排列的类属性。 
### __clone() 当对象复制完成时调用

对象复制可以通过`clone`关键字来完成（如果可能，这将调用对象的`__clone()`方法）。

对象中的`__clone()`方法不能被直接调用。

当对象被复制后，PHP 5 会对对象的所有属性执行一个浅复制（shallow copy）。所有的引用属性 仍然会是一个指向原来的变量的引用。

当复制完成时，如果定义了`__clone()`方法，则新创建的对象（复制生成的对象）中的`__clone()`方法会被调用，可用于修改属性的值（如果有必要的话）。

示例如下：

```php
<?php
class Person
{
    public $sex;
    public $name;
    public $age;

    public function __construct($name="",  $age=25, $sex='男')
    {
        $this->name = $name;
        $this->age  = $age;
        $this->sex  = $sex;
    }

    public function __clone()
    {
        echo __METHOD__."你正在克隆对象<br>";
    }

}

$person = new Person('小明'); // 初始赋值
$person2 = clone $person;

var_dump('persion1:');
var_dump($person);
echo '<br>';
var_dump('persion2:');
var_dump($person2);
```

输出结果为：

```
Person::__clone你正在克隆对象
string(9) "persion1:" object(Person)#1 (3) { ["sex"]=> string(3) "男" ["name"]=> string(6) "小明" ["age"]=> int(25) } 
string(9) "persion2:" object(Person)#2 (3) { ["sex"]=> string(3) "男" ["name"]=> string(6) "小明" ["age"]=> int(25) }
```

### __debugInfo() 打印所需调试信息

该方法在 PHP 5.6.0 及其以上版本才可以用。
 ```php
<?php
class C {
    private $prop;

    public function __construct($val) {
        $this->prop = $val;
    }

    /**
     * @return array
     */
    public function __debugInfo() {
        return [
            'propSquared' => $this->prop ** 2,
        ];
    }
}

var_dump(new C(42));

// 输出为
// object(C)#1 (1) { ["propSquared"]=> int(1764) }
``` 
### __autoload() 尝试加载未定义的类

可以通过定义这个函数来启用类的自动加载。

在魔术函数`__autoload()`方法出现以前，如果要在一个程序文件中实例化100个对象，那么必须用 include 或者 require 包含进来100个类文件，或者把这100个类定义在同一个类文件中。

但是有了`__autoload()`方法，以后就不必为此大伤脑筋了，这个类会在实例化对象之前自动加载制定的文件。

> 这个方法是定义在脚本文件中，而不是定义在类文件中。
> 
> 目前 PHP 中有更好的自动注册方法了：`spl_autoload_register` —- 注册给定的函数作为`__autoload`的实现。


### 转摘

[PHP之十六个魔术方法详解](https://segmentfault.com/a/1190000007250604)

