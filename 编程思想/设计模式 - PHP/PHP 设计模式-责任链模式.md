## 模式定义
责任链模式将处理请求的对象连成一条链，沿着这条链传递该请求，直到有一个对象处理请求为止，这使得多个对象都有机会处理请求，从而避免请求的发送者和接受者之间的耦合关系。

责任链模式在现实中使用的很多，常见的就是 OA 系统中的工作流。


## UML 类图
![责任链模式](http://cnd.qiniu.lin07ux.cn/markdown/1467785826557.png)


## 示例代码

**Request.php**

```php
namespace DesignPatterns\Behavioral\ChainOfResponsibilities;

/**
 * 经过责任链的Request类
 *
 * 关于请求: 有时候，不需要一个请求对象，只需一个整型数据或者一个数组即可。
 * 但是作为一个完整示例，这里我们生成了一个请求类。
 * 在实际项目中，也推荐使用请求类，即是是一个标准类\stdClass，
 * 因为这样的话代码更具扩展性，因为责任链的处理器并不了解外部世界，
 * 如果某天你想要添加其它复杂处理时不使用请求类会很麻烦
 */
class Request
{
    // getter and setter but I don't want to 
    // generate too much noise in handlers
}
```


**Handler.php**

```php
namespace DesignPatterns\Behavioral\ChainOfResponsibilities;

/**
 * 责任链的通用处理器类Handler（通常是一个接口或抽象类）
 *
 * 当然你可以通过一个更简单的处理器实现更加轻量级的责任链，
 * 但是如果你想让你的责任链拥有更好的扩展性和松耦合，
 * 那么就需要模拟一个更加真实的场景：通常一个责任链每时每刻都会被修改，
 * 这也是为什么我们在这里将其切分成好几个部分来完成。
 */
abstract class Handler
{
    /**
     * @var Handler
     */
    protected $successor = null;
    
    /**
     * 追加处理类到责任链
     * 通过这个方法可以追加多个处理类到责任链
     *
     * @param Handler $handler
     */
    final public function append(Handler $handler)
    {
        if (is_null($this->successor)) {
            $this->successor = $handler;
        } else {
            $this->successor->append($handler);
        }
    }
    
    /**
     * 处理请求
     *
     * 这里我们使用模板方法模式以确保每个子类都不会忘记调用successor
     * 此外，返回的布尔值表明请求是否被处理
     * 
     * @param Request $req
     *
     * @return bool
     */
    final public function handle(Request $req)
    {
        $req->forDebugOnly = get_called_class();
        
        $processed = $this->processing($req);
        if (!$processed) {
            // the request has not been processed by this handler 
            // so, see the next
            if (!is_null($this->successor)) {
                $processed = $this->successor->handle($req);
            }
        }
        
        return $processed;
    }
    
    /**
     * 每个处理器具体实现类都要实现这个方法对请求进行处理
     *
     * @param Request $req
     *
     * @return bool true if the request has been processed
     */
    abstract protected function processing(Request $req);
}
```

**Responsible/SlowStorage.php**

```php
namespace DesignPatterns\Behavioral\ChainOfResponsibilities\Responsible;

use DesignPatterns\Behavioral\ChainOfResponsibilities\Request;
use DesignPatterns\Behavioral\ChainOfResponsibilities\Handler;

/**
 * 该类和FastStorage基本相同，但也有所不同
 *
 * 责任链模式的一个重要特性是: 责任链中的每个处理器都不知道自己在责任链中的位置，
 * 如果请求没有被处理，那么责任链也就不能被称作责任链，除非在请求到达的时候抛出异常
 *
 * 为了实现真正的扩展性，每一个处理器都不知道后面是否还有处理器
 *
 */
class SlowStorage extends Handler
{
    /**
     * @var array
     */
    protected $data = array();
    
    /**
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->data = $data;
    }
    
    protected function processing(Request $req)
    {
        if ('get' == $req->verb) {
            if (array_key_exists($req->key, $this->data)) {
                $req->response = $this->data[$req->key];
                
                return true;
            }
        }
        
        return false;
    }
}
```

**Responsible/FastStorage.php**

```php
namespace DesignPatterns\Behavioral\ChainOfResponsibilities\Responsible;

use DesignPatterns\Behavioral\ChainOfResponsibilities\Handler;
use DesignPatterns\Behavioral\ChainOfResponsibilities\Request;

class FastStorage extends Handler
{
    /**
     * @var array
     */
    protected $data = array();
    
    /**
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->data = $data;
    }
    
    protected function processing(Request $req)
    {
        if ('post' == $req->verb) {
            if (array_key_exists($req->key, $this->data)) {
                $req->response = $this->data[$req->key];
                
                return true;
            }
        }
        
        return false;
    }
}
```


## 总结
责任链模式和 C 语言中的指针链有点像，每一个节点的前后都是和他同类的节点。请求从责任链的起始处传入，依次向后，直到某一个节点能够处理了这个请求，然后返回。

责任链模式的主要优点在于可以降低系统的耦合度，简化对象的相互连接，同时增强给对象指派职责的灵活性，增加新的请求处理类也很方便；其主要缺点在于不能保证请求一定被接收，且对于比较长的职责链，请求的处理可能涉及到多个处理对象，系统性能将受到一定影响，而且在进行代码调试时不太方便。


## 参考
[PHP 设计模式系列 —— 责任链模式（Chain Of Responsibilities）](http://laravelacademy.org/post/2858.html)

