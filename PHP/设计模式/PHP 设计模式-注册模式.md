## 模式定义
注册模式（Registry）也叫做注册树模式，注册器模式。注册模式为应用中经常使用的对象创建一个中央存储器来存放这些对象 —— 通常通过一个只包含静态方法的抽象类来实现（或者通过单例模式）。


## UML 类图
![注册模式](http://cnd.qiniu.lin07ux.cn/markdown/1467784815080.png)


## 示例代码

**Registry.php**

```php
namespace DesignPatterns\Structural\Registry;

/**
 * class Registry
 */
abstract class Registry
{
    const LOGGER = 'logger';
    
    /**
     * @var array
     */
    protected static $storedValues = array();
    
    /**
     * sets a value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @static
     * @return void
     */
    public function set($key, $value)
    {
        self::storedValues[$key] = $value;
    }
    /**
     * gets a value from the registry
     *
     * @param string $key
     *
     * @static
     * @return mixed
     */
    public function get($key)
    {
        return self::storedValues[$key];
    }
    
    // typically there would be methods to check if a 
    // key has already been registered and so on ...
}
```


## 总结
注册模式其实和创建模式中的多例模式、对象池模式很像。都是需要有一个存放对象的变量，然后通过方法来加入实例对象和取出实例对象。


## 参考
[PHP 设计模式系列 —— 注册模式（Registry）](http://laravelacademy.org/post/2850.html)

