## 模式定义
建造者模式将一个复杂的对象的构建与它的表示分离，使得同样的构建过程可以创建不同的表示。


## 问题引出
假设我们有个生产车的工厂，可以制造各种车，比如自行车、汽车、卡车等等，如果每辆车都是从头到尾按部就班地造，必然效率低下。


## 解决办法
我们可以试着将车的组装和零部件生产分离开来：让一个类似“导演”的角色负责车子组装，而具体造什么样的车需要什么样的零部件让具体的“构造者”去实现，“导演”知道什么样的车应该怎么去造，而制造过程中需要用到的零部件则让“构造者”去建造，何时完成由“导演”来控制并最终返回给客户端。


## UML 类图
![建造者模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467213206719.png)


## 示例代码

**Director.php**

```php
namespace DesignPatterns\Creational\Builder;

/**
 * Director 是建造者模式的一部分，它知道建造者接口并通过建造者构建复杂对象。
 *
 * 可以通过依赖注入建造者的方式构造任何复杂对象
 */
class Director
{
    /**
     * “导演”知道流程但并不知道具体实现细节
     *
     * @param BuilderInterface $builder
     *
     * @return Parts\Vehicle
     */
    public function build(BuilderInterface $builder)
    {
        $builder->createVehicle();
        $builder->addDoors();
        $builder->addEngine();
        $builder->addWheel();
        
        return $builder->getVehicle();
    }
}
```

**BuilderInterface.php**

```php
namespace DesignPatterns\Creational\Builder;

/**
 * 建造者接口
 * 
 * 提供建造车子的统一的方法接口
 */
interface BuilderInterface
{
    /**
     * @return mixed
     */
    public function createVehicle();
    
    /**
     * @return mixed
     */
    public function addDoors();
    
    /**
     * @return mixed
     */
    public function addEngine();
    
    /**
     * @return mixed
     */
    public function addWheel();
    
    /**
     * @return mixed
     */
    public function getVehicle();
}
```

**BikeBuilder.php**

```php
namespace DesignPatterns\Creational\Builder;

/**
 * BikeBuilder 用于建造自行车
 */
class BikeBuilder implements BuilderInterface
{
    /**
     * @var Parts\Bike
     */
    protected $bike;
    
    public function createVehicle()
    {
        $this->bike = new Parts\Bike();
    }
    
    public function addDoors()
    {
        // Nothing To Do
    }
    
    public function addEngine()
    {
        $this->bike->setPart('engine', new Parts\Engine());
    }
    
    public function addWheel()
    {
        $this->bike->setPart('forwardWheel', new Parts\Wheel());
        $this->bike->setPart('rearWheel', new Parts\Wheel());
    }
    
    public function getVehicle()
    {
        return $this->bike;
    }
}
```

**CarBuilder.php**

```php
namespace DesignPatterns\Creational\Builder;

/**
 * CarBuilder用于建造汽车
 */
class CarBuilder implements BuilderInterface
{
    /**
     * @var Parts\Car
     */
    protected $car;
    
    public function createVehicle()
    {
        $this->car = new Parts\Car();
    }
    
    public function addDoors()
    {
        $this->car->setPart('leftDoor', new Parts\Door());
        $this->car->setPart('rightDoor', new Parts\Door());
    }
    
    public function addEngine()
    {
        $this->car->setPart('engine', new Parts\Engine());
    }
    
    public function addWheel()
    {
        $this->car->setPart('wheelLF', new Parts\Wheel());
        $this->car->setPart('wheelRF', new Parts\Wheel());
        $this->car->setPart('wheelLR', new Parts\Wheel());
        $this->car->setPart('wheelRR', new Parts\Wheel());
    }
    
    public function getVehicle()
    {
        return $this->car;
    }
}
```

**Parts/Vehicle.php**

```php
namespace DesignPatterns\Creational\Builder\Parts;

/**
 * VehicleInterface 是车辆接口
 */
abstract Vehicle
{
    /**
     * @var array
     */
    protected $data;
    
    /**
     * @param string $key
     * @param mixed $value
     */
    public function setPart($part, $value)
    {
        $this->data[$part] = $value;
    }
}
```

**Parts/Bike.php**

```php
namespace DesignPatterns\Creational\Builder\Parts;

/**
 * Bike
 */
class Bike extends Vehicle
{
}
```

**Parts/Car.php**

```php
namespace DesignPatterns\Creational\Builder\Parts;

/**
 * Car
 */
class Car extends Vehicle
{
}
```

**Parts/Door.php**

```php
namespace DesignPatterns\Creational\Builder\Parts;

/**
 * Door
 */
class Door
{
}
```

**Parts/Engine.php**

```php
namespace DesignPatterns\Creational\Builder\Parts;

/**
 * Engine
 */
class Engine
{
}
```

**Parts/Wheel.php**

```php
namespace DesignPatterns\Creational\Builder\Parts;

/**
 * Wheel
 */
class Wheel
{
}
```


## 总结
建造者模式和抽象工厂模式很像：总体上，建造者模式仅仅只比抽象工厂模式多了一个“导演类”的角色。

与抽象工厂模式相比，建造者模式一般用来创建更为复杂的对象，因为对象的创建过程更为复杂，因此将对象的创建过程独立出来组成一个新的类 —— 导演类。

也就是说，抽像工厂模式是将对象的全部创建过程封装在工厂类中，由工厂类向客户端提供最终的产品；而建造者模式中，建造者类一般只提供产品类中各个组件的建造，而将完整建造过程交付给导演类。由导演类负责将各个组件按照特定的规则组建为产品，然后将组建好的产品交付给客户端。也就是整体流程和部件建造分开进行。


## 参考
[PHP 设计模式系列 —— 建造者模式（Builder）](http://laravelacademy.org/post/2489.html)

