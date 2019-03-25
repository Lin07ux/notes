## 模式定义
代理模式（Proxy）为其他对象提供一种代理以控制对这个对象的访问。使用代理模式创建代理对象，让代理对象控制目标对象的访问（目标对象可以是远程的对象、创建开销大的对象或需要安全控制的对象），并且可以在不改变目标对象的情况下添加一些额外的功能。

在某些情况下，一个客户不想或者不能直接引用另一个对象，而代理对象可以在客户端和目标对象之间起到中介的作用，并且可以通过代理对象去掉客户不能看到的内容和服务或者添加客户需要的额外服务。

经典例子就是网络代理，你想访问 Facebook 或者 Twitter ，如何绕过 GFW？找个代理网站。


## UML 类图
![代理模式](http://cnd.qiniu.lin07ux.cn/markdown/1467781503987.png)


## 示例代码

**Record.php**

```php
namespace DesignPatterns\Structural\Proxy;

/**
 * Record类
 */
class Record
{
    /**
     * @var array|null
     */
    protected $data;
    
    /**
     * @param null $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }
    
    /**
     * magic setter
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[(string)$name] = $value;
    }
    
    /**
     * magic getter
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        $name = (string)$name;
        
        if (array_key_exists($name)) {
            return $this->data[$name];
        }
        
        return null;
    }
}
```

**RecordProxy.php**

```php
namespace DesignPatterns\Structural\Proxy;

/**
 * RecordProxy 类
 */
class RecordProxy extends Record
{
    /**
     * @var bool
     */
    protected $isDirty = false;
    
    /**
     * @var bool
     */
    protected $isInitialized = false;
    
    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        
        // when the record has data, mark it as initialized
        // since Record will hold our business logic, we don't want to
        // implement this behavior there, but instead in a new proxy class
        // that extends the Record class
        if (null !== $data) {
            $this->isInitialized = true;
            $this->isDirty = true;
        }
    }
    
    /**
     * magic setter
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->isDirty = true;
        parent::__set($name, $value);
    }
}
```


## 总结
代理模式在很多情况下都非常有用，特别是你想强行控制一个对象的时候，比如延迟加载、监视状态变更的方法等等。

与类似接口的区别：

* 适配器模式 —— 适配器模式为它所适配的对象提供了一个不同的接口，而代理提供了与它的实体相同的接口。
* 装饰器模式 —— 两者目的不同：装饰器为对象添加一个或多个功能，而代理则控制对对象的访问。


## 参考
[PHP 设计模式系列 —— 代理模式（Proxy）](http://laravelacademy.org/post/2841.html)



