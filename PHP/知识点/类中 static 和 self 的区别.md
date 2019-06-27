PHP 类内部调用其自身内部的静态方法时，有两种方式：`self::static_func()`、`static::static_func()`，这两种方式大部分情况下。效果是一样的，但是也有一些特别的情况下会有所区别：**self 指向定义时的类，static 是指向后期运行时的类**。

### 示例一

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

### 示例二


```php
class Base {
    public function __construct() {
        echo "Base constructor!", PHP_EOL;
    }

    public static function getSelf() {
        return new self();
    }

    public static function getInstance() {
        return new static();
    }

    public function selfFoo() {
        return self::foo();
    }

    public function staticFoo() {
        return static::foo();
    }

    public function thisFoo() {
        return $this->foo();
    }

    public function foo() {
        echo  "Base Foo!", PHP_EOL;
    }
}

class Child extends Base {
    public function __construct() {
        echo "Child constructor!", PHP_EOL;
    }

    public function foo() {
        echo "Child Foo!", PHP_EOL;
    }
}

$base = Child::getSelf(); // Base constructor!
$child = Child::getInstance(); // Child constructor!

$child->selfFoo(); // Base Foo!
$child->staticFoo(); // Child Foo!
$child->thisFoo(); // Child Foo!
```


