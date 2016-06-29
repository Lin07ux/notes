## 模式定义
定义一个创建对象的接口，但是让子类去实例化具体类。工厂方法模式让类的实例化延迟到子类中。


## 问题引出
框架需要为多个应用提供标准化的架构模型，同时也要允许独立应用定义自己的域对象并对其进行实例化。


## 解决办法
工厂方法以模板方法的方式创建对象来解决上述问题。父类定义所有标准通用行为，然后将创建细节放到子类中实现并输出给客户端。

人们通常使用工厂模式作为创建对象的标准方式，但是在这些情况下不必使用工厂方法：实例化的类永远不会改变；或者实例化发生在子类可以轻易覆盖的操作中（比如初始化）。


## UML 类图
![工厂方法](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467191387171.png)


## 示例代码
**FactoryMethod.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

/**
 * 工厂方法抽象类
 */
abstract class FactoryMethod
{
    const CHEAP = 0;
    const FAST  = 1;
    
    /**
     * 子类必须实现该方法
     *
     * @param string $type a generic type
     *
     * @return VehicleInterface a new vehicle
     */
    abstract protected function createVehicle($type);
    
    /**
     * 创建新的车辆
     *
     * @param int $type
     *
     * @return VehicleInterface a new vehicle
     */
    public function create($type)
    {
        $vehicle = $this->createVehicle($type);
        $vehicle->setColor("#f00');
        
        return $vehicle;
    }
}
```

**ItalianFactory.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

/**
 * ItalianFactory 是意大利的造车厂
 */
class ItalianFactory extends FactoryMethod
{
    protected function createVehicle($type)
    {
        switch ($type) {
            case parent::CHEAP :
                return new Bicycle();
                break;
            case parent::FAST :
                return new Ferrari();
                break;
            default :
                throw new \InvalidArgumentException("$type is not a valid vehicle");
        }
    }
}
```

**GermanFactory.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

class GermanFactory extends FactoryMethod
{
    protected function createVehicle($type)
    {
        switch ($type) {
            case parent::CHEAP:
                return new Bicycle();
                break;
            case parent::FAST:
                $vehicle = new Prosche();
                //因为我们已经知道是什么对象所以可以调用具体方法
                $vehicle->addTuningAMG();
                return $vehicle;
                break;
            default:
                throw new \InvalidArgumentException("$type is not a valid vehicle");
        }
    }
}
```

**VehicleInterface.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

/**
 * VehicleInterface 是车辆接口
 * 定义一套车辆应该具有的公共接口
 */
interface VehicleInterface
{
    /**
     * 设置车的颜色
     *
     * @param string $rgb
     */
    public function setColor($rgb);
}
```

**Bicycle.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

/**
 * Bicycle（自行车）
 */
class Bicycle implements VehicleInterface
{
    /**
     * @var string
     */
    protected $color;

    /**
     * 设置自行车的颜色
     *
     * @param string $rgb
     */
    public function setColor($rgb)
    {
        $this->color = $rgb;
    }
}
```

**Ferrari.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

/**
 * Ferrari（法拉利）
 */
class Ferrari implements VehicleInterface
{
    /**
     * @var string
     */
    protected $color;

    /**
     * 设置自行车的颜色
     *
     * @param string $rgb
     */
    public function setColor($rgb)
    {
        $this->color = $rgb;
    }
}
```

**Porsche.php**

```php
namespace DesignPatterns\Creational\FactoryMethod;

/**
 * Porsche（保时捷）
 */
class Porsche implements VehicleInterface
{
    /**
     * @var string
     */
    protected $color;

    /**
     * @param string $rgb
     */
    public function setColor($color)
    {
        $this->color = $color;
    }
    
    /**
     * 尽管只有奔驰汽车挂有AMG品牌，这里我们提供一个空方法仅作代码示例
     */
    public function addTuningAMG()
    {
    }
}
```

可以看到，每个具体的工厂只生产特定的产品(可能会生产部分通用的产品)。所以，如果增加了新的产品，那么就可以通过增加一个新的工厂子类即可。


## 总结
工厂方法模式和抽象工厂模式有点类似，但也有不同：

- 工厂方法针对每一种产品提供一个工厂类，通过不同的工厂实例来创建不同的产品实例，在同一等级结构中，支持增加任意产品。
- 抽象工厂是应对产品族概念的，用来生产不同产品族的全部产品。比如说，每个汽车公司可能要同时生产轿车，货车，客车，那么每一个工厂都要有创建轿车，货车和客车的方法。应对产品族概念而生，增加新的产品线很容易，但是无法增加新的产品。对于增加新的产品，无能为力；但是支持增加产品族。
- 简单工厂用来生产同一等级结构中的任意产品。（对于增加新的产品，或新的生成方式，无能为力）

也就是说：工厂方法模式是对应产品种类会增加的情况，而抽象工厂模式则是对应生产产品的方式会增加的情况。一个对应具体产品的种类，一个对应产品具体生产的方式。


## 参考
[PHP 设计模式系列 —— 工厂方法模式（Factory Method）](http://laravelacademy.org/post/2506.html)


