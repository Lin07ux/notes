## 模式定义
中介者模式（Mediator）就是用一个中介对象来封装一系列的对象交互，中介者使各对象不需要显式地相互引用，从而使其耦合松散，而且可以独立地改变它们之间的交互。

对于中介对象而言，所有相互交互的对象，都视为同事类，中介对象就是用来维护各个同事对象之间的关系，所有的同事类都只和中介对象交互，也就是说，中介对象是需要知道所有的同事对象的。当一个同事对象自身发生变化时，它是不知道会对其他同事对象产生什么影响，它只需要通知中介对象，“我发生变化了”，中介对象会去和其他同事对象进行交互的。这样一来，同事对象之间的依赖就没有了。有了中介者之后，所有的交互都封装在了中介对象里面，各个对象只需要关心自己能做什么就行，不需要再关心做了之后会对其他对象产生什么影响，也就是无需再维护这些关系了。


## UML 类图
![中介者模式](http://cnd.qiniu.lin07ux.cn/markdown/1467901461099.png)


## 示例代码

**MediatorInterface.php**

```php
namespace DesignPatterns\Behavioral\Mediator;

/**
 * MediatorInterface 是一个中介者契约
 * 该接口不是强制的，但是使用它更加符合里氏替换原则
 */
interface MediatorInterface
{
    /**
     * 发送响应
     *
     * @param string $content
     */
    public function sendResponse($content);
    
    /**
     * 发起请求
     */
    public function makeRequest();
    
    /**
     * 查询数据库
     */
    public function queryDB();
}
```

**Mediator.php**

```php
namespace DesignPatterns\Behavioral\Mediator;

/**
 * Mediator 是中介者模式的具体实现类
 * In this example, I have made a "Hello World" 
 * with the Mediator Pattern.
 */
class Mediator implements MediatorInterface
{
    /**
     * @var Subsystem\Server
     */
    protected $server;
    
    /**
     * @var Subsystem\Client
     */
    protected $client;
    
    /**
     * @var Subsystem\Database
     */
    protected $database;
    
    /**
     * @param Subsystem\Database $db
     * @param Subsystem\Client   $cl
     * @param Subsystem\Server   $srv
     */
    public function setColleague(Subsystem\Server $srv, Subsystem\Database $db, Subsystem\Client $cl)
    {
        $this->server = $srv;
        $this->client = $cl;
        $this->database = $db;
    }
    
    /**
     * 发送响应
     *
     * @param string $content
     */
    public function sendResponse($content)
    {
        $this->client->output($content);
    }
    
    /**
     * 发起请求
     */
    public function makeRequest()
    {
        $this->server->process();
    }
    
    /**
     * 查询数据库
     * 
     * @return mixed
     */
    public function queryDB()
    {
        $this->database->getData();
    }
}
```


**Colleague.php**

```php
namespace DesignPatterns\Behavioral\Mediator;

/**
 * Colleague 是一个抽象的同事类，但是它只知道中介者 Mediator，而不知道其他同事
 */
class Colleague
{
    /**
     * this ensures no change in subclasses
     *
     * @var MediatorInterface
     */
    private $mediator;
    
    /**
     * @param MediatorInterface $medium
     */
    public function __construct(MediatorInterface $medium)
    {
        $this->mediator = $medium;
    }
    
    // for subclasses
    public function getMediator
    {
        return $this->mediator;
    }
}
```

**Subsystem/Client.php**

```php
namespace DesignPatterns\Behavioral\Mediator\Subsystem;

use DesignPatterns\Behavioral\Mediator\Colleague;

/**
 * Client 是发起请求&获取响应的客户端
 */
class Client extends Colleague
{
    /**
     * request
     */
    public function request()
    {
        $this->mediator->makeRequest();
    }
    
    /**
     * output content
     *
     * @param string $content
     */
    public function output($content)
    {
        echo $content;
    }
}
```

**Subsystem/Database.php**

```php
namespace DesignPatterns\Behavioral\Mediator\Subsystem;

use DesignPatterns\Behavioral\Mediator\Colleague;

/**
 * Database 提供数据库服务
 */
class Database extends Colleague
{
    /**
     * @return string
     */
    public function getData()
    {
        return "World!";
    }
}
```

**Subsystem/server.php**

```php
namespace DesignPatterns\Behavioral\Mediator\Subsystem;

use DesignPatterns\Behavioral\Mediator\Colleague;

/**
 * Server 用于发送响应
 */
class server extends Colleague
{
    /**
     * process on server
     */
    public function process()
    {
        $mediator = $this->getMediator();
        $data = $mediator->queryDb();
        $mediator->sendResponse('Hello ' . $data);
    }
}
```


## 总结
中介者主要是通过中介对象来封装对象之间的关系，使之各个对象在不需要知道其他对象的具体信息情况下通过中介者对象来与之通信。同时通过引用中介者对象来减少系统对象之间关系，提高了对象的可复用和系统的可扩展性。但是就是因为中介者对象封装了对象之间的关联关系，导致中介者对象变得比较庞大，所承担的责任也比较多。它需要知道每个对象和他们之间的交互细节，如果它出问题，将会导致整个系统都会出问题。

中介者模式就相当于订阅-发布系统中的管理者，他能够接受别人的请求，还能够将数据推送给某个对象。


## 参考
[PHP 设计模式系列 —— 中介者模式（Mediator）](http://laravelacademy.org/post/2894.html)

