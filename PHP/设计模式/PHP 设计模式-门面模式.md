## 模式定义
门面模式（Facade）又称外观模式，用于为子系统中的一组接口提供一个一致的界面。门面模式定义了一个高层接口，这个接口使得子系统更加容易使用：引入门面角色之后，用户只需要直接与门面角色交互，用户与子系统之间的复杂关系由门面角色来实现，从而降低了系统的耦合度。


## UML 类图
![门面模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467732256531.png)


## 示例代码

**Facade.php**

```php
namespace DesignPatterns\Structural\Facade;

/**
 * 门面类
 */
class Facade
{
    /**
     * @var OsInterface
     */
    protected $os;
    
    /**
     * @var BiosInterface
     */
    protected $bios;
    
    /**
     * This is the perfect time to use a dependency injection container
     * to create an instance of this class
     *
     * @param BiosInterface $bios
     * @param OsInterface   $os
     */
    public function __construct(BiosInterface $bios, OsInterface $os)
    {
        $this->bios = $bios;
        $this->os   = $os;
    }
    
    /**
     * turn on the system
     */
    public function turnOn()
    {
        $this->bios->execute();
        $this->bios->waitForKeyPress();
        $this->bios->launch($this->os);
    }
    
    /**
     * turn off the system
     */
    public function turnOff()
    {
        $this->os->halt();
        $this->bios->powerDown();
    }
}
```

**OsInterface.php**

```php
namespace DesignPatterns\Structural\Facade;

/**
 * OsInterface 接口
 */
interface OsInterface
{
    /**
     * halt the OS
     */
    public function halt();
}
```

**BiosInterface.php**

```php
namespace DesignPatterns\Structural\Facade;

/**
 * BiosInterface 接口
 */
interface BiosInterface
{
    /**
     * execute the BIOS
     */
    public function execute();
    
    /**
     * wait for halt
     */
    public function waitForKeyPress();
    
    /**
     * launches the OS
     * 
     * 这里也算是依赖注入
     * 
     * @param OsInterface $os
     */
    public function launch(OsInterface $os);
    
    /**
     * power down BIOS
     */
    public function powerDown();
}
```


## 总结
门面模式对客户屏蔽子系统组件，因而减少了客户处理的对象的数目并使得子系统使用起来更加方便；实现了子系统与客户之间的松耦合关系，而子系统内部的功能组件往往是紧耦合的，松耦合关系使得子系统的组件变化不会影响到它的客户；如果应用需要，门面模式并不限制客户程序使用子系统类，因此你可以让客户程序在系统易用性和通用性之间加以选择。

Laravel 中门面模式的使用也很广泛，基本上每个服务容器中注册的服务提供者类都对应一个门面类。

可以发现，其实门面模式做的工作就是将子系统的接口重新组合封装起来，只对外提供更简单的接口，避免了调用的复杂性。这有点类似于创建模式中的“建造者模式”和构建模式中的“适配器模式”的思想。


## 参考
[PHP 设计模式系列 —— 门面模式（Facade）](http://laravelacademy.org/post/2807.html)

