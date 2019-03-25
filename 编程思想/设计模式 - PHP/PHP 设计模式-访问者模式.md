## 模式定义
我们去银行柜台办业务，一般情况下会开几个个人业务柜台的，你去其中任何一个柜台办理都是可以的。我们的访问者模式可以很好付诸在这个场景中：对于银行柜台来说，他们是不用变化的，就是说今天和明天提供个人业务的柜台是不需要有变化的。而我们作为访问者，今天来银行可能是取消费流水，明天来银行可能是去办理手机银行业务，这些是我们访问者的操作，一直是在变化的。

访问者模式就是表示一个作用于某对象结构中的各元素的操作。它使你可以在不改变各元素的类的前提下定义作用于这些元素的新操作。


## UML 类图
![访问者模式](http://cnd.qiniu.lin07ux.cn/markdown/1468077399151.png)


## 示例代码

**RoleVisitorInterface.php**

```php
namespace DesignPatterns\Behavioral\Visitor;

/**
 * 访问者接口
 */
interface RoleVisitorInterface
{
    /**
     * 访问 User 对象
     *
     * @param \DesignPatterns\Behavioral\Visitor\User $role
     */
    public function visitUser(User $role);
    
    /**
     * 访问 Group 对象
     *
     * @param \DesignPatterns\Behavioral\Visitor\Group $role
     */
    public function visitGroup(Group $role);
}
```

**RolePrintVisitor.php**

```php
namespace DesignPatterns\Behavioral\Visitor;

/**
 * Visitor接口的具体实现
 */
class RolePrintVisitor implements RoleVisitorInterface
{
    public function visitUser(User $role)
    {
        echo "Role ' . $role->getName();
    }
    
    public function visitGroup(Group $role)
    {
        echo "Role ' . $role->getName();
    }
}
```

**Role.php**

```php
namespace DesignPatterns\Behavioral\Visitor;

/**
 * Role 类
 */
abstract class Role
{
    /**
     * 该方法基于 Visitor 的类名判断调用 Visitor 的方法
     *
     * 如果必须调用其它方法，重写本方法即可
     *
     * @param \DesignPatterns\Behavioral\Visitor\RoleVisitorInterface $visitor
     *
     * @throws \InvalidArgumentException
     */
    public function accept(RoleVisitorInterface $visitor)
    {
        $kclass = get_called_class();
        preg_match('#([^\\\\]+)$#', $klass, $extract);
        $visitingMethod = 'visit' . $extract[1];

        if (!method_exists(__NAMESPACE__ . '\RoleVisitorInterface', $visitingMethod)) {
            throw new \InvalidArgumentException("The visitor you provide cannot visit a $klass instance");
        }

        call_user_func(array($visitor, $visitingMethod), $this);
    }
}
```

**User.php**

```php
namespace DesignPatterns\Behavioral\Visitor;

class User extends Role
{
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string)$name;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
```

**Group.php**

```php
namespace DesignPatterns\Behavioral\Visitor;

class Group extends Role
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Group: " . $this->name;
    }
}
```

**Test.php**

```php
use DesignPatterns\Behavioral\Visitor;

$visitor = new Visitor\RolePrintVisitor();

$user    = new Visitor\User("Dominik");
$user->accept($visitor);   // Role: User Dominik

$group   = new Visitor\Group("Administrators")
$group->accept($visitor);  // Role: Group: Administrators
```

## 总结
访问者模式适用于数据结构相对稳定的系统，它把数据结构和作用于结构之上的操作之间的耦合解脱开，使得操作集合可以相对自由的演化。在本例中，User、Group 是数据结构，而 RolePrintVisitor 是访问者（用于结构之上的操作）。

当实现访问者模式时，要将尽可能多的将对象浏览逻辑放在 Visitor 类中，而不是放在它的子类中，这样的话，ConcreteVisitor 类所访问的对象结构依赖较少，从而使维护较为容易。

访问者模式和观察者模式的区别在于：观察者模式需要被观察对象在变化时主动通知其观察者，从而使其变化被观察者得到；访问者模式则不需要被访问者主动通知，而是在需要的时候，由被访问者注入一个访问者，从而使得访问者能够得到被访问者的某个或某些属性和状态。


## 参考
[PHP 设计模式系列 —— 访问者模式（Visitor）](http://laravelacademy.org/post/3024.html)

