## 模式定义
简单工厂的作用是实例化对象，而不需要客户了解这个对象属于哪个具体的子类。简单工厂实例化的类具有相同的接口或者基类，在子类比较固定并不需要扩展时，可以使用简单工厂。

也就是说：所有的具体的生成对象的类都属于某一个基类，或者继承实现同一套接口。然后可以新建一类，在这个类里面根据用户传入的参数来生成不同的子类对象，而不需要调用不同的子类来 new 生成其对象。

## UML 类图
![简单工厂模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1466937260004.png)

* ConcreteFactory：工厂类，简单工厂模式的核心，它负责实现创建所有实例的内部逻辑，含有一定的商业逻辑和判断逻辑。工厂类的创建产品类的方法可以被外界直接调用，创建所需的产品对象。
* VehicleInterface：抽象产品类，简单工厂模式所创建的所有对象的父类，它负责描述所有实例所共有的公共接口。
* Bicycle 和 Scooter：具体产品类，是简单工厂模式的创建目标。

## 实例代码

**ConcreteFactory.php**

```php
<?php
namespace DesignPatterns\Creational\SimpleFactory;

/**
 * ConcreteFactory
 */
class ConcreteFactory
{
    /**
     * @var array
     */
    protected $typeList;
    
    /**
     * 你可以在这里注入自己的车子类型
     */
    public function __construct()
    {
        $this->typeList = array(
            'bicycle' => __NAMESPACE__ . '\Bicycle',
            'other'   => __NAMESPACE__ . '\Scooter',
        );
    }
    
    /**
     * 创建车子
     *
     * @param string $type a known type key
     *
     * @return VehicleInterface a new instance of VehicleInterface
     * @throws \InvalidArgumentException
     */
    public function createVehicle($type)
    {
        if (!array_exists($type, $this->typeList) {
            throw new \InvalidArgumentException("$type is not valid vehicle");
        }
        
        $className = $this->typeList[$type];
        
        return new $className();
    }
}
```

**VehicleInterface.php**

```php
<?php
namespace DesignPatterns\Creational\SimpleFactory;

/**
 * VehicleInterface 是车子接口
 * 
 * 每种车子都有驾驶的功能
 */
interface VehicleInterface
{
    /**
     * @param mixed $destination
     *
     * @return mixed
     */
    public function driveTo($destination);
}
```

**Bicycle.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory;

/**
 * 自行车类
 */
class Bicycle implements VehicleInterface
{
    /**
     * @param mixed $destination
     *
     * @return mixed|void
     */
    public function driveTo($destination)
    {
        // ToDo
    }
}
```

**Scooter.php**

```php
<?php

namespace DesignPatterns\Creational\SimpleFactory;

/**
 * 摩托车类
 */
class Scooter implements VehicleInterface
{
    /**
     * @param mixed $destination
     */
    public function driveTo($destination)
    {
        // ToDo
    }
}
```

这样，就能使用`ConcreteFactory`类来分别创建`Bicycle`和`Scooter`类的对象，而不需要分别调用`Bicycle`和`Scooter`来分别生成各自的对象实例。

## 总结
优点：采用简单工厂的优点是可以使用户根据参数获得对应的类实例，避免了直接实例化类，降低了耦合性。

缺点：可实例化的类型在编译期间已经被确定，如果增加新类型，则需要修改工厂，不符合 OCP（开闭原则）的原则。简单工厂需要知道所有要生成的类型，当子类过多或者子类层次过多时不适合使用。

另外，如果将简单工厂模式中创建具体实例对象的方法设置成静态方法，那就成了静态工厂模式了。静态工厂模式使用一个静态方法来创建所有类型的对象，该静态方法通常是`factory`或`build`。

## 参考：
1. [PHP 设计模式系列 —— 简单工厂模式 Laravel 学院](http://laravelacademy.org/post/2643.html)
2. [PHP 设计模式系列 —— 静态工厂模式（Static Factory）](http://laravelacademy.org/post/2647.html)

