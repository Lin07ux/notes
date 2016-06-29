## 模式定义
多例模式和单例模式类似，但可以返回多个实例。比如我们有多个数据库连接，MySQL、SQLite、Postgres，又或者我们有多个日志记录器，分别用于记录调试信息和错误信息，这些都可以使用多例模式实现。

多例模式是单例模式的延展，区别只在于多例模式需要管理自己的多个实例，并根据获取情况来返回不同的实例。


## UML 类图
![多例模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467195293378.png)

> 这里的类图中的`INSTANCE_1`和`INSTANCE_2`属性其实可以不要。


## 示例代码

**Multiton.php**

```php
namespace DesignPatterns\Creational\Multiton;

/**
 * Multiton类
 */
class Multiton
{
    /**
     * 实例数组
     *
     * @var array
     */
    private static $instances = array();
    
    /**
     * 构造函数是私有的，不能从外部进行实例化
     *
     */
    private function __construct()
    {
    }
    
    /**
     * 通过指定名称返回实例（使用到该实例的时候才会实例化）
     *
     * @param string $instanceName
     *
     * @return Multiton
     */
    public static function getInstance($instanceName)
    {
        if (!array_key_exists($instanceName, self::$instances) {
            self::$instances[$instanceName] = new self();
        }
        
        return self::$instances[$instanceName];
    }
    
    /**
     * 防止实例从外部被克隆
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * 防止实例从外部反序列化
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
```

## 总结
多例模式和单例模式的区别主要在于其实例的个数。而且一般情况下多例模式和单例模式就是这个区别。

不过，多例模式还可以扩展成限制实例个数的情况。此时就要根据设置的实例上限来生成或者不生成实例对象了。


## 参考
[PHP 设计模式系列 —— 多例模式（Multiton）](http://laravelacademy.org/post/2519.html)

