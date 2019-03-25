## 模式定义
在软件开发中也常常遇到类似的情况，实现某一个功能有多种算法或者策略，我们可以根据环境或者条件的不同选择不同的算法或者策略来完成该功能。如查找、排序等，一种常用的方法是硬编码（Hard Coding）在一个类中，如需要提供多种查找算法，可以将这些算法写到一个类中，在该类中提供多个方法，每一个方法对应一个具体的查找算法；当然也可以将这些查找算法封装在一个统一的方法中，通过`if…else`或者`switch…case`等条件判断语句来进行选择。这两种实现方法我们都可以称之为硬编码，如果需要增加一种新的查找算法，需要修改封装算法类的源代码；更换查找算法，也需要修改客户端调用代码。在这个算法类中封装了大量查找算法，该类代码将较复杂，维护较为困难。如果我们将这些策略包含在客户端，这种做法更不可取，将导致客户端程序庞大而且难以维护，如果存在大量可供选择的算法时问题将变得更加严重。

如何让算法和对象分开来，使得算法可以独立于使用它的客户而变化？为此我们引入策略模式。

策略模式（Strategy），又叫算法簇模式，就是定义了不同的算法族，并且之间可以互相替换，此模式让算法的变化独立于使用算法的客户。

常见的使用场景比如对象筛选，可以根据日期筛选，也可以根据 ID 筛选；又比如在单元测试中，我们可以在文件和内存存储之间进行切换。


## UML 类图
![策略模式](http://cnd.qiniu.lin07ux.cn/markdown/1468062732443.png)


## 示例代码

**ObjectCollection.php**

```php
namespace DesignPatterns\Behavioral\Strategy;

/**
 * ObjectCollection 类
 */
class ObjectCollection
{
    /**
     * @var array
     */
    private $elements;
    
    /**
     * @var ComparatorInterface
     */
    private $comparator;
    
    /**
     * @param array $elements
     */
    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }
    
    /**
     * @return array
     */
    public function sort()
    {
        if (!this->comparator) {
            throw new \LogicException('Comparator is not set');
        }
        
        $callback = array($this->comparator, 'compare');
        uasort($this->elements, $callback);
        
        return $this->elements;
    }
    
    /**
     * @param ComparatorInterface $comparator
     *
     * @return void
     */
    public function setComparator(ComparatorInterface $comparator)
    {
        $this->comparator = $comparator;
    }
}
```

**ComparatorInterface.php**

```php
namespace DesignPatterns\Behavioral\Strategy;

/**
 * ComparatorInterface 接口
 */
interface ComparatorInterface
{
    /**
     * @param mixed $a
     * @param mixed $b
     *
     * @return bool
     */
    public function compare($a, $b);
}
```

**DateComparator.php**

```php
namespace DesignPatterns\Behavioral\Strategy;

/**
 * DateComparator 类
 */
class DateComparator implements ComparatorInterface
{
    public function compare($a, $b)
    {
        $aDate = new \DateTime($a['date']);
        $bDate = new \DateTime($b['date']);
        
        if ($aDate == $bDate) {
            return 0;
        } else {
            return $aDate < $bDate ? -1 : 1;
        }
    }
}
```

**IdComparator.php**

```php
namespace DesignPatterns\Behavioral\Strategy;

/**
 * IdComparator 类
 */
class IdComparator implements ComparatorInterface
{
    public function compare($a, $b)
    {
        if ($a['id'] == $b['id]) {
            return 0;
        } else {
            return $a['id'] < $b['id'] ? -1 : 1;
        }
    }
}
```


## 总结
策略模式属于对象行为型模式，主要针对一组算法，将每一个算法封装到具有共同接口的独立的类中，从而使得它们可以相互替换。策略模式使得算法可以在不影响到客户端的情况下发生变化。通常，策略模式适用于当一个应用程序需要实现一种特定的服务或者功能，而且该程序有多种实现方式时使用。

在使用策略模式的算法的时候，只需要使用依赖注入传递一个算法对象进去，然后调用算法对象的方法去操作即可。


## 参考
[PHP 设计模式系列 —— 策略模式（ Strategy）](http://laravelacademy.org/post/2990.html)

