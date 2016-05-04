## 特点
单例模式按字面来看就是某一个类只有一个实例，这样做的好处还是很大的，比如说数据库的连接，我们只需要实例化一次，不需要每次都去new了，这样极大的降低了资源的耗费。

单例类至少拥有以下三种特点：

- 必须拥有一个构造函数，并且必须被标记为`private`。这样可以防止外部代码使用new操作符创建对象。单例类不能在其他类中实例化，只能被其自身实例化；
- 拥有一个保存类的实例的静态成员变量。
- 拥有一个访问这个实例的公共的静态方法。常用`getInstance()`方法进行实例化单例类，通过`instanceof`操作符可以检测到类是否已经被实例化。

另外，一般还需要创建`__clone()`方法防止对象被复制（克隆）。

## 实现

```php
<?php
class Singleton {
    // 该属性用来保存实例对象
    private static $instance = null;

    // 构造函数为 private，防止外部代码创建对象
    private function __construct()
    {
        echo '创建了一个实例<br>';
    }

    // 通过这个方法获得实例化对象(创建实例对象)
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    // 防止对象被复制
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    // 其他正常的方法
    public function test()
    {
        echo '成功执行<br>';
    }
}

// 只能这样取得实例，不能 new 和 clone
$t = Singleton::getInstance();
$t->test();
```


