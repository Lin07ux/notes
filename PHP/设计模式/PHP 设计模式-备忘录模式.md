## 模式定义
备忘录模式又叫做快照模式（Snapshot）或 Token 模式，备忘录模式的用意是在不破坏封装性的前提下，捕获一个对象的内部状态，并在该对象之外保存这个状态，这样就可以在合适的时候将该对象恢复到原先保存的状态。

我们在编程的时候，经常需要保存对象的中间状态，当需要的时候，可以恢复到这个状态。比如，我们使用 Eclipse 进行编程时，假如编写失误（例如不小心误删除了几行代码），我们希望返回删除前的状态，便可以使用 Ctrl+Z 来进行返回。这时我们便可以使用备忘录模式来实现。


## UML 类图
![备忘录模式](http://cnd.qiniu.lin07ux.cn/markdown/1467904158344.png)

备忘录模式所涉及的角色有三个：备忘录(Memento)角色、发起人(Originator)角色、负责人(Caretaker)角色。

这三个角色的职责分别是：

* 发起人：记录当前时刻的内部状态，负责定义哪些属于备份范围的状态，负责创建和恢复备忘录数据。
* 备忘录：负责存储发起人对象的内部状态，在需要的时候提供发起人需要的内部状态。
* 管理角色：对备忘录进行管理，保存和提供备忘录。


## 示例代码

**Memento.php**

```php
namespace DesignPatterns\Behavioral\Memento;

class Memento
{
    /*
     * @var mixed
     */
    private $state;
    
    /**
     * @param mixed $stateToSave
     */
    public function __construct($stateToSave)
    {
        $this->state = $stateToSave;
    }
    
    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }
}
```

**Originator.php**

```php
namespace DesignPatterns\Behavioral\Memento;

class Originator
{
    /*
     * @var mixed 
     */
    private $state;
    
    // 这个类还可以包含不属于备忘录状态的额外数据

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        // 必须检查该类子类内部的状态类型或者使用依赖注入
        $this->state = $state;
    }
    
    /**
     * @return Memento
     */
    public function getStateAsMemento()
    {
        // 在 Memento 中必须保存一份隔离的备份
        $state = is_object($this->state)
            ? clone $this->state : $this->state;
            
        return new Memento($state);
    }
    
    public function restoreFromMemento(Memento $memento)
    {
        $this-state = $memento->getState();
    }
}
```

**Caretaker.php**

```php
namespace DesignPatterns\Behavioral\Memento;

class Caretaker
{
    protected $history = array();
    
    /**
     * @param int $id
     * 
     * @return Memento
     */
    public function getFormHistory($id)
    {
        return $this->history[$id];
    }
    
    /**
     * @param Memento $state
     */
    public function saveToHistory(Memento $state)
    {
        $this->history[] = $state;
    }
}
```

**Test.php**

```php
use DesignPatterns\Behavioral\Memento\Originator;
use DesignPatterns\Behavioral\Memento\Caretaker;

$originator = new Originator();
        
// 设置状态为 State-1
$originator->setState('State-1');
// 设置状态为 State-2
$originator->setState('State-2');
// 将 State-2 保存到 Memento
$memento = $originator->getStateAsMemento();
// 将状态保存到历史记录中
$caretaker = new Caretaker();
$caretaker->saveToHistory($memento);
// 设置状态为 State-3
$originator->setState("State-3");

// 我们可以请求多个备忘录, 然后选择其中一个进行回滚

// 保存State3到Memento
$caretaker->saveToHistory($originator->getStateAsMemento());
// 设置状态为 State-4
$originator->setState("State-4");

// 从备忘录历史中恢最后一次保存的状态
$originator->restoreFromMemento($this->getFromHistory(1));

// 从备忘录恢复后的状态: State-3
$originator->getStateAsMemento()->getState();
```


## 总结
备忘录模式就是一种记录关键信息的操作。用到的三个角色中：

* 发起人，就是需要对其状态进行保存的类。他需要操作备忘录角色对其状态做一个备份。
* 备忘录，是发起人的一个个的备份，还能够提供关键信息给发起人，使其恢复到某个状态。
* 管理角色，其实就是提供一个管理每个备忘录的对象，他能够将发起人制造出来的备忘录用特定的方式保存起来，而不是任其散落。这样在必要的时候就能方便的让发起人恢复状态。

如果有需要提供回滚操作的需求，使用备忘录模式非常适合，比如数据库的事务操作，文本编辑器的 Ctrl+Z 恢复等。


## 参考
[PHP 设计模式系列 —— 备忘录模式（Memento）](http://laravelacademy.org/post/2903.html)

