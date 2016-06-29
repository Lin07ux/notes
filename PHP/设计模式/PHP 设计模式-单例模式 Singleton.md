## 模式定义
简单说来，单例模式的作用就是保证在整个应用程序的生命周期中，任何一个时刻，单例类的实例都只存在一个，同时这个类还必须提供一个访问该类的全局访问点。

单例类至少拥有以下三种特点：

- 必须拥有一个构造函数，并且必须被标记为`private`。这样可以防止外部代码使用new操作符创建对象。单例类不能在其他类中实例化，只能被其自身实例化；
- 拥有一个保存类的实例的静态成员变量。
- 拥有一个访问这个实例的公共的静态方法。常用`getInstance()`方法进行实例化单例类，通过`instanceof`操作符可以检测到类是否已经被实例化。

另外，一般还需要创建`__clone()`方法防止对象被复制（克隆）。

常见使用实例：数据库连接器；日志记录器（如果有多种用途使用多例模式）；锁定文件。


## UML 类图
![单例模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467190546153.png)


## 示例代码

**Singleton.php**

```php
namespace DesignPatterns\Creational\Singleton;

/**
 * Singleton类
 */
class Singleton {
    /**
     * 用来保存实例对象
     *
     * @var Singleton reference to singleton instance
     */
    private static $instance = null;

    /**
     * 构造函数私有，不允许在外部实例化
     */
    private function __construct()
    {
        echo '创建了一个实例<br>';
    }

    /**
     * 通过延迟加载（用到时才加载）获取实例
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 防止对象实例被克隆
     *
     * @return void
     */
    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }
    
    /**
     * 防止被反序列化
     *
     * @return void
     */
    public function __wakeup()
    {
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

## 参考
[PHP 设计模式系列 —— 单例模式（Singleton）](http://laravelacademy.org/post/2599.html)






