## 模式概述
抽象工厂模式为一组相关或相互依赖的对象创建提供接口，而无需指定其具体实现类。抽象工厂的客户端不关心如何创建这些对象，只关心如何将它们组合到一起。

## 问题引出
举个例子，如果某个应用是可移植的，那么它需要封装平台依赖，这些平台可能包括窗口系统、操作系统、数据库等等。这种封装如果未经设计，通常代码会包含多个 if 条件语句以及对应平台的操作。这种硬编码不仅可读性差，而且扩展性也不好。

## 解决方案
提供一个间接的层（即“抽象工厂”）抽象一组相关或依赖对象的创建而不是直接指定具体实现类。该“工厂”对象的职责是为不同平台提供创建服务。客户端不需要直接创建平台对象，而是让工厂去做这件事。

这种机制让替换平台变得简单，因为抽象工厂的具体实现类只有在实例化的时候才出现，如果要替换的话只需要在实例化的时候指定具体实现类即可。

## UML 类图
抽象工厂为每个产品（具体实现）定义了工厂方法，每个工厂方法封装了 new 操作符和具体类（指定平台的产品类），每个“平台”都是抽象工厂的派生类。这样就可以调用针对特定“平台”的工厂类来生成具体的产品了。

![抽象工厂模式](http://7xkt52.com1.z0.glb.clouddn.com/markdown/1467187330018.png)

在本例中，抽象工厂为创建 Web 组件（产品）提供了接口，这里有两个组件：文本和图片，有两种渲染方式：HTML 和 JSON，所以需要对应四个具体实现类。

尽管有四个具体类，但是客户端只需要知道这个接口可以用于构建正确的 HTTP 响应即可，无需关心其具体实现。

在这里，就可以根据不同的渲染方式，实现不同的两个工厂，并在工厂内实现具体的接口方法从而生成特定渲染方式下的组件。

## 代码实现
**AbstractFactory.php**

```php
namespace DesignPatterns\Creational\AbstractFactory;

/**
 * 抽象工厂类
 *
 * 该设计模式实现了设计模式的依赖倒置原则，因为最终由具体子类创建具体组件
 */
abstract class AbstractFactory
{
    /**
     * 创建本文组件
     *
     * @param string $content
     *
     * @return Text
     */
    abstract public function createText($content);
    
    /**
     * 创建图片组件
     *
     * @param string $path
     * @param string $name
     *
     * @return Picture
     */
    abstract public function createPicture($path, $name='');
}
```

**JsonFactory.php**

```php
namespace DesignPatterns\Creational\AbstractFactory;

/**
 * JsonFactory类
 *
 * JsonFactory 是用于创建 JSON 组件的工厂
 */
class JsonFactory extends AbstractFactory
{
    /**
     * 创建文本组件
     *
     * @param string $content
     *
     * @return Json\Text|Text
     */
    public function createText($content)
    {
        return new Json\Text($content);
    }
    
    /**
     * 创建图片组件
     *
     * @param string $path
     * @param string $name
     *
     * @return Json\Picture|Picture
     */
    public function createPicture($path, $name='')
    {
        return Json\Picture($path, $name);
    }
}
```

**HtmlFactory.php**

```php
namespace DesignPatterns\Creational\AbstractFactory;

/**
 * HtmlFactory类
 *
 * HtmlFactory 是用于创建 HTML 组件的工厂
 */
class HtmlFactory extends AbstractFactory
{
    /**
     * 创建文本组件
     *
     * @param string $content
     *
     * @return Html\Text|Text
     */
    public function createText($content)
    {
        return Html\Text($content);
    }
    
    /**
     * 创建图片组件
     *
     * @param string $path
     * @param string $name
     *
     * @return Html\Picture|Picture
     */
    public function createPicture($path, $name='')
    {
        return Html\Picture($path, $name);
    }
}
```

**MediaInterface.php**

```php
namespace DesignPatterns\Creational\AbstractFactory;

/**
 * MediaInterface接口
 *
 * 该接口不是抽象工厂设计模式的一部分, 一般情况下, 每个组件都是不相干的
 */
interface MediaInterface 
{
    /**
     * JSON 或 HTML（取决于具体类）输出的未经处理的渲染
     *
     * @return string
     */
    public function render();
}
```

**Text.php**

```php
namespace DesignPatterns\Creational\AbstractFactory;

/**
 * Text类
 */
abstract class Text implements MediaInterface
{
    /**
     * @var string
     */
    protected $text;
    
    /**
     * @param string $text
     */
    public function __construct($content)
    {
        $this->text = $content;
    }
}
```

**Picture.php**

```php
namespace DesignPatterns\Creational\AbstractFactory;

/**
 * Picture类
 */
abstract class Picture implements MediaInterface
{
    /**
     * @var string
     */
    protected $path;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @param string $path
     * @param string $name
     */
    public function __construct($path, $name='')
    {
        $this->path = $path;
        $this->name = $name;
    }
}
```

**Json/Text.php**

```php
namespace DesignPatterns\Creational\AbstractFactory\Json;

use DesignPatterns\Creational\AbstractFactory\Text as BaseText;

/**
 * Text 类
 *
 * 该类是以 JSON 格式输出的具体文本组件类
 */
class Text extend BaseText
{
/**
     * JSON 格式输出
     *
     * @return string
     */
    public function render()
    {
        return json_encode(array('content' => $this->text));
    }
}
```

**Json/Picture.php**

```php
namespace DesignPatterns\Creational\AbstractFactory\Json;

use DesignPatterns\Creational\AbstractFactory\Picture as BasePicture;

/**
 * Picture类
 *
 * 该类是以 JSON 格式输出的具体图片组件类
 */
class Picture extend BasePicture
{
/**
     * JSON 格式输出
     *
     * @return string
     */
    public function render()
    {
        return json_encode(array(
            'title' => $this->name,
            'path'  => $this->path,   
        ));
    }
}
```

**Html/Text.php**

```php
namespace DesignPatterns\Creational\AbstractFactory\Html;

use DesignPatterns\Creational\AbstractFactory\Text as BaseText;

/**
 * Text 类
 *
 * 该类是以 HTML 渲染的具体文本组件类
 */
class Text extends BaseText
{
    /**
     * HTML 格式输出的文本
     *
     * @return string
     */
    public function render()
    {
        return '<p>' . htmlspecialchars($this->tex) . '</p>';
    }
}
```

**Html/Picture.php**

```php
namespace DesignPatterns\Creational\AbstractFactory\Html;

use DesignPatterns\Creational\AbstractFactory\Picture as BasePicture;

/**
 * Picture 类
 *
 * 该类是以 HTML 格式渲染的具体图片类
 */
class Picture extends BasePicture
{
    /**
     * HTML 格式输出的图片
     *
     * @return string
     */
    public function render()
    {
        return sprintf('<img src="%s" title="%s"/>', $this->path, $this->name);
    }
}
```

可以看到，有了抽象工厂这个同一的接口，我们虽然有多个不同的渲染方式，但是每个渲染方式的调用方式是相同的，而且不需要进行更多的判断，就可以使用不同渲染方式的具体工厂对象的具体方法来产生需要的响应。

## 总结
最后我们以工厂生产产品为例：所谓抽象工厂模式就是我们的抽象工厂约定了可以生产的产品，这些产品都包含多种规格，然后我们可以从抽象工厂为每一种规格派生出具体工厂类，然后让这些具体工厂类生产具体的产品。上面示例中`AbstractFactory`是抽象工厂，`JsonFactory`和`HtmlFactory`是具体工厂，`Html\Picture`、`Html\Text`、`Json\Picture`和`Json\Text`都是具体产品，客户端需要 HTML 格式的 Text，调用`HtmlFactory`的`createText`方法即可，而不必关心其实现逻辑。

由于每个工厂子类都需要生产所有类别的产品，所以抽象工厂模式不适用于产品会增加的情况。不过，它可以扩展工厂子类，从而适应其他的一些适配情况。也即是：抽象工厂模式可以扩展生产工厂(生成方式)，但是不能扩展产品。


## 参考
[PHP 设计模式系列 —— 抽象工厂模式（Abstract Factory）](http://laravelacademy.org/post/2471.html)

