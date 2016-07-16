## 模式定义
委托是对一个类的功能进行扩展和复用的方法。它的做法是：写一个附加的类提供附加的功能，并使用原来的类的实例提供原有的功能。

假设我们有一个 TeamLead 类，将其既定任务委托给一个关联辅助对象 JuniorDeveloper 来完成：本来 TeamLead 处理 writeCode 方法，Usage 调用 TeamLead 的该方法，但现在 TeamLead 将 writeCode 的实现委托给 JuniorDeveloper 的 writeBadCode 来实现，但 Usage 并没有感知在执行 writeBadCode 方法。


## UML 类图
![委托模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468079844564.png)


## 示例代码

**TeamLead.php**

```php
namespace DesignPatterns\More\Delegation;

/**
 * TeamLead 类
 * 
 * `TeamLead` 类将工作委托给 `JuniorDeveloper`
 */
class TeamLead
{
    /** 
     * @var JuniorDeveloper
     */
    protected $slave;
    
    /**
     * 在构造函数中注入初级开发者 JuniorDeveloper
     * @param JuniorDeveloper $junior
     */
    public function __construct(JuniorDeveloper $junior)
    {
        $this->slave = $junior;
    }
    
    /**
     * TeamLead 喝咖啡, JuniorDeveloper 工作
     * @return mixed
     */
    public function writeCode()
    {
        return $this->slave->writeBadCode();
    }
}
```

**JuniorDeveloper.php**

```php
namespace DesignPatterns\More\Delegation;

/**
 * JuniorDeveloper 类
 * @package DesignPatterns\Delegation
 */
class JuniorDeveloper
{
    public function writeBadCode()
    {
        return "Some junior developer generated code...";
    }
}
```

**Test.php**

```php
use DesignPatterns\More\Delegation;

// 初始化 TeamLead 并委托辅助者 JuniorDeveloper
$teamLead = new TeamLead(new JuniorDeveloper());

// TeamLead 将编写代码的任务委托给 JuniorDeveloper
// 但是我们在调用的仍然是 TeamLead 中的 writeCode() 方法
echo $teamLead->writeCode();
```

## 总结
委托模式其实很容易理解，就是在实现类的时候，在其某个方法中直接调用别的对象的方法，然后返回内容。这也是很常见很常用的方式。


## 参考
[PHP 设计模式系列 —— 委托模式（ Delegation）](http://laravelacademy.org/post/3038.html)

