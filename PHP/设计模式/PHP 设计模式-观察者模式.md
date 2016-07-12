## 模式定义
观察者模式有时也被称作发布/订阅模式，该模式用于为对象实现发布/订阅功能：一旦主体对象状态发生改变，与之关联的观察者对象会收到通知，并进行相应操作。

将一个系统分割成一个一些类相互协作的类有一个不好的副作用，那就是需要维护相关对象间的一致性。我们不希望为了维持一致性而使各类紧密耦合，这样会给维护、扩展和重用都带来不便。观察者就是解决这类的耦合关系的。

消息队列系统、事件都使用了观察者模式。

PHP 为观察者模式定义了两个接口：`SplSubject`和`SplObserver`。`SplSubject`可以看做主体对象的抽象，`SplObserver`可以看做观察者对象的抽象，要实现观察者模式，只需让主体对象实现 `SplSubject`，观察者对象实现`SplObserver`，并实现相应方法即可。


## UML 类图
![观察者模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468056838147.png)


## 示例代码

**User.php**

```php
namespace DesignPatterns\Behavioral\Observer;

/**
 * 观察者模式 : 被观察对象 (主体对象)
 *
 * 主体对象维护观察者列表并发送通知
 *
 */
class User implements \SplSubject
{
    /**
     * user data
     *
     * @var array
     */
    protected $data = array();
    
    /**
     * observers
     *
     * @var \SplObjectStorage
     */
    protected Observers;
    
    public function __construct()
    {
        $this->Observers = new \SplObjectStorage();
    }
    
    /**
     * 附加观察者
     *
     * @param \SplObserver $observer
     *
     * @return void
     */
    public function attach(\SplObserver $observer)
    {
        $this->observers->attach($observer);
    }
    
    /**
     * 取消观察者
     *
     * @param \SplObserver $observer
     *
     * @return void
     */
    public function detach(\SplObserver $observer)
    {
        $this->observers->detach($observer);
    }
    
    /**
     * 通知观察者方法
     *
     * @return void
     */
    public function notify()
    {
        // $this->observers 还符合迭代器模式
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
    
    /**
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        
        // 通知观察者用户被改变
        $this->notify();
    }
}
```

**UserObserver.php**

```php
namespace DesignPatterns\Behavioral\Observer;

/**
 * UserObserver 类（观察者对象）
 */
class UserObserver implements \SplObserver
{
    /**
     * 观察者要实现的唯一方法
     * 也是被 Subject 调用的方法
     *
     * @param \SplSubject $subject
     */
    public function update(\SplSubject $subject)
    {
        echo get_class($subject) . ' has been updated.';
    }
}
```

然后我们就能够创建一个用户对象和一个或多个用户观察者对象，然后将观察者添加到用户对象的 $observers 中，之后每次用户发生属性设置时，观察者对象都会被通知到，并做相应的输出操作。


## 总结
观察者模式解除了主体和具体观察者的耦合，让耦合的双方都依赖于抽象，而不是依赖具体。从而使得各自的变化都不会影响另一边的变化。

观察者模式实现的关键是被观察主体要实现一个主动通知观察者对象的方法，而观察者要实现一个对应的接收通知的方法，并通过参数接收发送通知的对象，从而实现解耦，还能关联相应操作。

观察者模式和中介者模式的区别在于，中介者还需要一个中间对象来串联同事对象的影响和操作，属于多对多的操作，信息传递是双向的。而观察者模式一般是用于广播模式，也就是一对多的情况，信息传递是单项的。


## 参考
[PHP 设计模式系列 —— 观察者模式（Observer）](http://laravelacademy.org/post/2935.html)

