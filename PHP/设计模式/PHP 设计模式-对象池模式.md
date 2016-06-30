## 模式定义
对象池（也称为资源池）被用来管理对象缓存。对象池是一组已经初始化过且可以直接使用的对象集合，用户在使用对象时可以从对象池中获取对象，对其进行操作处理，并在不需要时归还给对象池而非销毁它。

若对象初始化、实例化的代价高，且需要经常实例化，但每次实例化的数量较少的情况下，使用对象池可以获得显著的性能提升。常见的使用对象池模式的技术包括线程池、数据库连接池、任务队列池、图片资源对象池等。

当然，如果要实例化的对象较小，不需要多少资源开销，就没有必要使用对象池模式了，这非但不会提升性能，反而浪费内存空间，甚至降低性能。


## UML 类图
![对象池模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467256335361.png)


## 示例代码

**Pool.php**

```php
namespace DesignPatterns\Creational\Pool;

/**
 * 对象池类
 * 
 * 用于管理池内的对象：创建和归还
 */
class Pool
{
    /**
     * @var array
     */
    private $instances = array();
    
    /**
     * @var string
     */
    private $class;
    
    /**
     * 创建对象池时需要制定池内对象的类
     *
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }
    
    /**
     * 返回池内类的一个实例
     * 
     * 如果池内没有足够的对象实例，则返回一个新创建的对象
     * 
     * @return object
     */
    public function get()
    {
        if (count($this->instances) > 0) {
            return array_pop($this->instances);
        }
        
        return new $this->class();
    }
    
    /**
     * 回收对象实例
     * 
     * @return void
     */
    public function dispose($instance)
    {
        $this->instances[] = $instance;
    }
}
```

**Processor.php**

```php
namespace DesignPatterns\Creational\Pool;

/**
 * 处理者类
 * 
 * 用于总的任务调度：创建对象池、调用 Worker 处理任务、回收 Worker 等
 */
class Processor
{
    /**
     * @var Pool
     */
    private $pool;
    
    /**
     * @var int
     */
    private processing    = 0;
    
    /**
     * @var int
     */
    private $maxProcesses = 3;
    
    /**
     * @var array
     */
    private $waitingQueue = array();
    
    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }
    
    /**
     * 进行处理操作
     * 
     * 如果当前正在处理的对象较多，就推入到等待队列
     * 
     * @return void
     */
    public function process($image)
    {
        if ($this->processing++ < $this->maxProcesses) {
            $this->createWorker($image);
        } else {
            $this->pushToWaitingQueue($image);
        }
    }
    
    /**
     * 调用实际的处理对象进行操作
     * 
     * @return void
     */
    private function createWorker($image)
    {
        $worker = $this->pool->get();
        
        $worker->run($image, array($this, 'processDone');
    }
    
    /**
     * 处理完成之后进行对象的回收等操作
     * 
     * @return void
     */
    public function processDone($worker)
    {
        $this->processing--;
        $this->pool->dispose($worker);
        
        if (count($this->waitingQueue) > 0) {
            $this->createWorker($this->popFromWaitingQueue());
        }
    }
    
    /**
     * @return void
     */
    private function pushToWaitingQueue($image)
    {
        $this->waitingQueue[] = $image;
    }
    
    /**
     * @return void
     */
    private function popFromWaitingQueue()
    {
        return array_pop($this->waitingQueue);
    }
}
```

**Worker.php**

```php
namespace DesignPatterns\Creational\Pool;

/**
 * Worker 类
 * 
 * 用于进行实际的任务处理
 */
class Worker
{
    public function __Construct()
    {
        // let's say that constructor does really expensive work...
        // for example creates "thread"
    }
    
    /**
     * 实际的任务处理方法
     * 
     * @param $image
     * @param array $callback
     */
    public function run($image, array $callback)
    {
        // do something with $image...
        // and when it's done, execute callback
        call_user_func($callback, $this);
    }
}
```

有了上面的代码，就可以实例化一个调度者对象，然后通过调度者来处理实际的任务：

```php
$processor = new Processor(new Pool('worker'));

$image = '';   // some image
$processor->process($image);
```


## 总结
对象池模式和多例模式有些地方相像，比如都会在类中保存实例化过的类，而且不允许直接通过 new 创建对象实例。不过，对象池模式还需要有一步：归还对象实例。

对象池模式中，需要有一个专门的对象池对象来管理对象实例：创建和回收实际的处理任务的对象。而总的调度者则会将对象池对象和处理任务的对象结合起来，完成具体的任务。

对于客户端来说，就只需要通过调用调度者来进行任务的处理。


## 参考
[PHP 设计模式系列 —— 对象池模式（Object Pool）](http://laravelacademy.org/post/2532.html)


