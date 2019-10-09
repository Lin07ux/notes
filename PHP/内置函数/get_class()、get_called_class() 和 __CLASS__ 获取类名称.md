### `__CLASS__`

PHP 中`__CLASS__`可以用来获取当前类的名称，但是`__CLASS__`是静态绑定的，如果不在子类里进行重载的话，继承父类方法所得到的依旧是父类的名称，而不是子类的名称。

比如：

```php
class A
{
    public function __construct()
    {
        echo __CLASS__;
    }
    
    static public function name()
    {
        echo __CLASS__;
    }
}

class B extends A
{
}

$objB = new B(); // A
B::name(); // A
```

由于 B 类没有重载父类 A 中的构造方法和`name()`方法，所以在调用的时候，`__CLASS__`指向的就是定义时的类的名称，所以输出就是`A`。

### get_class()/get_called_class()

如果想要在不重载父类的方法就得到当前类的名称，可以使用 PHP 内置的`get_class()`和`get_called_class()`方法。其中，`get_class()`方法用于实例调用，`get_called_class()`用于静态方法调用。

> `get_called_class()`需要 PHP >= 5.3.0 才支持

对于上面的例子，修改如下：

```php
class A
{
    public function __construct()
    {
        echo get_class($this);
    }
    
    static public function name()
    {
        echo get_called_class();
    }
}

class B extends A
{
}

$objB = new B(); // B
B::name();  // B
```


