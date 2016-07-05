## 模式定义
装饰器模式能够从一个对象的外部动态地给对象添加功能。

通常给对象添加功能，要么直接修改对象添加相应的功能，要么派生对应的子类来扩展，抑或是使用对象组合的方式。显然，直接修改对应的类这种方式并不可取。在面向对象的设计中，我们也应该尽量使用对象组合，而不是对象继承来扩展和复用功能。装饰器模式就是基于对象组合的方式，可以很灵活的给对象添加所需要的功能。装饰器模式的本质就是动态组合。动态是手段，组合才是目的。

常见的使用示例：Web服务层 —— 为 REST 服务提供 JSON 和 XML 装饰器。


## UML 类图
![装饰器模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467693466744.png)


## 示例代码

**RenderInterface.php**

```php
namespace DesignPatterns\Structural\Decorator;

/**
 * RendererInterface接口
 */
interface RenderIterface
{
    /**
     * render data
     *
     * @return mixed
     */
    public function renderData();
}
```

**WebService.php**

```php
namespace DesignPatterns\Structural\Decorator;

/**
 * Webservice 类
 */
class WebService implements RenderInterface
{
    /**
     * @var mixed
     */
    protected $data;
    
    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    /**
     * @return string
     */
    public function renderData()
    {
        return $this->data;
    }
}
```

**Decorator.php**

```php
namespace DesignPatterns\Structural\Decorator;

/**
 * Decorator 类
 * 
 * 装饰器必须实现 RendererInterface 接口, 这是装饰器模式的主要特点，
 * 否则的话就不是装饰器而只是个包裹类
 */
abstract class Decorator implements RenderInterface
{
    /**
     * @var RendererInterface
     */
    protected $wrapped;
    
    /**
     * 必须类型声明装饰组件以便在子类中可以调用renderData()方法
     *
     * @param RendererInterface $wrappable
     */
    public function __construct(RenderInterface $wrappable)
    {
        $this->wrapped = $wrappable;
    }
}
```

**RenderInXML.php**

```php
namespace DesignPatterns\Structural\Decorator;

/**
 * RenderInXml类
 */
class RenderInXML extends Decorator
{
    /**
     * render data as XML
     *
     * @return mixed|string
     */
    public function renderData()
    {
        $output = $this->wrapped->renderData();
        
        // do some fancy conversion to xml from array ...
        
        $doc = new \DOMDocument();
        
        foreach ($output as $key => $val) {
            $doc->appendChild($doc->createElement($key, $val));
        }
        
        return $doc->saveXML();
    }
}
```

**RenderInJson.php**

```php
namespace DesignPatterns\Structural\Decorator;

/**
 * RenderInJson 类
 */
class RenderInJson extends Decorator
{
    /**
     * render data as JSON
     *
     * @return mixed|string
     */
    public function renderData()
    {
        $output = $this->wrapped->renderData();
        
        return json_encode($output);
    }
}
```


## 总结
装饰器模式的基础就是组合模式，所以，装饰器模式中，装饰器类也必须实现单个元素的接口。但是装饰器模式并不是简单的将一些单个元素组合起来，而是将单个元素“变成”另一种形态，装饰器只会改变数据的最终展现形式，而不是修改原先的结构和数据。


## 参考
[PHP 设计模式系列 —— 装饰器模式（Decorator）](http://laravelacademy.org/post/2760.html)

