PHP 类内部调用其自身内部的静态方法时，有两种方式：`self::static_func()`、`static::static_func()`，这两种方式大部分情况下，效果是一样的，但是也有一些特别的情况下会有所区别：**static 是后期静态绑定**。

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




