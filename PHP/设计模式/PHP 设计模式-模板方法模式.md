## 模式定义
模板方法模式又叫模板模式，该模式在一个方法中定义一个算法的骨架，而将一些步骤延迟到子类中。模板方法使得子类可以在不改变算法结构的情况下，重新定义算法中的某些步骤。

模板方法模式将主要的方法定义为 final，防止子类修改算法骨架，将子类必须实现的方法定义为 abstract。而普通的方法（无 final 或 abstract 修饰）则称之为钩子（hook）。


## UML 类图
![模板方法模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1468065120702.png)


## 示例代码

**Journey.php**

```php
namespace DesignPatterns\Behavioral\TemplateMethod;

abstract class Journey
{
    /**
     * 该方法是父类和子类提供的公共服务
     * 注意到方法前加了final，意味着子类不能重写该方法
     */
    final public function takeATrip()
    {
        $this->buyAFlight();
        $this->takePlane();
        $this->enjoyVacation();
        $this->buyGift();
        $this->takePlane();
    }
    
    /**
     * 该方法必须被子类实现, 这是模板方法模式的核心特性
     */
    abstract protected function enjoyVacation();
    
    /**
     * 这个方法也是算法的一部分，但是是可选的，只有在需要的时候才去重写它
     */
    protected function buyGift()
    {
    }
    
    /**
     * 子类不能访问该方法
     */
    private function buyAFlight()
    {
        echo "Buying a flight\n";
    }
    
    /**
     * 这也是个final方法
     */
    final protected function takePlane()
    {
        echo "Taking the plane\n";
    }
}
```

**BeachJourney.php**

```php
namespace DesignPatterns\Behavioral\TemplateMethod;

/**
 * BeachJourney 类（在海滩度假）
 */
class BeachJourney extends Journey
{
    protected function enjoyVacation()
    {
        echo "Swimming and sun-bathing\n";
    }
}
```

**CityJourney.php**

```php
namespace DesignPatterns\Behavioral\TemplateMethod;

class CityJourney extends Journey
{
    protected function enjoyVacation()
    {
        echo "Eat, drink, take photos and sleep\n";
    }
}
```


## 总结
模板方法模式是基于继承的代码复用技术，模板方法模式的结构和用法也是面向对象设计的核心之一。在模板方法模式中，可以将相同的代码放在父类中，而将不同的方法实现放在不同的子类中。

在模板方法模式中，我们需要准备一个抽象类，将部分逻辑以具体方法以及具体构造函数的形式实现，然后声明一些抽象方法来让子类实现剩余的逻辑。不同的子类可以以不同的方式实现这些抽象方法，从而对剩余的逻辑有不同的实现，这就是模板方法模式的用意。模板方法模式体现了面向对象的诸多重要思想，是一种使用频率较高的模式。


## 参考
[PHP 设计模式系列 —— 模板方法模式（Template Method）](http://laravelacademy.org/post/3006.html)

