## 模式定义
空对象模式并不是 GoF 那本《设计模式》中提到的 23 种经典设计模式之一，但却是一个经常出现以致我们不能忽略的模式。该模式有以下优点：

* 简化客户端代码
* 减少空指针异常风险
* 更少的条件控制语句以减少测试用例

在空对象模式中，以前返回对象或 null 的方法现在返回对象或空对象 NullObject，这样会减少代码中的条件判断，比如之前调用返回对象方法要这么写：

```php
if (!is_null($obj)) {
    $obj->callSomething();
}
```

现在因为即使对象为空也会返回空对象，所以可以直接这样调用返回对象上的方法：

```php
$obj->callSomething();
```

从而消除客户端的检查代码。

当然，你可能已经意识到了，要实现这种调用的前提是返回对象和空对象需要实现同一个接口，具备一致的代码结构。


## UML 类图
![空对象模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467956299545.png)


## 示例代码

**Service.php**

```php
namespace DesignPatterns\Behavioral\NullObject;

/**
 * Service 是使用 logger 的模拟服务
 */
class service
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * 我们在构造函数中注入logger
     *
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        $this->logger = $log;
    }
    
    /**
     * do something ...
     */
    public function doSomething()
    {
        // 在空对象模式中不再需要这样判断 "if (!is_null($this->logger))..."
        $this->logger->log('We are in ' . __METHOD__);
        // something to do...
    }
}
```

**LoggerInterface.php**

```php
namespace Designpatterns\Behavioral\NullObject;

/**
 * LoggerInterface 是 logger 接口
 *
 * 核心特性: NullLogger必须和其它Logger一样实现这个接口
 */
interface LoggerInterface
{
    /**
     * @param string $str
     *
     * @return mixed
     */
    public function log($str);
}
```

**PrintLogger.php**

```php
namespace DesignPatterns\Behavioral\NullObject;

/**
 * PrintLogger 是用于打印 Logger 实体到标准输出的 Logger
 */
class PrintLogger implements LoggerInterface
{
    /**
     * @param string $str
     */
    public function log($str)
    {
        echo $str;
    }
}
```

**NullObject.php**

```php
namespace DesignPatterns\Behavioral\NullObject;

/**
 * 核心特性 : 必须实现 LoggerInterface 接口
 */
class NullObject implements LoggerInterface
{
    public function log($str)
    {
        // do nothing
    }
}
```

这样，当我们给 Service 对象传入的是一个空对象的时候，不通过条件判断语句也能够正常的执行代码而不出错：

```php
$service = new Service(new NullLogger());
$this->expectOutputString(null);  // 没有输出，也不出错
$service->doSomething();
```


## 参考
[PHP 设计模式系列 —— 空对象模式（Null Object）](http://laravelacademy.org/post/2912.html)

