## 模式定义
依赖注入（Dependency Injection）是控制反转（Inversion of Control）的一种实现方式。

我们先来看看什么是控制反转。

当调用者需要被调用者的协助时，在传统的程序设计过程中，通常由调用者来创建被调用者的实例，但在这里，创建被调用者的工作不再由调用者来完成，而是将被调用者的创建移到调用者的外部，从而反转被调用者的创建，消除了调用者对被调用者创建的控制，因此称为控制反转。

要实现控制反转，通常的解决方案是将创建被调用者实例的工作交由 IoC 容器来完成，然后在调用者中注入被调用者（通过构造器/方法注入实现），这样我们就实现了调用者与被调用者的解耦，该过程被称为依赖注入。

依赖注入不是目的，它是一系列工具和手段，最终的目的是帮助我们开发出松散耦合（loose coupled）、可维护、可测试的代码和程序。这条原则的做法是大家熟知的面向接口，或者说是面向抽象编程。


## UML 类图
![依赖注入模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467731008188.png)

在本例中，我们在 Connection 类（调用者）的构造方法中依赖注入 Parameters 接口的实现类（被调用者），如果不使用依赖注入的话，则必须在 Connection 中创建该接口的实现类实例，这就形成紧耦合代码，如果我们要切换成该接口的其它实现类则必须要修改代码，这对到测试和扩展而言都是极为不利的。


## 示例代码

**AbstractConfig.php**

```php
namespace DesignPatterns\Structural\DependencyInjection;

/**
 * AbstractConfig类
 */
abstract class AbstractConfig
{
    /**
     * @var Storage of data
     */
    protected $storage;
    
    public function __construct($storage)
    {
        $this->storage = $storage;
    }
}
```

**Parameters.php**

```php
namespace DesignPatterns\Structural\DependencyInjection;

/**
 * Parameters 接口
 */
interface Parameters
{
    /**
     * 获取参数
     *
     * @param string|int $key
     *
     * @return mixed
     */
    public function get($key);
    
    /**
     * 设置参数
     *
     * @param string|int $key
     * @param mixed      $value
     */
    public function set($key, $value);
}
```

**ArrayConfig.php**

```php
namespace DesignPatterns\Structural\DependencyInjection;

/**
 * ArrayConfig类
 *
 * 使用数组作为数据源
 */
class ArrayConfig extends AbstractConfig implements Parameters
{
    /**
     * 获取参数
     *
     * @param string|int $key
     * @param null $default
     * 
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }
        
        return $default;
    }
    
    /**
     * 设置参数
     *
     * @param string|int $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }
}
```

**Connection.php**

```php
namespace DesignPatterns\Structural\DependencyInjection;

/**
 * Connection 类，实现了依赖注入
 */
class Connection
{
    /**
     * @var Configuration
     */
    protected $configuration;
    
    /**
     * @var Currently connected host
     */
    protected $host;
    
    /**
     * 这里就是依赖注入的关键
     * 
     * @param Parameters $config
     */
    public function __construct(Parameters $config)
    {
        $this->configuration = $config;
    }
    
    /**
     * connection using the injected config
     */
    public function connect()
    {
        $host = $this->configuration->get('host');
        
        // connection to host, authentication etc...

        //if connected
        $this->host = $host;
    }
    
    /*
     * 获取当前连接的主机
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }
}
```


## 总结
从示例代码中可以看出，其实依赖注入是很简单的事情：就是在一个类中用到了另一个类的实例，而且另一个类的实例是通过调用者类的方法参数传递进去的。这样就能够在调用者类外部根据我们的需要实例化一个类，然后传入到调用者类中即可。

所以，依赖注入模式其实在我们日常编程中就是经常用到的，并不是多么罕见的模式。

依赖注入模式需要在调用者外部完成容器创建以及容器中接口与实现类的运行时绑定工作，在 Laravel 中该容器就是服务容器，而接口与实现类的运行时绑定则在服务提供者中完成。此外，除了在调用者的构造函数中进行依赖注入外，还可以通过在调用者的方法中进行依赖注入。


## 参考
[PHP 设计模式系列 —— 依赖注入模式（Dependency Injection）](http://laravelacademy.org/post/2792.html)

